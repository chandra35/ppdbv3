<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use Illuminate\Support\Facades\DB;

// Find orphaned sesi (no jadwal_ujian_id)
$orphanedSesi = SesiUjian::whereNull('jadwal_ujian_id')->get();

if ($orphanedSesi->isEmpty()) {
    echo "No orphaned sesi found.\n";
    exit;
}

echo "Found " . count($orphanedSesi) . " orphaned sesi:\n";
foreach ($orphanedSesi as $s) {
    echo "  - {$s->id} | {$s->nama} | status:{$s->status}\n";
}

echo "\nDeleting orphaned sesi and related data...\n";

DB::beginTransaction();
try {
    foreach ($orphanedSesi as $sesi) {
        // Delete peserta_ruang
        $deleted = PesertaRuang::where('sesi_ujian_id', $sesi->id)->delete();
        echo "  Deleted {$deleted} peserta_ruang\n";
        
        // Delete ruang_ujian
        $deleted = RuangUjian::where('sesi_ujian_id', $sesi->id)->delete();
        echo "  Deleted {$deleted} ruang_ujian\n";
        
        // Delete sesi
        $sesi->delete();
        echo "  Deleted sesi: {$sesi->nama}\n";
    }
    
    DB::commit();
    echo "\nDone! Orphaned sesi cleaned up.\n";
    
    // Verify
    echo "\nRemaining sesi:\n";
    foreach (SesiUjian::all() as $s) {
        echo "  {$s->id} | {$s->nama} | status:{$s->status} | jadwal:{$s->jadwal_ujian_id}\n";
    }
} catch (Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
