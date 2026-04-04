<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Services\NpsnService;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNpsnController extends Controller
{
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $query = CalonSiswa::query()
            ->whereNotNull('npsn_asal_sekolah')
            ->where('npsn_asal_sekolah', '!=', '');

        $this->applyContextFilters($query, $context);

        $filterStatus = $request->filter_status ?? 'belum';
        if ($filterStatus === 'belum') {
            $query->where(function ($q) {
                $q->whereNull('status_sekolah_asal')
                    ->orWhereNull('bentuk_sekolah_asal')
                    ->orWhere('status_sekolah_asal', '')
                    ->orWhere('bentuk_sekolah_asal', '');
            });
        } elseif ($filterStatus === 'sudah') {
            $query->whereNotNull('status_sekolah_asal')
                ->where('status_sekolah_asal', '!=', '')
                ->whereNotNull('bentuk_sekolah_asal')
                ->where('bentuk_sekolah_asal', '!=', '');
        }

        $pendaftarList = $query->select([
            'id',
            'nama_lengkap',
            'nisn',
            'npsn_asal_sekolah',
            'nama_sekolah_asal',
            'status_sekolah_asal',
            'bentuk_sekolah_asal',
            'akreditasi_sekolah_asal',
            'alamat_sekolah_asal',
            'kabupaten_sekolah_asal',
            'provinsi_sekolah_asal',
        ])->orderBy('nama_lengkap')->get();

        $baseQuery = CalonSiswa::query()
            ->whereNotNull('npsn_asal_sekolah')
            ->where('npsn_asal_sekolah', '!=', '');
        $this->applyContextFilters($baseQuery, $context);

        $totalDenganNpsn = (clone $baseQuery)->count();
        $totalBelumSync = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('status_sekolah_asal')
                ->orWhereNull('bentuk_sekolah_asal')
                ->orWhere('status_sekolah_asal', '')
                ->orWhere('bentuk_sekolah_asal', '');
        })->count();
        $totalSudahSync = $totalDenganNpsn - $totalBelumSync;

        $tanpaNpsnQuery = CalonSiswa::query()
            ->where(function ($q) {
                $q->whereNull('npsn_asal_sekolah')
                    ->orWhere('npsn_asal_sekolah', '');
            });
        $this->applyContextFilters($tanpaNpsnQuery, $context);
        $totalTanpaNpsn = $tanpaNpsnQuery->count();

        $npsnUnikBelumSync = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('status_sekolah_asal')
                ->orWhereNull('bentuk_sekolah_asal')
                ->orWhere('status_sekolah_asal', '')
                ->orWhere('bentuk_sekolah_asal', '');
        })->distinct('npsn_asal_sekolah')->count('npsn_asal_sekolah');

        $contextInfo = [
            'tahun' => $context['selectedTahun']?->nama ?? '-',
            'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
            'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
        ];

        return view('admin.sync-npsn.index', [
            'tahunList' => $context['tahunPelajarans'],
            'jalurList' => $context['jalurs'],
            'gelombangList' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'pendaftarList' => $pendaftarList,
            'filterStatus' => $filterStatus,
            'totalDenganNpsn' => $totalDenganNpsn,
            'totalBelumSync' => $totalBelumSync,
            'totalSudahSync' => $totalSudahSync,
            'totalTanpaNpsn' => $totalTanpaNpsn,
            'npsnUnikBelumSync' => $npsnUnikBelumSync,
            'contextInfo' => $contextInfo,
        ]);
    }

    public function syncOne(Request $request)
    {
        $request->validate([
            'npsn' => 'required|string|size:8',
            'tahun_pelajaran_id' => 'nullable|string',
            'jalur_id' => 'nullable|string',
            'gelombang_id' => 'nullable|string',
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
            $context = AdminPpdbContext::resolve(
                $request->get('tahun_pelajaran_id'),
                $request->get('jalur_id'),
                $request->get('gelombang_id')
            );

            $query = CalonSiswa::where('npsn_asal_sekolah', $npsn);
            $this->applyContextFilters($query, $context);

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
                'nama_sekolah' => $data['nama_sekolah'] ?? null,
                'status' => $data['status'] ?? null,
                'bentuk' => $data['bentuk_pendidikan'] ?? null,
                'affected_rows' => $affected,
                'tahun_id' => $context['selectedTahunIdInput'],
                'jalur_id' => $context['selectedJalurIdInput'],
                'gelombang_id' => $context['selectedGelombangIdInput'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil sync NPSN {$npsn} - {$data['nama_sekolah']}",
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

        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $this->applyContextFilters($query, $context);

        $npsnList = $query->select(
            'npsn_asal_sekolah',
            DB::raw('COUNT(*) as jumlah'),
            DB::raw('MIN(nama_sekolah_asal) as nama_sekolah')
        )->groupBy('npsn_asal_sekolah')
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

    private function applyContextFilters($query, array $context): void
    {
        if ($context['selectedTahun']) {
            $query->where('tahun_pelajaran_id', $context['selectedTahun']->id);
        }

        if ($context['jalurFilterId']) {
            $query->where('jalur_pendaftaran_id', $context['jalurFilterId']);
        }

        if ($context['gelombangFilterId']) {
            $query->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
        }
    }
}
