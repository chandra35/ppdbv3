<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Role;
use App\Models\CustomPermission;
use Illuminate\Support\Facades\Cache;

echo "=== ANALISA DUPLIKASI & MASALAH PERMISSION ===\n\n";

// 1. Get hardcoded permissions flat
$hardcoded = Role::getHardcodedPermissions();
$hardcodedFlat = [];
foreach ($hardcoded as $group => $perms) {
    foreach ($perms as $name => $label) {
        $hardcodedFlat[$name] = [
            'group' => $group,
            'label' => $label
        ];
    }
}

// 2. Find duplicates in custom_permissions table
echo "1. DUPLIKAT (Permission yang ada di KEDUA tempat):\n";
echo "   ───────────────────────────────────────────────\n";

$customAll = CustomPermission::all();
$duplicates = [];

foreach ($customAll as $custom) {
    if (isset($hardcodedFlat[$custom->name])) {
        $duplicates[] = [
            'name' => $custom->name,
            'hardcoded_group' => $hardcodedFlat[$custom->name]['group'],
            'hardcoded_label' => $hardcodedFlat[$custom->name]['label'],
            'custom_group' => $custom->group,
            'custom_label' => $custom->display_name,
            'custom_id' => $custom->id,
        ];
    }
}

if (empty($duplicates)) {
    echo "   Tidak ada duplikat.\n";
} else {
    echo "   DITEMUKAN " . count($duplicates) . " duplikat:\n\n";
    foreach ($duplicates as $dup) {
        echo "   Permission: {$dup['name']}\n";
        echo "   - Hardcoded: [{$dup['hardcoded_group']}] {$dup['hardcoded_label']}\n";
        echo "   - Custom:    [{$dup['custom_group']}] {$dup['custom_label']} (ID: {$dup['custom_id']})\n";
        echo "   \n";
    }
}

echo "\n";

// 3. Suggest cleanup
echo "2. REKOMENDASI:\n";
echo "   ─────────────\n";
if (!empty($duplicates)) {
    echo "   - Hapus " . count($duplicates) . " custom permission yang duplikat\n";
    echo "   - Karena sudah ada di hardcoded, tidak perlu di custom\n";
    echo "\n";
    
    echo "3. QUERY UNTUK HAPUS DUPLIKAT:\n";
    echo "   ─────────────────────────────\n";
    $ids = array_column($duplicates, 'custom_id');
    echo "   CustomPermission::whereIn('id', ['" . implode("', '", $ids) . "'])->delete();\n";
}

echo "\n";

// 4. Check settings group issue
echo "4. CEK GROUP 'settings' (MERGED):\n";
echo "   ────────────────────────────────\n";
$available = Role::getAvailablePermissions();
if (isset($available['settings'])) {
    echo "   Permissions in 'settings' group:\n";
    foreach ($available['settings'] as $name => $label) {
        $source = isset($hardcodedFlat[$name]) ? 'HARDCODED' : 'CUSTOM';
        echo "   - {$name}: {$label} [{$source}]\n";
    }
}

echo "\n=== SELESAI ===\n";
