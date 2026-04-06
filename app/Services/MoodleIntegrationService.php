<?php

namespace App\Services;

use App\Models\CalonSiswa;
use App\Models\MoodleSyncMapping;
use App\Models\PpdbSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MoodleIntegrationService
{
    public const CONNECTION_WEBSERVICE = 'webservice';
    public const CONNECTION_BRIDGE = 'bridge';
    public const PASSWORD_ACCOUNT = 'account';
    public const PASSWORD_CUSTOM = 'custom';
    public const EMAIL_ACCOUNT = 'account';
    public const EMAIL_DOMAIN = 'domain';

    public const MODE_MANUAL = 'manual';
    public const MODE_ON_REGISTER = 'on_register';
    public const MODE_ON_FINALISASI = 'on_finalisasi';
    public const MODE_ON_NOMOR_TES = 'on_nomor_tes';

    public const TRIGGER_REGISTER = 'on_register';
    public const TRIGGER_FINALISASI = 'on_finalisasi';
    public const TRIGGER_NOMOR_TES = 'on_nomor_tes';

    public function getStatusData(PpdbSettings $settings): array
    {
        if (!$settings->moodle_sync_enabled) {
            return ['local', 'Integrasi Moodle belum diaktifkan.'];
        }

        if (!$this->isConfigured($settings)) {
            return ['incomplete', 'Integrasi Moodle aktif, tetapi URL Moodle atau token/secret integrasi belum lengkap.'];
        }

        try {
            $info = $this->testConnection($settings);
            $siteName = $info['sitename'] ?? 'Moodle';
            $modeLabel = $this->isBridgeMode($settings) ? 'Bridge Converter' : 'Web Service';

            return ['ready', "Koneksi Moodle siap dipakai ({$modeLabel}). Server aktif: {$siteName}."];
        } catch (\Throwable $e) {
            return ['warning', 'Integrasi Moodle aktif, tetapi tes koneksi belum berhasil. ' . $e->getMessage()];
        }
    }

    public function isConfigured(?PpdbSettings $settings = null): bool
    {
        $settings ??= PpdbSettings::getActive();

        return !empty($settings?->moodle_base_url) && !empty($settings?->moodle_webservice_token);
    }

    public function shouldSyncForTrigger(PpdbSettings $settings, string $trigger): bool
    {
        if (!$settings->moodle_sync_enabled || !$this->isConfigured($settings)) {
            return false;
        }

        return $settings->moodle_sync_mode === $trigger;
    }

    public function syncCandidateIfNeeded(CalonSiswa $calonSiswa, string $trigger): ?array
    {
        $settings = PpdbSettings::getActive();

        if (!$this->shouldSyncForTrigger($settings, $trigger)) {
            return null;
        }

        try {
            return $this->syncCandidate($calonSiswa, $settings);
        } catch (\Throwable $e) {
            $this->markSyncFailed($calonSiswa, $settings, $e->getMessage());

            Log::warning('Sinkron Moodle gagal', [
                'calon_siswa_id' => $calonSiswa->id,
                'trigger' => $trigger,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function syncCandidate(CalonSiswa $calonSiswa, ?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();

        if (!$settings->moodle_sync_enabled) {
            throw new RuntimeException('Integrasi Moodle belum diaktifkan.');
        }

        if (!$this->isConfigured($settings)) {
            throw new RuntimeException('URL Moodle atau token/secret integrasi belum lengkap.');
        }

        $calonSiswa->loadMissing(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']);

        $mapping = $this->resolveMapping($calonSiswa);
        $profile = $this->resolveUserProfile($calonSiswa, $settings, $mapping);
        $username = $profile['username'];
        $password = $profile['password'];
        $email = $profile['email'];

        if ($this->isBridgeMode($settings)) {
            $result = $this->syncCandidateViaBridge($settings, $calonSiswa, $mapping, [
                'username' => $username,
                'password' => $password,
                'firstname' => $calonSiswa->nama_lengkap,
                'lastname' => $profile['lastname'],
                'email' => $email,
            ]);
            $moodleUserId = (int) ($result['moodle_user_id'] ?? 0);
            $existing = !empty($result['existing']);
            if (!$moodleUserId) {
                throw new RuntimeException('Bridge converter tidak mengembalikan user id Moodle.');
            }
        } else {
            $existing = $this->findUserByUsername($settings, $username);
            if ($existing) {
                $moodleUserId = (int) $existing['id'];
            } else {
                $created = $this->createUser($settings, [
                    'username' => $username,
                    'password' => $password,
                    'firstname' => $calonSiswa->nama_lengkap,
                    'lastname' => $profile['lastname'],
                    'email' => $email,
                ]);

                $moodleUserId = (int) ($created['id'] ?? 0);
                if (!$moodleUserId) {
                    throw new RuntimeException('Moodle tidak mengembalikan user id setelah create user.');
                }
            }

            if ($settings->moodle_assign_default_cohort) {
                $cohortId = $mapping?->moodle_cohort_id ?: $settings->moodle_default_cohort_id;

                if (filled($cohortId)) {
                    $this->addUserToCohort($settings, $moodleUserId, (int) $cohortId);
                }
            }

            if ($settings->moodle_enrol_default_course) {
                $courseIds = $mapping?->moodle_course_ids ?: ($settings->moodle_default_course_ids ?? []);

                if (empty($courseIds) && filled($settings->moodle_default_course_id)) {
                    $courseIds = [(int) $settings->moodle_default_course_id];
                }

                foreach (collect($courseIds)->filter()->map(fn ($id) => (int) $id)->unique() as $courseId) {
                    $this->enrolUserToCourse(
                        $settings,
                        $moodleUserId,
                        $courseId,
                        (int) ($settings->moodle_course_role_id ?: 5)
                    );
                }
            }
        }

        $calonSiswa->forceFill([
            'moodle_user_id' => $moodleUserId,
            'moodle_username' => $username,
            'moodle_sync_status' => 'synced',
            'moodle_synced_at' => now(),
            'moodle_sync_error' => null,
        ])->save();

        $settings->forceFill([
            'moodle_sync_last_error' => null,
            'moodle_sync_last_success_at' => now(),
        ])->save();

        return [
            'moodle_user_id' => $moodleUserId,
            'moodle_username' => $username,
            'existing' => (bool) $existing,
        ];
    }

    public function testConnection(?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();

        if ($this->isBridgeMode($settings)) {
            return $this->bridgeCall($settings, 'ping');
        }

        return $this->call($settings, 'core_webservice_get_site_info');
    }

    public function listCategories(?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();

        if ($this->isBridgeMode($settings)) {
            $categories = $this->bridgeCall($settings, 'categories');

            return collect($categories['categories'] ?? [])
                ->filter(fn ($category) => is_array($category) && isset($category['id']))
                ->map(fn ($category) => [
                    'id' => (string) $category['id'],
                    'name' => $category['name'] ?? ('Category ' . $category['id']),
                    'parent' => (string) ($category['parent'] ?? '0'),
                    'depth' => (int) ($category['depth'] ?? 1),
                ])
                ->values()
                ->all();
        }

        $categories = $this->call($settings, 'core_course_get_categories');

        return collect($categories)
            ->filter(fn ($category) => is_array($category) && isset($category['id']))
            ->map(fn ($category) => [
                'id' => (string) $category['id'],
                'name' => $category['name'] ?? ('Category ' . $category['id']),
                'parent' => (string) ($category['parent'] ?? '0'),
                'depth' => (int) ($category['depth'] ?? 1),
            ])
            ->values()
            ->all();
    }

    public function listCohorts(?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();

        if ($this->isBridgeMode($settings)) {
            $cohorts = $this->bridgeCall($settings, 'cohorts');

            return collect($cohorts['cohorts'] ?? [])
                ->filter(fn ($cohort) => is_array($cohort) && isset($cohort['id']))
                ->map(fn ($cohort) => [
                    'id' => (string) $cohort['id'],
                    'name' => $cohort['name'] ?? ('Cohort ' . $cohort['id']),
                    'idnumber' => $cohort['idnumber'] ?? '',
                    'membercount' => (int) ($cohort['membercount'] ?? 0),
                ])
                ->values()
                ->all();
        }

        return [];
    }

    public function listCoursesByCategory(?PpdbSettings $settings = null, ?string $categoryId = null): array
    {
        $settings ??= PpdbSettings::getActive();

        if (!filled($categoryId)) {
            return [];
        }

        if ($this->isBridgeMode($settings)) {
            $courses = $this->bridgeCall($settings, 'courses', [
                'category_id' => (string) $categoryId,
            ]);

            return collect($courses['courses'] ?? [])
                ->filter(fn ($course) => is_array($course) && isset($course['id']))
                ->map(fn ($course) => [
                    'id' => (string) $course['id'],
                    'fullname' => $course['fullname'] ?? $course['name'] ?? ('Course ' . $course['id']),
                    'shortname' => $course['shortname'] ?? null,
                    'categoryid' => (string) ($course['categoryid'] ?? $course['category'] ?? $categoryId),
                ])
                ->values()
                ->all();
        }

        $courses = $this->call($settings, 'core_course_get_courses_by_field', [
            'field' => 'category',
            'value' => (string) $categoryId,
        ]);

        return collect($courses['courses'] ?? [])
            ->filter(fn ($course) => is_array($course) && isset($course['id']))
            ->map(fn ($course) => [
                'id' => (string) $course['id'],
                'fullname' => $course['fullname'] ?? ('Course ' . $course['id']),
                'shortname' => $course['shortname'] ?? null,
                'categoryid' => (string) ($course['categoryid'] ?? $categoryId),
            ])
            ->values()
            ->all();
    }

    public function usernameForCandidate(CalonSiswa $calonSiswa): string
    {
        return $this->buildUsername($calonSiswa);
    }

    public function findExistingUsersByUsernames(array $usernames, ?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();
        $cleanUsernames = collect($usernames)
            ->map(fn ($username) => trim((string) $username))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($cleanUsernames) || !$this->isConfigured($settings)) {
            return [];
        }

        if ($this->isBridgeMode($settings)) {
            $response = $this->bridgeCall($settings, 'users-status', [
                'usernames' => $cleanUsernames,
            ]);

            return collect($response['users'] ?? [])
                ->filter(fn ($user) => is_array($user) && !empty($user['username']))
                ->mapWithKeys(fn ($user) => [(string) $user['username'] => $user])
                ->all();
        }

        $response = $this->call($settings, 'core_user_get_users_by_field', [
            'field' => 'username',
            'values' => $cleanUsernames,
        ]);

        return collect($response)
            ->filter(fn ($user) => is_array($user) && !empty($user['username']))
            ->mapWithKeys(fn ($user) => [(string) $user['username'] => $user])
            ->all();
    }

    public function refreshCandidatesSyncState(iterable $candidates, ?PpdbSettings $settings = null): array
    {
        $settings ??= PpdbSettings::getActive();
        $candidateCollection = collect($candidates)->filter();

        if ($candidateCollection->isEmpty()) {
            return [];
        }

        $usernames = $candidateCollection
            ->map(fn (CalonSiswa $calonSiswa) => $this->buildUsername($calonSiswa))
            ->all();

        $existingUsers = [];
        if ($settings->moodle_sync_enabled && $this->isConfigured($settings)) {
            $existingUsers = $this->findExistingUsersByUsernames($usernames, $settings);
        }

        $statusRows = [];

        foreach ($candidateCollection as $calonSiswa) {
            $username = $this->buildUsername($calonSiswa);
            $foundUser = $existingUsers[$username] ?? null;

            if ($foundUser) {
                $calonSiswa->forceFill([
                    'moodle_user_id' => (int) ($foundUser['id'] ?? $calonSiswa->moodle_user_id),
                    'moodle_username' => $username,
                    'moodle_sync_status' => 'synced',
                    'moodle_synced_at' => $calonSiswa->moodle_synced_at ?? now(),
                    'moodle_sync_error' => null,
                ])->saveQuietly();
            } elseif ($calonSiswa->moodle_sync_status !== 'error') {
                $calonSiswa->forceFill([
                    'moodle_username' => $username,
                    'moodle_sync_status' => 'not_synced',
                    'moodle_sync_error' => null,
                ])->saveQuietly();
            }

            $statusRows[(string) $calonSiswa->id] = [
                'username' => $username,
                'exists' => (bool) $foundUser,
                'moodle_user_id' => $foundUser['id'] ?? $calonSiswa->moodle_user_id,
                'status' => $foundUser ? 'synced' : ($calonSiswa->moodle_sync_status ?: 'not_synced'),
            ];
        }

        return $statusRows;
    }

    private function buildUsername(CalonSiswa $calonSiswa): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $calonSiswa->nisn ?: ('ppdb_' . $calonSiswa->id));
    }

    private function buildLastName(CalonSiswa $calonSiswa): string
    {
        $tahun = explode('/', (string) ($calonSiswa->tahunPelajaran?->nama ?? now()->format('Y')))[0];
        $gelombang = $calonSiswa->gelombangPendaftaran?->nama ?? 'PPDB';

        return trim("PPDB {$tahun} {$gelombang}");
    }

    private function resolveUserProfile(CalonSiswa $calonSiswa, PpdbSettings $settings, ?MoodleSyncMapping $mapping): array
    {
        $username = $this->buildUsername($calonSiswa);
        $lastnameTemplate = $mapping?->moodle_lastname_template ?: $settings->moodle_lastname_template;
        $lastname = $this->applyTemplate(
            filled($lastnameTemplate) ? $lastnameTemplate : $this->buildLastName($calonSiswa),
            $calonSiswa
        );

        $passwordMode = $mapping?->moodle_password_mode ?: $settings->moodle_password_mode ?: self::PASSWORD_ACCOUNT;
        $password = match ($passwordMode) {
            self::PASSWORD_CUSTOM => $this->applyTemplate(
                (string) ($mapping?->moodle_password_custom ?: $settings->moodle_password_custom ?: 'madrasah'),
                $calonSiswa
            ),
            default => $calonSiswa->user?->readable_password ?: Str::password(10),
        };

        $emailMode = $mapping?->moodle_email_mode ?: $settings->moodle_email_mode ?: self::EMAIL_ACCOUNT;
        $emailDomain = trim((string) ($mapping?->moodle_email_domain ?: $settings->moodle_email_domain ?: '@man1metro.sch.id'));
        if ($emailDomain !== '' && !str_starts_with($emailDomain, '@')) {
            $emailDomain = '@' . $emailDomain;
        }

        $email = match ($emailMode) {
            self::EMAIL_DOMAIN => $username . $emailDomain,
            default => $calonSiswa->user?->email ?: ($calonSiswa->email ?: ($username . '@man1metro.sch.id')),
        };

        return [
            'username' => $username,
            'lastname' => $lastname,
            'password' => $password,
            'email' => $email,
        ];
    }

    private function applyTemplate(string $template, CalonSiswa $calonSiswa): string
    {
        $tahun = explode('/', (string) ($calonSiswa->tahunPelajaran?->nama ?? now()->format('Y')))[0];
        $replacements = [
            '{NISN}' => (string) $calonSiswa->nisn,
            '{NAMA}' => (string) $calonSiswa->nama_lengkap,
            '{TAHUN}' => $tahun,
            '{TAHUN_AJARAN}' => (string) ($calonSiswa->tahunPelajaran?->nama ?? ''),
            '{JALUR}' => (string) ($calonSiswa->jalurPendaftaran?->kode ?? $calonSiswa->jalurPendaftaran?->nama ?? ''),
            '{JALUR_NAMA}' => (string) ($calonSiswa->jalurPendaftaran?->nama ?? ''),
            '{GELOMBANG}' => (string) ($calonSiswa->gelombangPendaftaran?->nama ?? ''),
            '{NOREG}' => (string) ($calonSiswa->nomor_registrasi ?? ''),
        ];

        return trim(str_replace(array_keys($replacements), array_values($replacements), $template));
    }

    public function resolveMapping(CalonSiswa $calonSiswa): ?MoodleSyncMapping
    {
        return MoodleSyncMapping::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('is_active', true)
            ->where(function ($query) use ($calonSiswa) {
                $query->where(function ($q) use ($calonSiswa) {
                    $q->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                        ->where('jalur_pendaftaran_id', $calonSiswa->jalur_pendaftaran_id)
                        ->where('gelombang_pendaftaran_id', $calonSiswa->gelombang_pendaftaran_id);
                })->orWhere(function ($q) use ($calonSiswa) {
                    $q->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                        ->where('jalur_pendaftaran_id', $calonSiswa->jalur_pendaftaran_id)
                        ->whereNull('gelombang_pendaftaran_id');
                })->orWhere(function ($q) use ($calonSiswa) {
                    $q->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                        ->whereNull('jalur_pendaftaran_id')
                        ->whereNull('gelombang_pendaftaran_id');
                })->orWhere(function ($q) {
                    $q->whereNull('tahun_pelajaran_id')
                        ->whereNull('jalur_pendaftaran_id')
                        ->whereNull('gelombang_pendaftaran_id');
                });
            })
            ->orderByRaw('CASE WHEN gelombang_pendaftaran_id IS NOT NULL THEN 1 WHEN jalur_pendaftaran_id IS NOT NULL THEN 2 WHEN tahun_pelajaran_id IS NOT NULL THEN 3 ELSE 4 END')
            ->first();
    }

    private function findUserByUsername(PpdbSettings $settings, string $username): ?array
    {
        $response = $this->call($settings, 'core_user_get_users_by_field', [
            'field' => 'username',
            'values' => [$username],
        ]);

        return is_array($response) && !empty($response[0]) ? $response[0] : null;
    }

    private function createUser(PpdbSettings $settings, array $user): array
    {
        $response = $this->call($settings, 'core_user_create_users', [
            'users' => [[
                'username' => $user['username'],
                'password' => $user['password'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email'],
                'auth' => 'manual',
            ]],
        ]);

        if (!is_array($response) || empty($response[0])) {
            throw new RuntimeException('Respons create user Moodle tidak valid.');
        }

        return $response[0];
    }

    private function addUserToCohort(PpdbSettings $settings, int $userId, int $cohortId): void
    {
        $this->call($settings, 'core_cohort_add_cohort_members', [
            'members' => [[
                'cohorttype' => ['type' => 'id', 'value' => $cohortId],
                'usertype' => ['type' => 'id', 'value' => $userId],
            ]],
        ]);
    }

    private function enrolUserToCourse(PpdbSettings $settings, int $userId, int $courseId, int $roleId): void
    {
        $this->call($settings, 'enrol_manual_enrol_users', [
            'enrolments' => [[
                'roleid' => $roleId,
                'userid' => $userId,
                'courseid' => $courseId,
            ]],
        ]);
    }

    private function call(PpdbSettings $settings, string $function, array $params = []): array
    {
        $baseUrl = rtrim((string) $settings->moodle_base_url, '/');
        $url = $baseUrl . '/webservice/rest/server.php';

        $payload = array_merge([
            'wstoken' => $settings->moodle_webservice_token,
            'wsfunction' => $function,
            'moodlewsrestformat' => 'json',
        ], $params);

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBody(http_build_query($payload), 'application/x-www-form-urlencoded')
            ->timeout(20)
            ->post($url);

        if (!$response->successful()) {
            throw new RuntimeException('HTTP Moodle gagal: ' . $response->status());
        }

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new RuntimeException('Respons Moodle tidak valid.');
        }

        if (isset($decoded['exception'])) {
            throw new RuntimeException($decoded['message'] ?? $decoded['errorcode'] ?? 'Moodle API error.');
        }

        return $decoded;
    }

    private function syncCandidateViaBridge(
        PpdbSettings $settings,
        CalonSiswa $calonSiswa,
        ?MoodleSyncMapping $mapping,
        array $user
    ): array {
        $courseIds = [];
        if ($settings->moodle_enrol_default_course) {
            $courseIds = $mapping?->moodle_course_ids ?: ($settings->moodle_default_course_ids ?? []);
            if (empty($courseIds) && filled($settings->moodle_default_course_id)) {
                $courseIds = [(int) $settings->moodle_default_course_id];
            }
        }

        $response = $this->bridgeCall($settings, 'sync-user', [
            'user' => [
                'username' => $user['username'],
                'password' => $user['password'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email'],
            ],
            'cohort_id' => $settings->moodle_assign_default_cohort
                ? ($mapping?->moodle_cohort_id ?: $settings->moodle_default_cohort_id)
                : null,
            'course_ids' => collect($courseIds)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'role_id' => (int) ($settings->moodle_course_role_id ?: 5),
            'context' => [
                'calon_siswa_id' => (string) $calonSiswa->id,
                'tahun' => $calonSiswa->tahunPelajaran?->nama,
                'jalur' => $calonSiswa->jalurPendaftaran?->nama,
                'gelombang' => $calonSiswa->gelombangPendaftaran?->nama,
            ],
        ]);

        return [
            'moodle_user_id' => data_get($response, 'user.id'),
            'moodle_username' => data_get($response, 'user.username', $user['username']),
            'existing' => (bool) data_get($response, 'user.existing', false),
        ];
    }

    private function bridgeCall(PpdbSettings $settings, string $action, array $payload = []): array
    {
        $baseUrl = rtrim((string) $settings->moodle_base_url, '/');
        $url = $baseUrl . '/converter/ppdb/api.php';

        $response = Http::acceptJson()
            ->timeout(20)
            ->post($url, array_merge([
                'token' => $settings->moodle_webservice_token,
                'action' => $action,
            ], $payload));

        if (!$response->successful()) {
            throw new RuntimeException('HTTP Bridge Moodle gagal: ' . $response->status());
        }

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new RuntimeException('Respons bridge Moodle tidak valid.');
        }

        if (!(bool) ($decoded['success'] ?? false)) {
            throw new RuntimeException($decoded['message'] ?? $decoded['error'] ?? 'Bridge Moodle error.');
        }

        return $decoded;
    }

    private function isBridgeMode(PpdbSettings $settings): bool
    {
        return ($settings->moodle_connection_mode ?? PpdbSettings::MOODLE_CONNECTION_WEBSERVICE) === self::CONNECTION_BRIDGE;
    }

    private function markSyncFailed(CalonSiswa $calonSiswa, PpdbSettings $settings, string $message): void
    {
        $calonSiswa->forceFill([
            'moodle_username' => $this->buildUsername($calonSiswa),
            'moodle_sync_status' => 'error',
            'moodle_sync_error' => $message,
        ])->save();

        $settings->forceFill([
            'moodle_sync_last_error' => $message,
        ])->save();
    }
}
