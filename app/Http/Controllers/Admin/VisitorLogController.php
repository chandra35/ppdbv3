<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorLogController extends Controller
{
    /**
     * Display visitor statistics dashboard
     */
    public function index(Request $request)
    {
        // Date range filter
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Summary statistics
        $stats = [
            'total_visits' => VisitorLog::whereBetween('visited_at', [$start, $end])->count(),
            'unique_visitors' => VisitorLog::whereBetween('visited_at', [$start, $end])->distinct('ip_address')->count('ip_address'),
            'today_visits' => VisitorLog::today()->count(),
            'today_unique' => VisitorLog::today()->distinct('ip_address')->count('ip_address'),
            // Conversion statistics
            'total_converted' => VisitorLog::whereBetween('visited_at', [$start, $end])->where('converted_to_registration', true)->count(),
            'unique_converted' => VisitorLog::whereBetween('visited_at', [$start, $end])->where('converted_to_registration', true)->distinct('ip_address')->count('ip_address'),
            'conversion_rate' => 0,
            // Online statistics
            'online_now' => VisitorLog::online(5)->distinct('session_id')->count('session_id'),
        ];
        
        // Calculate conversion rate
        if ($stats['unique_visitors'] > 0) {
            $stats['conversion_rate'] = round(($stats['unique_converted'] / $stats['unique_visitors']) * 100, 1);
        }

        // Visits per day for chart
        $visitsPerDay = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('COUNT(*) as total'), DB::raw('COUNT(DISTINCT ip_address) as unique_visitors'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device statistics
        $deviceStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // Browser statistics
        $browserStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Platform statistics
        $platformStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select('platform', DB::raw('COUNT(*) as count'))
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Location statistics (by city)
        $locationStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select('city', 'country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city', 'country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Popular pages
        $pageStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select('page_title', 'page_url', DB::raw('COUNT(*) as count'))
            ->whereNotNull('page_title')
            ->groupBy('page_title', 'page_url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Hourly distribution
        $hourlyStats = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->select(DB::raw('HOUR(visited_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill missing hours
        $hourlyData = collect(range(0, 23))->map(function ($hour) use ($hourlyStats) {
            return [
                'hour' => $hour,
                'count' => $hourlyStats->get($hour)?->count ?? 0,
            ];
        });

        return view('admin.visitor-logs.index', compact(
            'stats',
            'visitsPerDay',
            'deviceStats',
            'browserStats',
            'platformStats',
            'locationStats',
            'pageStats',
            'hourlyData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display detailed visitor list
     */
    public function list(Request $request)
    {
        $query = VisitorLog::query()->with('calonSiswa')->latest('visited_at');

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('visited_at', $request->date);
        }

        // Device filter
        if ($request->filled('device')) {
            $query->where('device_type', $request->device);
        }

        // Conversion filter
        if ($request->filled('converted')) {
            if ($request->converted === 'yes') {
                $query->where('converted_to_registration', true);
            } else {
                $query->where('converted_to_registration', false);
            }
        }

        // Search by IP, location, or address
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('subdistrict', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%");
            });
        }

        $visitors = $query->paginate(50)->withQueryString();

        return view('admin.visitor-logs.list', compact('visitors'));
    }

    /**
     * Show visitor map
     */
    public function map(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get visitors with coordinates
        $visitors = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('latitude', 'longitude', 'city', 'country', 'region', 'ip_address', 'visited_at', 
                     'device_type', 'accuracy', 'altitude', 'heading', 'speed', 'location_source', 
                     'address', 'district', 'subdistrict')
            ->get()
            ->map(function ($v) {
                return [
                    'lat' => (float) $v->latitude,
                    'lng' => (float) $v->longitude,
                    'city' => $v->city,
                    'region' => $v->region,
                    'country' => $v->country,
                    'device' => $v->device_type,
                    'time' => $v->visited_at->format('d M Y H:i'),
                    'accuracy' => $v->accuracy,
                    'altitude' => $v->altitude,
                    'heading' => $v->heading,
                    'speed' => $v->speed,
                    'source' => $v->location_source,
                    'address' => $v->address,
                    'district' => $v->district,
                    'subdistrict' => $v->subdistrict,
                ];
            });

        return view('admin.visitor-logs.map', compact('visitors', 'startDate', 'endDate'));
    }

    /**
     * Export visitor data
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $visitors = VisitorLog::whereBetween('visited_at', [$start, $end])
            ->orderBy('visited_at', 'desc')
            ->get();

        $filename = "visitor_logs_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($visitors) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Tanggal & Waktu',
                'IP Address',
                'Device',
                'Browser',
                'Platform',
                'Kota',
                'Negara',
                'Halaman',
                'Latitude',
                'Longitude',
            ]);

            foreach ($visitors as $visitor) {
                fputcsv($file, [
                    $visitor->visited_at->format('Y-m-d H:i:s'),
                    $visitor->ip_address,
                    $visitor->device_type,
                    $visitor->browser . ' ' . $visitor->browser_version,
                    $visitor->platform . ' ' . $visitor->platform_version,
                    $visitor->city,
                    $visitor->country,
                    $visitor->page_title,
                    $visitor->latitude,
                    $visitor->longitude,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old logs
     */
    public function clear(Request $request)
    {
        $days = $request->get('days', 90);
        
        $deleted = VisitorLog::where('visited_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.visitor-logs.index')
            ->with('success', "Berhasil menghapus {$deleted} log pengunjung yang lebih dari {$days} hari.");
    }

    /**
     * Display online visitors
     */
    public function online(Request $request)
    {
        $minutes = $request->get('minutes', 5);
        
        // Get online visitors
        $onlineVisitors = VisitorLog::online($minutes)
            ->with(['user', 'calonSiswa'])
            ->orderBy('last_activity_at', 'desc')
            ->get();
        
        // Group by session to get unique visitors
        $uniqueOnline = $onlineVisitors->unique('session_id');
        
        // Statistics
        $stats = [
            'total_online' => $uniqueOnline->count(),
            'identified' => $uniqueOnline->filter(fn($v) => $v->user_id || $v->calon_siswa_id)->count(),
            'anonymous' => $uniqueOnline->filter(fn($v) => !$v->user_id && !$v->calon_siswa_id)->count(),
            'mobile' => $uniqueOnline->where('device_type', 'mobile')->count(),
            'desktop' => $uniqueOnline->where('device_type', 'desktop')->count(),
            'tablet' => $uniqueOnline->where('device_type', 'tablet')->count(),
        ];

        return view('admin.visitor-logs.online', compact('uniqueOnline', 'stats', 'minutes'));
    }

    /**
     * API endpoint for online visitors (for AJAX refresh)
     */
    public function onlineData(Request $request)
    {
        $minutes = $request->get('minutes', 5);
        
        $onlineVisitors = VisitorLog::online($minutes)
            ->with(['user', 'calonSiswa'])
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->unique('session_id')
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'nama' => $v->visitor_name,
                    'is_identified' => $v->isIdentified(),
                    'visitor_type' => $v->user_id ? 'user' : ($v->calon_siswa_id ? 'pendaftar' : 'anonim'),
                    'device' => $v->device_type,
                    'browser' => $v->browser,
                    'platform' => $v->platform,
                    'current_url' => $v->current_url ?? $v->page_url,
                    'current_page' => $v->current_page_title ?? $v->page_title ?? '-',
                    'city' => $v->city,
                    'region' => $v->region,
                    'last_activity' => $v->last_activity_at?->diffForHumans() ?? '-',
                    'online_duration' => $v->online_duration,
                    'visited_at' => $v->visited_at->format('H:i'),
                ];
            })
            ->values();

        $stats = [
            'total_online' => $onlineVisitors->count(),
            'identified' => $onlineVisitors->where('is_identified', true)->count(),
            'anonymous' => $onlineVisitors->where('is_identified', false)->count(),
        ];

        return response()->json([
            'visitors' => $onlineVisitors,
            'stats' => $stats,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Mark visitor as offline (cleanup)
     */
    public function markOffline()
    {
        // Mark visitors as offline if no activity for more than 10 minutes
        $updated = VisitorLog::where('is_online', true)
            ->where('last_activity_at', '<', now()->subMinutes(10))
            ->update(['is_online' => false]);

        return response()->json([
            'success' => true,
            'marked_offline' => $updated,
        ]);
    }
}
