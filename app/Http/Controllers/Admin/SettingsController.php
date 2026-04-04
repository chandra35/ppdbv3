<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbSettings;
use App\Models\ActivityLog;
use App\Services\DocumentStorageService;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $documentStorageService,
        private readonly GoogleDriveService $googleDriveService
    ) {
    }

    /**
     * Check permission - admin selalu bisa akses
     */
    private function checkPermission(string $permission): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        if ($user->isAdmin()) {
            return;
        }
        
        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }
    }

    public function index()
    {
        $settings = $this->getOrCreateSettings();

        return view('admin.settings.index', compact('settings'));
    }

    public function storage()
    {
        $settings = $this->getOrCreateSettings();
        $rawSettings = DB::table('ppdb_settings')->where('id', $settings->id)->first();

        if ($rawSettings) {
            $settings->dokumen_storage_mode = $rawSettings->dokumen_storage_mode ?? $settings->dokumen_storage_mode;
            $settings->google_drive_auth_mode = $rawSettings->google_drive_auth_mode ?? $settings->google_drive_auth_mode;
            $settings->google_drive_root_folder_id = $rawSettings->google_drive_root_folder_id ?? $settings->google_drive_root_folder_id;
            $settings->google_drive_credentials_path = $rawSettings->google_drive_credentials_path ?? $settings->google_drive_credentials_path;
            $settings->google_drive_make_public = (bool) ($rawSettings->google_drive_make_public ?? $settings->google_drive_make_public);
            $settings->google_drive_oauth_client_id = $rawSettings->google_drive_oauth_client_id ?? $settings->google_drive_oauth_client_id;
            $settings->google_drive_oauth_client_secret = $rawSettings->google_drive_oauth_client_secret ?? $settings->google_drive_oauth_client_secret;
            $settings->google_drive_oauth_refresh_token = $rawSettings->google_drive_oauth_refresh_token ?? $settings->google_drive_oauth_refresh_token;
            $settings->google_drive_oauth_email = $rawSettings->google_drive_oauth_email ?? $settings->google_drive_oauth_email;
        }

        [$googleDriveClientEmail, $googleDriveStatus, $googleDriveStatusMessage] = $this->getGoogleDriveStatusData($settings);

        return view('admin.settings.storage-dokumen', compact(
            'settings',
            'googleDriveClientEmail',
            'googleDriveStatus',
            'googleDriveStatusMessage'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'validasi_nisn_aktif' => 'nullable|boolean',
            'wajib_lokasi_registrasi' => 'nullable|boolean',
            'cegah_pendaftar_ganda' => 'nullable|boolean',
            'izinkan_dokumen_tambahan' => 'nullable|boolean',
            'dokumen_aktif' => 'nullable|array',
            'nomor_registrasi_prefix' => 'required|string|max:20',
            'nomor_tes_prefix' => 'required|string|max:10',
            'nomor_tes_digit' => 'required|integer|in:3,4,5',
        ]);

        // Convert checkbox values
        $validated['validasi_nisn_aktif'] = $request->has('validasi_nisn_aktif');
        $validated['wajib_lokasi_registrasi'] = $request->has('wajib_lokasi_registrasi');
        $validated['cegah_pendaftar_ganda'] = $request->has('cegah_pendaftar_ganda');
        $validated['izinkan_dokumen_tambahan'] = $request->has('izinkan_dokumen_tambahan');
        $validated['google_drive_make_public'] = $request->has('google_drive_make_public');

        $settings = PpdbSettings::first();
        
        if (!$settings) {
            $validated['tahun_pelajaran_id'] = (string) Str::uuid();
            if ($request->hasFile('google_drive_credentials_file')) {
                $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request);
            }

            $settings = PpdbSettings::create($validated);
            ActivityLog::log('create', "Membuat pengaturan PPDB", $settings);
        } else {
            $oldValues = $settings->toArray();
            if ($request->hasFile('google_drive_credentials_file')) {
                $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request, $settings->google_drive_credentials_path);
            }
            $settings->update($validated);
            ActivityLog::log('update', "Mengupdate pengaturan PPDB", $settings, $oldValues, $settings->fresh()->toArray());
        }

        return redirect()->back()->with('success', 'Pengaturan PPDB berhasil diupdate.');
    }

    public function updateStorage(Request $request)
    {
        Log::info('Storage dokumen submit', [
            'dokumen_storage_mode' => $request->input('dokumen_storage_mode'),
            'google_drive_auth_mode' => $request->input('google_drive_auth_mode'),
            '_dokumen_storage_mode_selected' => $request->input('_dokumen_storage_mode_selected'),
            '_google_drive_auth_mode_selected' => $request->input('_google_drive_auth_mode_selected'),
            'has_oauth_client_id' => $request->filled('google_drive_oauth_client_id'),
            'has_oauth_client_secret' => $request->filled('google_drive_oauth_client_secret'),
            'has_credentials_file' => $request->hasFile('google_drive_credentials_file'),
        ]);

        if (!$request->filled('google_drive_auth_mode') && $request->filled('_google_drive_auth_mode_selected')) {
            $request->merge([
                'google_drive_auth_mode' => $request->input('_google_drive_auth_mode_selected'),
            ]);
        }

        if (!$request->filled('dokumen_storage_mode') && $request->filled('_dokumen_storage_mode_selected')) {
            $request->merge([
                'dokumen_storage_mode' => $request->input('_dokumen_storage_mode_selected'),
            ]);
        }

        $validated = $request->validate([
            'dokumen_storage_mode' => 'required|string|in:local,gdrive_primary_local_fallback',
            'google_drive_auth_mode' => 'required|string|in:service_account,oauth',
            'google_drive_root_folder_id' => 'nullable|string|max:255',
            'google_drive_make_public' => 'nullable|boolean',
            'google_drive_credentials_file' => 'nullable|file|mimes:json,txt|max:2048',
            'google_drive_oauth_client_id' => 'nullable|string|max:255',
            'google_drive_oauth_client_secret' => 'nullable|string',
        ]);

        $validated['google_drive_make_public'] = $request->has('google_drive_make_public');

        $settings = $this->getOrCreateSettings();
        $oldValues = $settings->toArray();

        if ($request->hasFile('google_drive_credentials_file')) {
            $validated['google_drive_credentials_path'] = $this->storeGoogleDriveCredentialsFile($request, $settings->google_drive_credentials_path);
        }

        $settings->update($validated);

        ActivityLog::log('update', "Mengupdate pengaturan storage dokumen PPDB", $settings, $oldValues, $settings->fresh()->toArray());

        return redirect()->route('admin.settings.storage.index')->with('success', 'Pengaturan storage dokumen berhasil diupdate.');
    }

    public function redirectGoogleDriveOauth()
    {
        $settings = PpdbSettings::firstOrFail();

        if (empty($settings->google_drive_oauth_client_id) || empty($settings->google_drive_oauth_client_secret)) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Simpan dulu Client ID dan Client Secret Google OAuth sebelum menghubungkan akun.');
        }

        $state = Str::random(40);
        session(['google_drive_oauth_state' => $state]);

        $url = $this->googleDriveService->buildOAuthAuthUrl(
            $settings->google_drive_oauth_client_id,
            route('admin.settings.storage.google-drive.oauth.callback'),
            $state
        );

        return redirect()->away($url);
    }

    public function callbackGoogleDriveOauth(Request $request)
    {
        $settings = PpdbSettings::firstOrFail();
        $expectedState = session('google_drive_oauth_state');
        session()->forget('google_drive_oauth_state');

        if (!$request->filled('state') || !$expectedState || $request->state !== $expectedState) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'State OAuth Google Drive tidak valid.');
        }

        if ($request->filled('error')) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Google OAuth dibatalkan: ' . $request->error);
        }

        if (!$request->filled('code')) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Kode OAuth Google Drive tidak ditemukan.');
        }

        try {
            $tokenData = $this->googleDriveService->exchangeOAuthCode(
                $settings->google_drive_oauth_client_id,
                $settings->google_drive_oauth_client_secret,
                route('admin.settings.storage.google-drive.oauth.callback'),
                $request->code
            );

            $refreshToken = $tokenData['refresh_token'] ?? $settings->google_drive_oauth_refresh_token;
            if (!$refreshToken) {
                return redirect()->route('admin.settings.storage.index')
                    ->with('error', 'Google tidak mengirim refresh token. Coba putuskan akses lama lalu connect ulang.');
            }

            $email = null;
            if (!empty($tokenData['access_token'])) {
                $email = $this->googleDriveService->fetchOAuthUserEmail($tokenData['access_token']);
            }

            $settings->update([
                'google_drive_auth_mode' => 'oauth',
                'google_drive_oauth_refresh_token' => $refreshToken,
                'google_drive_oauth_email' => $email,
            ]);

            return redirect()->route('admin.settings.storage.index')
                ->with('success', 'Google Drive berhasil terhubung dengan akun ' . ($email ?: 'Google Anda') . '.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.storage.index')
                ->with('error', 'Gagal menyambungkan Google Drive OAuth: ' . $e->getMessage());
        }
    }

    public function disconnectGoogleDriveOauth()
    {
        $settings = PpdbSettings::firstOrFail();

        $settings->update([
            'google_drive_oauth_refresh_token' => null,
            'google_drive_oauth_email' => null,
        ]);

        return redirect()->route('admin.settings.storage.index')
            ->with('success', 'Koneksi OAuth Google Drive berhasil diputus.');
    }

    private function getOrCreateSettings(): PpdbSettings
    {
        $settings = PpdbSettings::first();

        if ($settings) {
            return $settings;
        }

        $settings = new PpdbSettings();
        $settings->tahun_pelajaran_id = (string) Str::uuid();
        $settings->kuota_penerimaan = 200;
        $settings->tanggal_dibuka = now();
        $settings->tanggal_ditutup = now()->addMonths(3);
        $settings->status_pendaftaran = true;
        $settings->validasi_nisn_aktif = true;
        $settings->cegah_pendaftar_ganda = true;
        $settings->dokumen_aktif = ['kk', 'akta_lahir', 'ijazah', 'foto'];
        $settings->nomor_registrasi_prefix = 'PPDB';
        $settings->nomor_registrasi_counter = 0;
        $settings->dokumen_storage_mode = 'local';
        $settings->save();

        return $settings;
    }

    private function getGoogleDriveStatusData(PpdbSettings $settings): array
    {
        $googleDriveClientEmail = $this->documentStorageService->getGoogleDriveClientEmail($settings);
        $googleDriveStatus = 'local';
        $googleDriveStatusMessage = 'Dokumen saat ini disimpan ke storage lokal.';

        if ($settings->isGoogleDrivePrimaryEnabled()) {
            if ($settings->isGoogleDriveConfigured()) {
                try {
                    $folderInfo = $this->documentStorageService->testGoogleDriveConnection($settings);
                    $this->documentStorageService->testGoogleDriveWriteAccess($settings);
                    $googleDriveStatus = 'ready';
                    $googleDriveStatusMessage = 'Google Drive siap dipakai. Folder aktif: ' . ($folderInfo['name'] ?? $settings->google_drive_root_folder_id);
                } catch (\Throwable $e) {
                    $googleDriveStatus = 'warning';
                    $googleDriveStatusMessage = 'Mode Google Drive aktif, tetapi uji tulis belum berhasil. Upload akan fallback ke lokal. ' . $e->getMessage();
                }
            } else {
                $googleDriveStatus = 'incomplete';
                $googleDriveStatusMessage = 'Mode Google Drive aktif, tetapi credential JSON atau Folder ID belum lengkap.';
            }
        }

        return [$googleDriveClientEmail, $googleDriveStatus, $googleDriveStatusMessage];
    }

    private function storeGoogleDriveCredentialsFile(Request $request, ?string $oldPath = null): string
    {
        $file = $request->file('google_drive_credentials_file');
        $content = $file->get();
        $decoded = json_decode($content, true);

        if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'google_drive_credentials_file' => 'File JSON Google Drive tidak valid.',
            ]);
        }

        $filename = 'google-drive/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.json';
        Storage::disk('local')->put($filename, $content);

        if ($oldPath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        return $filename;
    }
}
