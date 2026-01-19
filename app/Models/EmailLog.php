<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'calon_siswa_id',
        'to_email',
        'to_name',
        'subject',
        'type',
        'status',
        'error_message',
        'message_preview',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Konstanta tipe email
    public const TYPE_NOMOR_TES = 'nomor_tes';
    public const TYPE_REVISI = 'revisi';
    public const TYPE_DITERIMA = 'diterima';
    public const TYPE_DITOLAK = 'ditolak';
    public const TYPE_REGISTRASI = 'registrasi';
    public const TYPE_GENERAL = 'general';

    public const TYPES = [
        self::TYPE_NOMOR_TES => 'Nomor Tes',
        self::TYPE_REVISI => 'Revisi Dokumen',
        self::TYPE_DITERIMA => 'Diterima',
        self::TYPE_DITOLAK => 'Ditolak',
        self::TYPE_REGISTRASI => 'Registrasi',
        self::TYPE_GENERAL => 'Umum',
    ];

    /**
     * Relasi ke calon siswa
     */
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class);
    }

    /**
     * Log email yang berhasil dikirim
     */
    public static function logSent(
        string $toEmail,
        string $subject,
        string $type = 'general',
        ?string $calonSiswaId = null,
        ?string $toName = null,
        ?string $messagePreview = null
    ): self {
        return self::create([
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'type' => $type,
            'status' => 'sent',
            'calon_siswa_id' => $calonSiswaId,
            'message_preview' => $messagePreview,
            'sent_at' => now(),
        ]);
    }

    /**
     * Log email yang gagal dikirim
     */
    public static function logFailed(
        string $toEmail,
        string $subject,
        string $type = 'general',
        string $errorMessage = '',
        ?string $calonSiswaId = null,
        ?string $toName = null
    ): self {
        return self::create([
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'type' => $type,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'calon_siswa_id' => $calonSiswaId,
        ]);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'sent' => '<span class="badge badge-success"><i class="fas fa-check"></i> Terkirim</span>',
            'failed' => '<span class="badge badge-danger"><i class="fas fa-times"></i> Gagal</span>',
            'pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            default => '<span class="badge badge-secondary">Unknown</span>',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
