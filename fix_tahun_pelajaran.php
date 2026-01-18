<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX TAHUN PELAJARAN ===\n\n";

// Cek status saat ini
echo "Status sebelum:\n";
$all = DB::table('tahun_pelajarans')->get();
foreach ($all as $tp) {
    echo "   {$tp->nama} | is_active: " . ($tp->is_active ? 'true' : 'false') . "\n";
}

// Aktifkan 2026/2027
DB::table('tahun_pelajarans')->update(['is_active' => false]); // reset semua
DB::table('tahun_pelajarans')->where('nama', '2026/2027')->update(['is_active' => true]);

echo "\nStatus sesudah:\n";
$all = DB::table('tahun_pelajarans')->get();
foreach ($all as $tp) {
    $status = $tp->is_active ? '✅ AKTIF' : '❌';
    echo "   {$tp->nama} | {$status}\n";
}

echo "\n=== SELESAI ===\n";
