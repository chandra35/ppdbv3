<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenVerifikasiHistory extends Model
{
    protected $table = 'dokumen_verifikasi_histories';

    protected $fillable = [
        'dokumen_id',
        'user_id',
        'action',
        'status_from',
        'status_to',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(CalonDokumen::class, 'dokumen_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByDokumen($query, $dokumenId)
    {
        return $query->where('dokumen_id', $dokumenId);
    }

    // Helper methods
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            'revisi' => 'Minta Revisi',
            'cancel' => 'Dibatalkan',
            default => ucfirst($this->action),
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'approve' => '<span class="badge badge-success">Disetujui</span>',
            'reject' => '<span class="badge badge-danger">Ditolak</span>',
            'revisi' => '<span class="badge badge-warning">Minta Revisi</span>',
            'cancel' => '<span class="badge badge-secondary">Dibatalkan</span>',
            default => '<span class="badge badge-info">' . ucfirst($this->action) . '</span>',
        };
    }
}
