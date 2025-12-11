<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GelombangPendaftaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'gelombang_pendaftaran';

    protected $fillable = [
        'jalur_id',
        'nama',
        'deskripsi',
        'tanggal_buka',
        'tanggal_tutup',
        'kuota',
        'kuota_terisi',
        'biaya_pendaftaran',
        'prefix_nomor',
        'counter_nomor',
        'status',
        'is_active',
        'tampil_nama_gelombang',
        'urutan',
    ];

    protected $casts = [
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'kuota' => 'integer',
        'kuota_terisi' => 'integer',
        'biaya_pendaftaran' => 'decimal:2',
        'counter_nomor' => 'integer',
        'is_active' => 'boolean',
        'tampil_nama_gelombang' => 'boolean',
        'urutan' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_FINISHED = 'finished';

    const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_UPCOMING => 'Akan Datang',
        self::STATUS_OPEN => 'Dibuka',
        self::STATUS_CLOSED => 'Ditutup',
        self::STATUS_FINISHED => 'Selesai',
    ];

    const STATUS_COLORS = [
        self::STATUS_DRAFT => 'secondary',
        self::STATUS_UPCOMING => 'info',
        self::STATUS_OPEN => 'success',
        self::STATUS_CLOSED => 'warning',
        self::STATUS_FINISHED => 'dark',
    ];

    /**
     * Relasi ke jalur pendaftaran
     */
    public function jalur()
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_id');
    }

    /**
     * Relasi ke calon siswa
     */
    public function pendaftar()
    {
        return $this->hasMany(CalonSiswa::class, 'gelombang_id');
    }

    /**
     * Scope: Aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sedang dibuka
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: Dalam periode
     */
    public function scopeDalamPeriode($query)
    {
        $today = Carbon::today();
        return $query->where('tanggal_buka', '<=', $today)
                     ->where('tanggal_tutup', '>=', $today);
    }

    /**
     * Scope: Ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('tanggal_buka');
    }

    /**
     * Get gelombang yang sedang dibuka untuk pendaftaran
     */
    public static function getYangDibuka()
    {
        return static::with('jalur')
            ->active()
            ->open()
            ->dalamPeriode()
            ->whereHas('jalur', function ($q) {
                $q->where('is_active', true);
            })
            ->first();
    }

    /**
     * Cek apakah dalam periode
     */
    public function dalamPeriodePendaftaran(): bool
    {
        $today = Carbon::today();
        return $this->tanggal_buka <= $today && $this->tanggal_tutup >= $today;
    }

    /**
     * Get kuota efektif (dari gelombang atau jalur)
     */
    public function kuotaEfektif(): int
    {
        return $this->kuota ?? ($this->jalur ? $this->jalur->kuota : 0);
    }

    /**
     * Sisa kuota
     */
    public function sisaKuota(): int
    {
        $kuota = $this->kuotaEfektif();
        return max(0, $kuota - $this->kuota_terisi);
    }

    /**
     * Persentase kuota
     */
    public function persentaseKuota(): float
    {
        $kuota = $this->kuotaEfektif();
        if ($kuota <= 0) return 0;
        return round(($this->kuota_terisi / $kuota) * 100, 1);
    }

    /**
     * Cek apakah bisa menerima pendaftar
     */
    public function bisaMenerimaPendaftar(): bool
    {
        return $this->is_active 
            && $this->status === self::STATUS_OPEN
            && $this->dalamPeriodePendaftaran()
            && $this->sisaKuota() > 0
            && $this->jalur 
            && $this->jalur->is_active;
    }

    /**
     * Sisa hari
     */
    public function getSisaHariAttribute(): int
    {
        if ($this->status !== self::STATUS_OPEN) return 0;
        return max(0, Carbon::today()->diffInDays($this->tanggal_tutup, false));
    }

    /**
     * Get label status
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get warna status
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Generate nomor registrasi
     */
    public function generateNomorRegistrasi(): string
    {
        $this->increment('counter_nomor');
        $this->increment('kuota_terisi');
        
        // Update juga kuota_terisi jalur
        if ($this->jalur) {
            $this->jalur->increment('kuota_terisi');
        }
        
        $counter = str_pad($this->counter_nomor, 4, '0', STR_PAD_LEFT);
        $tahun = substr(str_replace('/', '', $this->jalur->tahunPelajaran?->nama ?? date('Y')), 0, 4);
        $kodeJalur = $this->jalur ? strtoupper(substr($this->jalur->kode, 0, 3)) : 'REG';
        
        return "{$this->prefix_nomor}-{$kodeJalur}-{$tahun}-{$counter}";
    }

    /**
     * Buka pendaftaran
     */
    public function bukaPendaftaran(): bool
    {
        // Nonaktifkan gelombang lain dalam jalur yang sama
        static::where('jalur_id', $this->jalur_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        $this->status = self::STATUS_OPEN;
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Tutup pendaftaran
     */
    public function tutupPendaftaran(): bool
    {
        $this->status = self::STATUS_CLOSED;
        return $this->save();
    }

    /**
     * Selesaikan gelombang
     */
    public function selesaikan(): bool
    {
        $this->status = self::STATUS_FINISHED;
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Update status otomatis berdasarkan tanggal
     */
    public function updateStatusOtomatis(): void
    {
        if ($this->status === self::STATUS_DRAFT || $this->status === self::STATUS_FINISHED) {
            return;
        }

        $today = Carbon::today();

        if ($today < $this->tanggal_buka) {
            $this->status = self::STATUS_UPCOMING;
        } elseif ($today >= $this->tanggal_buka && $today <= $this->tanggal_tutup) {
            if ($this->is_active && $this->status !== self::STATUS_FINISHED) {
                $this->status = self::STATUS_OPEN;
            }
        } elseif ($today > $this->tanggal_tutup) {
            if ($this->status !== self::STATUS_FINISHED) {
                $this->status = self::STATUS_CLOSED;
            }
        }

        $this->save();
    }
}
