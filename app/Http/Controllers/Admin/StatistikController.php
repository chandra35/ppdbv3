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
        
        // Get filtered pendaftar list based on criteria
        $filterType = $request->get('filter_type');
        $filterValue = $request->get('filter_value');
        
        $pendaftarQuery = CalonSiswa::query()
            ->with(['jalurPendaftaran', 'gelombangPendaftaran'])
            ->when($tahunAktif, function($q) use ($tahunAktif) {
                $jalurIds = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id');
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            });
        
        if ($filterType && $filterValue !== null) {
            switch ($filterType) {
                case 'status':
                    $pendaftarQuery->where('status_verifikasi', $filterValue);
                    break;
                case 'jenis_kelamin':
                    $pendaftarQuery->where('jenis_kelamin', $filterValue);
                    break;
                case 'jalur':
                    $pendaftarQuery->where('jalur_pendaftaran_id', $filterValue);
                    break;
                case 'gelombang':
                    $pendaftarQuery->where('gelombang_pendaftaran_id', $filterValue);
                    break;
                case 'pilihan_program':
                    $pendaftarQuery->where('pilihan_program', $filterValue);
                    break;
            }
        }
        
        $pendaftarList = $pendaftarQuery->orderBy('created_at', 'desc')->paginate(20);
        
        // Get jalur and gelombang for filter dropdowns
        $jalurList = $tahunAktif ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() : collect();
        $gelombangList = $tahunAktif ? GelombangPendaftaran::whereHas('jalurPendaftaran', function($q) use ($tahunAktif) {
            $q->where('tahun_pelajaran_id', $tahunAktif->id);
        })->get() : collect();
        
        return view('admin.statistik.index', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'totalPendaftar',
            'byStatus',
            'byJenisKelamin',
            'byJalur',
            'byGelombang',
            'byPilihanProgram',
            'trendPendaftaran',
            'pendaftarList',
            'filterType',
            'filterValue',
            'jalurList',
            'gelombangList'
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
        
        // Get jalur IDs for current tahun
        $jalurIds = $tahunAktif ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id') : collect();
        
        // By provinsi - use join with indonesia_provinces
        $byProvinsi = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('indonesia_provinces', 'calon_siswas.provinsi_id_siswa', '=', 'indonesia_provinces.code')
            ->select('indonesia_provinces.name as provinsi', 'indonesia_provinces.code as provinsi_code', DB::raw('count(*) as total'))
            ->whereNotNull('provinsi_id_siswa')
            ->groupBy('indonesia_provinces.code', 'indonesia_provinces.name')
            ->orderByDesc('total')
            ->get();
        
        // By kabupaten
        $filterProvinsi = $request->get('provinsi');
        $byKabupaten = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('indonesia_cities', 'calon_siswas.kabupaten_id_siswa', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->select('indonesia_cities.name as kabupaten', 'indonesia_cities.code as kabupaten_code', 'indonesia_provinces.name as provinsi', DB::raw('count(*) as total'))
            ->whereNotNull('kabupaten_id_siswa')
            ->when($filterProvinsi, function($q) use ($filterProvinsi) {
                $q->where('indonesia_provinces.code', $filterProvinsi);
            })
            ->groupBy('indonesia_cities.code', 'indonesia_cities.name', 'indonesia_provinces.name')
            ->orderByDesc('total')
            ->get();
        
        // By kecamatan
        $filterKabupaten = $request->get('kabupaten');
        $byKecamatan = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('indonesia_districts', 'calon_siswas.kecamatan_id_siswa', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->select('indonesia_districts.name as kecamatan', 'indonesia_districts.code as kecamatan_code', 'indonesia_cities.name as kabupaten', DB::raw('count(*) as total'))
            ->whereNotNull('kecamatan_id_siswa')
            ->when($filterKabupaten, function($q) use ($filterKabupaten) {
                $q->where('indonesia_cities.code', $filterKabupaten);
            })
            ->groupBy('indonesia_districts.code', 'indonesia_districts.name', 'indonesia_cities.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();
        
        // By kelurahan
        $filterKecamatan = $request->get('kecamatan');
        $byKelurahan = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->join('indonesia_villages', 'calon_siswas.kelurahan_id_siswa', '=', 'indonesia_villages.code')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->select('indonesia_villages.name as kelurahan', 'indonesia_districts.name as kecamatan', DB::raw('count(*) as total'))
            ->whereNotNull('kelurahan_id_siswa')
            ->when($filterKecamatan, function($q) use ($filterKecamatan) {
                $q->where('indonesia_districts.code', $filterKecamatan);
            })
            ->groupBy('indonesia_villages.code', 'indonesia_villages.name', 'indonesia_districts.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();
        
        // Data untuk peta (koordinat registrasi)
        $mapData = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->select('id', 'nama_lengkap', 'registration_latitude', 'registration_longitude', 'registration_address')
            ->get();
        
        return view('admin.statistik.geografis', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'byProvinsi',
            'byKabupaten',
            'byKecamatan',
            'byKelurahan',
            'mapData',
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
        $jalurIds = $tahunAktif ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->pluck('id') : collect();
        
        // By asal sekolah - using correct column names
        $byAsalSekolah = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->select('nama_sekolah_asal', 'npsn_asal_sekolah', DB::raw('count(*) as total'))
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->when($search, function($q) use ($search) {
                $q->where(function($q2) use ($search) {
                    $q2->where('nama_sekolah_asal', 'like', "%{$search}%")
                       ->orWhere('npsn_asal_sekolah', 'like', "%{$search}%");
                });
            })
            ->groupBy('nama_sekolah_asal', 'npsn_asal_sekolah')
            ->orderByDesc('total')
            ->paginate(20);
        
        // Top 10 sekolah
        $topSekolah = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->select('nama_sekolah_asal', 'npsn_asal_sekolah', DB::raw('count(*) as total'))
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->groupBy('nama_sekolah_asal', 'npsn_asal_sekolah')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        
        // Total sekolah unik
        $totalSekolah = CalonSiswa::query()
            ->when($tahunAktif, function($q) use ($jalurIds) {
                $q->whereIn('jalur_pendaftaran_id', $jalurIds);
            })
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->distinct('nama_sekolah_asal')
            ->count('nama_sekolah_asal');
        
        // Get selected school detail with pendaftar list
        $selectedSekolah = $request->get('sekolah');
        $pendaftarSekolah = null;
        if ($selectedSekolah) {
            $pendaftarSekolah = CalonSiswa::query()
                ->with(['jalurPendaftaran', 'gelombangPendaftaran'])
                ->when($tahunAktif, function($q) use ($jalurIds) {
                    $q->whereIn('jalur_pendaftaran_id', $jalurIds);
                })
                ->where('nama_sekolah_asal', $selectedSekolah)
                ->orderBy('nama_lengkap')
                ->paginate(20, ['*'], 'pendaftar_page');
        }
        
        return view('admin.statistik.asal-sekolah', compact(
            'tahunAktif',
            'tahunPelajaranList',
            'byAsalSekolah',
            'topSekolah',
            'totalSekolah',
            'search',
            'selectedSekolah',
            'pendaftarSekolah'
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
