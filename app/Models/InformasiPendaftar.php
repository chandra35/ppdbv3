<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformasiPendaftar extends Model
{
    use HasFactory;

    protected $table = 'informasi_pendaftar';

    protected $fillable = [
        'judul',
        'isi',
        'is_active',
        'tampilkan_modal',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tampilkan_modal' => 'boolean',
    ];

    /**
     * Scope untuk informasi aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk informasi yang tampil di modal
     */
    public function scopeModal($query)
    {
        return $query->where('tampilkan_modal', true);
    }

    /**
     * Get informasi aktif untuk modal
     */
    public static function getModalInfo()
    {
        return self::active()
            ->modal()
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get semua informasi aktif
     */
    public static function getActiveInfo()
    {
        return self::active()
            ->orderBy('urutan')
            ->get();
    }
}
