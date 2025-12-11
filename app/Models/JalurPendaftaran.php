<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JalurPendaftaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jalur_pendaftaran';

    protected $fillable = [
        'nama',
        'kode',
        'tahun_pelajaran_id',
        'deskripsi',
        'persyaratan',
        'tanggal_buka',
        'tanggal_tutup',
        'kuota',
        'kuota_terisi',
        'warna',
        'icon',
        'prefix_nomor',
        'counter_nomor',
        'is_active',
        'status',
        'tampil_di_publik',
        'urutan',
    ];

    protected $casts = [
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'kuota' => 'integer',
        'kuota_terisi' => 'integer',
        'counter_nomor' => 'integer',
        'is_active' => 'boolean',
        'tampil_di_publik' => 'boolean',
        'urutan' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_FINISHED = 'finished';

    const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_OPEN => 'Dibuka',
        self::STATUS_CLOSED => 'Ditutup Sementara',
        self::STATUS_FINISHED => 'Selesai',
    ];

    const STATUS_COLORS = [
        self::STATUS_DRAFT => 'secondary',
        self::STATUS_OPEN => 'success',
        self::STATUS_CLOSED => 'warning',
        self::STATUS_FINISHED => 'dark',
    ];

    // Warna yang tersedia
    const WARNA_OPTIONS = [
        '#007bff' => 'Biru',
        '#28a745' => 'Hijau',
        '#ffc107' => 'Kuning',
        '#dc3545' => 'Merah',
        '#17a2b8' => 'Cyan',
        '#6c757d' => 'Abu-abu',
        '#343a40' => 'Hitam',
        '#6f42c1' => 'Ungu',
        '#fd7e14' => 'Orange',
    ];

    // Icon yang tersedia
    const ICON_OPTIONS = [
        'fas fa-graduation-cap' => 'Topi Wisuda',
        'fas fa-trophy' => 'Piala',
        'fas fa-medal' => 'Medali',
        'fas fa-star' => 'Bintang',
        'fas fa-award' => 'Penghargaan',
        'fas fa-user-graduate' => 'Siswa',
        'fas fa-book' => 'Buku',
        'fas fa-school' => 'Sekolah',
        'fas fa-heart' => 'Hati',
        'fas fa-hands-helping' => 'Tangan Membantu',
    ];

    /**
     * Relasi ke tahun pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke gelombang pendaftaran (opsional)
     */
    public function gelombang()
    {
        return $this->hasMany(GelombangPendaftaran::class, 'jalur_id');
    }

    /**
     * Relasi ke calon siswa
     */
    public function pendaftar()
    {
        return $this->hasMany(CalonSiswa::class, 'jalur_pendaftaran_id');
    }

    /**
     * Scope: Aktif (is_active = true)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Status aktif (sedang dibuka)
     */
    public function scopeStatusOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: Dalam periode pendaftaran
     */
    public function scopeDalamPeriode($query)
    {
        $today = Carbon::today();
        return $query->where(function($q) use ($today) {
            $q->whereNull('tanggal_buka')
              ->orWhere('tanggal_buka', '<=', $today);
        })->where(function($q) use ($today) {
            $q->whereNull('tanggal_tutup')
              ->orWhere('tanggal_tutup', '>=', $today);
        });
    }

    /**
     * Scope: Tampil di publik
     */
    public function scopePublic($query)
    {
        return $query->where('tampil_di_publik', true);
    }

    /**
     * Scope: Berdasarkan tahun pelajaran
     */
    public function scopeTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    /**
     * Scope: Order by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    /**
     * Get jalur yang sedang AKTIF untuk pendaftaran
     * HANYA 1 JALUR YANG BISA AKTIF
     */
    public static function getAktif()
    {
        return static::where('status', self::STATUS_OPEN)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Cek apakah dalam periode pendaftaran
     */
    public function dalamPeriodePendaftaran(): bool
    {
        $today = Carbon::today();
        
        // Jika tidak ada tanggal, anggap selalu dalam periode
        if (!$this->tanggal_buka && !$this->tanggal_tutup) {
            return true;
        }
        
        // Cek tanggal buka
        if ($this->tanggal_buka && $this->tanggal_buka > $today) {
            return false;
        }
        
        // Cek tanggal tutup
        if ($this->tanggal_tutup && $this->tanggal_tutup < $today) {
            return false;
        }
        
        return true;
    }

    /**
     * Cek apakah bisa diaktifkan
     * Tidak bisa jika ada jalur lain yang sedang aktif di tahun pelajaran yang sama
     */
    public function bisaDiaktifkan(): bool
    {
        // Cek apakah sudah ada jalur aktif lainnya di tahun pelajaran yang sama
        $jalurAktifLain = static::where('id', '!=', $this->id)
            ->where('tahun_pelajaran_id', $this->tahun_pelajaran_id)
            ->where('status', self::STATUS_OPEN)
            ->exists();
        
        return !$jalurAktifLain;
    }

    /**
     * Aktifkan jalur ini
     */
    public function aktifkan(): bool
    {
        if (!$this->bisaDiaktifkan()) {
            return false;
        }
        
        $this->status = self::STATUS_OPEN;
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Tutup jalur
     */
    public function tutup(): bool
    {
        $this->status = self::STATUS_CLOSED;
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Selesaikan jalur
     */
    public function selesaikan(): bool
    {
        $this->status = self::STATUS_FINISHED;
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Cek apakah bisa menerima pendaftar
     */
    public function bisaMenerimaPendaftar(): bool
    {
        return $this->is_active 
            && $this->status === self::STATUS_OPEN
            && $this->dalamPeriodePendaftaran()
            && $this->sisaKuota() > 0;
    }

    /**
     * Sisa kuota
     */
    public function sisaKuota(): int
    {
        return max(0, $this->kuota - $this->kuota_terisi);
    }

    /**
     * Persentase kuota
     */
    public function persentaseKuota(): float
    {
        if ($this->kuota <= 0) return 0;
        return round(($this->kuota_terisi / $this->kuota) * 100, 1);
    }

    /**
     * Sisa hari pendaftaran
     */
    public function sisaHari(): int
    {
        if (!$this->tanggal_ditutup) return 0;
        return max(0, Carbon::today()->diffInDays($this->tanggal_ditutup, false));
    }

    /**
     * Generate nomor registrasi
     */
    public function generateNomorRegistrasi(): string
    {
        $this->increment('counter_nomor');
        $prefix = $this->prefix_nomor ?: 'PPDB';
        $year = date('Y');
        $counter = str_pad($this->counter_nomor, 5, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$counter}";
    }

    /**
     * Get tahun pelajaran nama (accessor untuk backward compatibility)
     */
    public function getTahunAjaranAttribute(): ?string
    {
        return $this->tahunPelajaran?->nama;
    }
}
