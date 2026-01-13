<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\PpdbSettings;
use App\Models\User;
use App\Models\VisitorLog;
use App\Services\EmisNisnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Ramsey\Uuid\Uuid;

class RegisterController extends Controller
{
    /**
     * Get active/open Jalur Pendaftaran
     */
    protected function getJalurAktif()
    {
        return JalurPendaftaran::getAktif();
    }

    /**
     * Validate that registration is open
     */
    protected function validateRegistrasiDibuka()
    {
        $jalur = $this->getJalurAktif();
        
        if (!$jalur) {
            return [
                'status' => false,
                'message' => 'Maaf, pendaftaran PPDB sedang tidak dibuka.',
                'jalur' => null
            ];
        }
        
        if (!$jalur->bisaMenerimaPendaftar()) {
            return [
                'status' => false,
                'message' => 'Maaf, kuota pendaftaran jalur ini sudah penuh.',
                'jalur' => $jalur
            ];
        }
        
        return [
            'status' => true,
            'message' => null,
            'jalur' => $jalur
        ];
    }

    /**
     * STEP 1: Validasi NISN & Create Account
     */
    public function step1()
    {
        $validasi = $this->validateRegistrasiDibuka();
        
        if (!$validasi['status']) {
            return redirect()->route('ppdb.landing')
                ->with('warning', $validasi['message']);
        }
        
        $jalurAktif = $validasi['jalur'];
        
        return view('ppdb.step1', compact('jalurAktif'));
    }

    public function validateNisn(Request $request)
    {
        $validasi = $this->validateRegistrasiDibuka();
        
        if (!$validasi['status']) {
            return redirect()->route('ppdb.landing')
                ->with('warning', $validasi['message']);
        }
        
        $jalur = $validasi['jalur'];
        
        $validated = $request->validate([
            'nisn' => 'required|digits:10|unique:calon_siswas,nisn',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'nisn.unique' => 'NISN sudah terdaftar di sistem.',
            'email.unique' => 'Email sudah digunakan. Silahkan gunakan email lain.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        // Check if NISN validation is enabled
        $settings = \App\Models\PpdbSettings::first();
        $validasiNisnAktif = $settings ? $settings->validasi_nisn_aktif : true;
        
        // Prepare session data
        $sessionData = [
            'ppdb_step' => 1,
            'ppdb_jalur_id' => $jalur->id,
            'ppdb_nisn' => $validated['nisn'],
            'ppdb_email' => $validated['email'],
            'ppdb_password' => $validated['password'],
        ];

        // If NISN validation is disabled, skip EMIS check and proceed with manual input
        if (!$validasiNisnAktif) {
            $sessionData['ppdb_nisn_valid'] = false;
            $sessionData['ppdb_emis_data'] = null;
            session($sessionData);
            return redirect()->route('ppdb.register.step2')
                ->with('info', 'Validasi NISN dinonaktifkan. Silahkan isi data secara manual.');
        }

        // Validate NISN against EMIS API
        $emisService = new EmisNisnService();
        $emisResult = $emisService->cekNisn($validated['nisn']);
        
        // Store EMIS data if found (for pre-filling form in step 2)
        if ($emisResult['success'] && $emisResult['data']) {
            $sessionData['ppdb_nisn_valid'] = true;
            $sessionData['ppdb_emis_data'] = $emisService->extractStudentData($emisResult['data']);
            session($sessionData);
            return redirect()->route('ppdb.register.step2')
                ->with('success', 'NISN valid! Data ditemukan di EMIS. Lanjutkan ke step 2.');
        } else {
            // NISN not found in EMIS but allow to continue with warning
            $sessionData['ppdb_nisn_valid'] = false;
            $sessionData['ppdb_emis_data'] = null;
            session($sessionData);
            return redirect()->route('ppdb.register.step2')
                ->with('warning', 'NISN tidak ditemukan di database EMIS. Silahkan isi data secara manual.');
        }
    }

    /**
     * API endpoint to check NISN via EMIS
     */
    public function apiCekNisn(Request $request)
    {
        $request->validate([
            'nisn' => 'required|digits:10'
        ]);
        
        $nisn = $request->nisn;
        
        // Check if NISN already registered
        $existing = CalonSiswa::where('nisn', $nisn)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'NISN sudah terdaftar di sistem PPDB.',
                'data' => null
            ]);
        }
        
