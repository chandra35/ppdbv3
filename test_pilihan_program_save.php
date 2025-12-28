<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;

echo "=== Test Pilihan Program Save ===\n\n";

// Get calon siswa
$calonSiswa = CalonSiswa::first();

if (!$calonSiswa) {
    echo "❌ Calon siswa tidak ditemukan\n";
    exit;
}

echo "📋 Testing dengan: {$calonSiswa->nama_lengkap}\n";
echo "   Current pilihan_program: " . ($calonSiswa->pilihan_program ?? 'NULL') . "\n\n";

// Test update
echo "🔄 Mencoba update pilihan_program ke 'Test Program'...\n";

try {
    $calonSiswa->update([
        'pilihan_program' => 'Test Program'
    ]);
    
    echo "✅ Update berhasil!\n\n";
    
    // Re-fetch from database
    $calonSiswa->refresh();
    
    echo "📊 Nilai setelah update:\n";
    echo "   pilihan_program: " . ($calonSiswa->pilihan_program ?? 'NULL') . "\n";
    
    if ($calonSiswa->pilihan_program === 'Test Program') {
        echo "\n✅ DATA TERSIMPAN DENGAN BENAR!\n";
    } else {
        echo "\n❌ DATA TIDAK TERSIMPAN (masih: " . ($calonSiswa->pilihan_program ?? 'NULL') . ")\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
