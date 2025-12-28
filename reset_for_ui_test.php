<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;

echo "=== Reset Finalisasi for UI Test ===\n\n";

$calonSiswa = CalonSiswa::first();

if ($calonSiswa) {
    echo "📋 Resetting: {$calonSiswa->nama_lengkap}\n\n";
    
    $calonSiswa->update([
        'is_finalisasi' => false,
        'tanggal_finalisasi' => null,
        'nomor_tes' => null,
        'pilihan_program' => null
    ]);
    
    echo "✅ Reset complete - ready for UI test\n";
} else {
    echo "❌ No calon siswa found\n";
}
