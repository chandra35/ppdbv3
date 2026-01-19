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

        $settings = \App\Models\PpdbSettings::first();
        $subject = '✅ Pendaftaran PPDB Berhasil - ' . ($settings->nama_sekolah ?? 'MAN 1 Metro');

        try {
            Mail::to($email)->send(new RegistrasiNotification($calonSiswa, $username, $password));

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REGISTRASI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: "Username: {$username}"
            );

            Log::info("Registration email sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
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

        $settings = \App\Models\PpdbSettings::first();
        $subject = '⚠️ Permintaan Revisi Dokumen PPDB - ' . ($settings->nama_sekolah ?? 'MAN 1 Metro');
        $dokumenName = ucwords(str_replace('_', ' ', $dokumen->jenis_dokumen));

        try {
            Mail::to($email)->send(new RevisiDokumenNotification($calonSiswa, $dokumen, $catatan));

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_REVISI,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: "Dokumen: {$dokumenName}\nCatatan: {$catatan}"
            );

            Log::info("Revision email sent to {$email} for dokumen {$dokumen->id}");
            return true;

        } catch (\Exception $e) {
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

        $settings = \App\Models\PpdbSettings::first();
        $type = $hasil === 'diterima' ? EmailLog::TYPE_DITERIMA : EmailLog::TYPE_DITOLAK;
        $emoji = $hasil === 'diterima' ? '🎉' : '📋';
        $status = $hasil === 'diterima' ? 'SELAMAT! Anda Diterima' : 'Pengumuman Hasil Seleksi';
        $subject = "{$emoji} {$status} - " . ($settings->nama_sekolah ?? 'MAN 1 Metro');

        try {
            Mail::to($email)->send(new HasilSeleksiNotification($calonSiswa, $hasil, $keterangan));

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: $type,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: "Hasil: " . strtoupper($hasil) . ($keterangan ? "\nKeterangan: {$keterangan}" : "")
            );

            Log::info("Result email ({$hasil}) sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
            EmailLog::logFailed(
                toEmail: $email,
                subject: $subject,
                type: $type,
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

        $settings = \App\Models\PpdbSettings::first();
        $subject = '🎉 Nomor Tes PPDB - ' . ($settings->nama_sekolah ?? 'MAN 1 Metro');

        try {
            Mail::to($email)->send(new NomorTesNotification($calonSiswa, $nomorTes));

            EmailLog::logSent(
                toEmail: $email,
                subject: $subject,
                type: EmailLog::TYPE_NOMOR_TES,
                calonSiswaId: $calonSiswa->id,
                toName: $calonSiswa->nama_lengkap,
                messagePreview: "Nomor Tes: {$nomorTes}"
            );

            Log::info("Nomor tes email sent to {$email} for calon_siswa {$calonSiswa->id}");
            return true;

        } catch (\Exception $e) {
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
