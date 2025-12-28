<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;

echo "=== Verify Pilihan Program After Refresh ===\n\n";

// Fresh query from database
$calonSiswa = CalonSiswa::first();

if (!$calonSiswa) {
    echo "❌ Calon siswa tidak ditemukan\n";
    exit;
}

echo "📋 Calon Siswa: {$calonSiswa->nama_lengkap}\n";
echo "📊 pilihan_program: " . ($calonSiswa->pilihan_program ?? 'NULL') . "\n";

if ($calonSiswa->pilihan_program === 'Test Program') {
    echo "\n✅ DATA MASIH ADA DI DATABASE!\n";
    
    // Reset to NULL untuk test selanjutnya
    echo "\n🔄 Mereset ke NULL untuk test UI...\n";
    $calonSiswa->update(['pilihan_program' => null]);
    $calonSiswa->refresh();
    echo "   pilihan_program: " . ($calonSiswa->pilihan_program ?? 'NULL') . "\n";
    echo "✅ Reset complete\n";
} else {
    echo "\n⚠️ DATA BERBEDA: " . ($calonSiswa->pilihan_program ?? 'NULL') . "\n";
}

echo "\n=== Complete ===\n";
