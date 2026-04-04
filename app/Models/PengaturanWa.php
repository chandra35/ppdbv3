<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanWa extends Model
{
    protected $table = 'pengaturan_wa';

    protected $fillable = [
        'provider',
        'api_key',
        'api_url',
        'sender_number',
        'is_active',
        'template_registrasi',
        'template_verifikasi',
        'template_diterima',
        'template_ditolak',
        'template_lupa_password',
        'settings',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getSettings(): ?self
    {
        return self::first();
    }

    public static function isActive(): bool
    {
        $settings = self::first();
        return $settings && $settings->is_active && $settings->api_key;
    }

    public function getApiUrlAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return match ($this->provider) {
            'fonnte' => 'https://api.fonnte.com/send',
            'wablas' => 'https://pati.wablas.com/api/send-message',
            'wabotapi' => 'https://api.wabotapi.com/send',
            'twilio' => 'https://api.twilio.com/2010-04-01',
            default => '',
        };
    }

    public static function getDefaultTemplates(): array
    {
        return [
            'template_registrasi' => "🎓 *PPDB {nama_sekolah}*\nTahun Pelajaran {tahun_pelajaran}\n\nHalo *{nama_siswa}*,\nAkun PPDB Anda telah berhasil dibuat.\n\n📋 *Detail Akun:*\n• Username: `{username}`\n• Password: `{password}`\n\n🔗 Link Login:\n{url_login}\n\n⚠️ Segera login dan lengkapi data pendaftaran Anda.\nJangan bagikan password kepada siapa pun.\n\nTerima kasih.\n_Tim PPDB {nama_sekolah}_",
            'template_verifikasi' => "🎓 *PPDB {nama_sekolah}*\n\nHalo *{nama_siswa}*,\n\nData pendaftaran Anda telah *DIVERIFIKASI*.\n\nNo. Registrasi: {nomor_registrasi}\nStatus: ✅ Terverifikasi\n\nSilakan tunggu pengumuman hasil seleksi.\n\nTerima kasih.\n_Tim PPDB {nama_sekolah}_",
            'template_diterima' => "🎓 *PPDB {nama_sekolah}*\n\n🎉 *SELAMAT!*\n\nHalo *{nama_siswa}*,\n\nAnda *DITERIMA* sebagai calon siswa baru di {nama_sekolah}.\n\nNo. Registrasi: {nomor_registrasi}\nJalur: {jalur_pendaftaran}\n\nSilakan lakukan daftar ulang sesuai jadwal yang ditentukan.\n\nTerima kasih.\n_Tim PPDB {nama_sekolah}_",
            'template_ditolak' => "🎓 *PPDB {nama_sekolah}*\n\nHalo *{nama_siswa}*,\n\nMohon maaf, Anda *TIDAK DITERIMA* pada seleksi PPDB {nama_sekolah}.\n\nNo. Registrasi: {nomor_registrasi}\nJalur: {jalur_pendaftaran}\n\nTetap semangat dan jangan menyerah.\n\nTerima kasih.\n_Tim PPDB {nama_sekolah}_",
            'template_lupa_password' => "🔐 *Reset Password PPDB*\n\n{nama_sekolah}\n\nHalo *{nama_siswa}*,\n\nAnda telah meminta reset password untuk akun PPDB Anda.\n\n📋 *Detail Akun:*\n• Username: `{username}`\n• Password Baru: `{password}`\n\n🔗 Link Login:\n{url_login}\n\n⚠️ Segera login dan ganti password Anda.\nJika Anda tidak meminta reset password, abaikan pesan ini.\n\nTerima kasih.\n_Tim PPDB {nama_sekolah}_",
        ];
    }
}
