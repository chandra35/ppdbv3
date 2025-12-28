<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\PpdbSettings;

echo "=== Test Dokumen Completion Calculation ===\n\n";

// Get settings
$settings = PpdbSettings::first();
if (!$settings) {
    echo "❌ PpdbSettings not found\n";
    exit;
}

echo "📋 Required Documents from Settings:\n";
$requiredDokumen = $settings->dokumen_aktif ?? [];
if (empty($requiredDokumen)) {
    echo "   Default: ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport']\n";
    $requiredDokumen = ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
} else {
    foreach ($requiredDokumen as $dok) {
        echo "   - $dok\n";
    }
}
echo "   Total Required: " . count($requiredDokumen) . "\n\n";

// Get all calon siswa
$calonSiswas = CalonSiswa::with('dokumen')->get();

echo "📊 Checking " . $calonSiswas->count() . " calon siswa:\n\n";

foreach ($calonSiswas as $cs) {
    $uploadedDocs = $cs->dokumen()
        ->whereIn('jenis_dokumen', $requiredDokumen)
        ->get();
    
    $uploadedCount = $uploadedDocs->count();
    $requiredCount = count($requiredDokumen);
    
    $calculatedCompleted = ($uploadedCount >= $requiredCount);
    $flagCompleted = $cs->data_dokumen_completed;
    
    $status = ($calculatedCompleted === $flagCompleted) ? '✅ OK' : '❌ MISMATCH';
    
    echo "👤 {$cs->nama_lengkap}\n";
    echo "   Uploaded: $uploadedCount/$requiredCount\n";
    echo "   Documents: ";
    if ($uploadedDocs->isEmpty()) {
        echo "none";
    } else {
        echo implode(', ', $uploadedDocs->pluck('jenis_dokumen')->toArray());
    }
    echo "\n";
    echo "   Calculated: " . ($calculatedCompleted ? 'true' : 'false') . "\n";
    echo "   DB Flag: " . ($flagCompleted ? 'true' : 'false') . "\n";
    echo "   Status: $status\n\n";
}

echo "\n=== Test Complete ===\n";
