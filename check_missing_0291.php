<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CalonSiswa;
use App\Models\JadwalPeserta;

echo "=== MENCARI man-2026-PRE-0291 ===\n\n";

// 1. Exact search by nomor_tes
$c0291 = CalonSiswa::where('nomor_tes', 'man-2026-PRE-0291')->first();
if ($c0291) {
    echo "DITEMUKAN (active):\n";
    echo "  ID: {$c0291->id}\n";
    echo "  Nama: {$c0291->nama_lengkap}\n";
    echo "  Nomor Tes: {$c0291->nomor_tes}\n";
    echo "  Nomor Reg: {$c0291->nomor_registrasi}\n";
    echo "  Finalisasi: {$c0291->is_finalisasi}\n";
    echo "  Status Verif: {$c0291->status_verifikasi}\n";
    $jp = JadwalPeserta::where('calon_siswa_id', $c0291->id)->first();
    echo "  Terjadwal: " . ($jp ? "YES" : "TIDAK") . "\n";
} else {
    echo "TIDAK ADA di active records\n";
    // Check trashed
    $c0291t = CalonSiswa::withTrashed()->where('nomor_tes', 'man-2026-PRE-0291')->first();
    if ($c0291t) {
        echo "DITEMUKAN di TRASH:\n";
        echo "  ID: {$c0291t->id}\n";
        echo "  Nama: {$c0291t->nama_lengkap}\n";
        echo "  Deleted at: {$c0291t->deleted_at}\n";
        echo "  Deleted by: {$c0291t->deleted_by}\n";
        echo "  Deleted reason: {$c0291t->deleted_reason}\n";
    } else {
        echo "TIDAK ADA bahkan di trash - nomor ini TIDAK PERNAH DIGENERATE\n";
    }
}

// 2. Range PRE-0285 to PRE-0300
echo "\n=== URUTAN NOMOR TES PRE-0285 s/d PRE-0300 ===\n";
$all = CalonSiswa::withTrashed()
    ->where('nomor_tes', 'like', 'man-2026-PRE-%')
    ->get();

$filtered = $all->filter(function($c) {
    $num = (int) substr($c->nomor_tes, -4);
    return $num >= 285 && $num <= 300;
})->sortBy('nomor_tes');

foreach ($filtered as $c) {
    $del = $c->deleted_at ? " [DELETED: {$c->deleted_at}]" : '';
    $jp = JadwalPeserta::where('calon_siswa_id', $c->id)->first();
    $sched = $jp ? 'TERJADWAL' : '-';
    echo "  {$c->nomor_tes} | {$c->nama_lengkap} | fin={$c->is_finalisasi} | verif={$c->status_verifikasi} | {$sched}{$del}\n";
}

// 3. Gaps in the full sequence
echo "\n=== GAPS DALAM URUTAN NOMOR TES PRE ===\n";
$allNums = $all->map(function($c) {
    return (int) substr($c->nomor_tes, -4);
})->sort()->values()->toArray();

$gaps = [];
if (count($allNums) > 0) {
    $min = $allNums[0];
    $max = end($allNums);
    for ($i = $min; $i <= $max; $i++) {
        if (!in_array($i, $allNums)) {
            $gaps[] = $i;
        }
    }
}
echo "Total PRE records: " . count($allNums) . "\n";
echo "Range: PRE-" . str_pad($allNums[0] ?? 0, 4, '0', STR_PAD_LEFT) . " to PRE-" . str_pad(end($allNums) ?: 0, 4, '0', STR_PAD_LEFT) . "\n";
echo "Total gaps: " . count($gaps) . "\n";
if (count($gaps) <= 20) {
    foreach ($gaps as $g) {
        echo "  MISSING: man-2026-PRE-" . str_pad($g, 4, '0', STR_PAD_LEFT) . "\n";
    }
} else {
    echo "  First 20 gaps:\n";
    foreach (array_slice($gaps, 0, 20) as $g) {
        echo "  MISSING: man-2026-PRE-" . str_pad($g, 4, '0', STR_PAD_LEFT) . "\n";
    }
}

// 4. Check how nomor_tes is generated
echo "\n=== CARA GENERATE NOMOR_TES ===\n";
echo "Checking if nomor_tes matches nomor_registrasi pattern or separate...\n";
$sample = CalonSiswa::where('nomor_tes', 'like', 'man-2026-PRE-%')->first();
if ($sample) {
    echo "  Sample nomor_tes: {$sample->nomor_tes}\n";
    echo "  Sample nomor_reg: {$sample->nomor_registrasi}\n";
    echo "  Same? " . ($sample->nomor_tes === $sample->nomor_registrasi ? 'YES' : 'NO - different columns!') . "\n";
}
