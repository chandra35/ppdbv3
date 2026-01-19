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
            
            // Get rendered body (store HTML for exact preview with real password)
            $renderedBody = $mailable->getRenderedBody();

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REGISTRASI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $renderedBody
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

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REVISI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $mailable->getRenderedBody()
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

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: $logType,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $mailable->getRenderedBody()
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

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_NOMOR_TES,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: $mailable->getRenderedBody()
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
