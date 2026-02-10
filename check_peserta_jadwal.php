<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\JadwalUjian;
use App\Models\JadwalPeserta;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;

$jadwalId = '019c483e-5110-7135-be8c-15e7ec54ed52';
$jadwal = JadwalUjian::find($jadwalId);

echo "=== Jadwal Ujian ===\n";
echo "Total peserta (setting): {$jadwal->total_peserta}\n";
echo "Jadwal peserta count: " . JadwalPeserta::where('jadwal_ujian_id', $jadwalId)->count() . "\n\n";

echo "=== Sesi Ujian ===\n";
$sesis = SesiUjian::where('jadwal_ujian_id', $jadwalId)->get();
foreach ($sesis as $sesi) {
    echo "\n{$sesi->nama} (sesi #{$sesi->nomor_sesi}):\n";
    $ruangs = RuangUjian::where('sesi_ujian_id', $sesi->id)->get();
    foreach ($ruangs as $ruang) {
        $pesertaCount = PesertaRuang::where('ruang_ujian_id', $ruang->id)->count();
        echo "  {$ruang->nama_ruang}: kapasitas={$ruang->kapasitas}, jumlah_peserta_field={$ruang->jumlah_peserta}, actual_peserta={$pesertaCount}\n";
    }
}

echo "\n=== Total peserta per jenis ===\n";
$cbtSesi = $sesis->where('jenis_ujian', 'cbt');
$wwcSesi = $sesis->where('jenis_ujian', 'wawancara');

$totalCbt = 0;
$totalWwc = 0;
foreach ($cbtSesi as $s) {
    foreach (RuangUjian::where('sesi_ujian_id', $s->id)->get() as $r) {
        $totalCbt += PesertaRuang::where('ruang_ujian_id', $r->id)->count();
    }
}
foreach ($wwcSesi as $s) {
    foreach (RuangUjian::where('sesi_ujian_id', $s->id)->get() as $r) {
        $totalWwc += PesertaRuang::where('ruang_ujian_id', $r->id)->count();
    }
}
echo "Total di ruang CBT: {$totalCbt}\n";
echo "Total di ruang Wawancara: {$totalWwc}\n";
