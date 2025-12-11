<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TahunPelajaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tahun_pelajarans';

    protected $fillable = [
        'nama',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Saat mengaktifkan, nonaktifkan yang lain
        static::saving(function ($model) {
            if ($model->is_active && $model->isDirty('is_active')) {
                static::where('id', '!=', $model->id ?? '')
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Scope: Aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get tahun pelajaran yang aktif
     */
    public static function getAktif()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get nama tahun pelajaran aktif
     */
    public static function getNamaAktif(): ?string
    {
        $aktif = static::getAktif();
        return $aktif ? $aktif->nama : null;
    }

    /**
     * Relasi ke jalur pendaftaran
     */
    public function jalurPendaftaran()
    {
        return $this->hasMany(JalurPendaftaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Generate tahun pelajaran otomatis berdasarkan bulan
     */
    public static function generateNama(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;
        
        // Tahun pelajaran dimulai Juli
        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        }
        return ($year - 1) . '/' . $year;
    }

    /**
     * Get options untuk dropdown
     */
    public static function getOptions(): array
    {
        $currentYear = (int) date('Y');
        $options = [];
        
        for ($i = -2; $i <= 3; $i++) {
            $year = $currentYear + $i;
            $nama = "{$year}/" . ($year + 1);
            $options[$nama] = $nama;
        }
        
        return $options;
    }

    /**
     * Cek apakah tahun pelajaran sedang berjalan
     */
    public function sedangBerjalan(): bool
    {
        $today = Carbon::today();
        
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            return $today->between($this->tanggal_mulai, $this->tanggal_selesai);
        }
        
        return $this->is_active;
    }

    /**
     * Aktifkan tahun pelajaran ini
     */
    public function aktifkan(): bool
    {
        // Nonaktifkan semua yang lain
        static::where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        $this->is_active = true;
        return $this->save();
    }
}
