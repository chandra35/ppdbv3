<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Slider;
use App\Models\JadwalPpdb;
use App\Models\SiteSettings;
use App\Models\PpdbSettings;
use App\Models\SekolahSettings;
use App\Models\JalurPendaftaran;
use App\Models\AlurPendaftaran;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LandingController extends Controller
{
    public function index()
    {
        // Get site settings
        $siteSettings = SiteSettings::instance();
        
        // Get PPDB settings
        $ppdbSettings = PpdbSettings::first();
        
        // Get Sekolah settings
        $sekolahSettings = SekolahSettings::getSettings();
        
        // Get jalur pendaftaran aktif dengan gelombang yang sedang dibuka
        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = \App\Models\TahunPelajaran::where('is_active', true)->first();
        
        $jalurAktif = JalurPendaftaran::active()
            ->when($tahunPelajaranAktif, function($q) use ($tahunPelajaranAktif) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranAktif->id);
            })
            ->with(['gelombang' => function($q) {
                $q->where('is_active', true)
                  ->whereIn('status', ['open', 'upcoming'])
                  ->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();
        
        // Get gelombang aktif untuk countdown (dari jalur tahun pelajaran aktif)
        $gelombangAktif = \App\Models\GelombangPendaftaran::with('jalur')
            ->where('is_active', true)
            ->whereIn('status', ['open', 'upcoming'])
            ->when($tahunPelajaranAktif, function($q) use ($tahunPelajaranAktif) {
                $q->whereHas('jalur', function($jq) use ($tahunPelajaranAktif) {
                    $jq->where('tahun_pelajaran_id', $tahunPelajaranAktif->id);
                });
            })
            ->orderBy('tanggal_buka', 'asc')
            ->first();
        
        // Tentukan status pendaftaran berdasarkan tanggal dan waktu
        $now = now();
        $statusPendaftaran = null;
        $countdownTarget = null;
        
        if ($gelombangAktif) {
            // Kombinasi tanggal dan waktu (gunakan timezone lokal)
            $tanggalBuka = $gelombangAktif->tanggal_buka->timezone(config('app.timezone'))->format('Y-m-d') . ' ' . ($gelombangAktif->waktu_buka ?? '00:00:00');
            $tanggalTutup = $gelombangAktif->tanggal_tutup->timezone(config('app.timezone'))->format('Y-m-d') . ' ' . ($gelombangAktif->waktu_tutup ?? '23:59:59');
            
            $waktuBuka = \Carbon\Carbon::parse($tanggalBuka, config('app.timezone'));
            $waktuTutup = \Carbon\Carbon::parse($tanggalTutup, config('app.timezone'));
            
            if ($now->lt($waktuBuka)) {
                $statusPendaftaran = 'upcoming'; // Belum dibuka
                $countdownTarget = $waktuBuka; // Countdown ke tanggal buka
            } elseif ($now->between($waktuBuka, $waktuTutup)) {
                $statusPendaftaran = 'open'; // Sedang dibuka
                $countdownTarget = null; // Tidak perlu countdown saat pendaftaran dibuka
            } else {
                $statusPendaftaran = 'closed'; // Sudah ditutup
                $countdownTarget = null;
            }
        }
        
        // Get active sliders
        $sliders = Slider::active()->ordered()->get();
        
        // Get published beritas (limit 6)
        $beritas = Berita::active()->limit(6)->get();
        
        // Get featured beritas for highlight
        $featuredBeritas = Berita::active()->featured()->limit(3)->get();
        
        // Get active jadwal
        $jadwals = JadwalPpdb::active()->get();
        
        // Get active alur pendaftaran
        $alurPendaftaran = AlurPendaftaran::getActiveOrdered();
        
        return view('ppdb.landing', compact(
            'siteSettings',
            'ppdbSettings',
            'sekolahSettings',
            'jalurAktif',
            'gelombangAktif',
            'statusPendaftaran',
            'countdownTarget',
            'sliders',
            'beritas',
            'featuredBeritas',
            'jadwals',
            'alurPendaftaran'
        ));
    }

    /**
     * Show single berita
     */
    public function showBerita($slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        
        // Increment view count
        $berita->incrementViews();
        
        // Get site settings
        $siteSettings = SiteSettings::instance();
        
        // Get related beritas
        $relatedBeritas = Berita::active()
            ->where('id', '!=', $berita->id)
            ->when($berita->kategori, function($q) use ($berita) {
                return $q->where('kategori', $berita->kategori);
            })
            ->limit(3)
            ->get();
        
        return view('ppdb.berita-detail', compact('berita', 'siteSettings', 'relatedBeritas'));
    }

    public function showLoginForm()
    {
        // If already logged in, redirect accordingly
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $siteSettings = SiteSettings::instance();
        $sekolahSettings = SekolahSettings::getSettings();
        return view('ppdb.login', compact('siteSettings', 'sekolahSettings'));
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user)
    {
        // User dengan role yang bisa akses admin panel → ke admin.dashboard
        if ($user->canAccessAdminPanel()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Calon Siswa / User biasa → redirect ke pendaftar.dashboard
        return redirect()->route('pendaftar.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|min:1',
        ], [
            'login.required' => 'Email/Username/NISN/NIP/NIK wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        $loginField = $credentials['login'];
        $password = $credentials['password'];
        
        // Determine login type and find user
        $user = null;
        
        // 1. Check if it's an email
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginField)->first();
        }
        // 2. Check if it's numeric (could be NISN, NIP, NIK, or numeric username)
        elseif (is_numeric($loginField)) {
            // Try to find GTK by NIP
            $gtk = \App\Models\LocalGtk::where('nip', $loginField)->first();
            if ($gtk && $gtk->email) {
                $user = User::where('email', $gtk->email)->first();
            }
            
            // If not found, try GTK by NIK
            if (!$user) {
                $gtk = \App\Models\LocalGtk::where('nik', $loginField)->first();
                if ($gtk && $gtk->email) {
                    $user = User::where('email', $gtk->email)->first();
                }
            }
            
            // If not found, try CalonSiswa by NISN
            if (!$user) {
                $calonSiswa = \App\Models\CalonSiswa::where('nisn', $loginField)->first();
                if ($calonSiswa && $calonSiswa->user_id) {
                    $user = User::find($calonSiswa->user_id);
                }
            }
            
            // If still not found, try username (for numeric usernames like NIP/NIK)
            if (!$user) {
                $user = User::where('username', $loginField)->first();
            }
        }
        // 3. Otherwise, try username
        else {
            $user = User::where('username', $loginField)->first();
        }

        if (!$user) {
            return back()->withErrors([
                'login' => 'Email/Username/NISN/NIP/NIK tidak terdaftar dalam sistem.',
            ])->onlyInput('login');
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('login');
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        // Set session flag untuk menampilkan modal informasi (untuk pendaftar)
        if ($user->hasRole('pendaftar')) {
            session(['show_info_modal' => true]);
        }

        // Log activity
        try {
            ActivityLog::log('login', 'User login berhasil: ' . $user->email . ' (via: ' . $loginField . ')');
        } catch (\Exception $e) {
            // Ignore if activity log fails
        }

        Log::info('Login berhasil', ['login' => $loginField, 'email' => $user->email, 'ip' => $request->ip()]);

        // Redirect based on user role
        $roleName = 'User';
        if ($user->isAdmin()) {
            $roleName = 'Administrator';
        } elseif ($user->canAccessAdminPanel()) {
            // Ambil nama role pertama yang bisa akses admin
            $adminRole = $user->roles->first(fn($r) => $r->can_access_admin);
            $roleName = $adminRole ? $adminRole->display_name : 'Operator';
        }

        return $this->redirectBasedOnRole($user)
            ->with('success', 'Selamat datang, ' . $user->name . '! Anda login sebagai ' . $roleName . '.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log activity
        try {
            if ($user) {
                ActivityLog::log('logout', 'User logout: ' . $user->email);
            }
        } catch (\Exception $e) {
            // Ignore if activity log fails
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppdb.landing')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
