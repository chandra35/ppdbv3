<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Check penguji role
$role = \App\Models\Role::where('name', 'penguji')->first();
if ($role) {
    echo "Penguji role found:\n";
    echo "  Name: {$role->name}\n";
    echo "  Display: {$role->display_name}\n";
    echo "  can_access_admin: " . ($role->can_access_admin ? 'YES' : 'NO') . "\n";
    echo "  permissions: " . json_encode($role->permissions) . "\n\n";
} else {
    echo "Penguji role NOT FOUND!\n\n";
    // List all roles
    $roles = \App\Models\Role::all();
    echo "Available roles:\n";
    foreach ($roles as $r) {
        echo "  - {$r->name} (display: {$r->display_name}, can_access_admin: " . ($r->can_access_admin ? 'Y' : 'N') . ")\n";
    }
    echo "\n";
}

// Check users with penguji role
$users = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'penguji'))->get();
echo "Penguji users: {$users->count()}\n";
foreach ($users as $u) {
    echo "  - {$u->name} | {$u->email} | roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
}

// Check user with NIP 1807034209910004
echo "\n--- Looking for NIP 1807034209910004 ---\n";
$gtk = \App\Models\LocalGtk::where('nip', '1807034209910004')->first();
if ($gtk) {
    echo "GTK found: {$gtk->nama} | email: {$gtk->email}\n";
} else {
    echo "GTK not found with NIP, trying NIK...\n";
    $gtk = \App\Models\LocalGtk::where('nik', '1807034209910004')->first();
    if ($gtk) {
        echo "GTK found by NIK: {$gtk->nama} | email: {$gtk->email}\n";
    } else {
        echo "GTK not found by NIK either.\n";
    }
}

// Try finding user by username
$user = \App\Models\User::where('username', '1807034209910004')->first();
if ($user) {
    echo "User found by username: {$user->name} | {$user->email}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "canAccessAdmin: " . ($user->canAccessAdminPanel() ? 'YES' : 'NO') . "\n";
    foreach ($user->roles as $r) {
        echo "  Role '{$r->name}': can_access_admin=" . ($r->can_access_admin ? 'Y' : 'N') . ", permissions=" . json_encode($r->permissions) . "\n";
    }
} else {
    echo "User not found by username either.\n";
}

// Try partial match  
echo "\n--- GTK with NIP containing 18070 ---\n";
$gtks = \App\Models\LocalGtk::where('nip', 'LIKE', '%18070342%')->get();
foreach ($gtks as $g) {
    echo "  {$g->nama} | NIP: {$g->nip} | NIK: {$g->nik}\n";
}
