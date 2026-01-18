<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cs = \App\Models\CalonSiswa::with(['nilaiRapor', 'dokumen'])->find('019bce6b-0dfd-7317-b099-5abc12d64f36');

echo "Nama: " . $cs->nama_lengkap . "\n";
echo "NISN: " . $cs->nisn . "\n";
echo "Status Verifikasi: " . $cs->status_verifikasi . "\n";
echo "Nomor Tes: " . ($cs->nomor_tes ?? 'NULL') . "\n";
echo "Is Finalisasi: " . ($cs->is_finalisasi ? 'YES' : 'NO') . "\n\n";

echo "=== Syncing rapor dokumen status ===\n";
// Get admin user ID
$adminId = \App\Models\User::where('username', 'admin')->orWhere('email', 'admin@sekolah.sch.id')->first()?->id;
echo "Using admin ID: " . ($adminId ?? 'NULL') . "\n\n";

foreach ($cs->nilaiRapor as $nr) {
    $jenisDokumen = 'rapor_sem_' . $nr->semester;
    $dok = $cs->dokumen->where('jenis_dokumen', $jenisDokumen)->first();
    
    if ($dok && $nr->status_validasi === 'valid' && $dok->status_verifikasi !== 'valid') {
        $dok->update([
            'status_verifikasi' => 'valid',
            'verified_at' => now(),
            'verified_by' => $adminId,
        ]);
        echo "Semester " . $nr->semester . ": SYNCED to valid\n";
    } else {
        echo "Semester " . $nr->semester . ": OK (nilai_rapor=" . $nr->status_validasi . ", dokumen=" . ($dok ? $dok->status_verifikasi : 'N/A') . ")\n";
    }
}

// Refresh data
$cs->refresh();
$cs->load('dokumen');

echo "\n=== Dokumen Status After Sync ===\n";
foreach ($cs->dokumen as $d) {
    echo $d->jenis_dokumen . ": " . $d->status_verifikasi . "\n";
}

echo "\nallDokumenValid(): " . ($cs->allDokumenValid() ? 'TRUE' : 'FALSE') . "\n";

// Trigger auto update
echo "\n=== Triggering autoUpdateStatusVerifikasi ===\n";
$cs->autoUpdateStatusVerifikasi();

$cs->refresh();
echo "Status Verifikasi: " . $cs->status_verifikasi . "\n";
echo "Nomor Tes: " . ($cs->nomor_tes ?? 'NULL') . "\n";
