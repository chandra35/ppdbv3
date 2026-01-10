<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JadwalPpdb extends Model
{
    use HasUuids;

    protected $table = 'jadwal_ppdb';

    protected $fillable = [
        'nama_kegiatan',
        'tanggal_mulai',
        'waktu_mulai',
        'tanggal_selesai',
        'waktu_selesai',
        'keterangan',
        'warna',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Get formatted date range
     */
    public function getTanggalRangeAttribute(): string
    {
        $mulai = $this->tanggal_mulai->format('d M Y');
        
        if ($this->tanggal_selesai && $this->tanggal_selesai->ne($this->tanggal_mulai)) {
            return $mulai . ' - ' . $this->tanggal_selesai->format('d M Y');
        }
        
        return $mulai;
    }

    /**
     * Check if jadwal is ongoing
     */
    public function getIsOngoingAttribute(): bool
    {
        $today = now()->startOfDay();
        $mulai = $this->tanggal_mulai->startOfDay();
        $selesai = $this->tanggal_selesai ? $this->tanggal_selesai->endOfDay() : $mulai->endOfDay();
        
        return $today->between($mulai, $selesai);
    }

    /**
     * Check if jadwal is upcoming
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->tanggal_mulai->isFuture();
    }

    /**
     * Check if jadwal is past
     */
    public function getIsPastAttribute(): bool
    {
        $selesai = $this->tanggal_selesai ?? $this->tanggal_mulai;
        return $selesai->isPast();
    }
}
