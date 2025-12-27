<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class EmisNisnService
{
    protected $apiUrl;
    protected $bearerToken;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.emis.api_url', 'https://api-emis.kemenag.go.id/v1');
        
        // Get token from database first, fallback to config
        $tokenData = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
        $this->bearerToken = $tokenData ? $tokenData->token : config('services.emis.bearer_token');
        
        $this->timeout = 30; // 30 seconds timeout
    }

    /**
     * Cek data NISN dari API EMIS Kemenag (Both Kemdikbud & Kemenag)
     *
     * @param string $nisn
     * @return array
     */
    public function cekNisn($nisn)
    {
        try {
            Log::info('EmisNisnService: Checking NISN from both sources', ['nisn' => $nisn]);

            // Validate token exists
            if (empty($this->bearerToken)) {
                throw new Exception('EMIS Bearer token tidak dikonfigurasi. Silakan set token di menu Pengaturan > Update Token EMIS');
            }

            // Prepare HTTP client
            $http = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->bearerToken,
                ]);

            // Skip SSL verification untuk development (Windows SSL issue)
            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            // Initialize data variables
            $kemdikbudData = null;
            $kemenagData = null;

            // 1. Fetch Kemdikbud data (Pusdatin endpoint)
            try {
                $response1 = $http->get($this->apiUrl . "/students/pusdatin/{$nisn}/0");
                
                Log::info('EmisNisnService: Kemdikbud API Response', [
                    'status' => $response1->status(),
                    'body' => $response1->body()
                ]);

                if ($response1->successful()) {
                    $data = $response1->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results'])) {
                        // Check if data is "data tidak ditemukan"
                        if (is_array($data['results']) && isset($data['results']['data']) && 
                            $data['results']['data'] === 'data tidak ditemukan') {
                            $kemdikbudData = null;
                        } else {
                            $kemdikbudData = $data['results'];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('EmisNisnService: Kemdikbud API failed', ['error' => $e->getMessage()]);
            }

            // 2. Fetch Kemenag data (PPDB Search endpoint)
            try {
                $response2 = $http->get($this->apiUrl . "/students/student-ppdb-search?fnisn={$nisn}");
                
                Log::info('EmisNisnService: Kemenag API Response', [
                    'status' => $response2->status(),
                    'body' => $response2->body()
                ]);

                if ($response2->successful()) {
                    $data = $response2->json();
                    if (isset($data['success']) && $data['success'] === true && isset($data['results']) && !empty($data['results'])) {
                        $kemenagData = $data['results'][0]; // Get first result from array
                    }
                }
            } catch (\Exception $e) {
                Log::warning('EmisNisnService: Kemenag API failed', ['error' => $e->getMessage()]);
            }

            // Check if at least one data source returned results
            if ($kemdikbudData || $kemenagData) {
                return [
                    'success' => true,
                    'message' => 'Data NISN ditemukan',
                    'data' => [
                        'kemdikbud' => $kemdikbudData,
                        'kemenag' => $kemenagData
                    ]
                ];
            }

            // No data found from both sources, allow manual input
            return [
                'success' => true,
                'manual_input' => true,
                'message' => 'NISN tidak ditemukan dalam database EMIS (Kemdikbud & Kemenag)',
                'data' => null
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('EmisNisnService: Connection error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server API EMIS. Periksa koneksi internet Anda.',
                'data' => null
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('EmisNisnService: Request error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Request timeout atau ditolak. Silakan coba lagi.',
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('EmisNisnService: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate NISN format
     *
     * @param string $nisn
     * @return bool
     */
    public function validateNisnFormat($nisn)
    {
        // NISN should be exactly 10 digits
        return preg_match('/^\d{10}$/', $nisn);
    }

    /**
     * Check if token is configured and not expired
     *
     * @return array
     */
    public function checkTokenStatus()
    {
        $tokenData = DB::table('api_tokens')->where('name', 'emis_api_token')->first();
        
        if (!$tokenData || empty($tokenData->token)) {
            return [
                'valid' => false,
                'message' => 'Token EMIS belum dikonfigurasi'
            ];
        }

        if ($tokenData->expires_at && strtotime($tokenData->expires_at) < time()) {
            return [
                'valid' => false,
                'message' => 'Token EMIS sudah kadaluarsa',
                'expires_at' => $tokenData->expires_at
            ];
        }

        return [
            'valid' => true,
            'message' => 'Token EMIS aktif',
            'expires_at' => $tokenData->expires_at
        ];
    }

    /**
     * Extract student data from EMIS response for form prefill
     *
     * @param array $emisData
     * @return array
     */
    public function extractStudentData($emisData)
    {
        $result = [
            'nama' => null,
            'nisn' => null,
            'tempat_lahir' => null,
            'tanggal_lahir' => null,
            'jenis_kelamin' => null,
            'nama_ibu' => null,
            'nama_ayah' => null,
            'asal_sekolah' => null,
        ];

        // Extract from Kemdikbud data (primary)
        if (isset($emisData['kemdikbud']) && $emisData['kemdikbud']) {
            $kd = $emisData['kemdikbud'];
            $result['nama'] = $kd['nama'] ?? null;
            $result['nisn'] = $kd['nisn'] ?? null;
            $result['tempat_lahir'] = $kd['tempat_lahir'] ?? null;
            $result['tanggal_lahir'] = isset($kd['tanggal_lahir']) ? date('Y-m-d', strtotime($kd['tanggal_lahir'])) : null;
            $result['jenis_kelamin'] = isset($kd['jenis_kelamin']) ? strtoupper(substr($kd['jenis_kelamin'], 0, 1)) : null;
            $result['nama_ibu'] = $kd['nama_ibu_kandung'] ?? null;
        }

        // Extract from Kemenag data (fallback/supplement)
        if (isset($emisData['kemenag']) && $emisData['kemenag']) {
            $km = $emisData['kemenag'];
            $result['nama'] = $result['nama'] ?? ($km['nama'] ?? null);
            $result['nisn'] = $result['nisn'] ?? ($km['nisn'] ?? null);
            $result['tempat_lahir'] = $result['tempat_lahir'] ?? ($km['tempat_lahir'] ?? null);
            $result['tanggal_lahir'] = $result['tanggal_lahir'] ?? (isset($km['tanggal_lahir']) ? date('Y-m-d', strtotime($km['tanggal_lahir'])) : null);
            $result['jenis_kelamin'] = $result['jenis_kelamin'] ?? (isset($km['jenis_kelamin']) ? strtoupper(substr($km['jenis_kelamin'], 0, 1)) : null);
            $result['nama_ayah'] = $km['nama_ayah'] ?? null;
            $result['nama_ibu'] = $result['nama_ibu'] ?? ($km['nama_ibu'] ?? null);
            $result['asal_sekolah'] = $km['nama_sekolah'] ?? null;
        }

        return $result;
    }
}
