<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\PpdbSettings;

echo "=== Update Dokumen Completion Flags ===\n\n";

// Get settings
$settings = PpdbSettings::first();
if (!$settings) {
    echo "❌ PpdbSettings not found, using default\n";
    $requiredDokumen = ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
} else {
    $requiredDokumen = $settings->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
}

echo "📋 Required Documents:\n";
foreach ($requiredDokumen as $dok) {
    echo "   - $dok\n";
}
echo "   Total: " . count($requiredDokumen) . "\n\n";

$requiredCount = count($requiredDokumen);

// Get all calon siswa
$calonSiswas = CalonSiswa::all();
$updated = 0;
$unchanged = 0;

foreach ($calonSiswas as $cs) {
    $oldFlag = $cs->data_dokumen_completed;
    
    if ($requiredCount > 0) {
        $uploadedCount = $cs->dokumen()
            ->whereIn('jenis_dokumen', $requiredDokumen)
            ->count();
        
        $newFlag = ($uploadedCount >= $requiredCount);
    } else {
        $newFlag = false;
    }
    
    if ($oldFlag !== $newFlag) {
        $cs->data_dokumen_completed = $newFlag;
        $cs->save();
        
        $status = $newFlag ? '✅ true' : '❌ false';
        echo "🔄 Updated: {$cs->nama_lengkap}\n";
        echo "   Old: " . ($oldFlag ? 'true' : 'false') . " → New: " . ($newFlag ? 'true' : 'false') . "\n";
        echo "   Uploaded: $uploadedCount/$requiredCount\n\n";
        $updated++;
    } else {
        $unchanged++;
    }
}

echo "\n📊 Summary:\n";
echo "   Updated: $updated\n";
echo "   Unchanged: $unchanged\n";
echo "\n=== Update Complete ===\n";
