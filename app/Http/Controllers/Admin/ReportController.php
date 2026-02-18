<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\NilaiSeleksi;
use App\Models\NilaiCbt;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Build statistics
        $stats = $this->buildStatistics($pendaftar, $selectedTahunId);

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

        $jalurs = JalurPendaftaran::when($selectedTahunId, fn($q) => $q->where('tahun_pelajaran_id', $selectedTahunId))
            ->where('is_active', true)
            ->get();

        // Build query
        $query = CalonSiswa::with([
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'kabupatenSiswa',
            'kecamatanSiswa',
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
        $stats = $this->buildStatistics($pendaftar, $selectedTahunId);

        $selectedTahun = TahunPelajaran::find($selectedTahunId);
        $selectedJalur = $request->jalur_id ? JalurPendaftaran::find($request->jalur_id) : null;
        $selectedGelombang = $request->gelombang_id ? GelombangPendaftaran::find($request->gelombang_id) : null;

        // Get sekolah info
        $sekolah = SekolahSettings::first();

        $pdf = Pdf::loadView('admin.report.pdf', compact(
            'stats',
            'selectedTahun',
            'selectedJalur',
            'selectedGelombang',
            'sekolah'
        ));

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Laporan_PPDB_' . ($selectedTahun?->nama ?? date('Y')) . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build all statistics from pendaftar collection
     */
    private function buildStatistics($pendaftar, $tahunPelajaranId = null)
    {
        $total = $pendaftar->count();

        // Jumlah yang mendapat nomor tes
        $dapatNomorTes = $pendaftar->filter(fn($p) => !empty($p->nomor_tes))->count();

        // Jumlah finalisasi 
        $finalisasi = $pendaftar->where('is_finalisasi', true)->count();

        // Jumlah yang ikut tes (punya nilai TBQ atau CBT)
        $calonIds = $pendaftar->pluck('id');

        $ikutTbq = NilaiSeleksi::whereIn('calon_siswa_id', $calonIds)
            ->whereIn('status', ['submitted', 'verified'])
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');

        $ikutCbt = NilaiCbt::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');

        // Union TBQ + CBT participants
        $tbqIds = NilaiSeleksi::whereIn('calon_siswa_id', $calonIds)
            ->whereIn('status', ['submitted', 'verified'])
            ->pluck('calon_siswa_id');
        $cbtIds = NilaiCbt::whereIn('calon_siswa_id', $calonIds)
            ->when($tahunPelajaranId, fn($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->pluck('calon_siswa_id');
        $ikutTes = $tbqIds->merge($cbtIds)->unique()->count();

        // Status verifikasi
        $statusVerifikasi = [
            'pending' => $pendaftar->where('status_verifikasi', 'pending')->count(),
            'verified' => $pendaftar->where('status_verifikasi', 'verified')->count(),
            'rejected' => $pendaftar->where('status_verifikasi', 'rejected')->count(),
            'revisi' => $pendaftar->where('status_verifikasi', 'revisi')->count(),
        ];

        // Status admisi
        $statusAdmisi = [
            'diterima' => $pendaftar->where('status_admisi', 'diterima')->count(),
            'ditolak' => $pendaftar->where('status_admisi', 'ditolak')->count(),
            'cadangan' => $pendaftar->where('status_admisi', 'cadangan')->count(),
            'pending' => $pendaftar->filter(fn($p) => !in_array($p->status_admisi, ['diterima', 'ditolak', 'cadangan']))->count(),
        ];

        // Jenis kelamin
        $jenisKelamin = [
            'laki_laki' => $pendaftar->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $pendaftar->where('jenis_kelamin', 'P')->count(),
        ];

        // Per jalur
        $perJalur = $pendaftar->groupBy(fn($p) => $p->jalurPendaftaran?->nama ?? 'Tidak Diketahui')
            ->map(function ($group, $namaJalur) {
                return [
                    'total' => $group->count(),
                    'laki_laki' => $group->where('jenis_kelamin', 'L')->count(),
                    'perempuan' => $group->where('jenis_kelamin', 'P')->count(),
                    'finalisasi' => $group->where('is_finalisasi', true)->count(),
                    'nomor_tes' => $group->filter(fn($p) => !empty($p->nomor_tes))->count(),
                ];
            })->sortByDesc('total');

        // Per gelombang
        $perGelombang = $pendaftar->groupBy(fn($p) => $p->gelombangPendaftaran?->nama ?? 'Tidak Diketahui')
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'laki_laki' => $group->where('jenis_kelamin', 'L')->count(),
                    'perempuan' => $group->where('jenis_kelamin', 'P')->count(),
                ];
            })->sortByDesc('total');

        // Sebaran wilayah (kabupaten)
        $sebaranKabupaten = $pendaftar->groupBy(fn($p) => $p->kabupatenSiswa?->name ?? $p->kabupaten_sekolah_asal ?? 'Tidak Diketahui')
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($v) => $v);

        // Sebaran wilayah (kecamatan) - top 20
        $sebaranKecamatan = $pendaftar->groupBy(fn($p) => $p->kecamatanSiswa?->name ?? $p->kecamatan_sekolah_asal ?? 'Tidak Diketahui')
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($v) => $v)
            ->take(20);

        // Sebaran asal sekolah - top 20
        $sebaranSekolah = $pendaftar->groupBy(fn($p) => $p->nama_sekolah_asal ?? 'Tidak Diketahui')
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($v) => $v)
            ->take(20);

        // Pilihan program
        $pilihanProgram = $pendaftar->groupBy(fn($p) => $p->pilihan_program ?? 'Belum Memilih')
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'laki_laki' => $group->where('jenis_kelamin', 'L')->count(),
                    'perempuan' => $group->where('jenis_kelamin', 'P')->count(),
                ];
            })->sortByDesc('total');

        return [
            'total' => $total,
            'dapat_nomor_tes' => $dapatNomorTes,
            'finalisasi' => $finalisasi,
            'ikut_tes' => $ikutTes,
            'ikut_tbq' => $ikutTbq,
            'ikut_cbt' => $ikutCbt,
            'jenis_kelamin' => $jenisKelamin,
            'status_verifikasi' => $statusVerifikasi,
            'status_admisi' => $statusAdmisi,
            'per_jalur' => $perJalur,
            'per_gelombang' => $perGelombang,
            'sebaran_kabupaten' => $sebaranKabupaten,
            'sebaran_kecamatan' => $sebaranKecamatan,
            'sebaran_sekolah' => $sebaranSekolah,
            'pilihan_program' => $pilihanProgram,
        ];
    }
}
