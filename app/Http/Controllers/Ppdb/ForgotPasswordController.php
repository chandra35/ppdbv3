<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\PengaturanWa;
use App\Models\PpdbSettings;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForm()
    {
        $settings = PpdbSettings::getActive();
        $waSettings = PengaturanWa::first();
        $waActive = $waSettings && $waSettings->is_active && $waSettings->api_key;
        
        return view('ppdb.forgot-password', compact('settings', 'waActive'));
    }

    /**
     * Process forgot password request
     */
    public function sendReset(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|max:20',
        ], [
            'nisn.required' => 'NISN wajib diisi',
        ]);

        // Check if WhatsApp service is active
        $waService = new WhatsAppService();
        if (!$waService->isActive()) {
            return back()->with('error', 'Layanan reset password via WhatsApp tidak tersedia saat ini. Silakan hubungi admin.');
        }

        // Find calon siswa by NISN
        $calonSiswa = CalonSiswa::where('nisn', $request->nisn)->first();

        if (!$calonSiswa) {
            return back()
                ->withInput()
                ->with('error', 'NISN tidak ditemukan dalam sistem.');
        }

        // Check if has phone number
        $phone = $calonSiswa->no_hp_siswa ?? $calonSiswa->calonOrtu?->no_hp_ayah ?? $calonSiswa->calonOrtu?->no_hp_ibu;
        
        if (!$phone) {
            return back()
                ->withInput()
                ->with('error', 'Nomor WhatsApp tidak ditemukan. Silakan hubungi admin untuk reset password.');
        }

        // Check if user exists
        $user = $calonSiswa->user;
        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'Akun tidak ditemukan. Silakan hubungi admin.');
        }

        // Generate new password
        $newPassword = $this->generateSecurePassword(8);

        // Update user password
        $user->password = Hash::make($newPassword);
        $user->plain_password = $newPassword; // Store plain for reference
        $user->save();

        // Get settings for message
        $settings = PpdbSettings::getActive();

        // Send WhatsApp notification
        $result = $waService->sendPasswordResetNotification([
            'phone' => $phone,
            'nama_siswa' => $calonSiswa->nama_lengkap,
            'nama_sekolah' => $settings->nama_sekolah ?? config('app.name'),
            'username' => $user->username,
            'password' => $newPassword,
            'url_login' => route('pendaftar.login'),
        ]);

        if ($result['success']) {
            // Mask phone number for display
            $maskedPhone = $this->maskPhoneNumber($phone);
            
            return back()->with('success', "Password baru telah dikirim ke WhatsApp ({$maskedPhone}). Silakan cek pesan Anda.");
        } else {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim pesan WhatsApp. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Mask phone number for display (e.g., 628123456789 -> 6281****6789)
     */
    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 8) {
            return $phone;
        }
        
        $start = substr($phone, 0, 4);
        $end = substr($phone, -4);
        $masked = str_repeat('*', $length - 8);
        
        return $start . $masked . $end;
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
}
