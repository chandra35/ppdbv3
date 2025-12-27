<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SIMANSA GTK Model - READ ONLY access to simansav3 database
 * Used for syncing GTK data to local database
 */
class SimansaGtk extends Model
{
    use HasUuids, SoftDeletes;

    protected $connection = 'simansav3';  // SIMANSA database
    protected $table = 'gtks';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nik',
        'jenis_kelamin',
        'nuptk',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'email',
        'nomor_hp',
        'alamat',
        'kategori_ptk',
        'jenis_ptk',
        'jabatan',
        'status_kepegawaian',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_kerja' => 'date',
        'data_diri_completed' => 'boolean',
        'data_kepegawaian_completed' => 'boolean',
    ];

    public $timestamps = true;

    // Scope untuk filter GTK aktif (tidak soft deleted)
    public function scopeAktif($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Accessor untuk nama display
    public function getNamaAttribute()
    {
        return $this->nama_lengkap;
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }
}

    
    protected $fillable = [
        'user_id',
        'nama_lengkap',  // ✅ Fixed: sesuai kolom di database
        'nik',
        'jenis_kelamin',
        'nuptk',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'email',
        'nomor_hp',
        'alamat',
        'kategori_ptk',
        'jenis_ptk',
        'jabatan',
        'status_kepegawaian',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_kerja' => 'date',
        'data_diri_completed' => 'boolean',
        'data_kepegawaian_completed' => 'boolean',
    ];

    public $timestamps = true;

    // Relation to Verifikator (PPDB)
    public function verifikator(): HasOne
    {
        return $this->hasOne(Verifikator::class, 'gtk_id');
    }

    // Scope untuk filter GTK aktif (tidak soft deleted)
    public function scopeAktif($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Accessor untuk nama display
    public function getNamaAttribute()
    {
        return $this->nama_lengkap;
    }
}
