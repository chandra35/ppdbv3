<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== UPDATE JALUR_PENDAFTARAN_ID DI CALON_SISWAS ===\n\n";

// Get calon siswa yang jalur_pendaftaran_id-nya null tapi punya gelombang
$calonSiswas = DB::table('calon_siswas')
    ->whereNull('jalur_pendaftaran_id')
    ->whereNotNull('gelombang_pendaftaran_id')
    ->get();

echo "Ditemukan {$calonSiswas->count()} calon siswa yang perlu diupdate\n\n";

$updated = 0;
foreach ($calonSiswas as $calon) {
    // Get jalur from gelombang
    $gelombang = DB::table('gelombang_pendaftaran')
        ->where('id', $calon->gelombang_pendaftaran_id)
        ->first();
    
    if ($gelombang && $gelombang->jalur_id) {
        DB::table('calon_siswas')
            ->where('id', $calon->id)
            ->update(['jalur_pendaftaran_id' => $gelombang->jalur_id]);
        
        $updated++;
        
        echo "✓ Updated: {$calon->nama_lengkap}\n";
        echo "  Jalur ID: {$gelombang->jalur_id}\n";
        echo "  Dari Gelombang: {$gelombang->nama}\n\n";
    } else {
        echo "✗ Skip: {$calon->nama_lengkap} (gelombang tidak punya jalur)\n\n";
    }
}

echo "\n=== VERIFIKASI ===\n\n";

$total = DB::table('calon_siswas')->count();
$with_jalur = DB::table('calon_siswas')->whereNotNull('jalur_pendaftaran_id')->count();

echo "Total calon siswa: {$total}\n";
echo "Yang punya jalur: {$with_jalur}\n";
echo "Yang berhasil diupdate: {$updated}\n";
