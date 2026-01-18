<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\JalurPendaftaran;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\DB;

echo "=== FIX JALUR PENDAFTAR ===\n\n";

// Tahun Pelajaran Aktif
$tpAktif = TahunPelajaran::where('is_active', true)->first();
echo "Tahun Pelajaran Aktif: " . ($tpAktif ? $tpAktif->nama : 'TIDAK ADA') . "\n\n";

if (!$tpAktif) {
    echo "ERROR: Tidak ada tahun pelajaran aktif!\n";
    exit(1);
}

// Jalur dari tahun aktif
$jalurAktif = JalurPendaftaran::where('tahun_pelajaran_id', $tpAktif->id)->first();
echo "Jalur di Tahun Aktif: " . ($jalurAktif ? $jalurAktif->nama : 'TIDAK ADA') . "\n\n";

if (!$jalurAktif) {
    echo "ERROR: Tidak ada jalur di tahun pelajaran aktif!\n";
    exit(1);
}

// Pendaftar dengan jalur dari tahun lama
echo "SEBELUM:\n";
echo str_repeat('-', 80) . "\n";
$pendaftarTahunLama = CalonSiswa::where('tahun_pelajaran_id', $tpAktif->id)
    ->whereHas('jalurPendaftaran', function($q) use ($tpAktif) {
        $q->where('tahun_pelajaran_id', '!=', $tpAktif->id);
    })->get();

foreach ($pendaftarTahunLama as $p) {
    $jalur = $p->jalurPendaftaran;
    $tp = $jalur && $jalur->tahunPelajaran ? $jalur->tahunPelajaran->nama : 'N/A';
    echo "   {$p->nama_lengkap} | Jalur: " . ($jalur ? $jalur->nama : 'N/A') . " | TP Jalur: {$tp}\n";
}

echo "\n\nMengupdate jalur_pendaftaran_id ke: {$jalurAktif->nama} ({$jalurAktif->id})...\n";

// Update
$updated = CalonSiswa::where('tahun_pelajaran_id', $tpAktif->id)
    ->whereHas('jalurPendaftaran', function($q) use ($tpAktif) {
        $q->where('tahun_pelajaran_id', '!=', $tpAktif->id);
    })
    ->update(['jalur_pendaftaran_id' => $jalurAktif->id]);

echo "Updated: {$updated} pendaftar\n\n";

// Verifikasi
echo "SESUDAH:\n";
echo str_repeat('-', 80) . "\n";
$pendaftarFixed = CalonSiswa::where('tahun_pelajaran_id', $tpAktif->id)->with('jalurPendaftaran')->get();
foreach ($pendaftarFixed as $p) {
    $jalur = $p->jalurPendaftaran;
    $tp = $jalur && $jalur->tahunPelajaran ? $jalur->tahunPelajaran->nama : 'N/A';
    echo "   {$p->nama_lengkap} | Jalur: " . ($jalur ? $jalur->nama : 'N/A') . " | TP Jalur: {$tp}\n";
}

echo "\n=== SELESAI ===\n";
