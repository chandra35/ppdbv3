<?php
/**
 * Script untuk memperbaiki gelombang_pendaftaran_id pendaftar
 * yang terdaftar di gelombang dari tahun pelajaran yang salah
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\DB;

echo "=== FIX GELOMBANG PENDAFTAR ===" . PHP_EOL . PHP_EOL;

// 1. Dapatkan TP aktif
$tpAktif = TahunPelajaran::where('is_active', true)->first();
echo "Tahun Pelajaran Aktif: " . ($tpAktif?->nama ?? 'TIDAK ADA') . PHP_EOL;

if (!$tpAktif) {
    echo "❌ Tidak ada tahun pelajaran aktif!" . PHP_EOL;
    exit(1);
}

// 2. Cari pendaftar yang gelombangnya dari TP berbeda dengan TP pendaftar
$pendaftarSalah = CalonSiswa::with([
    'jalurPendaftaran.tahunPelajaran',
    'gelombangPendaftaran.jalur.tahunPelajaran',
    'tahunPelajaran'
])
->where('tahun_pelajaran_id', $tpAktif->id)
->whereHas('gelombangPendaftaran.jalur', function ($q) use ($tpAktif) {
    $q->where('tahun_pelajaran_id', '!=', $tpAktif->id);
})
->get();

echo "Ditemukan: " . $pendaftarSalah->count() . " pendaftar dengan gelombang salah" . PHP_EOL . PHP_EOL;

if ($pendaftarSalah->isEmpty()) {
    echo "✅ Tidak ada pendaftar yang perlu difix." . PHP_EOL;
    exit(0);
}

// 3. Cari gelombang yang benar dari TP aktif
$gelombangBenar = GelombangPendaftaran::with('jalur')
    ->whereHas('jalur', function ($q) use ($tpAktif) {
        $q->where('tahun_pelajaran_id', $tpAktif->id);
    })
    ->where('is_active', true)
    ->first();

if (!$gelombangBenar) {
    // Jika tidak ada yang aktif, cari yang pertama
    $gelombangBenar = GelombangPendaftaran::with('jalur')
        ->whereHas('jalur', function ($q) use ($tpAktif) {
            $q->where('tahun_pelajaran_id', $tpAktif->id);
        })
        ->first();
}

if (!$gelombangBenar) {
    echo "❌ Tidak ada gelombang untuk TP {$tpAktif->nama}!" . PHP_EOL;
    exit(1);
}

echo "Gelombang target: {$gelombangBenar->nama}" . PHP_EOL;
echo "  Jalur: " . $gelombangBenar->jalur?->nama . PHP_EOL;
echo "  TP: " . $gelombangBenar->jalur?->tahunPelajaran?->nama . PHP_EOL . PHP_EOL;

// 4. Preview
echo str_repeat('-', 100) . PHP_EOL;
printf("%-35s %-30s %-30s\n", "Nama", "Gelombang Lama", "Gelombang Baru");
echo str_repeat('-', 100) . PHP_EOL;

foreach ($pendaftarSalah as $p) {
    $gelLama = $p->gelombangPendaftaran?->nama . ' (' . $p->gelombangPendaftaran?->jalur?->tahunPelajaran?->nama . ')';
    $gelBaru = $gelombangBenar->nama . ' (' . $gelombangBenar->jalur?->tahunPelajaran?->nama . ')';
    
    printf("%-35s %-30s %-30s\n",
        substr($p->nama_lengkap, 0, 33),
        $gelLama,
        $gelBaru
    );
}

echo str_repeat('-', 100) . PHP_EOL . PHP_EOL;

// 5. Konfirmasi
echo "⚠️  PERHATIAN: Ini akan mengubah gelombang_pendaftaran_id!" . PHP_EOL;
echo "Lanjutkan? (ketik 'YES' untuk konfirmasi): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if ($input !== 'YES') {
    echo PHP_EOL . "❌ Dibatalkan oleh user." . PHP_EOL;
    exit(1);
}

// 6. Proses update
echo PHP_EOL . "Memproses..." . PHP_EOL;

DB::beginTransaction();

try {
    $updated = CalonSiswa::whereIn('id', $pendaftarSalah->pluck('id'))
        ->update(['gelombang_pendaftaran_id' => $gelombangBenar->id]);
    
    DB::commit();
    
    echo PHP_EOL . "✅ Berhasil update {$updated} pendaftar ke gelombang yang benar!" . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo PHP_EOL . "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== SELESAI ===" . PHP_EOL;
echo PHP_EOL . "Selanjutnya jalankan: php fix_nomor_registrasi.php" . PHP_EOL;
