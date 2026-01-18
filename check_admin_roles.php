<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEMUA ROLES ===\n\n";

$roles = App\Models\Role::orderBy('name')->get();

foreach ($roles as $role) {
    $permCount = count($role->permissions ?? []);
    echo "{$role->name} - {$role->display_name} ({$permCount} permissions)\n";
}

echo "\n=== ROLE YANG BISA AKSES ADMIN PANEL ===\n";
echo "(Role dengan minimal 1 permission atau role khusus admin)\n\n";

$adminRoles = [];
foreach ($roles as $role) {
    // Role yang bisa akses admin panel:
    // 1. Role sistem: admin, super-admin, operator, verifikator
    // 2. Role dengan permission (bukan role pendaftar kosong)
    $isSystemAdmin = in_array($role->name, ['admin', 'super-admin', 'operator', 'verifikator']);
    $hasPermissions = !empty($role->permissions);
    
    if ($isSystemAdmin || $hasPermissions) {
        $adminRoles[] = $role->name;
        echo "✅ {$role->name}\n";
    } else {
        echo "❌ {$role->name} (no permissions)\n";
    }
}

echo "\n=== SUGGESTION ===\n";
echo "Roles untuk admin panel: " . implode(', ', $adminRoles) . "\n";
