<?php

namespace App\Services;

use App\Models\LocalGtk;
use App\Models\SimansaGtk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * GTK Sync Service
 * Synchronize GTK data from SIMANSA to local database
 */
class GtkSyncService
{
    protected int $syncedCount = 0;
    protected int $updatedCount = 0;
    protected int $errorCount = 0;
    protected array $errors = [];

    /**
     * Sync all GTK from SIMANSA to local database
     */
    public function syncFromSimansa(?callable $progressCallback = null): array
    {
        try {
            // Check if SIMANSA connection is available
            if (!$this->checkSimansaConnection()) {
                throw new Exception('SIMANSA database connection not available');
            }

            DB::beginTransaction();

            // Get all GTK from SIMANSA
            $simansaGtks = SimansaGtk::aktif()->get();

            if ($simansaGtks->isEmpty()) {
                throw new Exception('No GTK data found in SIMANSA database');
            }

            $total = $simansaGtks->count();
            $current = 0;

            foreach ($simansaGtks as $simansaGtk) {
                $current++;
                
                // Call progress callback
                if ($progressCallback) {
                    $progressCallback($current, $total);
                }

                try {
                    $this->syncSingleGtk($simansaGtk);
                } catch (Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'gtk' => $simansaGtk->nama_lengkap,
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to sync GTK: ' . $simansaGtk->nama_lengkap . ' - ' . $e->getMessage());
                }
            }

            DB::commit();

            return [
                'success' => true,
                'synced_count' => $this->syncedCount,
                'updated_count' => $this->updatedCount,
                'error_count' => $this->errorCount,
                'error_details' => $this->errors,
                'message' => "Sync completed: {$this->syncedCount} new, {$this->updatedCount} updated, {$this->errorCount} errors",
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GTK Sync failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'synced_count' => 0,
                'updated_count' => 0,
                'error_count' => 0,
            ];
        }
    }

    /**
     * Sync single GTK record
     */
    protected function syncSingleGtk(SimansaGtk $simansaGtk): void
    {
        $localGtk = LocalGtk::where('simansa_id', $simansaGtk->id)->first();

        $data = [
            'nama_lengkap' => $simansaGtk->nama_lengkap,
            'nip' => $simansaGtk->nip,
            'nuptk' => $simansaGtk->nuptk,
            'nik' => $simansaGtk->nik,
            'jenis_kelamin' => $simansaGtk->jenis_kelamin,
            'email' => $simansaGtk->email,
            'nomor_hp' => $simansaGtk->nomor_hp,
            'kategori_ptk' => $simansaGtk->kategori_ptk,
            'jenis_ptk' => $simansaGtk->jenis_ptk,
            'jabatan' => $simansaGtk->jabatan,
            'status_kepegawaian' => $simansaGtk->status_kepegawaian,
            'source' => 'simansa',
            'synced_at' => now(),
            'simansa_id' => $simansaGtk->id,
        ];

        if ($localGtk) {
            // Update existing
            $localGtk->update($data);
            $this->updatedCount++;
        } else {
            // Create new with same UUID as SIMANSA
            $data['id'] = $simansaGtk->id;
            LocalGtk::create($data);
            $this->syncedCount++;
        }
    }

    /**
     * Check if SIMANSA connection is available
     */
    protected function checkSimansaConnection(): bool
    {
        try {
            SimansaGtk::limit(1)->count();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get sync statistics
     */
    public function getStats(): array
    {
        $total = LocalGtk::count();
        $synced = LocalGtk::where('source', 'simansa')->count();
        $manual = LocalGtk::where('source', 'manual')->count();
        $lastSync = LocalGtk::where('source', 'simansa')->max('synced_at');

        return [
            'total' => $total,
            'synced' => $synced,
            'manual' => $manual,
            'last_sync' => $lastSync,
        ];
    }
}
