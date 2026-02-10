<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaRuang extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'peserta_ruang';

    // Status constants
    const STATUS_WAITING = 'waiting';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'sesi_ujian_id',
        'ruang_ujian_id',
        'calon_siswa_id',
        'nomor_urut',
        'status',
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
     * Get calon siswa
     */
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    /**
     * Check if peserta sudah dinilai
     */
    public function getSudahDinilaiAttribute(): bool
    {
        return NilaiSeleksi::where('sesi_ujian_id', $this->sesi_ujian_id)
            ->where('calon_siswa_id', $this->calon_siswa_id)
            ->whereIn('status', ['submitted', 'verified'])
            ->exists();
    }

    /**
     * Get nilai seleksi peserta
     */
    public function nilaiSeleksi()
    {
        return NilaiSeleksi::where('sesi_ujian_id', $this->sesi_ujian_id)
            ->where('calon_siswa_id', $this->calon_siswa_id)
            ->first();
    }
}
