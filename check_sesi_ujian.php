<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix existing sesi ujian: copy jalur/gelombang from jadwal_ujian
$updated = DB::table('sesi_ujian')
    ->whereNull('jalur_pendaftaran_id')
    ->whereNotNull('jadwal_ujian_id')
    ->update([
        'jalur_pendaftaran_id' => DB::raw('(SELECT jalur_pendaftaran_id FROM jadwal_ujian WHERE jadwal_ujian.id = sesi_ujian.jadwal_ujian_id)'),
        'gelombang_pendaftaran_id' => DB::raw('(SELECT gelombang_pendaftaran_id FROM jadwal_ujian WHERE jadwal_ujian.id = sesi_ujian.jadwal_ujian_id)'),
    ]);

echo "Updated {$updated} sesi ujian\n";

// Verify
$sesis = App\Models\SesiUjian::with(['jalur'])->get();
foreach ($sesis as $s) {
    echo $s->nama . ' | jalur: ' . ($s->jalur->nama ?? 'NULL') . "\n";
}
