<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TahunPelajaran;
use App\Models\CalonSiswa;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use Illuminate\Support\Facades\Schema;

echo "=== CEK DATA PENDAFTAR ===\n\n";

// 1. Cek Tahun Pelajaran
echo "1. TAHUN PELAJARAN:\n";
echo str_repeat('-', 80) . "\n";
$tahunPelajarans = TahunPelajaran::orderBy('created_at', 'desc')->get();
foreach ($tahunPelajarans as $tp) {
    $aktif = $tp->is_active ? '✅ AKTIF' : '❌';
    echo "   {$tp->id} | {$tp->nama} | {$aktif}\n";
}

// 2. Cek Pendaftar per Tahun Pelajaran
echo "\n2. PENDAFTAR PER TAHUN PELAJARAN:\n";
echo str_repeat('-', 80) . "\n";
foreach ($tahunPelajarans as $tp) {
    $count = CalonSiswa::where('tahun_pelajaran_id', $tp->id)->count();
    $countDeleted = CalonSiswa::where('tahun_pelajaran_id', $tp->id)->onlyTrashed()->count();
    echo "   {$tp->nama}: {$count} pendaftar aktif, {$countDeleted} dihapus (soft delete)\n";
}

// 3. Total Pendaftar
echo "\n3. TOTAL PENDAFTAR:\n";
echo str_repeat('-', 80) . "\n";
$totalAktif = CalonSiswa::count();
$totalDeleted = CalonSiswa::onlyTrashed()->count();
$totalAll = CalonSiswa::withTrashed()->count();
echo "   Aktif: {$totalAktif}\n";
echo "   Dihapus (soft delete): {$totalDeleted}\n";
echo "   Total: {$totalAll}\n";

// 4. Cek tahun pelajaran aktif
echo "\n4. TAHUN PELAJARAN AKTIF SAAT INI:\n";
echo str_repeat('-', 80) . "\n";
$tpAktif = TahunPelajaran::where('is_active', true)->first();
if ($tpAktif) {
    echo "   ✅ {$tpAktif->nama} ({$tpAktif->id})\n";
    $countAktif = CalonSiswa::where('tahun_pelajaran_id', $tpAktif->id)->count();
    echo "   Pendaftar di tahun ini: {$countAktif}\n";
} else {
    echo "   ❌ TIDAK ADA TAHUN PELAJARAN AKTIF!\n";
}

// 5. Sample pendaftar terakhir
echo "\n5. 5 PENDAFTAR TERAKHIR (SEMUA TAHUN):\n";
echo str_repeat('-', 80) . "\n";
$latestPendaftar = CalonSiswa::withTrashed()
    ->with('tahunPelajaran')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
foreach ($latestPendaftar as $p) {
    $deleted = $p->deleted_at ? ' [DELETED]' : '';
    $tp = $p->tahunPelajaran ? $p->tahunPelajaran->nama : 'N/A';
    echo "   {$p->nama_lengkap} | NISN: {$p->nisn} | TP: {$tp} | {$p->created_at}{$deleted}\n";
}

// 6. Cek Jalur & Gelombang
echo "\n6. JALUR & GELOMBANG AKTIF:\n";
echo str_repeat('-', 80) . "\n";
if ($tpAktif) {
    $jalurs = JalurPendaftaran::where('tahun_pelajaran_id', $tpAktif->id)->get();
    foreach ($jalurs as $jalur) {
        $gelombangCount = GelombangPendaftaran::where('jalur_pendaftaran_id', $jalur->id)->count();
        echo "   Jalur: {$jalur->nama} | Gelombang: {$gelombangCount}\n";
    }
}

echo "\n=== SELESAI ===\n";
