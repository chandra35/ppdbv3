<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Check status_admisi Column ===\n\n";

$result = DB::select("SHOW COLUMNS FROM calon_siswas WHERE Field = 'status_admisi'");

if (!empty($result)) {
    $column = $result[0];
    echo "Column: {$column->Field}\n";
    echo "Type: {$column->Type}\n";
    echo "Null: {$column->Null}\n";
    echo "Default: {$column->Default}\n";
} else {
    echo "Column not found\n";
}

echo "\n=== Check current values ===\n\n";

$values = DB::table('calon_siswas')
    ->select('status_admisi')
    ->distinct()
    ->get();

echo "Current distinct values in database:\n";
foreach ($values as $val) {
    echo "  - " . ($val->status_admisi ?? 'NULL') . "\n";
}
