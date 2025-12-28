<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class SekolahSettingsController extends Controller
{
    /**
     * Tampilkan form pengaturan sekolah
     */
    public function index()
    {
        $settings = SekolahSettings::getSettings();
        $provinces = Province::orderBy('name')->get();
        
        // Load cities, districts, villages jika sudah ada data
        $cities = $settings->province_code 
            ? City::where('province_code', $settings->province_code)->orderBy('name')->get() 
            : collect();
        
        $districts = $settings->city_code 
            ? District::where('city_code', $settings->city_code)->orderBy('name')->get() 
            : collect();
        
        $villages = $settings->district_code 
            ? Village::where('district_code', $settings->district_code)->orderBy('name')->get() 
            : collect();

        return view('admin.sekolah.index', compact(
            'settings', 
            'provinces', 
            'cities', 
            'districts', 
            'villages'
        ));
    }

    /**
     * Update pengaturan sekolah
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'nsm' => 'nullable|string|max:20',
            'jenjang' => 'required|in:MI,MTs,MA,SD,SMP,SMA,SMK',
            'email' => 'nullable|email|max:100',
            'telepon' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:100',
            'alamat_jalan' => 'nullable|string|max:255',
            'province_code' => 'nullable|string|max:2',
            'city_code' => 'nullable|string|max:4',
            'district_code' => 'nullable|string|max:7',
            'village_code' => 'nullable|string|max:10',
            'kode_pos' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nama_kepala_sekolah' => 'nullable|string|max:100',
            'nip_kepala_sekolah' => 'nullable|string|max:30',
        ]);

        $settings = SekolahSettings::getSettings();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            
            $logoPath = $request->file('logo')->store('sekolah', 'public');
            $validated['logo'] = $logoPath;
        }

        $settings->update($validated);

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }

    /**
     * API: Get cities by province
     */
    public function getCities(Request $request)
    {
        $cities = City::where('province_code', $request->province_code)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->toArray();

        // Clean any output buffer to prevent BOM issues
        if (ob_get_level()) ob_clean();
        
        return response()->json($cities, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * API: Get districts by city
     */
    public function getDistricts(Request $request)
    {
        $districts = District::where('city_code', $request->city_code)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->toArray();

        if (ob_get_level()) ob_clean();
        
        return response()->json($districts, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * API: Get villages by district
     */
    public function getVillages(Request $request)
    {
        $villages = Village::where('district_code', $request->district_code)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->toArray();

        if (ob_get_level()) ob_clean();
        
        return response()->json($villages, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show Kop Surat Builder
     */
    public function showKopBuilder()
    {
        $sekolah = SekolahSettings::getSettings();
        return view('admin.settings.kop-builder', compact('sekolah'));
    }

    /**
     * Update Kop Surat Configuration
     */
    public function updateKopConfig(Request $request)
    {
        $validated = $request->validate([
            'kop_mode' => 'required|in:builder,custom',
            'kop_surat_config' => 'nullable|json',
        ]);

        $sekolah = SekolahSettings::getSettings();
        
        // Decode JSON config
        if ($validated['kop_surat_config']) {
            $validated['kop_surat_config'] = json_decode($validated['kop_surat_config'], true);
        }

        $sekolah->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi kop surat berhasil disimpan.'
        ]);
    }

    /**
     * Upload Logo Kemenag
     */
    public function uploadLogoKemenag(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required|in:kemenag,custom',
        ]);

        $sekolah = SekolahSettings::getSettings();

        // Handle different types
        if ($request->type === 'kemenag') {
            // Hapus logo lama
            if ($sekolah->logo_kemenag_path && Storage::disk('public')->exists($sekolah->logo_kemenag_path)) {
                Storage::disk('public')->delete($sekolah->logo_kemenag_path);
            }
            
            $logoPath = $request->file('logo')->store('sekolah', 'public');
            $sekolah->update(['logo_kemenag_path' => $logoPath]);
            
            return response()->json([
                'success' => true,
                'message' => 'Logo Kemenag berhasil diupload.',
                'path' => $logoPath
            ]);
            
        } else if ($request->type === 'custom') {
            // Hapus kop custom lama
            if ($sekolah->kop_surat_custom_path && Storage::disk('public')->exists($sekolah->kop_surat_custom_path)) {
                Storage::disk('public')->delete($sekolah->kop_surat_custom_path);
            }
            
            $kopPath = $request->file('logo')->store('sekolah', 'public');
            $sekolah->update(['kop_surat_custom_path' => $kopPath]);
            
            return response()->json([
                'success' => true,
                'message' => 'Kop surat custom berhasil diupload.',
                'path' => $kopPath
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid type'
        ], 400);
    }

    /**
     * Delete Logo Kemenag or Custom Kop
     */
    public function deleteKopAsset(Request $request)
    {
        $request->validate([
            'type' => 'required|in:logo_kemenag,kop_custom',
        ]);

        $sekolah = SekolahSettings::getSettings();

        if ($request->type === 'logo_kemenag') {
            if ($sekolah->logo_kemenag_path && Storage::disk('public')->exists($sekolah->logo_kemenag_path)) {
                Storage::disk('public')->delete($sekolah->logo_kemenag_path);
            }
            $sekolah->update(['logo_kemenag_path' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Logo Kemenag berhasil dihapus.'
            ]);
            
        } else if ($request->type === 'kop_custom') {
            if ($sekolah->kop_surat_custom_path && Storage::disk('public')->exists($sekolah->kop_surat_custom_path)) {
                Storage::disk('public')->delete($sekolah->kop_surat_custom_path);
            }
            $sekolah->update(['kop_surat_custom_path' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Kop surat custom berhasil dihapus.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid type'
        ], 400);
    }
}
