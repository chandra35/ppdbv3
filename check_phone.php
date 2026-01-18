<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cs = \App\Models\CalonSiswa::with(['ortu', 'user', 'jalurPendaftaran'])->find('019bce6b-0dfd-7317-b099-5abc12d64f36');

echo "=== Data Pendaftar ===\n";
echo "Nama: " . $cs->nama_lengkap . "\n";
echo "Nomor HP: " . ($cs->nomor_hp ?? 'NULL') . "\n";
echo "HP Ayah: " . ($cs->ortu?->hp_ayah ?? 'NULL') . "\n";
echo "Nomor Tes: " . ($cs->nomor_tes ?? 'NULL') . "\n\n";

// Get phone
$phone = $cs->nomor_hp 
    ?? $cs->ortu?->hp_ayah 
    ?? $cs->ortu?->hp_ibu 
    ?? $cs->ortu?->hp_wali
    ?? $cs->user?->phone
    ?? null;

echo "Phone to use: " . ($phone ?? 'NULL') . "\n\n";

if ($phone && $cs->nomor_tes) {
    echo "=== Sending WA Notification ===\n";
    
    // Call sendVerificationNotification via reflection (it's protected)
    $reflection = new ReflectionMethod($cs, 'sendVerificationNotification');
    $reflection->setAccessible(true);
    $reflection->invoke($cs, $cs->nomor_tes);
    
    echo "Notification sent!\n";
} else {
    echo "Cannot send: missing phone or nomor_tes\n";
}
