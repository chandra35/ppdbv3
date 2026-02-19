<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\EmailLog;
use App\Models\PengaturanEmail;
use App\Models\PpdbSettings;
use App\Mail\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForm()
    {
        $settings = PpdbSettings::getActive();
        $emailActive = PengaturanEmail::isActive();
        
        return view('ppdb.forgot-password', compact('settings', 'emailActive'));
    }

    /**
     * Process forgot password - send reset link via email
     */
    public function sendReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // Find user by email
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            // Security: don't reveal if email exists
            return back()->with('success', 'Jika email terdaftar dalam sistem, kami telah mengirimkan link reset password. Silakan cek inbox atau folder spam Anda.');
        }

        // Check if user is a pendaftar (has calon_siswa)
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();
        if (!$calonSiswa) {
            return back()->with('success', 'Jika email terdaftar dalam sistem, kami telah mengirimkan link reset password. Silakan cek inbox atau folder spam Anda.');
        }

        // Rate limiting: check if token was generated recently (within 2 minutes)
        $recentToken = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>', Carbon::now()->subMinutes(2))
            ->first();

        if ($recentToken) {
            return back()
                ->withInput()
                ->with('error', 'Link reset password sudah dikirim. Silakan tunggu 2 menit sebelum meminta ulang.');
        }

        // Generate token
        $token = Str::random(64);

        // Delete old tokens
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Build reset link
        $resetLink = route('pendaftar.reset-password', ['token' => $token, 'email' => $user->email]);

        try {
            $mailable = new PasswordResetNotification($calonSiswa, $resetLink);
            Mail::to($user->email)->send($mailable);

            EmailLog::logSent(
                toEmail: $user->email,
                subject: $mailable->envelope()->subject,
                type: EmailLog::TYPE_GENERAL,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $mailable->getRenderedBody()
            );

            Log::info("Password reset email sent to {$user->email} for calon_siswa {$calonSiswa->id}");

            // Mask email for display
            $maskedEmail = $this->maskEmail($user->email);
            
            return back()->with('success', "Link reset password telah dikirim ke {$maskedEmail}. Silakan cek inbox atau folder spam Anda. Link berlaku selama 60 menit.");

        } catch (\Exception $e) {
            EmailLog::logFailed(
                toEmail: $user->email,
                subject: 'Reset Password PPDB',
                type: EmailLog::TYPE_GENERAL,
                errorMessage: $e->getMessage(),
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap
            );

            Log::error("Failed to send password reset email: " . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim email. Silakan coba lagi nanti atau hubungi panitia PPDB.');
        }
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, $token = null)
    {
        $settings = PpdbSettings::getActive();
        
        return view('ppdb.reset-password', [
            'token' => $token,
            'email' => $request->email,
            'settings' => $settings,
        ]);
    }

    /**
     * Process reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Check token
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah expired. Silakan request ulang.']);
        }

        // Check if token expired (60 minutes)
        $createdAt = Carbon::parse($tokenRecord->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token sudah expired (lebih dari 60 menit). Silakan request ulang.']);
        }

        // Verify token hash
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return back()->withErrors(['email' => 'Token tidak valid.']);
        }

        // Find user
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->readable_password = $request->password;
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Log::info("Password reset completed for user {$user->id} ({$user->email})");

        return redirect()->route('pendaftar.login')
            ->with('success', 'Password berhasil diubah! Silakan login menggunakan password baru Anda.');
    }

    /**
     * Mask email for display (e.g., user@example.com -> us***@example.com)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', min(strlen($name) - 2, 5));
        }

        return $masked . '@' . $domain;
    }
}
