<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalonDokumen extends Model
{
    use HasUuids;

    protected $table = 'calon_dokumens';

    protected $fillable = [
        'calon_siswa_id',
        'jenis_dokumen',
        'file_path',
        'file_size',
        'file_type',
        'status_verifikasi',
        'verifikator_id',
        'catatan_verifikasi',
        'tanggal_verifikasi',
        'alasan_tolak',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Verifikator::class, 'verifikator_id');
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

    public function scopeRejected($query)
    {
        return $query->where('status_verifikasi', 'rejected');
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }
}
