<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\EmisNisnService;

$nisn = '0083113918';

echo "=== Testing EMIS API ===\n";
echo "NISN: $nisn\n";
echo "========================\n\n";

$service = new EmisNisnService();
$result = $service->cekNisn($nisn);

echo "Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
