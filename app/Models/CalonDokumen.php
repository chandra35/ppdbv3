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

    // Jenis dokumen yang tersedia
    public const JENIS_DOKUMEN = [
        'foto' => 'Pas Foto',
        'kk' => 'Kartu Keluarga',
        'akta_lahir' => 'Akta Kelahiran',
        'ktp_ortu' => 'KTP Orang Tua',
        'ijazah' => 'Ijazah/SKL',
        'skhun' => 'SKHUN',
        'raport' => 'Raport',
        'rapor_sem_1' => 'Rapor Semester 1',
        'rapor_sem_2' => 'Rapor Semester 2',
        'rapor_sem_3' => 'Rapor Semester 3',
        'rapor_sem_4' => 'Rapor Semester 4',
        'rapor_sem_5' => 'Rapor Semester 5',
        'sertifikat_prestasi' => 'Sertifikat Prestasi',
        'surat_keterangan' => 'Surat Keterangan Lainnya',
        'surat_sehat' => 'Surat Keterangan Sehat',
        'surat_kelakuan_baik' => 'Surat Kelakuan Baik',
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
