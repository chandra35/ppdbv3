<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\SekolahSettings;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Services\KopSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'tanggal_ujian' => null,
            'waktu_mulai' => null,
            'waktu_selesai' => null,
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
            'tahun_pelajaran_id',
            'tanggal_ujian',
            'waktu_mulai',
            'waktu_selesai'
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
            'tahun_pelajaran_id',
            'tanggal_ujian',
            'waktu_mulai',
            'waktu_selesai'
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
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '512M');
        
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
        
        // Free memory from pesertaList as it's no longer needed
        unset($pesertaList);

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
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '512M');
        
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
        
        // Free memory
        unset($pesertaList);

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
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '512M');
        
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
        
        // Free memory
        unset($pesertaList);

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
        // Only select needed columns to reduce memory usage
        $query = CalonSiswa::select([
                'id',
                'nomor_tes',
                'nisn',
                'nama_lengkap',
                'jenis_kelamin',
                'nama_sekolah_asal',
                'jalur_pendaftaran_id',
                'gelombang_pendaftaran_id'
            ])
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

    /**
     * Save and lock room distribution as Sesi Ujian
     */
    public function saveAndLock(Request $request)
    {
        $request->validate([
            'nama_sesi' => 'required|string|max:100',
            'tanggal_ujian' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'peserta_per_ruang' => 'required|integer|min:1|max:100',
            'prefix_ruang' => 'required|string|max:50',
            'urutan' => 'required|in:nomor_tes,nama,tanggal_finalisasi',
        ]);

        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran tidak ditemukan.');
        }

        // Get peserta list
        $pesertaList = $this->getPesertaList($request, $tahunAktif);

        if ($pesertaList->isEmpty()) {
            return back()->with('error', 'Tidak ada peserta yang memenuhi kriteria.');
        }

        // Distribute to rooms
        $rooms = $this->distributeToRooms(
            $pesertaList, 
            $request->peserta_per_ruang, 
            $request->prefix_ruang
        );

        try {
            DB::beginTransaction();

            // Create Sesi Ujian
            $sesiUjian = SesiUjian::create([
                'tahun_pelajaran_id' => $tahunAktif->id,
                'jalur_pendaftaran_id' => $request->jalur_id ?: null,
                'gelombang_pendaftaran_id' => $request->gelombang_id ?: null,
                'nama' => $request->nama_sesi,
                'tanggal' => $request->tanggal_ujian,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'peserta_per_ruang' => $request->peserta_per_ruang,
                'prefix_ruang' => $request->prefix_ruang,
                'urutan_peserta' => $request->urutan,
                'status' => SesiUjian::STATUS_LOCKED,
                'created_by' => auth()->id(),
                'locked_by' => auth()->id(),
                'locked_at' => now(),
            ]);

            // Create Ruang Ujian and Peserta Ruang
            foreach ($rooms as $room) {
                $ruangUjian = RuangUjian::create([
                    'sesi_ujian_id' => $sesiUjian->id,
                    'nomor_ruang' => $room['nomor'],
                    'nama_ruang' => $room['nama'],
                    'kapasitas' => $request->peserta_per_ruang,
                    'jumlah_peserta' => $room['jumlah'],
                ]);

                // Create peserta ruang
                foreach ($room['peserta'] as $index => $peserta) {
                    PesertaRuang::create([
                        'sesi_ujian_id' => $sesiUjian->id,
                        'ruang_ujian_id' => $ruangUjian->id,
                        'calon_siswa_id' => $peserta->id,
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            // Clear session settings
            session()->forget('cetak_ruang_settings');

            return redirect()
                ->route('admin.sesi-ujian.show', $sesiUjian->id)
                ->with('success', 'Distribusi ruangan berhasil disimpan dan dikunci. Silakan assign penguji ke ruangan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan distribusi: ' . $e->getMessage());
        }
    }
}
