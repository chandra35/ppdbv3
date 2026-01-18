<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;

echo "=== CEK STATUS PENDAFTARAN ===\n\n";

// Tahun Aktif
$tahunAktif = TahunPelajaran::where('is_active', true)->first();
echo "1. TAHUN PELAJARAN AKTIF:\n";
echo "   " . ($tahunAktif ? "✅ {$tahunAktif->nama}" : "❌ TIDAK ADA") . "\n\n";

// Gelombang Aktif (is_active = true)
echo "2. GELOMBANG DENGAN is_active = true:\n";
$gelombangAktif = GelombangPendaftaran::with('jalur.tahunPelajaran')
    ->where('is_active', true)
    ->get();

if ($gelombangAktif->isEmpty()) {
    echo "   ❌ TIDAK ADA\n";
} else {
    foreach ($gelombangAktif as $gel) {
        $jalur = $gel->jalur;
        $tp = $jalur && $jalur->tahunPelajaran ? $jalur->tahunPelajaran->nama : 'N/A';
        $now = now();
        $isOpen = $gel->tanggal_buka <= $now && $gel->tanggal_tutup >= $now ? '✅ BUKA' : '❌ TUTUP';
        echo "   - {$gel->nama}\n";
        echo "     Jalur: " . ($jalur ? $jalur->nama : 'N/A') . "\n";
        echo "     TP: {$tp}\n";
        echo "     Periode: {$gel->tanggal_buka} s/d {$gel->tanggal_tutup}\n";
        echo "     Status: {$isOpen}\n\n";
    }
}

// Gelombang yang sedang buka (sesuai tanggal)
echo "3. GELOMBANG YANG SEDANG BUKA (sesuai tanggal):\n";
$now = now();
$gelombangBuka = GelombangPendaftaran::with('jalur.tahunPelajaran')
    ->where('is_active', true)
    ->where('tanggal_buka', '<=', $now)
    ->where('tanggal_tutup', '>=', $now)
    ->get();

if ($gelombangBuka->isEmpty()) {
    echo "   ❌ TIDAK ADA GELOMBANG YANG SEDANG BUKA\n";
    echo "   Tanggal sekarang: {$now}\n";
} else {
    foreach ($gelombangBuka as $gel) {
        $jalur = $gel->jalur;
        $tp = $jalur && $jalur->tahunPelajaran ? $jalur->tahunPelajaran->nama : 'N/A';
        echo "   ✅ {$gel->nama}\n";
        echo "     Jalur: " . ($jalur ? $jalur->nama : 'N/A') . " | TP: {$tp}\n";
    }
}

echo "\n=== SELESAI ===\n";
