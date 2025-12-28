<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JalurPendaftaran;
use App\Models\CalonSiswa;

echo "=== CEK JALUR PENDAFTARAN ===\n\n";

$jalurs = JalurPendaftaran::all();
foreach ($jalurs as $jalur) {
    echo "ID: {$jalur->id}\n";
    echo "Nama: {$jalur->nama_jalur}\n";
    echo "Pilihan Program Aktif: " . ($jalur->pilihan_program_aktif ? 'YA' : 'TIDAK') . "\n";
    echo "Tipe: " . ($jalur->pilihan_program_tipe ?? 'null') . "\n";
    echo "Options: " . json_encode($jalur->pilihan_program_options) . "\n";
    echo "---\n\n";
}

echo "\n=== CEK CALON SISWA (Sample) ===\n\n";

$calon = CalonSiswa::with('jalurPendaftaran')->first();
if ($calon) {
    echo "ID: {$calon->id}\n";
    echo "Nama: {$calon->nama_lengkap}\n";
    echo "User ID: {$calon->user_id}\n";
    echo "Jalur: " . ($calon->jalurPendaftaran->nama_jalur ?? 'null') . "\n";
    echo "Pilihan Program Aktif di Jalur: " . (optional($calon->jalurPendaftaran)->pilihan_program_aktif ? 'YA' : 'TIDAK') . "\n";
    echo "Pilihan Program Siswa: " . ($calon->pilihan_program ?? 'belum pilih') . "\n";
} else {
    echo "Tidak ada calon siswa\n";
}
