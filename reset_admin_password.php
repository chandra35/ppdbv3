<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$admin = App\Models\User::where('email', 'admin@ppdb.local')->first();

if ($admin) {
    $newPassword = 'admin123';
    $admin->password = Hash::make($newPassword);
    $admin->save();
    
    echo "✓ Password admin berhasil direset\n";
    echo "Email: {$admin->email}\n";
    echo "Password: {$newPassword}\n";
} else {
    echo "✗ User admin tidak ditemukan\n";
}
