<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SekolahSettings;

echo "=== Test Kop Builder Setup ===\n\n";

// Check sekolah settings
$sekolah = SekolahSettings::first();

if (!$sekolah) {
    echo "❌ Sekolah settings belum ada\n";
    echo "   Silakan buat data sekolah terlebih dahulu di /admin/sekolah\n";
    exit;
}

echo "✅ Sekolah settings ditemukan: {$sekolah->nama_sekolah}\n\n";

// Check new fields
echo "📋 Status field kop surat:\n";
echo "   - logo_kemenag_path: " . ($sekolah->logo_kemenag_path ?? 'NULL') . "\n";
echo "   - logo_kemenag_height: " . ($sekolah->logo_kemenag_height ?? 'NULL') . "\n";
echo "   - kop_mode: " . ($sekolah->kop_mode ?? 'NULL') . "\n";
echo "   - kop_surat_config: " . ($sekolah->kop_surat_config ? 'SET (JSON)' : 'NULL') . "\n";
echo "   - logo_display_height: " . ($sekolah->logo_display_height ?? 'NULL') . "\n";
echo "   - kop_height: " . ($sekolah->kop_height ?? 'NULL') . "\n\n";

// Check if kop_surat_config has elements
if ($sekolah->kop_surat_config && isset($sekolah->kop_surat_config['elements'])) {
    echo "✅ Kop config memiliki " . count($sekolah->kop_surat_config['elements']) . " elemen\n";
} else {
    echo "ℹ️  Kop config kosong (belum ada elemen)\n";
}

echo "\n=== Test Complete ===\n";
