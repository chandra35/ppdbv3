<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CalonSiswa;

// Generate verification hash for finalized students
$siswaList = CalonSiswa::where('is_finalisasi', true)
    ->whereNull('verification_hash')
    ->get();

$count = 0;
foreach ($siswaList as $siswa) {
    $hash = $siswa->generateVerificationHash();
    $siswa->update(['verification_hash' => $hash]);
    $count++;
}

echo "Generated $count verification hashes\n";
