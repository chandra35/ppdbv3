<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JadwalUjian;
use App\Models\JadwalPeserta;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\CalonSiswa;

$jadwalId = '019c57cd-979f-71c3-9f4d-3d56998fa544';

$j = JadwalUjian::find($jadwalId);
if (!$j) { echo "Jadwal not found\n"; exit; }

echo "=== JADWAL INFO ===\n";
echo "Tanggal: {$j->tanggal_ujian}\n";
echo "Mode: {$j->mode}\n";
echo "Total Peserta (stored): {$j->total_peserta}\n";
echo "Total Sesi (stored): {$j->total_sesi}\n";
echo "Kap CBT: {$j->jumlah_ruang_cbt} x {$j->kapasitas_cbt} = " . ($j->jumlah_ruang_cbt * $j->kapasitas_cbt) . "\n";
echo "Kap Waw: {$j->jumlah_ruang_wawancara} x {$j->kapasitas_wawancara} = " . ($j->jumlah_ruang_wawancara * $j->kapasitas_wawancara) . "\n";

// Actual peserta scheduled
$jpCount = JadwalPeserta::where('jadwal_ujian_id', $jadwalId)->count();
echo "\n=== PESERTA ===\n";
echo "Jadwal Peserta (DB): {$jpCount}\n";

// Total eligible peserta
$tahunId = $j->tahun_pelajaran_id;
$eligible = CalonSiswa::where('is_finalisasi', true)
    ->whereNotNull('nomor_tes')
    ->where('nomor_tes', '!=', '')
    ->where('tahun_pelajaran_id', $tahunId)
    ->count();
echo "Eligible (finalisasi + nomor tes): {$eligible}\n";

// Check by jalur/gelombang filter
if ($j->jalur_pendaftaran_id) {
    $filtered = CalonSiswa::where('is_finalisasi', true)
        ->whereNotNull('nomor_tes')
        ->where('nomor_tes', '!=', '')
        ->where('tahun_pelajaran_id', $tahunId)
        ->where('jalur_pendaftaran_id', $j->jalur_pendaftaran_id)
        ->count();
    echo "Filtered by jalur: {$filtered}\n";
}
if ($j->gelombang_pendaftaran_id) {
    $filtered = CalonSiswa::where('is_finalisasi', true)
        ->whereNotNull('nomor_tes')
        ->where('nomor_tes', '!=', '')
        ->where('tahun_pelajaran_id', $tahunId)
        ->where('gelombang_pendaftaran_id', $j->gelombang_pendaftaran_id)
        ->count();
    echo "Filtered by gelombang: {$filtered}\n";
}

// Missing peserta?
$scheduledIds = JadwalPeserta::where('jadwal_ujian_id', $jadwalId)->pluck('calon_siswa_id');
$missing = CalonSiswa::where('is_finalisasi', true)
    ->whereNotNull('nomor_tes')
    ->where('nomor_tes', '!=', '')
    ->where('tahun_pelajaran_id', $tahunId)
    ->when($j->jalur_pendaftaran_id, fn($q) => $q->where('jalur_pendaftaran_id', $j->jalur_pendaftaran_id))
    ->when($j->gelombang_pendaftaran_id, fn($q) => $q->where('gelombang_pendaftaran_id', $j->gelombang_pendaftaran_id))
    ->whereNotIn('id', $scheduledIds)
    ->count();
echo "Missing (not scheduled): {$missing}\n";

// Sesi breakdown
$sesiList = SesiUjian::where('jadwal_ujian_id', $jadwalId)->orderBy('nomor_sesi')->orderBy('jenis_ujian')->get();
echo "\n=== SESI BREAKDOWN ===\n";
foreach ($sesiList as $s) {
    $ruangCount = RuangUjian::where('sesi_ujian_id', $s->id)->count();
    $pesertaCount = PesertaRuang::where('sesi_ujian_id', $s->id)->count();
    echo "Sesi {$s->nomor_sesi} {$s->jenis_ujian}: {$pesertaCount} peserta, {$ruangCount} ruang ({$s->waktu_mulai} - {$s->waktu_selesai})\n";
}

// Room detail for last sesi
echo "\n=== RUANG DETAIL (all sesi) ===\n";
foreach ($sesiList as $s) {
    $rooms = RuangUjian::where('sesi_ujian_id', $s->id)->orderBy('nomor_ruang')->get();
    foreach ($rooms as $r) {
        echo "Sesi {$s->nomor_sesi} {$s->jenis_ujian} - {$r->nama_ruang}: {$r->jumlah_peserta}/{$r->kapasitas}\n";
    }
}
