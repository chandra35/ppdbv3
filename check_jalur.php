<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\JalurPendaftaran;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;

echo "=== CEK JALUR PENDAFTARAN ===\n\n";

// Tahun Pelajaran Aktif
$tpAktif = TahunPelajaran::where('is_active', true)->first();
echo "Tahun Pelajaran Aktif: " . ($tpAktif ? $tpAktif->nama : 'TIDAK ADA') . "\n\n";

// Jalur Pendaftaran
echo "JALUR PENDAFTARAN:\n";
echo str_repeat('-', 100) . "\n";
$jalurs = JalurPendaftaran::with('tahunPelajaran')->orderBy('created_at', 'desc')->get();
foreach ($jalurs as $j) {
    $tp = $j->tahunPelajaran ? $j->tahunPelajaran->nama : 'N/A';
    $tpAktifMark = ($j->tahunPelajaran && $j->tahunPelajaran->is_active) ? '✅' : '❌';
    $pendaftarCount = CalonSiswa::where('jalur_pendaftaran_id', $j->id)->count();
    echo "   {$j->id} | {$j->nama} | TP: {$tp} {$tpAktifMark} | Pendaftar: {$pendaftarCount}\n";
}

// Pendaftar tanpa jalur
echo "\n\nPENDAFTAR TANPA JALUR:\n";
echo str_repeat('-', 100) . "\n";
$tanpaJalur = CalonSiswa::whereNull('jalur_pendaftaran_id')->get();
if ($tanpaJalur->isEmpty()) {
    echo "   (Tidak ada)\n";
} else {
    foreach ($tanpaJalur as $p) {
        echo "   {$p->nama_lengkap} | NISN: {$p->nisn} | jalur_id: NULL\n";
    }
}

// Pendaftar dengan jalur dari tahun lama
echo "\n\nPENDAFTAR DENGAN JALUR DARI TAHUN LAMA:\n";
echo str_repeat('-', 100) . "\n";
if ($tpAktif) {
    $pendaftarTahunLama = CalonSiswa::whereHas('jalurPendaftaran', function($q) use ($tpAktif) {
        $q->where('tahun_pelajaran_id', '!=', $tpAktif->id);
    })->get();
    
    if ($pendaftarTahunLama->isEmpty()) {
        echo "   (Tidak ada)\n";
    } else {
        foreach ($pendaftarTahunLama as $p) {
            $jalur = $p->jalurPendaftaran;
            $tp = $jalur && $jalur->tahunPelajaran ? $jalur->tahunPelajaran->nama : 'N/A';
            echo "   {$p->nama_lengkap} | Jalur: " . ($jalur ? $jalur->nama : 'N/A') . " | TP: {$tp}\n";
        }
    }
}

echo "\n=== SELESAI ===\n";
