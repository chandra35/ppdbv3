<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class CalonOrtu extends Model
{
    use HasUuids, SoftDeletes;

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
        'hp_ayah',
        
        // Data Ibu
        'status_ibu',
        'nik_ibu',
        'nama_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pendidikan_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'hp_ibu',
        
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
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'kodepos',
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
        'pensiunan' => 'Pensiunan',
        'pns' => 'PNS',
        'tni_polri' => 'TNI/Polisi',
        'guru_dosen' => 'Guru/Dosen',
        'pegawai_swasta' => 'Pegawai Swasta',
        'wiraswasta' => 'Wiraswasta',
        'pengacara' => 'Pengacara/Jaksa/Hakim/Notaris',
        'seniman' => 'Seniman/Pelukis/Artis/Sejenis',
        'dokter' => 'Dokter/Bidan/Perawat',
        'pilot' => 'Pilot/Pramugara',
        'pedagang' => 'Pedagang',
        'petani' => 'Petani/Peternak',
        'nelayan' => 'Nelayan',
        'buruh' => 'Buruh (Tani/Pabrik/Bangunan)',
        'sopir' => 'Sopir/Masinis/Kondektur',
        'politikus' => 'Politikus',
        'lainnya' => 'Lainnya',
    ];

    public const PENGHASILAN = [
        'dibawah_800rb' => 'Dibawah 800.000',
        '800rb_1_2jt' => '800.001 - 1.200.000',
        '1_2jt_1_8jt' => '1.200.001 - 1.800.000',
        '1_8jt_2_5jt' => '1.800.001 - 2.500.000',
        '2_5jt_3_5jt' => '2.500.001 - 3.500.000',
        '3_5jt_4_8jt' => '3.500.001 - 4.800.000',
        '4_8jt_6_5jt' => '4.800.001 - 6.500.000',
        '6_5jt_10jt' => '6.500.001 - 10.000.000',
        '10jt_20jt' => '10.000.001 - 20.000.000',
        'diatas_20jt' => 'Diatas 20.000.001',
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
        return $this->belongsTo(Province::class, 'provinsi_id', 'code');
    }

    public function kabupatenOrtu(): BelongsTo
    {
        return $this->belongsTo(City::class, 'kabupaten_id', 'code');
    }

    public function kecamatanOrtu(): BelongsTo
    {
        return $this->belongsTo(District::class, 'kecamatan_id', 'code');
    }

    public function kelurahanOrtu(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'kelurahan_id', 'code');
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
            $this->kodepos,
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
