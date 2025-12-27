<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Verifikator extends Model
{
    use HasUuids;

    protected $table = 'ppdb_verifikators';

    protected $fillable = [
        'user_id',  // ✅ Changed from gtk_id to user_id
        'ppdb_settings_id',
        'jenis_dokumen_aktif',
        'is_active',
    ];

    protected $casts = [
        'jenis_dokumen_aktif' => 'array',
        'is_active' => 'boolean',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');  // ✅ Relasi ke User
    }

    public function ppdbSettings(): BelongsTo
    {
        return $this->belongsTo(PpdbSettings::class, 'ppdb_settings_id');
    }

    public function calonDokumen(): HasMany
    {
        return $this->hasMany(CalonDokumen::class, 'verifikator_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
