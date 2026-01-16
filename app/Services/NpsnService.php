<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NpsnService
{
    protected $baseUrl = 'https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn';
    protected $timeout = 30;

    /**
     * Cek data sekolah dari NPSN via web scraping
     *
     * @param string $npsn
     * @return array
     */
    public function cekNpsn($npsn)
    {
        try {
            Log::info('NpsnService: Checking NPSN', ['npsn' => $npsn]);

            // Validate NPSN format
            if (!$this->validateNpsnFormat($npsn)) {
                return [
                    'success' => false,
                    'message' => 'Format NPSN tidak valid. NPSN harus 8 digit angka.',
                    'data' => null
                ];
            }

            // Fetch the page
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get("{$this->baseUrl}/{$npsn}");

            Log::info('NpsnService: Response status', ['status' => $response->status()]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data dari server Kemdikdasmen. Status: ' . $response->status(),
                    'data' => null
                ];
            }

            $html = $response->body();

            // Check if NPSN not found
            if (strpos($html, 'NPSN TIDAK DITEMUKAN') !== false || strpos($html, 'Data tidak ditemukan') !== false) {
                return [
                    'success' => false,
                    'message' => 'NPSN tidak ditemukan dalam database Kemdikdasmen.',
                    'data' => null
                ];
            }

            // Parse the HTML to extract school data
            $schoolData = $this->parseSchoolData($html, $npsn);

            if ($schoolData) {
                return [
                    'success' => true,
                    'message' => 'Data sekolah ditemukan',
                    'data' => $schoolData
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal mengekstrak data sekolah dari halaman.',
                'data' => null
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('NpsnService: Connection error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server Kemdikdasmen. Periksa koneksi internet.',
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('NpsnService: Unexpected error', [
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
     * Parse school data from HTML
     *
     * @param string $html
     * @param string $npsn
     * @return array|null
     */
    protected function parseSchoolData($html, $npsn)
    {
        $data = [
            'npsn' => $npsn,
            'nama_sekolah' => null,
            'alamat' => null,
            'kelurahan' => null,
            'kecamatan' => null,
            'kabupaten' => null,
            'provinsi' => null,
            'status' => null,
            'bentuk_pendidikan' => null,
            'jenjang' => null,
            'akreditasi' => null,
        ];

        try {
            // Extract nama sekolah from <h4>NAMA SEKOLAH</h4>
            if (preg_match('/<h4>([^<]+)<\/h4>/i', $html, $matches)) {
                $nama = trim($matches[1]);
                // Exclude "NPSN TIDAK DITEMUKAN" or other non-school names
                if (!empty($nama) && 
                    stripos($nama, 'TIDAK DITEMUKAN') === false &&
                    stripos($nama, 'Data Pendidikan') === false) {
                    $data['nama_sekolah'] = $nama;
                }
            }

            // Format HTML: <td>&nbsp;</td><td>Label</td><td>:</td><td>Value</td>
            // Pattern untuk extract data dari tabel dengan format 4 kolom
            
            // Alamat
            if (preg_match('/<td[^>]*>Alamat<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $data['alamat'] = trim($matches[1]);
            }

            // Desa/Kelurahan
            if (preg_match('/<td[^>]*>Desa\/Kelurahan<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $data['kelurahan'] = trim($matches[1]);
            }

            // Kecamatan/Kota (LN)
            if (preg_match('/<td[^>]*>Kecamatan[^<]*<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $kec = trim($matches[1]);
                $data['kecamatan'] = preg_replace('/^KEC\.\s*/i', '', $kec);
            }

            // Kab.-Kota/Negara (LN)
            if (preg_match('/<td[^>]*>Kab[^<]*<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $kab = trim($matches[1]);
                $data['kabupaten'] = preg_replace('/^KAB\.\s*/i', '', $kab);
            }

            // Propinsi/Luar Negeri (LN)
            if (preg_match('/<td[^>]*>Prop[^<]*<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $prov = trim($matches[1]);
                $data['provinsi'] = preg_replace('/^PROV\.\s*/i', '', $prov);
            }

            // Status Sekolah
            if (preg_match('/<td[^>]*>Status Sekolah<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $data['status'] = trim($matches[1]);
            }

            // Bentuk Pendidikan
            if (preg_match('/<td[^>]*>Bentuk Pendidikan<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $data['bentuk_pendidikan'] = trim($matches[1]);
            }

            // Jenjang Pendidikan
            if (preg_match('/<td[^>]*>Jenjang Pendidikan<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>([^<]+)<\/td>/is', $html, $matches)) {
                $data['jenjang'] = trim($matches[1]);
            }

            // Akreditasi - may be in link format with multiline: <a...>A</a>
            if (preg_match('/<td[^>]*>Akreditasi<\/td>\s*<td[^>]*>:<\/td>\s*<td[^>]*>\s*(?:<a[^>]*>)?([A-Z])(?:<\/a>)?/is', $html, $matches)) {
                $data['akreditasi'] = strtoupper(trim($matches[1]));
            }

            // Return data if we at least got the school name
            if ($data['nama_sekolah']) {
                return $data;
            }

            return null;

        } catch (Exception $e) {
            Log::error('NpsnService: Parse error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate NPSN format
     *
     * @param string $npsn
     * @return bool
     */
    public function validateNpsnFormat($npsn)
    {
        // NPSN should be exactly 8 digits
        return preg_match('/^\d{8}$/', $npsn);
    }
}
