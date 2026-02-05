<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\JadwalUjian;
use App\Models\JadwalPeserta;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjadwalanUjianController extends Controller
{
    /**
     * Display scheduling form
     */
    public function index(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
        
        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        $jalurList = $tahunAktif 
            ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() 
            : collect();
        
        $gelombangList = $tahunAktif 
            ? GelombangPendaftaran::whereHas('jalur', fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))->get() 
            : collect();

        // Get eligible peserta count
        $totalPeserta = $this->getPesertaQuery($tahunAktif)->count();

        // Get existing jadwal for this tahun pelajaran
        $existingJadwal = $tahunAktif 
            ? JadwalUjian::forTahunPelajaran($tahunAktif->id)->latest()->first()
            : null;

        // Default settings
        $settings = session('penjadwalan_settings', [
            'tanggal_ujian' => now()->addDays(7)->format('Y-m-d'),
            'jam_mulai' => '08:00',
            'jeda_sesi' => 30,
            'jumlah_ruang_cbt' => 3,
            'kapasitas_cbt' => 30,
            'durasi_cbt' => 90,
            'prefix_ruang_cbt' => 'Ruang CBT',
            'jumlah_ruang_wawancara' => 4,
            'kapasitas_wawancara' => 15,
            'durasi_wawancara' => 60,
            'prefix_ruang_wawancara' => 'Ruang Wawancara',
            'jalur_id' => null,
            'gelombang_id' => null,
        ]);

        return view('admin.penjadwalan-ujian.index', compact(
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'totalPeserta',
            'settings',
            'existingJadwal'
        ));
    }

    /**
     * Generate preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tanggal_ujian' => 'required|date',
            'jam_mulai' => 'required',
            'jeda_sesi' => 'required|integer|min:5|max:120',
            'jumlah_ruang_cbt' => 'required|integer|min:1|max:50',
            'kapasitas_cbt' => 'required|integer|min:1|max:100',
            'durasi_cbt' => 'required|integer|min:15|max:240',
            'jumlah_ruang_wawancara' => 'required|integer|min:1|max:50',
            'kapasitas_wawancara' => 'required|integer|min:1|max:50',
            'durasi_wawancara' => 'required|integer|min:15|max:240',
        ]);

        // Save settings to session
        session(['penjadwalan_settings' => $request->only([
            'tanggal_ujian', 'jam_mulai', 'jeda_sesi',
            'jumlah_ruang_cbt', 'kapasitas_cbt', 'durasi_cbt', 'prefix_ruang_cbt',
            'jumlah_ruang_wawancara', 'kapasitas_wawancara', 'durasi_wawancara', 'prefix_ruang_wawancara',
            'jalur_id', 'gelombang_id', 'tahun_pelajaran_id'
        ])]);

        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        // Get peserta list
        $pesertaList = $this->getPesertaQuery($tahunAktif, $request->jalur_id, $request->gelombang_id)
            ->orderBy('nomor_tes')
            ->get();

        $totalPeserta = $pesertaList->count();

        if ($totalPeserta === 0) {
            return redirect()->route('admin.penjadwalan-ujian.index')
                ->with('error', 'Tidak ada peserta yang memenuhi syarat (sudah finalisasi dan punya nomor tes).');
        }

        // Calculate capacities
        $kapasitasCbt = $request->jumlah_ruang_cbt * $request->kapasitas_cbt;
        $kapasitasWawancara = $request->jumlah_ruang_wawancara * $request->kapasitas_wawancara;
        $kapasitasParalel = min($kapasitasCbt, $kapasitasWawancara);

        // Check capacity imbalance warning
        $warnings = [];
        if ($kapasitasCbt != $kapasitasWawancara) {
            $diff = abs($kapasitasCbt - $kapasitasWawancara);
            $persen = round(($diff / max($kapasitasCbt, $kapasitasWawancara)) * 100);
            if ($kapasitasCbt > $kapasitasWawancara) {
                $warnings[] = "Kapasitas CBT ({$kapasitasCbt}) lebih besar dari Wawancara ({$kapasitasWawancara}). Perbedaan {$persen}%. Beberapa ruang CBT mungkin tidak terpakai penuh.";
            } else {
                $warnings[] = "Kapasitas Wawancara ({$kapasitasWawancara}) lebih besar dari CBT ({$kapasitasCbt}). Perbedaan {$persen}%. Beberapa ruang Wawancara mungkin tidak terpakai penuh.";
            }
        }

        // Generate schedule
        $schedule = $this->generateSchedule(
            $pesertaList,
            $request->all(),
            $kapasitasParalel
        );

        // Get filter lists
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('nama', 'desc')->get();
        $jalurList = $tahunAktif ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() : collect();
        $gelombangList = $tahunAktif ? GelombangPendaftaran::whereHas('jalur', fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))->get() : collect();

        $settings = $request->only([
            'tanggal_ujian', 'jam_mulai', 'jeda_sesi',
            'jumlah_ruang_cbt', 'kapasitas_cbt', 'durasi_cbt', 'prefix_ruang_cbt',
            'jumlah_ruang_wawancara', 'kapasitas_wawancara', 'durasi_wawancara', 'prefix_ruang_wawancara',
            'jalur_id', 'gelombang_id', 'tahun_pelajaran_id'
        ]);

        return view('admin.penjadwalan-ujian.index', compact(
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'totalPeserta',
            'settings',
            'schedule',
            'warnings',
            'kapasitasCbt',
            'kapasitasWawancara',
            'kapasitasParalel'
        ));
    }

    /**
     * Save and lock schedule
     */
    public function store(Request $request)
    {
        $settings = session('penjadwalan_settings');
        if (empty($settings)) {
            return redirect()->route('admin.penjadwalan-ujian.index')
                ->with('error', 'Silakan generate preview terlebih dahulu.');
        }

        $tahunAktif = isset($settings['tahun_pelajaran_id']) 
            ? TahunPelajaran::find($settings['tahun_pelajaran_id'])
            : TahunPelajaran::where('is_active', true)->first();

        // Get peserta list
        $pesertaList = $this->getPesertaQuery($tahunAktif, $settings['jalur_id'] ?? null, $settings['gelombang_id'] ?? null)
            ->orderBy('nomor_tes')
            ->get();

        if ($pesertaList->isEmpty()) {
            return redirect()->route('admin.penjadwalan-ujian.index')
                ->with('error', 'Tidak ada peserta yang memenuhi syarat.');
        }

        // Calculate
        $kapasitasCbt = $settings['jumlah_ruang_cbt'] * $settings['kapasitas_cbt'];
        $kapasitasWawancara = $settings['jumlah_ruang_wawancara'] * $settings['kapasitas_wawancara'];
        $kapasitasParalel = min($kapasitasCbt, $kapasitasWawancara);

        try {
            DB::beginTransaction();

            // Create JadwalUjian
            $jadwalUjian = JadwalUjian::create([
                'tahun_pelajaran_id' => $tahunAktif->id,
                'jalur_pendaftaran_id' => $settings['jalur_id'] ?? null,
                'gelombang_pendaftaran_id' => $settings['gelombang_id'] ?? null,
                'tanggal_ujian' => $settings['tanggal_ujian'],
                'jam_mulai' => $settings['jam_mulai'],
                'jeda_sesi' => $settings['jeda_sesi'],
                'jumlah_ruang_cbt' => $settings['jumlah_ruang_cbt'],
                'kapasitas_cbt' => $settings['kapasitas_cbt'],
                'durasi_cbt' => $settings['durasi_cbt'],
                'prefix_ruang_cbt' => $settings['prefix_ruang_cbt'] ?? 'Ruang CBT',
                'jumlah_ruang_wawancara' => $settings['jumlah_ruang_wawancara'],
                'kapasitas_wawancara' => $settings['kapasitas_wawancara'],
                'durasi_wawancara' => $settings['durasi_wawancara'],
                'prefix_ruang_wawancara' => $settings['prefix_ruang_wawancara'] ?? 'Ruang Wawancara',
                'total_peserta' => $pesertaList->count(),
                'status' => 'locked',
                'generated_at' => now(),
                'generated_by' => Auth::id(),
                'locked_at' => now(),
                'locked_by' => Auth::id(),
            ]);

            // Generate schedule
            $schedule = $this->generateSchedule($pesertaList, $settings, $kapasitasParalel);

            // Create sesi and ruang, then assign peserta
            $this->saveScheduleToDatabase($jadwalUjian, $schedule, $settings, $tahunAktif);

            // Update jadwal ujian with calculated values
            $jadwalUjian->update([
                'total_sesi' => count($schedule['sesi']),
                'estimasi_selesai' => $schedule['estimasi_selesai'],
            ]);

            DB::commit();

            // Clear session
            session()->forget('penjadwalan_settings');

            return redirect()->route('admin.penjadwalan-ujian.show', $jadwalUjian)
                ->with('success', 'Jadwal ujian berhasil disimpan dan dikunci.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.penjadwalan-ujian.index')
                ->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Show saved jadwal
     */
    public function show(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->load([
            'tahunPelajaran',
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'sesiUjian.ruangUjian',
            'jadwalPeserta.calonSiswa',
        ]);

        // Group sesi by nomor_sesi
        $sesiGrouped = $jadwalUjian->sesiUjian->groupBy('nomor_sesi');

        return view('admin.penjadwalan-ujian.show', compact('jadwalUjian', 'sesiGrouped'));
    }

    /**
     * List all jadwal
     */
    public function list(Request $request)
    {
        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        $jadwalList = JadwalUjian::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->orderBy('tanggal_ujian', 'desc')
            ->paginate(10);

        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('nama', 'desc')->get();

        return view('admin.penjadwalan-ujian.list', compact('jadwalList', 'tahunPelajaranList', 'tahunAktif'));
    }

    /**
     * Delete jadwal
     */
    public function destroy(JadwalUjian $jadwalUjian)
    {
        try {
            DB::beginTransaction();

            // Delete related sesi_ujian, ruang_ujian, peserta_ruang
            foreach ($jadwalUjian->sesiUjian as $sesi) {
                PesertaRuang::where('sesi_ujian_id', $sesi->id)->delete();
                RuangUjian::where('sesi_ujian_id', $sesi->id)->delete();
                $sesi->delete();
            }

            // Delete jadwal_peserta
            JadwalPeserta::where('jadwal_ujian_id', $jadwalUjian->id)->delete();

            // Delete jadwal_ujian
            $jadwalUjian->delete();

            DB::commit();

            return redirect()->route('admin.penjadwalan-ujian.list')
                ->with('success', 'Jadwal ujian berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    // ================== PRINT METHODS ==================

    /**
     * Print kartu peserta
     */
    public function printKartuPeserta(JadwalUjian $jadwalUjian, Request $request)
    {
        $jadwalUjian->load(['tahunPelajaran']);
        
        $pesertaList = JadwalPeserta::with([
            'calonSiswa.jalur',
            'sesiCbt',
            'ruangCbt',
            'sesiWawancara',
            'ruangWawancara'
        ])
        ->where('jadwal_ujian_id', $jadwalUjian->id)
        ->get()
        ->sortBy(fn($jp) => $jp->calonSiswa->nomor_tes ?? '');

        $sekolah = SekolahSettings::first();

        return view('admin.penjadwalan-ujian.print.kartu-peserta', compact(
            'jadwalUjian', 'pesertaList', 'sekolah'
        ));
    }

    /**
     * Print daftar hadir per ruang
     */
    public function printDaftarHadir(JadwalUjian $jadwalUjian, Request $request)
    {
        $jadwalUjian->load(['tahunPelajaran', 'sesiUjian']);
        
        // Build room list with peserta
        $ruangList = [];
        
        foreach ($jadwalUjian->sesiUjian as $sesi) {
            $ruangUjianList = RuangUjian::where('sesi_ujian_id', $sesi->id)->get();
            
            foreach ($ruangUjianList as $ruang) {
                $pesertaRuang = PesertaRuang::with('calonSiswa')
                    ->where('ruang_ujian_id', $ruang->id)
                    ->orderBy('nomor_urut')
                    ->get();
                
                $ruangList[] = [
                    'nama' => $ruang->nama_ruang,
                    'jenis' => $sesi->jenis_ujian,
                    'sesi' => $sesi->nomor_sesi,
                    'waktu_mulai' => $sesi->waktu_mulai?->format('H:i') ?? '-',
                    'waktu_selesai' => $sesi->waktu_selesai?->format('H:i') ?? '-',
                    'kapasitas' => $ruang->kapasitas,
                    'peserta' => $pesertaRuang->map(fn($pr) => [
                        'nomor_tes' => $pr->calonSiswa->nomor_tes ?? '-',
                        'nama' => $pr->calonSiswa->nama_lengkap ?? '-',
                    ])->toArray(),
                ];
            }
        }

        $sekolah = SekolahSettings::first();

        return view('admin.penjadwalan-ujian.print.daftar-hadir', compact(
            'jadwalUjian', 'ruangList', 'sekolah'
        ));
    }

    /**
     * Print nama ruang untuk ditempel
     */
    public function printNamaRuang(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->load(['tahunPelajaran']);
        $sekolah = SekolahSettings::first();

        return view('admin.penjadwalan-ujian.print.nama-ruang', compact(
            'jadwalUjian', 'sekolah'
        ));
    }

    /**
     * Print jadwal per sesi
     */
    public function printJadwalSesi(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->load(['tahunPelajaran', 'sesiUjian', 'jadwalPeserta']);
        $sekolah = SekolahSettings::first();

        return view('admin.penjadwalan-ujian.print.jadwal-sesi', compact(
            'jadwalUjian', 'sekolah'
        ));
    }

    /**
     * Export Excel
     */
    public function exportExcel(JadwalUjian $jadwalUjian)
    {
        $jadwalPeserta = JadwalPeserta::with([
            'calonSiswa',
            'sesiCbt',
            'ruangCbt',
            'sesiWawancara',
            'ruangWawancara'
        ])
        ->where('jadwal_ujian_id', $jadwalUjian->id)
        ->orderBy('nomor_gelombang')
        ->orderBy('grup')
        ->get();

        $filename = 'jadwal-ujian-' . $jadwalUjian->tanggal_ujian->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($jadwalPeserta, $jadwalUjian) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'No',
                'Nomor Tes',
                'Nama Peserta',
                'Asal Sekolah',
                'Grup',
                'Gelombang',
                'Sesi CBT',
                'Waktu CBT',
                'Ruang CBT',
                'No Urut CBT',
                'Sesi Wawancara',
                'Waktu Wawancara',
                'Ruang Wawancara',
                'No Urut Wawancara',
            ]);

            $no = 1;
            foreach ($jadwalPeserta as $jp) {
                fputcsv($file, [
                    $no++,
                    $jp->calonSiswa?->nomor_tes,
                    $jp->calonSiswa?->nama_lengkap,
                    $jp->calonSiswa?->nama_sekolah_asal,
                    $jp->grup,
                    $jp->nomor_gelombang,
                    $jp->sesiCbt?->nomor_sesi,
                    $jp->sesiCbt?->waktu_mulai?->format('H:i') . '-' . $jp->sesiCbt?->waktu_selesai?->format('H:i'),
                    $jp->ruangCbt?->nama_ruang,
                    $jp->nomor_urut_cbt,
                    $jp->sesiWawancara?->nomor_sesi,
                    $jp->sesiWawancara?->waktu_mulai?->format('H:i') . '-' . $jp->sesiWawancara?->waktu_selesai?->format('H:i'),
                    $jp->ruangWawancara?->nama_ruang,
                    $jp->nomor_urut_wawancara,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ================== HELPER METHODS ==================

    /**
     * Get peserta query
     */
    protected function getPesertaQuery($tahunAktif, $jalurId = null, $gelombangId = null)
    {
        return CalonSiswa::where('is_finalisasi', true)
            ->whereNotNull('nomor_tes')
            ->where('nomor_tes', '!=', '')
            ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
            ->when($jalurId, fn($q) => $q->where('jalur_pendaftaran_id', $jalurId))
            ->when($gelombangId, fn($q) => $q->where('gelombang_pendaftaran_id', $gelombangId));
    }

    /**
     * Generate schedule (preview only, not saved)
     */
    protected function generateSchedule($pesertaList, $settings, $kapasitasParalel)
    {
        $totalPeserta = $pesertaList->count();
        
        // Each "gelombang" processes kapasitasParalel × 2 peserta (Grup A + Grup B swap)
        // But if paralel can handle more than half of peserta, we only need fewer sesi
        $pesertaPerGelombang = $kapasitasParalel * 2;
        $jumlahGelombang = ceil($totalPeserta / $pesertaPerGelombang);
        
        // Each gelombang has 2 sesi (for swap)
        $jumlahSesi = $jumlahGelombang * 2;

        // Calculate time
        $jamMulai = Carbon::parse($settings['tanggal_ujian'] . ' ' . $settings['jam_mulai']);
        $durasiMax = max($settings['durasi_cbt'], $settings['durasi_wawancara']);
        $jedaSesi = $settings['jeda_sesi'];

        $schedule = [
            'sesi' => [],
            'gelombang' => [],
            'peserta' => [],
            'estimasi_selesai' => null,
        ];

        $pesertaChunks = $pesertaList->chunk($pesertaPerGelombang);
        $currentTime = $jamMulai->copy();
        $sesiCounter = 1;

        foreach ($pesertaChunks as $gelombangIndex => $gelombangPeserta) {
            $gelombangNum = $gelombangIndex + 1;
            
            // Split into Grup A (CBT first) and Grup B (Wawancara first)
            $halfPoint = ceil($gelombangPeserta->count() / 2);
            $grupA = $gelombangPeserta->take($halfPoint)->values();
            $grupB = $gelombangPeserta->skip($halfPoint)->values();

            // Sesi 1 for this gelombang
            $sesi1Start = $currentTime->copy();
            $sesi1End = $currentTime->copy()->addMinutes($durasiMax);

            $schedule['sesi'][$sesiCounter] = [
                'nomor' => $sesiCounter,
                'waktu_mulai' => $sesi1Start->format('H:i'),
                'waktu_selesai' => $sesi1End->format('H:i'),
                'cbt' => [
                    'peserta' => $grupA->pluck('id')->toArray(),
                    'jumlah' => $grupA->count(),
                    'range' => $grupA->count() > 0 ? ($grupA->first()->nomor_tes . ' - ' . $grupA->last()->nomor_tes) : '-',
                ],
                'wawancara' => [
                    'peserta' => $grupB->pluck('id')->toArray(),
                    'jumlah' => $grupB->count(),
                    'range' => $grupB->count() > 0 ? ($grupB->first()->nomor_tes . ' - ' . $grupB->last()->nomor_tes) : '-',
                ],
            ];

            // Save peserta mapping for Sesi 1
            foreach ($grupA as $idx => $peserta) {
                $schedule['peserta'][$peserta->id] = [
                    'grup' => 'A',
                    'gelombang' => $gelombangNum,
                    'sesi_cbt' => $sesiCounter,
                    'sesi_wawancara' => $sesiCounter + 1,
                    'urut_cbt' => $idx + 1,
                    'ruang_cbt_idx' => floor($idx / $settings['kapasitas_cbt']),
                ];
            }
            foreach ($grupB as $idx => $peserta) {
                $schedule['peserta'][$peserta->id] = [
                    'grup' => 'B',
                    'gelombang' => $gelombangNum,
                    'sesi_cbt' => $sesiCounter + 1,
                    'sesi_wawancara' => $sesiCounter,
                    'urut_wawancara' => $idx + 1,
                    'ruang_wawancara_idx' => floor($idx / $settings['kapasitas_wawancara']),
                ];
            }

            $currentTime = $sesi1End->copy()->addMinutes($jedaSesi);
            $sesiCounter++;

            // Sesi 2 for this gelombang (swap)
            $sesi2Start = $currentTime->copy();
            $sesi2End = $currentTime->copy()->addMinutes($durasiMax);

            $schedule['sesi'][$sesiCounter] = [
                'nomor' => $sesiCounter,
                'waktu_mulai' => $sesi2Start->format('H:i'),
                'waktu_selesai' => $sesi2End->format('H:i'),
                'cbt' => [
                    'peserta' => $grupB->pluck('id')->toArray(),
                    'jumlah' => $grupB->count(),
                    'range' => $grupB->count() > 0 ? ($grupB->first()->nomor_tes . ' - ' . $grupB->last()->nomor_tes) : '-',
                ],
                'wawancara' => [
                    'peserta' => $grupA->pluck('id')->toArray(),
                    'jumlah' => $grupA->count(),
                    'range' => $grupA->count() > 0 ? ($grupA->first()->nomor_tes . ' - ' . $grupA->last()->nomor_tes) : '-',
                ],
            ];

            // Update peserta mapping for Sesi 2
            foreach ($grupA as $idx => $peserta) {
                $schedule['peserta'][$peserta->id]['urut_wawancara'] = $idx + 1;
                $schedule['peserta'][$peserta->id]['ruang_wawancara_idx'] = floor($idx / $settings['kapasitas_wawancara']);
            }
            foreach ($grupB as $idx => $peserta) {
                $schedule['peserta'][$peserta->id]['urut_cbt'] = $idx + 1;
                $schedule['peserta'][$peserta->id]['ruang_cbt_idx'] = floor($idx / $settings['kapasitas_cbt']);
            }

            $currentTime = $sesi2End->copy()->addMinutes($jedaSesi);
            $sesiCounter++;

            $schedule['gelombang'][$gelombangNum] = [
                'grup_a' => $grupA->count(),
                'grup_b' => $grupB->count(),
                'total' => $gelombangPeserta->count(),
            ];
        }

        $schedule['estimasi_selesai'] = $currentTime->subMinutes($jedaSesi)->format('H:i');

        return $schedule;
    }

    /**
     * Save schedule to database
     */
    protected function saveScheduleToDatabase(JadwalUjian $jadwalUjian, array $schedule, array $settings, $tahunAktif)
    {
        $sesiMap = []; // nomor_sesi => ['cbt' => sesi_id, 'wawancara' => sesi_id]
        $ruangMap = []; // "sesi_id-jenis-idx" => ruang_id

        // Create sesi and ruang
        foreach ($schedule['sesi'] as $nomorSesi => $sesiData) {
            // Create CBT sesi
            $sesiCbt = SesiUjian::create([
                'jadwal_ujian_id' => $jadwalUjian->id,
                'tahun_pelajaran_id' => $tahunAktif->id,
                'nama' => "Sesi {$nomorSesi} - CBT",
                'jenis_ujian' => 'cbt',
                'nomor_sesi' => $nomorSesi,
                'tanggal' => $jadwalUjian->tanggal_ujian,
                'waktu_mulai' => $sesiData['waktu_mulai'],
                'waktu_selesai' => $sesiData['waktu_selesai'],
                'durasi' => $settings['durasi_cbt'],
                'peserta_per_ruang' => $settings['kapasitas_cbt'],
                'prefix_ruang' => $settings['prefix_ruang_cbt'] ?? 'Ruang CBT',
                'status' => 'locked',
            ]);

            // Create Wawancara sesi
            $sesiWawancara = SesiUjian::create([
                'jadwal_ujian_id' => $jadwalUjian->id,
                'tahun_pelajaran_id' => $tahunAktif->id,
                'nama' => "Sesi {$nomorSesi} - Wawancara",
                'jenis_ujian' => 'wawancara',
                'nomor_sesi' => $nomorSesi,
                'tanggal' => $jadwalUjian->tanggal_ujian,
                'waktu_mulai' => $sesiData['waktu_mulai'],
                'waktu_selesai' => $sesiData['waktu_selesai'],
                'durasi' => $settings['durasi_wawancara'],
                'peserta_per_ruang' => $settings['kapasitas_wawancara'],
                'prefix_ruang' => $settings['prefix_ruang_wawancara'] ?? 'Ruang Wawancara',
                'status' => 'locked',
            ]);

            $sesiMap[$nomorSesi] = [
                'cbt' => $sesiCbt->id,
                'wawancara' => $sesiWawancara->id,
            ];

            // Create ruang for CBT
            for ($i = 0; $i < $settings['jumlah_ruang_cbt']; $i++) {
                $ruangCbt = RuangUjian::create([
                    'sesi_ujian_id' => $sesiCbt->id,
                    'nomor_ruang' => $i + 1,
                    'nama_ruang' => ($settings['prefix_ruang_cbt'] ?? 'Ruang CBT') . ' ' . ($i + 1),
                    'kapasitas' => $settings['kapasitas_cbt'],
                    'jumlah_peserta' => 0,
                ]);
                $ruangMap["{$sesiCbt->id}-cbt-{$i}"] = $ruangCbt->id;
            }

            // Create ruang for Wawancara
            for ($i = 0; $i < $settings['jumlah_ruang_wawancara']; $i++) {
                $ruangWawancara = RuangUjian::create([
                    'sesi_ujian_id' => $sesiWawancara->id,
                    'nomor_ruang' => $i + 1,
                    'nama_ruang' => ($settings['prefix_ruang_wawancara'] ?? 'Ruang Wawancara') . ' ' . ($i + 1),
                    'kapasitas' => $settings['kapasitas_wawancara'],
                    'jumlah_peserta' => 0,
                ]);
                $ruangMap["{$sesiWawancara->id}-wawancara-{$i}"] = $ruangWawancara->id;
            }
        }

        // Create jadwal_peserta and peserta_ruang
        foreach ($schedule['peserta'] as $calonSiswaId => $mapping) {
            $sesiCbtId = $sesiMap[$mapping['sesi_cbt']]['cbt'];
            $sesiWawancaraId = $sesiMap[$mapping['sesi_wawancara']]['wawancara'];
            
            $ruangCbtId = $ruangMap["{$sesiCbtId}-cbt-" . ($mapping['ruang_cbt_idx'] ?? 0)] ?? null;
            $ruangWawancaraId = $ruangMap["{$sesiWawancaraId}-wawancara-" . ($mapping['ruang_wawancara_idx'] ?? 0)] ?? null;

            // Create jadwal_peserta
            JadwalPeserta::create([
                'jadwal_ujian_id' => $jadwalUjian->id,
                'calon_siswa_id' => $calonSiswaId,
                'sesi_cbt_id' => $sesiCbtId,
                'ruang_cbt_id' => $ruangCbtId,
                'nomor_urut_cbt' => $mapping['urut_cbt'] ?? null,
                'sesi_wawancara_id' => $sesiWawancaraId,
                'ruang_wawancara_id' => $ruangWawancaraId,
                'nomor_urut_wawancara' => $mapping['urut_wawancara'] ?? null,
                'grup' => $mapping['grup'],
                'nomor_gelombang' => $mapping['gelombang'],
            ]);

            // Create peserta_ruang for CBT
            if ($ruangCbtId) {
                PesertaRuang::create([
                    'sesi_ujian_id' => $sesiCbtId,
                    'ruang_ujian_id' => $ruangCbtId,
                    'calon_siswa_id' => $calonSiswaId,
                    'nomor_urut' => $mapping['urut_cbt'] ?? 1,
                ]);
                
                // Update jumlah_peserta
                RuangUjian::where('id', $ruangCbtId)->increment('jumlah_peserta');
            }

            // Create peserta_ruang for Wawancara
            if ($ruangWawancaraId) {
                PesertaRuang::create([
                    'sesi_ujian_id' => $sesiWawancaraId,
                    'ruang_ujian_id' => $ruangWawancaraId,
                    'calon_siswa_id' => $calonSiswaId,
                    'nomor_urut' => $mapping['urut_wawancara'] ?? 1,
                ]);
                
                // Update jumlah_peserta
                RuangUjian::where('id', $ruangWawancaraId)->increment('jumlah_peserta');
            }
        }
    }
}
