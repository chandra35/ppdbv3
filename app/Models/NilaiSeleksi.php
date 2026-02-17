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
        'nilai_tajwid',
        'nilai_makhroj',
        'nilai_kelancaran',
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
        'nilai_tajwid' => 'decimal:2',
        'nilai_makhroj' => 'decimal:2',
        'nilai_kelancaran' => 'decimal:2',
        'nilai_baca_quran' => 'decimal:2',
        'nilai_tulis_quran' => 'decimal:2',
        'nilai_hafalan' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Status constants (tanpa verifikasi - langsung submitted = final)
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';

    /**
     * Hitung rata-rata nilai Baca Al-Qur'an dari sub-komponen (Tajwid, Makhroj, Kelancaran)
     */
    public function calculateNilaiBacaQuran(): ?float
    {
        $sub = array_filter([
            $this->nilai_tajwid,
            $this->nilai_makhroj,
            $this->nilai_kelancaran,
        ], fn($v) => $v !== null);

        if (empty($sub)) return null;
        return round(array_sum($sub) / count($sub), 2);
    }

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
            // Skip wawancara/minat - tidak masuk total, hanya sebagai sorting tiebreaker
            if ($bobot->komponen === 'wawancara') {
                continue;
            }

            $nilai = match($bobot->komponen) {
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
        // Auto-calculate nilai_baca_quran dari sub-komponen
        $nilaiBaca = $this->calculateNilaiBacaQuran();
        if ($nilaiBaca !== null) {
            $this->nilai_baca_quran = $nilaiBaca;
        }
        
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
            self::STATUS_SUBMITTED => '<span class="badge badge-success">Submitted</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }

    /**
     * Check if nilai is editable
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Scope submitted
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope final (submitted = final, no verification needed)
     */
    public function scopeFinal($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
