<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanWa;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengaturanWaController extends Controller
{
    /**
     * Show WhatsApp settings form
     */
    public function index()
    {
        $settings = PengaturanWa::first() ?? new PengaturanWa();
        $defaultTemplates = PengaturanWa::getDefaultTemplates();
        
        return view('admin.pengaturan.whatsapp', compact('settings', 'defaultTemplates'));
    }

    /**
     * Update WhatsApp settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:fonnte,wablas,wabotapi,twilio,other',
            'api_key' => 'nullable|string|max:500',
            'api_url' => 'nullable|url|max:255',
            'sender_number' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'template_registrasi' => 'nullable|string|max:2000',
            'template_verifikasi' => 'nullable|string|max:2000',
            'template_diterima' => 'nullable|string|max:2000',
            'template_ditolak' => 'nullable|string|max:2000',
        ]);

        $settings = PengaturanWa::first();
        
        if (!$settings) {
            $settings = new PengaturanWa();
        }

        $settings->fill([
            'provider' => $request->provider,
            'api_key' => $request->api_key,
            'api_url' => $request->api_url,
            'sender_number' => $request->sender_number,
            'is_active' => $request->has('is_active'),
            'template_registrasi' => $request->template_registrasi,
            'template_verifikasi' => $request->template_verifikasi,
            'template_diterima' => $request->template_diterima,
            'template_ditolak' => $request->template_ditolak,
            'updated_by' => Auth::id(),
        ]);

        $settings->save();

        return redirect()->route('admin.pengaturan.whatsapp.index')
            ->with('success', 'Pengaturan WhatsApp berhasil disimpan');
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection(Request $request)
    {
        $waService = new WhatsAppService();
        $result = $waService->testConnection();

        return response()->json($result);
    }

    /**
     * Send test message
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        $waService = new WhatsAppService();
        
        if (!$waService->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp service tidak aktif',
            ]);
        }

        $result = $waService->send($request->phone, $request->message);

        return response()->json($result);
    }

    /**
     * Reset templates to default
     */
    public function resetTemplates()
    {
        $settings = PengaturanWa::first();
        
        if ($settings) {
            $defaults = PengaturanWa::getDefaultTemplates();
            $settings->update($defaults);
        }

        return redirect()->route('admin.pengaturan.whatsapp.index')
            ->with('success', 'Template berhasil direset ke default');
    }
}
