<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jadwal_ujian';

    protected $fillable = [
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'tanggal_ujian',
        'jam_mulai',
        'jeda_sesi',
        'jumlah_ruang_cbt',
        'kapasitas_cbt',
        'durasi_cbt',
        'prefix_ruang_cbt',
        'jumlah_ruang_wawancara',
        'kapasitas_wawancara',
        'durasi_wawancara',
        'prefix_ruang_wawancara',
        'mode',
        'total_peserta',
        'total_sesi',
        'estimasi_selesai',
        'status',
        'generated_at',
        'generated_by',
        'locked_at',
        'locked_by',
        'catatan',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'estimasi_selesai' => 'datetime:H:i',
        'generated_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    // Relationships
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class);
    }

    public function gelombangPendaftaran(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class);
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function jadwalPeserta(): HasMany
    {
        return $this->hasMany(JadwalPeserta::class);
    }

    public function sesiUjian(): HasMany
    {
        return $this->hasMany(SesiUjian::class);
    }

    // Computed properties
    public function getTotalKapasitasCbtAttribute(): int
    {
        return $this->jumlah_ruang_cbt * $this->kapasitas_cbt;
    }

    public function getTotalKapasitasWawancaraAttribute(): int
    {
        return $this->jumlah_ruang_wawancara * $this->kapasitas_wawancara;
    }

    public function getKapasitasParalelAttribute(): int
    {
        return min($this->total_kapasitas_cbt, $this->total_kapasitas_wawancara);
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'locked';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'preview' => '<span class="badge badge-warning">Preview</span>',
            'locked' => '<span class="badge badge-success">Terkunci</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }

    // Scopes
    public function scopeForTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }
}
