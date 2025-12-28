<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK GELOMBANG PENDAFTARAN ===\n\n";

$gelombangs = DB::table('gelombang_pendaftaran')->get();

foreach ($gelombangs as $g) {
    echo "ID: {$g->id}\n";
    echo "Nama: {$g->nama_gelombang}\n";
    echo "Jalur ID: " . ($g->jalur_pendaftaran_id ?? 'NULL') . "\n";
    echo "---\n\n";
}

echo "\n=== CEK JALUR PENDAFTARAN ===\n\n";

$jalurs = DB::table('jalur_pendaftaran')->get();

foreach ($jalurs as $j) {
    echo "ID: {$j->id}\n";
    echo "Nama: " . ($j->nama_jalur ?? 'NULL') . "\n";
    echo "Kode: " . ($j->kode_jalur ?? 'NULL') . "\n";
    echo "Pilihan Program Aktif: " . ($j->pilihan_program_aktif ? 'YA' : 'TIDAK') . "\n";
    echo "---\n\n";
}
