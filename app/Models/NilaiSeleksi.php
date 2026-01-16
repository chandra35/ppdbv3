<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSeleksi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'nilai_seleksi';

    protected $fillable = [
        'sesi_ujian_id',
        'ruang_ujian_id',
        'calon_siswa_id',
        'penguji_id',
        'nilai_wawancara',
        'nilai_baca_quran',
        'nilai_tulis_quran',
        'nilai_hafalan',
        'jumlah_juz_hafalan',
        'total_nilai',
        'catatan_penguji',
        'status',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'nilai_wawancara' => 'decimal:2',
        'nilai_baca_quran' => 'decimal:2',
        'nilai_tulis_quran' => 'decimal:2',
        'nilai_hafalan' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REVISION = 'revision';

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
     * Get penguji
     */
    public function penguji(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penguji_id');
    }

    /**
     * Get verifier
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Calculate total nilai based on bobot
     */
    public function calculateTotalNilai(): float
    {
        $tahunPelajaranId = $this->sesiUjian->tahun_pelajaran_id ?? null;
        
        if (!$tahunPelajaranId) {
            return 0;
        }

        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('is_active', true)
            ->get();

        $totalNilai = 0;
        $totalBobot = 0;

        foreach ($bobotList as $bobot) {
            $nilai = match($bobot->komponen) {
                'wawancara' => $this->nilai_wawancara,
                'baca_quran' => $this->nilai_baca_quran,
                'tulis_quran' => $this->nilai_tulis_quran,
                'hafalan' => $this->nilai_hafalan,
                default => null,
            };

            if ($nilai !== null) {
                $totalNilai += ($nilai * $bobot->bobot / 100);
                $totalBobot += $bobot->bobot;
            }
        }

        // Normalize if not all components filled
        if ($totalBobot > 0 && $totalBobot < 100) {
            $totalNilai = ($totalNilai / $totalBobot) * 100;
        }

        return round($totalNilai, 2);
    }

    /**
     * Auto calculate and save total nilai
     */
    public function updateTotalNilai(): void
    {
        $this->total_nilai = $this->calculateTotalNilai();
        $this->save();
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => '<span class="badge badge-secondary">Draft</span>',
            self::STATUS_SUBMITTED => '<span class="badge badge-info">Submitted</span>',
            self::STATUS_VERIFIED => '<span class="badge badge-success">Terverifikasi</span>',
            self::STATUS_REVISION => '<span class="badge badge-warning">Revisi</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }

    /**
     * Check if nilai is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION]);
    }

    /**
     * Scope submitted
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Scope needs verification
     */
    public function scopeNeedsVerification($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
