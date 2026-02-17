<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiCbt extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'nilai_cbt';

    protected $fillable = [
        'calon_siswa_id',
        'tahun_pelajaran_id',
        'nilai_mtk',
        'nilai_ipa',
        'nilai_ips',
        'nilai_bahasa_inggris',
        'total_nilai',
        'rata_rata',
        'uploaded_by',
    ];

    protected $casts = [
        'nilai_mtk' => 'decimal:2',
        'nilai_ipa' => 'decimal:2',
        'nilai_ips' => 'decimal:2',
        'nilai_bahasa_inggris' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'rata_rata' => 'decimal:2',
    ];

    /**
     * Komponen CBT - label dan field mapping
     */
    public static function komponenList(): array
    {
        return [
            'nilai_mtk' => 'Matematika',
            'nilai_ipa' => 'IPA Terpadu',
            'nilai_ips' => 'IPS Terpadu',
            'nilai_bahasa_inggris' => 'Bahasa Inggris',
        ];
    }

    /**
     * Calculate total & rata-rata
     */
    public function calculateTotal(): void
    {
        $komponen = self::komponenList();
        $values = [];

        foreach (array_keys($komponen) as $field) {
            if ($this->$field !== null) {
                $values[] = (float) $this->$field;
            }
        }

        if (!empty($values)) {
            $this->total_nilai = round(array_sum($values), 2);
            $this->rata_rata = round(array_sum($values) / count($values), 2);
        } else {
            $this->total_nilai = null;
            $this->rata_rata = null;
        }
    }

    /**
     * Get calon siswa
     */
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    /**
     * Get tahun pelajaran
     */
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Get uploader
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
