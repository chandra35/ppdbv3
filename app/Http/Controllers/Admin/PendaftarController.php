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
use App\Services\KopSuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\Province;

class PendaftarController extends Controller
{
    protected $kopSuratService;

    public function __construct(KopSuratService $kopSuratService)
    {
        $this->kopSuratService = $kopSuratService;
    }

    public function index(Request $request)
    {
        // Get active tahun pelajaran
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Get default jalur from active tahun pelajaran
        $defaultJalurId = null;
        if ($tahunAktif) {
            $defaultJalur = JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)
                ->orderBy('urutan')
                ->first();
            $defaultJalurId = $defaultJalur?->id;
        }
        
        // Use request jalur_id or default to active tahun's jalur
        $selectedJalurId = $request->filled('jalur_id') ? $request->jalur_id : ($request->has('jalur_id') ? null : $defaultJalurId);
        
        $query = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'dokumen']);

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['nama_lengkap', 'nisn', 'nomor_registrasi', 'created_at', 'status_verifikasi'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        $query->orderBy($sortBy, $sortDir);

        // Filter by jalur
        if ($selectedJalurId) {
            $query->where('jalur_pendaftaran_id', $selectedJalurId);
        }

        // Filter by gelombang
        if ($request->filled('gelombang_id')) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
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
        $jalurList = JalurPendaftaran::with('tahunPelajaran')
            ->orderByRaw('(SELECT is_active FROM tahun_pelajarans WHERE tahun_pelajarans.id = jalur_pendaftaran.tahun_pelajaran_id) DESC')
            ->orderByDesc(function ($query) {
                $query->select('nama')
                      ->from('tahun_pelajarans')
                      ->whereColumn('tahun_pelajarans.id', 'jalur_pendaftaran.tahun_pelajaran_id')
                      ->limit(1);
            })
            ->orderBy('urutan')
            ->get();
            
        // Get gelombang list for filter - grouped by jalur
        $gelombangList = GelombangPendaftaran::with('jalur.tahunPelajaran')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pendaftar.index', compact('pendaftars', 'jalurList', 'gelombangList', 'selectedJalurId', 'sortBy', 'sortDir'));
    }

    /**
     * Show map of registration locations
     */
    public function map(Request $request)
    {
        $query = CalonSiswa::query()
            ->whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->with(['jalurPendaftaran']);
        
        // Filter by jalur
        if ($request->filled('jalur_id')) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
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
        
        $jalurList = JalurPendaftaran::orderBy('urutan')->get();
        
        return view('admin.pendaftar.map', compact('pendaftars', 'jalurList'));
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

        // Get tahun pelajaran aktif
        $tahunPelajaran = TahunPelajaran::active()->first();
        if (!$tahunPelajaran) {
            return redirect()->route('admin.pendaftar.index')
                ->with('error', 'Tidak ada tahun pelajaran aktif. Silakan aktifkan tahun pelajaran terlebih dahulu.');
        }

        // Get jalur pendaftaran (aktif atau tidak untuk manual input)
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

        return view('admin.pendaftar.create', compact('tahunPelajaran', 'jalurList', 'provinces'));
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
            $tahunPelajaran = TahunPelajaran::active()->first();

            // Generate nomor registrasi
            $nomorRegistrasi = $this->generateNomorRegistrasi($jalur);
            
            // Generate username & password
            $username = $request->nisn;
            $password = $this->generateSecurePassword(8);
            $hashedPassword = Hash::make($password);

            // Create user account
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email ?? $request->nisn . '@ppdb.local',
                'password' => $hashedPassword,
                'plain_password' => $password, // Simpan sementara untuk dicetak
            ]);

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
                'email' => $request->email,
                
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
                
                // Status
                'status_verifikasi' => 'pending',
                'tanggal_registrasi' => now(),
                'data_diri_completed' => true,
                
                // GPS location (from admin's browser if available)
                'registration_latitude' => $request->registration_latitude,
                'registration_longitude' => $request->registration_longitude,
                'registration_ip' => $request->ip(),
                'registration_device' => 'Admin Manual Input',
                'registration_browser' => $request->userAgent(),
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

            // Update kuota terisi
            $jalur->increment('kuota_terisi');
            $gelombang->increment('kuota_terisi');

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

    /**
     * Generate nomor registrasi
     */
    private function generateNomorRegistrasi(JalurPendaftaran $jalur): string
    {
        $prefix = $jalur->prefix_nomor ?? 'REG';
        $counter = $jalur->counter_nomor + 1;
        
        // Update counter
        $jalur->update(['counter_nomor' => $counter]);
        
        // Format: PREFIX-YYYYMM-XXXXX
        return $prefix . '-' . date('Ym') . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
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
        $dokumenLabels = [
            'kk' => 'Kartu Keluarga',
            'akta_lahir' => 'Akta Kelahiran',
            'ijazah' => 'Ijazah/SKL',
            'foto' => 'Pas Foto',
            'ktp_ortu' => 'KTP Orang Tua',
            'skhun' => 'SKHUN',
            'raport' => 'Raport',
            'surat_sehat' => 'Surat Keterangan Sehat',
            'surat_kelakuan_baik' => 'Surat Kelakuan Baik',
        ];
        
        // Get dokumen tambahan
        $dokumenTambahanOptions = \App\Models\CalonDokumen::DOKUMEN_TAMBAHAN;
        $dokumenTambahan = $pendaftar->dokumen
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanOptions))
            ->values();
        
        return view('admin.pendaftar.show', compact('pendaftar', 'requiredDocs', 'dokumenLabels', 'dokumenTambahanOptions', 'dokumenTambahan', 'wajibLokasiRegistrasi'));
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
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi dokumen telah dikirim.',
                'status' => 'revision'
            ]);
        }

        return redirect()->back()->with('info', 'Permintaan revisi dokumen telah dikirim.');
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
     * Edit pendaftar lengkap
     */
    public function edit($id)
    {
        $pendaftar = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'ortu'])->findOrFail($id);
        
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
        
        return view('admin.pendaftar.edit', compact('pendaftar', 'jalurList', 'gelombangList', 'provinces'));
    }
    
    /**
     * Update pendaftar lengkap
     */
    public function update(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with(['user', 'ortu'])->findOrFail($id);
        $oldValues = $pendaftar->toArray();
        
        $validated = $request->validate([
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
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah' => 'nullable|digits:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'hp_ayah' => 'nullable|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Data Orang Tua - Ibu
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
        ], [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik_ayah.digits' => 'NIK Ayah harus 16 digit angka.',
            'nik_ibu.digits' => 'NIK Ibu harus 16 digit angka.',
            'no_kk.digits' => 'No. KK harus 16 digit angka.',
            'nomor_hp.regex' => 'Format No. HP harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ayah.regex' => 'Format No. HP Ayah harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ibu.regex' => 'Format No. HP Ibu harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
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
            'nama_sekolah_asal' => $validated['nama_sekolah_asal'] ?? null,
            'npsn_asal_sekolah' => $validated['npsn_asal_sekolah'] ?? null,
        ]);
        
        // Update atau create data orang tua
        if ($pendaftar->ortu) {
            $pendaftar->ortu->update([
                'no_kk' => $validated['no_kk'] ?? null,
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
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
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
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
        
        $pendaftar->user->update([
            'password' => Hash::make($newPassword),
            'plain_password' => $newPassword, // Store plain password temporarily for admin to see
        ]);
        
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
            'password' => $pendaftar->user->plain_password ?? 'Password tidak tersedia (gunakan reset password)',
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
        
        $pdf = Pdf::loadView('pendaftar.pdf.bukti-registrasi', compact('calonSiswa', 'sekolah', 'kopHtml', 'qrCode', 'sekolahSettings'));
        
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
        
        $password = $calonSiswa->user->plain_password ?? '********';
        $isAdmin = true;
        
        // Return HTML view for preview (not PDF)
        return view('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'isAdmin'));
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
        
        $password = $calonSiswa->user->plain_password ?? '********';
        
        $pdf = Pdf::loadView('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'kopHtml'))
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
            'jenis_dokumen' => 'required|string',
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
            $filePath = null;
            $originalName = null;
            $fileSize = 0;
            $mimeType = null;
            
            // Handle file upload (from camera base64 or file upload)
            if ($request->filled('captured_image_data') && str_starts_with($request->captured_image_data, 'data:image')) {
                // Base64 image from camera
                $base64Image = $request->captured_image_data;
                $filePath = $this->saveBase64Image($base64Image, $calonSiswa->id, $jenisDokumen);
                $originalName = 'camera_capture_' . date('Ymd_His') . '.jpg';
                $fileSize = strlen(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image)));
                $mimeType = 'image/jpeg';
            } elseif ($request->hasFile('file')) {
                // Regular file upload
                $request->validate(['file' => 'file|mimes:jpg,jpeg,png,pdf|max:5120']);
                $file = $request->file('file');
                $filePath = $file->store("dokumen/{$calonSiswa->id}", 'public');
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
            }

            // Find existing document or create new
            $dokumen = CalonDokumen::where('calon_siswa_id', $calonSiswa->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->first();

            if ($dokumen) {
                // Delete old file if exists
                if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
                    Storage::disk('public')->delete($dokumen->file_path);
                }

                $dokumen->update([
                    'file_path' => $filePath,
                    'original_name' => $originalName,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'status_verifikasi' => 'pending',
                    'catatan_verifikasi' => null,
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                ]);
            } else {
                $dokumen = CalonDokumen::create([
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDokumen,
                    'file_path' => $filePath,
                    'original_name' => $originalName,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'status_verifikasi' => 'pending',
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                ]);
            }

            // Log history
            DokumenVerifikasiHistory::create([
                'calon_dokumen_id' => $dokumen->id,
                'action' => 'upload',
                'status_before' => null,
                'status_after' => 'pending',
                'catatan' => $request->catatan ?? 'Dokumen diupload oleh verifikator: ' . auth()->user()->name,
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
            $calonSiswa->updateDokumenCompletion();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'dokumen' => [
                    'id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'file_path' => $dokumen->file_path,
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
     * Save base64 image from camera capture
     */
    private function saveBase64Image($base64Image, $calonSiswaId, $jenisDokumen)
    {
        // Remove data:image prefix
        $image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $image = base64_decode($image);
        
        // Generate filename
        $filename = $jenisDokumen . '_' . time() . '_' . Str::random(8) . '.jpg';
        $path = "dokumen/{$calonSiswaId}/{$filename}";
        
        // Save to storage
        Storage::disk('public')->put($path, $image);
        
        return $path;
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
     * Format: Huruf kapital + Angka + 1 karakter spesial
     * Excluded: I, O, Q (mirip angka), 1, 0 (mirip huruf)
     */
    protected function generateSecurePassword(int $length = 8): string
    {
        $uppercase = 'ABCDEFGHJKLMNPRSTUVWXYZ'; // tanpa I, O, Q
        $numbers = '23456789'; // tanpa 1, 0
        $special = '@#$%&*!';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
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
}