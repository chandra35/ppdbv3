<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEMUA ROLES DAN PERMISSIONS ===\n\n";

$roles = App\Models\Role::orderBy('name')->get();

foreach ($roles as $role) {
    echo "----------------------------------------\n";
    echo "Role: {$role->name}\n";
    echo "Display: {$role->display_name}\n";
    echo "Permissions (" . count($role->permissions ?? []) . "):\n";
    
    if ($role->permissions) {
        foreach ($role->permissions as $perm) {
            echo "  - $perm\n";
        }
    } else {
        echo "  (tidak ada permission)\n";
    }
    echo "\n";
}

echo "=== MAPPING MENU -> PERMISSION ===\n\n";
echo "Dashboard        -> admin-panel (semua role admin panel)\n";
echo "Pendaftar        -> pendaftar.view\n";
echo "  - Semua Pendaftar -> pendaftar.view\n";
echo "  - Finalisasi      -> verifikasi.finalisasi\n";
echo "  - Cetak Dokumen   -> verifikasi.cetak\n";
echo "  - Cetak Ruang     -> verifikasi.cetak\n";
echo "  - Statistik       -> statistik.view\n";
echo "Berita           -> berita.view\n";
echo "Slider           -> slider.view\n";
echo "User & Role      -> user.view\n";
echo "  - User Management -> user.view\n";
echo "  - Role Management -> role.view\n";
echo "  - GTK             -> user.view\n";
echo "Activity Log     -> logs.view\n";
echo "Statistik Pengunjung -> visitor.view\n";
echo "Settings         -> settings.view (admin only)\n";
