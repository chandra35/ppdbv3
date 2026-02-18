<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\TahunPelajaran;
use App\Models\CalonSiswa;
use App\Models\JadwalUjian;
use App\Models\ActivityLog;
use App\Imports\NilaiPenilaianImport;
use App\Exports\RekapNilaiExport;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class NilaiSeleksiController extends Controller
{
    /**
     * Display nilai seleksi list for admin
     */
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Get sesi ujian list
        $sesiUjians = SesiUjian::with(['jalur', 'gelombang', 'ruangan.peserta'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereIn('status', ['locked', 'in_progress', 'completed'])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total_sesi' => $sesiUjians->count(),
            'total_peserta' => $sesiUjians->sum(fn($s) => $s->ruangan ? $s->ruangan->sum(fn($r) => $r->peserta->count()) : 0),
            'sudah_dinilai' => NilaiSeleksi::whereIn('sesi_ujian_id', $sesiUjians->pluck('id'))
                ->whereIn('status', ['submitted', 'verified'])->count(),
            'sudah_verifikasi' => NilaiSeleksi::whereIn('sesi_ujian_id', $sesiUjians->pluck('id'))
                ->where('status', 'verified')->count(),
        ];

        return view('admin.nilai-seleksi.index', compact(
            'sesiUjians',
            'stats',
            'tahunAktif'
        ));
    }

    /**
     * Show detail nilai per sesi ujian
     */
    public function show(SesiUjian $sesiUjian, Request $request)
    {
        $sesiUjian->load(['jalur', 'gelombang', 'ruangan.peserta']);
        
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $sesiUjian->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $nilaiQuery = NilaiSeleksi::with(['calonSiswa', 'penguji', 'ruangUjian'])
            ->where('sesi_ujian_id', $sesiUjian->id);

        if ($request->ruang) {
            $nilaiQuery->where('ruang_ujian_id', $request->ruang);
        }

        $nilaiList = $nilaiQuery->orderBy('created_at', 'desc')->get();

        return view('admin.nilai-seleksi.show', compact('sesiUjian', 'nilaiList', 'bobotList'));
    }

    /**
     * Verify nilai
     */
    public function verify(Request $request, NilaiSeleksi $nilaiSeleksi)
    {
        $request->validate([
            'action' => 'required|in:verify,revision',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($nilaiSeleksi->status !== NilaiSeleksi::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya nilai dengan status submitted yang bisa diverifikasi.');
        }

        if ($request->action === 'verify') {
            $nilaiSeleksi->update([
                'status' => NilaiSeleksi::STATUS_VERIFIED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'catatan_verifikasi' => $request->catatan,
            ]);
            $message = 'Nilai berhasil diverifikasi.';
        } else {
            $nilaiSeleksi->update([
                'status' => NilaiSeleksi::STATUS_REVISION,
                'catatan_verifikasi' => $request->catatan,
            ]);
            $message = 'Nilai dikembalikan untuk revisi.';
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk verify nilai
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'nilai_ids' => 'required|array',
            'nilai_ids.*' => 'exists:nilai_seleksi,id',
        ]);

        $count = NilaiSeleksi::whereIn('id', $request->nilai_ids)
            ->where('status', NilaiSeleksi::STATUS_SUBMITTED)
            ->update([
                'status' => NilaiSeleksi::STATUS_VERIFIED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

        return back()->with('success', "{$count} nilai berhasil diverifikasi.");
    }

    /**
     * Show bobot nilai settings
     */
    public function bobotIndex()
    {
        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();
        
        $bobotList = $tahunPelajaran 
            ? BobotNilaiSeleksi::where('tahun_pelajaran_id', $tahunPelajaran->id)
                ->orderBy('urutan')
                ->get()
            : collect();

        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

        return view('admin.nilai-seleksi.bobot', compact(
            'bobotList',
            'tahunPelajaran',
            'tahunPelajaranList'
        ));
    }

    /**
     * Update bobot nilai
     */
    public function bobotUpdate(Request $request)
    {
        $request->validate([
            'bobot' => 'required|array',
            'bobot.*.id' => 'required|exists:bobot_nilai_seleksi,id',
            'bobot.*.bobot' => 'required|numeric|min:0|max:100',
            'bobot.*.is_active' => 'nullable|boolean',
        ]);

        // Validate total bobot = 100
        $totalBobot = collect($request->bobot)
            ->where('is_active', true)
            ->sum('bobot');

        if ($totalBobot != 100) {
            return back()
                ->withInput()
                ->with('error', "Total bobot yang aktif harus 100%. Saat ini: {$totalBobot}%");
        }

        foreach ($request->bobot as $data) {
            BobotNilaiSeleksi::where('id', $data['id'])->update([
                'bobot' => $data['bobot'],
                'is_active' => $data['is_active'] ?? false,
            ]);
        }

        return back()->with('success', 'Bobot nilai berhasil diperbarui.');
    }

    /**
     * Rekap nilai per sesi - includes TBQ + CBT-only participants
     */
    public function rekap(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Filter by tahun pelajaran
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        // Filter by jenis tes
        $jenisTes = $request->jenis_tes;

        // ---- 1. Load NilaiSeleksi (TBQ) data ----
        $seleksiQuery = NilaiSeleksi::with(['calonSiswa.jalurPendaftaran', 'ruangUjian', 'sesiUjian.jalur'])
            ->whereIn('status', ['submitted', 'verified']);
            
        if ($selectedTahunId) {
            $seleksiQuery->whereHas('sesiUjian', function($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            });
        }
        
        // Filter by jalur (for TBQ records via sesiUjian)
        if ($request->jalur_id) {
            $seleksiQuery->whereHas('sesiUjian', function($q) use ($request) {
                $q->where('jalur_id', $request->jalur_id);
            });
        }
        
        // Filter by status
        if ($request->status) {
            $seleksiQuery->where('status', $request->status);
        }
        
        $rekapData = $seleksiQuery->orderBy('total_nilai', 'desc')
            ->orderBy('nilai_wawancara', 'desc') // Minat sebagai tiebreaker
            ->get();

        // ---- 2. Load CBT data ----
        $cbtData = \App\Models\NilaiCbt::where('tahun_pelajaran_id', $selectedTahunId)
            ->get()
            ->keyBy('calon_siswa_id');

        // ---- 3. Include CBT-only participants (no TBQ record) ----
        $seleksiCalonIds = $rekapData->pluck('calon_siswa_id');
        $cbtOnlyCalonIds = $cbtData->keys()->diff($seleksiCalonIds);

        if ($cbtOnlyCalonIds->isNotEmpty()) {
            $cbtOnlySiswa = CalonSiswa::with(['jalurPendaftaran'])
                ->whereIn('id', $cbtOnlyCalonIds);
            
            // Filter by jalur for CBT-only
            if ($request->jalur_id) {
                $cbtOnlySiswa->where('jalur_pendaftaran_id', $request->jalur_id);
            }
            
            $cbtOnlySiswa = $cbtOnlySiswa->get();

            foreach ($cbtOnlySiswa as $siswa) {
                $virtual = new \stdClass();
                $virtual->calon_siswa_id = $siswa->id;
                $virtual->calonSiswa = $siswa;
                $virtual->nilai_baca_quran = null;
                $virtual->nilai_tulis_quran = null;
                $virtual->nilai_hafalan = null;
                $virtual->jumlah_juz_hafalan = null;
                $virtual->nilai_wawancara = null;
                $virtual->total_nilai = 0;
                $virtual->status = 'cbt_only';
                $virtual->sesiUjian = null;
                $rekapData->push($virtual);
            }
        }

        // ---- 4. Filter by jenis tes ----
        if ($jenisTes === 'tbq') {
            // Hanya yang punya nilai TBQ/seleksi
            $rekapData = $rekapData->filter(fn($n) => ($n->total_nilai ?? 0) > 0)->values();
        } elseif ($jenisTes === 'cbt') {
            // Hanya yang punya data CBT
            $rekapData = $rekapData->filter(fn($n) => $cbtData->has($n->calon_siswa_id))->values();
        }

        // ---- 5. Load Rapor data ----
        $allCalonIds = $rekapData->pluck('calon_siswa_id');
        $raporData = \App\Models\NilaiRapor::selectRaw('calon_siswa_id, AVG(rata_rata) as avg_rapor')
            ->whereIn('calon_siswa_id', $allCalonIds)
            ->groupBy('calon_siswa_id')
            ->pluck('avg_rapor', 'calon_siswa_id')
            ->map(fn($v) => round((float) $v, 2));

        // Load Sertifikat/Prestasi
        $sertifikatData = \App\Models\CalonDokumen::whereIn('calon_siswa_id', $allCalonIds)
            ->whereIn('jenis_dokumen', ['sertifikat_prestasi', 'piagam'])
            ->where('status_verifikasi', '!=', 'rejected')
            ->get()
            ->groupBy('calon_siswa_id');

        // ---- 6. Hitung nilai akhir: CBT 50% + Rapor 10% + TBQ/Seleksi 40% ----
        $rekapData->each(function ($nilai) use ($cbtData, $raporData) {
            $cbt = $cbtData[$nilai->calon_siswa_id] ?? null;
            $avgRapor = $raporData[$nilai->calon_siswa_id] ?? null;

            $nilaiCbt = $cbt ? (float) $cbt->rata_rata : null;
            $nilaiRapor = $avgRapor ? (float) $avgRapor : null;
            $nilaiSeleksi = ($nilai->total_nilai ?? 0) > 0 ? (float) $nilai->total_nilai : null;

            // Hitung dengan bobot proporsional dari komponen yang tersedia
            $totalBobot = 0;
            $totalNilai = 0;

            if ($nilaiCbt !== null) {
                $totalNilai += $nilaiCbt * 50;
                $totalBobot += 50;
            }
            if ($nilaiRapor !== null) {
                $totalNilai += $nilaiRapor * 10;
                $totalBobot += 10;
            }
            if ($nilaiSeleksi !== null) {
                $totalNilai += $nilaiSeleksi * 40;
                $totalBobot += 40;
            }

            $nilai->nilai_akhir = $totalBobot > 0 ? round($totalNilai / $totalBobot, 2) : 0;
            $nilai->nilai_cbt_rata = $nilaiCbt;
            $nilai->nilai_rapor_rata = $nilaiRapor;
        });

        // Sort by nilai_akhir desc, then minat desc
        $rekapData = $rekapData->sortByDesc(function ($item) {
            return [$item->nilai_akhir, (float) ($item->nilai_wawancara ?? 0)];
        })->values();

        // Detail stats: jalur, minat & gender breakdown
        $detailStats = [
            'total' => $rekapData->count(),
            'laki_laki' => $rekapData->filter(fn($n) => $n->calonSiswa && $n->calonSiswa->jenis_kelamin === 'L')->count(),
            'perempuan' => $rekapData->filter(fn($n) => $n->calonSiswa && $n->calonSiswa->jenis_kelamin === 'P')->count(),
            'jalur' => $rekapData->groupBy(fn($n) => $n->calonSiswa?->jalurPendaftaran?->nama ?? 'Tidak Diketahui')
                ->map(fn($group) => [
                    'total' => $group->count(),
                    'laki_laki' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'L')->count(),
                    'perempuan' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'P')->count(),
                ])->sortByDesc('total'),
            'minat' => $rekapData->groupBy(fn($n) => $n->calonSiswa?->pilihan_program ?? 'Belum Memilih')
                ->map(fn($group) => [
                    'total' => $group->count(),
                    'laki_laki' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'L')->count(),
                    'perempuan' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'P')->count(),
                ])->sortByDesc('total'),
        ];

        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
            
        $jalurs = \App\Models\JalurPendaftaran::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->get();

        return view('admin.nilai-seleksi.rekap', compact(
            'rekapData',
            'cbtData',
            'raporData',
            'sertifikatData',
            'detailStats',
            'tahunAktif',
            'tahunPelajarans',
            'jalurs'
        ));
    }

    /**
     * Export rekap nilai seluruh pendaftar ke Excel (termasuk yang belum ada nilai)
     */
    public function exportRekap(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        $export = new RekapNilaiExport(
            $selectedTahunId,
            $request->jalur_id,
            $request->gelombang_id
        );

        $filename = 'Rekap_Nilai_Lengkap_PPDB_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Upload Nilai - Halaman dedicated untuk upload & manajemen nilai
     */
    public function uploadNilai(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        // Get jadwal ujian untuk tahun aktif (semua status yang punya sesi)
        $jadwalList = JadwalUjian::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->withCount('sesiUjian')
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->has('sesiUjian')
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        // Bobot nilai aktif
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Get semua nilai seleksi (filterable)
        $nilaiQuery = NilaiSeleksi::with(['calonSiswa', 'penguji', 'ruangUjian', 'sesiUjian.jalur'])
            ->whereHas('sesiUjian', function ($q) use ($tahunAktif) {
                $q->where('tahun_pelajaran_id', $tahunAktif?->id);
            });

        // Filter by jadwal
        if ($request->jadwal_id) {
            $jadwal = JadwalUjian::find($request->jadwal_id);
            if ($jadwal) {
                $sesiIds = $jadwal->sesiUjian()->pluck('id');
                $nilaiQuery->whereIn('sesi_ujian_id', $sesiIds);
            }
        }

        // Filter by status
        if ($request->status) {
            $nilaiQuery->where('status', $request->status);
        }

        // Search by name/nomor tes
        if ($request->search) {
            $search = $request->search;
            $nilaiQuery->whereHas('calonSiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_tes', 'like', "%{$search}%");
            });
        }

        $nilaiList = $nilaiQuery->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        // Stats
        $allNilaiQuery = NilaiSeleksi::whereHas('sesiUjian', function ($q) use ($tahunAktif) {
            $q->where('tahun_pelajaran_id', $tahunAktif?->id);
        });
        $stats = [
            'total' => (clone $allNilaiQuery)->count(),
            'draft' => (clone $allNilaiQuery)->where('status', 'draft')->count(),
            'submitted' => (clone $allNilaiQuery)->where('status', 'submitted')->count(),
            'verified' => (clone $allNilaiQuery)->where('status', 'verified')->count(),
        ];

        return view('admin.nilai-seleksi.upload', compact(
            'jadwalList',
            'bobotList',
            'nilaiList',
            'stats',
            'tahunAktif'
        ));
    }

    /**
     * Process Upload Nilai dari Excel Lembar Penilaian
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_ujian,id',
            'file_nilai' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'jadwal_id.required' => 'Jadwal ujian wajib dipilih.',
            'file_nilai.required' => 'File Excel wajib dipilih.',
            'file_nilai.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file_nilai.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $jadwal = JadwalUjian::findOrFail($request->jadwal_id);
            $file = $request->file('file_nilai');

            // Simpan file sementara untuk preview
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFileName = 'upload_nilai_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;
            $file->move($tempDir, $tempFileName);

            if (!file_exists($tempPath)) {
                throw new \Exception('Gagal menyimpan file sementara.');
            }

            $importer = new NilaiPenilaianImport($jadwal);
            $preview = $importer->preview($tempPath);

            return view('admin.nilai-seleksi.preview-upload', [
                'preview' => $preview,
                'jadwal' => $jadwal->load(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran']),
                'tempFile' => $tempFileName,
                'jadwalId' => $jadwal->id,
                'originalFileName' => $file->getClientOriginalName(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.nilai-seleksi.upload')
                ->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi import setelah preview
     */
    public function confirmUpload(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_ujian,id',
            'temp_file' => 'required|string',
        ]);

        $tempPath = storage_path('app/temp/' . $request->temp_file);

        if (!file_exists($tempPath)) {
            return redirect()->route('admin.nilai-seleksi.upload')
                ->with('error', 'File sementara tidak ditemukan. Silakan upload ulang.');
        }

        try {
            $jadwal = JadwalUjian::findOrFail($request->jadwal_id);
            $importer = new NilaiPenilaianImport($jadwal);
            $result = $importer->import($tempPath);

            // Hapus file temp
            @unlink($tempPath);

            $message = "Import selesai: <strong>{$result['imported']}</strong> baru, <strong>{$result['updated']}</strong> diupdate, <strong>{$result['skipped']}</strong> dilewati.";

            if (!empty($result['errors'])) {
                return redirect()->route('admin.nilai-seleksi.upload')
                    ->with('warning', $message)
                    ->with('import_errors', $result['errors']);
            }

            return redirect()->route('admin.nilai-seleksi.upload')
                ->with('success', $message);
        } catch (\Exception $e) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-seleksi.upload')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan upload - hapus file temp
     */
    public function cancelUpload(Request $request)
    {
        if ($request->temp_file) {
            $tempPath = storage_path('app/temp/' . $request->temp_file);
            @unlink($tempPath);
        }

        return redirect()->route('admin.nilai-seleksi.upload')
            ->with('info', 'Upload dibatalkan.');
    }

    /**
     * Pengumuman Hasil Seleksi - Form
     */
    public function pengumuman()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Get kandidat untuk pengumuman (yang sudah verified dan finalisasi)
        $kandidat = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('is_finalisasi', true)
            ->whereIn('status_verifikasi', ['verified'])
            ->orderBy('nilai_akhir', 'desc')
            ->get();
        
        // Hitung statistik
        $stats = [
            'total' => $kandidat->count(),
            'pending' => $kandidat->where('status_admisi', 'pending')->count(),
            'diterima' => $kandidat->where('status_admisi', 'diterima')->count(),
            'ditolak' => $kandidat->where('status_admisi', 'ditolak')->count(),
            'cadangan' => $kandidat->where('status_admisi', 'cadangan')->count(),
        ];
        
        $jalurs = \App\Models\JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('is_active', true)
            ->get();

        return view('admin.nilai-seleksi.pengumuman', compact(
            'kandidat',
            'stats',
            'tahunAktif',
            'jalurs'
        ));
    }

    /**
     * Update status admisi individual
     */
    public function updateAdmisi(Request $request, CalonSiswa $calonSiswa)
    {
        $request->validate([
            'status_admisi' => 'required|in:diterima,ditolak,cadangan,pending',
            'catatan_admisi' => 'nullable|string|max:500',
            'kirim_email' => 'nullable|boolean',
        ]);

        $oldStatus = $calonSiswa->status_admisi;
        $newStatus = $request->status_admisi;

        $calonSiswa->update([
            'status_admisi' => $newStatus,
            'catatan_admisi' => $request->catatan_admisi,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Log activity
        ActivityLog::log(
            'update_admisi',
            "Mengubah status admisi pendaftar: {$calonSiswa->nama_lengkap} dari {$oldStatus} menjadi {$newStatus}",
            $calonSiswa,
            ['status_admisi' => $oldStatus],
            ['status_admisi' => $newStatus]
        );

        // Kirim email jika diminta dan status bukan pending
        if ($request->kirim_email && in_array($newStatus, ['diterima', 'ditolak'])) {
            EmailNotificationService::sendHasilSeleksi(
                $calonSiswa, 
                $newStatus, 
                $request->catatan_admisi
            );
        }

        return redirect()->back()->with('success', "Status admisi {$calonSiswa->nama_lengkap} berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Bulk update status admisi
     */
    public function bulkUpdateAdmisi(Request $request)
    {
        $request->validate([
            'calon_siswa_ids' => 'required|array',
            'calon_siswa_ids.*' => 'exists:calon_siswas,id',
            'status_admisi' => 'required|in:diterima,ditolak,cadangan,pending',
            'catatan_admisi' => 'nullable|string|max:500',
            'kirim_email' => 'nullable|boolean',
        ]);

        $count = 0;
        $emailSent = 0;
        
        $calonSiswas = CalonSiswa::whereIn('id', $request->calon_siswa_ids)->get();
        
        foreach ($calonSiswas as $calonSiswa) {
            $oldStatus = $calonSiswa->status_admisi;
            
            $calonSiswa->update([
                'status_admisi' => $request->status_admisi,
                'catatan_admisi' => $request->catatan_admisi,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            $count++;
            
            // Kirim email jika diminta dan status bukan pending
            if ($request->kirim_email && in_array($request->status_admisi, ['diterima', 'ditolak'])) {
                if (EmailNotificationService::sendHasilSeleksi(
                    $calonSiswa, 
                    $request->status_admisi, 
                    $request->catatan_admisi
                )) {
                    $emailSent++;
                }
            }
        }

        $message = "{$count} pendaftar berhasil diubah statusnya menjadi {$request->status_admisi}.";
        if ($emailSent > 0) {
            $message .= " {$emailSent} email notifikasi terkirim.";
        }

        return redirect()->back()->with('success', $message);
    }
}
