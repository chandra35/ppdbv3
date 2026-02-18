<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelulusan extends Model
{
    use HasUuids;

    protected $table = 'kelulusan';

    protected $fillable = [
        'calon_siswa_id',
        'tahun_pelajaran_id',
        'status',
        'catatan',
        'diluluskan_oleh',
        'tanggal_kelulusan',
    ];

    protected $casts = [
        'tanggal_kelulusan' => 'datetime',
    ];

    // Relations
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diluluskan_oleh');
    }

    // Scopes
    public function scopeLulus($query)
    {
        return $query->where('status', 'lulus');
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('status', 'tidak_lulus');
    }

    public function scopeCadangan($query)
    {
        return $query->where('status', 'cadangan');
    }
}
