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
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->first();

        return view('pendaftar.landing', compact('tahunAktif', 'jalurPendaftaran', 'gelombangAktif'));
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

        // Check via EMIS API
        $result = $this->emisService->cekNisn($nisn);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $result['data'],
            ]);
        }

        // NISN not found in EMIS, but allow manual registration
        return response()->json([
            'success' => true,
            'message' => 'NISN tidak ditemukan di database EMIS. Anda dapat melanjutkan dengan input manual.',
            'data' => null,
            'manual_input' => true,
        ]);
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm(Request $request)
    {
        $nisn = $request->query('nisn');
        $emisData = session('emis_data');
        
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $jalurPendaftaran = JalurPendaftaran::where('is_active', true)->orderBy('urutan')->get();
        $gelombangAktif = GelombangPendaftaran::where('is_active', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->get();

        return view('pendaftar.register', compact(
            'nisn', 
            'emisData', 
            'tahunAktif', 
            'jalurPendaftaran', 
            'gelombangAktif'
        ));
    }

    /**
     * Process registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|size:10|unique:calon_siswas,nisn',
            'nama_lengkap' => 'required|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'email' => 'required|email|unique:users,email|unique:calon_siswas,email',
            'nomor_hp' => 'required|string|max:15',
            'nomor_hp_ortu' => 'required|string|max:15',
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'required|exists:gelombang_pendaftaran,id',
        ], [
            'nisn.unique' => 'NISN sudah terdaftar',
            'email.unique' => 'Email sudah digunakan',
            'nomor_hp.required' => 'Nomor HP wajib diisi',
            'nomor_hp_ortu.required' => 'Nomor HP orang tua wajib diisi',
        ]);

        DB::beginTransaction();
        try {
            // Generate password
            $password = $this->generatePassword();

            // Create user account
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($password),
            ]);

            // Assign pendaftar role
            $pendaftarRole = Role::where('name', 'pendaftar')->first();
            if ($pendaftarRole) {
                $user->roles()->attach($pendaftarRole->id);
            }

            // Get active tahun pelajaran
            $tahunAktif = TahunPelajaran::where('is_active', true)->first();

            // Create calon siswa
            $calonSiswa = CalonSiswa::create([
                'user_id' => $user->id,
                'nisn' => $request->nisn,
                'nisn_valid' => $request->has('nisn_valid') ? true : false,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'email' => $request->email,
                'nomor_hp' => $request->nomor_hp,
                'nama_sekolah_asal' => $request->nama_sekolah_asal,
                'jalur_pendaftaran_id' => $request->jalur_pendaftaran_id,
                'gelombang_pendaftaran_id' => $request->gelombang_pendaftaran_id,
                'tahun_pelajaran_id' => $tahunAktif?->id,
                'tanggal_registrasi' => now(),
                'status_verifikasi' => 'pending',
                'status_admisi' => 'pending',
            ]);

            // Generate nomor registrasi
            $calonSiswa->nomor_registrasi = $calonSiswa->generateNomorRegistrasi();
            $calonSiswa->save();

            // Create parent data placeholder
            $calonSiswa->ortu()->create([
                'nomor_hp_ortu' => $request->nomor_hp_ortu,
            ]);

            DB::commit();

            // Prepare credentials data
            $credentials = [
                'username' => $request->nisn,
                'email' => $request->email,
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

            Log::info('New registration', [
                'nisn' => $request->nisn,
                'email' => $request->email,
                'nomor_registrasi' => $calonSiswa->nomor_registrasi,
                'wa_sent' => $waSent,
            ]);

            return redirect()->route('pendaftar.register.success');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            
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
     */
    protected function generatePassword(int $length = 8): string
    {
        $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
