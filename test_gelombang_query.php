<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TahunPelajaran;
use App\Models\GelombangPendaftaran;

echo "=== TEST QUERY GELOMBANG YANG SUDAH DIPERBAIKI ===" . PHP_EOL . PHP_EOL;

$tahunAktif = TahunPelajaran::where('is_active', true)->first();
echo "1. TAHUN PELAJARAN AKTIF:" . PHP_EOL;
echo "   " . ($tahunAktif ? $tahunAktif->tahun . " (ID: {$tahunAktif->id})" : 'Tidak ada') . PHP_EOL . PHP_EOL;

// Query lama (tanpa filter TP)
$gelombangLama = GelombangPendaftaran::where('is_active', true)
    ->where('tanggal_buka', '<=', now())
    ->where('tanggal_tutup', '>=', now())
    ->first();

echo "2. QUERY LAMA (tanpa filter TP):" . PHP_EOL;
if ($gelombangLama) {
    echo "   Gelombang: {$gelombangLama->nama}" . PHP_EOL;
    echo "   Jalur: " . $gelombangLama->jalur?->nama . PHP_EOL;
    echo "   TP Jalur: " . $gelombangLama->jalur?->tahunPelajaran?->tahun . PHP_EOL;
} else {
    echo "   Tidak ada gelombang aktif" . PHP_EOL;
}
echo PHP_EOL;

// Query baru (dengan filter TP)
$gelombangBaru = GelombangPendaftaran::where('is_active', true)
    ->where('tanggal_buka', '<=', now())
    ->where('tanggal_tutup', '>=', now())
    ->whereHas('jalur', function ($query) use ($tahunAktif) {
        $query->where('tahun_pelajaran_id', $tahunAktif?->id);
    })
    ->first();

echo "3. QUERY BARU (dengan filter TP aktif):" . PHP_EOL;
if ($gelombangBaru) {
    echo "   Gelombang: {$gelombangBaru->nama}" . PHP_EOL;
    echo "   Jalur: " . $gelombangBaru->jalur?->nama . PHP_EOL;
    echo "   TP Jalur: " . $gelombangBaru->jalur?->tahunPelajaran?->tahun . PHP_EOL;
    echo "   ✅ KONSISTEN dengan TP aktif!" . PHP_EOL;
} else {
    echo "   ⚠️ Tidak ada gelombang aktif untuk TP {$tahunAktif->tahun}" . PHP_EOL;
}
echo PHP_EOL;

echo "=== KESIMPULAN ===" . PHP_EOL;
if ($gelombangLama && $gelombangBaru) {
    if ($gelombangLama->id === $gelombangBaru->id) {
        echo "✅ Keduanya mengembalikan gelombang yang sama - AMAN" . PHP_EOL;
    } else {
        echo "⚠️ BERBEDA! Query lama: {$gelombangLama->nama}, Query baru: {$gelombangBaru->nama}" . PHP_EOL;
        echo "   Perbaikan mencegah pendaftar terdaftar ke TP yang salah!" . PHP_EOL;
    }
} elseif (!$gelombangBaru) {
    echo "⚠️ Tidak ada gelombang terbuka untuk TP aktif {$tahunAktif->tahun}" . PHP_EOL;
    echo "   Pendaftar tidak akan bisa mendaftar sampai gelombang dibuka!" . PHP_EOL;
}

echo PHP_EOL . "=== SELESAI ===" . PHP_EOL;
