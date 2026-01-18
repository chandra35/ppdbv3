<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update role verifikator dengan permission baru
$role = App\Models\Role::where('name', 'verifikator')->first();

if (!$role) {
    echo "Role verifikator tidak ditemukan!\n";
    exit;
}

echo "Updating role: {$role->name}\n";
echo "Old permissions: " . json_encode($role->permissions) . "\n\n";

// Set permission baru yang sesuai dengan sistem
$newPermissions = [
    // Pendaftar
    'pendaftar.view',
    'pendaftar.edit',
    
    // Verifikasi
    'verifikasi.view',
    'verifikasi.verify',
    'verifikasi.approve',
    'verifikasi.reject',
    'verifikasi.finalisasi',
    'verifikasi.cetak',
    
    // Statistik
    'statistik.view',
    
    // Visitor
    'visitor.view',
];

$role->permissions = $newPermissions;
$role->save();

echo "New permissions: " . json_encode($role->permissions, JSON_PRETTY_PRINT) . "\n";
echo "\nRole verifikator berhasil diupdate!\n";
