<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Verifikator CRUD Implementation ===\n\n";

try {
    // Test 1: Check Gtk Model with gtks table
    echo "1. Testing Gtk Model...\n";
    $gtkCount = App\Models\Gtk::aktif()->count();
    echo "   ✅ Gtk Model OK - Found {$gtkCount} GTK records from 'gtks' table\n";
    
    if ($gtkCount > 0) {
        $sampleGtk = App\Models\Gtk::aktif()->first();
        echo "   Sample: {$sampleGtk->nama_lengkap} - NIP: " . ($sampleGtk->nip ?? 'N/A') . "\n";
    }
    
    // Test 2: Check Verifikator Model
    echo "\n2. Testing Verifikator Model...\n";
    $verifikatorCount = App\Models\Verifikator::count();
    echo "   ✅ Verifikator Model OK - {$verifikatorCount} verifikators assigned\n";
    
    // Test 3: Check PpdbSettings
    echo "\n3. Testing PpdbSettings...\n";
    $activePpdb = App\Models\PpdbSettings::where('is_active', true)->first();
    if ($activePpdb) {
        echo "   ✅ Active PPDB Settings found (ID: {$activePpdb->id})\n";
    } else {
        echo "   ⚠️  No active PPDB Settings found\n";
    }
    
    // Test 4: Check available GTK (not yet verifikator)
    echo "\n4. Testing Available GTK for Verifikator...\n";
    $availableGtk = App\Models\Gtk::aktif()
        ->whereDoesntHave('verifikator')
        ->count();
    echo "   ✅ Available GTK: {$availableGtk} (not yet assigned as verifikator)\n";
    
    // Test 5: Check Verifikator with GTK relation
    echo "\n5. Testing Verifikator -> GTK Relation...\n";
    if ($verifikatorCount > 0) {
        $verifikatorWithGtk = App\Models\Verifikator::with('gtk')->first();
        if ($verifikatorWithGtk && $verifikatorWithGtk->gtk) {
            echo "   ✅ Relation OK - Verifikator: {$verifikatorWithGtk->gtk->nama_lengkap}\n";
        } else {
            echo "   ⚠️  Verifikator exists but GTK relation not loaded\n";
        }
    } else {
        echo "   ℹ️  No verifikator to test relation\n";
    }
    
    // Test 6: Check database connection to simansav3
    echo "\n6. Testing Database Connection...\n";
    $dbName = DB::connection()->getDatabaseName();
    echo "   ✅ Connected to database: {$dbName}\n";
    
    // Test 7: Sample query that controller will use
    echo "\n7. Testing Controller Query Pattern...\n";
    $verifikators = App\Models\Verifikator::with('gtk')
        ->orderBy('created_at', 'desc')
        ->get();
    echo "   ✅ Query OK - Retrieved {$verifikators->count()} verifikators with GTK data\n";
    
    $availableGtkList = App\Models\Gtk::aktif()
        ->whereDoesntHave('verifikator')
        ->orderBy('nama_lengkap')
        ->get(['id', 'nama_lengkap', 'nip', 'jabatan', 'email']);
    echo "   ✅ Query OK - Retrieved {$availableGtkList->count()} available GTK\n";
    
    echo "\n=== All Tests Passed! ✅ ===\n";
    echo "\n📝 Summary:\n";
    echo "   - Total GTK in database: {$gtkCount}\n";
    echo "   - Currently assigned as verifikator: {$verifikatorCount}\n";
    echo "   - Available for assignment: {$availableGtk}\n";
    echo "   - PPDB Settings active: " . ($activePpdb ? 'Yes' : 'No') . "\n";
    
    if (!$activePpdb) {
        echo "\n⚠️  WARNING: No active PPDB Settings found!\n";
        echo "   Please create and activate PPDB Settings before assigning verifikators.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
