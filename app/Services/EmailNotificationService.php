<?php

namespace App\Services;

use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use App\Models\EmailLog;
use App\Models\PengaturanEmail;
use App\Mail\RegistrasiNotification;
use App\Mail\RevisiDokumenNotification;
use App\Mail\HasilSeleksiNotification;
use App\Mail\NomorTesNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Cek apakah email notifikasi enabled untuk tipe tertentu
     */
    protected static function isEnabled(string $type): bool
    {
        return PengaturanEmail::isEnabled($type);
    }

    /**
     * Mask password untuk keamanan di log
     */
    protected static function maskPassword(string $password): string
    {
        if (strlen($password) <= 4) {
            return str_repeat('*', strlen($password));
        }
        return substr($password, 0, 2) . str_repeat('*', strlen($password) - 4) . substr($password, -2);
    }

    /**
     * Convert HTML ke plain text untuk preview
     */
    protected static function htmlToPlainText(string $html): string
    {
        // Remove tags but keep content
        $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $html));
        // Clean up whitespace
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Kirim email notifikasi registrasi berhasil
     */
    public static function sendRegistrasi(CalonSiswa $calonSiswa, string $username, string $password): bool
    {
        // Cek apakah notifikasi registrasi diaktifkan
        if (!self::isEnabled('registrasi')) {
            Log::info("Registration email skipped: notification disabled for calon_siswa {$calonSiswa->id}");
            return false;
        }

        $email = $calonSiswa->user?->email ?? $calonSiswa->email ?? null;
        
        if (!$email) {
            Log::warning("Cannot send registration email: No email for calon_siswa {$calonSiswa->id}");
            return false;
        }

        try {
            $mailable = new RegistrasiNotification($calonSiswa, $username, $password);
            Mail::to($email)->send($mailable);

            // Get subject from mailable
            $subject = $mailable->envelope()->subject;
            
            // Get rendered body and mask password for security
            $renderedBody = $mailable->getRenderedBody();
            $maskedBody = str_replace($password, self::maskPassword($password), $renderedBody);
            $messagePreview = self::htmlToPlainText($maskedBody);

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REGISTRASI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $messagePreview
            );

            Log::info("Registration email sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
            $emailSettings = PengaturanEmail::getSettings();
            $settings = \App\Models\PpdbSettings::first();
            $subject = $emailSettings?->subject_registrasi 
                ?? PengaturanEmail::getDefaultTemplates()['subject_registrasi'];
            $subject = str_replace('{nama_sekolah}', $settings->nama_sekolah ?? 'MAN 1 Metro', $subject);

            EmailLog::logFailed(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REGISTRASI,
                errorMessage: $e->getMessage(),
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap
            );

            Log::error("Failed to send registration email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notifikasi revisi dokumen
     */
    public static function sendRevisiDokumen(CalonSiswa $calonSiswa, CalonDokumen $dokumen, string $catatan): bool
    {
        // Cek apakah notifikasi revisi diaktifkan
        if (!self::isEnabled('revisi')) {
            Log::info("Revision email skipped: notification disabled for calon_siswa {$calonSiswa->id}");
            return false;
        }

        $email = $calonSiswa->user?->email ?? $calonSiswa->email ?? null;
        
        if (!$email) {
            Log::warning("Cannot send revision email: No email for calon_siswa {$calonSiswa->id}");
            return false;
        }

        try {
            $mailable = new RevisiDokumenNotification($calonSiswa, $dokumen, $catatan);
            Mail::to($email)->send($mailable);

            // Get subject from mailable
            $subject = $mailable->envelope()->subject;
            
            // Get rendered body for log
            $messagePreview = self::htmlToPlainText($mailable->getRenderedBody());

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REVISI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $messagePreview
            );

            Log::info("Revision email sent to {$email} for dokumen {$dokumen->id}");
            return true;

        } catch (\Exception $e) {
            $emailSettings = PengaturanEmail::getSettings();
            $settings = \App\Models\PpdbSettings::first();
            $subject = $emailSettings?->subject_revisi 
                ?? PengaturanEmail::getDefaultTemplates()['subject_revisi'];
            $subject = str_replace('{nama_sekolah}', $settings->nama_sekolah ?? 'MAN 1 Metro', $subject);

            EmailLog::logFailed(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REVISI,
                errorMessage: $e->getMessage(),
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap
            );

            Log::error("Failed to send revision email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notifikasi hasil seleksi (diterima/ditolak)
     */
    public static function sendHasilSeleksi(CalonSiswa $calonSiswa, string $hasil, ?string $keterangan = null): bool
    {
        // Cek apakah notifikasi hasil seleksi diaktifkan
        $type = $hasil === 'diterima' ? 'diterima' : 'ditolak';
        if (!self::isEnabled($type)) {
            Log::info("Result ({$hasil}) email skipped: notification disabled for calon_siswa {$calonSiswa->id}");
            return false;
        }

        $email = $calonSiswa->user?->email ?? $calonSiswa->email ?? null;
        
        if (!$email) {
            Log::warning("Cannot send result email: No email for calon_siswa {$calonSiswa->id}");
            return false;
        }

        $logType = $hasil === 'diterima' ? EmailLog::TYPE_DITERIMA : EmailLog::TYPE_DITOLAK;

        try {
            $mailable = new HasilSeleksiNotification($calonSiswa, $hasil, $keterangan);
            Mail::to($email)->send($mailable);

            // Get subject from mailable
            $subject = $mailable->envelope()->subject;
            
            // Get rendered body for log
            $messagePreview = self::htmlToPlainText($mailable->getRenderedBody());

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: $logType,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $messagePreview
            );

            Log::info("Result email ({$hasil}) sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
            $emailSettings = PengaturanEmail::getSettings();
            $settings = \App\Models\PpdbSettings::first();
            $subjectKey = $hasil === 'diterima' ? 'subject_diterima' : 'subject_ditolak';
            $subject = $emailSettings?->{$subjectKey} 
                ?? PengaturanEmail::getDefaultTemplates()[$subjectKey];
            $subject = str_replace('{nama_sekolah}', $settings->nama_sekolah ?? 'MAN 1 Metro', $subject);

            EmailLog::logFailed(
                toEmail: $email,
                subject: $subject,
                type: $logType,
                errorMessage: $e->getMessage(),
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap
            );

            Log::error("Failed to send result email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notifikasi nomor tes
     */
    public static function sendNomorTes(CalonSiswa $calonSiswa, string $nomorTes): bool
    {
        // Cek apakah notifikasi nomor tes diaktifkan
        if (!self::isEnabled('nomor_tes')) {
            Log::info("Nomor tes email skipped: notification disabled for calon_siswa {$calonSiswa->id}");
            return false;
        }

        $email = $calonSiswa->user?->email ?? $calonSiswa->email ?? null;
        
        if (!$email) {
            Log::warning("Cannot send nomor tes email: No email for calon_siswa {$calonSiswa->id}");
            return false;
        }

        try {
            $mailable = new NomorTesNotification($calonSiswa, $nomorTes);
            Mail::to($email)->send($mailable);

            // Get subject from mailable
            $subject = $mailable->envelope()->subject;
            
            // Get rendered body for log
            $messagePreview = self::htmlToPlainText($mailable->getRenderedBody());

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_NOMOR_TES,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $messagePreview
            );

            Log::info("Nomor tes email sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
            $emailSettings = PengaturanEmail::getSettings();
            $settings = \App\Models\PpdbSettings::first();
            $subject = $emailSettings?->subject_nomor_tes 
                ?? PengaturanEmail::getDefaultTemplates()['subject_nomor_tes'];
            $subject = str_replace('{nama_sekolah}', $settings->nama_sekolah ?? 'MAN 1 Metro', $subject);

            EmailLog::logFailed(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_NOMOR_TES,
                errorMessage: $e->getMessage(),
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap
            );

            Log::error("Failed to send nomor tes email: " . $e->getMessage());
            return false;
        }
    }
}
