<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogVisitor
{
    /**
     * Pages to log (prefix match)
     */
    protected array $logPages = [
        '/ppdb',
        '/pendaftaran',
    ];

    /**
     * Pages to exclude
     */
    protected array $excludePages = [
        '/ppdb/api',
        '/_debugbar',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log GET requests
        if (!$request->isMethod('get')) {
            return $response;
        }

        // Check if should log this page
        if (!$this->shouldLog($request)) {
            return $response;
        }

        // Log visitor asynchronously (don't block response)
        try {
            $this->logVisitor($request);
        } catch (\Exception $e) {
            Log::error('Visitor logging failed: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Check if this request should be logged
     */
    protected function shouldLog(Request $request): bool
    {
        $path = $request->path();

        // Check exclusions first
        foreach ($this->excludePages as $exclude) {
            if (str_starts_with('/' . $path, $exclude)) {
                return false;
            }
        }

        // Check if matches any log pages
        foreach ($this->logPages as $page) {
            if (str_starts_with('/' . $path, $page)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the visitor
     */
    protected function logVisitor(Request $request): void
    {
        $sessionId = session()->getId();
        $currentUrl = $request->fullUrl();
        $pageTitle = $this->getPageTitle($request);

        // Check if visitor already has a record today (by session)
        $existingVisitor = VisitorLog::where('session_id', $sessionId)
            ->whereDate('visited_at', today())
            ->orderBy('visited_at', 'desc')
            ->first();

        if ($existingVisitor) {
            // Update existing record with latest activity
            $existingVisitor->update([
                'last_activity_at' => now(),
                'current_url' => $currentUrl,
                'current_page_title' => $pageTitle,
                'is_online' => true,
                'user_id' => auth()->id() ?? $existingVisitor->user_id, // Keep existing if auth changes
            ]);
            return;
        }

        // Create new visitor record
        $ip = $this->getClientIp($request);
        $userAgent = $request->userAgent();
        $deviceInfo = $this->parseUserAgent($userAgent);
        $geoData = $this->getGeoLocation($ip);

        VisitorLog::create([
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 500),
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'latitude' => $geoData['latitude'] ?? null,
            'longitude' => $geoData['longitude'] ?? null,
            'city' => $geoData['city'] ?? null,
            'region' => $geoData['region'] ?? null,
            'country' => $geoData['country'] ?? null,
            'country_code' => $geoData['country_code'] ?? null,
            'timezone' => $geoData['timezone'] ?? null,
            'isp' => $geoData['isp'] ?? null,
            'page_url' => $currentUrl,
            'page_title' => $pageTitle,
            'current_url' => $currentUrl,
            'current_page_title' => $pageTitle,
            'referrer' => $request->header('referer'),
            'user_id' => auth()->id(),
            'session_id' => $sessionId,
            'visited_at' => now(),
            'last_activity_at' => now(),
            'is_online' => true,
        ]);
    }

    /**
     * Get client IP address
     */
    protected function getClientIp(Request $request): string
    {
        // Check various headers for real IP (behind proxy/load balancer)
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if ($request->server($header)) {
                $ip = $request->server($header);
                // X-Forwarded-For can contain multiple IPs
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return $request->ip();
    }

    /**
     * Parse user agent string
     */
    protected function parseUserAgent(?string $userAgent): array
    {
        $result = [
            'device_type' => 'desktop',
            'browser' => null,
            'browser_version' => null,
            'platform' => null,
            'platform_version' => null,
        ];

        if (!$userAgent) {
            return $result;
        }

        // Detect device type
        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
            $result['device_type'] = 'mobile';
        } elseif (preg_match('/Tablet|iPad|Android(?!.*Mobile)/i', $userAgent)) {
            $result['device_type'] = 'tablet';
        }

        // Detect browser
        $browsers = [
            'Edge' => '/Edg(?:e|A|iOS)?\/([0-9.]+)/i',
            'Chrome' => '/Chrome\/([0-9.]+)/i',
            'Firefox' => '/Firefox\/([0-9.]+)/i',
            'Safari' => '/Version\/([0-9.]+).*Safari/i',
            'Opera' => '/OPR\/([0-9.]+)/i',
            'IE' => '/MSIE ([0-9.]+)|Trident.*rv:([0-9.]+)/i',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $result['browser'] = $browser;
                $result['browser_version'] = $matches[1] ?? $matches[2] ?? null;
                break;
            }
        }

        // Detect platform
        $platforms = [
            'Windows 11' => '/Windows NT 10\.0.*Win64/i',
            'Windows 10' => '/Windows NT 10\.0/i',
            'Windows 8.1' => '/Windows NT 6\.3/i',
            'Windows 8' => '/Windows NT 6\.2/i',
            'Windows 7' => '/Windows NT 6\.1/i',
            'Android' => '/Android ([0-9.]+)/i',
            'iOS' => '/iPhone OS ([0-9_]+)/i',
            'iPad' => '/iPad.*OS ([0-9_]+)/i',
            'Mac OS X' => '/Mac OS X ([0-9_]+)/i',
            'Linux' => '/Linux/i',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $result['platform'] = preg_replace('/ [0-9.]+$/', '', $platform);
                if (isset($matches[1])) {
                    $result['platform_version'] = str_replace('_', '.', $matches[1]);
                }
                break;
            }
        }

        return $result;
    }

    /**
     * Get geo location from IP
     */
    protected function getGeoLocation(string $ip): array
    {
        // Skip for local IPs
        if ($this->isLocalIp($ip)) {
            return [
                'city' => 'Local',
                'country' => 'Local Network',
                'country_code' => 'LO',
            ];
        }

        try {
            // Using ip-api.com (free, no API key needed, 45 requests per minute)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,timezone,isp");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'isp' => $data['isp'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Geo location lookup failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Check if IP is local
     */
    protected function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) ||
               str_starts_with($ip, '192.168.') ||
               str_starts_with($ip, '10.') ||
               preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $ip);
    }

    /**
     * Get page title based on route
     */
    protected function getPageTitle(Request $request): string
    {
        $route = $request->route();
        
        if (!$route) {
            return 'Unknown Page';
        }

        $routeName = $route->getName();
        
        $titles = [
            'ppdb.landing' => 'Beranda PPDB',
            'ppdb.login' => 'Login PPDB',
            'ppdb.register.step1' => 'Pendaftaran - Step 1',
            'ppdb.register.step2' => 'Pendaftaran - Step 2',
            'ppdb.register.step3' => 'Pendaftaran - Step 3',
            'ppdb.register.step4' => 'Pendaftaran - Step 4',
            'ppdb.register.success' => 'Pendaftaran Berhasil',
            'ppdb.berita.show' => 'Detail Berita',
            'ppdb.siswa.dashboard' => 'Dashboard Siswa',
        ];

        return $titles[$routeName] ?? ucwords(str_replace(['.', '-', '_'], ' ', $routeName ?? 'Page'));
    }
}
