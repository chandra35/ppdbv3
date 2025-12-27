<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK ADMIN USERS ===\n\n";

$adminRole = App\Models\Role::where('name', 'admin')->first();

if ($adminRole) {
    $admins = $adminRole->users;
    if ($admins->count() > 0) {
        echo "User dengan role 'admin':\n";
        foreach ($admins as $user) {
            echo "- {$user->name} ({$user->email})\n";
        }
    } else {
        echo "Tidak ada user dengan role 'admin'\n";
    }
} else {
    echo "Role 'admin' tidak ditemukan\n";
}

echo "\n=== CEK SUPER-ADMIN USERS ===\n\n";

$superAdminRole = App\Models\Role::where('name', 'super-admin')->first();

if ($superAdminRole) {
    $superAdmins = $superAdminRole->users;
    if ($superAdmins->count() > 0) {
        echo "User dengan role 'super-admin':\n";
        foreach ($superAdmins as $user) {
            echo "- {$user->name} ({$user->email})\n";
        }
    } else {
        echo "Tidak ada user dengan role 'super-admin'\n";
    }
} else {
    echo "Role 'super-admin' tidak ditemukan\n";
}

echo "\n=== CEK SEMUA USERS ===\n\n";

$allUsers = App\Models\User::with('roles')->get();
foreach ($allUsers as $user) {
    $roles = $user->roles->pluck('name')->implode(', ');
    echo "- {$user->name} ({$user->email}) - Roles: " . ($roles ?: 'none') . "\n";
}
