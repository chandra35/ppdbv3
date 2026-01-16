<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$calonSiswaId = '019bc062-1545-713a-89ce-013580605f08';

echo "=== Cek Data Nilai Rapor ===\n";
echo "Calon Siswa ID: $calonSiswaId\n\n";

// Cek nilai_rapor
$nilaiRapor = \App\Models\NilaiRapor::where('calon_siswa_id', $calonSiswaId)->get();
echo "Nilai Rapor (" . $nilaiRapor->count() . " records):\n";
foreach ($nilaiRapor as $nr) {
    echo "  Semester {$nr->semester}: ";
    echo "Mat={$nr->matematika}, IPA={$nr->ipa}, IPS={$nr->ips}, ";
    echo "Dok=" . ($nr->dokumen_path ?: 'null') . ", ";
    echo "Status={$nr->status_validasi}\n";
}

echo "\n=== Cek Dokumen Rapor di calon_dokumens ===\n";
$dokRapor = \App\Models\CalonDokumen::where('calon_siswa_id', $calonSiswaId)
    ->where('jenis_dokumen', 'like', 'rapor_sem_%')
    ->get();
echo "Dokumen Rapor (" . $dokRapor->count() . " records):\n";
foreach ($dokRapor as $dok) {
    echo "  {$dok->jenis_dokumen}: {$dok->file_path} (status: {$dok->status_verifikasi})\n";
}
