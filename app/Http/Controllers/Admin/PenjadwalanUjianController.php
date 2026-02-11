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
use App\Models\PengujiRuang;
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
            'tanggal_ujian', 'jam_mulai', 'jeda_sesi', 'mode',
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
        $mode = $request->mode ?? 'swap';
        
        if ($mode === 'swap' && $kapasitasCbt != $kapasitasWawancara) {
            $diff = abs($kapasitasCbt - $kapasitasWawancara);
            $persen = round(($diff / max($kapasitasCbt, $kapasitasWawancara)) * 100);
            if ($kapasitasCbt > $kapasitasWawancara) {
                $warnings[] = "Kapasitas CBT ({$kapasitasCbt}) lebih besar dari Wawancara ({$kapasitasWawancara}). Perbedaan {$persen}%. Disarankan gunakan mode Queue atau seimbangkan kapasitas.";
            } else {
                $warnings[] = "Kapasitas Wawancara ({$kapasitasWawancara}) lebih besar dari CBT ({$kapasitasCbt}). Perbedaan {$persen}%. Disarankan gunakan mode Queue untuk memaksimalkan ruangan.";
            }
        }

        // Generate schedule based on mode
        if ($mode === 'queue') {
            $schedule = $this->generateQueueSchedule($pesertaList, $request->all());
        } else {
            $schedule = $this->generateSchedule($pesertaList, $request->all(), $kapasitasParalel);
        }

        // Get filter lists
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')->orderBy('nama', 'desc')->get();
        $jalurList = $tahunAktif ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() : collect();
        $gelombangList = $tahunAktif ? GelombangPendaftaran::whereHas('jalur', fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))->get() : collect();

        $settings = $request->only([
            'tanggal_ujian', 'jam_mulai', 'jeda_sesi', 'mode',
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

        // Cast numeric settings to integers
        $settings['jeda_sesi'] = (int) ($settings['jeda_sesi'] ?? 30);
        $settings['jumlah_ruang_cbt'] = (int) ($settings['jumlah_ruang_cbt'] ?? 3);
        $settings['kapasitas_cbt'] = (int) ($settings['kapasitas_cbt'] ?? 30);
        $settings['durasi_cbt'] = (int) ($settings['durasi_cbt'] ?? 90);
        $settings['jumlah_ruang_wawancara'] = (int) ($settings['jumlah_ruang_wawancara'] ?? 4);
        $settings['kapasitas_wawancara'] = (int) ($settings['kapasitas_wawancara'] ?? 15);
        $settings['durasi_wawancara'] = (int) ($settings['durasi_wawancara'] ?? 60);

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
                'mode' => $settings['mode'] ?? 'swap',
                'total_peserta' => $pesertaList->count(),
                'status' => 'locked',
                'generated_at' => now(),
                'generated_by' => Auth::id(),
                'locked_at' => now(),
                'locked_by' => Auth::id(),
            ]);

            // Generate schedule based on mode
            $mode = $settings['mode'] ?? 'swap';
            if ($mode === 'queue') {
                $schedule = $this->generateQueueSchedule($pesertaList, $settings);
            } else {
                $schedule = $this->generateSchedule($pesertaList, $settings, $kapasitasParalel);
            }

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
            'ketuaPanitia',
            'sesiUjian.ruangUjian',
            'jadwalPeserta.calonSiswa',
        ]);

        // Group sesi by nomor_sesi
        $sesiGrouped = $jadwalUjian->sesiUjian->groupBy('nomor_sesi');

        // Get users for ketua panitia assignment
        $pengujiList = User::with('roles')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['penguji', 'admin', 'verifikator', 'super-admin', 'mas-admin']);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $jadwal = $jadwalUjian;
        return view('admin.penjadwalan-ujian.show', compact('jadwal', 'sesiGrouped', 'pengujiList'));
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
     * Update ketua panitia for jadwal (AJAX)
     */
    public function updateKetuaPanitia(Request $request, JadwalUjian $jadwalUjian)
    {
        $request->validate([
            'ketua_panitia_id' => 'nullable|exists:users,id',
        ]);

        $jadwalUjian->update([
            'ketua_panitia_id' => $request->ketua_panitia_id ?: null,
        ]);

        $namaKetua = $request->ketua_panitia_id
            ? User::find($request->ketua_panitia_id)?->name ?? '-'
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Ketua Panitia berhasil diperbarui.',
            'ketua_panitia_name' => $namaKetua,
        ]);
    }

    /**
     * Unlock jadwal (change status from locked to draft)
     */
    public function unlock(JadwalUjian $jadwalUjian)
    {
        try {
            $jadwalUjian->update(['status' => 'draft']);

            return redirect()->back()
                ->with('success', 'Jadwal berhasil dibuka kuncinya. Sekarang jadwal dapat dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuka kunci jadwal: ' . $e->getMessage());
        }
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
            'calonSiswa.jalurPendaftaran',
            'sesiCbt',
            'ruangCbt',
            'sesiWawancara',
            'ruangWawancara'
        ])
        ->where('jadwal_ujian_id', $jadwalUjian->id)
        ->get()
        ->sortBy(fn($jp) => $jp->calonSiswa->nomor_tes ?? '');

        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;

        return view('admin.penjadwalan-ujian.print.kartu-peserta', compact(
            'jadwal', 'pesertaList', 'sekolah'
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
        $jadwal = $jadwalUjian;

        return view('admin.penjadwalan-ujian.print.daftar-hadir', compact(
            'jadwal', 'ruangList', 'sekolah'
        ));
    }

    /**
     * Print nama ruang untuk ditempel
     */
    public function printNamaRuang(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->load(['tahunPelajaran']);
        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;

        return view('admin.penjadwalan-ujian.print.nama-ruang', compact(
            'jadwal', 'sekolah'
        ));
    }

    /**
     * Print jadwal per sesi
     */
    public function printJadwalSesi(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->load(['tahunPelajaran', 'sesiUjian', 'jadwalPeserta']);
        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;

        return view('admin.penjadwalan-ujian.print.jadwal-sesi', compact(
            'jadwal', 'sekolah'
        ));
    }

    /**
     * PDF Daftar Hadir (with Kop Surat)
     */
    public function pdfDaftarHadir(JadwalUjian $jadwalUjian)
    {
        // Increase memory limit for large PDFs
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        
        $jadwalUjian->load(['tahunPelajaran', 'ketuaPanitia']);
        
        // Build room list with peserta
        $ruangList = [];
        $ketuaPanitia = $jadwalUjian->ketuaPanitia;
        
        foreach ($jadwalUjian->sesiUjian as $sesi) {

            $ruangUjianList = RuangUjian::where('sesi_ujian_id', $sesi->id)->get();
            
            foreach ($ruangUjianList as $ruang) {
                $pesertaRuang = PesertaRuang::with('calonSiswa')
                    ->where('ruang_ujian_id', $ruang->id)
                    ->orderBy('nomor_urut')
                    ->get();

                $pengujiRuangList = PengujiRuang::with('user')
                    ->where('ruang_ujian_id', $ruang->id)
                    ->where('is_active', true)
                    ->get();
                
                $ruangList[] = [
                    'nama' => $ruang->nama_ruang,
                    'jenis' => $sesi->jenis_ujian,
                    'sesi' => $sesi->nomor_sesi,
                    'waktu_mulai' => $sesi->waktu_mulai?->format('H:i') ?? '-',
                    'waktu_selesai' => $sesi->waktu_selesai?->format('H:i') ?? '-',
                    'kapasitas' => $ruang->kapasitas,
                    'peserta' => $pesertaRuang,
                    'penguji' => $pengujiRuangList,
                ];
            }
        }

        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;
        $kopSuratService = app(\App\Services\KopSuratService::class);
        $kopSurat = $kopSuratService->renderKopHtml($sekolah, true);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.penjadwalan-ujian.pdf.daftar-hadir', compact(
            'jadwal', 'ruangList', 'sekolah', 'kopSurat', 'ketuaPanitia'
        ));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('daftar-hadir-' . $jadwalUjian->tanggal_ujian->format('Y-m-d') . '.pdf');
    }

    /**
     * PDF Nama Ruang (with Kop Surat)
     */
    public function pdfNamaRuang(JadwalUjian $jadwalUjian)
    {
        // Increase memory limit for large PDFs
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        
        $jadwalUjian->load(['tahunPelajaran', 'sesiUjian']);
        
        // Build room list
        $ruangList = [];
        
        foreach ($jadwalUjian->sesiUjian as $sesi) {
            $ruangUjianList = RuangUjian::where('sesi_ujian_id', $sesi->id)->get();
            
            foreach ($ruangUjianList as $ruang) {
                // Get peserta range
                $pesertaRuang = PesertaRuang::with('calonSiswa')
                    ->where('ruang_ujian_id', $ruang->id)
                    ->orderBy('nomor_urut')
                    ->get();
                
                $nomorTes = $pesertaRuang->map(fn($pr) => $pr->calonSiswa->nomor_tes ?? '')->filter()->sort()->values();
                
                $ruangList[] = [
                    'nama' => $ruang->nama_ruang,
                    'jenis' => $sesi->jenis_ujian,
                    'sesi' => $sesi->nomor_sesi,
                    'waktu' => $sesi->waktu_mulai?->format('H:i') . ' - ' . $sesi->waktu_selesai?->format('H:i'),
                    'jumlah_peserta' => $pesertaRuang->count(),
                    'nomor_tes_awal' => $nomorTes->first() ?? '-',
                    'nomor_tes_akhir' => $nomorTes->last() ?? '-',
                ];
            }
        }

        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.penjadwalan-ujian.pdf.nama-ruang', compact(
            'jadwal', 'ruangList', 'sekolah'
        ));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('nama-ruang-' . $jadwalUjian->tanggal_ujian->format('Y-m-d') . '.pdf');
    }

    /**
     * PDF Daftar Peserta (untuk ditempel di depan ruang)
     */
    public function pdfDaftarPeserta(JadwalUjian $jadwalUjian)
    {
        // Increase memory limit for large PDFs
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        
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
                
                $nomorTes = $pesertaRuang->map(fn($pr) => $pr->calonSiswa->nomor_tes ?? '')->filter()->sort()->values();
                
                $ruangList[] = [
                    'nama' => $ruang->nama_ruang,
                    'jenis' => $sesi->jenis_ujian,
                    'sesi' => $sesi->nomor_sesi,
                    'waktu' => $sesi->waktu_mulai?->format('H:i') . ' - ' . $sesi->waktu_selesai?->format('H:i'),
                    'jumlah_peserta' => $pesertaRuang->count(),
                    'nomor_tes_awal' => $nomorTes->first() ?? '-',
                    'nomor_tes_akhir' => $nomorTes->last() ?? '-',
                    'peserta' => $pesertaRuang,
                ];
            }
        }

        $sekolah = SekolahSettings::first();
        $jadwal = $jadwalUjian;
        $kopSuratService = app(\App\Services\KopSuratService::class);
        $kopSurat = $kopSuratService->renderKopHtml($sekolah, true);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.penjadwalan-ujian.pdf.daftar-peserta', compact(
            'jadwal', 'ruangList', 'sekolah', 'kopSurat'
        ));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('daftar-peserta-' . $jadwalUjian->tanggal_ujian->format('Y-m-d') . '.pdf');
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
        $durasiMax = (int) max($settings['durasi_cbt'], $settings['durasi_wawancara']);
        $jedaSesi = (int) $settings['jeda_sesi'];

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
            $jumlahRuangCbt = (int) $settings['jumlah_ruang_cbt'];
            $jumlahRuangWawancara = (int) $settings['jumlah_ruang_wawancara'];
            $kapasitasCbt = (int) $settings['kapasitas_cbt'];

            // CBT: sequential fill (room 1 penuh dulu, lalu room 2, dst. Room terakhir sisanya)
            foreach ($grupA as $idx => $peserta) {
                $ruangIdx = min(floor($idx / $kapasitasCbt), $jumlahRuangCbt - 1);
                $schedule['peserta'][$peserta->id] = [
                    'grup' => 'A',
                    'gelombang' => $gelombangNum,
                    'sesi_cbt' => $sesiCounter,
                    'sesi_wawancara' => $sesiCounter + 1,
                    'urut_cbt' => $idx - ($ruangIdx * $kapasitasCbt) + 1,
                    'ruang_cbt_idx' => $ruangIdx,
                ];
            }
            // Wawancara: sequential-even (bagi rata, peserta berurutan per ruang)
            $perRoomWawancaraB = max(1, ceil($grupB->count() / $jumlahRuangWawancara));
            foreach ($grupB as $idx => $peserta) {
                $ruangIdx = min(floor($idx / $perRoomWawancaraB), $jumlahRuangWawancara - 1);
                $schedule['peserta'][$peserta->id] = [
                    'grup' => 'B',
                    'gelombang' => $gelombangNum,
                    'sesi_cbt' => $sesiCounter + 1,
                    'sesi_wawancara' => $sesiCounter,
                    'urut_wawancara' => $idx - ($ruangIdx * $perRoomWawancaraB) + 1,
                    'ruang_wawancara_idx' => $ruangIdx,
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
            // Wawancara: sequential-even (bagi rata, peserta berurutan per ruang)
            $perRoomWawancaraA = max(1, ceil($grupA->count() / $jumlahRuangWawancara));
            foreach ($grupA as $idx => $peserta) {
                $ruangIdx = min(floor($idx / $perRoomWawancaraA), $jumlahRuangWawancara - 1);
                $schedule['peserta'][$peserta->id]['urut_wawancara'] = $idx - ($ruangIdx * $perRoomWawancaraA) + 1;
                $schedule['peserta'][$peserta->id]['ruang_wawancara_idx'] = $ruangIdx;
            }
            // CBT: sequential fill (room 1 penuh dulu, lalu room 2, dst)
            foreach ($grupB as $idx => $peserta) {
                $ruangIdx = min(floor($idx / $kapasitasCbt), $jumlahRuangCbt - 1);
                $schedule['peserta'][$peserta->id]['urut_cbt'] = $idx - ($ruangIdx * $kapasitasCbt) + 1;
                $schedule['peserta'][$peserta->id]['ruang_cbt_idx'] = $ruangIdx;
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
     * Generate Queue Schedule - CBT first, remaining do wawancara while waiting
     * More efficient when wawancara capacity > CBT capacity
     */
    protected function generateQueueSchedule($pesertaList, $settings)
    {
        $totalPeserta = $pesertaList->count();
        $kapasitasCbt = (int) $settings['jumlah_ruang_cbt'] * (int) $settings['kapasitas_cbt'];
        $kapasitasWawancara = (int) $settings['jumlah_ruang_wawancara'] * (int) $settings['kapasitas_wawancara'];
        
        $jamMulai = Carbon::parse($settings['tanggal_ujian'] . ' ' . $settings['jam_mulai']);
        $durasiMax = (int) max($settings['durasi_cbt'], $settings['durasi_wawancara']);
        $jedaSesi = (int) $settings['jeda_sesi'];

        $schedule = [
            'sesi' => [],
            'gelombang' => [],
            'peserta' => [],
            'estimasi_selesai' => null,
        ];

        // Track status peserta
        $belumCbt = $pesertaList->pluck('id')->toArray();
        $sudahCbt = []; // id => true, waiting for wawancara
        $belumWawancara = [];
        $selesai = [];

        $currentTime = $jamMulai->copy();
        $sesiCounter = 1;

        // Loop until everyone done both CBT and Wawancara
        while (count($selesai) < $totalPeserta) {
            $sesiStart = $currentTime->copy();
            $sesiEnd = $currentTime->copy()->addMinutes($durasiMax);

            // CBT: Take from belumCbt queue
            $cbtPeserta = array_slice($belumCbt, 0, $kapasitasCbt);
            $belumCbt = array_slice($belumCbt, $kapasitasCbt);

            // Wawancara: Prioritize those who finished CBT, then those who haven't started
            $wawancaraPeserta = [];
            
            // First: from sudahCbt (already did CBT, need wawancara)
            $fromSudahCbt = array_slice($sudahCbt, 0, $kapasitasWawancara);
            $sudahCbt = array_slice($sudahCbt, count($fromSudahCbt));
            $wawancaraPeserta = array_merge($wawancaraPeserta, $fromSudahCbt);
            
            // If still have space: from belumCbt that are NOT doing CBT this session
            $remainingWawancaraSlots = $kapasitasWawancara - count($wawancaraPeserta);
            if ($remainingWawancaraSlots > 0 && count($belumWawancara) > 0) {
                $fromBelumWawancara = array_slice($belumWawancara, 0, $remainingWawancaraSlots);
                $belumWawancara = array_slice($belumWawancara, count($fromBelumWawancara));
                $wawancaraPeserta = array_merge($wawancaraPeserta, $fromBelumWawancara);
            }

            // Get nomor_tes for display
            $cbtCollection = $pesertaList->whereIn('id', $cbtPeserta);
            $wawancaraCollection = $pesertaList->whereIn('id', $wawancaraPeserta);

            $schedule['sesi'][$sesiCounter] = [
                'nomor' => $sesiCounter,
                'waktu_mulai' => $sesiStart->format('H:i'),
                'waktu_selesai' => $sesiEnd->format('H:i'),
                'cbt' => [
                    'peserta' => $cbtPeserta,
                    'jumlah' => count($cbtPeserta),
                    'range' => count($cbtPeserta) > 0 ? ($cbtCollection->first()->nomor_tes . ' - ' . $cbtCollection->last()->nomor_tes) : '-',
                ],
                'wawancara' => [
                    'peserta' => $wawancaraPeserta,
                    'jumlah' => count($wawancaraPeserta),
                    'range' => count($wawancaraPeserta) > 0 ? ($wawancaraCollection->first()->nomor_tes . ' - ' . $wawancaraCollection->last()->nomor_tes) : '-',
                ],
            ];

            // Update peserta mapping
            $jumlahRuangCbt = (int) $settings['jumlah_ruang_cbt'];
            $jumlahRuangWawancara = (int) $settings['jumlah_ruang_wawancara'];
            $kapasitasCbt = (int) $settings['kapasitas_cbt'];

            // CBT: sequential fill (room 1 penuh dulu, lalu room 2, dst)
            foreach ($cbtPeserta as $idx => $pesertaId) {
                if (!isset($schedule['peserta'][$pesertaId])) {
                    $schedule['peserta'][$pesertaId] = [
                        'grup' => 'Q', // Queue mode
                        'gelombang' => 1,
                    ];
                }
                $ruangIdx = min(floor($idx / $kapasitasCbt), $jumlahRuangCbt - 1);
                $schedule['peserta'][$pesertaId]['sesi_cbt'] = $sesiCounter;
                $schedule['peserta'][$pesertaId]['urut_cbt'] = $idx - ($ruangIdx * $kapasitasCbt) + 1;
                $schedule['peserta'][$pesertaId]['ruang_cbt_idx'] = $ruangIdx;
            }

            // Wawancara: sequential-even (bagi rata, peserta berurutan per ruang)
            $perRoomWawancara = max(1, ceil(count($wawancaraPeserta) / $jumlahRuangWawancara));
            foreach ($wawancaraPeserta as $idx => $pesertaId) {
                if (!isset($schedule['peserta'][$pesertaId])) {
                    $schedule['peserta'][$pesertaId] = [
                        'grup' => 'Q',
                        'gelombang' => 1,
                    ];
                }
                $ruangIdx = min(floor($idx / $perRoomWawancara), $jumlahRuangWawancara - 1);
                $schedule['peserta'][$pesertaId]['sesi_wawancara'] = $sesiCounter;
                $schedule['peserta'][$pesertaId]['urut_wawancara'] = $idx - ($ruangIdx * $perRoomWawancara) + 1;
                $schedule['peserta'][$pesertaId]['ruang_wawancara_idx'] = $ruangIdx;
            }

            // After this sesi:
            // - Those who did CBT now need wawancara (if haven't done)
            foreach ($cbtPeserta as $pesertaId) {
                // Check if they already did wawancara
                if (isset($schedule['peserta'][$pesertaId]['sesi_wawancara'])) {
                    $selesai[] = $pesertaId;
                } else {
                    $sudahCbt[] = $pesertaId;
                }
            }

            // - Those who did Wawancara, check if they already did CBT
            foreach ($wawancaraPeserta as $pesertaId) {
                if (isset($schedule['peserta'][$pesertaId]['sesi_cbt'])) {
                    if (!in_array($pesertaId, $selesai)) {
                        $selesai[] = $pesertaId;
                    }
                } else {
                    $belumWawancara[] = $pesertaId; // They did wawancara but still need CBT
                }
            }

            $currentTime = $sesiEnd->copy()->addMinutes($jedaSesi);
            $sesiCounter++;

            // Safety: prevent infinite loop
            if ($sesiCounter > 100) {
                break;
            }
        }

        $schedule['estimasi_selesai'] = $currentTime->subMinutes($jedaSesi)->format('H:i');
        $schedule['gelombang'][1] = [
            'grup_a' => 0,
            'grup_b' => 0,
            'total' => $totalPeserta,
        ];

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
                'jalur_pendaftaran_id' => $jadwalUjian->jalur_pendaftaran_id,
                'gelombang_pendaftaran_id' => $jadwalUjian->gelombang_pendaftaran_id,
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
                'jalur_pendaftaran_id' => $jadwalUjian->jalur_pendaftaran_id,
                'gelombang_pendaftaran_id' => $jadwalUjian->gelombang_pendaftaran_id,
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
