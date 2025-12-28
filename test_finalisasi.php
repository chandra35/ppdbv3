<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;

echo "=== Test Finalisasi Update ===\n\n";

// Get calon siswa
$calonSiswa = CalonSiswa::first();

if (!$calonSiswa) {
    echo "❌ Calon siswa tidak ditemukan\n";
    exit;
}

echo "📋 Testing dengan: {$calonSiswa->nama_lengkap}\n";
echo "   Current status:\n";
echo "   - is_finalisasi: " . ($calonSiswa->is_finalisasi ? 'true' : 'false') . "\n";
echo "   - status_admisi: " . ($calonSiswa->status_admisi ?? 'NULL') . "\n";
echo "   - nomor_tes: " . ($calonSiswa->nomor_tes ?? 'NULL') . "\n\n";

// Reset first if already finalized
if ($calonSiswa->is_finalisasi) {
    echo "🔄 Mereset finalisasi untuk test...\n";
    $calonSiswa->update([
        'is_finalisasi' => false,
        'tanggal_finalisasi' => null,
        'nomor_tes' => null,
        'status_admisi' => 'pending'
    ]);
    echo "✅ Reset complete\n\n";
}

// Test finalisasi update
echo "🔄 Mencoba finalisasi...\n";

try {
    $calonSiswa->update([
        'is_finalisasi' => true,
        'tanggal_finalisasi' => now(),
        'nomor_tes' => 'PPDB-2025-TEST-0001',
        'status_admisi' => 'pending'
    ]);
    
    echo "✅ Update berhasil!\n\n";
    
    // Re-fetch from database
    $calonSiswa->refresh();
    
    echo "📊 Nilai setelah finalisasi:\n";
    echo "   - is_finalisasi: " . ($calonSiswa->is_finalisasi ? 'true' : 'false') . "\n";
    echo "   - status_admisi: " . ($calonSiswa->status_admisi ?? 'NULL') . "\n";
    echo "   - nomor_tes: " . ($calonSiswa->nomor_tes ?? 'NULL') . "\n";
    echo "   - tanggal_finalisasi: " . ($calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('Y-m-d H:i:s') : 'NULL') . "\n";
    
    if ($calonSiswa->is_finalisasi && $calonSiswa->status_admisi === 'pending' && $calonSiswa->nomor_tes) {
        echo "\n✅ FINALISASI BERHASIL!\n";
    } else {
        echo "\n❌ DATA TIDAK LENGKAP\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
