<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('ppdb.landing');
});

// Load PPDB routes
require __DIR__ . '/ppdb.php';
