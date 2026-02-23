<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Services\NpsnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNpsnController extends Controller
{
    /**
     * Halaman utama Sync NPSN Asal Sekolah
     */
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::active()->first();
        $tahunList = TahunPelajaran::orderByDesc('is_active')->orderByDesc('nama')->get();
        $selectedTahunId = $request->tahun_pelajaran_id ?? $tahunAktif?->id;

        // Query pendaftar yang punya NPSN
        $query = CalonSiswa::query()
            ->whereNotNull('npsn_asal_sekolah')
            ->where('npsn_asal_sekolah', '!=', '');

        if ($selectedTahunId) {
            $query->where('tahun_pelajaran_id', $selectedTahunId);
        }

        // Filter status sync
        $filterStatus = $request->filter_status ?? 'belum';
        if ($filterStatus === 'belum') {
            // Belum sync = status_sekolah_asal OR bentuk_sekolah_asal kosong
            $query->where(function ($q) {
                $q->whereNull('status_sekolah_asal')
                  ->orWhereNull('bentuk_sekolah_asal')
                  ->orWhere('status_sekolah_asal', '')
                  ->orWhere('bentuk_sekolah_asal', '');
            });
        } elseif ($filterStatus === 'sudah') {
            // Sudah sync = keduanya terisi
            $query->whereNotNull('status_sekolah_asal')
                  ->where('status_sekolah_asal', '!=', '')
                  ->whereNotNull('bentuk_sekolah_asal')
                  ->where('bentuk_sekolah_asal', '!=', '');
        }
        // 'semua' = no additional filter

        $pendaftarList = $query->select([
                'id', 'nama_lengkap', 'nisn', 'npsn_asal_sekolah', 'nama_sekolah_asal',
                'status_sekolah_asal', 'bentuk_sekolah_asal', 'akreditasi_sekolah_asal',
                'alamat_sekolah_asal', 'kabupaten_sekolah_asal', 'provinsi_sekolah_asal',
            ])
            ->orderBy('nama_lengkap')
            ->get();

        // Statistik ringkas
        $baseQuery = CalonSiswa::query()
            ->whereNotNull('npsn_asal_sekolah')
            ->where('npsn_asal_sekolah', '!=', '');
        if ($selectedTahunId) {
            $baseQuery->where('tahun_pelajaran_id', $selectedTahunId);
        }

        $totalDenganNpsn = (clone $baseQuery)->count();
        $totalBelumSync = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('status_sekolah_asal')
              ->orWhereNull('bentuk_sekolah_asal')
              ->orWhere('status_sekolah_asal', '')
              ->orWhere('bentuk_sekolah_asal', '');
        })->count();
        $totalSudahSync = $totalDenganNpsn - $totalBelumSync;

        // Hitung pendaftar TANPA NPSN
        $tanpaNpsnQuery = CalonSiswa::query()
            ->where(function ($q) {
                $q->whereNull('npsn_asal_sekolah')
                  ->orWhere('npsn_asal_sekolah', '');
            });
        if ($selectedTahunId) {
            $tanpaNpsnQuery->where('tahun_pelajaran_id', $selectedTahunId);
        }
        $totalTanpaNpsn = $tanpaNpsnQuery->count();

        // NPSN unik yang belum sync
        $npsnUnikBelumSync = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('status_sekolah_asal')
              ->orWhereNull('bentuk_sekolah_asal')
              ->orWhere('status_sekolah_asal', '')
              ->orWhere('bentuk_sekolah_asal', '');
        })->distinct('npsn_asal_sekolah')->count('npsn_asal_sekolah');

        return view('admin.sync-npsn.index', compact(
            'tahunList', 'selectedTahunId', 'pendaftarList', 'filterStatus',
            'totalDenganNpsn', 'totalBelumSync', 'totalSudahSync', 'totalTanpaNpsn',
            'npsnUnikBelumSync'
        ));
    }

    /**
     * Sync satu NPSN (AJAX) - update semua pendaftar dengan NPSN yang sama
     */
    public function syncOne(Request $request)
    {
        $request->validate([
            'npsn' => 'required|string|size:8',
            'tahun_pelajaran_id' => 'nullable|string',
        ]);

        $npsn = $request->npsn;

        try {
            $npsnService = new NpsnService();
            $result = $npsnService->cekNpsn($npsn);

            if (!$result['success'] || !$result['data']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'NPSN tidak ditemukan di Kemendikdasmen.',
                    'npsn' => $npsn,
                ]);
            }

            $data = $result['data'];

            // Update semua pendaftar dengan NPSN ini
            $query = CalonSiswa::where('npsn_asal_sekolah', $npsn);
            if ($request->filled('tahun_pelajaran_id')) {
                $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
            }

            $updateData = [
                'nama_sekolah_asal' => $data['nama_sekolah'] ?? null,
                'alamat_sekolah_asal' => $data['alamat'] ?? null,
                'kelurahan_sekolah_asal' => $data['kelurahan'] ?? null,
                'kecamatan_sekolah_asal' => $data['kecamatan'] ?? null,
                'kabupaten_sekolah_asal' => $data['kabupaten'] ?? null,
                'provinsi_sekolah_asal' => $data['provinsi'] ?? null,
                'status_sekolah_asal' => $data['status'] ? strtoupper($data['status']) : null,
                'bentuk_sekolah_asal' => $data['bentuk_pendidikan'] ?? null,
                'akreditasi_sekolah_asal' => $data['akreditasi'] ?? null,
            ];

            $affected = $query->update($updateData);

            Log::info('SyncNpsn: Updated', [
                'npsn' => $npsn,
                'nama_sekolah' => $data['nama_sekolah'],
                'status' => $data['status'],
                'bentuk' => $data['bentuk_pendidikan'],
                'affected_rows' => $affected,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil sync NPSN {$npsn} — {$data['nama_sekolah']}",
                'npsn' => $npsn,
                'data' => $updateData,
                'affected' => $affected,
            ]);

        } catch (\Exception $e) {
            Log::error('SyncNpsn error: ' . $e->getMessage(), ['npsn' => $npsn]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync: ' . $e->getMessage(),
                'npsn' => $npsn,
            ], 500);
        }
    }

    /**
     * Get daftar NPSN unik yang belum di-sync (untuk batch AJAX)
     */
    public function getNpsnList(Request $request)
    {
        $query = CalonSiswa::query()
            ->whereNotNull('npsn_asal_sekolah')
            ->where('npsn_asal_sekolah', '!=', '')
            ->where(function ($q) {
                $q->whereNull('status_sekolah_asal')
                  ->orWhereNull('bentuk_sekolah_asal')
                  ->orWhere('status_sekolah_asal', '')
                  ->orWhere('bentuk_sekolah_asal', '');
            });

        if ($request->filled('tahun_pelajaran_id')) {
            $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
        }

        $npsnList = $query->select('npsn_asal_sekolah', DB::raw('COUNT(*) as jumlah'), DB::raw('MIN(nama_sekolah_asal) as nama_sekolah'))
            ->groupBy('npsn_asal_sekolah')
            ->orderBy('npsn_asal_sekolah')
            ->get()
            ->map(fn ($row) => [
                'npsn' => $row->npsn_asal_sekolah,
                'jumlah' => $row->jumlah,
                'nama_sekolah' => $row->nama_sekolah,
            ]);

        return response()->json([
            'success' => true,
            'data' => $npsnList,
            'total' => $npsnList->count(),
        ]);
    }
}
