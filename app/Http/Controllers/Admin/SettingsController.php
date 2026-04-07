<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\MoodleSyncMapping;
use App\Models\PpdbSettings;
use App\Support\AdminPpdbContext;
use App\Models\ActivityLog;
use App\Services\DocumentStorageService;
use App\Services\GoogleDriveService;
use App\Services\MoodleIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $documentStorageService,
        private readonly GoogleDriveService $googleDriveService,
        private readonly MoodleIntegrationService $moodleIntegrationService
    ) {
    }

    /**
     * Check permission - admin selalu bisa akses
     */
    private function checkPermission(string $permission): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        if ($user->isAdmin()) {
            return;
        }
        
        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }
    }

    public function index()
    {
        $settings = $this->getOrCreateSettings();

        return view('admin.settings.index', compact('settings'));
    }

    public function storage()
    {
        $settings = $this->getOrCreateSettings();
        $rawSettings = DB::table('ppdb_settings')->where('id', $settings->id)->first();

        if ($rawSettings) {
            $settings->dokumen_storage_mode = $rawSettings->dokumen_storage_mode ?? $settings->dokumen_storage_mode;
            $settings->google_drive_auth_mode = $rawSettings->google_drive_auth_mode ?? $settings->google_drive_auth_mode;
            $settings->google_drive_root_folder_id = $rawSettings->google_drive_root_folder_id ?? $settings->google_drive_root_folder_id;
            $settings->google_drive_credentials_path = $rawSettings->google_drive_credentials_path ?? $settings->google_drive_credentials_path;
            $settings->google_drive_make_public = (bool) ($rawSettings->google_drive_make_public ?? $settings->google_drive_make_public);
            $settings->google_drive_oauth_client_id = $rawSettings->google_drive_oauth_client_id ?? $settings->google_drive_oauth_client_id;
            $settings->google_drive_oauth_client_secret = $rawSettings->google_drive_oauth_client_secret ?? $settings->google_drive_oauth_client_secret;
            $settings->google_drive_oauth_refresh_token = $rawSettings->google_drive_oauth_refresh_token ?? $settings->google_drive_oauth_refresh_token;
            $settings->google_drive_oauth_email = $rawSettings->google_drive_oauth_email ?? $settings->google_drive_oauth_email;
        }

        [$googleDriveClientEmail, $googleDriveStatus, $googleDriveStatusMessage] = $this->getGoogleDriveStatusData($settings);

        return view('admin.settings.storage-dokumen', compact(
            'settings',
            'googleDriveClientEmail',
            'googleDriveStatus',
            'googleDriveStatusMessage'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'validasi_nisn_aktif' => 'nullable|boolean',
            'wajib_lokasi_registrasi' => 'nullable|boolean',
            'cegah_pendaftar_ganda' => 'nullable|boolean',
            'izinkan_dokumen_tambahan' => 'nullable|boolean',
            'dokumen_aktif' => 'nullable|array',
            'nomor_registrasi_prefix' => 'required|string|max:20',
            'nomor_tes_prefix' => 'required|string|max:10',
            'nomor_tes_digit' => 'required|integer|in:3,4,5',
        ]);

        // Convert checkbox values
        $validated['validasi_nisn_aktif'] = $request->has('validasi_nisn_aktif');
        $validated['wajib_lokasi_registrasi'] = $request->has('wajib_lokasi_registrasi');
        $validated['cegah_pendaftar_ganda'] = $request->has('cegah_pendaftar_ganda');
        $validated['izinkan_dokumen_tambahan'] = $request->has('izinkan_dokumen_tambahan');
        $validated['google_drive_make_public'] = $request->has('google_drive_make_public');

        $settings = PpdbSettings::first();
        
        if (!$settings) {
            $validated['tahun_pelajaran_id'] = (string) Str::uuid();
            if ($request->hasFile('google_drive_credentials_file')) {
                $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request);
            }

            $settings = PpdbSettings::create($validated);
            ActivityLog::log('create', "Membuat pengaturan PPDB", $settings);
        } else {
            $oldValues = $settings->toArray();
            if ($request->hasFile('google_drive_credentials_file')) {
                $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request, $settings->google_drive_credentials_path);
            }
            $settings->update($validated);
            ActivityLog::log('update', "Mengupdate pengaturan PPDB", $settings, $oldValues, $settings->fresh()->toArray());
        }

        return redirect()->back()->with('success', 'Pengaturan PPDB berhasil diupdate.');
    }

    public function moodle()
    {
        $settings = $this->getOrCreateSettings();
        [$moodleStatus, $moodleStatusMessage] = $this->moodleIntegrationService->getStatusData($settings);
        $context = AdminPpdbContext::resolve(
            request('tahun_pelajaran_id'),
            request('jalur_id'),
            request('gelombang_id')
        );
        $mappings = MoodleSyncMapping::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();

        $moodleCategories = [];
        $moodleCategoryOptions = [];
        $moodleCoursesByCategory = [];
        $moodleCohorts = [];
        $syncCandidates = $this->buildMoodleSyncCandidateQuery($context)->paginate(50)->withQueryString();

        if ($settings->moodle_sync_enabled && $this->moodleIntegrationService->isConfigured($settings)) {
            try {
                $moodleCategories = $this->moodleIntegrationService->listCategories($settings);
                $moodleCategoryOptions = $this->buildCategoryOptions($moodleCategories);
                $moodleCohorts = $this->moodleIntegrationService->listCohorts($settings);
                $categoryIds = collect($moodleCategories)
                    ->pluck('id')
                    ->filter()
                    ->unique()
                    ->values();

                foreach ($categoryIds as $categoryId) {
                    $moodleCoursesByCategory[(string) $categoryId] = $this->moodleIntegrationService->listCoursesByCategory($settings, (string) $categoryId);
                }
            } catch (\Throwable $e) {
                $moodleStatus = 'warning';
                $moodleStatusMessage = 'Integrasi Moodle aktif, tetapi daftar category/course belum berhasil dimuat. ' . $e->getMessage();
            }

            try {
                $refreshedStatuses = $this->moodleIntegrationService->refreshCandidatesSyncState(
                    $syncCandidates->getCollection(),
                    $settings
                );
                $syncCandidates->setCollection($syncCandidates->getCollection()->fresh());
            } catch (\Throwable $e) {
                if ($moodleStatus !== 'warning') {
                    $moodleStatus = 'warning';
                    $moodleStatusMessage = 'Integrasi Moodle aktif, tetapi cek status sinkron belum berhasil. ' . $e->getMessage();
                }
                $refreshedStatuses = [];
            }
        } else {
            $refreshedStatuses = [];
        }

        $syncStatusSummary = [
            'synced' => $syncCandidates->getCollection()->where('moodle_sync_status', 'synced')->count(),
            'not_synced' => $syncCandidates->getCollection()->where('moodle_sync_status', 'not_synced')->count(),
            'error' => $syncCandidates->getCollection()->where('moodle_sync_status', 'error')->count(),
            'total' => $syncCandidates->count(),
        ];

        return view('admin.settings.moodle', compact(
            'settings',
            'moodleStatus',
            'moodleStatusMessage',
            'mappings',
            'moodleCategories',
            'moodleCategoryOptions',
            'moodleCoursesByCategory',
            'syncCandidates',
            'syncStatusSummary',
            'refreshedStatuses'
        ) + [
            'moodleCohorts' => $moodleCohorts,
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['allGelombangs'],
            'selectedTahunId' => $context['selectedTahunIdInput'],
            'selectedJalurId' => $context['selectedJalurIdInput'],
            'selectedGelombangId' => $context['selectedGelombangIdInput'],
        ]);
    }

    private function buildMoodleSyncCandidateQuery(array $context)
    {
        return CalonSiswa::query()
            ->with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran', 'user'])
            ->when($context['selectedTahun']?->id, fn ($query, $tahunId) => $query->where('tahun_pelajaran_id', $tahunId))
            ->when($context['jalurFilterId'], fn ($query, $jalurId) => $query->where('jalur_pendaftaran_id', $jalurId))
            ->when($context['gelombangFilterId'], fn ($query, $gelombangId) => $query->where('gelombang_pendaftaran_id', $gelombangId))
            ->orderByDesc('tanggal_registrasi')
            ->orderByDesc('created_at');
    }

    private function buildCategoryOptions(array $categories): array
    {
        $items = collect($categories)->keyBy('id');

        return collect($categories)->map(function ($category) use ($items) {
            $depth = max(0, ((int) ($category['depth'] ?? 1)) - 1);
            $indent = str_repeat('-- ', $depth);

            $parts = [(string) ($category['name'] ?? ('Category ' . $category['id']))];
            $parentId = (string) ($category['parent'] ?? '0');

            if ($parentId !== '0' && $items->has($parentId)) {
                $parts[] = 'Parent: ' . ($items->get($parentId)['name'] ?? $parentId);
            }

            return [
                'id' => (string) $category['id'],
                'label' => trim($indent . ($category['name'] ?? ('Category ' . $category['id']))),
                'meta' => implode(' | ', $parts),
            ];
        })->all();
    }

    public function updateMoodle(Request $request)
    {
        $validated = $request->validate([
            'moodle_connection_mode' => 'required|string|in:webservice,bridge',
            'moodle_sync_mode' => 'required|string|in:manual,on_register,on_finalisasi,on_nomor_tes',
            'moodle_base_url' => 'nullable|url|max:255',
            'moodle_webservice_token' => 'nullable|string',
            'moodle_default_cohort_id' => 'nullable|string|max:50',
            'moodle_default_course_id' => 'nullable|string|max:50',
            'moodle_default_category_id' => 'nullable|string|max:50',
            'moodle_default_course_ids' => 'nullable|array',
            'moodle_default_course_ids.*' => 'nullable|string|max:50',
            'moodle_lastname_template' => 'nullable|string|max:255',
            'moodle_password_mode' => 'required|string|in:account,custom',
            'moodle_password_custom' => 'nullable|string|max:255',
            'moodle_email_mode' => 'required|string|in:account,domain',
            'moodle_email_domain' => 'nullable|string|max:255',
            'moodle_course_role_id' => 'nullable|integer|min:1',
        ]);

        $validated['moodle_sync_enabled'] = $request->has('moodle_sync_enabled');
        $validated['moodle_assign_default_cohort'] = $request->has('moodle_assign_default_cohort');
        $validated['moodle_enrol_default_course'] = $request->has('moodle_enrol_default_course');
        $validated['moodle_base_url'] = filled($validated['moodle_base_url'] ?? null)
            ? rtrim((string) $validated['moodle_base_url'], '/')
            : null;
        $validated['moodle_lastname_template'] = filled($validated['moodle_lastname_template'] ?? null)
            ? trim((string) $validated['moodle_lastname_template'])
            : null;
        $validated['moodle_password_custom'] = filled($validated['moodle_password_custom'] ?? null)
            ? trim((string) $validated['moodle_password_custom'])
            : null;
        $validated['moodle_email_domain'] = filled($validated['moodle_email_domain'] ?? null)
            ? trim((string) $validated['moodle_email_domain'])
            : null;
        $validated['moodle_default_course_ids'] = collect($request->input('moodle_default_course_ids', []))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();

        $settings = $this->getOrCreateSettings();
        $oldValues = $settings->toArray();
        $settings->update($validated);

        ActivityLog::log('update', 'Mengupdate pengaturan integrasi Moodle', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.settings.moodle.index')->with('success', 'Pengaturan integrasi Moodle berhasil diupdate.');
    }

    public function storeMoodleMapping(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajarans,id',
            'jalur_pendaftaran_id' => 'nullable|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'nullable|exists:gelombang_pendaftaran,id',
            'moodle_cohort_id' => 'nullable|string|max:50',
            'moodle_category_id' => 'nullable|string|max:50',
            'moodle_course_ids' => 'nullable|array',
            'moodle_course_ids.*' => 'nullable|string|max:50',
            'moodle_lastname_template' => 'nullable|string|max:255',
            'moodle_password_mode' => 'nullable|string|in:account,custom',
            'moodle_password_custom' => 'nullable|string|max:255',
            'moodle_email_mode' => 'nullable|string|in:account,domain',
            'moodle_email_domain' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['moodle_lastname_template'] = filled($validated['moodle_lastname_template'] ?? null)
            ? trim((string) $validated['moodle_lastname_template'])
            : null;
        $validated['moodle_password_custom'] = filled($validated['moodle_password_custom'] ?? null)
            ? trim((string) $validated['moodle_password_custom'])
            : null;
        $validated['moodle_email_domain'] = filled($validated['moodle_email_domain'] ?? null)
            ? trim((string) $validated['moodle_email_domain'])
            : null;
        $validated['moodle_course_ids'] = collect($request->input('moodle_course_ids', []))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();

        $mapping = MoodleSyncMapping::create($validated);
        ActivityLog::log('create', 'Menambahkan mapping integrasi Moodle', $mapping);

        return redirect()->route('admin.settings.moodle.index')->with('success', 'Mapping Moodle berhasil ditambahkan.');
    }

    public function updateMoodleMapping(Request $request, MoodleSyncMapping $moodleMapping)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajarans,id',
            'jalur_pendaftaran_id' => 'nullable|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'nullable|exists:gelombang_pendaftaran,id',
            'moodle_cohort_id' => 'nullable|string|max:50',
            'moodle_category_id' => 'nullable|string|max:50',
            'moodle_course_ids' => 'nullable|array',
            'moodle_course_ids.*' => 'nullable|string|max:50',
            'moodle_lastname_template' => 'nullable|string|max:255',
            'moodle_password_mode' => 'nullable|string|in:account,custom',
            'moodle_password_custom' => 'nullable|string|max:255',
            'moodle_email_mode' => 'nullable|string|in:account,domain',
            'moodle_email_domain' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['moodle_lastname_template'] = filled($validated['moodle_lastname_template'] ?? null)
            ? trim((string) $validated['moodle_lastname_template'])
            : null;
        $validated['moodle_password_custom'] = filled($validated['moodle_password_custom'] ?? null)
            ? trim((string) $validated['moodle_password_custom'])
            : null;
        $validated['moodle_email_domain'] = filled($validated['moodle_email_domain'] ?? null)
            ? trim((string) $validated['moodle_email_domain'])
            : null;
        $validated['moodle_course_ids'] = collect($request->input('moodle_course_ids', []))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();

        $oldValues = $moodleMapping->toArray();
        $moodleMapping->update($validated);
        ActivityLog::log('update', 'Mengupdate mapping integrasi Moodle', $moodleMapping, $oldValues, $moodleMapping->fresh()->toArray());

        return redirect()->route('admin.settings.moodle.index')->with('success', 'Mapping Moodle berhasil diupdate.');
    }

    public function destroyMoodleMapping(MoodleSyncMapping $moodleMapping)
    {
        $oldValues = $moodleMapping->toArray();
        $moodleMapping->delete();
        ActivityLog::log('delete', 'Menghapus mapping integrasi Moodle', $moodleMapping, $oldValues);

        return redirect()->route('admin.settings.moodle.index')->with('success', 'Mapping Moodle berhasil dihapus.');
    }

    public function refreshMoodleCandidateStatuses(Request $request)
    {
        $settings = $this->getOrCreateSettings();
        $candidateIds = collect($request->input('candidate_ids', []))
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        $candidates = empty($candidateIds)
            ? $this->buildMoodleSyncCandidateQuery(AdminPpdbContext::resolve(
                $request->input('tahun_pelajaran_id'),
                $request->input('jalur_id'),
                $request->input('gelombang_id')
            ))->limit(200)->get()
            : CalonSiswa::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran', 'user'])
                ->whereIn('id', $candidateIds)
                ->get();

        $statuses = $this->moodleIntegrationService->refreshCandidatesSyncState($candidates, $settings);

        return response()->json([
            'success' => true,
            'message' => 'Status sinkron Moodle berhasil diperbarui.',
            'count' => count($statuses),
            'statuses' => $statuses,
        ]);
    }

    public function syncMoodleCandidate(CalonSiswa $calonSiswa)
    {
        $settings = $this->getOrCreateSettings();

        try {
            $result = $this->moodleIntegrationService->syncCandidate(
                $calonSiswa->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                $settings
            );

            ActivityLog::log('update', 'Sinkron manual pendaftar ke Moodle', $calonSiswa->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Sinkron Moodle berhasil untuk ' . $calonSiswa->nama_lengkap . '.',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateStorage(Request $request)
    {
        Log::info('Storage dokumen submit', [
            'dokumen_storage_mode' => $request->input('dokumen_storage_mode'),
            'google_drive_auth_mode' => $request->input('google_drive_auth_mode'),
            '_dokumen_storage_mode_selected' => $request->input('_dokumen_storage_mode_selected'),
            '_google_drive_auth_mode_selected' => $request->input('_google_drive_auth_mode_selected'),
            'has_oauth_client_id' => $request->filled('google_drive_oauth_client_id'),
            'has_oauth_client_secret' => $request->filled('google_drive_oauth_client_secret'),
            'has_credentials_file' => $request->hasFile('google_drive_credentials_file'),
        ]);

        if (!$request->filled('google_drive_auth_mode') && $request->filled('_google_drive_auth_mode_selected')) {
            $request->merge([
                'google_drive_auth_mode' => $request->input('_google_drive_auth_mode_selected'),
            ]);
        }

        if (!$request->filled('dokumen_storage_mode') && $request->filled('_dokumen_storage_mode_selected')) {
            $request->merge([
                'dokumen_storage_mode' => $request->input('_dokumen_storage_mode_selected'),
            ]);
        }

        $validated = $request->validate([
            'dokumen_storage_mode' => 'required|string|in:local,gdrive_primary_local_fallback',
            'google_drive_auth_mode' => 'required|string|in:service_account,oauth',
            'google_drive_root_folder_id' => 'nullable|string|max:255',
            'google_drive_make_public' => 'nullable|boolean',
            'google_drive_credentials_file' => 'nullable|file|mimes:json,txt|max:2048',
            'google_drive_oauth_client_id' => 'nullable|string|max:255',
            'google_drive_oauth_client_secret' => 'nullable|string',
        ]);

        $validated['google_drive_make_public'] = $request->has('google_drive_make_public');

        $settings = $this->getOrCreateSettings();
        $oldValues = $settings->toArray();

        if ($request->hasFile('google_drive_credentials_file')) {
            $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request, $settings->google_drive_credentials_path);
        }

        $settings->update($validated);

        ActivityLog::log('update', "Mengupdate pengaturan storage dokumen PPDB", $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.settings.storage.index')->with('success', 'Pengaturan storage dokumen berhasil diupdate.');
    }

    public function redirectGoogleDriveOauth()
    {
        $settings = PpdbSettings::firstOrFail();

        if (empty($settings->google_drive_oauth_client_id) || empty($settings->google_drive_oauth_client_secret)) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Simpan dulu Client ID dan Client Secret Google OAuth sebelum menghubungkan akun.');
        }

        $state = Str::random(40);
        session(['google_drive_oauth_state' => $state]);

        $url = $this->googleDriveService->buildOAuthAuthUrl(
            $settings->google_drive_oauth_client_id,
            route('admin.settings.storage.google-drive.oauth.callback'),
            $state
        );

        return redirect()->away($url);
    }

    public function callbackGoogleDriveOauth(Request $request)
    {
        $settings = PpdbSettings::firstOrFail();
        $expectedState = session('google_drive_oauth_state');
        session()->forget('google_drive_oauth_state');

        if (!$request->filled('state') || !$expectedState || $request->state !== $expectedState) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'State OAuth Google Drive tidak valid.');
        }

        if ($request->filled('error')) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Google OAuth dibatalkan: ' . $request->error);
        }

        if (!$request->filled('code')) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Kode OAuth Google Drive tidak ditemukan.');
        }

        try {
            $tokenData = $this->googleDriveService->exchangeOAuthCode(
                $settings->google_drive_oauth_client_id,
                $settings->google_drive_oauth_client_secret,
                route('admin.settings.storage.google-drive.oauth.callback'),
                $request->code
            );

            $refreshToken = $tokenData['refresh_token'] ?? $settings->google_drive_oauth_refresh_token;
            if (!$refreshToken) {
                return redirect()->route('admin.settings.storage.index')
                    ->with('error', 'Google tidak mengirim refresh token. Coba putuskan akses lama lalu connect ulang.');
            }

            $email = null;
            if (!empty($tokenData['access_token'])) {
                $email = $this->googleDriveService->fetchOAuthUserEmail($tokenData['access_token']);
            }

            $settings->update([
                'google_drive_auth_mode' => 'oauth',
                'google_drive_oauth_refresh_token' => $refreshToken,
                'google_drive_oauth_email' => $email,
            ]);

            return redirect()->route('admin.settings.storage.index')
                ->with('success', 'Google Drive berhasil terhubung dengan akun ' . ($email ?: 'Google Anda') . '.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Gagal menyambungkan Google Drive OAuth: ' . $e->getMessage());
        }
    }

    public function disconnectGoogleDriveOauth()
    {
        $settings = PpdbSettings::firstOrFail();

        $settings->update([
            'google_drive_oauth_refresh_token' => null,
            'google_drive_oauth_email' => null,
        ]);

        return redirect()->route('admin.settings.storage.index')
            ->with('success', 'Koneksi OAuth Google Drive berhasil diputus.');
    }

    private function getOrCreateSettings(): PpdbSettings
    {
        $settings = PpdbSettings::first();

        if ($settings) {
            return $settings;
        }

        $settings = new PpdbSettings();
        $settings->tahun_pelajaran_id = (string) Str::uuid();
        $settings->kuota_penerimaan = 200;
        $settings->tanggal_dibuka = now();
        $settings->tanggal_ditutup = now()->addMonths(3);
        $settings->status_pendaftaran = true;
        $settings->validasi_nisn_aktif = true;
        $settings->cegah_pendaftar_ganda = true;
        $settings->dokumen_aktif = ['kk', 'akta_lahir', 'ijazah', 'foto'];
        $settings->nomor_registrasi_prefix = 'PPDB';
        $settings->nomor_registrasi_counter = 0;
        $settings->dokumen_storage_mode = 'local';
        $settings->save();

        return $settings;
    }

    private function getGoogleDriveStatusData(PpdbSettings $settings): array
    {
        $googleDriveClientEmail = $this->documentStorageService->getGoogleDriveClientEmail($settings);
        $googleDriveStatus = 'local';
        $googleDriveStatusMessage = 'Dokumen saat ini disimpan ke storage lokal.';

        if ($settings->isGoogleDrivePrimaryEnabled()) {
            if ($settings->isGoogleDriveConfigured()) {
                try {
                    $folderInfo = $this->documentStorageService->testGoogleDriveConnection($settings);
                    $this->documentStorageService->testGoogleDriveWriteAccess($settings);
                    $googleDriveStatus = 'ready';
                    $googleDriveStatusMessage = 'Google Drive siap dipakai. Folder aktif: ' . ($folderInfo['name'] ?? $settings->google_drive_root_folder_id);
                } catch (\Throwable $e) {
                    $googleDriveStatus = 'warning';
                    $googleDriveStatusMessage = 'Mode Google Drive aktif, tetapi uji tulis belum berhasil. Upload akan fallback ke lokal. ' . $e->getMessage();
                }
            } else {
                $googleDriveStatus = 'incomplete';
                $googleDriveStatusMessage = 'Mode Google Drive aktif, tetapi credential JSON atau Folder ID belum lengkap.';
            }
        }

        return [$googleDriveClientEmail, $googleDriveStatus, $googleDriveStatusMessage];
    }

    private function storeGoogleDriveCredentialsFile(Request $request, ?string $oldPath = null): string
    {
        $file = $request->file('google_drive_credentials_file');
        $content = $file->get();
        $decoded = json_decode($content, true);

        if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'google_drive_credentials_file' => 'File JSON Google Drive tidak valid.',
            ]);
        }

        $filename = 'google-drive/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.json';
        Storage::disk('local')->put($filename, $content);

        if ($oldPath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        return $filename;
    }
}
