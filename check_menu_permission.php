<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Role;
use App\Models\CustomPermission;

echo "=== ANALISA MISMATCH MENU vs PERMISSION ===\n\n";

// Menu permissions yang digunakan (dari config/adminlte.php)
$menuPermissions = [
    // Headers & Dashboard
    'admin-panel',
    
    // Pendaftar menu
    'pendaftar.view',
    'verifikasi.finalisasi',
    'verifikasi.cetak',
    'statistik.view',
    
    // Settings menu
    'settings.view',
    'settings.edit',
    
    // Berita & Slider
    'berita.view',
    'slider.view',
    
    // User & Role
    'user.view',
    'role.view',
    
    // System
    'logs.view',
    'visitor.view',
    
    // Admin only
    'admin',
    'pendaftar.delete',
];

// Get all available permissions
$available = Role::getAvailablePermissions();
$availableFlat = [];
foreach ($available as $group => $perms) {
    foreach ($perms as $name => $label) {
        $availableFlat[$name] = $label;
    }
}

// Custom permissions yang ada
$customPerms = CustomPermission::pluck('display_name', 'name')->toArray();

echo "1. PERMISSION DI MENU TAPI TIDAK DI getAvailablePermissions():\n";
echo "   (Menu tidak akan muncul karena Gate tidak terdaftar)\n";
echo "   ─────────────────────────────────────────────────────────────\n";

$notInAvailable = [];
foreach ($menuPermissions as $perm) {
    // Skip special gates (bukan permission biasa)
    if (in_array($perm, ['admin', 'admin-panel', 'operator', 'verifikator', 'operator-or-verifikator', 'only-operator-or-verifikator'])) {
        continue;
    }
    
    if (!isset($availableFlat[$perm])) {
        $notInAvailable[] = $perm;
        echo "   ❌ {$perm} - TIDAK TERDAFTAR\n";
    }
}

if (empty($notInAvailable)) {
    echo "   ✅ Semua permission di menu sudah terdaftar\n";
}

echo "\n";

// Check routes that use permission middleware
echo "2. ROUTES DENGAN permission: MIDDLEWARE:\n";
echo "   ─────────────────────────────────────────────────────────────\n";

$routes = app('router')->getRoutes();
$routePermissions = [];

foreach ($routes as $route) {
    $middleware = $route->middleware();
    foreach ($middleware as $mw) {
        if (str_starts_with($mw, 'permission:')) {
            $perm = str_replace('permission:', '', $mw);
            $routePermissions[$perm][] = $route->getName() ?? $route->uri();
        }
    }
}

foreach ($routePermissions as $perm => $routeNames) {
    $status = isset($availableFlat[$perm]) ? '✅' : '❌';
    echo "   {$status} {$perm} → " . count($routeNames) . " routes\n";
}

echo "\n";

// Check what routes are still in can:admin that might need permission
echo "3. ROUTES DALAM can:admin YANG MUNGKIN PERLU PERMISSION SENDIRI:\n";
echo "   (Ini yang menyebabkan 403 untuk non-admin)\n";
echo "   ─────────────────────────────────────────────────────────────\n";

// Group by prefix
$adminOnlyRoutes = [];
foreach ($routes as $route) {
    $middleware = $route->middleware();
    if (in_array('can:admin', $middleware)) {
        $name = $route->getName() ?? '';
        if (str_starts_with($name, 'admin.')) {
            // Group by second level prefix
            $parts = explode('.', $name);
            $group = $parts[1] ?? 'other';
            $adminOnlyRoutes[$group][] = $name;
        }
    }
}

ksort($adminOnlyRoutes);
foreach ($adminOnlyRoutes as $group => $routeNames) {
    echo "   📁 {$group}: " . count($routeNames) . " routes\n";
}

echo "\n";

// Summary of what needs to be done
echo "4. RINGKASAN MASALAH:\n";
echo "   ─────────────────────────────────────────────────────────────\n";

$totalAdminOnly = 0;
foreach ($adminOnlyRoutes as $routeNames) {
    $totalAdminOnly += count($routeNames);
}

echo "   - Routes dengan can:admin: {$totalAdminOnly}\n";
echo "   - Ini yang menyebabkan 403 untuk role non-admin\n";
echo "   - Perlu dipindah ke permission-based middleware\n";

echo "\n=== SELESAI ===\n";
