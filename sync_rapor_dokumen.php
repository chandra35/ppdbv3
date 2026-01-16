<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Sync Dokumen Rapor ke nilai_rapor ===\n\n";

$docs = \App\Models\CalonDokumen::where('jenis_dokumen', 'like', 'rapor_sem_%')->get();

foreach ($docs as $doc) {
    preg_match('/rapor_sem_(\d)/', $doc->jenis_dokumen, $matches);
    if ($matches) {
        $semester = $matches[1];
        
        $updated = \App\Models\NilaiRapor::where('calon_siswa_id', $doc->calon_siswa_id)
            ->where('semester', $semester)
            ->update(['dokumen_path' => $doc->file_path]);
        
        if ($updated) {
            echo "✓ Updated semester $semester for {$doc->calon_siswa_id}\n";
        } else {
            echo "✗ No record found for semester $semester, {$doc->calon_siswa_id}\n";
        }
    }
}

echo "\nDone!\n";
