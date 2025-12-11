<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class CalonSiswa extends Model
{
    use HasUuids;

    protected $table = 'calon_siswas';

    protected $fillable = [
        // PPDB fields
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'nomor_registrasi',
        'status_verifikasi',
        'status_admisi',
        'catatan_verifikasi',
        'tanggal_verifikasi',
        'verified_by',
        
        // Data diri siswa (sesuai SIMANSAV3)
        'nisn',
        'nisn_valid',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'jumlah_saudara',
        'anak_ke',
        'hobi',
        'cita_cita',
        
        // Alamat siswa (Laravolt FK)
        'alamat_siswa',
        'rt_siswa',
        'rw_siswa',
        'provinsi_id_siswa',
        'kabupaten_id_siswa',
        'kecamatan_id_siswa',
        'kelurahan_id_siswa',
        'kode_pos_siswa',
        
        // Kontak
        'no_hp',
        'email',
        
        // Asal sekolah
        'npsn_asal',
        'sekolah_asal',
        'alamat_sekolah_asal',
        
        // Foto
        'foto',
        
        // Completion flags
        'data_diri_completed',
        'data_ortu_completed',
        'data_dokumen_completed',
        
        // Relations
        'user_id',
        'tahun_pelajaran_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'nisn_valid' => 'boolean',
        'data_diri_completed' => 'boolean',
        'data_ortu_completed' => 'boolean',
        'data_dokumen_completed' => 'boolean',
        'jumlah_saudara' => 'integer',
        'anak_ke' => 'integer',
    ];

    // Relations
    public function ortu(): HasOne
    {
        return $this->hasOne(CalonOrtu::class, 'calon_siswa_id');
    }

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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Laravolt Address Relations - Siswa
    public function provinsiSiswa(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provinsi_id_siswa', 'code');
    }

    public function kabupatenSiswa(): BelongsTo
    {
        return $this->belongsTo(City::class, 'kabupaten_id_siswa', 'code');
    }

    public function kecamatanSiswa(): BelongsTo
    {
        return $this->belongsTo(District::class, 'kecamatan_id_siswa', 'code');
    }

    public function kelurahanSiswa(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'kelurahan_id_siswa', 'code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status_verifikasi', 'verified');
    }

    public function scopeDiterima($query)
    {
        return $query->where('status_admisi', 'diterima');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status_admisi', 'ditolak');
    }

    public function scopeCadangan($query)
    {
        return $query->where('status_admisi', 'cadangan');
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

    // Accessors
    public function getJenisKelaminLengkapAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getAlamatLengkapSiswaAttribute(): string
    {
        $parts = array_filter([
            $this->alamat_siswa,
            $this->rt_siswa ? 'RT ' . $this->rt_siswa : null,
            $this->rw_siswa ? 'RW ' . $this->rw_siswa : null,
            $this->kelurahanSiswa?->name,
            $this->kecamatanSiswa?->name,
            $this->kabupatenSiswa?->name,
            $this->provinsiSiswa?->name,
            $this->kode_pos_siswa,
        ]);
        return implode(', ', $parts);
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->data_diri_completed && $this->data_ortu_completed && $this->data_dokumen_completed;
    }

    // Helper methods
    public function generateNomorRegistrasi(): string
    {
        $tahun = date('Y');
        $jalur = $this->jalurPendaftaran?->kode ?? 'XX';
        $gelombang = $this->gelombangPendaftaran?->kode ?? '0';
        $sequence = self::whereYear('created_at', $tahun)->count() + 1;
        
        return sprintf('%s/%s/%s/%04d', $tahun, $jalur, $gelombang, $sequence);
    }
}
