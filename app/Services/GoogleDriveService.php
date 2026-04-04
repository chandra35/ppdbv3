<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleDriveService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const DRIVE_API_BASE = 'https://www.googleapis.com/drive/v3';
    private const DRIVE_UPLOAD_BASE = 'https://www.googleapis.com/upload/drive/v3/files';

    public function getClientEmail(array $credentials): ?string
    {
        if (($credentials['auth_type'] ?? null) === 'oauth') {
            return $credentials['oauth_email'] ?? null;
        }

        return $credentials['client_email'] ?? null;
    }

    public function buildOAuthAuthUrl(string $clientId, string $redirectUri, string $state): string
    {
        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function exchangeOAuthCode(string $clientId, string $clientSecret, string $redirectUri, string $code): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gagal menukar kode OAuth Google: ' . $response->body());
        }

        return $response->json();
    }

    public function fetchOAuthUserEmail(string $accessToken): ?string
    {
        $response = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (!$response->successful()) {
            return null;
        }

        return $response->json('email');
    }

    public function uploadFile(array $credentials, string $rootFolderId, array $folderSegments, string $fileName, string $mimeType, string $content, bool $makePublic = true): array
    {
        $token = $this->getAccessToken($credentials);
        $parentId = $this->ensureFolderPath($token, $rootFolderId, $folderSegments);

        $metadata = [
            'name' => $fileName,
            'parents' => [$parentId],
        ];

        $boundary = 'ppdb-' . Str::random(24);
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata, JSON_UNESCAPED_SLASHES) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mimeType}\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--{$boundary}--";

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => "multipart/related; boundary={$boundary}",
            ])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post(self::DRIVE_UPLOAD_BASE . '?uploadType=multipart&fields=id,name');

        if (!$response->successful()) {
            throw new RuntimeException('Upload Google Drive gagal: ' . $response->body());
        }

        $fileId = $response->json('id');
        if (!$fileId) {
            throw new RuntimeException('Google Drive tidak mengembalikan file id.');
        }

        if ($makePublic) {
            $this->setPublicPermission($token, $fileId);
        }

        return [
            'remote_file_id' => $fileId,
            'remote_file_url' => $this->buildPreviewUrl($fileId),
        ];
    }

    public function deleteFile(array $credentials, string $fileId): void
    {
        $token = $this->getAccessToken($credentials);
        $response = Http::withToken($token)->delete(self::DRIVE_API_BASE . '/files/' . $fileId);

        if (!$response->successful() && $response->status() !== 404) {
            throw new RuntimeException('Gagal menghapus file Google Drive: ' . $response->body());
        }
    }

    public function testConnection(array $credentials, string $rootFolderId): array
    {
        $token = $this->getAccessToken($credentials);
        $response = Http::withToken($token)
            ->get(self::DRIVE_API_BASE . '/files/' . $rootFolderId, [
                'fields' => 'id,name,mimeType',
                'supportsAllDrives' => 'true',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Koneksi Google Drive gagal: ' . $response->body());
        }

        return $response->json();
    }

    public function buildPreviewUrl(string $fileId): string
    {
        return 'https://drive.google.com/uc?export=view&id=' . $fileId;
    }

    private function getAccessToken(array $credentials): string
    {
        $cacheKey = 'gdrive_token_' . md5(
            ($credentials['auth_type'] ?? 'service_account')
            . '|'
            . ($credentials['client_email'] ?? $credentials['google_drive_oauth_client_id'] ?? '')
            . '|'
            . ($credentials['private_key_id'] ?? $credentials['google_drive_oauth_refresh_token'] ?? '')
        );

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($credentials) {
            if (($credentials['auth_type'] ?? null) === 'oauth') {
                $response = Http::asForm()->post(self::TOKEN_URL, [
                    'client_id' => $credentials['google_drive_oauth_client_id'] ?? null,
                    'client_secret' => $credentials['google_drive_oauth_client_secret'] ?? null,
                    'refresh_token' => $credentials['google_drive_oauth_refresh_token'] ?? null,
                    'grant_type' => 'refresh_token',
                ]);
            } else {
                $jwt = $this->buildJwt($credentials);
                $response = Http::asForm()->post(self::TOKEN_URL, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);
            }

            if (!$response->successful()) {
                throw new RuntimeException('Gagal mengambil access token Google: ' . $response->body());
            }

            $token = $response->json('access_token');
            if (!$token) {
                throw new RuntimeException('Access token Google tidak ditemukan.');
            }

            return $token;
        });
    }

    private function buildJwt(array $credentials): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $issuedAt = time();
        $payload = [
            'iss' => $credentials['client_email'] ?? null,
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud' => self::TOKEN_URL,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ];

        if (empty($payload['iss']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Credential Google Drive tidak lengkap.');
        }

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];
        $signingInput = implode('.', $segments);

        $privateKey = openssl_pkey_get_private($credentials['private_key']);
        if (!$privateKey) {
            throw new RuntimeException('Private key Google Drive tidak valid.');
        }

        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if (!$signed) {
            throw new RuntimeException('Gagal menandatangani JWT Google Drive.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function ensureFolderPath(string $token, string $rootFolderId, array $segments): string
    {
        $parentId = $rootFolderId;

        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }

            $existingId = $this->findFolderId($token, $segment, $parentId);
            if ($existingId) {
                $parentId = $existingId;
                continue;
            }

            $parentId = $this->createFolder($token, $segment, $parentId);
        }

        return $parentId;
    }

    private function findFolderId(string $token, string $name, string $parentId): ?string
    {
        $query = sprintf(
            "name = '%s' and mimeType = 'application/vnd.google-apps.folder' and '%s' in parents and trashed = false",
            str_replace("'", "\\'", $name),
            $parentId
        );

        $response = Http::withToken($token)->get(self::DRIVE_API_BASE . '/files', [
            'q' => $query,
            'fields' => 'files(id,name)',
            'pageSize' => 1,
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gagal mencari folder Google Drive: ' . $response->body());
        }

        return $response->json('files.0.id');
    }

    private function createFolder(string $token, string $name, string $parentId): string
    {
        $response = Http::withToken($token)->post(self::DRIVE_API_BASE . '/files?supportsAllDrives=true', [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gagal membuat folder Google Drive: ' . $response->body());
        }

        $folderId = $response->json('id');
        if (!$folderId) {
            throw new RuntimeException('Folder Google Drive gagal dibuat.');
        }

        return $folderId;
    }

    private function setPublicPermission(string $token, string $fileId): void
    {
        $response = Http::withToken($token)->post(self::DRIVE_API_BASE . '/files/' . $fileId . '/permissions?supportsAllDrives=true', [
            'role' => 'reader',
            'type' => 'anyone',
        ]);

        if (!$response->successful() && $response->status() !== 409) {
            throw new RuntimeException('Gagal memberi izin baca file Google Drive: ' . $response->body());
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
