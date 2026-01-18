<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;

echo "=== CEK KONSISTENSI DATA PENDAFTAR ===" . PHP_EOL . PHP_EOL;

$tpAktif = TahunPelajaran::where('is_active', true)->first();
echo "Tahun Pelajaran Aktif: " . ($tpAktif?->nama ?? 'TIDAK ADA') . PHP_EOL . PHP_EOL;

$pendaftar = CalonSiswa::with([
    'jalurPendaftaran.tahunPelajaran', 
    'gelombangPendaftaran.jalur.tahunPelajaran',
    'tahunPelajaran'
])
->whereNotNull('nomor_registrasi')
->where('nomor_registrasi', 'like', '%/%')
->get();

echo "Ditemukan: " . $pendaftar->count() . " pendaftar dengan format lama" . PHP_EOL . PHP_EOL;

echo str_repeat('=', 120) . PHP_EOL;
printf("%-35s %-20s %-20s %-20s %-20s\n", 
    "Nama", 
    "TP Pendaftar", 
    "TP Jalur", 
    "TP Gelombang",
    "Status"
);
echo str_repeat('=', 120) . PHP_EOL;

$inconsistent = [];

foreach ($pendaftar as $p) {
    $tpPendaftar = $p->tahunPelajaran?->nama ?? '-';
    $tpJalur = $p->jalurPendaftaran?->tahunPelajaran?->nama ?? '-';
    $tpGelombang = $p->gelombangPendaftaran?->jalur?->tahunPelajaran?->nama ?? '-';
    
    // Cek konsistensi
    $isConsistent = ($tpPendaftar === $tpJalur && $tpPendaftar === $tpGelombang);
    $status = $isConsistent ? '✅ OK' : '❌ MISMATCH';
    
    if (!$isConsistent) {
        $inconsistent[] = $p;
    }
    
    printf("%-35s %-20s %-20s %-20s %-20s\n",
        substr($p->nama_lengkap, 0, 33),
        $tpPendaftar,
        $tpJalur,
        $tpGelombang,
        $status
    );
}

echo str_repeat('=', 120) . PHP_EOL . PHP_EOL;

echo "RINGKASAN:" . PHP_EOL;
echo "  Total: " . $pendaftar->count() . PHP_EOL;
echo "  Konsisten: " . ($pendaftar->count() - count($inconsistent)) . PHP_EOL;
echo "  Tidak Konsisten: " . count($inconsistent) . PHP_EOL;

if (count($inconsistent) > 0) {
    echo PHP_EOL . "⚠️  Ada " . count($inconsistent) . " pendaftar yang perlu difix gelombangnya!" . PHP_EOL;
}
