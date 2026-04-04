<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Berita;
use App\Models\CalonSiswa;
use App\Models\User;
use App\Models\Verifikator;
use App\Support\AdminPpdbContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $context = AdminPpdbContext::resolve();

        $stats = $this->buildStats($context, $isAdmin);

        $recentPendaftar = $this->baseQuery($context)
            ->with('dokumen')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentLogs = [];
        if ($isAdmin && class_exists(ActivityLog::class) && Schema::hasTable('activity_logs')) {
            $recentLogs = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        $chartData = $this->buildChartData($context);

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentPendaftar' => $recentPendaftar,
            'recentLogs' => $recentLogs,
            'chartData' => $chartData,
            'isAdmin' => $isAdmin,
            'contextInfo' => $this->buildContextInfo($context),
        ]);
    }

    public function getStats()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $context = AdminPpdbContext::resolve();

        return response()->json([
            'stats' => $this->buildStats($context, $isAdmin),
            'chartData' => $this->buildChartData($context),
            'contextInfo' => $this->buildContextInfo($context),
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    private function baseQuery(array $context): Builder
    {
        return CalonSiswa::query()
            ->when($context['selectedTahun'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.tahun_pelajaran_id', $context['selectedTahun']->id);
            })
            ->when($context['jalurFilterId'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.jalur_pendaftaran_id', $context['jalurFilterId']);
            })
            ->when($context['gelombangFilterId'], function (Builder $q) use ($context) {
                $q->where('calon_siswas.gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
    }

    private function buildStats(array $context, bool $isAdmin): array
    {
        $stats = [];

        $totalPendaftar = $this->baseQuery($context)->count();
        $mendapatkanNomorTes = $this->baseQuery($context)->whereNotNull('nomor_tes')->count();
        $belumLengkap = $this->baseQuery($context)->whereNull('nomor_tes')->count();
        $siapVerifikasi = $this->baseQuery($context)->where(function ($q) {
            $this->applySiapVerifikasiQuery($q);
        })->count();
        $hanyaMendaftar = $this->baseQuery($context)
            ->whereNull('nomor_tes')
            ->whereDoesntHave('dokumen')
            ->count();

        $currentJalurCount = $context['selectedJalur']
            ? CalonSiswa::where('jalur_pendaftaran_id', $context['selectedJalur']->id)->count()
            : $totalPendaftar;
        $currentGelombangCount = $context['selectedGelombang']
            ? CalonSiswa::where('gelombang_pendaftaran_id', $context['selectedGelombang']->id)->count()
            : $this->baseQuery($context)->count();
        $tahunCount = $context['selectedTahun']
            ? CalonSiswa::where('tahun_pelajaran_id', $context['selectedTahun']->id)->count()
            : $totalPendaftar;

        $stats = [
            'total_pendaftar' => $totalPendaftar,
            'pendaftar_jalur' => $currentJalurCount,
            'pendaftar_gelombang' => $currentGelombangCount,
            'pendaftar_tahun' => $tahunCount,
            'pendaftar_baru' => $belumLengkap,
            'pendaftar_baru_jalur' => $this->baseQuery($context)->whereNull('nomor_tes')->count(),
            'pendaftar_baru_gelombang' => $context['selectedGelombang']
                ? CalonSiswa::where('gelombang_pendaftaran_id', $context['selectedGelombang']->id)->whereNull('nomor_tes')->count()
                : $belumLengkap,
            'pendaftar_baru_tahun' => $context['selectedTahun']
                ? CalonSiswa::where('tahun_pelajaran_id', $context['selectedTahun']->id)->whereNull('nomor_tes')->count()
                : $belumLengkap,
            'terverifikasi' => $mendapatkanNomorTes,
            'terverifikasi_jalur' => $this->baseQuery($context)->whereNotNull('nomor_tes')->count(),
            'terverifikasi_gelombang' => $context['selectedGelombang']
                ? CalonSiswa::where('gelombang_pendaftaran_id', $context['selectedGelombang']->id)->whereNotNull('nomor_tes')->count()
                : $mendapatkanNomorTes,
            'terverifikasi_tahun' => $context['selectedTahun']
                ? CalonSiswa::where('tahun_pelajaran_id', $context['selectedTahun']->id)->whereNotNull('nomor_tes')->count()
                : $mendapatkanNomorTes,
            'siap_verifikasi' => $siapVerifikasi,
            'siap_verifikasi_jalur' => $siapVerifikasi,
            'siap_verifikasi_gelombang' => $context['selectedGelombang']
                ? CalonSiswa::where('gelombang_pendaftaran_id', $context['selectedGelombang']->id)->where(function ($q) {
                    $this->applySiapVerifikasiQuery($q);
                })->count()
                : $siapVerifikasi,
            'siap_verifikasi_tahun' => $context['selectedTahun']
                ? CalonSiswa::where('tahun_pelajaran_id', $context['selectedTahun']->id)->where(function ($q) {
                    $this->applySiapVerifikasiQuery($q);
                })->count()
                : $siapVerifikasi,
            'hanya_mendaftar' => $hanyaMendaftar,
            'hanya_mendaftar_jalur' => $hanyaMendaftar,
            'hanya_mendaftar_gelombang' => $context['selectedGelombang']
                ? CalonSiswa::where('gelombang_pendaftaran_id', $context['selectedGelombang']->id)
                    ->whereNull('nomor_tes')
                    ->whereDoesntHave('dokumen')
                    ->count()
                : $hanyaMendaftar,
            'hanya_mendaftar_tahun' => $context['selectedTahun']
                ? CalonSiswa::where('tahun_pelajaran_id', $context['selectedTahun']->id)
                    ->whereNull('nomor_tes')
                    ->whereDoesntHave('dokumen')
                    ->count()
                : $hanyaMendaftar,
        ];

        if ($isAdmin) {
            $stats['ditolak'] = $this->baseQuery($context)->where('status_admisi', 'ditolak')->count();
            $stats['total_berita'] = Berita::count();
            $stats['total_verifikator'] = Verifikator::count();
            $stats['total_user'] = User::count();
        }

        return $stats;
    }

    private function buildChartData(array $context): array
    {
        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData['labels'][] = Carbon::now()->subDays($i)->format('d M');
            $chartData['data'][] = $this->baseQuery($context)
                ->whereDate('created_at', $date)
                ->count();
        }

        return $chartData;
    }

    private function buildContextInfo(array $context): array
    {
        $jalur = $context['selectedJalur'];
        $gelombang = $context['selectedGelombang'];
        $tahun = $context['selectedTahun'];

        return [
            'tahun' => $tahun?->nama ?? '-',
            'jalur' => $jalur?->nama ?? 'Semua Jalur',
            'gelombang' => $gelombang?->nama ?? 'Semua Gelombang',
            'jalur_is_default' => $context['isDefaultJalur'],
            'gelombang_is_default' => $context['isDefaultGelombang'],
            'mode' => $jalur
                ? ($gelombang ? 'jalur_dan_gelombang' : 'jalur')
                : 'tahun',
        ];
    }

    private function applySiapVerifikasiQuery($q): void
    {
        $q->whereNull('nomor_tes')
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'rapor_sem_1');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'rapor_sem_2');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'rapor_sem_3');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'rapor_sem_4');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'rapor_sem_5');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'kk');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'foto');
            })
            ->whereHas('dokumen', function ($d) {
                $d->where('jenis_dokumen', 'kartu_pelajar');
            });
    }
}
