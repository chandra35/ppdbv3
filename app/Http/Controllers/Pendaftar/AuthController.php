<?php

namespace App\Http\Controllers\Pendaftar;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\Role;
use App\Models\User;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Services\EmisNisnService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected EmisNisnService $emisService;
    protected WhatsAppService $whatsAppService;

    public function __construct(EmisNisnService $emisService, WhatsAppService $whatsAppService)
    {
        $this->emisService = $emisService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Show landing page with NISN check
     */
    public function landing()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $jalurPendaftaran = JalurPendaftaran::where('is_active', true)->orderBy('urutan')->get();
        $gelombangAktif = GelombangPendaftaran::where('is_active', true)
            ->where('tanggal_buka', '<=', now())
            ->where('tanggal_tutup', '>=', now())
            ->first();
        
        // Get location setting
        $settings = \App\Models\PpdbSettings::first();
        $wajibLokasi = $settings?->wajib_lokasi_registrasi ?? false;

        return view('pendaftar.landing', compact('tahunAktif', 'jalurPendaftaran', 'gelombangAktif', 'wajibLokasi'));
    }

    /**
     * Check NISN via EMIS API
     */
    public function cekNisn(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|size:10',
        ]);

        $nisn = $request->nisn;

        // Check if NISN already registered
        $existing = CalonSiswa::where('nisn', $nisn)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'NISN sudah terdaftar. Silakan login untuk melanjutkan pendaftaran.',
                'already_registered' => true,
            ]);
        }

        // Check if NISN validation is enabled
        $settings = \App\Models\PpdbSettings::first();
        $validasiNisnAktif = $settings ? $settings->validasi_nisn_aktif : true;

        // If validation is disabled, allow direct registration with manual input
        if (!$validasiNisnAktif) {
            // Store empty data for manual input
            session(['emis_data_' . $nisn => null]);
            
            // Encrypt NISN for URL security
            $encryptedNisn = encrypt($nisn);
            
            return response()->json([
                'success' => true,
                'message' => 'Validasi NISN dinonaktifkan. Silakan lanjutkan dengan input manual.',
                'data' => null,
                'manual_input' => true,
                'validation_disabled' => true,
                'encrypted_nisn' => $encryptedNisn,
            ]);
        }

        // Check via EMIS API
        try {
            $result = $this->emisService->cekNisn($nisn);
            
            Log::info('AuthController: EMIS result', ['result' => $result]);

            if (isset($result['success']) && $result['success']) {
                // Transform nested data structure to flat structure for frontend
                $emisData = $result['data'];
                $transformedData = null;
                $dataSource = [];
                $isEligible = true;
                $warningMessage = null;
                
                if ($emisData) {
                    $kemdikbud = $emisData['kemdikbud'] ?? null;
                    $kemenag = $emisData['kemenag'] ?? null;
                    
                    Log::info('AuthController: Data sources', [
                        'kemdikbud' => $kemdikbud ? 'available' : 'null',
                        'kemenag' => $kemenag ? 'available' : 'null'
                    ]);
                    
                    // Determine data source with proper labels
                    if ($kemdikbud) $dataSource[] = 'Kemdikbud Pusdatin';
                    if ($kemenag) $dataSource[] = 'Kemenag PPDB';
                    
                    // Get tingkat pendidikan (grade level)
                    $tingkatPendidikan = null;
                    $levelName = null;
                    
                    if ($kemdikbud && isset($kemdikbud['tingkat_pendidikan'])) {
                        $tingkatPendidikan = (int) $kemdikbud['tingkat_pendidikan'];
                    } elseif ($kemenag && isset($kemenag['level_id'])) {
                        $tingkatPendidikan = (int) $kemenag['level_id'];
                        $levelName = $kemenag['level_name'] ?? null;
                    }
                    
                    // Validate: Must be grade 9 (SMP/MTs) for MA registration
                    $isEligible = ($tingkatPendidikan === 9);
                    $warningMessage = null;
                    
                    if (!$isEligible) {
                        $currentLevel = $levelName ?? "Kelas $tingkatPendidikan";
                        $warningMessage = "NISN tidak dapat didaftarkan. Status kelas saat ini: $currentLevel. Peserta didik yang dapat didaftarkan adalah siswa Kelas 9 SMP/MTs.";
                    }
                    
                    // Merge data from both sources, prioritize Kemdikbud for basic data
                    // Determine gender from both sources
                    $jenisKelamin = null;
                    if (isset($kemdikbud['jenis_kelamin'])) {
                        $jenisKelamin = $kemdikbud['jenis_kelamin'];
                    } elseif (isset($kemenag['gender_id'])) {
                        $jenisKelamin = $kemenag['gender_id'] == 1 ? 'L' : 'P';
                    }
                    
                    $transformedData = [
                        // Data Dasar
                        'nama' => $kemdikbud['nama'] ?? ($kemenag['full_name'] ?? null),
                        'nisn' => $kemdikbud['nisn'] ?? ($kemenag['nisn'] ?? $nisn),
                        'nik' => $kemdikbud['nik'] ?? ($kemenag['nik'] ?? null),
                        
                        // Personal
                        'tempat_lahir' => $kemdikbud['tempat_lahir'] ?? ($kemenag['birth_place'] ?? null),
                        'tanggal_lahir' => $kemdikbud['tanggal_lahir'] ?? ($kemenag['birth_date'] ?? null),
                        'jenis_kelamin' => $jenisKelamin,
                        'agama' => $this->mapAgama($kemenag['religion_id'] ?? null),
                        
                        // Sekolah
                        'sekolah_asal' => $kemdikbud['sekolah'] ?? ($kemenag['institution_name'] ?? null),
                        'npsn' => $kemdikbud['npsn'] ?? ($kemenag['npsn'] ?? null),
                        'nsm' => $kemenag['institution_nsm'] ?? null,
                        'tingkat_pendidikan' => $tingkatPendidikan,
                        'level_name' => $levelName,
                        
                        // Alamat Lengkap (dari Kemenag)
                        'alamat' => $kemenag['address'] ?? null,
                        'rt' => $kemenag['rt'] ?? null,
                        'rw' => $kemenag['rw'] ?? null,
                        'provinsi_id' => $kemenag['province_code'] ?? null,
                        'kabupaten_id' => $kemenag['city_code'] ?? null,
                        'kecamatan_id' => $kemenag['district_code'] ?? null,
                        'kelurahan_id' => $kemenag['village_code'] ?? null,
                        'kode_pos' => $kemenag['postal_code'] ?? null,
                        
                        // Keluarga
                        'nama_ibu' => $kemdikbud['nama_ibu_kandung'] ?? ($kemenag['mother_name'] ?? null),
                        'nama_ayah' => $kemenag['father_name'] ?? null,
                        'status_dalam_keluarga' => $kemenag['child_status'] ?? null,
                        'anak_ke' => $kemenag['child_number'] ?? null,
                        'jumlah_saudara' => $kemenag['sibling_count'] ?? null,
                        
                        // Lainnya
                        'transportasi' => $kemenag['transportation'] ?? null,
                        'jarak_ke_sekolah' => $kemenag['distance_to_school'] ?? null,
                    ];
                    
                    Log::info('AuthController: Transformed data', ['data' => $transformedData]);
                }
                
                // Store EMIS data in session for registration form
                session(['emis_data_' . $nisn => $transformedData]);
                
                // Encrypt NISN for URL security
                $encryptedNisn = encrypt($nisn);
                
                // Build message
                $message = !empty($dataSource) 
                    ? 'Data ditemukan di ' . implode(' & ', $dataSource)
                    : 'NISN valid';
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $transformedData,
                    'data_source' => implode(' & ', $dataSource),
                    'is_eligible' => $isEligible,
                    'warning' => $warningMessage,
                    'encrypted_nisn' => $encryptedNisn,
                ], 200, ['Content-Type' => 'application/json']);
            } elseif (isset($result['manual_input']) && $result['manual_input']) {
                // NISN not found in EMIS, but allow manual registration
                // Store empty data for manual input
                session(['emis_data_' . $nisn => null]);
                
                // Encrypt NISN for URL security
                $encryptedNisn = encrypt($nisn);
                
                return response()->json([
                    'success' => true,
                    'message' => 'NISN tidak ditemukan di database EMIS. Anda dapat melanjutkan dengan input manual.',
                    'data' => null,
                    'manual_input' => true,
                    'encrypted_nisn' => $encryptedNisn,
                ]);
            } else {
                // Error from EMIS or unexpected response
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Terjadi kesalahan saat menghubungi server EMIS',
                    'data' => null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AuthController cekNisn error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data NISN',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Helper method to map religion_id to string
     */
    private function mapAgama($religionId)
    {
        $religions = [
            1 => 'Islam',
            2 => 'Kristen',
            3 => 'Katolik',
            4 => 'Hindu',
            5 => 'Buddha',
            6 => 'Konghucu',
            99 => 'Lainnya',
        ];
        
        return $religions[$religionId] ?? null;
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm(Request $request)
    {
        // Get encrypted NISN from query parameter
        $encryptedNisn = $request->query('token');
        
        if (!$encryptedNisn) {
            return redirect()->route('pendaftar.landing')->with('error', 'Token tidak valid');
        }
        
        try {
            // Decrypt NISN
            $nisn = decrypt($encryptedNisn);
        } catch (\Exception $e) {
            Log::warning('Invalid registration token', ['token' => $encryptedNisn]);
            return redirect()->route('pendaftar.landing')->with('error', 'Token tidak valid atau sudah kadaluarsa');
        }
        
        // Validate NISN format
        if (!preg_match('/^\d{10}$/', $nisn)) {
            return redirect()->route('pendaftar.landing')->with('error', 'Token tidak valid');
        }
        
        // Check if already registered
        $existing = CalonSiswa::where('nisn', $nisn)->first();
        if ($existing) {
            return redirect()->route('pendaftar.landing')->with('error', 'NISN sudah terdaftar. Silakan login.');
        }
        
        // Get EMIS data from session (set during cekNisn)
        $emisData = session('emis_data_' . $nisn);
        
        // Allow registration even if emisData is null (manual input)
        // Check if session key exists (means cekNisn was called)
        if (!session()->has('emis_data_' . $nisn)) {
            return redirect()->route('pendaftar.landing')->with('error', 'Silakan cek NISN terlebih dahulu');
        }
        
        return view('pendaftar.register-simple', [
            'nisn' => $nisn,
            'nama_lengkap' => $emisData['nama'] ?? '',
            'emis_data' => $emisData ?? [],
        ]);
    }

    /**
     * Process registration - simplified version
     */
    public function register(Request $request)
    {
        Log::info('Registration attempt', [
            'nisn' => $request->nisn,
            'nama_lengkap' => $request->nama_lengkap,
            'encrypted_token' => $request->encrypted_token ? 'present' : 'missing',
            'emis_data' => $request->emis_data ? 'present' : 'missing',
            'location' => [
                'latitude' => $request->registration_latitude,
                'longitude' => $request->registration_longitude,
                'accuracy' => $request->registration_accuracy,
                'source' => $request->registration_location_source,
            ],
        ]);
        
        // Validate input
        $request->validate([
            'nisn' => 'required|string|size:10|unique:calon_siswas,nisn',
            'nama_lengkap' => 'required|string|max:100',
            'nomor_hp' => 'required|string|min:10|max:15',
            'email' => 'nullable|email|unique:users,email|unique:calon_siswas,email',
            'emis_data' => 'required|json',
            'encrypted_token' => 'required',
        ], [
            'nisn.unique' => 'NISN sudah terdaftar',
            'email.unique' => 'Email sudah digunakan',
            'nomor_hp.required' => 'Nomor WhatsApp wajib diisi',
        ]);

        // Normalize phone number untuk pengecekan
        $phoneNormalized = $this->normalizePhoneNumber($request->nomor_hp);
        
        // Check if phone number already registered
        $existingPhone = CalonSiswa::where(function($query) use ($phoneNormalized, $request) {
            $query->where('nomor_hp', $phoneNormalized)
                  ->orWhere('nomor_hp', $request->nomor_hp)
                  ->orWhere('nomor_hp', '+62' . ltrim($request->nomor_hp, '0'))
                  ->orWhere('nomor_hp', '0' . substr($phoneNormalized, 3));
        })->first();
        
        if ($existingPhone) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['nomor_hp' => ['Nomor WhatsApp sudah digunakan oleh pendaftar lain.']]
                ], 422);
            }
            return back()->withErrors(['nomor_hp' => 'Nomor WhatsApp sudah digunakan oleh pendaftar lain.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Parse EMIS data
            $emisData = json_decode($request->emis_data, true);
            
            // Generate password (8 karakter random)
            $password = $this->generatePassword(8);

            // Determine email (use input or generate from NISN)
            $email = $request->email ?: $request->nisn . '@ppdb.temp';

            // Create user account
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            // Assign pendaftar role
            $user->assignRole('pendaftar');

            // Get active tahun pelajaran & gelombang
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();
            $gelombangAktif = GelombangPendaftaran::where('is_active', true)
                ->where('tanggal_buka', '<=', now())
                ->where('tanggal_tutup', '>=', now())
                ->first();

            // Create calon siswa with EMIS data
            $calonSiswa = CalonSiswa::create([
                'user_id' => $user->id,
                
                // Identitas
                'nisn' => $request->nisn,
                'nisn_valid' => true, // From EMIS
                'nik' => $emisData['nik'] ?? null,
                
                // Data Personal
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $emisData['tempat_lahir'] ?? null,
                'tanggal_lahir' => $emisData['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $emisData['jenis_kelamin'] ?? null,
                'agama' => $emisData['agama'] ?? null,
                
                // Contact
                'email' => $email,
                'nomor_hp' => $request->nomor_hp,
                
                // Sekolah Asal
                'nama_sekolah_asal' => $emisData['sekolah_asal'] ?? null,
                'npsn_asal_sekolah' => $emisData['npsn'] ?? null,
                'nsm_asal_sekolah' => $emisData['nsm'] ?? null,
                
                // Alamat Lengkap (field suffix _siswa)
                'alamat_siswa' => $emisData['alamat'] ?? null,
                'rt_siswa' => $emisData['rt'] ?? null,
                'rw_siswa' => $emisData['rw'] ?? null,
                'kodepos_siswa' => $emisData['kode_pos'] ?? null,
                'provinsi_id_siswa' => $emisData['provinsi_id'] ?? null,
                'kabupaten_id_siswa' => $emisData['kabupaten_id'] ?? null,
                'kecamatan_id_siswa' => $emisData['kecamatan_id'] ?? null,
                'kelurahan_id_siswa' => $emisData['kelurahan_id'] ?? null,
                
                // Keluarga
                'nama_ibu' => $emisData['nama_ibu'] ?? null,
                'nama_ayah' => $emisData['nama_ayah'] ?? null,
                'status_dalam_keluarga' => $emisData['status_dalam_keluarga'] ?? null,
                'anak_ke' => $emisData['anak_ke'] ?? null,
                'jumlah_saudara' => $emisData['jumlah_saudara'] ?? null,
                
                // Transportasi
                'transportasi' => $emisData['transportasi'] ?? null,
                'jarak_ke_sekolah' => $emisData['jarak_ke_sekolah'] ?? null,
                
                // Administrasi
                'jalur_pendaftaran_id' => $gelombangAktif?->jalur_id,
                'gelombang_pendaftaran_id' => $gelombangAktif?->id,
                'tahun_pelajaran_id' => $tahunAktif?->id ?? $gelombangAktif?->jalur?->tahun_pelajaran_id,
                'tanggal_registrasi' => now(),
                'status_verifikasi' => 'pending',
                'status_admisi' => 'pending',
                
                // Location tracking (from landing page)
                'registration_latitude' => $request->registration_latitude,
                'registration_longitude' => $request->registration_longitude,
                'registration_accuracy' => $request->registration_accuracy,
                'registration_location_source' => $request->registration_location_source,
                'registration_city' => $request->registration_city,
                'registration_region' => $request->registration_region,
                'registration_address' => $request->registration_address,
                'registration_ip' => $request->ip(),
                'registration_device' => $this->getDeviceType($request->header('User-Agent')),
                'registration_browser' => $this->getBrowserName($request->header('User-Agent')),
            ]);

            // Reverse geocode if coordinates available but location data not provided from client
            if ($request->filled('registration_latitude') && $request->filled('registration_longitude') 
                && !$request->filled('registration_city')) {
                try {
                    $geoData = $this->reverseGeocode(
                        (float) $request->registration_latitude,
                        (float) $request->registration_longitude
                    );
                    if ($geoData) {
                        $calonSiswa->update([
                            'registration_address' => $geoData['address'] ?? null,
                            'registration_city' => $geoData['city'] ?? null,
                            'registration_region' => $geoData['region'] ?? null,
                            'registration_country' => $geoData['country'] ?? null,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Reverse geocode failed', ['error' => $e->getMessage()]);
                }
            }

            // Generate nomor registrasi
            $calonSiswa->nomor_registrasi = $calonSiswa->generateNomorRegistrasi();
            $calonSiswa->save();

            DB::commit();

            // Prepare credentials data
            $credentials = [
                'username' => $request->nisn,
                'email' => $email,
                'password' => $password,
                'nama_siswa' => $request->nama_lengkap,
                'nomor_registrasi' => $calonSiswa->nomor_registrasi,
            ];

            // Try to send via WhatsApp
            $waSent = false;
            if ($this->whatsAppService->isActive()) {
                $waResult = $this->whatsAppService->sendRegistrationCredentials([
                    'phone' => $request->nomor_hp,
                    'nama_siswa' => $request->nama_lengkap,
                    'username' => $request->nisn,
                    'password' => $password,
                    'url_login' => route('pendaftar.login'),
                    'nama_sekolah' => config('app.name'),
                    'tahun_pelajaran' => $tahunAktif?->nama ?? date('Y'),
                ]);
                $waSent = $waResult['success'];
            }

            // Store credentials in session for display
            session(['registration_credentials' => $credentials]);
            session(['wa_sent' => $waSent]);

            // Auto login user
            Auth::login($user);

            Log::info('New registration', [
                'nisn' => $request->nisn,
                'email' => $email,
                'nomor_registrasi' => $calonSiswa->nomor_registrasi,
                'wa_sent' => $waSent,
            ]);

            // Clear EMIS data from session
            session()->forget('emis_data_' . $request->nisn);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil!',
                    'redirect' => route('pendaftar.register.success')
                ]);
            }

            return redirect()->route('pendaftar.register.success');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'
                ], 500);
            }
            
            return back()->withInput()->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    /**
     * Show registration success page
     */
    public function registrationSuccess()
    {
        $credentials = session('registration_credentials');
        $waSent = session('wa_sent', false);

        if (!$credentials) {
            return redirect()->route('pendaftar.landing');
        }

        // Clear session after displaying
        session()->forget(['registration_credentials', 'wa_sent', 'emis_data']);

        return view('pendaftar.register-success', compact('credentials', 'waSent'));
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('pendaftar.login');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Can be NISN or email
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nisn';
        
        // Find user by email or by NISN through calon_siswa
        if ($loginField === 'email') {
            $user = User::where('email', $request->login)->first();
        } else {
            $calonSiswa = CalonSiswa::where('nisn', $request->login)->first();
            $user = $calonSiswa?->user;
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'NISN/Email atau password salah');
        }

        // Check if user has pendaftar role
        if (!$user->hasRole('pendaftar')) {
            return back()->withInput()->with('error', 'Akun ini bukan akun pendaftar');
        }

        Auth::login($user, $request->has('remember'));

        return redirect()->intended(route('pendaftar.dashboard'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pendaftar.landing');
    }

    /**
     * Generate random password
     * Format: Huruf kapital + Angka + 1 karakter spesial
     * Excluded: I, O, Q (mirip angka), 1, 0 (mirip huruf)
     */
    protected function generatePassword(int $length = 8): string
    {
        // Karakter yang digunakan (tanpa I, O, Q, 1, 0)
        $uppercase = 'ABCDEFGHJKLMNPRSTUVWXYZ'; // tanpa I, O, Q
        $numbers = '23456789'; // tanpa 1, 0
        $special = '@#$%&*!';
        
        // Pastikan minimal ada 1 huruf kapital, 1 angka, dan 1 karakter spesial
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)]; // 1 huruf kapital
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)]; // 1 huruf kapital lagi
        $password .= $numbers[random_int(0, strlen($numbers) - 1)]; // 1 angka
        $password .= $numbers[random_int(0, strlen($numbers) - 1)]; // 1 angka lagi
        $password .= $special[random_int(0, strlen($special) - 1)]; // 1 karakter spesial
        
        // Sisa karakter (campuran huruf kapital dan angka)
        $allChars = $uppercase . $numbers;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Acak urutan password
        $password = str_shuffle($password);
        
        return $password;
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
     * Reverse geocode coordinates to address
     */
    protected function reverseGeocode(float $lat, float $lng): ?array
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'PPDB-App/1.0',
                'Accept-Language' => 'id',
            ])->timeout(5)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];
                
                // Build display name from parts
                $parts = array_filter([
                    $addr['village'] ?? $addr['suburb'] ?? $addr['neighbourhood'] ?? null,
                    $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? null,
                    $addr['state'] ?? null,
                ]);
                
                return [
                    'address' => $data['display_name'] ?? implode(', ', $parts),
                    'city' => $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? null,
                    'region' => $addr['state'] ?? null,
                    'country' => $addr['country'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocode error', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Get device type from user agent
     */
    protected function getDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';
        
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/(mobile|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop)/i', $userAgent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Get browser name from user agent
     */
    protected function getBrowserName(?string $userAgent): string
    {
        if (!$userAgent) return 'Unknown';
        
        if (preg_match('/MSIE/i', $userAgent) || preg_match('/Trident/i', $userAgent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Edge (Chromium)';
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
        
        return 'Unknown';
    }
}
