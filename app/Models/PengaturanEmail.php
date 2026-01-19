<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanEmail extends Model
{
    protected $table = 'pengaturan_email';

    protected $fillable = [
        // Enable/Disable
        'enable_registrasi',
        'enable_revisi',
        'enable_nomor_tes',
        'enable_diterima',
        'enable_ditolak',
        
        // Subject
        'subject_registrasi',
        'subject_revisi',
        'subject_nomor_tes',
        'subject_diterima',
        'subject_ditolak',
        
        // Template
        'template_registrasi',
        'template_revisi',
        'template_nomor_tes',
        'template_diterima',
        'template_ditolak',
        
        // Global settings
        'is_active',
        'from_name',
        'from_email',
        'reply_to',
        'footer_text',
        
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enable_registrasi' => 'boolean',
        'enable_revisi' => 'boolean',
        'enable_nomor_tes' => 'boolean',
        'enable_diterima' => 'boolean',
        'enable_ditolak' => 'boolean',
    ];

    // Relations
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Static helper to get settings
    public static function getSettings(): ?self
    {
        return self::first();
    }

    // Check if Email notification is active globally
    public static function isActive(): bool
    {
        $settings = self::first();
        return $settings ? $settings->is_active : true; // Default aktif jika belum ada setting
    }

    // Check if specific type is enabled
    public static function isEnabled(string $type): bool
    {
        $settings = self::first();
        if (!$settings) {
            return true; // Default enabled jika belum ada setting
        }
        
        if (!$settings->is_active) {
            return false; // Global disabled
        }

        return match ($type) {
            'registrasi' => $settings->enable_registrasi,
            'revisi' => $settings->enable_revisi,
            'nomor_tes' => $settings->enable_nomor_tes,
            'diterima' => $settings->enable_diterima,
            'ditolak' => $settings->enable_ditolak,
            default => true,
        };
    }

    // Get subject for type
    public static function getSubject(string $type): ?string
    {
        $settings = self::first();
        if (!$settings) {
            return null;
        }

        return match ($type) {
            'registrasi' => $settings->subject_registrasi,
            'revisi' => $settings->subject_revisi,
            'nomor_tes' => $settings->subject_nomor_tes,
            'diterima' => $settings->subject_diterima,
            'ditolak' => $settings->subject_ditolak,
            default => null,
        };
    }

    // Get template for type
    public static function getTemplate(string $type): ?string
    {
        $settings = self::first();
        if (!$settings) {
            return null;
        }

        return match ($type) {
            'registrasi' => $settings->template_registrasi,
            'revisi' => $settings->template_revisi,
            'nomor_tes' => $settings->template_nomor_tes,
            'diterima' => $settings->template_diterima,
            'ditolak' => $settings->template_ditolak,
            default => null,
        };
    }

    // Default templates
    public static function getDefaultTemplates(): array
    {
        $namaSekolah = config('app.name', 'MAN 1 Metro');
        
        return [
            'subject_registrasi' => "✅ Pendaftaran PPDB Berhasil - {$namaSekolah}",
            'subject_revisi' => "⚠️ Permintaan Revisi Dokumen PPDB - {$namaSekolah}",
            'subject_nomor_tes' => "🎉 Nomor Tes PPDB Anda - {$namaSekolah}",
            'subject_diterima' => "🎉 SELAMAT! Anda Diterima - {$namaSekolah}",
            'subject_ditolak' => "📋 Pengumuman Hasil Seleksi - {$namaSekolah}",
            
            'template_registrasi' => '<p>Halo <strong>{nama_siswa}</strong>,</p>
<p>Selamat! Pendaftaran akun PPDB Anda berhasil.</p>
<p><strong>Detail Akun:</strong></p>
<ul>
<li>Username: <code>{username}</code></li>
<li>Password: <code>{password}</code></li>
</ul>
<p>Silakan login dan lengkapi data pendaftaran Anda.</p>
<p><strong>⚠️ Penting:</strong> Jangan bagikan password kepada siapapun.</p>',
            
            'template_revisi' => '<p>Halo <strong>{nama_siswa}</strong>,</p>
<p>Dokumen <strong>{nama_dokumen}</strong> Anda perlu direvisi.</p>
<p><strong>Catatan dari Tim Verifikasi:</strong></p>
<blockquote>{catatan}</blockquote>
<p>Silakan login dan upload ulang dokumen yang diperlukan.</p>',
            
            'template_nomor_tes' => '<p>Halo <strong>{nama_siswa}</strong>,</p>
<p>Selamat! Data pendaftaran Anda telah difinalisasi.</p>
<p><strong>Nomor Tes Anda:</strong></p>
<h2 style="text-align:center;color:#007bff;">{nomor_tes}</h2>
<p>Harap simpan nomor tes ini dan bawa saat ujian.</p>',
            
            'template_diterima' => '<p>Halo <strong>{nama_siswa}</strong>,</p>
<p>🎉 <strong>SELAMAT!</strong></p>
<p>Anda <strong>DITERIMA</strong> sebagai calon siswa baru di {nama_sekolah}.</p>
<p>Silakan lakukan daftar ulang sesuai jadwal yang ditentukan.</p>',
            
            'template_ditolak' => '<p>Halo <strong>{nama_siswa}</strong>,</p>
<p>Mohon maaf, Anda <strong>tidak diterima</strong> pada seleksi PPDB {nama_sekolah}.</p>
<p>Tetap semangat dan jangan menyerah!</p>
<p>{catatan}</p>',
        ];
    }

    // Available placeholders per type
    public static function getPlaceholders(string $type): array
    {
        $common = ['{nama_siswa}', '{nama_sekolah}', '{tahun_pelajaran}', '{nomor_registrasi}'];
        
        return match ($type) {
            'registrasi' => array_merge($common, ['{username}', '{password}', '{url_login}']),
            'revisi' => array_merge($common, ['{nama_dokumen}', '{catatan}']),
            'nomor_tes' => array_merge($common, ['{nomor_tes}', '{jalur_pendaftaran}']),
            'diterima' => array_merge($common, ['{jalur_pendaftaran}', '{catatan}']),
            'ditolak' => array_merge($common, ['{jalur_pendaftaran}', '{catatan}']),
            default => $common,
        };
    }
}
