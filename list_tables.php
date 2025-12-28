<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DAFTAR TABEL ===\n\n";

$tables = DB::select('SHOW TABLES');

foreach ($tables as $t) {
    $table = array_values((array)$t)[0];
    if (strpos($table, 'gelombang') !== false || strpos($table, 'jalur') !== false) {
        echo $table . "\n";
    }
}
