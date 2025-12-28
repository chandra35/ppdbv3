<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class CalonSiswa extends Model
{
    use HasUuids, SoftDeletes;

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
        'kodepos_siswa',
        
        // Transportasi (dari EMIS)
        'transportasi',
        'jarak_ke_sekolah',
        
        // Kontak
        'nomor_hp',
        'email',
        
        // Asal sekolah
        'npsn_asal_sekolah',
        'nsm_asal_sekolah',
        'nama_sekolah_asal',
        
        // Foto
        'foto_profile',
        
        // Completion flags
        'data_diri_completed',
        'data_ortu_completed',
        'data_dokumen_completed',
        'nilai_rapor_completed',
        
        // Pilihan Program & Finalisasi
        'pilihan_program',
        'is_finalisasi',
        'tanggal_finalisasi',
        'nomor_tes',
        'verification_hash',
        
        // Nilai & Ranking
        'nilai_cbt',
        'nilai_wawancara',
        'nilai_akhir',
        'ranking',
        'status_admisi',
        'catatan_admisi',
        
        // Relations
        'user_id',
        'tahun_pelajaran_id',
        'tanggal_registrasi',
        
        // Soft delete fields
        'deleted_by',
        'deleted_reason',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'tanggal_finalisasi' => 'datetime',
        'nisn_valid' => 'boolean',
        'data_diri_completed' => 'boolean',
        'data_ortu_completed' => 'boolean',
        'data_dokumen_completed' => 'boolean',
        'nilai_rapor_completed' => 'boolean',
        'is_finalisasi' => 'boolean',
        'jumlah_saudara' => 'integer',
        'anak_ke' => 'integer',
        'nilai_cbt' => 'decimal:2',
        'nilai_wawancara' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'ranking' => 'integer',
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

    public function nilaiRapor(): HasMany
    {
        return $this->hasMany(NilaiRapor::class, 'calon_siswa_id');
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

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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

    /**
     * Check apakah semua dokumen sudah valid
     */
    public function allDokumenValid(): bool
    {
        $totalDokumen = $this->dokumen()->count();
        
        // Jika belum ada dokumen, return false
        if ($totalDokumen === 0) {
            return false;
        }
        
        $validDokumen = $this->dokumen()->where('status_verifikasi', 'valid')->count();
        
        return $totalDokumen === $validDokumen;
    }

    /**
     * Auto-update status verifikasi berdasarkan kelengkapan dokumen
     */
    public function autoUpdateStatusVerifikasi(): void
    {
        if ($this->allDokumenValid()) {
            // Semua dokumen valid -> set verified
            $this->update([
                'status_verifikasi' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id() ?? $this->verified_by,
            ]);
        } else {
            // Ada dokumen yang belum valid -> set pending
            // Kecuali jika statusnya sudah approved/rejected, jangan ubah
            if (in_array($this->status_verifikasi, ['pending', 'verified'])) {
                $this->update([
                    'status_verifikasi' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                ]);
            }
        }
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
    public function getRataRataRaporAttribute(): ?float
    {
        if ($this->nilaiRapor->isEmpty()) {
            return null;
        }
        
        return $this->nilaiRapor->avg('rata_rata');
    }

    public function getNilaiRaporCompletedAttribute(): bool
    {
        // Cek apakah semua 5 semester sudah diisi
        return $this->nilaiRapor()->count() === 5;
    }

    public function getNilaiRaporProgressAttribute(): int
    {
        $total = $this->nilaiRapor()->count();
        return ($total / 5) * 100;
    }
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

    /**
     * Generate verification hash untuk QR code
     */
    public function generateVerificationHash(): string
    {
        return hash('sha256', $this->id . $this->nomor_registrasi . $this->created_at . config('app.key'));
    }

    /**
     * Generate or get verification hash
     */
    public function getOrGenerateHash(): string
    {
        if (!$this->verification_hash) {
            $hash = $this->generateVerificationHash();
            $this->update(['verification_hash' => $hash]);
            return $hash;
        }
        return $this->verification_hash;
    }

    // Boot method for cascade delete
    protected static function boot()
    {
        parent::boot();

        // Event saat soft delete
        static::deleting(function ($pendaftar) {
            // Jika ini adalah soft delete (bukan force delete)
            if (!$pendaftar->isForceDeleting()) {
                // Soft delete related data
                $pendaftar->ortu()->delete();
                $pendaftar->dokumen()->delete();
                $pendaftar->user()->delete();
            }
        });

        // Event saat force delete (hapus permanen)
        static::forceDeleting(function ($pendaftar) {
            // Hapus file dokumen dari storage
            $dokumenCollection = $pendaftar->dokumen()->withTrashed()->get();
            foreach ($dokumenCollection as $dokumen) {
                if ($dokumen->file_path && Storage::exists($dokumen->file_path)) {
                    Storage::delete($dokumen->file_path);
                }
                // Hapus histories
                $dokumen->histories()->forceDelete();
                // Force delete dokumen
                $dokumen->forceDelete();
            }

            // Hapus foto profile
            if ($pendaftar->foto_profile && Storage::exists($pendaftar->foto_profile)) {
                Storage::delete($pendaftar->foto_profile);
            }

            // Hapus related data permanen
            $pendaftar->ortu()->withTrashed()->forceDelete();
            $pendaftar->user()->withTrashed()->forceDelete();
        });

        // Event saat restore
        static::restoring(function ($pendaftar) {
            // Restore related data
            $pendaftar->ortu()->withTrashed()->restore();
            $pendaftar->dokumen()->withTrashed()->restore();
            $pendaftar->user()->withTrashed()->restore();
        });
    }
}
