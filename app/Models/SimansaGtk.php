<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk mengakses data GTK dari database simansav3
 */
class SimansaGtk extends Model
{
    protected $connection = 'simansav3';
    protected $table = 'gtks';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_lengkap',
        'nik',
        'nuptk',
        'nip',
        'jenis_kelamin',
        'email',
        'nomor_hp',
        'kategori_ptk',
        'jenis_ptk',
        'status_kepegawaian',
        'jabatan',
    ];

    /**
     * Get GTK user from simansav3
     */
    public function simansaUser()
    {
        return $this->belongsTo(SimansaUser::class, 'user_id');
    }

    /**
     * Scope untuk filter berdasarkan kategori PTK
     */
    public function scopeKategoriPtk($query, $kategori)
    {
        return $query->where('kategori_ptk', $kategori);
    }

    /**
     * Scope untuk filter berdasarkan jenis PTK
     */
    public function scopeJenisPtk($query, $jenis)
    {
        return $query->where('jenis_ptk', $jenis);
    }

    /**
     * Scope untuk filter aktif (tidak soft deleted)
     */
    public function scopeAktif($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get display name for jenis kelamin
     */
    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }
}
