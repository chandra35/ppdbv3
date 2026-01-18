<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Role;
use App\Models\CustomPermission;
use Illuminate\Support\Facades\Cache;

echo "=== ANALISA CUSTOM PERMISSIONS ===\n\n";

// 1. Check tabel
echo "1. Tabel custom_permissions:\n";
$count = CustomPermission::count();
$activeCount = CustomPermission::where('is_active', true)->count();
echo "   - Total: {$count}\n";
echo "   - Aktif: {$activeCount}\n\n";

// 2. Check cache
echo "2. Cache custom_permissions_grouped:\n";
$cache = Cache::get('custom_permissions_grouped');
if ($cache) {
    echo "   - Status: ADA (". count($cache) ." groups)\n";
    echo "   - Groups: " . implode(', ', array_keys($cache)) . "\n";
} else {
    echo "   - Status: TIDAK ADA (akan di-generate saat dibutuhkan)\n";
}
echo "\n";

// 3. Check hardcoded
echo "3. Hardcoded Permissions (dari Role::getHardcodedPermissions()):\n";
$hardcoded = Role::getHardcodedPermissions();
foreach ($hardcoded as $group => $perms) {
    echo "   - {$group}: " . count($perms) . " permissions\n";
}
echo "\n";

// 4. Check custom grouped
echo "4. Custom Permissions Grouped (dari CustomPermission::getGrouped()):\n";
$customGrouped = CustomPermission::getGrouped();
if (empty($customGrouped)) {
    echo "   - KOSONG! Tidak ada custom permission yang aktif atau method tidak berjalan\n";
} else {
    foreach ($customGrouped as $group => $perms) {
        echo "   - {$group}: " . count($perms) . " permissions\n";
    }
}
echo "\n";

// 5. Check merged
echo "5. Merged Permissions (dari Role::getAvailablePermissions()):\n";
$available = Role::getAvailablePermissions();
$customGroups = array_diff(array_keys($available), array_keys($hardcoded));
echo "   - Total groups: " . count($available) . "\n";
echo "   - Hardcoded groups: " . count($hardcoded) . "\n";
echo "   - Custom/Merged groups: " . count($customGroups) . "\n";
echo "   - Custom groups: " . implode(', ', $customGroups) . "\n\n";

// 6. Check duplikat
echo "6. Duplikat Permission Names (hardcoded vs custom):\n";
$hardcodedFlat = [];
foreach ($hardcoded as $group => $perms) {
    $hardcodedFlat = array_merge($hardcodedFlat, array_keys($perms));
}

$customFlat = CustomPermission::where('is_active', true)->pluck('name')->toArray();
$duplicates = array_intersect($hardcodedFlat, $customFlat);

if (!empty($duplicates)) {
    echo "   - DITEMUKAN " . count($duplicates) . " duplikat:\n";
    foreach ($duplicates as $dup) {
        echo "     - {$dup}\n";
    }
} else {
    echo "   - Tidak ada duplikat (BAIK)\n";
}
echo "\n";

echo "=== SELESAI ===\n";
