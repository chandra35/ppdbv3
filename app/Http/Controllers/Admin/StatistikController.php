<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonDokumen;
use App\Models\CalonOrtu;
use App\Models\CalonSiswa;
use App\Support\AdminPpdbContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
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

    public function index(Request $request)
    {
        $context = $this->resolveContext($request);
        $query = $this->baseCalonSiswaQuery($context);

        $totalPendaftar = (clone $query)->count();

        $byStatus = (clone $query)
            ->select('status_verifikasi', DB::raw('count(*) as total'))
            ->groupBy('status_verifikasi')
            ->pluck('total', 'status_verifikasi')
            ->toArray();

        $byJenisKelamin = (clone $query)
            ->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kelamin')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin')
            ->toArray();

        $byJalur = (clone $query)
            ->join('jalur_pendaftaran', 'calon_siswas.jalur_pendaftaran_id', '=', 'jalur_pendaftaran.id')
            ->select('jalur_pendaftaran.nama', 'jalur_pendaftaran.warna', DB::raw('count(*) as total'))
            ->groupBy('jalur_pendaftaran.id', 'jalur_pendaftaran.nama', 'jalur_pendaftaran.warna')
            ->get();

        $byGelombang = (clone $query)
            ->join('gelombang_pendaftaran', 'calon_siswas.gelombang_pendaftaran_id', '=', 'gelombang_pendaftaran.id')
            ->select('gelombang_pendaftaran.nama', DB::raw('count(*) as total'))
            ->groupBy('gelombang_pendaftaran.id', 'gelombang_pendaftaran.nama')
            ->get();

        $byPilihanProgram = (clone $query)
            ->join('jalur_pendaftaran', 'calon_siswas.jalur_pendaftaran_id', '=', 'jalur_pendaftaran.id')
            ->select('calon_siswas.pilihan_program', DB::raw('count(*) as total'))
            ->where('jalur_pendaftaran.pilihan_program_aktif', true)
            ->whereNotNull('calon_siswas.pilihan_program')
            ->groupBy('calon_siswas.pilihan_program')
            ->pluck('total', 'pilihan_program')
            ->toArray();

        $trendPendaftaran = (clone $query)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('count(*) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $filterType = $request->get('filter_type');
        $filterValue = $request->get('filter_value');

        $pendaftarQuery = $this->baseCalonSiswaQuery($context)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran']);

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

        return view('admin.statistik.index', [
            'tahunAktif' => $context['selectedTahun'],
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'totalPendaftar' => $totalPendaftar,
            'byStatus' => $byStatus,
            'byJenisKelamin' => $byJenisKelamin,
            'byJalur' => $byJalur,
            'byGelombang' => $byGelombang,
            'byPilihanProgram' => $byPilihanProgram,
            'trendPendaftaran' => $trendPendaftaran,
            'pendaftarList' => $pendaftarList,
            'filterType' => $filterType,
            'filterValue' => $filterValue,
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->buildContextInfo($context),
            'showPilihanProgramStat' => !empty($byPilihanProgram),
        ]);
    }

    public function geografis(Request $request)
    {
        $context = $this->resolveContext($request);

        $filterProvinsi = $request->get('provinsi');
        $filterKabupaten = $request->get('kabupaten');
        $filterKecamatan = $request->get('kecamatan');

        $baseQuery = $this->baseCalonSiswaQuery($context);

        $byProvinsi = (clone $baseQuery)
            ->join('indonesia_provinces', 'calon_siswas.provinsi_id_siswa', '=', 'indonesia_provinces.code')
            ->select('indonesia_provinces.name as provinsi', 'indonesia_provinces.code as provinsi_code', DB::raw('count(*) as total'))
            ->whereNotNull('provinsi_id_siswa')
            ->groupBy('indonesia_provinces.code', 'indonesia_provinces.name')
            ->orderByDesc('total')
            ->get();

        $byKabupaten = (clone $baseQuery)
            ->join('indonesia_cities', 'calon_siswas.kabupaten_id_siswa', '=', 'indonesia_cities.code')
            ->join('indonesia_provinces', 'indonesia_cities.province_code', '=', 'indonesia_provinces.code')
            ->select('indonesia_cities.name as kabupaten', 'indonesia_cities.code as kabupaten_code', 'indonesia_provinces.name as provinsi', DB::raw('count(*) as total'))
            ->whereNotNull('kabupaten_id_siswa')
            ->when($filterProvinsi, function ($q) use ($filterProvinsi) {
                $q->where('indonesia_provinces.code', $filterProvinsi);
            })
            ->groupBy('indonesia_cities.code', 'indonesia_cities.name', 'indonesia_provinces.name')
            ->orderByDesc('total')
            ->get();

        $byKecamatan = (clone $baseQuery)
            ->join('indonesia_districts', 'calon_siswas.kecamatan_id_siswa', '=', 'indonesia_districts.code')
            ->join('indonesia_cities', 'indonesia_districts.city_code', '=', 'indonesia_cities.code')
            ->select('indonesia_districts.name as kecamatan', 'indonesia_districts.code as kecamatan_code', 'indonesia_cities.name as kabupaten', DB::raw('count(*) as total'))
            ->whereNotNull('kecamatan_id_siswa')
            ->when($filterKabupaten, function ($q) use ($filterKabupaten) {
                $q->where('indonesia_cities.code', $filterKabupaten);
            })
            ->groupBy('indonesia_districts.code', 'indonesia_districts.name', 'indonesia_cities.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $byKelurahan = (clone $baseQuery)
            ->join('indonesia_villages', 'calon_siswas.kelurahan_id_siswa', '=', 'indonesia_villages.code')
            ->join('indonesia_districts', 'indonesia_villages.district_code', '=', 'indonesia_districts.code')
            ->select('indonesia_villages.name as kelurahan', 'indonesia_districts.name as kecamatan', DB::raw('count(*) as total'))
            ->whereNotNull('kelurahan_id_siswa')
            ->when($filterKecamatan, function ($q) use ($filterKecamatan) {
                $q->where('indonesia_districts.code', $filterKecamatan);
            })
            ->groupBy('indonesia_villages.code', 'indonesia_villages.name', 'indonesia_districts.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $mapData = (clone $baseQuery)
            ->whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->select('id', 'nama_lengkap', 'registration_latitude', 'registration_longitude', 'registration_address')
            ->get();

        return view('admin.statistik.geografis', [
            'tahunAktif' => $context['selectedTahun'],
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'byProvinsi' => $byProvinsi,
            'byKabupaten' => $byKabupaten,
            'byKecamatan' => $byKecamatan,
            'byKelurahan' => $byKelurahan,
            'mapData' => $mapData,
            'filterProvinsi' => $filterProvinsi,
            'filterKabupaten' => $filterKabupaten,
            'filterKecamatan' => $filterKecamatan,
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->buildContextInfo($context),
        ]);
    }

    public function asalSekolah(Request $request)
    {
        $context = $this->resolveContext($request);
        $search = $request->get('search');
        $baseQuery = $this->baseCalonSiswaQuery($context);

        $byAsalSekolah = (clone $baseQuery)
            ->select('nama_sekolah_asal', 'npsn_asal_sekolah', 'nsm_asal_sekolah', DB::raw('count(*) as total'))
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama_sekolah_asal', 'like', "%{$search}%")
                        ->orWhere('npsn_asal_sekolah', 'like', "%{$search}%")
                        ->orWhere('nsm_asal_sekolah', 'like', "%{$search}%");
                });
            })
            ->groupBy('nama_sekolah_asal', 'npsn_asal_sekolah', 'nsm_asal_sekolah')
            ->orderByDesc('total')
            ->paginate(20);

        $topSekolah = (clone $baseQuery)
            ->select('nama_sekolah_asal', 'npsn_asal_sekolah', 'nsm_asal_sekolah', DB::raw('count(*) as total'))
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->groupBy('nama_sekolah_asal', 'npsn_asal_sekolah', 'nsm_asal_sekolah')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $totalSekolah = (clone $baseQuery)
            ->whereNotNull('nama_sekolah_asal')
            ->where('nama_sekolah_asal', '!=', '')
            ->distinct('nama_sekolah_asal')
            ->count('nama_sekolah_asal');

        $selectedSekolah = $request->get('sekolah');
        $pendaftarSekolah = null;
        if ($selectedSekolah) {
            $pendaftarSekolah = $this->baseCalonSiswaQuery($context)
                ->with(['jalurPendaftaran', 'gelombangPendaftaran'])
                ->where('nama_sekolah_asal', $selectedSekolah)
                ->orderBy('nama_lengkap')
                ->paginate(20, ['*'], 'pendaftar_page');
        }

        return view('admin.statistik.asal-sekolah', [
            'tahunAktif' => $context['selectedTahun'],
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'byAsalSekolah' => $byAsalSekolah,
            'topSekolah' => $topSekolah,
            'totalSekolah' => $totalSekolah,
            'search' => $search,
            'selectedSekolah' => $selectedSekolah,
            'pendaftarSekolah' => $pendaftarSekolah,
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->buildContextInfo($context),
        ]);
    }

    public function ekonomi(Request $request)
    {
        $context = $this->resolveContext($request);
        $calonSiswaIds = $this->baseCalonSiswaQuery($context)->pluck('id');

        $kategoriPendapatan = [
            'Tidak Ada' => [0, 0],
            '< Rp 1 Juta' => [1, 1000000],
            'Rp 1-3 Juta' => [1000001, 3000000],
            'Rp 3-5 Juta' => [3000001, 5000000],
            'Rp 5-10 Juta' => [5000001, 10000000],
            '> Rp 10 Juta' => [10000001, 999999999],
        ];

        $byPenghasilanAyah = [];
        $byPenghasilanIbu = [];
        foreach ($kategoriPendapatan as $label => $range) {
            $byPenghasilanAyah[$label] = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
                ->whereBetween('penghasilan_ayah', $range)
                ->count();
            $byPenghasilanIbu[$label] = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
                ->whereBetween('penghasilan_ibu', $range)
                ->count();
        }

        $byPekerjaanAyah = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pekerjaan_ayah', DB::raw('count(*) as total'))
            ->whereNotNull('pekerjaan_ayah')
            ->where('pekerjaan_ayah', '!=', '')
            ->groupBy('pekerjaan_ayah')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $byPekerjaanIbu = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pekerjaan_ibu', DB::raw('count(*) as total'))
            ->whereNotNull('pekerjaan_ibu')
            ->where('pekerjaan_ibu', '!=', '')
            ->groupBy('pekerjaan_ibu')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $byPendidikanAyah = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pendidikan_ayah', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_ayah')
            ->where('pendidikan_ayah', '!=', '')
            ->groupBy('pendidikan_ayah')
            ->orderByDesc('total')
            ->get();

        $byPendidikanIbu = CalonOrtu::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('pendidikan_ibu', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_ibu')
            ->where('pendidikan_ibu', '!=', '')
            ->groupBy('pendidikan_ibu')
            ->orderByDesc('total')
            ->get();

        return view('admin.statistik.ekonomi', [
            'tahunAktif' => $context['selectedTahun'],
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'byPenghasilanAyah' => $byPenghasilanAyah,
            'byPenghasilanIbu' => $byPenghasilanIbu,
            'byPekerjaanAyah' => $byPekerjaanAyah,
            'byPekerjaanIbu' => $byPekerjaanIbu,
            'byPendidikanAyah' => $byPendidikanAyah,
            'byPendidikanIbu' => $byPendidikanIbu,
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->buildContextInfo($context),
        ]);
    }

    public function dokumenPrestasi(Request $request)
    {
        $context = $this->resolveContext($request);
        $calonSiswaIds = $this->baseCalonSiswaQuery($context)->pluck('id');
        $totalPendaftar = $calonSiswaIds->count();

        $dokumenTambahanTypes = CalonDokumen::getPrestasiDocumentTypes();

        $byJenisDokumen = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->select('jenis_dokumen', DB::raw('count(*) as total'))
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->groupBy('jenis_dokumen')
            ->get()
            ->map(function ($item) use ($dokumenTambahanTypes) {
                $item->label = $dokumenTambahanTypes[$item->jenis_dokumen] ?? $item->jenis_dokumen;
                return $item;
            });

        $pendaftarDenganPrestasi = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');

        $byStatusDokumen = CalonDokumen::whereIn('calon_siswa_id', $calonSiswaIds)
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes))
            ->select('status_verifikasi', DB::raw('count(*) as total'))
            ->groupBy('status_verifikasi')
            ->pluck('total', 'status_verifikasi')
            ->toArray();

        $detailPrestasi = CalonSiswa::whereIn('id', $calonSiswaIds)
            ->whereHas('dokumen', function ($q) use ($dokumenTambahanTypes) {
                $q->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes));
            })
            ->with(['dokumen' => function ($q) use ($dokumenTambahanTypes) {
                $q->whereIn('jenis_dokumen', array_keys($dokumenTambahanTypes));
            }])
            ->select('id', 'nama_lengkap', 'nama_sekolah_asal', 'npsn_asal_sekolah', 'nsm_asal_sekolah')
            ->paginate(20);

        return view('admin.statistik.dokumen-prestasi', [
            'tahunAktif' => $context['selectedTahun'],
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'totalPendaftar' => $totalPendaftar,
            'pendaftarDenganPrestasi' => $pendaftarDenganPrestasi,
            'byJenisDokumen' => $byJenisDokumen,
            'byStatusDokumen' => $byStatusDokumen,
            'detailPrestasi' => $detailPrestasi,
            'dokumenTambahanTypes' => $dokumenTambahanTypes,
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->buildContextInfo($context),
        ]);
    }

    public function export(Request $request, $type)
    {
        return back()->with('info', 'Fitur export sedang dalam pengembangan');
    }

    private function resolveContext(Request $request): array
    {
        return AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
    }

    private function baseCalonSiswaQuery(array $context): Builder
    {
        return CalonSiswa::query()
            ->when($context['selectedTahun'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.tahun_pelajaran_id', $context['selectedTahun']->id);
            })
            ->when($context['jalurFilterId'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.jalur_pendaftaran_id', $context['jalurFilterId']);
            })
            ->when($context['gelombangFilterId'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
    }

    private function buildContextInfo(array $context): array
    {
        return [
            'tahun' => $context['selectedTahun']?->nama ?? '-',
            'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
            'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
        ];
    }
}
