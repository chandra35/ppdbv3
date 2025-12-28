<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK STRUKTUR GELOMBANG ===\n\n";

$columns = DB::select("SHOW COLUMNS FROM gelombang_pendaftaran");

foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

echo "\n=== CEK DATA GELOMBANG ===\n\n";

$gelombangs = DB::table('gelombang_pendaftaran')->get();

foreach ($gelombangs as $g) {
    echo json_encode($g, JSON_PRETTY_PRINT) . "\n\n";
}
