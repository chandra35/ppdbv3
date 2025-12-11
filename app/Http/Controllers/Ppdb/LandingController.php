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
        $jalurAktif = JalurPendaftaran::active()
            ->with(['gelombang' => function($q) {
                $q->open()->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();
        
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
        // Admin
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Operator/Verifikator
        if ($user->hasAnyRole(['operator', 'verifikator'])) {
            return redirect()->route('operator.dashboard');
        }
        
        // Calon Siswa / User biasa
        return redirect()->route('ppdb.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:1',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ]);

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar dalam sistem.',
            ])->onlyInput('email');
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Log activity
        try {
            ActivityLog::log('login', 'User login berhasil: ' . $user->email);
        } catch (\Exception $e) {
            // Ignore if activity log fails
        }

        Log::info('Login berhasil', ['email' => $user->email, 'ip' => $request->ip()]);

        // Redirect based on user role
        $roleName = 'User';
        if ($user->isAdmin()) {
            $roleName = 'Administrator';
        } elseif ($user->hasAnyRole(['operator', 'verifikator'])) {
            $roleName = 'Operator';
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
