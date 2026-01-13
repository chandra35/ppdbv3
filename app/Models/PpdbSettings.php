<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbSettings extends Model
{
    use HasUuids;

    protected $table = 'ppdb_settings';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kuota_penerimaan',
        'tanggal_dibuka',
        'tanggal_ditutup',
        'status_pendaftaran',
        'validasi_nisn_aktif',
        'wajib_lokasi_registrasi',
        'cegah_pendaftar_ganda',
        'dokumen_aktif',
        'izinkan_dokumen_tambahan',
        'nomor_registrasi_prefix',
        'nomor_registrasi_counter',
        'nomor_tes_prefix',
        'nomor_tes_format',
        'nomor_tes_digit',
        'nomor_tes_counter',
    ];

    protected $casts = [
        'tanggal_dibuka' => 'date',
        'tanggal_ditutup' => 'date',
        'status_pendaftaran' => 'boolean',
        'validasi_nisn_aktif' => 'boolean',
        'wajib_lokasi_registrasi' => 'boolean',
        'cegah_pendaftar_ganda' => 'boolean',
        'dokumen_aktif' => 'array',
        'izinkan_dokumen_tambahan' => 'boolean',
        'nomor_tes_counter' => 'array',
    ];

    protected $attributes = [
        'status_pendaftaran' => true,
        'validasi_nisn_aktif' => true,
        'wajib_lokasi_registrasi' => false,
        'cegah_pendaftar_ganda' => true,
        'kuota_penerimaan' => 200,
        'nomor_registrasi_prefix' => 'PPDB',
        'nomor_registrasi_counter' => 0,
        'nomor_tes_prefix' => 'NTS',
        'nomor_tes_format' => '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}',
        'nomor_tes_digit' => 4,
    ];

    // Relations
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function verifikators(): HasMany
    {
        return $this->hasMany(Verifikator::class, 'ppdb_settings_id');
    }

    // Helper untuk mendapatkan tahun pelajaran string
    public function getTahunPelajaranStringAttribute(): string
    {
        return $this->tahunPelajaran?->nama ?? date('Y') . '/' . (date('Y') + 1);
    }

    // Generate nomor registrasi
    public function generateNomorRegistrasi(): string
    {
        $this->increment('nomor_registrasi_counter');
        $tahun = now()->year;
        $counter = str_pad($this->nomor_registrasi_counter, 5, '0', STR_PAD_LEFT);
        return "{$this->nomor_registrasi_prefix}-{$tahun}-{$counter}";
    }
}
