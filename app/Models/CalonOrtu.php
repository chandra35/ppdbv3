<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class CalonOrtu extends Model
{
    use HasUuids;

    protected $table = 'calon_ortus';

    protected $fillable = [
        'calon_siswa_id',
        'no_kk',
        
        // Data Ayah
        'status_ayah',
        'nik_ayah',
        'nama_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pendidikan_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_hp_ayah',
        
        // Data Ibu
        'status_ibu',
        'nik_ibu',
        'nama_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pendidikan_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_hp_ibu',
        
        // Data Wali (jika tinggal dengan wali)
        'tinggal_dengan_wali',
        'nama_wali',
        'hubungan_wali',
        'nik_wali',
        'tempat_lahir_wali',
        'tanggal_lahir_wali',
        'pendidikan_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
        'no_hp_wali',
        
        // Alamat Orang Tua (Laravolt FK)
        'alamat_ortu',
        'rt_ortu',
        'rw_ortu',
        'provinsi_id_ortu',
        'kabupaten_id_ortu',
        'kecamatan_id_ortu',
        'kelurahan_id_ortu',
        'kode_pos_ortu',
    ];

    protected $casts = [
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'tanggal_lahir_wali' => 'date',
        'tinggal_dengan_wali' => 'boolean',
    ];

    // Enum values
    public const STATUS_ORTU = ['masih_hidup', 'meninggal'];
    
    public const PENDIDIKAN = [
        'tidak_sekolah' => 'Tidak Sekolah',
        'sd' => 'SD/Sederajat',
        'smp' => 'SMP/Sederajat',
        'sma' => 'SMA/Sederajat',
        'd1' => 'D1',
        'd2' => 'D2',
        'd3' => 'D3',
        'd4' => 'D4/S1',
        's1' => 'S1',
        's2' => 'S2',
        's3' => 'S3',
    ];

    public const PEKERJAAN = [
        'tidak_bekerja' => 'Tidak Bekerja',
        'pns' => 'PNS',
        'tni_polri' => 'TNI/Polri',
        'swasta' => 'Karyawan Swasta',
        'wiraswasta' => 'Wiraswasta',
        'petani' => 'Petani',
        'nelayan' => 'Nelayan',
        'buruh' => 'Buruh',
        'guru' => 'Guru/Dosen',
        'dokter' => 'Dokter',
        'pedagang' => 'Pedagang',
        'ibu_rumah_tangga' => 'Ibu Rumah Tangga',
        'pensiunan' => 'Pensiunan',
        'lainnya' => 'Lainnya',
    ];

    public const PENGHASILAN = [
        'tidak_ada' => 'Tidak ada',
        'dibawah_1jt' => '< Rp 1.000.000',
        '1jt_2jt' => 'Rp 1.000.000 - Rp 2.000.000',
        '2jt_3jt' => 'Rp 2.000.000 - Rp 3.000.000',
        '3jt_5jt' => 'Rp 3.000.000 - Rp 5.000.000',
        '5jt_10jt' => 'Rp 5.000.000 - Rp 10.000.000',
        'diatas_10jt' => '> Rp 10.000.000',
    ];

    public const HUBUNGAN_WALI = [
        'kakek' => 'Kakek',
        'nenek' => 'Nenek',
        'paman' => 'Paman',
        'bibi' => 'Bibi',
        'kakak' => 'Kakak',
        'saudara_lainnya' => 'Saudara Lainnya',
        'bukan_keluarga' => 'Bukan Keluarga',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    // Laravolt Address Relations - Orang Tua
    public function provinsiOrtu(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provinsi_id_ortu', 'code');
    }

    public function kabupatenOrtu(): BelongsTo
    {
        return $this->belongsTo(City::class, 'kabupaten_id_ortu', 'code');
    }

    public function kecamatanOrtu(): BelongsTo
    {
        return $this->belongsTo(District::class, 'kecamatan_id_ortu', 'code');
    }

    public function kelurahanOrtu(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'kelurahan_id_ortu', 'code');
    }

    // Accessors
    public function getAlamatLengkapOrtuAttribute(): string
    {
        $parts = array_filter([
            $this->alamat_ortu,
            $this->rt_ortu ? 'RT ' . $this->rt_ortu : null,
            $this->rw_ortu ? 'RW ' . $this->rw_ortu : null,
            $this->kelurahanOrtu?->name,
            $this->kecamatanOrtu?->name,
            $this->kabupatenOrtu?->name,
            $this->provinsiOrtu?->name,
            $this->kode_pos_ortu,
        ]);
        return implode(', ', $parts);
    }

    public function getStatusAyahLabelAttribute(): string
    {
        return $this->status_ayah === 'masih_hidup' ? 'Masih Hidup' : 'Meninggal';
    }

    public function getStatusIbuLabelAttribute(): string
    {
        return $this->status_ibu === 'masih_hidup' ? 'Masih Hidup' : 'Meninggal';
    }

    public function getPendidikanAyahLabelAttribute(): ?string
    {
        return self::PENDIDIKAN[$this->pendidikan_ayah] ?? $this->pendidikan_ayah;
    }

    public function getPendidikanIbuLabelAttribute(): ?string
    {
        return self::PENDIDIKAN[$this->pendidikan_ibu] ?? $this->pendidikan_ibu;
    }

    public function getPekerjaanAyahLabelAttribute(): ?string
    {
        return self::PEKERJAAN[$this->pekerjaan_ayah] ?? $this->pekerjaan_ayah;
    }

    public function getPekerjaanIbuLabelAttribute(): ?string
    {
        return self::PEKERJAAN[$this->pekerjaan_ibu] ?? $this->pekerjaan_ibu;
    }

    public function getPenghasilanAyahLabelAttribute(): ?string
    {
        return self::PENGHASILAN[$this->penghasilan_ayah] ?? $this->penghasilan_ayah;
    }

    public function getPenghasilanIbuLabelAttribute(): ?string
    {
        return self::PENGHASILAN[$this->penghasilan_ibu] ?? $this->penghasilan_ibu;
    }

    public function getHubunganWaliLabelAttribute(): ?string
    {
        return self::HUBUNGAN_WALI[$this->hubungan_wali] ?? $this->hubungan_wali;
    }

    // Helper methods
    public function isAyahMeninggal(): bool
    {
        return $this->status_ayah === 'meninggal';
    }

    public function isIbuMeninggal(): bool
    {
        return $this->status_ibu === 'meninggal';
    }

    public function isTinggalDenganWali(): bool
    {
        return $this->tinggal_dengan_wali === true;
    }
}
