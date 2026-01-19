<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PengaturanEmailController extends Controller
{
    /**
     * Show Email settings form
     */
    public function index()
    {
        $settings = PengaturanEmail::first();
        $defaultTemplates = PengaturanEmail::getDefaultTemplates();
        
        // If no settings exist, create a new instance with defaults
        if (!$settings) {
            $settings = new PengaturanEmail([
                'is_active' => true,
                'enable_registrasi' => true,
                'enable_revisi' => true,
                'enable_nomor_tes' => true,
                'enable_diterima' => true,
                'enable_ditolak' => true,
            ]);
        }
        
        // Get placeholders for each type
        $placeholders = [
            'registrasi' => PengaturanEmail::getPlaceholders('registrasi'),
            'revisi' => PengaturanEmail::getPlaceholders('revisi'),
            'nomor_tes' => PengaturanEmail::getPlaceholders('nomor_tes'),
            'diterima' => PengaturanEmail::getPlaceholders('diterima'),
            'ditolak' => PengaturanEmail::getPlaceholders('ditolak'),
        ];
        
        return view('admin.pengaturan.email', compact('settings', 'defaultTemplates', 'placeholders'));
    }

    /**
     * Update Email settings
     */
    public function update(Request $request)
    {
        $request->validate([
            // Global settings
            'is_active' => 'nullable|boolean',
            'from_name' => 'nullable|string|max:100',
            'from_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'footer_text' => 'nullable|string|max:2000',
            
            // Enable/Disable
            'enable_registrasi' => 'nullable|boolean',
            'enable_revisi' => 'nullable|boolean',
            'enable_nomor_tes' => 'nullable|boolean',
            'enable_diterima' => 'nullable|boolean',
            'enable_ditolak' => 'nullable|boolean',
            
            // Subjects
            'subject_registrasi' => 'nullable|string|max:255',
            'subject_revisi' => 'nullable|string|max:255',
            'subject_nomor_tes' => 'nullable|string|max:255',
            'subject_diterima' => 'nullable|string|max:255',
            'subject_ditolak' => 'nullable|string|max:255',
            
            // Templates
            'template_registrasi' => 'nullable|string|max:10000',
            'template_revisi' => 'nullable|string|max:10000',
            'template_nomor_tes' => 'nullable|string|max:10000',
            'template_diterima' => 'nullable|string|max:10000',
            'template_ditolak' => 'nullable|string|max:10000',
        ]);

        $settings = PengaturanEmail::first();
        
        if (!$settings) {
            $settings = new PengaturanEmail();
        }

        $settings->fill([
            // Global
            'is_active' => $request->has('is_active'),
            'from_name' => $request->from_name,
            'from_email' => $request->from_email,
            'reply_to' => $request->reply_to,
            'footer_text' => $request->footer_text,
            
            // Enable/Disable
            'enable_registrasi' => $request->has('enable_registrasi'),
            'enable_revisi' => $request->has('enable_revisi'),
            'enable_nomor_tes' => $request->has('enable_nomor_tes'),
            'enable_diterima' => $request->has('enable_diterima'),
            'enable_ditolak' => $request->has('enable_ditolak'),
            
            // Subjects
            'subject_registrasi' => $request->subject_registrasi,
            'subject_revisi' => $request->subject_revisi,
            'subject_nomor_tes' => $request->subject_nomor_tes,
            'subject_diterima' => $request->subject_diterima,
            'subject_ditolak' => $request->subject_ditolak,
            
            // Templates
            'template_registrasi' => $request->template_registrasi,
            'template_revisi' => $request->template_revisi,
            'template_nomor_tes' => $request->template_nomor_tes,
            'template_diterima' => $request->template_diterima,
            'template_ditolak' => $request->template_ditolak,
            
            'updated_by' => Auth::id(),
        ]);

        $settings->save();

        return redirect()->route('admin.email.index')
            ->with('success', 'Pengaturan Email berhasil disimpan');
    }

    /**
     * Send test email
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:registrasi,revisi,nomor_tes,diterima,ditolak',
        ]);

        try {
            $settings = PengaturanEmail::getSettings();
            $defaults = PengaturanEmail::getDefaultTemplates();
            
            $type = $request->type;
            $subject = $settings?->{'subject_' . $type} ?? $defaults['subject_' . $type] ?? 'Test Email';
            $template = $settings?->{'template_' . $type} ?? $defaults['template_' . $type] ?? '<p>Test email content</p>';
            
            // Replace placeholders with sample data
            $replacements = [
                '{nama_siswa}' => 'John Doe',
                '{nama_sekolah}' => config('app.name', 'MAN 1 Metro'),
                '{tahun_pelajaran}' => '2026/2027',
                '{nomor_registrasi}' => 'PPDB/2026/0001',
                '{username}' => 'johndoe123',
                '{password}' => 'secretpass',
                '{url_login}' => route('pendaftar.login'),
                '{nama_dokumen}' => 'Kartu Keluarga',
                '{catatan}' => 'Dokumen tidak jelas, mohon upload ulang dengan resolusi lebih tinggi.',
                '{nomor_tes}' => 'MAN-2026-REG-0001',
                '{jalur_pendaftaran}' => 'Reguler',
            ];
            
            $body = str_replace(array_keys($replacements), array_values($replacements), $template);
            
            Mail::html($body, function ($message) use ($request, $subject, $settings) {
                $message->to($request->email)
                    ->subject('[TEST] ' . $subject);
                
                if ($settings?->from_name && $settings?->from_email) {
                    $message->from($settings->from_email, $settings->from_name);
                }
                
                if ($settings?->reply_to) {
                    $message->replyTo($settings->reply_to);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email berhasil dikirim ke ' . $request->email,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset templates to default
     */
    public function resetTemplates()
    {
        $settings = PengaturanEmail::first();
        
        if ($settings) {
            $defaults = PengaturanEmail::getDefaultTemplates();
            $settings->update($defaults);
        }

        return redirect()->route('admin.email.index')
            ->with('success', 'Template berhasil direset ke default');
    }
}
