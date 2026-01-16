<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BobotNilaiSeleksi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bobot_nilai_seleksi';

    protected $fillable = [
        'tahun_pelajaran_id',
        'komponen',
        'nama_komponen',
        'bobot',
        'nilai_min',
        'nilai_max',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'nilai_min' => 'decimal:2',
        'nilai_max' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Komponen constants
    const KOMPONEN_WAWANCARA = 'wawancara';
    const KOMPONEN_BACA_QURAN = 'baca_quran';
    const KOMPONEN_TULIS_QURAN = 'tulis_quran';
    const KOMPONEN_HAFALAN = 'hafalan';

    /**
     * Get tahun pelajaran
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Scope active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for tahun pelajaran
     */
    public function scopeForTahun($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    /**
     * Get icon for komponen
     */
    public function getIconAttribute(): string
    {
        return match($this->komponen) {
            self::KOMPONEN_WAWANCARA => 'fas fa-comments',
            self::KOMPONEN_BACA_QURAN => 'fas fa-book-open',
            self::KOMPONEN_TULIS_QURAN => 'fas fa-pen',
            self::KOMPONEN_HAFALAN => 'fas fa-quran',
            default => 'fas fa-check',
        };
    }

    /**
     * Get color for komponen
     */
    public function getColorAttribute(): string
    {
        return match($this->komponen) {
            self::KOMPONEN_WAWANCARA => 'primary',
            self::KOMPONEN_BACA_QURAN => 'success',
            self::KOMPONEN_TULIS_QURAN => 'info',
            self::KOMPONEN_HAFALAN => 'warning',
            default => 'secondary',
        };
    }
}
