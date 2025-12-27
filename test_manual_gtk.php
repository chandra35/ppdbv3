<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LocalGtk;

// Create manual GTK
$gtk = LocalGtk::create([
    'nama_lengkap' => 'Test GTK Manual',
    'nip' => '199001012020121001',
    'email' => 'test.gtk.manual@man.id',
    'nomor_hp' => '081234567890',
    'kategori_ptk' => 'Pendidik',
    'jenis_ptk' => 'Guru Mapel',
    'jabatan' => 'Guru',
    'jenis_kelamin' => 'L',
    'source' => 'manual',
]);

echo "✓ Created GTK Manual:\n";
echo "  ID: {$gtk->id}\n";
echo "  Nama: {$gtk->nama_lengkap}\n";
echo "  Email: {$gtk->email}\n";
echo "  Source: {$gtk->source}\n\n";

// Show statistics
$totalGtk = LocalGtk::count();
$manualGtk = LocalGtk::manual()->count();
$syncedGtk = LocalGtk::synced()->count();

echo "📊 Statistics:\n";
echo "  Total GTK: {$totalGtk}\n";
echo "  Manual: {$manualGtk}\n";
echo "  Synced from SIMANSA: {$syncedGtk}\n";
