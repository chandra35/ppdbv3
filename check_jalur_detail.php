<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK JALUR PENDAFTARAN ===\n\n";

$jalurs = DB::table('jalur_pendaftaran')->get();

foreach ($jalurs as $jalur) {
    echo json_encode($jalur, JSON_PRETTY_PRINT) . "\n\n";
}
