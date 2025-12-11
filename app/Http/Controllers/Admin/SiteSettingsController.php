<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\ActivityLog;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSettings::instance();
        
        // Check Facebook configuration
        $facebookService = new FacebookService();
        $facebookConfigured = $facebookService->isConfigured();
        $facebookPageInfo = $facebookConfigured ? $facebookService->getPageInfo() : null;
        
        return view('admin.site-settings.index', compact('settings', 'facebookConfigured', 'facebookPageInfo'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:500',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:1024',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $validated['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan umum website', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index')
            ->with('success', 'Pengaturan umum berhasil diupdate');
    }

    public function updateSocial(Request $request)
    {
        $validated = $request->validate([
            'facebook_url' => 'nullable|url|max:255',
            'facebook_page_id' => 'nullable|string|max:100',
            'facebook_access_token' => 'nullable|string|max:500',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan media sosial', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index', ['tab' => 'social'])
            ->with('success', 'Pengaturan media sosial berhasil diupdate');
    }

    public function updateLanding(Request $request)
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'about_content' => 'nullable|string',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            if ($settings->hero_image && Storage::disk('public')->exists($settings->hero_image)) {
                Storage::disk('public')->delete($settings->hero_image);
            }
            $validated['hero_image'] = $request->file('hero_image')->store('settings', 'public');
        }

        // Handle about image upload
        if ($request->hasFile('about_image')) {
            if ($settings->about_image && Storage::disk('public')->exists($settings->about_image)) {
                Storage::disk('public')->delete($settings->about_image);
            }
            $validated['about_image'] = $request->file('about_image')->store('settings', 'public');
        }

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan halaman landing', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index', ['tab' => 'landing'])
            ->with('success', 'Pengaturan halaman landing berhasil diupdate');
    }

    public function updateSeo(Request $request)
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan SEO', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index', ['tab' => 'seo'])
            ->with('success', 'Pengaturan SEO berhasil diupdate');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'footer_text' => 'nullable|string',
            'copyright_text' => 'nullable|string|max:255',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan tema', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index', ['tab' => 'theme'])
            ->with('success', 'Pengaturan tema berhasil diupdate');
    }

    public function updateMaps(Request $request)
    {
        $validated = $request->validate([
            'google_maps_embed' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $settings = SiteSettings::instance();
        $oldValues = $settings->toArray();

        $settings->update($validated);
        $this->clearCache();

        ActivityLog::log('update', 'Mengupdate pengaturan lokasi/peta', $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.ppdb.site-settings.index', ['tab' => 'maps'])
            ->with('success', 'Pengaturan lokasi/peta berhasil diupdate');
    }

    /**
     * Verify Facebook Token
     */
    public function verifyFacebookToken()
    {
        $facebookService = new FacebookService();
        $result = $facebookService->verifyToken();

        if ($result['valid']) {
            return redirect()->back()->with('success', 'Token Facebook valid! Terhubung ke: ' . ($result['name'] ?? 'Unknown'));
        }

        return redirect()->back()->with('error', 'Token Facebook tidak valid: ' . $result['message']);
    }

    /**
     * Clear settings cache
     */
    protected function clearCache()
    {
        Cache::forget('site_settings');
    }
}
