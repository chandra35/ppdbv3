<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CalonSiswa;
use App\Models\SekolahSettings;

// Get first finalized student
$siswa = CalonSiswa::where('is_finalisasi', true)->first();

if (!$siswa) {
    echo "No finalized student found\n";
    exit;
}

echo "Student: " . $siswa->nama_lengkap . "\n";
echo "Nomor Registrasi: " . $siswa->nomor_registrasi . "\n";
echo "Is Finalisasi: " . ($siswa->is_finalisasi ? 'Yes' : 'No') . "\n";
echo "Verification Hash: " . ($siswa->verification_hash ?: 'NULL') . "\n";

// Get or generate hash
$hash = $siswa->getOrGenerateHash();
echo "Hash after getOrGenerate: " . $hash . "\n";

// Check sekolah settings
$settings = SekolahSettings::first();
echo "\nQR Settings:\n";
echo "- QR Enable: " . ($settings->qr_enable ? 'Yes' : 'No') . "\n";
echo "- QR With Logo: " . ($settings->qr_with_logo ? 'Yes' : 'No') . "\n";
echo "- QR Size: " . $settings->qr_size . "\n";
echo "- QR Error Level: " . $settings->qr_error_level . "\n";

// Test QR generation
echo "\nTesting QR Code generation...\n";
try {
    $url = route('verify.bukti', $hash);
    echo "URL: " . $url . "\n";
    
    $qr = \QrCode::size(150)
        ->format('png')
        ->errorCorrection('H')
        ->generate($url);
    
    echo "QR Code generated successfully!\n";
    echo "Length: " . strlen($qr) . " bytes\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
