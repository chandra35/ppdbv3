<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrikulasiPeserta extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'calon_siswa_id',
        'tahun_pelajaran_id',
        'jalur_pendaftaran_id',
        'gelombang_pendaftaran_id',
        'kategori',
        'is_smart_q',
        'input_text',
        'match_score',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'is_smart_q' => 'boolean',
        'assigned_at' => 'datetime',
        'match_score' => 'integer',
    ];

    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
