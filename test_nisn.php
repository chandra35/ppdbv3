<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(\App\Services\EmisNisnService::class);

// Test NISN 1
$nisn1 = '0105433296';
echo "=== TEST NISN: $nisn1 ===" . PHP_EOL;
$result1 = $service->cekNisn($nisn1);

if (isset($result1['data'])) {
    $kemdikbud1 = $result1['data']['kemdikbud'] ?? null;
    $kemenag1 = $result1['data']['kemenag'] ?? null;
    
    echo "Kemdikbud: " . ($kemdikbud1 ? 'ADA ✓' : 'TIDAK') . PHP_EOL;
    echo "Kemenag: " . ($kemenag1 ? 'ADA ✓' : 'TIDAK') . PHP_EOL;
    
    if ($kemdikbud1) {
        echo "\n  Kemdikbud Data:" . PHP_EOL;
        echo "  - Nama: " . ($kemdikbud1['nama'] ?? '-') . PHP_EOL;
        echo "  - NIK: " . ($kemdikbud1['nik'] ?? '-') . PHP_EOL;
        echo "  - NPSN: " . ($kemdikbud1['npsn'] ?? '-') . PHP_EOL;
        echo "  - Sekolah: " . ($kemdikbud1['sekolah'] ?? '-') . PHP_EOL;
        
        echo "\n  Available keys in Kemdikbud data:" . PHP_EOL;
        foreach (array_keys($kemdikbud1) as $key) {
            echo "    - $key: " . (is_string($kemdikbud1[$key]) ? $kemdikbud1[$key] : json_encode($kemdikbud1[$key])) . PHP_EOL;
        }
    }
    if ($kemenag1) {
        echo "  - Nama: " . ($kemenag1['full_name'] ?? '-') . PHP_EOL;
        echo "  - NIK: " . ($kemenag1['nik'] ?? '-') . PHP_EOL;
    }
}

echo PHP_EOL;

// Test NISN 2
$nisn2 = '3124284913';
echo "=== TEST NISN: $nisn2 ===" . PHP_EOL;
$result2 = $service->cekNisn($nisn2);

if (isset($result2['data'])) {
    $kemdikbud2 = $result2['data']['kemdikbud'] ?? null;
    $kemenag2 = $result2['data']['kemenag'] ?? null;
    
    echo "Kemdikbud: " . ($kemdikbud2 ? 'ADA ✓' : 'TIDAK') . PHP_EOL;
    echo "Kemenag: " . ($kemenag2 ? 'ADA ✓' : 'TIDAK') . PHP_EOL;
    
    if ($kemdikbud2) {
        echo "  - Nama: " . ($kemdikbud2['nama'] ?? '-') . PHP_EOL;
        echo "  - Sekolah: " . ($kemdikbud2['sekolah'] ?? '-') . PHP_EOL;
    }
    if ($kemenag2) {
        echo "  - Nama: " . ($kemenag2['full_name'] ?? '-') . PHP_EOL;
        echo "  - NIK: " . ($kemenag2['nik'] ?? '-') . PHP_EOL;
        echo "\n  Available keys in Kemenag data:" . PHP_EOL;
        foreach (array_keys($kemenag2) as $key) {
            echo "    - $key" . PHP_EOL;
        }
    }
}
