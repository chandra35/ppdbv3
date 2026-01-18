<?php
/**
 * Script untuk memperbaiki nomor registrasi pendaftar yang sudah ada
 * 
 * Script ini akan:
 * 1. Mengambil semua pendaftar yang sudah punya nomor registrasi format lama
 * 2. Re-generate nomor registrasi dengan format baru dari gelombang
 * 
 * PERHATIAN: Jalankan script ini HANYA SEKALI setelah fix code di-deploy
 * 
 * Format lama: 2026/PRA/1/0001
 * Format baru: REG-PRA-2026-0001 (dari gelombang)
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;
use Illuminate\Support\Facades\DB;

echo "=== FIX NOMOR REGISTRASI PENDAFTAR ===" . PHP_EOL . PHP_EOL;

// Cari pendaftar dengan format lama (mengandung /)
$pendaftarLama = CalonSiswa::whereNotNull('nomor_registrasi')
    ->where('nomor_registrasi', 'like', '%/%')
    ->with(['gelombangPendaftaran.jalur.tahunPelajaran', 'jalurPendaftaran'])
    ->get();

echo "Ditemukan: " . $pendaftarLama->count() . " pendaftar dengan format nomor registrasi lama" . PHP_EOL . PHP_EOL;

if ($pendaftarLama->isEmpty()) {
    echo "✅ Tidak ada pendaftar yang perlu difix." . PHP_EOL;
    exit(0);
}

echo "Data yang akan diubah:" . PHP_EOL;
echo str_repeat('-', 100) . PHP_EOL;
printf("%-40s %-25s %-25s\n", "Nama", "Nomor Lama", "Nomor Baru (preview)");
echo str_repeat('-', 100) . PHP_EOL;

// Group by gelombang untuk preview yang benar
$groupedByGelombang = $pendaftarLama->groupBy('gelombang_pendaftaran_id');
$previewData = [];

foreach ($groupedByGelombang as $gelombangId => $pendaftars) {
    $gelombang = $gelombangId ? GelombangPendaftaran::with('jalur.tahunPelajaran')->find($gelombangId) : null;
    $counterPreview = $gelombang ? $gelombang->counter_nomor : 0;
    
    foreach ($pendaftars as $p) {
        if ($gelombang) {
            $counterPreview++;
            $counter = str_pad($counterPreview, 4, '0', STR_PAD_LEFT);
            // Ambil tahun dari nama TP (format: "2026/2027" -> ambil "2026")
            $tpNama = $gelombang->jalur?->tahunPelajaran?->nama ?? date('Y');
            $tahun = explode('/', $tpNama)[0];
            $kodeJalur = $gelombang->jalur ? strtoupper(substr($gelombang->jalur->kode, 0, 3)) : 'REG';
            $prefix = $gelombang->prefix_nomor ?: 'REG';
            $nomorBaru = "{$prefix}-{$kodeJalur}-{$tahun}-{$counter}";
        } else {
            $nomorBaru = "⚠️ SKIP (no gelombang)";
        }
        
        $previewData[$p->id] = [
            'pendaftar' => $p,
            'nomor_lama' => $p->nomor_registrasi,
            'nomor_baru_preview' => $nomorBaru,
        ];
        
        printf("%-40s %-25s %-25s\n", 
            substr($p->nama_lengkap, 0, 38), 
            $p->nomor_registrasi, 
            $nomorBaru
        );
    }
}

echo str_repeat('-', 80) . PHP_EOL . PHP_EOL;

// Konfirmasi
echo "⚠️  PERHATIAN: Proses ini akan mengubah nomor registrasi pendaftar!" . PHP_EOL;
echo "   Pastikan tidak ada proses registrasi yang sedang berjalan." . PHP_EOL . PHP_EOL;

echo "Lanjutkan? (ketik 'YES' untuk konfirmasi): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if ($input !== 'YES') {
    echo PHP_EOL . "❌ Dibatalkan oleh user." . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "Memproses..." . PHP_EOL . PHP_EOL;

// Proses update
$sukses = 0;
$gagal = 0;

DB::beginTransaction();

try {
    // Group by gelombang untuk increment counter yang benar
    $groupedByGelombang = $pendaftarLama->groupBy('gelombang_pendaftaran_id');
    
    foreach ($groupedByGelombang as $gelombangId => $pendaftars) {
        if (!$gelombangId) {
            // Pendaftar tanpa gelombang, skip atau handle terpisah
            foreach ($pendaftars as $p) {
                echo "⚠️  {$p->nama_lengkap}: Tidak punya gelombang, dilewati" . PHP_EOL;
                $gagal++;
            }
            continue;
        }
        
        $gelombang = GelombangPendaftaran::with('jalur.tahunPelajaran')->find($gelombangId);
        
        if (!$gelombang) {
            foreach ($pendaftars as $p) {
                echo "⚠️  {$p->nama_lengkap}: Gelombang tidak ditemukan, dilewati" . PHP_EOL;
                $gagal++;
            }
            continue;
        }
        
        foreach ($pendaftars as $p) {
            $nomorLama = $p->nomor_registrasi;
            
            // Generate nomor baru (ini akan increment counter)
            $nomorBaru = $gelombang->generateNomorRegistrasi();
            
            // Update pendaftar (tanpa trigger generateNomorRegistrasi lagi)
            $p->nomor_registrasi = $nomorBaru;
            $p->saveQuietly(); // saveQuietly untuk bypass event
            
            echo "✅ {$p->nama_lengkap}: {$nomorLama} → {$nomorBaru}" . PHP_EOL;
            $sukses++;
        }
    }
    
    DB::commit();
    
    echo PHP_EOL . str_repeat('=', 80) . PHP_EOL;
    echo "HASIL:" . PHP_EOL;
    echo "  ✅ Sukses: {$sukses} pendaftar" . PHP_EOL;
    echo "  ⚠️  Gagal/Dilewati: {$gagal} pendaftar" . PHP_EOL;
    echo str_repeat('=', 80) . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "   Semua perubahan dibatalkan (rollback)." . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== SELESAI ===" . PHP_EOL;
