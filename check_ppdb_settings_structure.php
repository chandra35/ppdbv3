<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Structure ppdb_settings table ===\n\n";

$columns = DB::select("SHOW COLUMNS FROM ppdb_settings");

foreach ($columns as $col) {
    echo "Field: {$col->Field}\n";
    echo "Type: {$col->Type}\n";
    echo "---\n";
}
