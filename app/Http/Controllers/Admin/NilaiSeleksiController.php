<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Show detail nilai
     */
    public function show(NilaiSeleksi $nilaiSeleksi)
    {
        $nilaiSeleksi->load(['calonSiswa', 'penguji', 'ruangUjian', 'sesiUjian', 'verifier']);
        
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $nilaiSeleksi->sesiUjian->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        return view('admin.nilai-seleksi.show', compact('nilaiSeleksi', 'bobotList'));
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
     * Rekap nilai per sesi
     */
    public function rekap(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        
        // Build query for rekapData
        $query = NilaiSeleksi::with(['calonSiswa', 'ruangUjian', 'sesiUjian.jalur'])
            ->whereIn('status', ['submitted', 'verified']);
            
        // Filter by tahun pelajaran
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;
        if ($selectedTahunId) {
            $query->whereHas('sesiUjian', function($q) use ($selectedTahunId) {
                $q->where('tahun_pelajaran_id', $selectedTahunId);
            });
        }
        
        // Filter by jalur
        if ($request->jalur_id) {
            $query->whereHas('sesiUjian', function($q) use ($request) {
                $q->where('jalur_id', $request->jalur_id);
            });
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $rekapData = $query->orderBy('total_nilai', 'desc')->get();

        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
            
        $jalurs = \App\Models\JalurPendaftaran::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('is_active', true)
            ->get();

        return view('admin.nilai-seleksi.rekap', compact(
            'rekapData',
            'tahunAktif',
            'tahunPelajarans',
            'jalurs'
        ));
    }
}
