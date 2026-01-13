<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Dashboard statistik utama
     */
    public function index(Request $request)
    {
        // Get active tahun pelajaran or selected
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $tahunAktif = $tahunPelajaranId 
            ? TahunPelajaran::find($tahunPelajaranId) 
            : TahunPelajaran::where('is_active', true)->first();
        
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        
        // Base query for current tahun pelajaran
        $query = CalonSiswa::query();
        if ($tahunAktif) {
            $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
            $query->whereIn('jalur_pendaftaran_id', $jalurIds);
        }
        
        // Total pendaftar
        $totalPendaftar = $query->count();
        
        // By status
        $byStatus = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->select('status_verifikasi', DB::raw('count(*) as total'))
            ->groupBy('status_verifikasi')
            ->pluck('total', 'status_verifikasi')
            ->toArray();
        
        // By jenis kelamin
        $byJenisKelamin = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kelamin')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin')
            ->toArray();
        
        // By jalur
        $byJalur = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('jalur_pendaftaran', 'calon_siswas.jalur_pendaftaran_id', '=', 'jalur_pendaftaran.id')
            ->select('jalur_pendaftaran.nama', 'jalur_pendaftaran.warna', DB::raw('count(*) as total'))
            ->groupBy('jalur_pendaftaran.id', 'jalur_pendaftaran.nama', 'jalur_pendaftaran.warna')
            ->get();
        
        // By gelombang
        $byGelombang = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('gelombang_pendaftaran', 'calon_siswas.gelombang_pendaftaran_id', '=', 'gelombang_pendaftaran.id')
            ->select('gelombang_pendaftaran.nama', DB::raw('count(*) as total'))
            ->groupBy('gelombang_pendaftaran.id', 'gelombang_pendaftaran.nama')
            ->get();
        
        // By pilihan program
        $byPilihanProgram = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->select('pilihan_program', DB::raw('count(*) as total'))
            ->whereNotNull('pilihan_program')
            ->groupBy('pilihan_program')
            ->pluck('total', 'pilihan_program')
            ->toArray();
        
        // Trend pendaftaran 30 hari terakhir
        $trendPendaftaran = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('count(*) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        return view('admin.statistik.index', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'totalPendaftar',
            'byStatus',
            'byJenisKelamin',
            'byJalur',
            'byGelombang',
            'byPilihanProgram',
            'trendPendaftaran'
        ));
    }
    
    /**
     * Statistik sebaran geografis
     */
    public function geografis(Request $request)
    {
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $tahunAktif = $tahunPelajaranId 
            ? TahunPelajaran::find($tahunPelajaranId) 
            : TahunPelajaran::where('is_active', true)->first();
        
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        
        // Base query
        $baseQuery = function() use ($tahunAktif) {
            $query = CalonSiswa::query();
            if ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $query->whereIn('jalur_pendaftaran_id', $jalurIds);
            }
            return $query;
        };
        
        // By provinsi
        $byProvinsi = $baseQuery()
            ->select('provinsi', DB::raw('count(*) as total'))
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->groupBy('provinsi')
            ->orderByDesc('total')
            ->get();
        
        // By kabupaten
        $filterProvinsi = $request->get('provinsi');
        $byKabupaten = $baseQuery()
            ->select('kabupaten', 'provinsi', DB::raw('count(*) as total'))
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->when($filterProvinsi, function($q) use ($filterProvinsi) {
                $q->where('provinsi', $filterProvinsi);
            })
            ->groupBy('kabupaten', 'provinsi')
            ->orderByDesc('total')
            ->get();
        
        // By kecamatan
        $filterKabupaten = $request->get('kabupaten');
        $byKecamatan = $baseQuery()
            ->select('kecamatan', 'kabupaten', DB::raw('count(*) as total'))
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->when($filterKabupaten, function($q) use ($filterKabupaten) {
                $q->where('kabupaten', $filterKabupaten);
            })
            ->groupBy('kecamatan', 'kabupaten')
            ->orderByDesc('total')
            ->limit(50)
            ->get();
        
        // By kelurahan
        $filterKecamatan = $request->get('kecamatan');
        $byKelurahan = $baseQuery()
            ->select('kelurahan', 'kecamatan', DB::raw('count(*) as total'))
            ->whereNotNull('kelurahan')
            ->where('kelurahan', '!=', '')
            ->when($filterKecamatan, function($q) use ($filterKecamatan) {
                $q->where('kecamatan', $filterKecamatan);
            })
            ->groupBy('kelurahan', 'kecamatan')
            ->orderByDesc('total')
            ->limit(50)
            ->get();
        
        // Data untuk peta (koordinat registrasi)
        $mapData = $baseQuery()
            ->whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->select('id', 'nama_lengkap', 'registration_latitude', 'registration_longitude', 'registration_address')
            ->get();
        
        // List provinsi untuk filter
        $provinsiList = $baseQuery()
            ->select('provinsi')
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->distinct()
            ->orderBy('provinsi')
            ->pluck('provinsi');
        
        return view('admin.statistik.geografis', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'byProvinsi',
            'byKabupaten',
            'byKecamatan',
            'byKelurahan',
            'mapData',
            'provinsiList',
            'filterProvinsi',
            'filterKabupaten',
            'filterKecamatan'
        ));
    }
    
    /**
     * Statistik asal sekolah
     */
    public function asalSekolah(Request $request)
    {
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $tahunAktif = $tahunPelajaranId 
            ? TahunPelajaran::find($tahunPelajaranId) 
            : TahunPelajaran::where('is_active', true)->first();
        
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        
        $search = $request->get('search');
        
        // Base query
        $query = CalonSiswa::query();
        if ($tahunAktif) {
            $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
            $query->whereIn('jalur_pendaftaran_id', $jalurIds);
        }
        
        // By asal sekolah
        $byAsalSekolah = (clone $query)
            ->select('asal_sekolah', 'npsn', DB::raw('count(*) as total'))
            ->whereNotNull('asal_sekolah')
            ->where('asal_sekolah', '!=', '')
            ->when($search, function($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('asal_sekolah', 'like', "%{$search}%")
                       ->orWhere('npsn', 'like', "%{$search}%");
                });
            })
            ->groupBy('asal_sekolah', 'npsn')
            ->orderByDesc('total')
            ->paginate(20);
        
        // Top 10 sekolah
        $topSekolah = (clone $query)
            ->select('asal_sekolah', 'npsn', DB::raw('count(*) as total'))
            ->whereNotNull('asal_sekolah')
            ->where('asal_sekolah', '!=', '')
            ->groupBy('asal_sekolah', 'npsn')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        // Total sekolah unik
        $totalSekolah = (clone $query)
            ->whereNotNull('asal_sekolah')
            ->where('asal_sekolah', '!=', '')
            ->distinct('asal_sekolah')
            ->count('asal_sekolah');
        
        return view('admin.statistik.asal-sekolah', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'byAsalSekolah',
            'topSekolah',
            'totalSekolah',
            'search'
        ));
    }
    
    /**
     * Statistik ekonomi orang tua
     */
    public function ekonomi(Request $request)
    {
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $tahunAktif = $tahunPelajaranId 
            ? TahunPelajaran::find($tahunPelajaranId) 
            : TahunPelajaran::where('is_active', true)->first();
        
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        
        // Get calon siswa IDs for current tahun
        $calonSiswaIds = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->pluck('id');
        
        // Kategori pendapatan
        $kategoriPendapatan = [
            'Tidak Ada' => [0, 0],
            '< Rp 1 Juta' => [1, 1000000],
            'Rp 1-3 Juta' => [1000001, 3000000],
            'Rp 3-5 Juta' => [3000001, 5000000],
            'Rp 5-10 Juta' => [5000001, 10000000],
            '> Rp 10 Juta' => [10000001, 999999999],
        ];
        
        // By penghasilan ayah
        $byPenghasilanAyah = [];
        foreach ($kategoriPendapatan as $label => $range) {
            $count = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
                ->whereBetween('penghasilan_ayah', $range)
                ->count();
            $byPenghasilanAyah[$label] = $count;
        }
        
        // By penghasilan ibu
        $byPenghasilanIbu = [];
        foreach ($kategoriPendapatan as $label => $range) {
            $count = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
                ->whereBetween('penghasilan_ibu', $range)
                ->count();
            $byPenghasilanIbu[$label] = $count;
        }
        
        // By pekerjaan ayah
        $byPekerjaanAyah = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pekerjaan_ayah', DB::raw('count(*) as total'))
            ->whereNotNull('pekerjaan_ayah')
            ->where('pekerjaan_ayah', '!=', '')
            ->groupBy('pekerjaan_ayah')
            ->orderByDesc('total')
            ->limit(15)
            ->get();
        
        // By pekerjaan ibu
        $byPekerjaanIbu = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pekerjaan_ibu', DB::raw('count(*) as total'))
            ->whereNotNull('pekerjaan_ibu')
            ->where('pekerjaan_ibu', '!=', '')
            ->groupBy('pekerjaan_ibu')
            ->orderByDesc('total')
            ->limit(15)
            ->get();
        
        // By pendidikan ayah
        $byPendidikanAyah = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pendidikan_ayah', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_ayah')
            ->where('pendidikan_ayah', '!=', '')
            ->groupBy('pendidikan_ayah')
            ->orderByDesc('total')
            ->get();
        
        // By pendidikan ibu
        $byPendidikanIbu = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pendidikan_ibu', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_ibu')
            ->where('pendidikan_ibu', '!=', '')
            ->groupBy('pendidikan_ibu')
            ->orderByDesc('total')
            ->get();
        
        return view('admin.statistik.ekonomi', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'byPenghasilanAyah',
            'byPenghasilanIbu',
            'byPekerjaanAyah',
            'byPekerjaanIbu',
            'byPendidikanAyah',
            'byPendidikanIbu'
        ));
    }
    
    /**
     * Statistik dokumen prestasi
     */
    public function dokumenPrestasi(Request $request)
    {
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $tahunAktif = $tahunPelajaranId 
            ? TahunPelajaran::find($tahunPelajaranId) 
            : TahunPelajaran::where('is_active', true)->first();
        
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        
        // Get calon siswa IDs for current tahun
        $calonSiswaIds = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->pluck('id');
        
        $totalPendaftar = $calonSiswaIds->count();
        
        // Daftar jenis dokumen tambahan/prestasi
        $dokumenTambahanTypes = [
            'sertifikat_prestasi' => 'Sertifikat Prestasi',
            'piagam_penghargaan' => 'Piagam Penghargaan',
            'sertifikat_tahfidz' => 'Sertifikat Tahfidz',
            'sertifikat_olimpiade' => 'Sertifikat Olimpiade',
            'sertifikat_lainnya' => 'Sertifikat Lainnya',
        ];
        
        // By jenis dokumen tambahan
        $byJenisDokumen = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('jenis_dokumen', DB::raw('count(*) as total'))
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->groupBy('jenis_dokumen')
            ->get()
            ->map(function($item) use ($dokumenTambahanTypes) {
                $item->label = $dokumenTambahanTypes[$item->jenis_dokumen] ?? $item->jenis_dokumen;
                return $item;
            });
        
        // Yang memiliki dokumen prestasi
        $pendaftarDenganPrestasi = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');
        
        // By status verifikasi dokumen prestasi
        $byStatusDokumen = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->select('status_verifikasi', DB::raw('count(*) as total'))
            ->groupBy('status_verifikasi')
            ->pluck('total', 'status_verifikasi')
            ->toArray();
        
        // Detail pendaftar dengan prestasi
        $detailPrestasi = CalonSiswa::whereIn('id', $calonSiswaIds)
            ->whereHas('dokumen', function($q) use ($dokumenTambahanTypes) {
                $q->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes));
            })
            ->with(['dokumen' => function($q) use ($dokumenTambahanTypes) {
                $q->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes));
            }])
            ->select('id', 'nama_lengkap', 'asal_sekolah')
            ->paginate(20);
        
        return view('admin.statistik.dokumen-prestasi', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'totalPendaftar',
            'pendaftarDenganPrestasi',
            'byJenisDokumen',
            'byStatusDokumen',
            'detailPrestasi',
            'dokumenTambahanTypes'
        ));
    }
    
    /**
     * Export statistik ke Excel
     */
    public function export(Request $request, $type)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Fitur export sedang dalam pengembangan');
    }
}
