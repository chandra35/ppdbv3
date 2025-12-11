<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\Berita;
use App\Models\Verifikator;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics untuk operator
        $stats = [
            'total_pendaftar' => CalonSiswa::count(),
            'pendaftar_baru' => CalonSiswa::where('status_verifikasi', 'pending')->count(),
            'terverifikasi' => CalonSiswa::where('status_verifikasi', 'verified')->count(),
            'diterima' => CalonSiswa::where('status_admisi', 'diterima')->count(),
            'ditolak' => CalonSiswa::where('status_admisi', 'ditolak')->count(),
        ];

        // Recent registrations
        $recentPendaftar = CalonSiswa::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent activity logs
        $recentLogs = [];
        if (class_exists(ActivityLog::class) && Schema::hasTable('activity_logs')) {
            $recentLogs = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Pendaftar per day (last 7 days)
        $chartData = [
            'labels' => [],
            'data' => [],
        ];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData['labels'][] = $date->format('d M');
            $chartData['data'][] = CalonSiswa::whereDate('created_at', $date)->count();
        }

        return view('operator.dashboard', compact('stats', 'recentPendaftar', 'recentLogs', 'chartData'));
    }
}
