<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK STRUKTUR CALON_SISWAS ===\n\n";

$columns = DB::select("SHOW COLUMNS FROM calon_siswas WHERE Field LIKE '%jalur%' OR Field LIKE '%gelombang%'");

foreach ($columns as $col) {
    echo "Field: {$col->Field}\n";
    echo "Type: {$col->Type}\n";
    echo "Null: {$col->Null}\n";
    echo "Default: {$col->Default}\n";
    echo "---\n\n";
}

echo "\n=== CEK DATA CALON SISWA (dengan jalur) ===\n\n";

$data = DB::table('calon_siswas')
    ->select('id', 'nama_lengkap', 'jalur_pendaftaran_id', 'gelombang_pendaftaran_id')
    ->limit(3)
    ->get();

foreach ($data as $row) {
    echo "Nama: {$row->nama_lengkap}\n";
    echo "Jalur ID: {$row->jalur_pendaftaran_id}\n";
    echo "Gelombang ID: {$row->gelombang_pendaftaran_id}\n";
    echo "---\n\n";
}
