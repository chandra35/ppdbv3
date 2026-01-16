<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuangUjian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ruang_ujian';

    protected $fillable = [
        'sesi_ujian_id',
        'nomor_ruang',
        'nama_ruang',
        'kapasitas',
        'jumlah_peserta',
    ];

    /**
     * Get sesi ujian
     */
    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    /**
     * Get peserta
     */
    public function peserta(): HasMany
    {
        return $this->hasMany(PesertaRuang::class, 'ruang_ujian_id')->orderBy('nomor_urut');
    }

    /**
     * Get penguji
     */
    public function penguji(): HasMany
    {
        return $this->hasMany(PengujiRuang::class, 'ruang_ujian_id');
    }

    /**
     * Get nilai seleksi
     */
    public function nilaiSeleksi(): HasMany
    {
        return $this->hasMany(NilaiSeleksi::class, 'ruang_ujian_id');
    }

    /**
     * Get ketua penguji
     */
    public function ketuaPenguji()
    {
        return $this->penguji()->where('is_ketua', true)->first();
    }

    /**
     * Get list penguji names
     */
    public function getPengujiNamesAttribute(): string
    {
        $names = $this->penguji()
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->pluck('user.name')
            ->filter();
        
        return $names->isEmpty() ? '-' : $names->join(', ');
    }

    /**
     * Get peserta count
     */
    public function getPesertaCountAttribute(): int
    {
        return $this->peserta()->count();
    }

    /**
     * Get progress penilaian
     */
    public function getProgressPenilaianAttribute(): array
    {
        return $this->getProgress();
    }

    /**
     * Get progress method (for direct method call)
     */
    public function getProgress(): array
    {
        $totalPeserta = $this->peserta()->count();
        $sudahDinilai = $this->nilaiSeleksi()
            ->whereIn('status', ['submitted', 'verified'])
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');
        
        $percentage = $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0;
        
        return [
            'total' => $totalPeserta,
            'dinilai' => $sudahDinilai,
            'percentage' => $percentage,
        ];
    }
}
