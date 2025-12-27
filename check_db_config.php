<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Database Configuration:\n";
echo "Default: " . config('database.default') . "\n";
echo "MySQL DB: " . config('database.connections.mysql.database') . "\n";
echo "Simansa DB: " . config('database.connections.simansav3.database', 'NOT CONFIGURED') . "\n";
