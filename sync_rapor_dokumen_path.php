<?php
/**
 * Sinkronisasi dokumen_path dari calon_dokumens ke nilai_rapor
 * 
 * Masalah: Field dokumen_path di tabel nilai_rapor NULL padahal
 *          dokumen sudah ada di tabel calon_dokumens
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\NilaiRapor;
use App\Models\CalonDokumen;

echo "=== Sync Dokumen Path Rapor ===\n\n";

// Get all calon siswa yang punya nilai rapor
$calonSiswas = CalonSiswa::has('nilaiRapor')->with(['nilaiRapor', 'dokumen'])->get();

$totalFixed = 0;

foreach ($calonSiswas as $cs) {
    echo "Checking: {$cs->nama_lengkap} (NISN: {$cs->nisn})\n";
    
    $fixed = 0;
    
    foreach ($cs->nilaiRapor as $nr) {
        // Skip if already has dokumen_path
        if ($nr->dokumen_path) {
            continue;
        }
        
        // Find matching dokumen from calon_dokumens
        $jenisDokumen = 'rapor_sem_' . $nr->semester;
        $dokumen = $cs->dokumen->where('jenis_dokumen', $jenisDokumen)->first();
        
        if ($dokumen && $dokumen->file_path) {
            $nr->update([
                'dokumen_path' => $dokumen->file_path,
                'status_validasi' => 'pending',
            ]);
            $fixed++;
            echo "  - Semester {$nr->semester}: FIXED -> {$dokumen->file_path}\n";
        }
    }
    
    if ($fixed > 0) {
        $totalFixed += $fixed;
        echo "  Total fixed for this student: {$fixed}\n";
    } else {
        echo "  No fix needed\n";
    }
    echo "\n";
}

echo "=== DONE ===\n";
echo "Total dokumen_path fixed: {$totalFixed}\n";
