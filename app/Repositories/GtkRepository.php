<?php

namespace App\Repositories;

use App\Models\LocalGtk;
use App\Models\SimansaGtk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * GTK Repository - Abstraction layer for GTK data access
 * Switches between local and SIMANSA data based on configuration
 */
class GtkRepository
{
    protected string $source;
    protected bool $simansaAvailable;

    public function __construct()
    {
        $this->source = config('ppdb.gtk_source', 'local');
        $this->simansaAvailable = $this->checkSimansaConnection();
        
        // Fallback to local if SIMANSA not available
        if ($this->source === 'simansa' && !$this->simansaAvailable) {
            Log::warning('GTK_SOURCE set to simansa but connection not available. Falling back to local.');
            $this->source = 'local';
        }
    }

    /**
     * Get all GTK based on configured source
     */
    public function all(): Collection
    {
        if ($this->source === 'simansa') {
            return $this->fromSimansa();
        }
        
        return $this->fromLocal();
    }

    /**
     * Get GTK from local database
     */
    public function fromLocal(): Collection
    {
        return LocalGtk::aktif()->orderBy('nama_lengkap')->get();
    }

    /**
     * Get GTK from SIMANSA database
     */
    public function fromSimansa(): Collection
    {
        try {
            return SimansaGtk::aktif()->orderBy('nama_lengkap')->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch GTK from SIMANSA: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Find GTK by ID
     */
    public function find(string $id)
    {
        if ($this->source === 'simansa') {
            return SimansaGtk::find($id);
        }
        
        return LocalGtk::find($id);
    }

    /**
     * Get source being used
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Check if SIMANSA connection is available
     */
    public function checkSimansaConnection(): bool
    {
        try {
            if (!config('ppdb.simansa_available')) {
                return false;
            }

            // Try to query SIMANSA database
            SimansaGtk::limit(1)->count();
            return true;
        } catch (\Exception $e) {
            Log::debug('SIMANSA connection check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if SIMANSA is available
     */
    public function isSimansaAvailable(): bool
    {
        return $this->simansaAvailable;
    }

    /**
     * Get available GTK for user registration (not yet registered as PPDB user)
     */
    public function getAvailableForRegistration(): Collection
    {
        $gtks = $this->all();
        $registeredEmails = \App\Models\User::pluck('email')->toArray();
        
        return $gtks->filter(function($gtk) use ($registeredEmails) {
            return $gtk->email && !in_array($gtk->email, $registeredEmails);
        });
    }
}
