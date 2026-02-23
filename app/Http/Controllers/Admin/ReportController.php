<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\NilaiSeleksi;
use App\Models\NilaiCbt;
use App\Models\Kelulusan;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Halaman utama laporan PPDB
     */
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

        $jalurs = JalurPendaftaran::when($selectedTahunId, fn($q) => $q->where('tahun_pelajaran_id', $selectedTahunId))
            ->where('is_active', true)
            ->get();

        $gelombangs = GelombangPendaftaran::when($request->jalur_id, fn($q) => $q->where('jalur_id', $request->jalur_id))
            ->when(!$request->jalur_id && $selectedTahunId, function ($q) use ($jalurs) {
                $q->whereIn('jalur_id', $jalurs->pluck('id'));
            })
            ->get();

        // Build query
        $query = CalonSiswa::with([
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelulusan',
        ]);

        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }
        if ($request->jalur_id) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }
        if ($request->gelombang_id) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        $pendaftar = $query->get();

        // Build comprehensive statistics
        $stats = $this->buildComprehensiveStatistics($pendaftar, $selectedTahunId);

        $selectedTahun = $tahunPelajarans->firstWhere('id', $selectedTahunId);

        return view('admin.report.index', compact(
            'tahunPelajarans',
            'jalurs',
            'gelombangs',
            'stats',
            'selectedTahun',
            'tahunAktif'
        ));
    }

    /**
     * Export laporan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        $query = CalonSiswa::with([
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelulusan',
        ]);

        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }
        if ($request->jalur_id) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }
        if ($request->gelombang_id) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        $pendaftar = $query->get();
        $stats = $this->buildComprehensiveStatistics($pendaftar, $selectedTahunId);

        $selectedTahun = TahunPelajaran::find($selectedTahunId);
        $selectedJalur = $request->jalur_id ? JalurPendaftaran::find($request->jalur_id) : null;
        $selectedGelombang = $request->gelombang_id ? GelombangPendaftaran::find($request->gelombang_id) : null;
        $sekolah = SekolahSettings::first();

        $pdf = Pdf::loadView('admin.report.pdf', compact(
            'stats',
            'selectedTahun',
            'selectedJalur',
            'selectedGelombang',
            'sekolah'
        ));

        $pdf->setPaper('A4', 'portrait');

        $tahunNama = str_replace(['/', '\\'], '-', $selectedTahun?->nama ?? date('Y'));
        $filename = 'Laporan_PPDB_' . $tahunNama . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export laporan ke Excel
     */
    public function exportExcel(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        $query = CalonSiswa::with([
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelulusan',
        ]);

        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }
        if ($request->jalur_id) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }
        if ($request->gelombang_id) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        $pendaftar = $query->get();
        $stats = $this->buildComprehensiveStatistics($pendaftar, $selectedTahunId);

        $selectedTahun = TahunPelajaran::find($selectedTahunId);
        $selectedJalur = $request->jalur_id ? JalurPendaftaran::find($request->jalur_id) : null;
        $selectedGelombang = $request->gelombang_id ? GelombangPendaftaran::find($request->gelombang_id) : null;
        $sekolah = SekolahSettings::first();

        $tahunNama = str_replace(['/', '\\'], '-', $selectedTahun?->nama ?? date('Y'));
        $filename = 'Laporan_PPDB_' . $tahunNama . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new \App\Exports\LaporanPpdbExport($stats, $selectedTahun, $selectedJalur, $selectedGelombang, $sekolah),
            $filename
        );
    }

    /**
     * Build comprehensive statistics - all 5 sections
     */
    private function buildComprehensiveStatistics(Collection $pendaftar, $tahunPelajaranId = null): array
    {
        $calonIds = $pendaftar->pluck('id');

        // Get IDs for tes participants (CBT or TBQ)
        $tbqParticipantIds = NilaiSeleksi::whereIn('calon_siswa_id', $calonIds)
            ->whereIn('status', ['submitted', 'verified'])
            ->pluck('calon_siswa_id')
            ->unique();

        $cbtParticipantIds = NilaiCbt::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->pluck('calon_siswa_id')
            ->unique();

        $tesParticipantIds = $tbqParticipantIds->merge($cbtParticipantIds)->unique();

        // Split pendaftar into groups
        $dapatNomorTes = $pendaftar->filter(fn($p) => !empty($p->nomor_tes));
        $tidakDapatNomorTes = $pendaftar->filter(fn($p) => empty($p->nomor_tes));
        $ikutTes = $pendaftar->filter(fn($p) => $tesParticipantIds->contains($p->id));

        // Kelulusan data
        $lulusIds = Kelulusan::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->where('status', 'lulus')
            ->pluck('calon_siswa_id');
        $tidakLulusIds = Kelulusan::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->where('status', 'tidak_lulus')
            ->pluck('calon_siswa_id');
        $cadanganIds = Kelulusan::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->where('status', 'cadangan')
            ->pluck('calon_siswa_id');

        $lulus = $pendaftar->filter(fn($p) => $lulusIds->contains($p->id));
        $tidakLulus = $pendaftar->filter(fn($p) => $tidakLulusIds->contains($p->id));
        $cadangan = $pendaftar->filter(fn($p) => $cadanganIds->contains($p->id));

        return [
            // Summary numbers
            'total' => $pendaftar->count(),
            'dapat_nomor_tes' => $dapatNomorTes->count(),
            'tidak_dapat_nomor_tes' => $tidakDapatNomorTes->count(),
            'finalisasi' => $pendaftar->where('is_finalisasi', true)->count(),
            'ikut_tes' => $ikutTes->count(),
            'ikut_tbq' => $tbqParticipantIds->count(),
            'ikut_cbt' => $cbtParticipantIds->count(),
            'lulus_total' => $lulus->count(),
            'tidak_lulus_total' => $tidakLulus->count(),
            'cadangan_total' => $cadangan->count(),

            // Section 1: Total Pendaftar
            'total_pendaftar' => $this->buildSectionStats($pendaftar),

            // Section 2: Yang Mendapat Nomor Tes
            'dengan_nomor_tes' => $this->buildSectionStats($dapatNomorTes),

            // Section 3: Yang Tidak Mendapat Nomor Tes
            'tanpa_nomor_tes' => $this->buildSectionStats($tidakDapatNomorTes),

            // Section 4: Yang Mengikuti Tes
            'peserta_tes' => $this->buildSectionStats($ikutTes),

            // Section 5: Kelulusan
            'kelulusan' => $this->buildSectionStats($lulus),
            'kelulusan_tidak_lulus' => $this->buildSectionStats($tidakLulus),
            'kelulusan_cadangan' => $this->buildSectionStats($cadangan),

            // Keep legacy data for status verifikasi / admisi etc
            'status_verifikasi' => [
                'pending' => $pendaftar->where('status_verifikasi', 'pending')->count(),
                'verified' => $pendaftar->where('status_verifikasi', 'verified')->count(),
                'rejected' => $pendaftar->where('status_verifikasi', 'rejected')->count(),
                'revisi' => $pendaftar->where('status_verifikasi', 'revisi')->count(),
            ],
            'status_admisi' => [
                'diterima' => $pendaftar->where('status_admisi', 'diterima')->count(),
                'ditolak' => $pendaftar->where('status_admisi', 'ditolak')->count(),
                'cadangan' => $pendaftar->where('status_admisi', 'cadangan')->count(),
                'pending' => $pendaftar->filter(fn($p) => !in_array($p->status_admisi, ['diterima', 'ditolak', 'cadangan']))->count(),
            ],

            // Per jalur & gelombang
            'per_jalur' => $pendaftar->groupBy(fn($p) => $p->jalurPendaftaran?->nama ?? 'Tidak Diketahui')
                ->map(function ($group) {
                    return [
                        'total' => $group->count(),
                        'laki_laki' => $group->where('jenis_kelamin', 'L')->count(),
                        'perempuan' => $group->where('jenis_kelamin', 'P')->count(),
                        'finalisasi' => $group->where('is_finalisasi', true)->count(),
                        'nomor_tes' => $group->filter(fn($p) => !empty($p->nomor_tes))->count(),
                    ];
                })->sortByDesc('total'),
            'per_gelombang' => $pendaftar->groupBy(fn($p) => $p->gelombangPendaftaran?->nama ?? 'Tidak Diketahui')
                ->map(function ($group) {
                    return [
                        'total' => $group->count(),
                        'laki_laki' => $group->where('jenis_kelamin', 'L')->count(),
                        'perempuan' => $group->where('jenis_kelamin', 'P')->count(),
                    ];
                })->sortByDesc('total'),

            // Sebaran wilayah
            'sebaran_kabupaten' => $pendaftar->groupBy(fn($p) => $p->kabupatenSiswa?->name ?? $p->kabupaten_sekolah_asal ?? 'Tidak Diketahui')
                ->map(fn($group) => $group->count())
                ->sortByDesc(fn($v) => $v),
            'sebaran_kecamatan' => $pendaftar->groupBy(fn($p) => $p->kecamatanSiswa?->name ?? $p->kecamatan_sekolah_asal ?? 'Tidak Diketahui')
                ->map(fn($group) => $group->count())
                ->sortByDesc(fn($v) => $v)
                ->take(20),

            // Sebaran sekolah asal (all schools with details)
            'sebaran_sekolah' => $this->buildSebaranSekolah($pendaftar),
        ];
    }

    /**
     * Build detailed stats for a group of pendaftar (reusable per section)
     */
    private function buildSectionStats(Collection $group): array
    {
        $total = $group->count();
        $lakiLaki = $group->where('jenis_kelamin', 'L');
        $perempuan = $group->where('jenis_kelamin', 'P');

        // Pilihan Program
        $reguler = $group->filter(fn($p) => strtolower($p->pilihan_program ?? '') === 'reguler');
        $asrama = $group->filter(fn($p) => strtolower($p->pilihan_program ?? '') === 'asrama');
        $belumMemilih = $group->filter(fn($p) => empty($p->pilihan_program) || !in_array(strtolower($p->pilihan_program), ['reguler', 'asrama']));

        // Kategorisasi Asal Sekolah
        $asalSekolah = $this->kategorikanAsalSekolah($group);

        return [
            'total' => $total,
            'laki_laki' => $lakiLaki->count(),
            'perempuan' => $perempuan->count(),

            // Program
            'reguler' => $reguler->count(),
            'asrama' => $asrama->count(),
            'reguler_l' => $reguler->where('jenis_kelamin', 'L')->count(),
            'reguler_p' => $reguler->where('jenis_kelamin', 'P')->count(),
            'asrama_l' => $asrama->where('jenis_kelamin', 'L')->count(),
            'asrama_p' => $asrama->where('jenis_kelamin', 'P')->count(),
            'belum_memilih' => $belumMemilih->count(),
            'belum_memilih_l' => $belumMemilih->where('jenis_kelamin', 'L')->count(),
            'belum_memilih_p' => $belumMemilih->where('jenis_kelamin', 'P')->count(),

            // Asal Sekolah
            'asal_sekolah' => $asalSekolah,
        ];
    }

    /**
     * Kategorikan asal sekolah pendaftar
     * Prioritas: bentuk_sekolah_asal > regex nama, status_sekolah_asal > regex nama
     */
    private function kategorikanAsalSekolah(Collection $group): array
    {
        $categories = [
            'MTs Negeri' => ['total' => 0, 'l' => 0, 'p' => 0],
            'MTs Swasta' => ['total' => 0, 'l' => 0, 'p' => 0],
            'SMP Negeri' => ['total' => 0, 'l' => 0, 'p' => 0],
            'SMP Swasta' => ['total' => 0, 'l' => 0, 'p' => 0],
            'Lainnya' => ['total' => 0, 'l' => 0, 'p' => 0],
        ];

        foreach ($group as $p) {
            $nama = strtolower($p->nama_sekolah_asal ?? '');
            $bentuk = strtoupper(trim($p->bentuk_sekolah_asal ?? ''));
            $status = strtoupper(trim($p->status_sekolah_asal ?? ''));
            $jk = $p->jenis_kelamin;

            // 1) Tentukan bentuk pendidikan: prioritas field bentuk_sekolah_asal
            if (in_array($bentuk, ['MTS', 'MTS.', 'MADRASAH TSANAWIYAH'])) {
                $isMts = true;
                $isSmp = false;
            } elseif (in_array($bentuk, ['SMP', 'SMP.', 'SEKOLAH MENENGAH PERTAMA'])) {
                $isMts = false;
                $isSmp = true;
            } else {
                // Fallback: regex pada nama_sekolah_asal
                $isMts = (bool) preg_match('/\b(mts|madrasah\s*tsanawiyah)\b/i', $nama);
                $isSmp = (bool) preg_match('/\b(smp|sekolah\s*menengah\s*pertama)\b/i', $nama);
            }

            // 2) Tentukan status: prioritas field status_sekolah_asal
            if ($status === 'NEGERI') {
                $isNegeri = true;
            } elseif ($status === 'SWASTA') {
                $isNegeri = false;
            } else {
                // Fallback: regex pada nama_sekolah_asal
                $isNegeri = (bool) preg_match('/\bnegeri\b/i', $nama);
            }

            // 3) Gabungkan kategori
            if ($isMts) {
                $cat = $isNegeri ? 'MTs Negeri' : 'MTs Swasta';
            } elseif ($isSmp) {
                $cat = $isNegeri ? 'SMP Negeri' : 'SMP Swasta';
            } else {
                $cat = 'Lainnya';
            }

            $categories[$cat]['total']++;
            if ($jk === 'L') $categories[$cat]['l']++;
            if ($jk === 'P') $categories[$cat]['p']++;
        }

        return $categories;
    }

    /**
     * Build sebaran sekolah asal (all schools grouped + sorted by count)
     */
    private function buildSebaranSekolah(Collection $pendaftar): array
    {
        return $pendaftar
            ->filter(fn($p) => !empty($p->nama_sekolah_asal))
            ->groupBy(fn($p) => trim($p->nama_sekolah_asal))
            ->map(function ($group, $namaSekolah) {
                $first = $group->first();
                return [
                    'nama' => $namaSekolah,
                    'total' => $group->count(),
                    'l' => $group->where('jenis_kelamin', 'L')->count(),
                    'p' => $group->where('jenis_kelamin', 'P')->count(),
                    'bentuk' => $first->bentuk_sekolah_asal ?? '-',
                    'status' => $first->status_sekolah_asal ?? '-',
                    'npsn' => $first->npsn_sekolah_asal ?? '-',
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }
}
