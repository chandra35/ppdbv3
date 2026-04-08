<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\NilaiCbtImport;
use App\Models\CalonSiswa;
use App\Models\MoodleSyncMapping;
use App\Models\NilaiCbt;
use App\Models\PpdbSettings;
use App\Models\TahunPelajaran;
use App\Services\MoodleIntegrationService;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NilaiCbtController extends Controller
{
    public function __construct(
        private readonly MoodleIntegrationService $moodleIntegrationService
    ) {
    }

    /**
     * Index - rekap data CBT
     */
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $selectedTahunId = $tahunAktif?->id;

        $query = NilaiCbt::with('calonSiswa')
            ->where('tahun_pelajaran_id', $selectedTahunId);

        if ($context['jalurFilterId']) {
            $query->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }

        if ($context['gelombangFilterId']) {
            $query->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
        }

        $data = $query->orderBy('rata_rata', 'desc')->get();

        // Hitung progress per mapel
        $komponenList = NilaiCbt::komponenList();
        $totalPeserta = $data->count();
        $mapelProgress = [];
        foreach ($komponenList as $field => $label) {
            $filled = $data->whereNotNull($field)->count();
            $mapelProgress[$field] = [
                'label' => $label,
                'filled' => $filled,
                'total' => $totalPeserta,
                'percent' => $totalPeserta > 0 ? round($filled / $totalPeserta * 100) : 0,
            ];
        }

        return view('admin.nilai-cbt.index', compact(
            'data',
            'tahunAktif',
            'selectedTahunId',
            'mapelProgress'
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    public function moodleScan(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $tahunAktif = $context['selectedTahun'];
        $settings = PpdbSettings::getActive();

        if (!$tahunAktif) {
            return redirect()->route('admin.nilai-cbt.index')
                ->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        if (!$settings->moodle_sync_enabled || !$this->moodleIntegrationService->isConfigured($settings)) {
            return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $context['selectedTahunIdInput'],
                'jalur_id' => $context['selectedJalurIdInput'] ?? 'all',
                'gelombang_id' => $context['selectedGelombangIdInput'] ?? 'all',
            ])->with('error', 'Integrasi Moodle belum aktif atau belum lengkap. Periksa /admin/settings/moodle terlebih dahulu.');
        }

        $candidates = CalonSiswa::query()
            ->with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran', 'user'])
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->when($context['jalurFilterId'], fn ($query, $jalurId) => $query->where('jalur_pendaftaran_id', $jalurId))
            ->when($context['gelombangFilterId'], fn ($query, $gelombangId) => $query->where('gelombang_pendaftaran_id', $gelombangId))
            ->orderBy('nama_lengkap')
            ->get();

        if ($candidates->isEmpty()) {
            return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $context['selectedTahunIdInput'],
                'jalur_id' => $context['selectedJalurIdInput'] ?? 'all',
                'gelombang_id' => $context['selectedGelombangIdInput'] ?? 'all',
            ])->with('warning', 'Tidak ada pendaftar pada filter yang dipilih.');
        }

        $mapping = $this->resolveMoodleMappingForContext($tahunAktif?->id, $context['jalurFilterId'], $context['gelombangFilterId']);
        $courseIds = collect($mapping?->moodle_course_ids ?: ($settings->moodle_default_course_ids ?? []))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->values()
            ->all();
        $categoryId = (string) ($mapping?->moodle_category_id ?: $settings->moodle_default_category_id ?: '');

        if (empty($courseIds) && blank($categoryId)) {
            return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $context['selectedTahunIdInput'],
                'jalur_id' => $context['selectedJalurIdInput'] ?? 'all',
                'gelombang_id' => $context['selectedGelombangIdInput'] ?? 'all',
            ])->with('error', 'Mapping course/category Moodle untuk filter ini belum diatur.');
        }

        $quizzes = $this->moodleIntegrationService->listQuizzes($settings, $categoryId ?: null, $courseIds);
        $usernames = $candidates->map(fn (CalonSiswa $candidate) => $this->moodleIntegrationService->usernameForCandidate($candidate))->all();
        $statuses = $this->moodleIntegrationService->findExistingUsersByUsernames($usernames, $settings);
        $gradeRows = $this->moodleIntegrationService->fetchQuizGrades($settings, $usernames, $categoryId ?: null, $courseIds);
        $existingNilai = NilaiCbt::where('tahun_pelajaran_id', $tahunAktif->id)
            ->whereIn('calon_siswa_id', $candidates->pluck('id'))
            ->get()
            ->keyBy('calon_siswa_id');

        $quizMap = collect($quizzes)->keyBy('id');
        $gradesByUsername = collect($gradeRows)->groupBy('username');
        $rows = [];
        $summary = [
            'total_candidates' => $candidates->count(),
            'matched_users' => 0,
            'with_quiz_data' => 0,
            'ready_to_save' => 0,
            'existing_records' => 0,
        ];

        foreach ($candidates as $candidate) {
            $username = $this->moodleIntegrationService->usernameForCandidate($candidate);
            $moodleUser = $statuses[$username] ?? null;
            $candidateGrades = collect($gradesByUsername->get($username, []));
            $groupedByCourse = $candidateGrades->groupBy('courseid');
            $existing = $existingNilai->get($candidate->id);

            $derived = [
                'nilai_mtk' => null,
                'nilai_ipa' => null,
                'nilai_ips' => null,
                'nilai_bahasa_inggris' => null,
            ];

            $courseSummaries = [];
            foreach ($groupedByCourse as $courseId => $courseGrades) {
                $first = $courseGrades->first();
                $courseName = (string) ($first['course_fullname'] ?? ($quizMap->get((string) ($first['quizid'] ?? ''))['course_fullname'] ?? ('Course ' . $courseId)));
                $courseLabel = trim($courseName) !== '' ? $courseName : ('Course ' . $courseId);
                $mappedField = $this->mapCourseNameToNilaiField($courseLabel);
                $percentScores = $courseGrades->pluck('score_percent')->filter(fn ($value) => $value !== null)->values();
                $average = $percentScores->isNotEmpty()
                    ? round($percentScores->avg(), 2)
                    : null;

                if ($mappedField && $average !== null) {
                    $derived[$mappedField] = $average;
                }

                $courseSummaries[] = [
                    'course_id' => (string) $courseId,
                    'course_name' => $courseLabel,
                    'mapped_field' => $mappedField,
                    'average' => $average,
                    'quizzes' => $courseGrades->map(function ($grade) {
                        return [
                            'quizid' => (string) $grade['quizid'],
                            'quiz_name' => $grade['quiz_name'],
                            'state' => $grade['state'],
                            'score_percent' => $grade['score_percent'],
                            'raw_grade' => $grade['raw_grade'],
                            'max_grade' => $grade['max_grade'],
                            'attempt' => $grade['attempt'],
                        ];
                    })->values()->all(),
                ];
            }

            $readyToSave = collect($derived)->filter(fn ($value) => $value !== null)->isNotEmpty();
            $total = collect($derived)->filter(fn ($value) => $value !== null)->sum();
            $count = collect($derived)->filter(fn ($value) => $value !== null)->count();

            $rows[] = [
                'candidate_id' => (string) $candidate->id,
                'nama_lengkap' => $candidate->nama_lengkap,
                'nisn' => $candidate->nisn,
                'nomor_registrasi' => $candidate->nomor_registrasi,
                'nomor_tes' => $candidate->nomor_tes,
                'moodle_username' => $username,
                'moodle_user_id' => $moodleUser['id'] ?? $candidate->moodle_user_id,
                'moodle_exists' => (bool) $moodleUser,
                'status' => $readyToSave ? 'ready' : ($candidateGrades->isNotEmpty() ? 'partial' : 'empty'),
                'existing' => $existing ? [
                    'nilai_mtk' => $existing->nilai_mtk,
                    'nilai_ipa' => $existing->nilai_ipa,
                    'nilai_ips' => $existing->nilai_ips,
                    'nilai_bahasa_inggris' => $existing->nilai_bahasa_inggris,
                    'rata_rata' => $existing->rata_rata,
                ] : null,
                'derived' => $derived + [
                    'total_nilai' => $count > 0 ? round($total, 2) : null,
                    'rata_rata' => $count > 0 ? round($total / $count, 2) : null,
                ],
                'course_summaries' => $courseSummaries,
                'quiz_count' => $candidateGrades->count(),
                'issue' => $this->resolveMoodleCbtIssue((bool) $moodleUser, $candidateGrades->count(), $readyToSave),
            ];

            if ($moodleUser) {
                $summary['matched_users']++;
            }
            if ($candidateGrades->isNotEmpty()) {
                $summary['with_quiz_data']++;
            }
            if ($readyToSave) {
                $summary['ready_to_save']++;
            }
            if ($existing) {
                $summary['existing_records']++;
            }
        }

        $token = Str::random(40);
        Cache::put('moodle_cbt_preview_' . $token, [
            'tahun_pelajaran_id' => (string) $tahunAktif->id,
            'rows' => $rows,
            'generated_at' => now()->toDateTimeString(),
        ], now()->addMinutes(30));

        return view('admin.nilai-cbt.preview-moodle', [
            'token' => $token,
            'rows' => $rows,
            'summary' => $summary,
            'quizzes' => $quizzes,
            'mapping' => $mapping,
            'tahunAktif' => $tahunAktif,
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
            'returnContext' => [
                'tahun_pelajaran_id' => $context['selectedTahunIdInput'],
                'jalur_id' => $context['selectedJalurIdInput'],
                'gelombang_id' => $context['selectedGelombangIdInput'],
            ],
        ]);
    }

    public function confirmMoodleScan(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'candidate_ids' => 'nullable|array',
            'candidate_ids.*' => 'required|string',
            'overwrite_existing' => 'nullable|boolean',
            'tahun_pelajaran_id' => 'required|string',
            'jalur_id' => 'nullable',
            'gelombang_id' => 'nullable',
        ]);

        $payload = Cache::get('moodle_cbt_preview_' . $validated['token']);
        if (!$payload || ($payload['tahun_pelajaran_id'] ?? null) !== $validated['tahun_pelajaran_id']) {
            return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])->with('error', 'Preview Moodle sudah kadaluarsa. Silakan scan ulang.');
        }

        $selectedIds = collect($request->input('candidate_ids', []))
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();
        $overwrite = $request->boolean('overwrite_existing');
        $rows = collect($payload['rows'] ?? []);
        $targets = empty($selectedIds)
            ? $rows->filter(fn ($row) => ($row['status'] ?? '') === 'ready')
            : $rows->whereIn('candidate_id', $selectedIds);

        $userId = Auth::id();
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($targets as $row) {
            $derived = collect($row['derived'] ?? [])
                ->only(['nilai_mtk', 'nilai_ipa', 'nilai_ips', 'nilai_bahasa_inggris'])
                ->filter(fn ($value) => $value !== null)
                ->all();

            if (empty($derived)) {
                $skipped++;
                continue;
            }

            $nilaiCbt = NilaiCbt::firstOrNew([
                'calon_siswa_id' => $row['candidate_id'],
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
            ]);

            $exists = $nilaiCbt->exists;
            if ($exists && !$overwrite) {
                $hasExistingScores = collect(NilaiCbt::komponenList())
                    ->keys()
                    ->contains(fn ($field) => $nilaiCbt->{$field} !== null);

                if ($hasExistingScores) {
                    $skipped++;
                    continue;
                }
            }

            foreach (NilaiCbt::komponenList() as $field => $label) {
                if (array_key_exists($field, $derived)) {
                    $nilaiCbt->{$field} = $derived[$field];
                }
            }

            $nilaiCbt->uploaded_by = $userId;
            $nilaiCbt->calculateTotal();
            $nilaiCbt->save();

            if ($exists) {
                $updated++;
            } else {
                $imported++;
            }
        }

        Cache::forget('moodle_cbt_preview_' . $validated['token']);

        return redirect()->route('admin.nilai-cbt.index', [
            'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
            'jalur_id' => $request->input('jalur_id'),
            'gelombang_id' => $request->input('gelombang_id'),
        ])->with('success', "Import Moodle selesai. {$imported} baru, {$updated} diupdate, {$skipped} dilewati.");
    }

    /**
     * Upload form - pilih mapel dulu
     */
    public function upload(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $komponenList = NilaiCbt::komponenList();
        $selectedMapel = $request->mapel;

        return view('admin.nilai-cbt.upload', compact('tahunAktif', 'komponenList', 'selectedMapel') + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Process upload - preview mode
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'jalur_id' => 'nullable',
            'gelombang_id' => 'nullable',
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'mapel' => 'required|in:nilai_mtk,nilai_ipa,nilai_ips,nilai_bahasa_inggris',
        ]);

        $context = AdminPpdbContext::resolve(
            $request->input('tahun_pelajaran_id'),
            $request->input('jalur_id'),
            $request->input('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $mapel = $request->mapel;
        $komponenList = NilaiCbt::komponenList();
        $mapelLabel = $komponenList[$mapel] ?? $mapel;

        $file = $request->file('file');
        $token = Str::random(32);
        $tempDir = storage_path('app/temp');
        $tempName = "cbt_{$token}." . $file->getClientOriginalExtension();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $file->move($tempDir, $tempName);
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempName;

        $importer = new NilaiCbtImport($tahunAktif->id, $mapel);
        $preview = $importer->preview($tempPath);

        return view('admin.nilai-cbt.preview-upload', [
            'preview' => $preview,
            'token' => $token,
            'extension' => $file->getClientOriginalExtension(),
            'originalName' => $file->getClientOriginalName(),
            'mapel' => $mapel,
            'mapelLabel' => $mapelLabel,
            'returnContext' => [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ],
        ]);
    }

    /**
     * Confirm upload - actually import
     */
    public function confirmUpload(Request $request)
    {
        $token = $request->input('token');
        $ext = $request->input('extension', 'xlsx');
        $mapel = $request->input('mapel');
        $tempPath = storage_path("app/temp/cbt_{$token}.{$ext}");

        if (!file_exists($tempPath)) {
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'File temporary tidak ditemukan. Silakan upload ulang.');
        }

        if (!$mapel || !in_array($mapel, array_keys(NilaiCbt::komponenList()))) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'Mapel tidak valid.');
        }

        $context = AdminPpdbContext::resolve(
            $request->input('tahun_pelajaran_id'),
            $request->input('jalur_id'),
            $request->input('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $komponenList = NilaiCbt::komponenList();
        $mapelLabel = $komponenList[$mapel] ?? $mapel;

        $importer = new NilaiCbtImport($tahunAktif->id, $mapel);
        $result = $importer->import($tempPath);

        @unlink($tempPath);

        $message = "Import {$mapelLabel} berhasil! {$result['imported']} baru, {$result['updated']} diupdate.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} dilewati.";
        }

        return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])
            ->with('success', $message);
    }

    /**
     * Cancel upload
     */
    public function cancelUpload(Request $request)
    {
        $token = $request->input('token');
        $ext = $request->input('extension', 'xlsx');
        $tempPath = storage_path("app/temp/cbt_{$token}.{$ext}");

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return redirect()->route('admin.nilai-cbt.upload', [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])
            ->with('info', 'Upload dibatalkan.');
    }

    /**
     * Delete single record
     */
    public function destroy(NilaiCbt $nilaiCbt)
    {
        $nilaiCbt->delete();
        return back()->with('success', 'Data nilai CBT berhasil dihapus.');
    }

    private function resolveMoodleMappingForContext(?string $tahunPelajaranId, ?string $jalurId, ?string $gelombangId): ?MoodleSyncMapping
    {
        return MoodleSyncMapping::query()
            ->where('is_active', true)
            ->where(function ($query) use ($tahunPelajaranId, $jalurId, $gelombangId) {
                $query->where(function ($q) use ($tahunPelajaranId, $jalurId, $gelombangId) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->where('jalur_pendaftaran_id', $jalurId)
                        ->where('gelombang_pendaftaran_id', $gelombangId);
                })->orWhere(function ($q) use ($tahunPelajaranId, $jalurId) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId)
                        ->where('jalur_pendaftaran_id', $jalurId)
                        ->whereNull('gelombang_pendaftaran_id');
                })->orWhere(function ($q) use ($tahunPelajaranId) {
                    $q->where('tahun_pelajaran_id', $tahunPelajaranId)
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

    private function mapCourseNameToNilaiField(string $courseName): ?string
    {
        $normalized = mb_strtoupper($courseName);

        return match (true) {
            str_contains($normalized, 'MTK'),
            str_contains($normalized, 'MATEMATIKA') => 'nilai_mtk',
            str_contains($normalized, 'IPA') => 'nilai_ipa',
            str_contains($normalized, 'IPS') => 'nilai_ips',
            str_contains($normalized, 'INGGRIS'),
            str_contains($normalized, 'BHS INGGRIS'),
            str_contains($normalized, 'BAHASA INGGRIS') => 'nilai_bahasa_inggris',
            default => null,
        };
    }

    private function resolveMoodleCbtIssue(bool $moodleExists, int $quizCount, bool $readyToSave): string
    {
        if (!$moodleExists) {
            return 'User Moodle belum ditemukan untuk username ini.';
        }

        if ($quizCount === 0) {
            return 'Belum ada attempt/nilai quiz Moodle yang cocok dengan mapping filter ini.';
        }

        if (!$readyToSave) {
            return 'Data quiz ditemukan, tetapi belum terpetakan ke komponen Nilai CBT.';
        }

        return 'Siap dipreview dan disimpan ke Nilai CBT.';
    }
}
