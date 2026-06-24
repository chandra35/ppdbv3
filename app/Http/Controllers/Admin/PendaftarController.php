<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\DokumenVerifikasiHistory;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Role;
use App\Services\EmisNisnService;
use App\Services\NpsnService;
use App\Services\KopSuratService;
use App\Services\NomorService;
use App\Services\DocumentStorageService;
use App\Support\AdminPpdbContext;
use App\Exports\PendaftarExport;
use App\Exports\MoodlePendaftarExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\Province;

class PendaftarController extends Controller
{
    protected $kopSuratService;
    protected $nomorService;
    protected $emisService;
    protected $documentStorageService;

    public function __construct(KopSuratService $kopSuratService, NomorService $nomorService, EmisNisnService $emisService, DocumentStorageService $documentStorageService)
    {
        $this->kopSuratService = $kopSuratService;
        $this->nomorService = $nomorService;
        $this->emisService = $emisService;
        $this->documentStorageService = $documentStorageService;
    }

    /**
     * Check permission - admin selalu bisa akses, user lain cek permission
     */
    private function checkPermission(string $permission): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        if ($user->isAdmin()) {
            return; // Admin selalu punya akses
        }
        
        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }
    }

    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $tahunAktif = $context['selectedTahun'];
        $selectedJalurId = $context['selectedJalurIdInput'];
        $selectedGelombangId = $context['selectedGelombangIdInput'];
        
        $query = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'dokumen']);

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['nama_lengkap', 'nisn', 'nomor_registrasi', 'nomor_tes', 'created_at', 'status_verifikasi', 'dokumen_count'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        if ($sortBy === 'dokumen_count') {
            $query->withCount('dokumen')->orderBy('dokumen_count', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Filter by jalur
        if ($context['jalurFilterId']) {
            $query->where('jalur_pendaftaran_id', $context['jalurFilterId']);
        }

        // Filter by gelombang
        if ($context['gelombangFilterId']) {
            $query->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'has_nomor_tes') {
                $query->whereNotNull('nomor_tes');
            } else {
                $query->where('status_verifikasi', $request->status);
            }
        }

        // Filter by custom filters from dashboard
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'belum_lengkap':
                    // Belum lengkap: semua yang belum punya nomor tes (Total - Mendapatkan No.Tes)
                    $query->whereNull('nomor_tes');
                    break;
                    
                case 'siap_verifikasi':
                    // Siap verifikasi: sudah upload Rapor Sem 1-5, KK, Foto, Kartu Pelajar, belum dapat nomor tes
                    $query->whereNull('nomor_tes')
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_sem_1'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_sem_2'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_sem_3'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_sem_4'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_sem_5'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kk'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); })
                        ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kartu_pelajar'); });
                    break;
                    
                case 'hanya_mendaftar':
                    // Hanya mendaftar: hanya register saja tanpa upload file apapun
                    $query->whereNull('nomor_tes')
                        ->whereDoesntHave('dokumen');
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%");
            });
        }

        // Pagination with flexible per_page option
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $pendaftars = $query->get();
            // Wrap in a custom paginator for view compatibility
            $pendaftars = new \Illuminate\Pagination\LengthAwarePaginator(
                $pendaftars,
                $pendaftars->count(),
                $pendaftars->count() > 0 ? $pendaftars->count() : 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $perPage = in_array((int)$perPage, [20, 50, 100]) ? (int)$perPage : 20;
            $pendaftars = $query->paginate($perPage);
        }
        
        // Get jalur list for filter - prioritize active tahun pelajaran
        $jalurList = $context['jalurs'];
        $gelombangList = $context['allGelombangs'];

        return view('admin.pendaftar.index', compact(
            'pendaftars',
            'jalurList',
            'gelombangList',
            'selectedJalurId',
            'selectedGelombangId',
            'sortBy',
            'sortDir'
        ) + [
            'tahunAktif' => $tahunAktif,
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Export pendaftar data to Excel
     */
    public function export(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $type = $request->get('type', 'all'); // 'all' or 'with_nomor_tes'
        $tahunAktif = $context['selectedTahun'];
        $jalurId = $context['jalurFilterId'];
        $gelombangId = $context['gelombangFilterId'];
        
        $tahunLabel = $tahunAktif ? str_replace('/', '-', $tahunAktif->nama) : date('Y');
        
        $filename = $type === 'with_nomor_tes' 
            ? "Peserta_Ujian_PPDB_{$tahunLabel}.xlsx"
            : "Data_Pendaftar_PPDB_{$tahunLabel}.xlsx";
        
        return Excel::download(
            new PendaftarExport($type, $tahunAktif?->id, $jalurId, $gelombangId),
            $filename
        );
    }

    /**
     * Export Moodle-compatible XLSX for pendaftar with nomor tes
     * For uploading to Moodle (students who got nomor tes on exam day)
     */
    public function exportMoodle(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $tahunNama = $tahunAktif->nama ?? date('Y');
        $tahunShort = preg_match('/(\d{4})\/\d{4}/', $tahunNama, $m) ? $m[1] : date('Y');

        $jalurId = $context['jalurFilterId'];
        $gelombangId = $context['gelombangFilterId'];

        $filename = 'moodle-pendaftar-' . $tahunShort . '.xlsx';

        return Excel::download(
            new MoodlePendaftarExport($tahunShort, $tahunAktif?->id, $jalurId, $gelombangId),
            $filename
        );
    }

    /**
     * Show map of registration locations
     */
    public function map(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];

        $query = CalonSiswa::query()
            ->whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->with(['jalurPendaftaran']);

        if ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }
        
        if ($context['jalurFilterId']) {
            $query->where('jalur_pendaftaran_id', $context['jalurFilterId']);
        }

        if ($context['gelombangFilterId']) {
            $query->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
        }
        
        $pendaftars = $query->select([
            'id', 'nama_lengkap', 'nomor_registrasi', 'nisn',
            'registration_latitude', 'registration_longitude',
            'registration_altitude', 'registration_accuracy',
            'registration_address', 'registration_city', 'registration_region',
            'registration_device', 'tanggal_registrasi',
            'jalur_pendaftaran_id', 'status_verifikasi'
        ])->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama_lengkap,
                'nomor_registrasi' => $p->nomor_registrasi,
                'nisn' => $p->nisn,
                'lat' => (float) $p->registration_latitude,
                'lng' => (float) $p->registration_longitude,
                'altitude' => $p->registration_altitude,
                'accuracy' => $p->registration_accuracy,
                'address' => $p->registration_address,
                'city' => $p->registration_city,
                'region' => $p->registration_region,
                'device' => $p->registration_device,
                'tanggal' => $p->tanggal_registrasi ? $p->tanggal_registrasi->format('d M Y H:i') : '-',
                'jalur' => $p->jalurPendaftaran?->nama ?? '-',
                'warna_jalur' => $p->jalurPendaftaran?->warna ?? '#007bff',
                'status' => $p->status_verifikasi,
            ];
        });
        
        $jalurList = $context['jalurs'];
        
        return view('admin.pendaftar.map', compact('pendaftars', 'jalurList') + [
            'tahunAktif' => $tahunAktif,
            'tahunPelajaranList' => $context['tahunPelajarans'],
            'selectedJalurId' => $context['selectedJalurIdInput'],
            'selectedGelombangId' => $context['selectedGelombangIdInput'],
            'gelombangList' => $context['allGelombangs'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Show form to create new pendaftar (Manual Registration)
     */
    public function create()
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.create')) {
            abort(403, 'Anda tidak memiliki izin untuk menambah pendaftar');
        }

        $context = AdminPpdbContext::resolve(request('tahun_pelajaran_id'), request('jalur_id'), request('gelombang_id'));

        // Get tahun pelajaran aktif / konteks
        $tahunPelajaran = $context['selectedTahun'];
        if (!$tahunPelajaran) {
            return redirect()->route('admin.pendaftar.index')
                ->with('error', 'Tidak ada tahun pelajaran aktif. Silakan aktifkan tahun pelajaran terlebih dahulu.');
        }

        // Get jalur pendaftaran pada tahun konteks (aktif atau tidak untuk manual input)
        $jalurList = JalurPendaftaran::where('tahun_pelajaran_id', $tahunPelajaran->id)
            ->with('gelombang')
            ->orderBy('urutan')
            ->get();

        if ($jalurList->isEmpty()) {
            return redirect()->route('admin.pendaftar.index')
                ->with('error', 'Tidak ada jalur pendaftaran. Silakan buat jalur pendaftaran terlebih dahulu.');
        }

        // Get provinces for address selection
        $provinces = Province::orderBy('name')->get();

        $contextInfo = [
            'tahun' => $context['selectedTahun']?->nama ?? '-',
            'jalur' => $context['selectedJalur']?->nama ?? 'Belum dipilih',
            'gelombang' => $context['selectedGelombang']?->nama ?? 'Belum dipilih',
        ];

        return view('admin.pendaftar.create', compact(
            'tahunPelajaran',
            'jalurList',
            'provinces',
            'contextInfo'
        ) + [
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
        ]);
    }

    public function cekNisnManual(Request $request)
    {
        $this->checkPermission('pendaftar.create');

        $request->validate([
            'nisn' => 'required|string|size:10',
        ]);

        $nisn = $request->nisn;

        $existing = CalonSiswa::where('nisn', $nisn)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'NISN sudah terdaftar. Silakan buka data pendaftar yang sudah ada.',
                'already_registered' => true,
                'existing_id' => $existing->id,
            ], 422);
        }

        try {
            $result = $this->emisService->cekNisn($nisn);

            if (!($result['success'] ?? false) || !($result['data'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'NISN tidak ditemukan di Kemdikbud/Kemenag. Operator tetap bisa melanjutkan input manual.',
                    'manual_allowed' => true,
                ], 404);
            }

            $transformed = $this->transformEmisDataForManualForm($nisn, $result['data']);
            $sources = [];
            if (!empty($result['data']['kemdikbud'])) {
                $sources[] = 'Kemdikbud Pusdatin';
            }
            if (!empty($result['data']['kemenag'])) {
                $sources[] = 'Kemenag PPDB';
            }

            return response()->json([
                'success' => true,
                'message' => 'Data NISN ditemukan dan siap mengisi form otomatis.',
                'data' => $transformed,
                'sources' => $sources,
                'source_label' => implode(' & ', $sources),
                'is_eligible' => $transformed['tingkat_pendidikan'] === 9,
                'warning' => $transformed['tingkat_pendidikan'] !== 9 && $transformed['tingkat_pendidikan']
                    ? 'Status kelas terdeteksi bukan kelas 9. Mohon periksa kembali sebelum menyimpan.'
                    : null,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi layanan cek NISN. Operator tetap bisa lanjut input manual.',
                'manual_allowed' => true,
            ], 500);
        }
    }

    private function transformEmisDataForManualForm(string $nisn, array $emisData): array
    {
        $kemdikbud = $emisData['kemdikbud'] ?? null;
        $kemenag = $emisData['kemenag'] ?? null;

        $tingkatPendidikan = null;
        $levelName = null;

        if ($kemdikbud && isset($kemdikbud['tingkat_pendidikan'])) {
            $tingkatPendidikan = (int) $kemdikbud['tingkat_pendidikan'];
        } elseif ($kemenag && isset($kemenag['level_id'])) {
            $tingkatPendidikan = (int) $kemenag['level_id'];
            $levelName = $kemenag['level_name'] ?? null;
        }

        $jenisKelamin = null;
        if (isset($kemdikbud['jenis_kelamin'])) {
            $jenisKelamin = $kemdikbud['jenis_kelamin'];
        } elseif (isset($kemenag['gender_id'])) {
            $jenisKelamin = (string) $kemenag['gender_id'] === '1' ? 'L' : 'P';
        }

        $npsnValue = $kemdikbud['npsn'] ?? ($kemenag['npsn'] ?? null);
        $npsnDetail = null;
        if ($npsnValue && strlen($npsnValue) === 8) {
            try {
                $npsnService = app(NpsnService::class);
                $npsnResult = $npsnService->cekNpsn($npsnValue);
                if (($npsnResult['success'] ?? false) && !empty($npsnResult['data'])) {
                    $npsnDetail = $npsnResult['data'];
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'nisn' => $kemdikbud['nisn'] ?? ($kemenag['nisn'] ?? $nisn),
            'nik' => $kemdikbud['nik'] ?? ($kemenag['nik'] ?? null),
            'nama_lengkap' => $kemdikbud['nama'] ?? ($kemenag['full_name'] ?? null),
            'tempat_lahir' => $kemdikbud['tempat_lahir'] ?? ($kemenag['birth_place'] ?? null),
            'tanggal_lahir' => $kemdikbud['tanggal_lahir'] ?? ($kemenag['birth_date'] ?? null),
            'jenis_kelamin' => $jenisKelamin,
            'agama' => $this->mapAgama($kemenag['religion_id'] ?? null),
            'nama_sekolah_asal' => $npsnDetail['nama_sekolah'] ?? ($kemdikbud['sekolah'] ?? ($kemenag['institution_name'] ?? null)),
            'npsn_asal_sekolah' => $npsnValue,
            'nsm_asal_sekolah' => $kemenag['institution_nsm'] ?? null,
            'alamat_siswa' => $kemenag['address'] ?? null,
            'rt_siswa' => $kemenag['rt'] ?? null,
            'rw_siswa' => $kemenag['rw'] ?? null,
            'kodepos_siswa' => $kemenag['postal_code'] ?? null,
            'provinsi_id_siswa' => $kemenag['province_code'] ?? null,
            'kabupaten_id_siswa' => $kemenag['city_code'] ?? null,
            'kecamatan_id_siswa' => $kemenag['district_code'] ?? null,
            'kelurahan_id_siswa' => $kemenag['village_code'] ?? null,
            'nama_ayah' => $kemenag['father_name'] ?? null,
            'nama_ibu' => $kemdikbud['nama_ibu_kandung'] ?? ($kemenag['mother_name'] ?? null),
            'status_dalam_keluarga' => $kemenag['child_status'] ?? null,
            'anak_ke' => $kemenag['child_number'] ?? null,
            'jumlah_saudara' => $kemenag['sibling_count'] ?? null,
            'transportasi' => $kemenag['transportation'] ?? null,
            'jarak_ke_sekolah' => $kemenag['distance_to_school'] ?? null,
            'status_sekolah_asal' => isset($npsnDetail['status']) ? strtoupper($npsnDetail['status']) : null,
            'bentuk_sekolah_asal' => $npsnDetail['bentuk_pendidikan'] ?? null,
            'akreditasi_sekolah_asal' => $npsnDetail['akreditasi'] ?? null,
            'alamat_sekolah_asal' => $npsnDetail['alamat'] ?? null,
            'kelurahan_sekolah_asal' => $npsnDetail['kelurahan'] ?? null,
            'kecamatan_sekolah_asal' => $npsnDetail['kecamatan'] ?? null,
            'kabupaten_sekolah_asal' => $npsnDetail['kabupaten'] ?? null,
            'provinsi_sekolah_asal' => $npsnDetail['provinsi'] ?? null,
            'tingkat_pendidikan' => $tingkatPendidikan,
            'level_name' => $levelName,
        ];
    }

    private function mapAgama($religionId): ?string
    {
        return match ((string) $religionId) {
            '1' => 'Islam',
            '2' => 'Kristen',
            '3' => 'Katolik',
            '4' => 'Hindu',
            '5' => 'Buddha',
            '6' => 'Konghucu',
            default => null,
        };
    }

    private function detectBrowserName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        if (preg_match('/MSIE/i', $userAgent) || preg_match('/Trident/i', $userAgent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Microsoft Edge';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Chrome/i', $userAgent)) {
            if (preg_match('/OPR/i', $userAgent)) {
                return 'Opera';
            }

            return 'Chrome';
        }
        if (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Opera/i', $userAgent)) {
            return 'Opera';
        }

        return Str::limit($userAgent, 100, '');
    }

    /**
     * Store new pendaftar (Manual Registration by Admin/Verifikator)
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.create')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambah pendaftar'
            ], 403);
        }

        // Validate request
        $request->validate([
            // Data Diri Wajib
            'nisn' => 'required|string|size:10|unique:calon_siswas,nisn',
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|string',
            'nomor_hp' => 'required|string|max:20',
            
            // Alamat Siswa
            'alamat_siswa' => 'required|string',
            'provinsi_id_siswa' => 'required|string',
            'kabupaten_id_siswa' => 'required|string',
            'kecamatan_id_siswa' => 'required|string',
            'kelurahan_id_siswa' => 'required|string',
            
            // PPDB Selection
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'required|exists:gelombang_pendaftaran,id',
            
            // Data Orang Tua Wajib
            'nama_ayah' => 'required|string|max:100',
            'nama_ibu' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email|unique:calon_siswas,email',
        ], [
            'nisn.unique' => 'NISN sudah terdaftar dalam sistem',
            'nisn.size' => 'NISN harus 10 digit',
        ]);

        // Check if phone number already registered
        $phoneNormalized = $this->normalizePhoneNumber($request->nomor_hp);
        $existingPhone = CalonSiswa::where(function($query) use ($phoneNormalized, $request) {
            $query->where('nomor_hp', $phoneNormalized)
                  ->orWhere('nomor_hp', $request->nomor_hp)
                  ->orWhere('nomor_hp', '+62' . ltrim($request->nomor_hp, '0'))
                  ->orWhere('nomor_hp', '0' . substr($phoneNormalized, 3));
        })->first();
        
        if ($existingPhone) {
            return back()->withErrors(['nomor_hp' => 'Nomor WhatsApp sudah digunakan oleh pendaftar lain.'])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Get jalur & gelombang
            $jalur = JalurPendaftaran::findOrFail($request->jalur_pendaftaran_id);
            $gelombang = GelombangPendaftaran::findOrFail($request->gelombang_pendaftaran_id);
            $tahunPelajaran = TahunPelajaran::find($jalur->tahun_pelajaran_id) ?? TahunPelajaran::active()->first();

            if ($gelombang->jalur_id !== $jalur->id) {
                return back()->withErrors([
                    'gelombang_pendaftaran_id' => 'Gelombang tidak sesuai dengan jalur yang dipilih.'
                ])->withInput();
            }

            // Generate nomor registrasi dari service agar konsisten dengan rule baru dan fallback legacy
            $nomorRegistrasi = $this->nomorService->generateNomorRegistrasi(
                new CalonSiswa([
                    'jalur_pendaftaran_id' => $jalur->id,
                    'gelombang_pendaftaran_id' => $gelombang->id,
                    'tahun_pelajaran_id' => $tahunPelajaran->id,
                ])
            );
            
            // Generate username & password
            $username = $request->nisn;
            $password = $this->generateSecurePassword(8);
            $hashedPassword = Hash::make($password);
            $email = trim((string) $request->email) !== '' ? trim((string) $request->email) : ($request->nisn . '@ppdb.local');

            // Create user account
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $email,
                'password' => $hashedPassword,
            ]);
            
            // Store encrypted password for printing kartu ujian
            $user->readable_password = $password;
            $user->save();

            // Assign pendaftar role
            $pendaftarRole = Role::where('name', 'pendaftar')->first();
            if ($pendaftarRole) {
                $user->roles()->attach($pendaftarRole->id);
            }

            // Normalize phone number
            $nomorHp = $request->nomor_hp;
            if (str_starts_with($nomorHp, '08')) {
                $nomorHp = '+62' . substr($nomorHp, 1);
            }

            // Create calon siswa
            $calonSiswa = CalonSiswa::create([
                'user_id' => $user->id,
                'tahun_pelajaran_id' => $tahunPelajaran->id,
                'jalur_pendaftaran_id' => $jalur->id,
                'gelombang_pendaftaran_id' => $gelombang->id,
                'nomor_registrasi' => $nomorRegistrasi,
                
                // Data Diri
                'nisn' => $request->nisn,
                'nisn_valid' => false, // Manual input, tidak divalidasi API
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'nomor_hp' => $nomorHp,
                'email' => $email,
                
                // Data Tambahan
                'jumlah_saudara' => $request->jumlah_saudara,
                'anak_ke' => $request->anak_ke,
                'hobi' => $request->hobi,
                'cita_cita' => $request->cita_cita,
                
                // Alamat Siswa
                'alamat_siswa' => $request->alamat_siswa,
                'rt_siswa' => $request->rt_siswa,
                'rw_siswa' => $request->rw_siswa,
                'provinsi_id_siswa' => $request->provinsi_id_siswa,
                'kabupaten_id_siswa' => $request->kabupaten_id_siswa,
                'kecamatan_id_siswa' => $request->kecamatan_id_siswa,
                'kelurahan_id_siswa' => $request->kelurahan_id_siswa,
                'kodepos_siswa' => $request->kodepos_siswa,
                
                // Asal Sekolah
                'npsn_asal_sekolah' => $request->npsn_asal_sekolah,
                'nama_sekolah_asal' => $request->nama_sekolah_asal,
                'status_sekolah_asal' => $request->status_sekolah_asal,
                'bentuk_sekolah_asal' => $request->bentuk_sekolah_asal,
                'akreditasi_sekolah_asal' => $request->akreditasi_sekolah_asal,
                'alamat_sekolah_asal' => $request->alamat_sekolah_asal,
                'kelurahan_sekolah_asal' => $request->kelurahan_sekolah_asal,
                'kecamatan_sekolah_asal' => $request->kecamatan_sekolah_asal,
                'kabupaten_sekolah_asal' => $request->kabupaten_sekolah_asal,
                'provinsi_sekolah_asal' => $request->provinsi_sekolah_asal,
                
                // Status
                'status_verifikasi' => 'pending',
                'tanggal_registrasi' => now(),
                'data_diri_completed' => true,
                
                // GPS location (from admin's browser if available)
                'registration_latitude' => $request->registration_latitude,
                'registration_longitude' => $request->registration_longitude,
                'registration_ip' => $request->ip(),
                'registration_device' => 'Admin Manual Input',
                'registration_browser' => $this->detectBrowserName($request->userAgent()),
            ]);

            // Create calon ortu record
            $calonOrtu = CalonOrtu::create([
                'calon_siswa_id' => $calonSiswa->id,
                'no_kk' => $request->no_kk,
                
                // Data Ayah
                'nama_ayah' => $request->nama_ayah,
                'nik_ayah' => $request->nik_ayah,
                'tempat_lahir_ayah' => $request->tempat_lahir_ayah,
                'tanggal_lahir_ayah' => $request->tanggal_lahir_ayah,
                'pendidikan_ayah' => $request->pendidikan_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'penghasilan_ayah' => $request->penghasilan_ayah,
                'hp_ayah' => $request->hp_ayah,
                
                // Data Ibu
                'nama_ibu' => $request->nama_ibu,
                'nik_ibu' => $request->nik_ibu,
                'tempat_lahir_ibu' => $request->tempat_lahir_ibu,
                'tanggal_lahir_ibu' => $request->tanggal_lahir_ibu,
                'pendidikan_ibu' => $request->pendidikan_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'penghasilan_ibu' => $request->penghasilan_ibu,
                'hp_ibu' => $request->hp_ibu,
                
                // Alamat Ortu (sama dengan siswa jika copy)
                'alamat_ortu' => $request->copy_alamat_to_ortu ? $request->alamat_siswa : $request->alamat_ortu,
                'rt_ortu' => $request->copy_alamat_to_ortu ? $request->rt_siswa : $request->rt_ortu,
                'rw_ortu' => $request->copy_alamat_to_ortu ? $request->rw_siswa : $request->rw_ortu,
                'provinsi_id' => $request->copy_alamat_to_ortu ? $request->provinsi_id_siswa : $request->provinsi_id_ortu,
                'kabupaten_id' => $request->copy_alamat_to_ortu ? $request->kabupaten_id_siswa : $request->kabupaten_id_ortu,
                'kecamatan_id' => $request->copy_alamat_to_ortu ? $request->kecamatan_id_siswa : $request->kecamatan_id_ortu,
                'kelurahan_id' => $request->copy_alamat_to_ortu ? $request->kelurahan_id_siswa : $request->kelurahan_id_ortu,
                'kodepos' => $request->copy_alamat_to_ortu ? $request->kodepos_siswa : $request->kodepos_ortu,
            ]);

            // Update completion flags
            $calonSiswa->update([
                'data_ortu_completed' => true,
            ]);

            app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
                $calonSiswa->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
                \App\Services\MoodleIntegrationService::TRIGGER_REGISTER
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'App\Models\CalonSiswa',
                'model_id' => $calonSiswa->id,
                'description' => "Menambahkan pendaftar baru secara manual: {$calonSiswa->nama_lengkap} (NISN: {$calonSiswa->nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            // Return success with credentials
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftar berhasil ditambahkan',
                    'data' => [
                        'id' => $calonSiswa->id,
                        'nomor_registrasi' => $nomorRegistrasi,
                        'nama_lengkap' => $calonSiswa->nama_lengkap,
                        'nisn' => $calonSiswa->nisn,
                        'username' => $username,
                        'password' => $password, // Plain password for printing
                        'jalur' => $jalur->nama,
                        'gelombang' => $gelombang->nama,
                    ]
                ]);
            }

            return redirect()->route('admin.pendaftar.show', $calonSiswa->id)
                ->with('success', 'Pendaftar berhasil ditambahkan. Username: ' . $username . ', Password: ' . $password);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan pendaftar: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan pendaftar: ' . $e->getMessage());
        }
    }

    public function getDokumenList($id)
    {
        $pendaftar = CalonSiswa::with(['dokumen.verifiedBy'])->findOrFail($id);
        
        $dokumen = $pendaftar->dokumen->map(function ($dok) {
            return [
                'id' => $dok->id,
                'jenis_dokumen' => $dok->jenis_dokumen,
                'nama_dokumen_lengkap' => $dok->nama_dokumen_lengkap,
                'status_verifikasi' => $dok->status_verifikasi,
                'catatan_verifikasi' => $dok->catatan_verifikasi,
                'verified_by_name' => $dok->verifiedBy ? $dok->verifiedBy->name : null,
                'verified_at' => $dok->verified_at,
                'file_path' => $dok->file_path,
                'file_url' => $dok->file_url,
                'mime_type' => $dok->mime_type,
                'file_size' => $dok->file_size_formatted,
                'nama_file' => $dok->nama_file,
            ];
        });

        return response()->json([
            'success' => true,
            'dokumen' => $dokumen
        ]);
    }

    public function show($id)
    {
        $pendaftar = CalonSiswa::with([
            'user', 
            'ortu.provinsiOrtu',
            'ortu.kabupatenOrtu',
            'ortu.kecamatanOrtu',
            'ortu.kelurahanOrtu',
            'dokumen.histories.user',
            'dokumen.verifiedBy',
            'dokumen.revisedBy',
            'dokumen.cancelledBy',
            'jalurPendaftaran', 
            'gelombangPendaftaran',
            'riwayatGelombang.dariGelombang',
            'riwayatGelombang.keGelombang',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa',
            'nilaiRapor'
        ])->findOrFail($id);
        
        // Get active documents from settings
        $settings = \App\Models\PpdbSettings::first();
        $requiredDocs = $settings?->dokumen_aktif ?? ['kk', 'akta_lahir', 'ijazah', 'foto'];
        
        // Get location tracking setting
        $wajibLokasiRegistrasi = $settings?->wajib_lokasi_registrasi ?? false;
        
        // Map document types to labels
        $dokumenLabels = CalonDokumen::DOKUMEN_UTAMA;
        $adminUploadDokumenOptions = CalonDokumen::getAdminUploadOptions();
        $adminUploadDokumenGroups = CalonDokumen::getAdminUploadOptionGroups();
        
        // Get dokumen tambahan
        $dokumenTambahanOptions = \App\Models\CalonDokumen::DOKUMEN_TAMBAHAN;
        $dokumenTambahan = $pendaftar->dokumen
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanOptions))
            ->values();
        $nomorTesUndoInfo = $this->getNomorTesUndoInfo($pendaftar);
        
        return view('admin.pendaftar.show', compact('pendaftar', 'requiredDocs', 'dokumenLabels', 'dokumenTambahanOptions', 'dokumenTambahan', 'wajibLokasiRegistrasi', 'adminUploadDokumenOptions', 'adminUploadDokumenGroups', 'nomorTesUndoInfo'));
    }

    public function verify(Request $request, $id)
    {
        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;
        
        $pendaftar->update([
            'status_verifikasi' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        ActivityLog::log('verify', "Memverifikasi pendaftar: {$pendaftar->nama_lengkap}", $pendaftar, 
            ['status' => $oldStatus], ['status' => 'verified']);

        return redirect()->back()->with('success', 'Pendaftar berhasil diverifikasi.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        $pendaftar->update([
            'status_verifikasi' => 'rejected',
            'rejection_reason' => $request->alasan,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        ActivityLog::log('reject', "Menolak pendaftar: {$pendaftar->nama_lengkap}. Alasan: {$request->alasan}", $pendaftar,
            ['status' => $oldStatus], ['status' => 'rejected']);

        return redirect()->back()->with('warning', 'Pendaftar ditolak.');
    }

    public function approve(Request $request, $id)
    {
        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        $pendaftar->update([
            'status_verifikasi' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        ActivityLog::log('approve', "Menerima pendaftar: {$pendaftar->nama_lengkap}", $pendaftar,
            ['status' => $oldStatus], ['status' => 'approved']);

        return redirect()->back()->with('success', 'Pendaftar diterima.');
    }

    public function verifikasiDokumen(Request $request)
    {
        $query = CalonSiswa::with(['dokumen'])
            ->whereHas('dokumen')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->whereHas('dokumen', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        $pendaftars = $query->paginate(20);

        return view('admin.pendaftar.verifikasi-dokumen', compact('pendaftars'));
    }

    public function verifikasiDokumenDetail($id)
    {
        $pendaftar = CalonSiswa::with(['dokumen'])->findOrFail($id);
        return view('admin.pendaftar.verifikasi-dokumen-detail', compact('pendaftar'));
    }

    public function updateVerifikasiDokumen(Request $request, $id)
    {
        $dokumen = CalonDokumen::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,verified,rejected',
            'catatan' => 'nullable|string|max:500',
        ]);

        $oldStatus = $dokumen->status;

        $dokumen->update([
            'status' => $request->status,
            'catatan_verifikasi' => $request->catatan,
            'verified_at' => $request->status === 'verified' ? now() : null,
            'verified_by' => $request->status === 'verified' ? auth()->id() : null,
        ]);

        ActivityLog::log('update', "Memverifikasi dokumen {$dokumen->jenis_dokumen}", $dokumen,
            ['status' => $oldStatus], ['status' => $request->status]);

        return redirect()->back()->with('success', 'Verifikasi dokumen berhasil diupdate.');
    }

    public function approveDokumen($id)
    {
        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        $dokumen->update([
            'status_verifikasi' => 'valid',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'approve',
            'status_from' => $oldStatus,
            'status_to' => 'valid',
            'keterangan' => 'Dokumen disetujui',
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disetujui.',
                'status' => 'valid'
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui.');
    }

    public function rejectDokumen(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        $dokumen->update([
            'status_verifikasi' => 'invalid',
            'catatan_verifikasi' => $request->catatan,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'reject',
            'status_from' => $oldStatus,
            'status_to' => 'invalid',
            'keterangan' => $request->catatan,
        ]);
        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditolak.',
                'status' => 'invalid'
            ]);
        }

        return redirect()->back()->with('warning', 'Dokumen ditolak.');
    }

    public function revisiDokumen(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        $calonSiswa = $dokumen->calonSiswa;
        
        $dokumen->update([
            'status_verifikasi' => 'revision',
            'catatan_verifikasi' => $request->catatan,
            'revised_by' => auth()->id(),
            'revised_at' => now(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'revisi',
            'status_from' => $oldStatus,
            'status_to' => 'revision',
            'keterangan' => $request->catatan,
        ]);

        // Auto-update status pendaftar
        $calonSiswa->autoUpdateStatusVerifikasi();

        // Otomatis batalkan finalisasi agar pendaftar bisa upload ulang
        $finalisasiDibatalkan = false;
        if ($calonSiswa->is_finalisasi) {
            $calonSiswa->update([
                'is_finalisasi' => false,
                'tanggal_finalisasi' => null,
            ]);
            $finalisasiDibatalkan = true;

            // Log activity pembatalan finalisasi
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'App\Models\CalonSiswa',
                'model_id' => $calonSiswa->id,
                'description' => "Finalisasi otomatis dibatalkan karena ada permintaan revisi dokumen: {$dokumen->jenis_dokumen}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        // Kirim notifikasi email revisi dokumen
        \App\Services\EmailNotificationService::sendRevisiDokumen($calonSiswa, $dokumen, $request->catatan);

        $message = 'Permintaan revisi dokumen telah dikirim.';
        if ($finalisasiDibatalkan) {
            $message .= ' Finalisasi pendaftar otomatis dibatalkan agar dapat upload ulang.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => 'revision',
                'finalisasi_dibatalkan' => $finalisasiDibatalkan
            ]);
        }

        return redirect()->back()->with('info', $message);
    }

    public function cancelVerifikasi(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        // Hanya bisa cancel jika statusnya valid atau invalid
        if (!in_array($oldStatus, ['valid', 'invalid'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dokumen yang sudah diverifikasi yang bisa dibatalkan.',
                ], 400);
            }
            return redirect()->back()->with('error', 'Hanya dokumen yang sudah diverifikasi yang bisa dibatalkan.');
        }
        
        $dokumen->update([
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'verifikasi_note' => $request->alasan,
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'cancel',
            'status_from' => $oldStatus,
            'status_to' => 'pending',
            'keterangan' => $request->alasan,
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi dokumen berhasil dibatalkan.',
                'status' => 'pending'
            ]);
        }

        return redirect()->back()->with('info', 'Verifikasi dokumen berhasil dibatalkan.');
    }

    public function cancelRevisi(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        // Hanya bisa cancel revisi jika statusnya revision
        if ($oldStatus !== 'revision') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dokumen dengan status revisi yang bisa dibatalkan.',
                ], 400);
            }
            return redirect()->back()->with('error', 'Hanya dokumen dengan status revisi yang bisa dibatalkan.');
        }
        
        $dokumen->update([
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'revised_by' => null,
            'revised_at' => null,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'verifikasi_note' => $request->alasan,
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'cancel_revisi',
            'status_from' => $oldStatus,
            'status_to' => 'pending',
            'keterangan' => $request->alasan,
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi berhasil dibatalkan.',
                'status' => 'pending'
            ]);
        }

        return redirect()->back()->with('info', 'Permintaan revisi berhasil dibatalkan.');
    }
    
    /**
     * Validasi dokumen rapor per semester
     */
    public function validasiRapor(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:valid,invalid,pending',
            'catatan' => 'nullable|string|max:500',
        ]);

        $nilaiRapor = \App\Models\NilaiRapor::findOrFail($id);
        
        $updateData = [
            'status_validasi' => $request->status,
            'catatan_validasi' => $request->status === 'pending' ? null : $request->catatan,
        ];
        
        // Only update validated_by and validated_at if not pending
        if ($request->status !== 'pending') {
            $updateData['validated_by'] = auth()->id();
            $updateData['validated_at'] = now();
        } else {
            $updateData['validated_by'] = null;
            $updateData['validated_at'] = null;
        }
        
        $nilaiRapor->update($updateData);
        $nilaiRapor->refresh();

        // Sync status ke calon_dokumens (jika dokumen rapor ada di tabel dokumen)
        $jenisDokumen = 'rapor_sem_' . $nilaiRapor->semester;
        $calonDokumen = CalonDokumen::where('calon_siswa_id', $nilaiRapor->calon_siswa_id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->first();
        
        if ($calonDokumen) {
            $dokumenStatus = $request->status === 'valid' ? 'valid' : ($request->status === 'invalid' ? 'invalid' : 'pending');
            $calonDokumen->update([
                'status_verifikasi' => $dokumenStatus,
                'catatan_verifikasi' => $request->catatan,
                'verified_at' => $request->status !== 'pending' ? now() : null,
                'verified_by' => $request->status !== 'pending' ? auth()->id() : null,
            ]);
        }

        // Auto-update status verifikasi calon siswa (generate nomor tes jika semua dokumen valid)
        $nilaiRapor->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            // Generate HTML for table cell
            $html = $this->generateRaporValidasiCellHtml($nilaiRapor);
            
            $statusMessages = [
                'valid' => 'divalidasi',
                'invalid' => 'ditolak',
                'pending' => 'dikembalikan ke pending',
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Dokumen rapor semester ' . $nilaiRapor->semester . ' berhasil ' . $statusMessages[$request->status] . '.',
                'status' => $request->status,
                'html' => $html,
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen rapor berhasil divalidasi.');
    }
    
    /**
     * Generate HTML for rapor validation cell
     */
    private function generateRaporValidasiCellHtml($nilai)
    {
        $html = '';
        
        if ($nilai->status_validasi === 'pending') {
            $html = '<div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success btn-xs btn-validasi-rapor" 
                        data-id="' . $nilai->id . '" data-status="valid" title="Validasi">
                    <i class="fas fa-check"></i> Valid
                </button>
                <button type="button" class="btn btn-danger btn-xs btn-validasi-rapor" 
                        data-id="' . $nilai->id . '" data-status="invalid" title="Tolak">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </div>';
        } elseif ($nilai->status_validasi === 'valid') {
            $html = '<span class="badge badge-success mb-1"><i class="fas fa-check-circle"></i> Valid</span>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-warning btn-xs btn-validasi-rapor" 
                            data-id="' . $nilai->id . '" data-status="pending" title="Batal Verifikasi">
                        <i class="fas fa-undo"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger btn-xs btn-validasi-rapor" 
                            data-id="' . $nilai->id . '" data-status="invalid" title="Tolak">
                        <i class="fas fa-times"></i> Tolak
                    </button>
                </div>
                <br><small class="text-muted">' . ($nilai->validated_at ? $nilai->validated_at->format('d/m H:i') : '') . '</small>';
        } else {
            $catatanHtml = $nilai->catatan_validasi 
                ? '<br><small class="text-danger" title="' . e($nilai->catatan_validasi) . '"><i class="fas fa-comment"></i> ' . \Str::limit($nilai->catatan_validasi, 20) . '</small>' 
                : '';
            $html = '<span class="badge badge-danger mb-1"><i class="fas fa-times-circle"></i> Ditolak</span>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-success btn-xs btn-validasi-rapor" 
                            data-id="' . $nilai->id . '" data-status="valid" title="Validasi">
                        <i class="fas fa-check"></i> Valid
                    </button>
                    <button type="button" class="btn btn-warning btn-xs btn-validasi-rapor" 
                            data-id="' . $nilai->id . '" data-status="pending" title="Batal Tolak">
                        <i class="fas fa-undo"></i> Batal
                    </button>
                </div>' . $catatanHtml . '
                <br><small class="text-muted">' . ($nilai->validated_at ? $nilai->validated_at->format('d/m H:i') : '') . '</small>';
        }
        
        return $html;
    }
    
    /**
     * Edit pendaftar lengkap
     */
    public function edit($id)
    {
        $pendaftar = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'ortu'])->findOrFail($id);
        $jalurAktifPendaftar = $pendaftar->jalurPendaftaran;
        $pilihanProgramOptions = collect($jalurAktifPendaftar?->pilihan_program_options ?? [])
            ->filter(fn ($option) => filled($option))
            ->values()
            ->all();
        
        // Get jalur list
        $jalurList = JalurPendaftaran::with('tahunPelajaran')
            ->orderByDesc(function ($query) {
                $query->select('nama')
                      ->from('tahun_pelajarans')
                      ->whereColumn('tahun_pelajarans.id', 'jalur_pendaftaran.tahun_pelajaran_id')
                      ->limit(1);
            })
            ->orderBy('urutan')
            ->get();
            
        // Get gelombang list
        $gelombangList = GelombangPendaftaran::with('jalur')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get provinces for Laravolt
        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        return view('admin.pendaftar.edit', compact(
            'pendaftar',
            'jalurList',
            'gelombangList',
            'provinces',
            'pilihanProgramOptions'
        ));
    }
    
    /**
     * Update pendaftar lengkap
     */
    public function update(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with(['user', 'ortu'])->findOrFail($id);
        $oldValues = $pendaftar->toArray();
        $jalurAktifPendaftar = $pendaftar->jalurPendaftaran;
        $pilihanProgramAktif = (bool) ($jalurAktifPendaftar?->pilihan_program_aktif);
        $pilihanProgramOptions = collect($jalurAktifPendaftar?->pilihan_program_options ?? [])
            ->filter(fn ($option) => filled($option))
            ->values()
            ->all();
        
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            // NISN tidak di-update (disabled di form)
            'nik' => 'required|digits:16',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            // Laravolt Indonesia fields
            'alamat_siswa' => 'nullable|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
            'kodepos_siswa' => 'nullable|string|max:10',
            'nomor_hp' => 'required|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Email updated via user
            'jalur_pendaftaran_id' => 'nullable|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'nullable|exists:gelombang_pendaftaran,id',
            // Data Orang Tua
            'no_kk' => 'nullable|digits:16',
            // Data Orang Tua - Ayah
            'status_ayah' => 'nullable|in:masih_hidup,meninggal',
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah' => 'nullable|digits:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'hp_ayah' => 'nullable|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Data Orang Tua - Ibu
            'status_ibu' => 'nullable|in:masih_hidup,meninggal',
            'nama_ibu' => 'nullable|string|max:100',
            'nik_ibu' => 'nullable|digits:16',
            'tempat_lahir_ibu' => 'nullable|string|max:100',
            'tanggal_lahir_ibu' => 'nullable|date',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'hp_ibu' => 'nullable|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Data Asal Sekolah
            'nama_sekolah_asal' => 'nullable|string|max:255',
            'npsn_asal_sekolah' => 'nullable|string|max:20',
            'alamat_sekolah_asal' => 'nullable|string|max:500',
            'kelurahan_sekolah_asal' => 'nullable|string|max:100',
            'kecamatan_sekolah_asal' => 'nullable|string|max:100',
            'kabupaten_sekolah_asal' => 'nullable|string|max:100',
            'provinsi_sekolah_asal' => 'nullable|string|max:100',
            'status_sekolah_asal' => 'nullable|in:NEGERI,SWASTA',
            'bentuk_sekolah_asal' => 'nullable|string|max:50',
            'akreditasi_sekolah_asal' => 'nullable|string|max:1',
        ];

        if ($pilihanProgramAktif && !empty($pilihanProgramOptions)) {
            $escapedOptions = array_map(fn ($option) => str_replace(',', '\,', $option), $pilihanProgramOptions);
            $rules['pilihan_program'] = 'required|in:' . implode(',', $escapedOptions);
        } else {
            $rules['pilihan_program'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules, [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik_ayah.digits' => 'NIK Ayah harus 16 digit angka.',
            'nik_ibu.digits' => 'NIK Ibu harus 16 digit angka.',
            'no_kk.digits' => 'No. KK harus 16 digit angka.',
            'nomor_hp.regex' => 'Format No. HP harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ayah.regex' => 'Format No. HP Ayah harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ibu.regex' => 'Format No. HP Ibu harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'pilihan_program.in' => 'Pilihan program tidak sesuai dengan pengaturan jalur pendaftaran aktif.',
        ]);

        // Check if phone number already registered by other user
        $phoneNormalized = $this->normalizePhoneNumber($validated['nomor_hp']);
        $existingPhone = CalonSiswa::where('id', '!=', $pendaftar->id)
            ->where(function($query) use ($phoneNormalized, $validated) {
                $query->where('nomor_hp', $phoneNormalized)
                      ->orWhere('nomor_hp', $validated['nomor_hp'])
                      ->orWhere('nomor_hp', '+62' . ltrim($validated['nomor_hp'], '0'))
                      ->orWhere('nomor_hp', '0' . substr($phoneNormalized, 3));
            })->first();
        
        if ($existingPhone) {
            return back()->withErrors(['nomor_hp' => 'Nomor WhatsApp sudah digunakan oleh pendaftar lain.'])->withInput();
        }
        
        // Convert phone numbers from 08xx to +628xx format
        $phoneFields = ['nomor_hp', 'hp_ayah', 'hp_ibu'];
        foreach ($phoneFields as $field) {
            if (!empty($validated[$field])) {
                $phone = $validated[$field];
                // If starts with 0, convert to +62
                if (substr($phone, 0, 1) === '0') {
                    $validated[$field] = '+62' . substr($phone, 1);
                }
                // If already starts with +62, keep it
                // If starts with 62 without +, add +
                elseif (substr($phone, 0, 2) === '62') {
                    $validated[$field] = '+' . $phone;
                }
            }
        }
        
        // Update data siswa
        $pendaftar->update([
            'nama_lengkap' => $validated['nama_lengkap'],
            'nik' => $validated['nik'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'agama' => $validated['agama'],
            'alamat_siswa' => $validated['alamat_siswa'],
            'rt_siswa' => $validated['rt_siswa'],
            'rw_siswa' => $validated['rw_siswa'],
            'provinsi_id_siswa' => $validated['provinsi_id_siswa'],
            'kabupaten_id_siswa' => $validated['kabupaten_id_siswa'],
            'kecamatan_id_siswa' => $validated['kecamatan_id_siswa'],
            'kelurahan_id_siswa' => $validated['kelurahan_id_siswa'],
            'kodepos_siswa' => $validated['kodepos_siswa'] ?? null,
            'nomor_hp' => $validated['nomor_hp'],
            'pilihan_program' => $pilihanProgramAktif ? ($validated['pilihan_program'] ?? null) : null,
            'nama_sekolah_asal' => $validated['nama_sekolah_asal'] ?? null,
            'npsn_asal_sekolah' => $validated['npsn_asal_sekolah'] ?? null,
            'alamat_sekolah_asal' => $validated['alamat_sekolah_asal'] ?? null,
            'kelurahan_sekolah_asal' => $validated['kelurahan_sekolah_asal'] ?? null,
            'kecamatan_sekolah_asal' => $validated['kecamatan_sekolah_asal'] ?? null,
            'kabupaten_sekolah_asal' => $validated['kabupaten_sekolah_asal'] ?? null,
            'provinsi_sekolah_asal' => $validated['provinsi_sekolah_asal'] ?? null,
            'status_sekolah_asal' => $validated['status_sekolah_asal'] ?? null,
            'bentuk_sekolah_asal' => $validated['bentuk_sekolah_asal'] ?? null,
            'akreditasi_sekolah_asal' => $validated['akreditasi_sekolah_asal'] ?? null,
        ]);
        
        // Update atau create data orang tua
        if ($pendaftar->ortu) {
            $pendaftar->ortu->update([
                'no_kk' => $validated['no_kk'] ?? null,
                'status_ayah' => $validated['status_ayah'] ?? 'masih_hidup',
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
                'status_ibu' => $validated['status_ibu'] ?? 'masih_hidup',
                'nama_ibu' => $validated['nama_ibu'] ?? null,
                'nik_ibu' => $validated['nik_ibu'] ?? null,
                'tempat_lahir_ibu' => $validated['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $validated['tanggal_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $validated['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
                'hp_ibu' => $validated['hp_ibu'] ?? null,
            ]);
        } else {
            $pendaftar->ortu()->create([
                'no_kk' => $validated['no_kk'] ?? null,
                'status_ayah' => $validated['status_ayah'] ?? 'masih_hidup',
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
                'status_ibu' => $validated['status_ibu'] ?? 'masih_hidup',
                'nama_ibu' => $validated['nama_ibu'] ?? null,
                'nik_ibu' => $validated['nik_ibu'] ?? null,
                'tempat_lahir_ibu' => $validated['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $validated['tanggal_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $validated['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
                'hp_ibu' => $validated['hp_ibu'] ?? null,
            ]);
        }
        
        // Update user email if provided and changed
        if ($request->filled('email') && $pendaftar->user && $pendaftar->user->email !== $request->email) {
            $pendaftar->user->update(['email' => $request->email]);
        }
        
        ActivityLog::log('update', "Mengupdate data pendaftar: {$pendaftar->nama_lengkap}", $pendaftar, 
            $oldValues, $pendaftar->fresh()->toArray());
        
        return redirect()->route('admin.pendaftar.show', $id)
            ->with('success', 'Data pendaftar berhasil diperbarui.');
    }
    
    /**
     * Reset password pendaftar
     */
    public function resetPassword(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with('user')->findOrFail($id);
        
        if (!$pendaftar->user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }
        
        // Generate random password
        $newPassword = $this->generateSecurePassword(8);
        
        $pendaftar->user->password = Hash::make($newPassword);
        $pendaftar->user->readable_password = $newPassword;
        $pendaftar->user->save();
        
        ActivityLog::log('update', "Reset password pendaftar: {$pendaftar->nama_lengkap}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset.',
                'password' => $newPassword
            ]);
        }
        
        return redirect()->back()->with([
            'success' => 'Password berhasil direset.',
            'new_password' => $newPassword
        ]);
    }
    
    /**
     * Show password pendaftar
     */
    public function showPassword($id)
    {
        $pendaftar = CalonSiswa::with('user')->findOrFail($id);
        
        if (!$pendaftar->user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'password' => $pendaftar->user->readable_password ?? 'Password tidak tersedia (gunakan reset password)',
            'email' => $pendaftar->user->email
        ]);
    }

    /**
     * Soft delete pendaftar (move to trash).
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $pendaftar = CalonSiswa::findOrFail($id);
            
            // Set deleted_by and deleted_reason before soft delete
            $pendaftar->deleted_by = auth()->id();
            $pendaftar->deleted_reason = $request->reason ?? 'Dihapus oleh admin';
            $pendaftar->save();
            
            // Soft delete (akan trigger cascade di model)
            $pendaftar->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'App\Models\CalonSiswa',
                'model_id' => $pendaftar->id,
                'description' => "Menghapus pendaftar: {$pendaftar->nama_lengkap} (NISN: {$pendaftar->nisn}). Alasan: " . ($request->reason ?? 'Dihapus oleh admin'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()
                ->route('admin.data.delete-list')
                ->with('success', 'Data pendaftar berhasil dihapus dan dipindah ke Data Terhapus. Data masih bisa di-restore.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan finalisasi pendaftar
     */
    public function batalFinalisasi($id)
    {
        try {
            $pendaftar = CalonSiswa::findOrFail($id);
            
            if (!$pendaftar->is_finalisasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendaftar belum difinalisasi'
                ], 400);
            }

            // Reset finalisasi data
            $pendaftar->update([
                'is_finalisasi' => false,
                'tanggal_finalisasi' => null,
                // Keep nomor_tes for history, don't reset
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'App\Models\CalonSiswa',
                'model_id' => $pendaftar->id,
                'description' => "Membatalkan finalisasi pendaftar: {$pendaftar->nama_lengkap} (NISN: {$pendaftar->nisn})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Finalisasi berhasil dibatalkan. Pendaftar sekarang dapat mengedit data kembali.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan finalisasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan nomor tes terakhir yang baru digenerate.
     */
    public function batalGenerateNomorTes($id)
    {
        $this->checkPermission('verifikasi.verify');

        try {
            $result = DB::transaction(function () use ($id) {
                $pendaftar = CalonSiswa::lockForUpdate()->findOrFail($id);

                if (!$pendaftar->nomor_tes) {
                    throw new \RuntimeException('Pendaftar belum memiliki nomor tes.');
                }

                $rule = $this->nomorService->resolveRule(\App\Models\NomorRule::JENIS_TES, $pendaftar);
                if (!$rule) {
                    throw new \RuntimeException('Rule nomor tes untuk pendaftar ini tidak ditemukan.');
                }

                $sequence = \App\Models\NomorSequence::where('nomor_rule_id', $rule->id)
                    ->lockForUpdate()
                    ->first();

                if (!$sequence || $sequence->last_generated_value !== $pendaftar->nomor_tes) {
                    throw new \RuntimeException('Nomor tes pendaftar bukan nomor terakhir yang digenerate.');
                }

                $currentNumber = $this->extractNomorUrut($pendaftar->nomor_tes);
                if (!$currentNumber || $currentNumber !== (int) $sequence->last_number) {
                    throw new \RuntimeException('Nomor tes tidak cocok dengan counter terakhir.');
                }

                $previous = $this->getPreviousNomorTesRecord($pendaftar);
                $previousNumber = $previous ? $this->extractNomorUrut($previous->nomor_tes) : 0;

                if ($previousNumber !== $currentNumber - 1) {
                    throw new \RuntimeException('Ada ketidaksesuaian urutan nomor terakhir. Undo dibatalkan agar data aman.');
                }

                $relations = $this->getNomorTesBlockingRelations($pendaftar->id);
                if (!empty($relations)) {
                    throw new \RuntimeException('Nomor tes sudah dipakai pada data lain: ' . implode(', ', $relations));
                }

                $oldValues = [
                    'nomor_tes' => $pendaftar->nomor_tes,
                    'is_finalisasi' => $pendaftar->is_finalisasi,
                    'tanggal_finalisasi' => optional($pendaftar->tanggal_finalisasi)->toDateTimeString(),
                    'status_verifikasi' => $pendaftar->status_verifikasi,
                    'verification_hash' => $pendaftar->verification_hash,
                    'sequence_last_number' => $sequence->last_number,
                    'sequence_last_generated_value' => $sequence->last_generated_value,
                ];

                $nomorDibatalkan = $pendaftar->nomor_tes;

                $pendaftar->forceFill([
                    'nomor_tes' => null,
                    'is_finalisasi' => false,
                    'tanggal_finalisasi' => null,
                    'status_verifikasi' => 'pending',
                    'verification_hash' => null,
                ])->save();

                $sequence->forceFill([
                    'last_number' => $previousNumber,
                    'last_generated_value' => $previous?->nomor_tes,
                    'last_generated_at' => $previous?->updated_at,
                ])->save();

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'model_type' => CalonSiswa::class,
                    'model_id' => $pendaftar->id,
                    'description' => "Membatalkan generate nomor tes terakhir: {$nomorDibatalkan} untuk {$pendaftar->nama_lengkap} (NISN: {$pendaftar->nisn})",
                    'old_values' => json_encode($oldValues),
                    'new_values' => json_encode([
                        'nomor_tes' => null,
                        'is_finalisasi' => false,
                        'tanggal_finalisasi' => null,
                        'status_verifikasi' => 'pending',
                        'verification_hash' => null,
                        'sequence_last_number' => $previousNumber,
                        'sequence_last_generated_value' => $previous?->nomor_tes,
                    ]),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return [
                    'nomor_dibatalkan' => $nomorDibatalkan,
                    'sequence_kembali_ke' => $previous?->nomor_tes,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Nomor tes terakhir berhasil dibatalkan. Status finalisasi ikut dibatalkan.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function getNomorTesUndoInfo(CalonSiswa $pendaftar): array
    {
        $info = [
            'available' => false,
            'can_undo' => false,
            'reason' => null,
            'rule' => null,
            'sequence' => null,
            'last_owner' => null,
            'previous_owner' => null,
            'blocking_relations' => [],
            'latest_print_log' => null,
        ];

        if (!$pendaftar->nomor_tes) {
            $info['reason'] = 'Pendaftar belum memiliki nomor tes.';
            return $info;
        }

        $rule = $this->nomorService->resolveRule(\App\Models\NomorRule::JENIS_TES, $pendaftar);
        if (!$rule) {
            $info['reason'] = 'Rule nomor tes tidak ditemukan.';
            return $info;
        }

        $sequence = \App\Models\NomorSequence::where('nomor_rule_id', $rule->id)->first();
        if (!$sequence) {
            $info['reason'] = 'Sequence nomor tes belum tersedia.';
            return $info;
        }

        $lastOwner = $sequence->last_generated_value
            ? CalonSiswa::where('nomor_tes', $sequence->last_generated_value)->first()
            : null;
        $previousOwner = $this->getPreviousNomorTesRecord($lastOwner ?: $pendaftar);
        $blockingRelations = $this->getNomorTesBlockingRelations($pendaftar->id);
        $latestPrintLog = ActivityLog::where('model_type', CalonSiswa::class)
            ->where('model_id', $pendaftar->id)
            ->where('action', 'print')
            ->where('description', 'like', '%kartu ujian%')
            ->latest()
            ->first();

        $info = array_merge($info, [
            'available' => true,
            'rule' => $rule,
            'sequence' => $sequence,
            'last_owner' => $lastOwner,
            'previous_owner' => $previousOwner,
            'blocking_relations' => $blockingRelations,
            'latest_print_log' => $latestPrintLog,
        ]);

        if (!$lastOwner || $lastOwner->id !== $pendaftar->id) {
            $info['reason'] = 'Nomor tes pendaftar bukan nomor terakhir yang digenerate.';
            return $info;
        }

        if (!empty($blockingRelations)) {
            $info['reason'] = 'Nomor tes sudah dipakai pada data lain: ' . implode(', ', $blockingRelations);
            return $info;
        }

        $currentNumber = $this->extractNomorUrut($pendaftar->nomor_tes);
        $previousNumber = $previousOwner ? $this->extractNomorUrut($previousOwner->nomor_tes) : 0;
        if (!$currentNumber || $previousNumber !== $currentNumber - 1) {
            $info['reason'] = 'Urutan nomor terakhir tidak bersebelahan dengan nomor sebelumnya.';
            return $info;
        }

        $info['can_undo'] = true;
        return $info;
    }

    private function getPreviousNomorTesRecord(CalonSiswa $pendaftar): ?CalonSiswa
    {
        if (!$pendaftar->nomor_tes) {
            return null;
        }

        $currentNumber = $this->extractNomorUrut($pendaftar->nomor_tes);
        $prefix = preg_replace('/-\d+$/', '', $pendaftar->nomor_tes);

        if (!$currentNumber || !$prefix) {
            return null;
        }

        return CalonSiswa::where('id', '!=', $pendaftar->id)
            ->where('nomor_tes', 'like', $prefix . '-%')
            ->select('*')
            ->selectRaw("CAST(SUBSTRING_INDEX(nomor_tes, '-', -1) AS UNSIGNED) as nomor_urut_tes")
            ->having('nomor_urut_tes', '<', $currentNumber)
            ->orderByDesc('nomor_urut_tes')
            ->first();
    }

    private function extractNomorUrut(?string $nomor): ?int
    {
        if (!$nomor || !preg_match('/-(\d+)$/', $nomor, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function getNomorTesBlockingRelations(string $calonSiswaId): array
    {
        $checks = [
            'jadwal peserta' => ['jadwal_peserta', 'calon_siswa_id'],
            'peserta ruang' => ['peserta_ruang', 'calon_siswa_id'],
            'nilai seleksi' => ['nilai_seleksi', 'calon_siswa_id'],
            'nilai CBT' => ['nilai_cbt', 'calon_siswa_id'],
            'kelulusan' => ['kelulusan', 'calon_siswa_id'],
        ];

        $blocked = [];
        foreach ($checks as $label => [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column) && DB::table($table)->where($column, $calonSiswaId)->exists()) {
                $blocked[] = $label;
            }
        }

        return $blocked;
    }

    /**
     * Cetak Bukti Registrasi (for Admin/Verifikator)
     */
    public function cetakBuktiRegistrasi($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.cetak-registrasi')) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak bukti registrasi');
        }

        $calonSiswa = CalonSiswa::with([
            'jalurPendaftaran', 
            'gelombangPendaftaran', 
            'tahunPelajaran', 
            'ortu',
            'user'
        ])->findOrFail($id);

        if (!$calonSiswa->is_finalisasi) {
            return redirect()->route('admin.pendaftar.show', $id)
                ->with('error', 'Pendaftar belum difinalisasi, tidak dapat mencetak bukti registrasi');
        }

        // Increase memory limit for PDF generation
        ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        // Generate kop surat HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);
        
        // Generate or get verification hash for QR code
        $verificationHash = $calonSiswa->getOrGenerateHash();
        
        // Generate QR code if enabled
        $qrCode = null;
        if ($sekolahSettings && $sekolahSettings->qr_enable) {
            $qrCode = $this->generateQrCode($calonSiswa, $sekolahSettings, $verificationHash);
        }
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $this->getSchoolLogo(),
            'alamat' => $sekolahSettings ? trim(($sekolahSettings->alamat_jalan ?? '') . ' ' . ($sekolahSettings->city->name ?? '') . ' ' . ($sekolahSettings->province->name ?? '')) : config('app.school_address', ''),
            'telepon' => $sekolahSettings->telepon ?? config('app.school_phone', '-'),
            'email' => $sekolahSettings->email ?? config('app.school_email', '-'),
            'kota' => $sekolahSettings->city->name ?? config('app.school_city', ''),
        ];

        $fotoPdfSrc = $this->resolveFotoPdfSource($calonSiswa);
        
        $pdf = Pdf::loadView('pendaftar.pdf.bukti-registrasi', compact('calonSiswa', 'sekolah', 'kopHtml', 'qrCode', 'sekolahSettings', 'fotoPdfSrc'))
            ->setOption('isRemoteEnabled', true);
        
        $filename = 'bukti-registrasi-' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nomor_registrasi) . '.pdf';
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'print',
            'model_type' => 'App\Models\CalonSiswa',
            'model_id' => $calonSiswa->id,
            'description' => "Mencetak bukti registrasi: {$calonSiswa->nama_lengkap} (NISN: {$calonSiswa->nisn})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $pdf->download($filename);
    }

    private function resolveFotoPdfSource(CalonSiswa $calonSiswa): ?string
    {
        $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
        if (!$fotoDokumen) {
            return null;
        }

        if ($fotoDokumen->storage_disk === 'public' && $fotoDokumen->file_path) {
            $fotoPath = storage_path('app/public/' . $fotoDokumen->file_path);
            return file_exists($fotoPath) ? $fotoPath : null;
        }

        if ($fotoDokumen->storage_disk === 'gdrive') {
            try {
                $response = Http::timeout(20)->get($fotoDokumen->preview_url ?: $fotoDokumen->download_url);

                if ($response->successful()) {
                    $mimeType = $fotoDokumen->mime_type ?: 'image/jpeg';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($response->body());
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal memuat foto PDF dari Google Drive (admin)', [
                    'calon_siswa_id' => $calonSiswa->id,
                    'dokumen_id' => $fotoDokumen->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $fotoDokumen->preview_url ?: $fotoDokumen->download_url;
        }

        return null;
    }

    /**
     * Preview Kartu Ujian (HTML view for Admin/Verifikator)
     */
    public function previewKartuUjian($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.cetak-ujian')) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak kartu ujian');
        }

        $calonSiswa = CalonSiswa::with([
            'jalurPendaftaran', 
            'gelombangPendaftaran', 
            'tahunPelajaran',
            'user'
        ])->findOrFail($id);

        if (!$calonSiswa->is_finalisasi) {
            return redirect()->route('admin.pendaftar.show', $id)
                ->with('error', 'Pendaftar belum difinalisasi, tidak dapat mencetak kartu ujian');
        }

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $sekolahSettings->logo ?? null,
        ];
        
        $password = $calonSiswa->user->readable_password ?? '********';
        $isAdmin = true;
        
        // Return HTML view for preview (not PDF)
        return view('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'isAdmin'))
            ->with('isPdf', false);
    }

    /**
     * Cetak Kartu Ujian (for Admin/Verifikator)
     */
    public function cetakKartuUjian($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.cetak-ujian')) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak kartu ujian');
        }

        $calonSiswa = CalonSiswa::with([
            'jalurPendaftaran', 
            'gelombangPendaftaran', 
            'tahunPelajaran',
            'user'
        ])->findOrFail($id);

        if (!$calonSiswa->is_finalisasi) {
            return redirect()->route('admin.pendaftar.show', $id)
                ->with('error', 'Pendaftar belum difinalisasi, tidak dapat mencetak kartu ujian');
        }

        // Increase memory limit for PDF generation
        ini_set('memory_limit', '256M');

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        // Generate kop surat HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $this->getSchoolLogo(),
        ];
        
        $password = $calonSiswa->user->readable_password ?? '********';
        
        $isPdf = true;
        
        $pdf = Pdf::loadView('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'kopHtml', 'isPdf'))
            ->setOption('isRemoteEnabled', true)
            ->setPaper([0, 0, 298, 421], 'landscape');
        
        $filename = 'kartu-ujian-' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nomor_tes ?? $calonSiswa->nomor_registrasi) . '.pdf';
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'print',
            'model_type' => 'App\Models\CalonSiswa',
            'model_id' => $calonSiswa->id,
            'description' => "Mencetak kartu ujian: {$calonSiswa->nama_lengkap} (NISN: {$calonSiswa->nisn})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $pdf->download($filename);
    }

    /**
     * Upload Dokumen oleh Verifikator (with camera support)
     */
    public function uploadDokumen(Request $request, $id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('pendaftar.upload-dokumen')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk upload dokumen'
            ], 403);
        }

        $request->validate([
            'jenis_dokumen' => 'required|string|in:' . implode(',', array_keys(CalonDokumen::getAdminUploadOptions())),
            'catatan' => 'nullable|string|max:500',
        ]);

        // Must have either file or captured_image_data
        if (!$request->hasFile('file') && !$request->filled('captured_image_data')) {
            return response()->json([
                'success' => false,
                'message' => 'File atau foto dari kamera harus disertakan'
            ], 422);
        }

        try {
            $calonSiswa = CalonSiswa::findOrFail($id);
            
            $jenisDokumen = $request->jenis_dokumen;
            $stored = null;
            
            // Handle file upload (from camera base64 or file upload)
            if ($request->filled('captured_image_data') && str_starts_with($request->captured_image_data, 'data:image')) {
                // Base64 image from camera
                $stored = $this->documentStorageService->storeBase64Image($request->captured_image_data, $calonSiswa, $jenisDokumen, [
                    'filename' => $jenisDokumen . '_' . time() . '_' . Str::random(8) . '.jpg',
                    'original_name' => 'camera_capture_' . date('Ymd_His') . '.jpg',
                    'local_directory' => "dokumen/{$calonSiswa->id}",
                ]);
            } elseif ($request->hasFile('file')) {
                // Regular file upload - pas foto hanya boleh format gambar
                if ($jenisDokumen === 'foto') {
                    $request->validate(
                        ['file' => 'file|mimes:jpg,jpeg,png|max:5120'],
                        ['file.mimes' => 'Pas foto harus berupa file gambar (JPG, JPEG, PNG). PDF tidak diperbolehkan.']
                    );
                } else {
                    $request->validate(['file' => 'file|mimes:jpg,jpeg,png,pdf|max:5120']);
                }
                $file = $request->file('file');
                $stored = $this->documentStorageService->storeUploadedFile($file, $calonSiswa, $jenisDokumen, [
                    'local_directory' => "dokumen/{$calonSiswa->id}",
                ]);
            }

            // Semua dokumen yang diupload admin langsung valid
            $isDokumenTambahan = array_key_exists($jenisDokumen, CalonDokumen::DOKUMEN_TAMBAHAN);

            // Find existing document or create new
            $dokumen = CalonDokumen::where('calon_siswa_id', $calonSiswa->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->first();

            if ($dokumen) {
                // Delete old file if exists
                $this->documentStorageService->delete($dokumen);

                $updateData = [
                    'file_path' => $stored['file_path'],
                    'remote_file_id' => $stored['remote_file_id'],
                    'remote_file_url' => $stored['remote_file_url'],
                    'nama_file' => $stored['nama_file'],
                    'file_size' => $stored['file_size'],
                    'mime_type' => $stored['mime_type'],
                    'storage_disk' => $stored['storage_disk'],
                    'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? $jenisDokumen,
                    'status_verifikasi' => 'valid',
                    'catatan_verifikasi' => null,
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                    'verified_at' => now(),
                ];
                if ($isDokumenTambahan) {
                    $updateData['is_required'] = false;
                }
                $dokumen->update($updateData);
            } else {
                $createData = [
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDokumen,
                    'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? $jenisDokumen,
                    'nama_file' => $stored['nama_file'],
                    'file_path' => $stored['file_path'],
                    'remote_file_id' => $stored['remote_file_id'],
                    'remote_file_url' => $stored['remote_file_url'],
                    'file_size' => $stored['file_size'],
                    'mime_type' => $stored['mime_type'],
                    'storage_disk' => $stored['storage_disk'],
                    'status_verifikasi' => 'valid',
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                    'verified_at' => now(),
                ];
                if ($isDokumenTambahan) {
                    $createData['is_required'] = false;
                }
                $dokumen = CalonDokumen::create($createData);
            }

            // Log history
            DokumenVerifikasiHistory::create([
                'dokumen_id' => $dokumen->id,
                'action' => 'upload',
                'status_from' => null,
                'status_to' => 'valid',
                'keterangan' => 'Dokumen diupload oleh admin (otomatis valid): ' . auth()->user()->name,
                'user_id' => auth()->id(),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'upload',
                'model_type' => 'App\Models\CalonDokumen',
                'model_id' => $dokumen->id,
                'description' => "Mengupload dokumen {$jenisDokumen} untuk pendaftar: {$calonSiswa->nama_lengkap}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Update completion status
            $calonSiswa->syncCompletionStatus();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'dokumen' => [
                    'id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'file_path' => $dokumen->file_path,
                    'file_url' => $dokumen->file_url,
                    'status_verifikasi' => $dokumen->status_verifikasi,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR code with optional logo
     */
    private function generateQrCode($calonSiswa, $sekolahSettings, $verificationHash)
    {
        try {
            $qrSize = $sekolahSettings->qr_size ?? 150;
            $errorLevel = $sekolahSettings->qr_error_level ?? 'H';
            
            // Generate URL based on function setting
            $url = route('verify.bukti', $verificationHash);
            
            // Generate QR code as SVG (works without imagick)
            $qrSvg = \QrCode::size($qrSize)
                ->errorCorrection($errorLevel)
                ->generate($url);
            
            return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get school logo path (optimized for PDF)
     */
    private function getSchoolLogo()
    {
        // Get logo from sekolah_settings table
        $sekolahSettings = \App\Models\SekolahSettings::first();
        
        if ($sekolahSettings && $sekolahSettings->logo) {
            $logoPath = storage_path('app/public/' . $sekolahSettings->logo);
            if (file_exists($logoPath)) {
                return $logoPath;
            }
        }

        // Fallback: check common logo locations
        $possiblePaths = [
            public_path('logo.png'),
            public_path('images/logo.png'),
            public_path('assets/logo.png'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
    
    /**
     * Generate secure password
     * Format: Huruf kapital + Huruf kecil + Angka (tanpa karakter spesial untuk kemudahan input)
     * Excluded: I, O, Q (mirip angka), 1, 0 (mirip huruf)
     */
    protected function generateSecurePassword(int $length = 8): string
    {
        $uppercase = 'ABCDEFGHJKLMNPRSTUVWXYZ'; // tanpa I, O, Q
        $numbers = '23456789'; // tanpa 1, 0
        
        // Minimal 4 huruf kapital dan 4 angka
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        
        $allChars = $uppercase . $numbers;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Normalize phone number to +62 format
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Convert various formats to +62
        if (substr($phone, 0, 1) === '0') {
            return '+62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) === '62') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 3) === '+62') {
            return $phone;
        }
        
        return $phone;
    }

    /**
     * Sync NPSN data from Kemdikdasmen
     */
    public function syncNpsn(Request $request)
    {
        $request->validate([
            'npsn' => 'required|alpha_num|size:8',
        ]);

        $npsn = $request->npsn;
        
        try {
            $npsnService = new \App\Services\NpsnService();
            $result = $npsnService->cekNpsn($npsn);

            if ($result['success'] && $result['data']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sekolah ditemukan',
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'NPSN tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('syncNpsn error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim email notifikasi manual ke pendaftar
     */
    public function sendEmail(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:registrasi,revisi,nomor_tes,diterima,ditolak,lainnya',
            'dokumen_id' => 'required_if:type,revisi|nullable|exists:calon_dokumens,id',
            'catatan' => 'nullable|string|max:1000',
            'subject' => 'required_if:type,lainnya|nullable|string|max:255',
        ]);

        $pendaftar = CalonSiswa::with(['user', 'jalurPendaftaran', 'dokumen'])->findOrFail($id);
        
        $email = $pendaftar->user?->email ?? $pendaftar->email ?? null;
        if (!$email || str_contains($email, '@ppdb.temp')) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar tidak memiliki alamat email yang valid'
            ], 400);
        }

        $type = $request->type;
        $result = false;

        try {
            switch ($type) {
                case 'registrasi':
                    $username = $pendaftar->nisn;
                    $password = $pendaftar->user->readable_password ?? null;
                    
                    if (!$password) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Password tidak tersedia. Silakan reset password terlebih dahulu.'
                        ], 400);
                    }
                    
                    $result = \App\Services\EmailNotificationService::sendRegistrasi($pendaftar, $username, $password);
                    break;

                case 'revisi':
                    $dokumen = CalonDokumen::findOrFail($request->dokumen_id);
                    $catatan = $request->catatan ?? 'Silakan perbaiki dokumen yang diminta.';
                    $result = \App\Services\EmailNotificationService::sendRevisiDokumen($pendaftar, $dokumen, $catatan);
                    break;

                case 'nomor_tes':
                    $nomorTes = $pendaftar->nomor_tes;
                    if (!$nomorTes) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Pendaftar belum memiliki nomor tes'
                        ], 400);
                    }
                    $result = \App\Services\EmailNotificationService::sendNomorTes($pendaftar, $nomorTes);
                    break;

                case 'diterima':
                    $catatan = $request->catatan ?? 'Selamat! Anda diterima.';
                    $result = \App\Services\EmailNotificationService::sendHasilSeleksi($pendaftar, 'diterima', $catatan);
                    break;

                case 'ditolak':
                    $catatan = $request->catatan ?? '';
                    $result = \App\Services\EmailNotificationService::sendHasilSeleksi($pendaftar, 'ditolak', $catatan);
                    break;

                case 'lainnya':
                    $subject = $request->subject;
                    $message = $request->catatan ?? '';
                    
                    if (!$subject) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Subjek email harus diisi untuk jenis notifikasi lainnya'
                        ], 400);
                    }
                    
                    if (!$message) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Isi pesan email harus diisi'
                        ], 400);
                    }
                    
                    $result = \App\Services\EmailNotificationService::sendCustom($pendaftar, $subject, $message);
                    break;
            }

            if ($result) {
                ActivityLog::log('email', "Mengirim email {$type} ke pendaftar: {$pendaftar->nama_lengkap}", $pendaftar);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Email berhasil dikirim ke ' . $email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Email gagal dikirim. Periksa log email untuk detail.'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('sendEmail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
