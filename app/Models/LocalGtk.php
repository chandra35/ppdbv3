<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Local GTK Model - Data stored in ppdbv3 database
 * Can be created manually or synced from SIMANSA
 */
class LocalGtk extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'gtks';
    protected $connection = 'mysql'; // ppdbv3 database

    protected $fillable = [
        'nama_lengkap',
        'nip',
        'nuptk',
        'nik',
        'jenis_kelamin',
        'email',
        'nomor_hp',
        'kategori_ptk',
        'jenis_ptk',
        'jabatan',
        'status_kepegawaian',
        'source',
        'synced_at',
        'simansa_id',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    // Relations
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'gtk_id');
    }

    // Scopes
    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    public function scopeSynced($query)
    {
        return $query->where('source', 'simansa');
    }

    public function scopeAktif($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Accessors
    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->source === 'manual' ? 'Manual' : 'Sync SIMANSA';
    }

    public function getSourceBadgeAttribute(): string
    {
        return $this->source === 'manual' 
            ? '<span class="badge badge-primary">Manual</span>' 
            : '<span class="badge badge-success">SIMANSA</span>';
    }
}
