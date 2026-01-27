<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\Berita;
use App\Models\Verifikator;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        
        // Statistics for all roles
        $totalPendaftar = CalonSiswa::count();
        
        // Mendapatkan Nomor Tes - yang sudah punya nomor_tes
        $mendapatkanNomorTes = CalonSiswa::whereNotNull('nomor_tes')->count();
        $mendapatkanNomorTesReguler = CalonSiswa::whereNotNull('nomor_tes')->where('pilihan_program', 'Reguler')->count();
        $mendapatkanNomorTesAsrama = CalonSiswa::whereNotNull('nomor_tes')->where('pilihan_program', 'Asrama')->count();
        $mendapatkanNomorTesBelumMemilih = CalonSiswa::whereNotNull('nomor_tes')->whereNull('pilihan_program')->count();
        
        // Belum Lengkap - semua yang belum punya nomor tes (Total - Mendapatkan No.Tes)
        $belumLengkap = CalonSiswa::whereNull('nomor_tes')->count();
        $belumLengkapReguler = CalonSiswa::whereNull('nomor_tes')->where('pilihan_program', 'Reguler')->count();
        $belumLengkapAsrama = CalonSiswa::whereNull('nomor_tes')->where('pilihan_program', 'Asrama')->count();
        $belumLengkapBelumMemilih = CalonSiswa::whereNull('nomor_tes')->whereNull('pilihan_program')->count();
        
        // Siap Verifikasi: sudah upload Rapor1-5, KK, Foto, Kartu Pelajar, belum dapat nomor tes
        $siapVerifikasiQuery = function($q) {
            $q->whereNull('nomor_tes')
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_1'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_2'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_3'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_4'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_5'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kk'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kartu_pelajar'); });
        };
        
        $siapVerifikasi = CalonSiswa::where($siapVerifikasiQuery)->count();
        $siapVerifikasiReguler = CalonSiswa::where('pilihan_program', 'Reguler')->where($siapVerifikasiQuery)->count();
        $siapVerifikasiAsrama = CalonSiswa::where('pilihan_program', 'Asrama')->where($siapVerifikasiQuery)->count();
        $siapVerifikasiBelumMemilih = CalonSiswa::whereNull('pilihan_program')->where($siapVerifikasiQuery)->count();
        
        // Hanya Mendaftar - hanya register tanpa mengisi nilai atau upload foto
        $hanyaMendaftar = CalonSiswa::whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarReguler = CalonSiswa::where('pilihan_program', 'Reguler')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarAsrama = CalonSiswa::where('pilihan_program', 'Asrama')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarBelumMemilih = CalonSiswa::whereNull('pilihan_program')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        
        $stats = [
            'total_pendaftar' => $totalPendaftar,
            'pendaftar_reguler' => CalonSiswa::where('pilihan_program', 'Reguler')->count(),
            'pendaftar_asrama' => CalonSiswa::where('pilihan_program', 'Asrama')->count(),
            'pendaftar_belum_memilih' => CalonSiswa::whereNull('pilihan_program')->count(),
            // Belum lengkap
            'pendaftar_baru' => $belumLengkap,
            'pendaftar_baru_reguler' => $belumLengkapReguler,
            'pendaftar_baru_asrama' => $belumLengkapAsrama,
            'pendaftar_baru_belum_memilih' => $belumLengkapBelumMemilih,
            // Mendapatkan Nomor Tes
            'terverifikasi' => $mendapatkanNomorTes,
            'terverifikasi_reguler' => $mendapatkanNomorTesReguler,
            'terverifikasi_asrama' => $mendapatkanNomorTesAsrama,
            'terverifikasi_belum_memilih' => $mendapatkanNomorTesBelumMemilih,
            // Siap Verifikasi
            'siap_verifikasi' => $siapVerifikasi,
            'siap_verifikasi_reguler' => $siapVerifikasiReguler,
            'siap_verifikasi_asrama' => $siapVerifikasiAsrama,
            'siap_verifikasi_belum_memilih' => $siapVerifikasiBelumMemilih,
            // Hanya Mendaftar
            'hanya_mendaftar' => $hanyaMendaftar,
            'hanya_mendaftar_reguler' => $hanyaMendaftarReguler,
            'hanya_mendaftar_asrama' => $hanyaMendaftarAsrama,
            'hanya_mendaftar_belum_memilih' => $hanyaMendaftarBelumMemilih,
        ];

        // Admin-only statistics
        if ($isAdmin) {
            $stats['ditolak'] = CalonSiswa::where('status_admisi', 'ditolak')->count();
            $stats['total_berita'] = Berita::count();
            $stats['total_verifikator'] = Verifikator::count();
            $stats['total_user'] = User::count();
        }

        // Recent registrations
        $recentPendaftar = CalonSiswa::with('dokumen')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent activity logs (admin only)
        $recentLogs = [];
        if ($isAdmin && class_exists(ActivityLog::class) && Schema::hasTable('activity_logs')) {
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
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = Carbon::now()->subDays($i)->format('d M');
            $chartData['data'][] = CalonSiswa::whereDate('created_at', $date)->count();
        }

        return view('admin.dashboard', compact('stats', 'recentPendaftar', 'recentLogs', 'chartData', 'isAdmin'));
    }

    /**
     * Get realtime stats for AJAX refresh
     */
    public function getStats()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        
        $totalPendaftar = CalonSiswa::count();
        
        // Mendapatkan Nomor Tes
        $mendapatkanNomorTes = CalonSiswa::whereNotNull('nomor_tes')->count();
        $mendapatkanNomorTesReguler = CalonSiswa::whereNotNull('nomor_tes')->where('pilihan_program', 'Reguler')->count();
        $mendapatkanNomorTesAsrama = CalonSiswa::whereNotNull('nomor_tes')->where('pilihan_program', 'Asrama')->count();
        $mendapatkanNomorTesBelumMemilih = CalonSiswa::whereNotNull('nomor_tes')->whereNull('pilihan_program')->count();
        
        // Belum Lengkap - semua yang belum punya nomor tes
        $belumLengkap = CalonSiswa::whereNull('nomor_tes')->count();
        $belumLengkapReguler = CalonSiswa::whereNull('nomor_tes')->where('pilihan_program', 'Reguler')->count();
        $belumLengkapAsrama = CalonSiswa::whereNull('nomor_tes')->where('pilihan_program', 'Asrama')->count();
        $belumLengkapBelumMemilih = CalonSiswa::whereNull('nomor_tes')->whereNull('pilihan_program')->count();
        
        // Siap Verifikasi
        $siapVerifikasiQuery = function($q) {
            $q->whereNull('nomor_tes')
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_1'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_2'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_3'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_4'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'rapor_5'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kk'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); })
              ->whereHas('dokumen', function($d) { $d->where('jenis_dokumen', 'kartu_pelajar'); });
        };
        
        $siapVerifikasi = CalonSiswa::where($siapVerifikasiQuery)->count();
        $siapVerifikasiReguler = CalonSiswa::where('pilihan_program', 'Reguler')->where($siapVerifikasiQuery)->count();
        $siapVerifikasiAsrama = CalonSiswa::where('pilihan_program', 'Asrama')->where($siapVerifikasiQuery)->count();
        $siapVerifikasiBelumMemilih = CalonSiswa::whereNull('pilihan_program')->where($siapVerifikasiQuery)->count();
        
        // Hanya Mendaftar
        $hanyaMendaftar = CalonSiswa::whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarReguler = CalonSiswa::where('pilihan_program', 'Reguler')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarAsrama = CalonSiswa::where('pilihan_program', 'Asrama')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        $hanyaMendaftarBelumMemilih = CalonSiswa::whereNull('pilihan_program')
            ->whereNull('nomor_tes')
            ->where(function($q) {
                $q->whereDoesntHave('nilaiRapor')
                  ->orWhereDoesntHave('dokumen', function($d) { $d->where('jenis_dokumen', 'foto'); });
            })->count();
        
        $stats = [
            'total_pendaftar' => $totalPendaftar,
            'pendaftar_reguler' => CalonSiswa::where('pilihan_program', 'Reguler')->count(),
            'pendaftar_asrama' => CalonSiswa::where('pilihan_program', 'Asrama')->count(),
            'pendaftar_belum_memilih' => CalonSiswa::whereNull('pilihan_program')->count(),
            // Belum lengkap
            'pendaftar_baru' => $belumLengkap,
            'pendaftar_baru_reguler' => $belumLengkapReguler,
            'pendaftar_baru_asrama' => $belumLengkapAsrama,
            'pendaftar_baru_belum_memilih' => $belumLengkapBelumMemilih,
            // Mendapatkan Nomor Tes
            'terverifikasi' => $mendapatkanNomorTes,
            'terverifikasi_reguler' => $mendapatkanNomorTesReguler,
            'terverifikasi_asrama' => $mendapatkanNomorTesAsrama,
            'terverifikasi_belum_memilih' => $mendapatkanNomorTesBelumMemilih,
            // Siap Verifikasi
            'siap_verifikasi' => $siapVerifikasi,
            'siap_verifikasi_reguler' => $siapVerifikasiReguler,
            'siap_verifikasi_asrama' => $siapVerifikasiAsrama,
            'siap_verifikasi_belum_memilih' => $siapVerifikasiBelumMemilih,
            // Hanya Mendaftar
            'hanya_mendaftar' => $hanyaMendaftar,
            'hanya_mendaftar_reguler' => $hanyaMendaftarReguler,
            'hanya_mendaftar_asrama' => $hanyaMendaftarAsrama,
            'hanya_mendaftar_belum_memilih' => $hanyaMendaftarBelumMemilih,
        ];

        if ($isAdmin) {
            $stats['ditolak'] = CalonSiswa::where('status_admisi', 'ditolak')->count();
            $stats['total_berita'] = Berita::count();
            $stats['total_verifikator'] = Verifikator::count();
            $stats['total_user'] = User::count();
        }

        // Chart data (last 7 days)
        $chartData = [
            'labels' => [],
            'data' => [],
        ];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = Carbon::now()->subDays($i)->format('d M');
            $chartData['data'][] = CalonSiswa::whereDate('created_at', $date)->count();
        }

        return response()->json([
            'stats' => $stats,
            'chartData' => $chartData,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }
}
