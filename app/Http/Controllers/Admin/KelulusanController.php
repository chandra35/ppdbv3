<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\Kelulusan;
use App\Models\KelulusanSetting;
use App\Models\NilaiSeleksi;
use App\Models\NilaiCbt;
use App\Models\NilaiRapor;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelulusanController extends Controller
{
    /**
     * Halaman utama kelulusan - tampilan mirip rekap nilai dengan checkbox
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

        // ---- 1. Load NilaiSeleksi (TBQ) data ----
        $seleksiQuery = NilaiSeleksi::with(['calonSiswa.jalurPendaftaran', 'calonSiswa.gelombangPendaftaran', 'ruangUjian', 'sesiUjian.jalur'])
            ->whereIn('status', ['submitted', 'verified']);

        if ($selectedTahunId) {
            $seleksiQuery->whereHas('sesiUjian', function ($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            });
        }

        if ($context['jalurFilterId']) {
            $seleksiQuery->whereHas('sesiUjian', function ($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }

        if ($context['gelombangFilterId']) {
            $seleksiQuery->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
        }

        $rekapData = $seleksiQuery->orderBy('total_nilai', 'desc')
            ->orderBy('nilai_wawancara', 'desc')
            ->get();

        // ---- 2. Load CBT data ----
        $cbtData = NilaiCbt::where('tahun_pelajaran_id', $selectedTahunId)
            ->get()
            ->keyBy('calon_siswa_id');

        // ---- 3. Include CBT-only participants ----
        $seleksiCalonIds = $rekapData->pluck('calon_siswa_id');
        $cbtOnlyCalonIds = $cbtData->keys()->diff($seleksiCalonIds);

        if ($cbtOnlyCalonIds->isNotEmpty()) {
            $cbtOnlySiswa = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
                ->whereIn('id', $cbtOnlyCalonIds);

            if ($context['jalurFilterId']) {
                $cbtOnlySiswa->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            }
            if ($context['gelombangFilterId']) {
                $cbtOnlySiswa->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            }

            foreach ($cbtOnlySiswa->get() as $siswa) {
                $virtual = new \stdClass();
                $virtual->calon_siswa_id = $siswa->id;
                $virtual->calonSiswa = $siswa;
                $virtual->nilai_baca_quran = null;
                $virtual->nilai_tulis_quran = null;
                $virtual->nilai_hafalan = null;
                $virtual->jumlah_juz_hafalan = null;
                $virtual->nilai_wawancara = null;
                $virtual->total_nilai = 0;
                $virtual->status = 'cbt_only';
                $virtual->sesiUjian = null;
                $rekapData->push($virtual);
            }
        }

        // ---- 4. Also include NISN search results ----
        $nisnSearch = $request->nisn_search;
        $nisnList = [];
        if ($nisnSearch) {
            $nisnList = array_filter(array_map('trim', preg_split('/[\r\n]+/', $nisnSearch)));
            if (!empty($nisnList)) {
                $existingCalonIds = $rekapData->pluck('calon_siswa_id');
                $nisnSiswa = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
                    ->whereIn('nisn', $nisnList)
                    ->where('tahun_pelajaran_id', $selectedTahunId)
                    ->whereNotIn('id', $existingCalonIds)
                    ->get();

                foreach ($nisnSiswa as $siswa) {
                    $virtual = new \stdClass();
                    $virtual->calon_siswa_id = $siswa->id;
                    $virtual->calonSiswa = $siswa;
                    $virtual->nilai_baca_quran = null;
                    $virtual->nilai_tulis_quran = null;
                    $virtual->nilai_hafalan = null;
                    $virtual->jumlah_juz_hafalan = null;
                    $virtual->nilai_wawancara = null;
                    $virtual->total_nilai = 0;
                    $virtual->status = 'nisn_search';
                    $virtual->sesiUjian = null;
                    $rekapData->push($virtual);
                }
            }
        }

        // ---- 5. Load Rapor data ----
        $allCalonIds = $rekapData->pluck('calon_siswa_id');
        $raporData = NilaiRapor::selectRaw('calon_siswa_id, AVG(rata_rata) as avg_rapor')
            ->whereIn('calon_siswa_id', $allCalonIds)
            ->groupBy('calon_siswa_id')
            ->pluck('avg_rapor', 'calon_siswa_id')
            ->map(fn($v) => round((float) $v, 2));

        // ---- 6. Hitung nilai akhir ----
        $rekapData->each(function ($nilai) use ($cbtData, $raporData) {
            $cbt = $cbtData[$nilai->calon_siswa_id] ?? null;
            $avgRapor = $raporData[$nilai->calon_siswa_id] ?? null;

            $nilaiCbt = $cbt ? (float) $cbt->rata_rata : null;
            $nilaiRapor = $avgRapor ? (float) $avgRapor : null;
            $nilaiSeleksi = ($nilai->total_nilai ?? 0) > 0 ? (float) $nilai->total_nilai : null;

            $totalBobot = 0;
            $totalNilai = 0;
            if ($nilaiCbt !== null) { $totalNilai += $nilaiCbt * 50; $totalBobot += 50; }
            if ($nilaiRapor !== null) { $totalNilai += $nilaiRapor * 10; $totalBobot += 10; }
            if ($nilaiSeleksi !== null) { $totalNilai += $nilaiSeleksi * 40; $totalBobot += 40; }

            $nilai->nilai_akhir = $totalBobot > 0 ? round($totalNilai / $totalBobot, 2) : 0;
            $nilai->nilai_cbt_rata = $nilaiCbt;
            $nilai->nilai_rapor_rata = $nilaiRapor;
        });

        // Sort by nilai_akhir desc
        $rekapData = $rekapData->sortByDesc(function ($item) {
            return [$item->nilai_akhir, (float) ($item->nilai_wawancara ?? 0)];
        })->values();

        // ---- 7. Load existing kelulusan status ----
        $kelulusanBaseQuery = Kelulusan::query()
            ->where('tahun_pelajaran_id', $selectedTahunId);

        if ($context['jalurFilterId'] || $context['gelombangFilterId']) {
            $kelulusanBaseQuery->whereHas('calonSiswa', function ($q) use ($context) {
                if ($context['jalurFilterId']) {
                    $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
                }
                if ($context['gelombangFilterId']) {
                    $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
                }
            });
        }

        $kelulusanData = (clone $kelulusanBaseQuery)
            ->pluck('status', 'calon_siswa_id');

        // Stats
        $stats = [
            'total' => $rekapData->count(),
            'sudah_lulus' => $kelulusanData->where('lulus')->count(),
            'total_lulus' => (clone $kelulusanBaseQuery)->lulus()->count(),
            'total_tidak_lulus' => (clone $kelulusanBaseQuery)->tidakLulus()->count(),
            'total_cadangan' => (clone $kelulusanBaseQuery)->cadangan()->count(),
        ];

        return view('admin.kelulusan.index', compact(
            'rekapData', 'cbtData', 'raporData', 'kelulusanData', 'stats',
            'tahunAktif', 'nisnSearch'
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'allGelombangs' => $context['allGelombangs'],
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
     * Proses luluskan siswa (bulk)
     */
    public function luluskan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'calon_siswa_ids' => 'required|array|min:1',
            'calon_siswa_ids.*' => 'required|uuid',
            'status' => 'required|in:lulus,tidak_lulus,cadangan',
            'catatan' => 'nullable|string|max:500',
        ]);

        $context = AdminPpdbContext::resolve($request->input('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return response()->json(['success' => false, 'message' => 'Tahun pelajaran aktif tidak ditemukan'], 422);
        }

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($request->calon_siswa_ids as $calonSiswaId) {
                Kelulusan::updateOrCreate(
                    [
                        'calon_siswa_id' => $calonSiswaId,
                        'tahun_pelajaran_id' => $tahunAktif->id,
                    ],
                    [
                        'status' => $request->status,
                        'catatan' => $request->catatan,
                        'diluluskan_oleh' => Auth::id(),
                        'tanggal_kelulusan' => now(),
                    ]
                );

                // Update status_admisi di calon_siswas
                $statusAdmisi = match($request->status) {
                    'lulus' => 'diterima',
                    'tidak_lulus' => 'ditolak',
                    'cadangan' => 'cadangan',
                };
                CalonSiswa::where('id', $calonSiswaId)->update([
                    'status_admisi' => $statusAdmisi,
                    'catatan_admisi' => $request->catatan,
                ]);

                $count++;
            }
            DB::commit();

            $statusLabel = match($request->status) {
                'lulus' => 'LULUS',
                'tidak_lulus' => 'TIDAK LULUS',
                'cadangan' => 'CADANGAN',
            };

            return response()->json([
                'success' => true,
                'message' => "{$count} siswa berhasil ditetapkan sebagai {$statusLabel}",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batalkan kelulusan
     */
    public function batalkan(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'calon_siswa_ids' => 'required|array|min:1',
            'calon_siswa_ids.*' => 'required|uuid',
        ]);

        $context = AdminPpdbContext::resolve($request->input('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];

        DB::beginTransaction();
        try {
            $count = Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)
                ->whereIn('calon_siswa_id', $request->calon_siswa_ids)
                ->delete();

            CalonSiswa::whereIn('id', $request->calon_siswa_ids)->update([
                'status_admisi' => 'pending',
                'catatan_admisi' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} kelulusan dibatalkan",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}
