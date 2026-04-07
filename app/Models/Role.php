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
        'can_access_admin',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'can_access_admin' => 'boolean',
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
     * Get hardcoded permissions (system default)
     */
    public static function getHardcodedPermissions(): array
    {
        return [
            'pendaftar' => [
                'pendaftar.view' => 'Lihat Pendaftar',
                'pendaftar.create' => 'Tambah Pendaftar',
                'pendaftar.edit' => 'Edit Pendaftar',
                'pendaftar.delete' => 'Hapus Pendaftar',
                'pendaftar.export' => 'Export Data Pendaftar',
            ],
            'verifikasi' => [
                'verifikasi.view' => 'Lihat Status Verifikasi',
                'verifikasi.verify' => 'Verifikasi Dokumen',
                'verifikasi.approve' => 'Terima Pendaftar',
                'verifikasi.reject' => 'Tolak Pendaftar',
                'verifikasi.finalisasi' => 'Finalisasi Pendaftar',
                'verifikasi.cetak' => 'Cetak Dokumen',
            ],
            'statistik' => [
                'statistik.view' => 'Lihat Statistik',
                'statistik.export' => 'Export Statistik',
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
            'kelulusan' => [
                'kelulusan.view' => 'Lihat Kelulusan',
                'kelulusan.manage' => 'Kelola Status Kelulusan',
                'kelulusan.setting' => 'Atur Pengumuman Kelulusan',
                'kelulusan.logs' => 'Lihat Log Amplop Kelulusan',
            ],
            'logs' => [
                'logs.view' => 'Lihat Activity Log',
                'logs.clear' => 'Hapus Activity Log',
            ],
            'visitor' => [
                'visitor.view' => 'Lihat Statistik Pengunjung',
                'visitor.online' => 'Lihat Pengunjung Online',
                'visitor.detail' => 'Lihat Detail Pengunjung',
                'visitor.export' => 'Export Data Pengunjung',
                'visitor.clear' => 'Hapus Data Pengunjung',
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

    /**
     * Get available permissions grouped by category (hardcoded + custom)
     */
    public static function getAvailablePermissions(): array
    {
        $hardcoded = self::getHardcodedPermissions();
        $custom = CustomPermission::getGrouped();
        
        // Merge custom permissions into hardcoded (custom permissions are appended to groups)
        foreach ($custom as $group => $permissions) {
            if (isset($hardcoded[$group])) {
                $hardcoded[$group] = array_merge($hardcoded[$group], $permissions);
            } else {
                $hardcoded[$group] = $permissions;
            }
        }
        
        return $hardcoded;
    }
}
