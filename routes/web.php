<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('ppdb.landing');
});

// Laravolt Indonesia AJAX Routes
Route::middleware(['auth'])->prefix('laravolt/indonesia')->group(function () {
    Route::get('/cities', function(\Illuminate\Http\Request $request) {
        $provinceCode = $request->get('province_code');
        $cities = \Laravolt\Indonesia\Models\City::where('province_code', $provinceCode)->orderBy('name')->get();
        return response()->json($cities);
    });
    
    Route::get('/districts', function(\Illuminate\Http\Request $request) {
        $cityCode = $request->get('city_code');
        $districts = \Laravolt\Indonesia\Models\District::where('city_code', $cityCode)->orderBy('name')->get();
        return response()->json($districts);
    });
    
    Route::get('/villages', function(\Illuminate\Http\Request $request) {
        $districtCode = $request->get('district_code');
        $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $districtCode)->orderBy('name')->get();
        return response()->json($villages);
    });
});

// Load PPDB routes
require __DIR__ . '/ppdb.php';