        // Check if NISN validation is enabled
        $settings = \App\Models\PpdbSettings::first();
        $validasiNisnAktif = $settings ? $settings->validasi_nisn_aktif : true;
        
        // If validation is disabled, allow direct registration with manual input
        if (!$validasiNisnAktif) {
            return response()->json([
                'success' => true,
                'message' => 'Validasi NISN dinonaktifkan. Silakan lanjutkan dengan input manual.',
                'data' => null,
                'manual_input' => true,
                'validation_disabled' => true,
            ]);
        }
        
        // Check against EMIS API
        $emisService = new EmisNisnService();
        $result = $emisService->cekNisn($nisn);
        
        if ($result['success']) {
            $extractedData = $emisService->extractStudentData($result['data']);
            return response()->json([
                'success' => true,
                'message' => 'Data NISN ditemukan',
                'data' => [
                    'raw' => $result['data'],
                    'extracted' => $extractedData
                ]
            ]);
        }
        
        return response()->json($result);
    }

    /**
     * STEP 2: Data Diri Siswa (sesuai SIMANSAV3)
     */
    public function step2()
    {
        if (session('ppdb_step') < 1) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $provinces = Province::pluck('name', 'code');
        $agamaOptions = ['islam' => 'Islam', 'kristen' => 'Kristen', 'katolik' => 'Katolik', 'hindu' => 'Hindu', 'budha' => 'Budha', 'konghucu' => 'Konghucu'];
        
        // Get EMIS data if available (for pre-filling form)
        $emisData = session('ppdb_emis_data');
        $nisn = session('ppdb_nisn');
        
        return view('ppdb.step2', compact('provinces', 'agamaOptions', 'emisData', 'nisn'));
    }

    public function storePersonalData(Request $request)
    {
        if (session('ppdb_step') < 1) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $validated = $request->validate([
            'nik' => 'required|digits:16',
            'nama_lengkap' => 'required|string|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|in:islam,kristen,katolik,hindu,budha,konghucu',
            'jumlah_saudara' => 'nullable|integer|min:0|max:20',
            'anak_ke' => 'nullable|integer|min:1|max:20',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            
            // Alamat siswa
            'alamat_siswa' => 'required|string|max:500',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
            'kode_pos_siswa' => 'nullable|string|max:10',
            
            // Kontak
            'no_hp' => 'nullable|string|max:20',
            
            // Asal sekolah
            'npsn_asal' => 'nullable|string|max:20',
            'sekolah_asal' => 'required|string|max:255',
            'alamat_sekolah_asal' => 'nullable|string|max:500',
        ], [
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
        ]);

        session([
            'ppdb_step' => 2,
            'ppdb_data_diri' => $validated,
        ]);

        return redirect()->route('ppdb.register.step3')->with('success', 'Data diri tersimpan. Lanjutkan ke step 3.');
    }

    /**
     * STEP 3: Data Orang Tua/Wali (sesuai SIMANSAV3)
     */
    public function step3()
    {
        if (session('ppdb_step') < 2) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $provinces = Province::pluck('name', 'code');
        $pendidikanOptions = CalonOrtu::PENDIDIKAN;
        $pekerjaanOptions = CalonOrtu::PEKERJAAN;
        $penghasilanOptions = CalonOrtu::PENGHASILAN;
        $hubunganWaliOptions = CalonOrtu::HUBUNGAN_WALI;
        
        // Get EMIS data for name prefill (nama ayah, ibu)
        $emisData = session('ppdb_emis_data');
        
        return view('ppdb.step3', compact('provinces', 'pendidikanOptions', 'pekerjaanOptions', 'penghasilanOptions', 'hubunganWaliOptions', 'emisData'));
    }

    public function storeParentData(Request $request)
    {
        if (session('ppdb_step') < 2) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $rules = [
            'no_kk' => 'required|digits:16',
            
            // Ayah
            'status_ayah' => 'required|in:masih_hidup,meninggal',
            'nik_ayah' => 'nullable|digits:16',
            'nama_ayah' => 'required|string|max:100',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date|before:today',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'no_hp_ayah' => 'nullable|string|max:20',
            
            // Ibu
            'status_ibu' => 'required|in:masih_hidup,meninggal',
            'nik_ibu' => 'nullable|digits:16',
            'nama_ibu' => 'required|string|max:100',
            'tempat_lahir_ibu' => 'nullable|string|max:100',
            'tanggal_lahir_ibu' => 'nullable|date|before:today',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'no_hp_ibu' => 'nullable|string|max:20',
            
            // Wali (optional)
            'tinggal_dengan_wali' => 'nullable|boolean',
            'nama_wali' => 'nullable|string|max:100',
            'hubungan_wali' => 'nullable|string|max:50',
            'nik_wali' => 'nullable|digits:16',
            'tempat_lahir_wali' => 'nullable|string|max:100',
            'tanggal_lahir_wali' => 'nullable|date|before:today',
            'pendidikan_wali' => 'nullable|string|max:50',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'penghasilan_wali' => 'nullable|string|max:50',
            'no_hp_wali' => 'nullable|string|max:20',
            
            // Alamat ortu
            'alamat_ortu' => 'required|string|max:500',
            'rt_ortu' => 'nullable|string|max:5',
            'rw_ortu' => 'nullable|string|max:5',
            'provinsi_id_ortu' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_ortu' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_ortu' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_ortu' => 'required|exists:indonesia_villages,code',
            'kode_pos_ortu' => 'nullable|string|max:10',
        ];

        // If tinggal_dengan_wali, wali data is required
        if ($request->boolean('tinggal_dengan_wali')) {
            $rules['nama_wali'] = 'required|string|max:100';
            $rules['hubungan_wali'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules);

        session([
            'ppdb_step' => 3,
            'ppdb_data_ortu' => $validated,
        ]);

        return redirect()->route('ppdb.register.step4')->with('success', 'Data orang tua tersimpan. Lanjutkan ke step 4.');
    }

    /**
     * STEP 4: Upload Dokumen
     */
    public function step4()
    {
        if (session('ppdb_step') < 3) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $requiredDocs = [
            'foto' => ['label' => 'Pas Foto (3x4)', 'required' => true],
            'kk' => ['label' => 'Kartu Keluarga', 'required' => true],
            'akta_lahir' => ['label' => 'Akta Kelahiran', 'required' => true],
            'ktp_ortu' => ['label' => 'KTP Orang Tua', 'required' => false],
            'ijazah' => ['label' => 'Ijazah/SKL', 'required' => false],
            'raport' => ['label' => 'Raport', 'required' => false],
            'sertifikat_prestasi' => ['label' => 'Sertifikat Prestasi (jika ada)', 'required' => false],
        ];

        return view('ppdb.step4', compact('requiredDocs'));
    }

    public function uploadDocuments(Request $request)
    {
        if (session('ppdb_step') < 3) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $validated = $request->validate([
            'foto' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'kk' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'akta_lahir' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp_ortu' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'raport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'sertifikat_prestasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'foto.required' => 'Pas foto wajib diunggah.',
            'kk.required' => 'Kartu Keluarga wajib diunggah.',
            'akta_lahir.required' => 'Akta Kelahiran wajib diunggah.',
            '*.mimes' => 'File harus berformat PDF atau gambar (JPG, PNG).',
            '*.max' => 'Ukuran file maksimal 5MB.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $uploadedDocs = [];
        $documentTypes = ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport', 'sertifikat_prestasi'];
        $nisn = session('ppdb_nisn');

        foreach ($documentTypes as $docType) {
            if ($request->hasFile($docType)) {
                $file = $request->file($docType);
                $fileName = $docType . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('ppdb/' . $nisn, $fileName, 'public');
                
                $uploadedDocs[$docType] = [
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        session([
            'ppdb_step' => 4,
            'ppdb_uploaded_docs' => $uploadedDocs,
        ]);

        return redirect()->route('ppdb.register.step5')->with('success', 'Dokumen terupload. Lanjutkan ke review.');
    }

    /**
     * STEP 5: Review & Confirm
     */
    public function step5()
    {
        if (session('ppdb_step') < 4) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $dataSiswa = session('ppdb_data_diri');
        $dataOrtu = session('ppdb_data_ortu');
        $uploadedDocs = session('ppdb_uploaded_docs');
        $nisn = session('ppdb_nisn');
        $email = session('ppdb_email');

        // Get address names
        $dataSiswa['provinsi_nama'] = Province::where('code', $dataSiswa['provinsi_id_siswa'])->value('name');
        $dataSiswa['kabupaten_nama'] = City::where('code', $dataSiswa['kabupaten_id_siswa'])->value('name');
        $dataSiswa['kecamatan_nama'] = District::where('code', $dataSiswa['kecamatan_id_siswa'])->value('name');
        $dataSiswa['kelurahan_nama'] = Village::where('code', $dataSiswa['kelurahan_id_siswa'])->value('name');

        $dataOrtu['provinsi_nama'] = Province::where('code', $dataOrtu['provinsi_id_ortu'])->value('name');
        $dataOrtu['kabupaten_nama'] = City::where('code', $dataOrtu['kabupaten_id_ortu'])->value('name');
        $dataOrtu['kecamatan_nama'] = District::where('code', $dataOrtu['kecamatan_id_ortu'])->value('name');
        $dataOrtu['kelurahan_nama'] = Village::where('code', $dataOrtu['kelurahan_id_ortu'])->value('name');

        // Get setting wajib lokasi
        $ppdbSettings = PpdbSettings::first();
        $wajibLokasi = $ppdbSettings?->wajib_lokasi_registrasi ?? false;

        return view('ppdb.step5', compact('dataSiswa', 'dataOrtu', 'uploadedDocs', 'nisn', 'email', 'wajibLokasi'));
    }

    public function confirmRegistration(Request $request)
    {
        if (session('ppdb_step') < 4) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $request->validate([
            'agree' => 'required|accepted',
            // GPS data (optional, bisa nullable atau required tergantung setting)
            'registration_latitude' => 'nullable|numeric|between:-90,90',
            'registration_longitude' => 'nullable|numeric|between:-180,180',
            'registration_altitude' => 'nullable|numeric',
            'registration_accuracy' => 'nullable|numeric',
            'location_source' => 'nullable|string|in:gps,ip,unavailable',
        ]);

        // Check if location is required
        $ppdbSettings = PpdbSettings::first();
        $wajibLokasi = $ppdbSettings?->wajib_lokasi_registrasi ?? false;
        
        // Validate location is provided if required
        if ($wajibLokasi && !$request->filled('registration_latitude') && $request->input('location_source') !== 'ip') {
            return back()->with('error', 'Lokasi pendaftaran wajib diizinkan. Silakan aktifkan GPS atau izinkan akses lokasi.');
        }

        $validasi = $this->validateRegistrasiDibuka();
        
        if (!$validasi['status']) {
            return redirect()->route('ppdb.landing')
                ->with('error', $validasi['message']);
        }

        $jalur = $validasi['jalur'];

        DB::beginTransaction();
        
        try {
            // 1. Create User
            $user = User::create([
                'id' => Uuid::uuid4()->toString(),
                'name' => session('ppdb_data_diri.nama_lengkap'),
                'email' => session('ppdb_email'),
                'password' => Hash::make(session('ppdb_password')),
                'email_verified_at' => now(),
            ]);

            // 2. Create CalonSiswa
            $dataDiri = session('ppdb_data_diri');
            
            // Determine location source and get location data
            $locationSource = $request->input('location_source', 'unavailable');
            $gpsAddress = null;
            $gpsCity = null;
            $gpsRegion = null;
            $gpsCountry = null;
            $gpsIsp = null;
            $regLatitude = $request->registration_latitude;
            $regLongitude = $request->registration_longitude;
            
            // If GPS coordinates provided, reverse geocode
            if ($request->filled('registration_latitude') && $request->filled('registration_longitude')) {
                $geoResult = $this->reverseGeocode($request->registration_latitude, $request->registration_longitude);
                if ($geoResult) {
                    $gpsAddress = $geoResult['address'] ?? null;
                    $gpsCity = $geoResult['city'] ?? null;
                    $gpsRegion = $geoResult['region'] ?? null;
                    $gpsCountry = $geoResult['country'] ?? null;
                }
                $locationSource = 'gps';
            } 
            // If no GPS but IP fallback requested, get location from IP
            elseif ($locationSource === 'ip' || !$request->filled('registration_latitude')) {
                $ipGeoData = $this->getIpGeolocation($request->ip());
                if ($ipGeoData) {
                    $regLatitude = $ipGeoData['latitude'] ?? null;
                    $regLongitude = $ipGeoData['longitude'] ?? null;
                    $gpsCity = $ipGeoData['city'] ?? null;
                    $gpsRegion = $ipGeoData['region'] ?? null;
                    $gpsCountry = $ipGeoData['country'] ?? null;
                    $gpsIsp = $ipGeoData['isp'] ?? null;
                    $locationSource = 'ip';
                } else {
                    $locationSource = 'unavailable';
                }
            }
            
            // Parse user agent for device info
            $userAgent = $request->header('User-Agent');
            $deviceType = $this->getDeviceType($userAgent);
            $browser = $this->getBrowserName($userAgent);
            
            $calonSiswa = CalonSiswa::create([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $user->id,
                'jalur_pendaftaran_id' => session('ppdb_jalur_id'),
                'gelombang_pendaftaran_id' => $jalur->gelombangAktif()?->id,
                'nisn' => session('ppdb_nisn'),
                'nisn_valid' => session('ppdb_nisn_valid'),
                'email' => session('ppdb_email'),
                'status_verifikasi' => 'pending',
                'status_admisi' => 'pending',
                'tanggal_registrasi' => now(),
                
                // Data diri
                'nik' => $dataDiri['nik'],
                'nama_lengkap' => $dataDiri['nama_lengkap'],
                'tempat_lahir' => $dataDiri['tempat_lahir'],
                'tanggal_lahir' => $dataDiri['tanggal_lahir'],
                'jenis_kelamin' => $dataDiri['jenis_kelamin'],
                'agama' => $dataDiri['agama'],
                'jumlah_saudara' => $dataDiri['jumlah_saudara'] ?? null,
                'anak_ke' => $dataDiri['anak_ke'] ?? null,
                'hobi' => $dataDiri['hobi'] ?? null,
                'cita_cita' => $dataDiri['cita_cita'] ?? null,
                
                // Alamat siswa
                'alamat_siswa' => $dataDiri['alamat_siswa'],
                'rt_siswa' => $dataDiri['rt_siswa'] ?? null,
                'rw_siswa' => $dataDiri['rw_siswa'] ?? null,
                'provinsi_id_siswa' => $dataDiri['provinsi_id_siswa'],
                'kabupaten_id_siswa' => $dataDiri['kabupaten_id_siswa'],
                'kecamatan_id_siswa' => $dataDiri['kecamatan_id_siswa'],
                'kelurahan_id_siswa' => $dataDiri['kelurahan_id_siswa'],
                'kode_pos_siswa' => $dataDiri['kode_pos_siswa'] ?? null,
                
                // Kontak & Sekolah
                'no_hp' => $dataDiri['no_hp'] ?? null,
                'npsn_asal' => $dataDiri['npsn_asal'] ?? null,
                'sekolah_asal' => $dataDiri['sekolah_asal'],
                'alamat_sekolah_asal' => $dataDiri['alamat_sekolah_asal'] ?? null,
                
                // GPS & Registration Location
                'registration_latitude' => $regLatitude,
                'registration_longitude' => $regLongitude,
                'registration_altitude' => $request->registration_altitude,
                'registration_accuracy' => $request->registration_accuracy,
                'registration_address' => $gpsAddress,
                'registration_city' => $gpsCity,
                'registration_region' => $gpsRegion,
                'registration_country' => $gpsCountry,
                'registration_isp' => $gpsIsp,
                'registration_location_source' => $locationSource,
                'registration_ip' => $request->ip(),
                'registration_device' => $deviceType,
                'registration_browser' => $browser,
                'visitor_session_id' => session()->getId(),
                
                // Completion flags
                'data_diri_completed' => true,
                'data_ortu_completed' => true,
                'data_dokumen_completed' => true,
            ]);

            // Generate nomor registrasi
            $calonSiswa->nomor_registrasi = $calonSiswa->generateNomorRegistrasi();
            $calonSiswa->save();

            // 3. Create CalonOrtu
            $dataOrtu = session('ppdb_data_ortu');
            
            CalonOrtu::create([
                'id' => Uuid::uuid4()->toString(),
                'calon_siswa_id' => $calonSiswa->id,
                'no_kk' => $dataOrtu['no_kk'],
                
                // Ayah
                'status_ayah' => $dataOrtu['status_ayah'],
                'nik_ayah' => $dataOrtu['nik_ayah'] ?? null,
                'nama_ayah' => $dataOrtu['nama_ayah'],
                'tempat_lahir_ayah' => $dataOrtu['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $dataOrtu['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $dataOrtu['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $dataOrtu['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $dataOrtu['penghasilan_ayah'] ?? null,
                'no_hp_ayah' => $dataOrtu['no_hp_ayah'] ?? null,
                
                // Ibu
                'status_ibu' => $dataOrtu['status_ibu'],
                'nik_ibu' => $dataOrtu['nik_ibu'] ?? null,
                'nama_ibu' => $dataOrtu['nama_ibu'],
                'tempat_lahir_ibu' => $dataOrtu['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $dataOrtu['tanggal_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $dataOrtu['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $dataOrtu['pekerjaan_ibu'] ?? null,
                'penghasilan_ibu' => $dataOrtu['penghasilan_ibu'] ?? null,
                'no_hp_ibu' => $dataOrtu['no_hp_ibu'] ?? null,
                
                // Wali
                'tinggal_dengan_wali' => $dataOrtu['tinggal_dengan_wali'] ?? false,
                'nama_wali' => $dataOrtu['nama_wali'] ?? null,
                'hubungan_wali' => $dataOrtu['hubungan_wali'] ?? null,
                'nik_wali' => $dataOrtu['nik_wali'] ?? null,
                'tempat_lahir_wali' => $dataOrtu['tempat_lahir_wali'] ?? null,
                'tanggal_lahir_wali' => $dataOrtu['tanggal_lahir_wali'] ?? null,
                'pendidikan_wali' => $dataOrtu['pendidikan_wali'] ?? null,
                'pekerjaan_wali' => $dataOrtu['pekerjaan_wali'] ?? null,
                'penghasilan_wali' => $dataOrtu['penghasilan_wali'] ?? null,
                'no_hp_wali' => $dataOrtu['no_hp_wali'] ?? null,
                
                // Alamat ortu
                'alamat_ortu' => $dataOrtu['alamat_ortu'],
                'rt_ortu' => $dataOrtu['rt_ortu'] ?? null,
                'rw_ortu' => $dataOrtu['rw_ortu'] ?? null,
                'provinsi_id_ortu' => $dataOrtu['provinsi_id_ortu'],
                'kabupaten_id_ortu' => $dataOrtu['kabupaten_id_ortu'],
                'kecamatan_id_ortu' => $dataOrtu['kecamatan_id_ortu'],
                'kelurahan_id_ortu' => $dataOrtu['kelurahan_id_ortu'],
                'kode_pos_ortu' => $dataOrtu['kode_pos_ortu'] ?? null,
            ]);

            // 4. Create CalonDokumen
            $uploadedDocs = session('ppdb_uploaded_docs', []);
            $requiredDocs = ['foto', 'kk', 'akta_lahir'];
            
            foreach ($uploadedDocs as $jenisDoc => $docData) {
                CalonDokumen::create([
                    'id' => Uuid::uuid4()->toString(),
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDoc,
                    'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDoc] ?? $jenisDoc,
                    'nama_file' => $docData['nama_file'],
                    'file_path' => $docData['file_path'],
                    'file_size' => $docData['file_size'],
                    'mime_type' => $docData['mime_type'],
                    'storage_disk' => 'public',
                    'is_required' => in_array($jenisDoc, $requiredDocs),
                    'status_verifikasi' => 'pending',
                ]);
            }

            DB::commit();

            // Mark visitor logs as converted (pengunjung yang mendaftar)
            VisitorLog::where('session_id', session()->getId())
                ->where('converted_to_registration', false)
                ->update([
                    'calon_siswa_id' => $calonSiswa->id,
                    'converted_to_registration' => true,
                    'conversion_at' => now(),
                ]);

            // Clear session
            session()->forget([
                'ppdb_step', 'ppdb_jalur_id', 'ppdb_nisn', 'ppdb_email', 'ppdb_password', 
                'ppdb_nisn_valid', 'ppdb_data_diri', 'ppdb_data_ortu', 'ppdb_uploaded_docs',
            ]);

            return redirect()->route('ppdb.register.success', ['nomor_registrasi' => $calonSiswa->nomor_registrasi])
                ->with('success', 'Pendaftaran berhasil! Nomor registrasi Anda: ' . $calonSiswa->nomor_registrasi);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files on failure
            $uploadedDocs = session('ppdb_uploaded_docs', []);
            foreach ($uploadedDocs as $docData) {
                if (isset($docData['file_path'])) {
                    Storage::disk('public')->delete($docData['file_path']);
                }
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $nomor_registrasi = $request->query('nomor_registrasi', 'PPDB-' . date('Y') . '-00000');
        return view('ppdb.success', compact('nomor_registrasi'));
    }

    /**
     * API endpoints for cascading dropdowns
     */
    public function getKabupaten(Request $request)
    {
        $cities = City::where('province_code', $request->province_code)
            ->orderBy('name')
            ->pluck('name', 'code');
        return response()->json($cities);
    }

    public function getKecamatan(Request $request)
    {
        $districts = District::where('city_code', $request->city_code)
            ->orderBy('name')
            ->pluck('name', 'code');
        return response()->json($districts);
    }

    public function getKelurahan(Request $request)
    {
        $villages = Village::where('district_code', $request->district_code)
            ->orderBy('name')
            ->pluck('name', 'code');
        return response()->json($villages);
    }

    /**
     * Reverse geocode coordinates to address
     */
    protected function reverseGeocode($lat, $lng): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
                'addressdetails' => 1,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];
                
                return [
                    'address' => $data['display_name'] ?? null,
                    'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['county'] ?? null,
                    'region' => $address['state'] ?? $address['province'] ?? null,
                    'country' => $address['country'] ?? null,
                    'postal_code' => $address['postcode'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Reverse geocode failed: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get geolocation from IP address using ip-api.com (free, no API key needed)
     * Fallback for devices without GPS
     */
    protected function getIpGeolocation(string $ip): ?array
    {
        // Skip for localhost/private IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || 
            preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip)) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,message,country,regionName,city,lat,lon,isp',
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'country' => $data['country'] ?? null,
                        'isp' => $data['isp'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('IP geolocation failed: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get device type from user agent
     */
    protected function getDeviceType($userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get browser name from user agent
     */
    protected function getBrowserName($userAgent): string
    {
        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'OPR') !== false || strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
        
        return 'Unknown';
    }
}
