<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalonDokumen extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'calon_dokumen';

    protected $fillable = [
        'calon_siswa_id',
        'jenis_dokumen',
        'nama_dokumen',
        'nama_file',
        'file_path',
        'remote_file_id',
        'remote_file_url',
        'file_size',
        'mime_type',
        'storage_disk',
        'is_required',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
        'revised_by',
        'revised_at',
        'cancelled_by',
        'cancelled_at',
        'verifikasi_note',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'revised_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_required' => 'boolean',
        'file_size' => 'integer',
    ];

    public const DOKUMEN_UTAMA = [
        'foto' => 'Pas Foto',
        'kk' => 'Kartu Keluarga',
        'akta_lahir' => 'Akta Kelahiran',
        'ktp_ortu' => 'KTP Orang Tua',
        'ijazah' => 'Ijazah/SKL',
        'skhun' => 'SKHUN',
        'raport' => 'Raport',
        'surat_pindah' => 'Surat Pindah',
        'rapor_sem_1' => 'Rapor Semester 1',
        'rapor_sem_2' => 'Rapor Semester 2',
        'rapor_sem_3' => 'Rapor Semester 3',
        'rapor_sem_4' => 'Rapor Semester 4',
        'rapor_sem_5' => 'Rapor Semester 5',
        'surat_keterangan' => 'Surat Keterangan Lainnya',
        'surat_sehat' => 'Surat Keterangan Sehat',
        'surat_kelakuan_baik' => 'Surat Kelakuan Baik',
        'kartu_pelajar' => 'Kartu Pelajar/NISN',
        'lainnya' => 'Dokumen Lainnya',
    ];

    // Jenis dokumen tambahan yang bisa diupload pendaftar / admin
    public const DOKUMEN_TAMBAHAN = [
        'sertifikat_prestasi' => 'Sertifikat Prestasi/Lomba',
        'piagam' => 'Piagam Penghargaan',
        'sertifikat_ksm' => 'Sertifikat KSM',
        'piagam_ksm' => 'Piagam KSM',
        'sertifikat_osn' => 'Sertifikat OSN',
        'piagam_osn' => 'Piagam OSN',
        'sertifikat_olimpiade' => 'Sertifikat Olimpiade',
        'piagam_olimpiade' => 'Piagam Olimpiade',
        'sertifikat_tahfidz' => 'Sertifikat Tahfidz',
        'piagam_tahfidz' => 'Piagam Tahfidz',
        'kip' => 'KIP (Kartu Indonesia Pintar)',
        'pip' => 'PIP (Program Indonesia Pintar)',
        'sktm' => 'SKTM (Surat Ket. Tidak Mampu)',
        'surat_domisili' => 'Surat Keterangan Domisili',
        'surat_rekomendasi' => 'Surat Rekomendasi',
        'dokumen_lainnya' => 'Dokumen Lainnya',
    ];

    // Seluruh jenis dokumen yang dikenal sistem
    public const JENIS_DOKUMEN = self::DOKUMEN_UTAMA + self::DOKUMEN_TAMBAHAN;

    public const DOKUMEN_TAMBAHAN_PRESTASI = [
        'sertifikat_prestasi' => 'Sertifikat Prestasi/Lomba',
        'piagam' => 'Piagam Penghargaan',
        'sertifikat_ksm' => 'Sertifikat KSM',
        'piagam_ksm' => 'Piagam KSM',
        'sertifikat_osn' => 'Sertifikat OSN',
        'piagam_osn' => 'Piagam OSN',
        'sertifikat_olimpiade' => 'Sertifikat Olimpiade',
        'piagam_olimpiade' => 'Piagam Olimpiade',
        'sertifikat_tahfidz' => 'Sertifikat Tahfidz',
        'piagam_tahfidz' => 'Piagam Tahfidz',
    ];

    // Jenis dokumen rapor semester
    public const RAPOR_SEMESTER = [
        1 => 'rapor_sem_1',
        2 => 'rapor_sem_2',
        3 => 'rapor_sem_3',
        4 => 'rapor_sem_4',
        5 => 'rapor_sem_5',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function histories()
    {
        return $this->hasMany(DokumenVerifikasiHistory::class, 'dokumen_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    public function scopeValid($query)
    {
        return $query->where('status_verifikasi', 'valid');
    }

    public function scopeInvalid($query)
    {
        return $query->where('status_verifikasi', 'invalid');
    }

    public function scopeRevision($query)
    {
        return $query->where('status_verifikasi', 'revision');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    // Accessors
    public function getNamaDokumenLengkapAttribute(): string
    {
        return self::JENIS_DOKUMEN[$this->jenis_dokumen] ?? $this->nama_dokumen ?? $this->jenis_dokumen;
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->storage_disk === 'gdrive' && $this->remote_file_id) {
            return $this->getDownloadUrlAttribute();
        }

        if ($this->file_path) {
            return asset('storage/' . ltrim($this->file_path, '/'));
        }

        return null;
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->storage_disk === 'gdrive' && $this->remote_file_id) {
            if ($this->isImage()) {
                return 'https://lh3.googleusercontent.com/d/' . $this->remote_file_id . '=w1600';
            }

            return 'https://drive.google.com/file/d/' . $this->remote_file_id . '/preview';
        }

        return $this->getFileUrlAttribute();
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->storage_disk === 'gdrive' && $this->remote_file_id) {
            return 'https://drive.usercontent.google.com/download?id=' . $this->remote_file_id . '&export=view';
        }

        if ($this->file_path) {
            return asset('storage/' . ltrim($this->file_path, '/'));
        }

        return null;
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public static function getAdminUploadOptions(): array
    {
        return self::DOKUMEN_UTAMA + self::DOKUMEN_TAMBAHAN;
    }

    public static function getDokumenTambahanGroups(): array
    {
        return [
            'Prestasi & Akademik' => [
                'sertifikat_prestasi' => self::DOKUMEN_TAMBAHAN['sertifikat_prestasi'],
                'piagam' => self::DOKUMEN_TAMBAHAN['piagam'],
                'sertifikat_ksm' => self::DOKUMEN_TAMBAHAN['sertifikat_ksm'],
                'piagam_ksm' => self::DOKUMEN_TAMBAHAN['piagam_ksm'],
                'sertifikat_osn' => self::DOKUMEN_TAMBAHAN['sertifikat_osn'],
                'piagam_osn' => self::DOKUMEN_TAMBAHAN['piagam_osn'],
                'sertifikat_olimpiade' => self::DOKUMEN_TAMBAHAN['sertifikat_olimpiade'],
                'piagam_olimpiade' => self::DOKUMEN_TAMBAHAN['piagam_olimpiade'],
            ],
            'Keagamaan' => [
                'sertifikat_tahfidz' => self::DOKUMEN_TAMBAHAN['sertifikat_tahfidz'],
                'piagam_tahfidz' => self::DOKUMEN_TAMBAHAN['piagam_tahfidz'],
            ],
            'Bantuan & Keterangan' => [
                'kip' => self::DOKUMEN_TAMBAHAN['kip'],
                'pip' => self::DOKUMEN_TAMBAHAN['pip'],
                'sktm' => self::DOKUMEN_TAMBAHAN['sktm'],
                'surat_domisili' => self::DOKUMEN_TAMBAHAN['surat_domisili'],
                'surat_rekomendasi' => self::DOKUMEN_TAMBAHAN['surat_rekomendasi'],
            ],
            'Lainnya' => [
                'dokumen_lainnya' => self::DOKUMEN_TAMBAHAN['dokumen_lainnya'],
            ],
        ];
    }

    public static function getAdminUploadOptionGroups(): array
    {
        return [
            'Dokumen Utama' => self::DOKUMEN_UTAMA,
            'Dokumen Tambahan' => self::getDokumenTambahanGroups(),
        ];
    }

    public static function getPrestasiDocumentTypes(): array
    {
        return self::DOKUMEN_TAMBAHAN_PRESTASI;
    }

    public static function getDokumenTambahanCategory(string $jenisDokumen): ?string
    {
        foreach (self::getDokumenTambahanGroups() as $groupLabel => $groupOptions) {
            if (array_key_exists($jenisDokumen, $groupOptions)) {
                return $groupLabel;
            }
        }

        return null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status_verifikasi) {
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'valid' => '<span class="badge badge-success">Valid</span>',
            'invalid' => '<span class="badge badge-danger">Invalid</span>',
            'revision' => '<span class="badge badge-info">Revisi</span>',
            default => '<span class="badge badge-secondary">Unknown</span>',
        };
    }
}
