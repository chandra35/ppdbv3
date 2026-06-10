<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registrasi extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'registrasis';

    protected $fillable = [
        'calon_siswa_id',
        'tahun_pelajaran_id',
        'notes',
        'nama_excel',
        'jurusan_excel',
        'jurusan_awal',
        'jurusan_final',
        'pindah_jurusan',
        'match_status',
        'match_score',
        'catatan',
        'tanggal_registrasi',
        'created_by',
    ];

    protected $casts = [
        'tanggal_registrasi' => 'datetime',
        'match_score' => 'integer',
        'pindah_jurusan' => 'boolean',
    ];

    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeTahun($query, $tahunId)
    {
        return $query->where('tahun_pelajaran_id', $tahunId);
    }
}
