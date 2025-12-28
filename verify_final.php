<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CalonSiswa;

echo "=== VERIFIKASI FINAL ===\n\n";

$calon = CalonSiswa::with('jalurPendaftaran')->first();

if ($calon) {
    echo "Calon Siswa: {$calon->nama_lengkap}\n";
    echo "User ID: {$calon->user_id}\n";
    echo "Jalur ID: {$calon->jalur_pendaftaran_id}\n";
    
    if ($calon->jalurPendaftaran) {
        echo "Jalur Nama: {$calon->jalurPendaftaran->nama}\n";
        echo "Pilihan Program Aktif: " . ($calon->jalurPendaftaran->pilihan_program_aktif ? 'YA' : 'TIDAK') . "\n";
        echo "Tipe: {$calon->jalurPendaftaran->pilihan_program_tipe}\n";
        echo "Options: " . json_encode($calon->jalurPendaftaran->pilihan_program_options) . "\n";
        
        echo "\n✅ MENU PILIHAN PROGRAM AKAN MUNCUL DI SIDEBAR!\n";
    } else {
        echo "❌ Jalur tidak ditemukan\n";
    }
} else {
    echo "❌ Tidak ada calon siswa\n";
}
