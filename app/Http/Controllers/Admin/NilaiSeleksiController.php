<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\TahunPelajaran;
use App\Models\CalonSiswa;
use App\Models\Kelulusan;
use App\Models\NilaiCbt;
use App\Models\JadwalUjian;
use App\Models\ActivityLog;
use App\Models\PengaturanEmail;
use App\Imports\NilaiPenilaianImport;
use App\Exports\RekapNilaiExport;
use App\Services\EmailNotificationService;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class NilaiSeleksiController extends Controller
{
    private function dispatchHasilSeleksiEmail(CalonSiswa $calonSiswa, string $statusAdmisi, ?string $catatan = null): array
    {
        if (!in_array($statusAdmisi, ['diterima', 'ditolak'], true)) {
            return [
                'attempted' => false,
                'status' => 'not_applicable',
                'message' => 'Email hanya berlaku untuk status diterima atau ditolak.',
            ];
        }

        if (!PengaturanEmail::isEnabled($statusAdmisi)) {
            return [
                'attempted' => false,
                'status' => 'disabled',
                'message' => 'Template email untuk status ini sedang nonaktif di Pengaturan Email.',
            ];
        }

        $email = $calonSiswa->user?->email ?? $calonSiswa->email ?? null;
        if (!$email) {
            return [
                'attempted' => false,
                'status' => 'missing_email',
                'message' => 'Email pendaftar belum tersedia, jadi notifikasi tidak dikirim.',
            ];
        }

        $sent = EmailNotificationService::sendHasilSeleksi($calonSiswa, $statusAdmisi, $catatan);

        return [
            'attempted' => true,
            'status' => $sent ? 'sent' : 'failed',
            'message' => $sent
                ? "Email notifikasi berhasil dikirim ke {$email}."
                : 'Gagal mengirim email notifikasi. Silakan cek Pengaturan Email atau Email Log.',
        ];
    }

    private function mapStatusAdmisiToKelulusan(?string $statusAdmisi): ?string
    {
        return match ($statusAdmisi) {
            'diterima' => 'lulus',
            'ditolak' => 'tidak_lulus',
            'cadangan' => 'cadangan',
            default => null,
        };
    }

    private function syncKelulusanFromAdmisi(CalonSiswa $calonSiswa, string $statusAdmisi, ?string $catatan = null): void
    {
        $statusKelulusan = $this->mapStatusAdmisiToKelulusan($statusAdmisi);

        if ($statusKelulusan === null) {
            Kelulusan::where('calon_siswa_id', $calonSiswa->id)
                ->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                ->delete();

            return;
        }

        Kelulusan::updateOrCreate(
            [
                'calon_siswa_id' => $calonSiswa->id,
                'tahun_pelajaran_id' => $calonSiswa->tahun_pelajaran_id,
            ],
            [
                'status' => $statusKelulusan,
                'catatan' => $catatan,
                'diluluskan_oleh' => auth()->id(),
                'tanggal_kelulusan' => now(),
            ]
        );
    }

    /**
     * Display nilai seleksi list for admin
     */
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        
        // Get sesi ujian list
        $sesiUjians = SesiUjian::with(['jalur', 'gelombang', 'ruangan.peserta'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereIn('status', ['locked', 'in_progress', 'completed'])
            ->when($context['jalurFilterId'], function ($query, $jalurId) {
                $query->where('jalur_pendaftaran_id', $jalurId);
            })
            ->when($context['gelombangFilterId'], function ($query, $gelombangId) {
                $query->where('gelombang_pendaftaran_id', $gelombangId);
            })
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
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
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
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $selectedTahunId = $tahunAktif?->id;

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
        if ($context['jalurFilterId']) {
            $seleksiQuery->whereHas('sesiUjian', function($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }
        if ($context['gelombangFilterId']) {
            $seleksiQuery->whereHas('calonSiswa', function($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
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
            if ($context['jalurFilterId']) {
                $cbtOnlySiswa->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            }
            if ($context['gelombangFilterId']) {
                $cbtOnlySiswa->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
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
                $virtual->hafalan_quran_raw = null;
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
            ->whereIn('jenis_dokumen', array_keys(\App\Models\CalonDokumen::getPrestasiDocumentTypes()))
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
        $programGroups = $rekapData
            ->filter(fn($n) => $n->calonSiswa?->jalurPendaftaran?->pilihan_program_aktif)
            ->groupBy(fn($n) => $n->calonSiswa?->pilihan_program ?? 'Belum Memilih')
            ->map(fn($group) => [
                'total' => $group->count(),
                'laki_laki' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'L')->count(),
                'perempuan' => $group->filter(fn($n) => $n->calonSiswa?->jenis_kelamin === 'P')->count(),
            ])->sortByDesc('total');

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
            'minat' => $programGroups,
        ];

        return view('admin.nilai-seleksi.rekap', compact(
            'rekapData',
            'cbtData',
            'raporData',
            'sertifikatData',
            'detailStats',
            'tahunAktif',
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Export rekap nilai seluruh pendaftar ke Excel (termasuk yang belum ada nilai)
     */
    public function exportRekap(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $selectedTahunId = $context['selectedTahun']?->id;

        $export = new RekapNilaiExport(
            $selectedTahunId,
            $context['jalurFilterId'],
            $context['gelombangFilterId']
        );

        $filename = 'Rekap_Nilai_Lengkap_PPDB_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Upload Nilai - Halaman dedicated untuk upload & manajemen nilai
     */
    public function uploadNilai(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];

        // Get jadwal ujian untuk tahun aktif (semua status yang punya sesi)
        $jadwalList = JadwalUjian::with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran'])
            ->withCount('sesiUjian')
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->when($context['jalurFilterId'], function ($query, $jalurId) {
                $query->where('jalur_pendaftaran_id', $jalurId);
            })
            ->when($context['gelombangFilterId'], function ($query, $gelombangId) {
                $query->where('gelombang_pendaftaran_id', $gelombangId);
            })
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

        if ($context['jalurFilterId']) {
            $nilaiQuery->whereHas('sesiUjian', function ($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }

        if ($context['gelombangFilterId']) {
            $nilaiQuery->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
        }

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
        if ($context['jalurFilterId']) {
            $allNilaiQuery->whereHas('sesiUjian', function ($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }
        if ($context['gelombangFilterId']) {
            $allNilaiQuery->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
        }
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
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Process Upload Nilai dari Excel Lembar Penilaian
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'jalur_id' => 'nullable',
            'gelombang_id' => 'nullable',
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
                'returnContext' => [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ],
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
            'tahun_pelajaran_id' => 'nullable',
            'jalur_id' => 'nullable',
            'gelombang_id' => 'nullable',
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
                return redirect()->route('admin.nilai-seleksi.upload', [
                        'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                        'jalur_id' => $request->input('jalur_id'),
                        'gelombang_id' => $request->input('gelombang_id'),
                    ])
                    ->with('warning', $message)
                    ->with('import_errors', $result['errors']);
            }

            return redirect()->route('admin.nilai-seleksi.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('success', $message);
        } catch (\Exception $e) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-seleksi.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
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

        return redirect()->route('admin.nilai-seleksi.upload', [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])
            ->with('info', 'Upload dibatalkan.');
    }

    /**
     * Pengumuman Hasil Seleksi - Form
     */
    public function pengumuman(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        
        // Get kandidat untuk pengumuman (yang sudah verified dan finalisasi)
        $kandidat = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('is_finalisasi', true)
            ->whereIn('status_verifikasi', ['verified'])
            ->when($context['jalurFilterId'], function ($query, $jalurId) {
                $query->where('jalur_pendaftaran_id', $jalurId);
            })
            ->when($context['gelombangFilterId'], function ($query, $gelombangId) {
                $query->where('gelombang_pendaftaran_id', $gelombangId);
            })
            ->orderBy('nilai_akhir', 'desc')
            ->get();

        $cbtMap = NilaiCbt::query()
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereIn('calon_siswa_id', $kandidat->pluck('id'))
            ->get()
            ->keyBy('calon_siswa_id');

        $kandidat->each(function (CalonSiswa $calonSiswa) use ($cbtMap) {
            $nilaiCbt = $cbtMap->get($calonSiswa->id);

            $calonSiswa->setAttribute('nilai_cbt_record', $nilaiCbt);
            $calonSiswa->setAttribute('nilai_cbt_rata', $nilaiCbt?->rata_rata);
            $calonSiswa->setAttribute('nilai_cbt_total', $nilaiCbt?->total_nilai);
            $calonSiswa->setAttribute('has_nilai_cbt', $this->candidateHasCbt($nilaiCbt));
        });
        
        // Hitung statistik
        $stats = [
            'total' => $kandidat->count(),
            'pending' => $kandidat->where('status_admisi', 'pending')->count(),
            'diterima' => $kandidat->where('status_admisi', 'diterima')->count(),
            'ditolak' => $kandidat->where('status_admisi', 'ditolak')->count(),
            'cadangan' => $kandidat->where('status_admisi', 'cadangan')->count(),
            'belum_cbt' => $kandidat->where('has_nilai_cbt', false)->count(),
        ];
        
        return view('admin.nilai-seleksi.pengumuman', compact(
            'kandidat',
            'stats',
            'tahunAktif'
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
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

        if ($newStatus === 'diterima') {
            $nilaiCbt = $this->getCandidateCbt($calonSiswa);

            if (!$this->candidateHasCbt($nilaiCbt)) {
                return redirect()->back()->with(
                    'error',
                    "Status admisi {$calonSiswa->nama_lengkap} tidak bisa diubah menjadi diterima karena nilai CBT belum tersedia."
                );
            }
        }

        DB::transaction(function () use ($calonSiswa, $newStatus, $request) {
            $calonSiswa->update([
                'status_admisi' => $newStatus,
                'catatan_admisi' => $request->catatan_admisi,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Sinkronkan ke tabel kelulusan agar amplop & halaman kelulusan pendaftar langsung mengikuti.
            $this->syncKelulusanFromAdmisi($calonSiswa->fresh(), $newStatus, $request->catatan_admisi);
        });

        // Log activity
        ActivityLog::log(
            'update_admisi',
            "Mengubah status admisi pendaftar: {$calonSiswa->nama_lengkap} dari {$oldStatus} menjadi {$newStatus}",
            $calonSiswa,
            ['status_admisi' => $oldStatus],
            ['status_admisi' => $newStatus]
        );

        $emailMessage = null;

        // Kirim email jika diminta dan status bukan pending
        if ($request->kirim_email && in_array($newStatus, ['diterima', 'ditolak'])) {
            $emailResult = $this->dispatchHasilSeleksiEmail($calonSiswa, $newStatus, $request->catatan_admisi);
            $emailMessage = $emailResult['message'];
        }

        $message = "Status admisi {$calonSiswa->nama_lengkap} berhasil diubah menjadi {$newStatus}.";
        if ($emailMessage) {
            $message .= ' ' . $emailMessage;
        }

        return redirect()->back()->with('success', $message);
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
        $emailSkippedNoAddress = 0;
        $emailSkippedDisabled = 0;
        $emailFailed = 0;
        
        $calonSiswas = CalonSiswa::whereIn('id', $request->calon_siswa_ids)->get();

        if ($request->status_admisi === 'diterima') {
            $cbtMap = NilaiCbt::query()
                ->whereIn('calon_siswa_id', $calonSiswas->pluck('id'))
                ->get()
                ->keyBy('calon_siswa_id');

            $missingCbt = $calonSiswas
                ->filter(fn (CalonSiswa $calonSiswa) => !$this->candidateHasCbt($cbtMap->get($calonSiswa->id)))
                ->pluck('nama_lengkap')
                ->take(5)
                ->values();

            if ($missingCbt->isNotEmpty()) {
                $suffix = $calonSiswas->count() > 5 ? ' dan lainnya' : '';

                return redirect()->back()->with(
                    'error',
                    'Status diterima tidak dapat diterapkan karena beberapa pendaftar belum memiliki nilai CBT: '
                    . $missingCbt->implode(', ')
                    . $suffix
                    . '.'
                );
            }
        }
        
        DB::transaction(function () use (
            $calonSiswas,
            $request,
            &$count,
            &$emailSent,
            &$emailSkippedNoAddress,
            &$emailSkippedDisabled,
            &$emailFailed
        ) {
            foreach ($calonSiswas as $calonSiswa) {
                $calonSiswa->update([
                    'status_admisi' => $request->status_admisi,
                    'catatan_admisi' => $request->catatan_admisi,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Sinkronkan ke tabel kelulusan agar admin & pendaftar membaca hasil yang sama.
                $this->syncKelulusanFromAdmisi($calonSiswa->fresh(), $request->status_admisi, $request->catatan_admisi);

                $count++;

                // Kirim email jika diminta dan status bukan pending
                if ($request->kirim_email && in_array($request->status_admisi, ['diterima', 'ditolak'])) {
                    $emailResult = $this->dispatchHasilSeleksiEmail(
                        $calonSiswa,
                        $request->status_admisi,
                        $request->catatan_admisi
                    );

                    if ($emailResult['status'] === 'sent') {
                        ++$emailSent;
                    } elseif ($emailResult['status'] === 'missing_email') {
                        ++$emailSkippedNoAddress;
                    } elseif ($emailResult['status'] === 'disabled') {
                        ++$emailSkippedDisabled;
                    } elseif ($emailResult['status'] === 'failed') {
                        ++$emailFailed;
                    }
                }
            }
        });

        $message = "{$count} pendaftar berhasil diubah statusnya menjadi {$request->status_admisi}.";
        if ($emailSent > 0) {
            $message .= " {$emailSent} email notifikasi terkirim.";
        }
        if ($emailSkippedNoAddress > 0) {
            $message .= " {$emailSkippedNoAddress} pendaftar dilewati karena email belum tersedia.";
        }
        if ($emailSkippedDisabled > 0) {
            $message .= " Template email nonaktif untuk {$emailSkippedDisabled} pendaftar.";
        }
        if ($emailFailed > 0) {
            $message .= " {$emailFailed} email gagal dikirim. Silakan cek Email Log.";
        }

        return redirect()->back()->with('success', $message);
    }

    private function getCandidateCbt(CalonSiswa $calonSiswa): ?NilaiCbt
    {
        return NilaiCbt::query()
            ->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
            ->where('calon_siswa_id', $calonSiswa->id)
            ->first();
    }

    private function candidateHasCbt(?NilaiCbt $nilaiCbt): bool
    {
        if (!$nilaiCbt) {
            return false;
        }

        return collect(NilaiCbt::komponenList())
            ->keys()
            ->contains(fn ($field) => $nilaiCbt->{$field} !== null)
            || $nilaiCbt->rata_rata !== null
            || $nilaiCbt->total_nilai !== null;
    }
}
