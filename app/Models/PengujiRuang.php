<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengujiRuang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'penguji_ruang';

    protected $fillable = [
        'sesi_ujian_id',
        'ruang_ujian_id',
        'user_id',
        'is_ketua',
        'is_active',
    ];

    protected $casts = [
        'is_ketua' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get sesi ujian
     */
    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    /**
     * Get ruang ujian
     */
    public function ruangUjian(): BelongsTo
    {
        return $this->belongsTo(RuangUjian::class, 'ruang_ujian_id');
    }

    /**
     * Get user (penguji)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for user
     */
    public function penguji(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Scope active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ketua
     */
    public function scopeKetua($query)
    {
        return $query->where('is_ketua', true);
    }
}
