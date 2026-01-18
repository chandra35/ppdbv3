<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== VERIFIKASI SISTEM CAN_ACCESS_ADMIN ===" . PHP_EOL . PHP_EOL;

// 1. Cek kolom can_access_admin di roles
echo "1. Role dengan can_access_admin = TRUE:" . PHP_EOL;
$adminRoles = Role::where('can_access_admin', true)->get();
foreach ($adminRoles as $role) {
    echo "   - " . $role->display_name . " (" . $role->name . ")" . PHP_EOL;
}

echo PHP_EOL . "2. Role dengan can_access_admin = FALSE:" . PHP_EOL;
$nonAdminRoles = Role::where('can_access_admin', false)->get();
foreach ($nonAdminRoles as $role) {
    echo "   - " . $role->display_name . " (" . $role->name . ")" . PHP_EOL;
}

echo PHP_EOL . "3. Test canAccessAdminPanel() untuk beberapa user:" . PHP_EOL;

// Test user dengan role mas-admin
$masAdminUser = User::whereHas('roles', function($q) {
    $q->where('name', 'mas-admin');
})->first();
if ($masAdminUser) {
    $result = $masAdminUser->canAccessAdminPanel() ? 'TRUE' : 'FALSE';
    echo "   - " . $masAdminUser->name . " (mas-admin): canAccessAdminPanel = " . $result . PHP_EOL;
}

// Test user dengan role pendaftar
$pendaftarUser = User::whereHas('roles', function($q) {
    $q->where('name', 'pendaftar');
})->first();
if ($pendaftarUser) {
    $result = $pendaftarUser->canAccessAdminPanel() ? 'TRUE' : 'FALSE';
    echo "   - " . $pendaftarUser->name . " (pendaftar): canAccessAdminPanel = " . $result . PHP_EOL;
}

// Test user admin
$admin = User::where('email', 'admin@madrasah.sch.id')->first();
if ($admin) {
    $result = $admin->canAccessAdminPanel() ? 'TRUE' : 'FALSE';
    echo "   - " . $admin->name . " (admin): canAccessAdminPanel = " . $result . PHP_EOL;
}

echo PHP_EOL . "=== SISTEM SIAP! ===" . PHP_EOL;
