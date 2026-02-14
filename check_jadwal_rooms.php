<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jadwalId = '019c57fd-5fbb-724a-9ca1-5b1cbaf1b2da';

echo "=== JADWAL INFO ===\n";
$jadwal = DB::table('jadwal_ujian')->where('id', $jadwalId)->first();
echo "Mode: {$jadwal->mode}\n";
echo "Total Sesi: {$jadwal->total_sesi}\n";
echo "Ruang CBT: {$jadwal->jumlah_ruang_cbt} x {$jadwal->kapasitas_cbt}\n";
echo "Ruang Waw: {$jadwal->jumlah_ruang_wawancara} x {$jadwal->kapasitas_wawancara}\n";

echo "\n=== SESI & RUANG ===\n";
$sesiList = DB::table('sesi_ujian')->where('jadwal_ujian_id', $jadwalId)->orderBy('nomor_sesi')->orderBy('jenis_ujian')->get();

foreach ($sesiList as $sesi) {
    echo "\nSesi {$sesi->nomor_sesi} - {$sesi->jenis_ujian} (ID: " . substr($sesi->id, 0, 8) . ")\n";
    
    $ruangList = DB::table('ruang_ujian')->where('sesi_ujian_id', $sesi->id)->orderBy('nomor_ruang')->get();
    foreach ($ruangList as $ruang) {
        // Get actual peserta in this room with nomor_tes
        $peserta = DB::table('peserta_ruang')
            ->join('calon_siswas', 'peserta_ruang.calon_siswa_id', '=', 'calon_siswas.id')
            ->where('peserta_ruang.ruang_ujian_id', $ruang->id)
            ->orderBy('peserta_ruang.nomor_urut')
            ->select('calon_siswas.nomor_tes', 'peserta_ruang.nomor_urut')
            ->get();
        
        $first = $peserta->first();
        $last = $peserta->last();
        $firstTes = $first ? $first->nomor_tes : '-';
        $lastTes = $last ? $last->nomor_tes : '-';
        
        echo "  {$ruang->nama_ruang}: kap={$ruang->kapasitas}, actual={$ruang->jumlah_peserta}, range={$firstTes} s/d {$lastTes}\n";
    }
}

// Also show total peserta per jadwal
$totalJP = DB::table('jadwal_peserta')->where('jadwal_ujian_id', $jadwalId)->count();
echo "\n=== TOTAL ===\n";
echo "Total jadwal_peserta: {$totalJP}\n";

// Show distribution by grup
$grups = DB::table('jadwal_peserta')->where('jadwal_ujian_id', $jadwalId)
    ->select('grup', DB::raw('count(*) as cnt'))
    ->groupBy('grup')
    ->get();
foreach ($grups as $g) {
    echo "Grup {$g->grup}: {$g->cnt}\n";
}
