<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nik = '1802060207870001';

echo "=== Cek User dengan NIK: {$nik} ===\n\n";

// 1. Cek via username
$userByUsername = \App\Models\User::where('username', $nik)->first();
if ($userByUsername) {
    echo "User ditemukan via USERNAME:\n";
    echo "  - ID: {$userByUsername->id}\n";
    echo "  - Name: {$userByUsername->name}\n";
    echo "  - Email: {$userByUsername->email}\n";
    echo "  - Roles: " . $userByUsername->roles->pluck('name')->join(', ') . "\n";
    echo "  - isAdmin(): " . ($userByUsername->isAdmin() ? 'YES' : 'NO') . "\n";
} else {
    echo "User TIDAK ditemukan via username\n";
}

// 2. Cek GTK
$gtkByNik = \App\Models\LocalGtk::where('nik', $nik)->first();
$gtkByNip = \App\Models\LocalGtk::where('nip', $nik)->first();

if ($gtkByNik) {
    echo "\nGTK ditemukan via NIK:\n";
    echo "  - ID: {$gtkByNik->id}\n";
    echo "  - Nama: {$gtkByNik->nama}\n";
    echo "  - Email: {$gtkByNik->email}\n";
} else {
    echo "\nGTK TIDAK ditemukan via NIK\n";
}

if ($gtkByNip) {
    echo "\nGTK ditemukan via NIP:\n";
    echo "  - ID: {$gtkByNip->id}\n";
    echo "  - Nama: {$gtkByNip->nama}\n";
    echo "  - Email: {$gtkByNip->email}\n";
} else {
    echo "GTK TIDAK ditemukan via NIP\n";
}

// 3. Cek apakah ada user dengan role operator tapi tidak ada di GTK
if ($userByUsername) {
    $calonSiswa = \App\Models\CalonSiswa::where('user_id', $userByUsername->id)->first();
    if ($calonSiswa) {
        echo "\nUser memiliki data CalonSiswa: {$calonSiswa->nama_lengkap}\n";
    } else {
        echo "\nUser TIDAK memiliki data CalonSiswa\n";
    }
}

echo "\n=== Selesai ===\n";
