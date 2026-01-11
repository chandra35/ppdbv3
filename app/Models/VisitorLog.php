<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'latitude',
        'longitude',
        'altitude',
        'accuracy',
        'altitude_accuracy',
        'heading',
        'speed',
        'location_source',
        'city',
        'district',
        'subdistrict',
        'region',
        'country',
        'country_code',
        'timezone',
        'isp',
        'address',
        'postal_code',
        'page_url',
        'page_title',
        'referrer',
        'user_id',
        'calon_siswa_id',
        'converted_to_registration',
        'conversion_at',
        'session_id',
        'visited_at',
        'last_activity_at',
        'current_url',
        'current_page_title',
        'is_online',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'altitude_accuracy' => 'decimal:2',
        'heading' => 'decimal:2',
        'speed' => 'decimal:2',
        'visited_at' => 'datetime',
        'conversion_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'converted_to_registration' => 'boolean',
        'is_online' => 'boolean',
    ];

    /**
     * Get the user that owns the visitor log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the calon siswa (pendaftar) associated with this visitor.
     */
    public function calonSiswa(): BelongsTo
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    /**
     * Scope for converted visitors (yang sudah mendaftar)
     */
    public function scopeConverted($query)
    {
        return $query->where('converted_to_registration', true);
    }

    /**
     * Scope for non-converted visitors
     */
    public function scopeNotConverted($query)
    {
        return $query->where('converted_to_registration', false);
    }

    /**
     * Check if visitor has converted to registration
     */
    public function hasConverted(): bool
    {
        return $this->converted_to_registration || $this->calon_siswa_id !== null;
    }

    /**
     * Get conversion status badge
     */
    public function getConversionBadgeAttribute(): string
    {
        if ($this->converted_to_registration || $this->calon_siswa_id) {
            return '<span class="badge badge-success" title="Sudah Mendaftar"><i class="fas fa-check-circle"></i> Mendaftar</span>';
        }
        
        return '<span class="badge badge-secondary" title="Belum Mendaftar"><i class="fas fa-clock"></i> Belum</span>';
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('visited_at', [$start, $end]);
    }

    /**
     * Scope for today's visitors
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * Scope for unique visitors by IP
     */
    public function scopeUniqueVisitors($query)
    {
        return $query->distinct('ip_address');
    }

    /**
     * Get device icon
     */
    public function getDeviceIconAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            'desktop' => 'fas fa-desktop',
            default => 'fas fa-question-circle',
        };
    }

    /**
     * Get device color class
     */
    public function getDeviceColorAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'success',
            'tablet' => 'info',
            'desktop' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get browser icon
     */
    public function getBrowserIconAttribute(): string
    {
        $browser = strtolower($this->browser ?? '');
        
        if (str_contains($browser, 'chrome')) return 'fab fa-chrome';
        if (str_contains($browser, 'firefox')) return 'fab fa-firefox';
        if (str_contains($browser, 'safari')) return 'fab fa-safari';
        if (str_contains($browser, 'edge')) return 'fab fa-edge';
        if (str_contains($browser, 'opera')) return 'fab fa-opera';
        if (str_contains($browser, 'ie') || str_contains($browser, 'internet explorer')) return 'fab fa-internet-explorer';
        
        return 'fas fa-globe';
    }

    /**
     * Get platform icon
     */
    public function getPlatformIconAttribute(): string
    {
        $platform = strtolower($this->platform ?? '');
        
        if (str_contains($platform, 'windows')) return 'fab fa-windows';
        if (str_contains($platform, 'android')) return 'fab fa-android';
        if (str_contains($platform, 'ios') || str_contains($platform, 'iphone') || str_contains($platform, 'ipad')) return 'fab fa-apple';
        if (str_contains($platform, 'mac')) return 'fab fa-apple';
        if (str_contains($platform, 'linux')) return 'fab fa-linux';
        
        return 'fas fa-laptop';
    }

    /**
     * Get location string
     */
    public function getLocationStringAttribute(): string
    {
        $parts = array_filter([
            $this->subdistrict,
            $this->district,
            $this->city,
            $this->region,
            $this->country,
        ]);
        
        return $parts ? implode(', ', $parts) : 'Unknown';
    }

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        if ($this->address) {
            return $this->address;
        }
        
        return $this->location_string;
    }

    /**
     * Get coordinates string (formatted)
     */
    public function getCoordinatesStringAttribute(): string
    {
        if (!$this->hasCoordinates()) {
            return '-';
        }
        
        return sprintf('%.8f, %.8f', $this->latitude, $this->longitude);
    }

    /**
     * Get coordinates with altitude
     */
    public function getFullCoordinatesAttribute(): string
    {
        if (!$this->hasCoordinates()) {
            return '-';
        }
        
        $coords = sprintf('%.8f, %.8f', $this->latitude, $this->longitude);
        
        if ($this->altitude) {
            $coords .= sprintf(' (Alt: %.1fm)', $this->altitude);
        }
        
        return $coords;
    }

    /**
     * Get accuracy badge
     */
    public function getAccuracyBadgeAttribute(): string
    {
        if (!$this->accuracy) {
            return '<span class="badge badge-secondary">N/A</span>';
        }
        
        if ($this->accuracy <= 10) {
            return '<span class="badge badge-success" title="Akurasi GPS sangat baik"><i class="fas fa-crosshairs"></i> ±' . round($this->accuracy) . 'm</span>';
        } elseif ($this->accuracy <= 50) {
            return '<span class="badge badge-info" title="Akurasi GPS baik"><i class="fas fa-crosshairs"></i> ±' . round($this->accuracy) . 'm</span>';
        } elseif ($this->accuracy <= 100) {
            return '<span class="badge badge-warning" title="Akurasi GPS sedang"><i class="fas fa-crosshairs"></i> ±' . round($this->accuracy) . 'm</span>';
        } else {
            return '<span class="badge badge-danger" title="Akurasi GPS rendah"><i class="fas fa-crosshairs"></i> ±' . round($this->accuracy) . 'm</span>';
        }
    }

    /**
     * Get location source badge
     */
    public function getLocationSourceBadgeAttribute(): string
    {
        if ($this->location_source === 'gps') {
            return '<span class="badge badge-success" title="Lokasi dari GPS perangkat"><i class="fas fa-satellite"></i> GPS</span>';
        }
        
        return '<span class="badge badge-secondary" title="Lokasi dari IP Address"><i class="fas fa-globe"></i> IP</span>';
    }

    /**
     * Check if has coordinates
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude && $this->longitude;
    }

    /**
     * Check if location is from GPS
     */
    public function isGpsLocation(): bool
    {
        return $this->location_source === 'gps';
    }

    /**
     * Get speed in km/h
     */
    public function getSpeedKmhAttribute(): ?float
    {
        if (!$this->speed) {
            return null;
        }
        
        return round($this->speed * 3.6, 1); // m/s to km/h
    }

    /**
     * Get heading direction
     */
    public function getHeadingDirectionAttribute(): string
    {
        if (!$this->heading) {
            return '-';
        }
        
        $directions = ['U', 'TL', 'T', 'TG', 'S', 'BD', 'B', 'BL'];
        $index = round($this->heading / 45) % 8;
        
        return $directions[$index] . ' (' . round($this->heading) . '°)';
    }

    /**
     * Scope for online visitors (active within X minutes)
     */
    public function scopeOnline($query, int $minutes = 5)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes))
                     ->where('is_online', true);
    }

    /**
     * Scope for offline visitors
     */
    public function scopeOffline($query)
    {
        return $query->where(function ($q) {
            $q->where('last_activity_at', '<', now()->subMinutes(5))
              ->orWhereNull('last_activity_at')
              ->orWhere('is_online', false);
        });
    }

    /**
     * Check if visitor is online
     */
    public function isOnline(int $minutes = 5): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }
        
        return $this->last_activity_at->diffInMinutes(now()) < $minutes && $this->is_online;
    }

    /**
     * Get online status badge
     */
    public function getOnlineStatusBadgeAttribute(): string
    {
        if ($this->isOnline()) {
            $minutesAgo = $this->last_activity_at->diffInMinutes(now());
            $durationText = $minutesAgo < 1 ? 'baru saja' : "{$minutesAgo}m lalu";
            return '<span class="badge badge-success" title="Terakhir aktif: ' . $durationText . '"><i class="fas fa-circle"></i> Online</span>';
        }
        
        return '<span class="badge badge-secondary"><i class="far fa-circle"></i> Offline</span>';
    }

    /**
     * Get online duration (time since first visited today)
     */
    public function getOnlineDurationAttribute(): string
    {
        if (!$this->last_activity_at || !$this->isOnline()) {
            return '-';
        }
        
        $minutes = $this->visited_at->diffInMinutes($this->last_activity_at);
        
        if ($minutes < 1) {
            return '< 1m';
        } elseif ($minutes < 60) {
            return $minutes . 'm';
        } else {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $hours . 'j ' . $mins . 'm';
        }
    }

    /**
     * Get visitor name (from user or calon_siswa or anonymous)
     */
    public function getVisitorNameAttribute(): string
    {
        if ($this->user_id && $this->user) {
            return $this->user->name;
        }
        
        if ($this->calon_siswa_id && $this->calonSiswa) {
            return $this->calonSiswa->nama;
        }
        
        // Check if there's a calon siswa with same session_id
        if ($this->session_id) {
            $calonSiswa = \App\Models\CalonSiswa::where('visitor_session_id', $this->session_id)->first();
            if ($calonSiswa) {
                return $calonSiswa->nama;
            }
        }
        
        return 'Pengunjung Anonim';
    }

    /**
     * Check if visitor is identified (has user or calon_siswa)
     */
    public function isIdentified(): bool
    {
        return $this->user_id !== null || $this->calon_siswa_id !== null;
    }

    /**
     * Get visitor type badge
     */
    public function getVisitorTypeBadgeAttribute(): string
    {
        if ($this->user_id) {
            return '<span class="badge badge-primary" title="Pengguna Terdaftar"><i class="fas fa-user"></i> User</span>';
        }
        
        if ($this->calon_siswa_id || ($this->session_id && \App\Models\CalonSiswa::where('visitor_session_id', $this->session_id)->exists())) {
            return '<span class="badge badge-info" title="Calon Pendaftar"><i class="fas fa-user-graduate"></i> Pendaftar</span>';
        }
        
        return '<span class="badge badge-secondary" title="Pengunjung Anonim"><i class="fas fa-user-secret"></i> Anonim</span>';
    }

    /**
     * Update last activity
     */
    public function updateActivity(string $url, ?string $pageTitle = null): void
    {
        $this->update([
            'last_activity_at' => now(),
            'current_url' => $url,
            'current_page_title' => $pageTitle,
            'is_online' => true,
        ]);
    }

    /**
     * Mark as offline
     */
    public function markOffline(): void
    {
        $this->update(['is_online' => false]);
    }

    /**
     * Get the latest visitor log by session (for updating activity)
     */
    public static function getBySession(string $sessionId): ?self
    {
        return static::where('session_id', $sessionId)
                     ->whereDate('visited_at', today())
                     ->orderBy('visited_at', 'desc')
                     ->first();
    }
}
