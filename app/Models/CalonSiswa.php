<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalonSiswa extends Model
{
    use HasUuids;

    protected $table = 'calon_siswas';

    protected $fillable = [
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'nisn',
        'nisn_valid',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'no_hp_pribadi',
        'no_hp_ortu',
        'email',
        'alamat_rumah',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'asal_sekolah',
        'status_verifikasi',
        'status_admisi',
        'nilai_tes',
        'nilai_wawancara',
        'rata_rata_nilai',
        'ranking',
        'nomor_pendaftaran_sementara',
        'nomor_pendaftaran_final',
        'nomor_registrasi',
        'tanggal_registrasi',
        'bukti_registrasi_path',
        'tahun_pelajaran_id',
        'user_id',
        'kelas_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'nisn_valid' => 'boolean',
        'tanggal_registrasi' => 'datetime',
    ];

    // Relations
    public function dokumen(): HasMany
    {
        return $this->hasMany(CalonDokumen::class, 'calon_siswa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function gelombangPendaftaran(): BelongsTo
    {
        return $this->belongsTo(GelombangPendaftaran::class, 'gelombang_pendaftaran_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_verifikasi', 'approved');
    }

    public function scopeDiterima($query)
    {
        return $query->where('status_admisi', 'diterima');
    }

    public function scopeByTahun($query, $tahunId)
    {
        return $query->where('tahun_pelajaran_id', $tahunId);
    }

    public function scopeByJalur($query, $jalurId)
    {
        return $query->where('jalur_pendaftaran_id', $jalurId);
    }

    public function scopeByGelombang($query, $gelombangId)
    {
        return $query->where('gelombang_pendaftaran_id', $gelombangId);
    }
}
