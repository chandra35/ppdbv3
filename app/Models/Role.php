<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->using(UserRole::class)
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check for exact permission
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check for wildcard in permission group (e.g., pendaftar.*)
        $parts = explode('.', $permission);
        if (count($parts) > 1) {
            $groupWildcard = $parts[0] . '.*';
            if (in_array($groupWildcard, $permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get available permissions grouped by category
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'pendaftar' => [
                'pendaftar.view' => 'Lihat Pendaftar',
                'pendaftar.create' => 'Tambah Pendaftar',
                'pendaftar.edit' => 'Edit Pendaftar',
                'pendaftar.delete' => 'Hapus Pendaftar',
                'pendaftar.verify' => 'Verifikasi Pendaftar',
                'pendaftar.approve' => 'Terima Pendaftar',
                'pendaftar.reject' => 'Tolak Pendaftar',
                'pendaftar.cetak-registrasi' => 'Cetak Bukti Registrasi',
                'pendaftar.cetak-ujian' => 'Cetak Kartu Ujian',
                'pendaftar.upload-dokumen' => 'Upload Dokumen Pendaftar',
            ],
            'berita' => [
                'berita.view' => 'Lihat Berita',
                'berita.create' => 'Tambah Berita',
                'berita.edit' => 'Edit Berita',
                'berita.delete' => 'Hapus Berita',
            ],
            'slider' => [
                'slider.view' => 'Lihat Slider',
                'slider.create' => 'Tambah Slider',
                'slider.edit' => 'Edit Slider',
                'slider.delete' => 'Hapus Slider',
            ],
            'user' => [
                'user.view' => 'Lihat User',
                'user.create' => 'Tambah User',
                'user.edit' => 'Edit User',
                'user.delete' => 'Hapus User',
            ],
            'role' => [
                'role.view' => 'Lihat Role',
                'role.create' => 'Tambah Role',
                'role.edit' => 'Edit Role',
                'role.delete' => 'Hapus Role',
            ],
            'settings' => [
                'settings.view' => 'Lihat Pengaturan',
                'settings.edit' => 'Edit Pengaturan',
            ],
            'logs' => [
                'logs.view' => 'Lihat Activity Log',
                'logs.clear' => 'Hapus Activity Log',
            ],
            'public' => [
                'public.view' => 'Lihat Halaman Publik',
                'public.info-ppdb' => 'Lihat Informasi PPDB',
                'public.berita' => 'Lihat Berita',
                'public.pengumuman' => 'Lihat Pengumuman',
                'public.kontak' => 'Lihat Kontak',
            ],
        ];
    }
}
