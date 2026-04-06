<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Services\NomorService;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalisasiController extends Controller
{
    public function __construct(private readonly NomorService $nomorService)
    {
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

    /**
     * Display list of pendaftar for finalization
     */
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $jalurList = $context['jalurs'];
        $gelombangList = $context['gelombangs'];

        // Build query
        $query = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])
            ->where('status_verifikasi', '!=', 'rejected'); // Tidak termasuk yang ditolak

        // Filter status finalisasi
        if ($request->status_finalisasi === 'sudah') {
            $query->where('is_finalisasi', true);
        } elseif ($request->status_finalisasi === 'semua') {
            // Tampilkan semua (tidak ada filter)
        } else {
            // Default: tampilkan yang belum difinalisasi
            $query->where('is_finalisasi', false);
        }

        if ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        // Filter jalur
        if ($context['jalurFilterId']) {
            $query->where('jalur_pendaftaran_id', $context['jalurFilterId']);
        }

        // Filter gelombang
        if ($context['gelombangFilterId']) {
            $query->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
        }

        // Filter kelengkapan
        if ($request->kelengkapan == 'lengkap') {
            $query->where('data_diri_completed', true)
                  ->where('data_ortu_completed', true)
                  ->where('data_dokumen_completed', true);
        } elseif ($request->kelengkapan == 'tidak_lengkap') {
            $query->where(function($q) {
                $q->where('data_diri_completed', false)
                  ->orWhere('data_ortu_completed', false)
                  ->orWhere('data_dokumen_completed', false);
            });
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%");
            });
        }

        $pendaftarList = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total_belum_final' => CalonSiswa::where('is_finalisasi', false)
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
            'siap_finalisasi' => CalonSiswa::where('is_finalisasi', false)
                ->where('data_diri_completed', true)
                ->where('data_ortu_completed', true)
                ->where('data_dokumen_completed', true)
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
            'belum_lengkap' => CalonSiswa::where('is_finalisasi', false)
                ->where(function($q) {
                    $q->where('data_diri_completed', false)
                      ->orWhere('data_ortu_completed', false)
                      ->orWhere('data_dokumen_completed', false);
                })
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
            'sudah_finalisasi' => CalonSiswa::where('is_finalisasi', true)
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
        ];

        return view('admin.finalisasi.index', compact(
            'pendaftarList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'stats'
        ) + [
            'tahunPelajaranList' => $context['tahunPelajarans'],
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
     * Finalize a pendaftar
     */
    public function finalisasi(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])->findOrFail($id);

        // Cek apakah sudah difinalisasi
        if ($pendaftar->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar sudah difinalisasi sebelumnya',
                'data' => [
                    'nomor_registrasi' => $pendaftar->nomor_registrasi,
                    'nomor_tes' => $pendaftar->nomor_tes,
                    'tanggal_finalisasi' => $pendaftar->tanggal_finalisasi?->format('d/m/Y H:i')
                ]
            ], 422);
        }

        // Validasi kelengkapan
        $errors = $this->validateKelengkapan($pendaftar);
        
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftar belum lengkap',
                'errors' => $errors
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate nomor registrasi jika belum ada
            if (!$pendaftar->nomor_registrasi) {
                $pendaftar->nomor_registrasi = $this->nomorService->generateNomorRegistrasi($pendaftar);
            }

            // Generate nomor tes
            if (!$pendaftar->nomor_tes) {
                $pendaftar->nomor_tes = $this->nomorService->generateNomorTes($pendaftar);
            }

            // Generate verification hash
            if (!$pendaftar->verification_hash) {
                $pendaftar->verification_hash = $pendaftar->generateVerificationHash();
            }

            // Update status finalisasi
            $pendaftar->is_finalisasi = true;
            $pendaftar->tanggal_finalisasi = now();
            // Keep status_verifikasi as 'verified' (don't change to 'final' as it's not a valid enum value)
            if ($pendaftar->status_verifikasi !== 'verified') {
                $pendaftar->status_verifikasi = 'verified';
            }
            $pendaftar->save();

            DB::commit();

            app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
                $pendaftar->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                \App\Services\MoodleIntegrationService::TRIGGER_FINALISASI
            );
            app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
                $pendaftar->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                \App\Services\MoodleIntegrationService::TRIGGER_NOMOR_TES
            );

            return response()->json([
                'success' => true,
                'message' => 'Pendaftar berhasil difinalisasi',
                'data' => [
                    'nomor_registrasi' => $pendaftar->nomor_registrasi,
                    'nomor_tes' => $pendaftar->nomor_tes
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memfinalisasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch finalize multiple pendaftar
     */
    public function batchFinalisasi(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id'
        ]);

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            $pendaftar = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])->find($id);
            
            if (!$pendaftar) {
                $failed++;
                continue;
            }

            $validationErrors = $this->validateKelengkapan($pendaftar);
            
            if (!empty($validationErrors)) {
                $failed++;
                $errors[] = [
                    'nama' => $pendaftar->nama_lengkap,
                    'errors' => $validationErrors
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                if (!$pendaftar->nomor_registrasi) {
                    $pendaftar->nomor_registrasi = $this->nomorService->generateNomorRegistrasi($pendaftar);
                }

                if (!$pendaftar->nomor_tes) {
                    $pendaftar->nomor_tes = $this->nomorService->generateNomorTes($pendaftar);
                }

                if (!$pendaftar->verification_hash) {
                    $pendaftar->verification_hash = $pendaftar->generateVerificationHash();
                }

                $pendaftar->is_finalisasi = true;
                $pendaftar->tanggal_finalisasi = now();
                // Keep status_verifikasi as 'verified' (don't change to 'final' as it's not a valid enum value)
                if ($pendaftar->status_verifikasi !== 'verified') {
                    $pendaftar->status_verifikasi = 'verified';
                }
                $pendaftar->save();

                DB::commit();

                app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
                    $pendaftar->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                    \App\Services\MoodleIntegrationService::TRIGGER_FINALISASI
                );
                app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
                    $pendaftar->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                    \App\Services\MoodleIntegrationService::TRIGGER_NOMOR_TES
                );
                $success++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $errors[] = [
                    'nama' => $pendaftar->nama_lengkap,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Finalisasi batch selesai. Berhasil: {$success}, Gagal: {$failed}",
            'data' => [
                'success' => $success,
                'failed' => $failed,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Check kelengkapan pendaftar
     */
    public function cekKelengkapan($id)
    {
        $pendaftar = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])->findOrFail($id);
        
        $errors = $this->validateKelengkapan($pendaftar);
        $isComplete = empty($errors);

        return response()->json([
            'success' => true,
            'is_complete' => $isComplete,
            'data' => [
                'data_diri' => $pendaftar->data_diri_completed,
                'data_ortu' => $pendaftar->data_ortu_completed,
                'data_dokumen' => $pendaftar->data_dokumen_completed,
            ],
            'errors' => $errors
        ]);
    }

    /**
     * Validate kelengkapan data
     */
    private function validateKelengkapan(CalonSiswa $pendaftar): array
    {
        $errors = [];

        // Cek data diri
        if (!$pendaftar->data_diri_completed) {
            $errors[] = 'Data diri belum lengkap';
        }

        // Cek data diri field penting
        if (empty($pendaftar->nik)) {
            $errors[] = 'NIK belum diisi';
        }
        if (empty($pendaftar->nama_lengkap)) {
            $errors[] = 'Nama lengkap belum diisi';
        }
        if (empty($pendaftar->tanggal_lahir)) {
            $errors[] = 'Tanggal lahir belum diisi';
        }
        if (empty($pendaftar->jenis_kelamin)) {
            $errors[] = 'Jenis kelamin belum diisi';
        }

        // Cek data orang tua
        if (!$pendaftar->data_ortu_completed) {
            $errors[] = 'Data orang tua belum lengkap';
        }

        // Cek relasi ortu
        if (!$pendaftar->ortu) {
            $errors[] = 'Data orang tua belum diisi';
        }

        // Cek dokumen
        if (!$pendaftar->data_dokumen_completed) {
            $errors[] = 'Dokumen belum lengkap';
        }

        // Cek jalur dan gelombang
        if (!$pendaftar->jalur_pendaftaran_id) {
            $errors[] = 'Jalur pendaftaran belum dipilih';
        }

        if (!$pendaftar->gelombang_pendaftaran_id) {
            $errors[] = 'Gelombang pendaftaran belum dipilih';
        }

        return $errors;
    }

}
