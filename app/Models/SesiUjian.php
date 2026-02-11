<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiUjian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sesi_ujian';

    protected $fillable = [
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'jadwal_ujian_id',
        'nama',
        'jenis_ujian',
        'nomor_sesi',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'durasi',
        'peserta_per_ruang',
        'prefix_ruang',
        'urutan_peserta',
        'status',
        'catatan',
        'created_by',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
        'locked_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_LOCKED = 'locked';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get tahun pelajaran
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Get jalur pendaftaran
     */
    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    /**
     * Get gelombang pendaftaran
     */
    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'gelombang_pendaftaran_id');
    }

    /**
     * Get jadwal ujian
     */
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Get ruangan
     */
    public function ruangan(): HasMany
    {
        return $this->hasMany(RuangUjian::class, 'sesi_ujian_id')->orderBy('nomor_ruang');
    }

    /**
     * Alias for ruangan
     */
    public function ruangUjian(): HasMany
    {
        return $this->hasMany(RuangUjian::class, 'sesi_ujian_id')->orderBy('nomor_ruang');
    }

    /**
     * Get peserta ruang
     */
    public function pesertaRuang(): HasMany
    {
        return $this->hasMany(PesertaRuang::class, 'sesi_ujian_id');
    }

    /**
     * Get penguji ruang
     */
    public function pengujiRuang(): HasMany
    {
        return $this->hasMany(PengujiRuang::class, 'sesi_ujian_id');
    }

    /**
     * Get nilai seleksi
     */
    public function nilaiSeleksi(): HasMany
    {
        return $this->hasMany(NilaiSeleksi::class, 'sesi_ujian_id');
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get locker
     */
    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Check if sesi is editable
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if sesi is locked
     */
    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_LOCKED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED]);
    }

    /**
     * Get total peserta
     */
    public function getTotalPesertaAttribute(): int
    {
        return $this->pesertaRuang()->count();
    }

    /**
     * Get total ruangan
     */
    public function getTotalRuanganAttribute(): int
    {
        return $this->ruangan()->count();
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => '<span class="badge badge-secondary">Draft</span>',
            self::STATUS_LOCKED => '<span class="badge badge-primary">Terkunci</span>',
            self::STATUS_IN_PROGRESS => '<span class="badge badge-warning">Berlangsung</span>',
            self::STATUS_COMPLETED => '<span class="badge badge-success">Selesai</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }

    /**
     * Get waktu display
     */
    public function getWaktuDisplayAttribute(): string
    {
        return $this->waktu_mulai->format('H:i') . ' - ' . $this->waktu_selesai->format('H:i');
    }

    /**
     * Scope for active tahun pelajaran
     */
    public function scopeForTahunAktif($query)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if ($tahunAktif) {
            return $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }
        return $query;
    }
}
