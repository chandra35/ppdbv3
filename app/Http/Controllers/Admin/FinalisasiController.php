<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalisasiController extends Controller
{
    /**
     * Display list of pendaftar for finalization
     */
    public function index(Request $request)
    {
        // Get tahun pelajaran
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
        
        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        // Get jalur and gelombang for filters
        $jalurList = $tahunAktif 
            ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() 
            : collect();
        
        $gelombangList = $tahunAktif 
            ? GelombangPendaftaran::whereHas('jalur', function($q) use ($tahunAktif) {
                $q->where('tahun_pelajaran_id', $tahunAktif->id);
            })->get() 
            : collect();

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
        if ($request->jalur_id) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }

        // Filter gelombang
        if ($request->gelombang_id) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
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
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'stats'
        ));
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
                $pendaftar->nomor_registrasi = $this->generateNomorRegistrasi($pendaftar);
            }

            // Generate nomor tes
            if (!$pendaftar->nomor_tes) {
                $pendaftar->nomor_tes = $this->generateNomorTes($pendaftar);
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
                    $pendaftar->nomor_registrasi = $this->generateNomorRegistrasi($pendaftar);
                }

                if (!$pendaftar->nomor_tes) {
                    $pendaftar->nomor_tes = $this->generateNomorTes($pendaftar);
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

    /**
     * Generate nomor registrasi
     */
    private function generateNomorRegistrasi(CalonSiswa $pendaftar): string
    {
        $tahun = date('Y');
        $jalurKode = $pendaftar->jalurPendaftaran?->kode ?? 'XX';
        $gelombangKode = $pendaftar->gelombangPendaftaran?->kode ?? '0';
        
        // Get sequence number for this year, jalur, gelombang
        $count = CalonSiswa::whereYear('created_at', $tahun)
            ->where('jalur_pendaftaran_id', $pendaftar->jalur_pendaftaran_id)
            ->where('gelombang_pendaftaran_id', $pendaftar->gelombang_pendaftaran_id)
            ->whereNotNull('nomor_registrasi')
            ->count();

        $sequence = $count + 1;
        
        return sprintf('%s/%s/%s/%04d', $tahun, $jalurKode, $gelombangKode, $sequence);
    }

    /**
     * Generate nomor tes using settings format
     */
    private function generateNomorTes(CalonSiswa $pendaftar): string
    {
        // If already has nomor_tes, return it
        if ($pendaftar->nomor_tes) {
            return $pendaftar->nomor_tes;
        }

        $settings = \App\Models\PpdbSettings::first();
        $tahun = $pendaftar->tahunPelajaran->tahun_mulai ?? date('Y');
        $jalurCode = strtoupper(substr($pendaftar->jalurPendaftaran->nama ?? 'REG', 0, 3));
        
        // Get and update counter for this jalur
        $counters = $settings->nomor_tes_counter ?? [];
        $jalurKey = (string) $pendaftar->jalur_pendaftaran_id;
        $counter = ($counters[$jalurKey] ?? 0) + 1;
        
        // Update counter atomically
        $counters[$jalurKey] = $counter;
        $settings->update(['nomor_tes_counter' => $counters]);
        
        // Generate nomor using format template
        $format = $settings->nomor_tes_format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}';
        $nomor = str_pad($counter, $settings->nomor_tes_digit ?? 4, '0', STR_PAD_LEFT);
        
        $nomorTes = str_replace(
            ['{PREFIX}', '{TAHUN}', '{JALUR}', '{NOMOR}'],
            [$settings->nomor_tes_prefix ?? 'NTS', $tahun, $jalurCode, $nomor],
            $format
        );

        return $nomorTes;
    }
}
