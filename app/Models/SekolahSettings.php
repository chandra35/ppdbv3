<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class SekolahSettings extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sekolah_settings';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'nsm',
        'jenjang',
        'email',
        'telepon',
        'website',
        'alamat_jalan',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
        'kode_pos',
        'latitude',
        'longitude',
        'logo',
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Jenjang sekolah yang tersedia
     */
    public const JENJANG_LIST = [
        'MI' => 'Madrasah Ibtidaiyah (MI)',
        'MTs' => 'Madrasah Tsanawiyah (MTs)',
        'MA' => 'Madrasah Aliyah (MA)',
        'SD' => 'Sekolah Dasar (SD)',
        'SMP' => 'Sekolah Menengah Pertama (SMP)',
        'SMA' => 'Sekolah Menengah Atas (SMA)',
        'SMK' => 'Sekolah Menengah Kejuruan (SMK)',
    ];

    /**
     * Mapping jenjang sekolah ke kelas minimum yang diizinkan untuk PPDB
     * Jika sekolah jenjang MA, maka pendaftar harus kelas 9 (MTs/SMP)
     */
    public const KELAS_MINIMUM_PPDB = [
        'MI' => ['kelas' => 6, 'jenjang_asal' => ['TK', 'RA', 'PAUD']],
        'MTs' => ['kelas' => 6, 'jenjang_asal' => ['SD', 'MI']],
        'MA' => ['kelas' => 9, 'jenjang_asal' => ['SMP', 'MTs']],
        'SD' => ['kelas' => 6, 'jenjang_asal' => ['TK', 'RA', 'PAUD']],
        'SMP' => ['kelas' => 6, 'jenjang_asal' => ['SD', 'MI']],
        'SMA' => ['kelas' => 9, 'jenjang_asal' => ['SMP', 'MTs']],
        'SMK' => ['kelas' => 9, 'jenjang_asal' => ['SMP', 'MTs']],
    ];

    /**
     * Relasi ke Provinsi
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Relasi ke Kota/Kabupaten
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }

    /**
     * Relasi ke Kecamatan
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Relasi ke Kelurahan/Desa
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    /**
     * Get alamat lengkap
     */
    public function getAlamatLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->alamat_jalan,
            $this->village?->name,
            $this->district?->name,
            $this->city?->name,
            $this->province?->name,
            $this->kode_pos,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get kelas minimum untuk pendaftaran PPDB
     */
    public function getKelasMinimumPpdbAttribute(): ?int
    {
        return self::KELAS_MINIMUM_PPDB[$this->jenjang]['kelas'] ?? null;
    }

    /**
     * Get jenjang asal yang diizinkan untuk PPDB
     */
    public function getJenjangAsalPpdbAttribute(): array
    {
        return self::KELAS_MINIMUM_PPDB[$this->jenjang]['jenjang_asal'] ?? [];
    }

    /**
     * Cek apakah pendaftar memenuhi syarat kelas
     * 
     * @param int $kelasPendaftar Kelas saat ini pendaftar (dari NISN check)
     * @param string $jenjangAsal Jenjang sekolah asal pendaftar
     * @return bool
     */
    public function isEligibleForPpdb(int $kelasPendaftar, string $jenjangAsal): bool
    {
        $requirement = self::KELAS_MINIMUM_PPDB[$this->jenjang] ?? null;
        
        if (!$requirement) {
            return false;
        }

        // Cek kelas harus sama dengan kelas minimum
        if ($kelasPendaftar != $requirement['kelas']) {
            return false;
        }

        // Cek jenjang asal harus sesuai
        $jenjangAsalUpper = strtoupper($jenjangAsal);
        foreach ($requirement['jenjang_asal'] as $allowedJenjang) {
            if (stripos($jenjangAsalUpper, strtoupper($allowedJenjang)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get atau create singleton settings
     */
    public static function getSettings(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'nama_sekolah' => 'Nama Sekolah',
                'jenjang' => 'MA',
            ]);
        }

        return $settings;
    }
}
