<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPeserta extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jadwal_peserta';

    protected $fillable = [
        'jadwal_ujian_id',
        'calon_siswa_id',
        'sesi_cbt_id',
        'ruang_cbt_id',
        'nomor_urut_cbt',
        'sesi_wawancara_id',
        'ruang_wawancara_id',
        'nomor_urut_wawancara',
        'grup',
        'nomor_gelombang',
    ];

    // Relationships
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class);
    }

    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class);
    }

    public function sesiCbt(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_cbt_id');
    }

    public function ruangCbt(): BelongsTo
    {
        return $this->belongsTo(RuangUjian::class, 'ruang_cbt_id');
    }

    public function sesiWawancara(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_wawancara_id');
    }

    public function ruangWawancara(): BelongsTo
    {
        return $this->belongsTo(RuangUjian::class, 'ruang_wawancara_id');
    }

    // Computed
    public function getJadwalCbtAttribute(): ?string
    {
        if (!$this->sesiCbt) return null;
        
        $waktu = $this->sesiCbt->waktu_mulai?->format('H:i') . ' - ' . $this->sesiCbt->waktu_selesai?->format('H:i');
        $ruang = $this->ruangCbt?->nama_ruang ?? '-';
        
        return "{$waktu} | {$ruang}";
    }

    public function getJadwalWawancaraAttribute(): ?string
    {
        if (!$this->sesiWawancara) return null;
        
        $waktu = $this->sesiWawancara->waktu_mulai?->format('H:i') . ' - ' . $this->sesiWawancara->waktu_selesai?->format('H:i');
        $ruang = $this->ruangWawancara?->nama_ruang ?? '-';
        
        return "{$waktu} | {$ruang}";
    }

    public function getGrupLabelAttribute(): string
    {
        return $this->grup === 'A' ? 'CBT → Wawancara' : 'Wawancara → CBT';
    }

    public function getRuangCbtNomorAttribute(): ?int
    {
        return $this->ruangCbt?->nomor_ruang;
    }

    public function getRuangWawancaraNomorAttribute(): ?int
    {
        return $this->ruangWawancara?->nomor_ruang;
    }
}
