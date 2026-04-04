<?php

namespace App\Services;

use App\Models\CalonDokumen;
use App\Models\CalonSiswa;
use App\Models\PpdbSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentStorageService
{
    public function __construct(
        private readonly GoogleDriveService $googleDriveService
    ) {
    }

    public function storeUploadedFile(UploadedFile $file, CalonSiswa $calonSiswa, string $jenisDokumen, array $options = []): array
    {
        $filename = $options['filename'] ?? $file->hashName();
        $localDirectory = trim($options['local_directory'] ?? ('dokumen/' . $calonSiswa->id), '/');
        $logicalPath = $localDirectory . '/' . $filename;
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        return $this->storeBinaryContent(
            content: file_get_contents($file->getRealPath()),
            calonSiswa: $calonSiswa,
            jenisDokumen: $jenisDokumen,
            logicalPath: $logicalPath,
            originalName: $options['original_name'] ?? $file->getClientOriginalName(),
            mimeType: $mimeType,
            fileSize: $file->getSize(),
            fileName: $filename
        );
    }

    public function storeBase64Image(string $base64Image, CalonSiswa $calonSiswa, string $jenisDokumen, array $options = []): array
    {
        $clean = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $content = base64_decode($clean);

        if ($content === false) {
            throw new RuntimeException('Gagal memproses gambar base64.');
        }

        $filename = $options['filename'] ?? ($jenisDokumen . '_' . time() . '_' . Str::random(8) . '.jpg');
        $localDirectory = trim($options['local_directory'] ?? ('dokumen/' . $calonSiswa->id), '/');
        $logicalPath = $localDirectory . '/' . $filename;

        return $this->storeBinaryContent(
            content: $content,
            calonSiswa: $calonSiswa,
            jenisDokumen: $jenisDokumen,
            logicalPath: $logicalPath,
            originalName: $options['original_name'] ?? $filename,
            mimeType: 'image/jpeg',
            fileSize: strlen($content),
            fileName: $filename
        );
    }

    public function storeRawContent(string $content, CalonSiswa $calonSiswa, string $jenisDokumen, array $options = []): array
    {
        $filename = $options['filename'] ?? ($jenisDokumen . '_' . time());
        $localDirectory = trim($options['local_directory'] ?? ('dokumen/' . $calonSiswa->id), '/');
        $logicalPath = $localDirectory . '/' . $filename;

        return $this->storeBinaryContent(
            content: $content,
            calonSiswa: $calonSiswa,
            jenisDokumen: $jenisDokumen,
            logicalPath: $logicalPath,
            originalName: $options['original_name'] ?? $filename,
            mimeType: $options['mime_type'] ?? 'application/octet-stream',
            fileSize: $options['file_size'] ?? strlen($content),
            fileName: $filename
        );
    }

    public function delete(CalonDokumen $dokumen): void
    {
        if ($dokumen->storage_disk === 'gdrive' && $dokumen->remote_file_id) {
            try {
                $credentials = $this->loadGoogleDriveCredentials();
                if ($credentials) {
                    $this->googleDriveService->deleteFile($credentials, $dokumen->remote_file_id);
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal menghapus file Google Drive', [
                    'dokumen_id' => $dokumen->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }
    }

    public function testGoogleDriveConnection(?PpdbSettings $settings = null): array
    {
        $settings = $settings ?? PpdbSettings::first();
        $credentials = $this->loadGoogleDriveCredentials($settings);

        if (!$settings || !$credentials || empty($settings->google_drive_root_folder_id)) {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap.');
        }

        return $this->googleDriveService->testConnection($credentials, $settings->google_drive_root_folder_id);
    }

    public function testGoogleDriveWriteAccess(?PpdbSettings $settings = null): array
    {
        $settings = $settings ?? PpdbSettings::first();
        $credentials = $this->loadGoogleDriveCredentials($settings);

        if (!$settings || !$credentials || empty($settings->google_drive_root_folder_id)) {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap.');
        }

        $fileName = 'ppdb-write-test-' . now()->format('Ymd_His') . '.txt';
        $result = $this->googleDriveService->uploadFile(
            credentials: $credentials,
            rootFolderId: $settings->google_drive_root_folder_id,
            folderSegments: ['PPDB-Write-Test'],
            fileName: $fileName,
            mimeType: 'text/plain',
            content: 'PPDB Google Drive write test ' . now()->toIso8601String(),
            makePublic: false
        );

        if (!empty($result['remote_file_id'])) {
            $this->googleDriveService->deleteFile($credentials, $result['remote_file_id']);
        }

        return $result;
    }

    public function getGoogleDriveClientEmail(?PpdbSettings $settings = null): ?string
    {
        $credentials = $this->loadGoogleDriveCredentials($settings);

        return $credentials
            ? $this->googleDriveService->getClientEmail($credentials)
            : null;
    }

    public function loadGoogleDriveCredentials(?PpdbSettings $settings = null): ?array
    {
        $settings = $settings ?? PpdbSettings::first();
        if (!$settings) {
            return null;
        }

        if ($settings->google_drive_auth_mode === 'oauth') {
            if (
                empty($settings->google_drive_oauth_client_id)
                || empty($settings->google_drive_oauth_client_secret)
                || empty($settings->google_drive_oauth_refresh_token)
            ) {
                return null;
            }

            return [
                'auth_type' => 'oauth',
                'google_drive_oauth_client_id' => $settings->google_drive_oauth_client_id,
                'google_drive_oauth_client_secret' => $settings->google_drive_oauth_client_secret,
                'google_drive_oauth_refresh_token' => $settings->google_drive_oauth_refresh_token,
                'oauth_email' => $settings->google_drive_oauth_email,
            ];
        }

        $relativePath = $settings->google_drive_credentials_path;

        if (!$relativePath) {
            return null;
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($relativePath)) {
            return null;
        }

        $decoded = json_decode($disk->get($relativePath), true);
        if (!is_array($decoded)) {
            return null;
        }

        $decoded['auth_type'] = 'service_account';

        return $decoded;
    }

    private function storeBinaryContent(
        string $content,
        CalonSiswa $calonSiswa,
        string $jenisDokumen,
        string $logicalPath,
        string $originalName,
        string $mimeType,
        int $fileSize,
        string $fileName
    ): array {
        $settings = PpdbSettings::first();

        if ($settings?->isGoogleDriveConfigured()) {
            try {
                $credentials = $this->loadGoogleDriveCredentials($settings);
                if ($credentials) {
                    $folderSegments = $this->buildDriveFolderSegments($calonSiswa);
                    $driveResult = $this->googleDriveService->uploadFile(
                        credentials: $credentials,
                        rootFolderId: $settings->google_drive_root_folder_id,
                        folderSegments: $folderSegments,
                        fileName: $fileName,
                        mimeType: $mimeType,
                        content: $content,
                        makePublic: (bool) $settings->google_drive_make_public
                    );

                    return [
                        'nama_file' => $originalName,
                        'file_path' => $logicalPath,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'storage_disk' => 'gdrive',
                        'remote_file_id' => $driveResult['remote_file_id'],
                        'remote_file_url' => $driveResult['remote_file_url'],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Upload Google Drive gagal, fallback ke lokal', [
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDokumen,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Storage::disk('public')->put($logicalPath, $content);

        return [
            'nama_file' => $originalName,
            'file_path' => $logicalPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'storage_disk' => 'public',
            'remote_file_id' => null,
            'remote_file_url' => null,
        ];
    }

    private function buildDriveFolderSegments(CalonSiswa $calonSiswa): array
    {
        $tahun = str_replace('/', '-', $calonSiswa->tahunPelajaran?->nama ?? now()->format('Y') . '-' . now()->addYear()->format('Y'));
        $jalur = $this->sanitizeFolderSegment($calonSiswa->jalurPendaftaran?->nama ?? 'Tanpa-Jalur');
        $gelombang = $this->sanitizeFolderSegment($calonSiswa->gelombangPendaftaran?->nama ?? 'Tanpa-Gelombang');
        $nomor = $this->sanitizeFolderSegment($calonSiswa->nomor_registrasi ?: $calonSiswa->nisn);
        $nama = $this->sanitizeFolderSegment($calonSiswa->nama_lengkap);

        return [
            $tahun,
            $jalur,
            $gelombang,
            $nomor . '_' . $nama,
        ];
    }

    private function sanitizeFolderSegment(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 'Tanpa-Nama';
        }

        $value = preg_replace('/[\\\\\\/\\:\\*\\?\"<>\\|]+/', '-', $value);
        $value = preg_replace('/\\s+/', ' ', $value);

        return Str::limit($value, 120, '');
    }
}
