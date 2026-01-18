<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// NIK mungkin sebagai email atau username
$nik = '1802060207870001';
$user = App\Models\User::where('email', $nik)
    ->orWhere('username', $nik)
    ->orWhere('email', 'like', "%$nik%")
    ->with('roles')
    ->first();

if (!$user) {
    echo "User tidak ditemukan dengan NIK: $nik\n";
    echo "Mencari di calon_siswa...\n";
    
    $calon = App\Models\CalonSiswa::where('nik', $nik)->first();
    if ($calon) {
        echo "Calon Siswa ditemukan: " . $calon->nama_lengkap . "\n";
        echo "User ID: " . $calon->user_id . "\n";
        $user = App\Models\User::with('roles')->find($calon->user_id);
    }
}

if (!$user) {
    echo "User tidak ditemukan!\n";
    exit;
}

echo "=== USER INFO ===\n";
echo "Name: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "Role(s): " . $user->roles->pluck('name')->join(', ') . "\n";
echo "isAdmin(): " . ($user->isAdmin() ? 'Yes' : 'No') . "\n\n";

$role = $user->roles->first();
if ($role) {
    echo "=== ROLE DETAIL ===\n";
    echo "Role Name: " . $role->name . "\n";
    echo "Display Name: " . $role->display_name . "\n";
    
    // Check hasPermission for some permissions
    echo "\n=== PERMISSION CHECK (via hasPermission) ===\n";
    $permsToCheck = ['berita.view', 'berita.create', 'slider.view', 'slider.create', 'settings.view', 'admin'];
    foreach ($permsToCheck as $perm) {
        echo "hasPermission('$perm'): " . ($user->hasPermission($perm) ? 'Yes' : 'No') . "\n";
    }
    
    // Check Gate
    echo "\n=== GATE CHECK ===\n";
    auth()->login($user);
    foreach ($permsToCheck as $perm) {
        try {
            $result = Illuminate\Support\Facades\Gate::allows($perm) ? 'Yes' : 'No';
        } catch (Exception $e) {
            $result = 'Error: ' . $e->getMessage();
        }
        echo "Gate::allows('$perm'): $result\n";
    }
}

echo "=== USER INFO ===\n";
echo "Name: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "NIK: " . $user->nik . "\n";
echo "Role(s): " . $user->roles->pluck('name')->join(', ') . "\n";
echo "isAdmin(): " . ($user->isAdmin() ? 'Yes' : 'No') . "\n\n";

$role = $user->roles->first();
if ($role) {
    echo "=== ROLE DETAIL ===\n";
    echo "Role Name: " . $role->name . "\n";
    echo "Display Name: " . $role->display_name . "\n";
    echo "Permissions stored: " . json_encode($role->permissions, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check hasPermission for some permissions
    echo "=== PERMISSION CHECK ===\n";
    $permsToCheck = ['pendaftar.view', 'pendaftar.edit', 'pendaftar.delete', 'settings.view', 'user.view', 'role.view', 'admin'];
    foreach ($permsToCheck as $perm) {
        echo "hasPermission('$perm'): " . ($user->hasPermission($perm) ? 'Yes' : 'No') . "\n";
    }
}
