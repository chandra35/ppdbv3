<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CalonSiswa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackfillLocationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ppdb:backfill-location {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill location data (city, region, address) for pendaftar yang punya koordinat tapi belum ada nama lokasinya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Mencari pendaftar dengan koordinat tapi tanpa data lokasi...');
        
        $pendaftars = CalonSiswa::whereNotNull('registration_latitude')
            ->whereNotNull('registration_longitude')
            ->whereNull('registration_city')
            ->get();
        
        $count = $pendaftars->count();
        
        if ($count === 0) {
            $this->info('Tidak ada pendaftar yang perlu di-update.');
            return 0;
        }
        
        $this->info("Ditemukan {$count} pendaftar yang perlu di-update.");
        
        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada perubahan yang akan disimpan.');
        }
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $updated = 0;
        $failed = 0;
        
        foreach ($pendaftars as $pendaftar) {
            $geoData = $this->reverseGeocode(
                (float) $pendaftar->registration_latitude,
                (float) $pendaftar->registration_longitude
            );
            
            if ($geoData) {
                if (!$dryRun) {
                    $pendaftar->update([
                        'registration_address' => $geoData['address'] ?? null,
                        'registration_city' => $geoData['city'] ?? null,
                        'registration_region' => $geoData['region'] ?? null,
                        'registration_country' => $geoData['country'] ?? null,
                    ]);
                }
                $updated++;
            } else {
                $failed++;
            }
            
            $bar->advance();
            
            // Rate limiting: Nominatim allows max 1 request per second
            usleep(1100000); // 1.1 seconds
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Selesai! Updated: {$updated}, Failed: {$failed}");
        
        if ($dryRun && $updated > 0) {
            $this->info("Jalankan tanpa --dry-run untuk menyimpan perubahan.");
        }
        
        return 0;
    }
    
    /**
     * Reverse geocode coordinates to address
     */
    protected function reverseGeocode(float $lat, float $lng): ?array
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: PPDB-App/1.0\r\nAccept-Language: id\r\n",
                    'timeout' => 10,
                ]
            ];
            
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                $addr = $data['address'] ?? [];
                
                return [
                    'address' => $data['display_name'] ?? null,
                    'city' => $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? null,
                    'region' => $addr['state'] ?? null,
                    'country' => $addr['country'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocode error in backfill', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
}
