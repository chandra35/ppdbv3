<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\SekolahSettings;
use App\Services\KopSuratService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakRuangController extends Controller
{
    protected $kopSuratService;

    public function __construct(KopSuratService $kopSuratService)
    {
        $this->kopSuratService = $kopSuratService;
    }

    /**
     * Display room assignment settings and preview
     */
    public function index(Request $request)
    {
        // Get tahun pelajaran
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
        
        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        // Get jalur and gelombang for filters
        $jalurList = $tahunAktif 
            ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() 
            : collect();
        
        $gelombangList = $tahunAktif 
            ? GelombangPendaftaran::whereHas('jalur', function($q) use ($tahunAktif) {
                $q->where('tahun_pelajaran_id', $tahunAktif->id);
            })->get() 
            : collect();

        // Get count of eligible peserta (finalisasi + nomor_tes)
        $pesertaQuery = CalonSiswa::where('is_finalisasi', true)
            ->whereNotNull('nomor_tes')
            ->where('nomor_tes', '!=', '');

        if ($tahunAktif) {
            $pesertaQuery->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        $totalPeserta = $pesertaQuery->count();

        // Get settings from session or defaults
        $settings = session('cetak_ruang_settings', [
            'peserta_per_ruang' => 20,
            'prefix_ruang' => 'Ruang',
            'jalur_id' => null,
            'gelombang_id' => null,
            'urutan' => 'nomor_tes',
        ]);

        return view('admin.cetak-ruang.index', compact(
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'totalPeserta',
            'settings'
        ));
    }

    /**
     * Preview room distribution
     */
    public function preview(Request $request)
    {
        $request->validate([
            'peserta_per_ruang' => 'required|integer|min:1|max:100',
            'prefix_ruang' => 'required|string|max:50',
            'urutan' => 'required|in:nomor_tes,nama,tanggal_finalisasi',
        ]);

        // Save settings to session
        session(['cetak_ruang_settings' => $request->only([
            'peserta_per_ruang', 
            'prefix_ruang', 
            'jalur_id', 
            'gelombang_id',
            'urutan',
            'tahun_pelajaran_id'
        ])]);

        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        // Build query
        $pesertaList = $this->getPesertaList($request, $tahunAktif);

        // Distribute to rooms
        $rooms = $this->distributeToRooms(
            $pesertaList, 
            $request->peserta_per_ruang, 
            $request->prefix_ruang
        );

        // Get filter info
        $jalurList = $tahunAktif 
            ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() 
            : collect();
        
        $gelombangList = $tahunAktif 
            ? GelombangPendaftaran::whereHas('jalur', function($q) use ($tahunAktif) {
                $q->where('tahun_pelajaran_id', $tahunAktif->id);
            })->get() 
            : collect();

        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

        $totalPeserta = count($pesertaList);

        $settings = $request->only([
            'peserta_per_ruang', 
            'prefix_ruang', 
            'jalur_id', 
            'gelombang_id',
            'urutan',
            'tahun_pelajaran_id'
        ]);

        return view('admin.cetak-ruang.index', compact(
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'totalPeserta',
            'settings',
            'rooms'
        ));
    }

    /**
     * Print Daftar Hadir (Attendance List)
     */
    public function printDaftarHadir(Request $request)
    {
        $settings = session('cetak_ruang_settings', []);
        
        // Convert to array if stored as object
        if (is_object($settings)) {
            $settings = (array) $settings;
        }
        
        if (empty($settings)) {
            return redirect()->route('admin.cetak-ruang.index')
                ->with('error', 'Silakan lakukan preview terlebih dahulu.');
        }

        $tahunAktif = isset($settings['tahun_pelajaran_id']) 
            ? TahunPelajaran::find($settings['tahun_pelajaran_id'])
            : TahunPelajaran::where('is_active', true)->first();

        $pesertaList = $this->getPesertaList($settings, $tahunAktif);
        
        $rooms = $this->distributeToRooms(
            $pesertaList, 
            $settings['peserta_per_ruang'], 
            $settings['prefix_ruang']
        );

        $sekolah = SekolahSettings::first();
        
        // Generate Kop HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolah, true);

        // Filter specific room if requested
        if ($request->ruang) {
            $rooms = collect($rooms)->filter(function($room) use ($request) {
                return $room['nama'] === $request->ruang;
            })->values()->all();
        }

        $pdf = Pdf::loadView('admin.cetak-ruang.pdf.daftar-hadir', compact(
            'rooms',
            'sekolah',
            'tahunAktif',
            'settings',
            'kopHtml'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('daftar-hadir-ruang-ujian.pdf');
    }

    /**
     * Print Daftar Peserta per Ruang (untuk ditempel di ruangan)
     */
    public function printDaftarPeserta(Request $request)
    {
        $settings = session('cetak_ruang_settings', []);
        
        // Convert to array if stored as object
        if (is_object($settings)) {
            $settings = (array) $settings;
        }
        
        if (empty($settings)) {
            return redirect()->route('admin.cetak-ruang.index')
                ->with('error', 'Silakan lakukan preview terlebih dahulu.');
        }

        $tahunAktif = isset($settings['tahun_pelajaran_id']) 
            ? TahunPelajaran::find($settings['tahun_pelajaran_id'])
            : TahunPelajaran::where('is_active', true)->first();

        $pesertaList = $this->getPesertaList($settings, $tahunAktif);
        
        $rooms = $this->distributeToRooms(
            $pesertaList, 
            $settings['peserta_per_ruang'], 
            $settings['prefix_ruang']
        );

        $sekolah = SekolahSettings::first();
        
        // Generate Kop HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolah, true);

        // Filter specific room if requested
        if ($request->ruang) {
            $rooms = collect($rooms)->filter(function($room) use ($request) {
                return $room['nama'] === $request->ruang;
            })->values()->all();
        }

        $pdf = Pdf::loadView('admin.cetak-ruang.pdf.daftar-peserta', compact(
            'rooms',
            'sekolah',
            'tahunAktif',
            'settings',
            'kopHtml'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('daftar-peserta-ruang-ujian.pdf');
    }

    /**
     * Print Nama Ruang (untuk ditempel di pintu ruangan)
     */
    public function printNamaRuang(Request $request)
    {
        $settings = session('cetak_ruang_settings', []);
        
        // Convert to array if stored as object
        if (is_object($settings)) {
            $settings = (array) $settings;
        }
        
        if (empty($settings)) {
            return redirect()->route('admin.cetak-ruang.index')
                ->with('error', 'Silakan lakukan preview terlebih dahulu.');
        }

        $tahunAktif = isset($settings['tahun_pelajaran_id']) 
            ? TahunPelajaran::find($settings['tahun_pelajaran_id'])
            : TahunPelajaran::where('is_active', true)->first();

        $pesertaList = $this->getPesertaList($settings, $tahunAktif);
        
        $rooms = $this->distributeToRooms(
            $pesertaList, 
            $settings['peserta_per_ruang'], 
            $settings['prefix_ruang']
        );

        $sekolah = SekolahSettings::first();

        // Filter specific room if requested
        if ($request->ruang) {
            $rooms = collect($rooms)->filter(function($room) use ($request) {
                return $room['nama'] === $request->ruang;
            })->values()->all();
        }

        $pdf = Pdf::loadView('admin.cetak-ruang.pdf.nama-ruang', compact(
            'rooms',
            'sekolah',
            'tahunAktif',
            'settings'
        ));

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('nama-ruang-ujian.pdf');
    }

    /**
     * Get peserta list based on settings
     */
    private function getPesertaList($request, $tahunAktif)
    {
        $query = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('is_finalisasi', true)
            ->whereNotNull('nomor_tes')
            ->where('nomor_tes', '!=', '');

        if ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        // Filter jalur
        $jalurId = is_object($request) && isset($request->jalur_id) ? $request->jalur_id : ($request['jalur_id'] ?? null);
        if ($jalurId) {
            $query->where('jalur_pendaftaran_id', $jalurId);
        }

        // Filter gelombang
        $gelombangId = is_object($request) && isset($request->gelombang_id) ? $request->gelombang_id : ($request['gelombang_id'] ?? null);
        if ($gelombangId) {
            $query->where('gelombang_pendaftaran_id', $gelombangId);
        }

        // Order by
        $urutan = is_object($request) && isset($request->urutan) ? $request->urutan : ($request['urutan'] ?? 'nomor_tes');
        switch ($urutan) {
            case 'nama':
                $query->orderBy('nama_lengkap', 'asc');
                break;
            case 'tanggal_finalisasi':
                $query->orderBy('tanggal_finalisasi', 'asc');
                break;
            case 'nomor_tes':
            default:
                $query->orderBy('nomor_tes', 'asc');
                break;
        }

        return $query->get();
    }

    /**
     * Distribute peserta to rooms
     */
    private function distributeToRooms($pesertaList, $perRoom, $prefix)
    {
        $rooms = [];
        $roomNumber = 1;
        $currentRoom = [];

        foreach ($pesertaList as $index => $peserta) {
            $currentRoom[] = $peserta;

            // When room is full or last peserta
            if (count($currentRoom) >= $perRoom || $index === count($pesertaList) - 1) {
                $rooms[] = [
                    'nomor' => $roomNumber,
                    'nama' => $prefix . ' ' . $roomNumber,
                    'peserta' => $currentRoom,
                    'jumlah' => count($currentRoom),
                ];
                $currentRoom = [];
                $roomNumber++;
            }
        }

        return $rooms;
    }
}
