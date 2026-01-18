<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Data Count Before Reset ===\n";
echo "Total pendaftar (with trashed): " . \App\Models\CalonSiswa::withTrashed()->count() . "\n";
echo "Total pendaftar aktif: " . \App\Models\CalonSiswa::count() . "\n";
echo "Total users pendaftar: " . \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'pendaftar'))->count() . "\n";
echo "Total dokumen: " . \App\Models\CalonDokumen::withTrashed()->count() . "\n";
echo "Total nilai rapor: " . \App\Models\NilaiRapor::count() . "\n";
echo "Total ortu: " . \App\Models\CalonOrtu::count() . "\n";
