<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;

echo "=== UPDATE JALUR_PENDAFTARAN_ID DARI GELOMBANG ===\n\n";

// Get calon siswa yang jalur_pendaftaran_id-nya null tapi punya gelombang
$calonSiswas = CalonSiswa::whereNull('jalur_pendaftaran_id')
    ->whereNotNull('gelombang_pendaftaran_id')
    ->get();

echo "Ditemukan {$calonSiswas->count()} calon siswa yang perlu diupdate\n\n";

foreach ($calonSiswas as $calon) {
    // Get jalur from gelombang
    $gelombang = GelombangPendaftaran::find($calon->gelombang_pendaftaran_id);
    
    if ($gelombang && $gelombang->jalur_pendaftaran_id) {
        $calon->jalur_pendaftaran_id = $gelombang->jalur_pendaftaran_id;
        $calon->save();
        
        echo "✓ Updated: {$calon->nama_lengkap}\n";
        echo "  Jalur ID: {$gelombang->jalur_pendaftaran_id}\n";
        echo "  Dari Gelombang: {$gelombang->nama_gelombang}\n\n";
    } else {
        echo "✗ Skip: {$calon->nama_lengkap} (gelombang tidak punya jalur)\n\n";
    }
}

echo "\n=== VERIFIKASI ===\n\n";

$updated = CalonSiswa::whereNotNull('jalur_pendaftaran_id')->count();
$total = CalonSiswa::count();

echo "Total calon siswa: {$total}\n";
echo "Yang punya jalur: {$updated}\n";
echo "Yang belum: " . ($total - $updated) . "\n";
