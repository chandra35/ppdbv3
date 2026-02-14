<?php
// Simulate the generateSchedule logic with 411 peserta, swap mode, max_sesi=2
// 5 CBT rooms x 40 = 200, 10 Waw rooms x 20 = 200, kapParalel = 200

$totalPeserta = 411;
$kapasitasCbt = 200; // 5 x 40
$kapasitasWawancara = 200; // 10 x 20
$kapasitasParalel = min($kapasitasCbt, $kapasitasWawancara); // 200
$pesertaPerPutaran = $kapasitasParalel * 2; // 400
$maxSesi = 2;

echo "=== SIMULASI 411 PESERTA, SWAP MODE, MAX_SESI=2 ===\n\n";

$jumlahPutaran = ceil($totalPeserta / $pesertaPerPutaran); // ceil(411/400) = 2
$jumlahSesi = $jumlahPutaran * 2; // 4

echo "Tanpa overflow fix:\n";
echo "  Putaran: {$jumlahPutaran}, Sesi: {$jumlahSesi}\n";
echo "  maxPutaran = floor(2/2) = 1 → take(1) → 400 terjadwal, 11 HILANG\n\n";

// With overflow fix: merge overflow ke putaran terakhir
$maxPutaran = floor($maxSesi / 2); // 1

// Simulate chunks
$chunks = [];
for ($i = 0; $i < $jumlahPutaran; $i++) {
    $start = $i * $pesertaPerPutaran;
    $count = min($pesertaPerPutaran, $totalPeserta - $start);
    $chunks[] = $count;
}
echo "Chunks sebelum fix: " . implode(', ', $chunks) . "\n";

// Apply overflow merge
if (count($chunks) > $maxPutaran) {
    $overflow = 0;
    for ($i = $maxPutaran; $i < count($chunks); $i++) {
        $overflow += $chunks[$i];
    }
    $chunks = array_slice($chunks, 0, $maxPutaran);
    $chunks[count($chunks) - 1] += $overflow;
}
echo "Chunks setelah fix: " . implode(', ', $chunks) . "\n\n";

// For the single putaran of 411 peserta:
$putaranPeserta = $chunks[0]; // 411
$halfPoint = ceil($putaranPeserta / 2); // 206 
$grupA = $halfPoint; // 206
$grupB = $putaranPeserta - $halfPoint; // 205

echo "Putaran 1: {$putaranPeserta} peserta\n";
echo "  Grup A: {$grupA} peserta (CBT → Wawancara)\n";  
echo "  Grup B: {$grupB} peserta (Wawancara → CBT)\n\n";

// CBT room distribution for Grup A (206 peserta, 5 rooms x 40)
echo "=== SESI 1: Grup A → CBT ===\n";
$jumlahRuang = 5;
$kapPerRuang = 40;
for ($i = 0; $i < $jumlahRuang; $i++) {
    $start = $i * $kapPerRuang;
    if ($i == $jumlahRuang - 1) {
        // Last room gets the rest
        $count = $grupA - $start;
    } else {
        $count = min($kapPerRuang, $grupA - $start);
    }
    if ($count <= 0) break;
    $pct = round(($count / $kapPerRuang) * 100);
    $marker = $count > $kapPerRuang ? ' ⚠️ OVERFLOW' : '';
    echo "  Ruang CBT " . ($i+1) . ": {$count}/{$kapPerRuang} ({$pct}%){$marker}\n";
}

echo "\n=== SESI 1: Grup B → Wawancara ===\n";
$jumlahRuangWaw = 10;
$kapPerRuangWaw = 20;
// Wawancara uses even distribution
$perRoom = ceil($grupB / $jumlahRuangWaw);
for ($i = 0; $i < $jumlahRuangWaw; $i++) {
    $start = $i * $perRoom;
    $count = min($perRoom, max(0, $grupB - $start));
    if ($count <= 0) break;
    $pct = round(($count / $kapPerRuangWaw) * 100);
    $marker = $count > $kapPerRuangWaw ? ' ⚠️ OVERFLOW' : '';
    echo "  Ruang Waw " . ($i+1) . ": {$count}/{$kapPerRuangWaw} ({$pct}%){$marker}\n";
}

echo "\n=== SESI 2 (SWAP): Grup A → Wawancara ===\n";
$perRoom = ceil($grupA / $jumlahRuangWaw);
for ($i = 0; $i < $jumlahRuangWaw; $i++) {
    $start = $i * $perRoom;
    $count = min($perRoom, max(0, $grupA - $start));
    if ($count <= 0) break;
    $pct = round(($count / $kapPerRuangWaw) * 100);
    $marker = $count > $kapPerRuangWaw ? ' ⚠️ OVERFLOW' : '';
    echo "  Ruang Waw " . ($i+1) . ": {$count}/{$kapPerRuangWaw} ({$pct}%){$marker}\n";
}

echo "\n=== SESI 2 (SWAP): Grup B → CBT ===\n";
for ($i = 0; $i < $jumlahRuang; $i++) {
    $start = $i * $kapPerRuang;
    if ($i == $jumlahRuang - 1) {
        $count = $grupB - $start;
    } else {
        $count = min($kapPerRuang, $grupB - $start);
    }
    if ($count <= 0) break;
    $pct = round(($count / $kapPerRuang) * 100);
    $marker = $count > $kapPerRuang ? ' ⚠️ OVERFLOW' : '';
    echo "  Ruang CBT " . ($i+1) . ": {$count}/{$kapPerRuang} ({$pct}%){$marker}\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total terjadwalkan: {$putaranPeserta}/{$totalPeserta}\n";
echo "Missing: " . ($totalPeserta - $putaranPeserta) . "\n";
echo "CBT Sesi 1 Ruang 5 overflow: " . ($grupA - 4 * $kapPerRuang) . "/{$kapPerRuang}\n";
echo "Wawancara Sesi 2 per ruang: {$perRoom}/{$kapPerRuangWaw}\n";
