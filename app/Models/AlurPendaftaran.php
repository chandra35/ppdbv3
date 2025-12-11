<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlurPendaftaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'alur_pendaftarans';

    protected $fillable = [
        'urutan',
        'judul',
        'deskripsi',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope untuk yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc');
    }

    /**
     * Get all active alur ordered
     */
    public static function getActiveOrdered()
    {
        return static::active()->ordered()->get();
    }

    /**
     * Get next urutan number
     */
    public static function getNextUrutan()
    {
        return (static::max('urutan') ?? 0) + 1;
    }

    /**
     * Icon list for selection
     */
    public const ICON_LIST = [
        'fas fa-user-plus' => 'User Plus (Registrasi)',
        'fas fa-file-upload' => 'File Upload (Upload Dokumen)',
        'fas fa-clipboard-check' => 'Clipboard Check (Verifikasi)',
        'fas fa-bullhorn' => 'Bullhorn (Pengumuman)',
        'fas fa-check-circle' => 'Check Circle (Selesai)',
        'fas fa-file-alt' => 'File Alt (Dokumen)',
        'fas fa-edit' => 'Edit (Edit Data)',
        'fas fa-search' => 'Search (Seleksi)',
        'fas fa-calendar-check' => 'Calendar Check (Jadwal)',
        'fas fa-id-card' => 'ID Card (Kartu)',
        'fas fa-print' => 'Print (Cetak)',
        'fas fa-money-bill' => 'Money (Pembayaran)',
        'fas fa-handshake' => 'Handshake (Wawancara)',
        'fas fa-graduation-cap' => 'Graduation Cap (Lulus)',
    ];
}
