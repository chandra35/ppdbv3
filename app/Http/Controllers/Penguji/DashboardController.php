<?php

namespace App\Http\Controllers\Penguji;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\PengujiRuang;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\CalonDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard penguji
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get sesi ujian where penguji is assigned
        $assignments = PengujiRuang::with(['sesiUjian.jalur', 'ruangUjian.peserta'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('sesiUjian', function ($q) {
                $q->whereIn('status', ['locked', 'in_progress']);
            })
            ->get();

        // Group by sesi
        $sesiGroups = $assignments->groupBy('sesi_ujian_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'sesi' => $first->sesiUjian,
                    'ruangan' => $items->map(fn($item) => $item->ruangUjian),
                ];
            });

        return view('penguji.dashboard', compact('sesiGroups'));
    }

    /**
     * Show ruangan peserta list
     */
    public function ruangan(RuangUjian $ruangUjian)
    {
        $user = Auth::user();

        // Check if penguji is assigned to this ruangan
        $isAssigned = PengujiRuang::where('user_id', $user->id)
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->where('is_active', true)
            ->exists();

        if (!$isAssigned) {
            return redirect()->route('penguji.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke ruangan ini.');
        }

        $sesiUjian = $ruangUjian->sesiUjian;

        // Get peserta with their nilai status
        $pesertaList = PesertaRuang::with(['calonSiswa.dokumen'])
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->orderBy('nomor_urut')
            ->get()
            ->map(function ($peserta) use ($user, $sesiUjian) {
                $nilai = NilaiSeleksi::where('sesi_ujian_id', $sesiUjian->id)
                    ->where('calon_siswa_id', $peserta->calon_siswa_id)
                    ->where('penguji_id', $user->id)
                    ->first();

                return [
                    'peserta' => $peserta,
                    'calon_siswa' => $peserta->calonSiswa,
                    'nilai' => $nilai,
                    'status' => $nilai?->status ?? 'belum',
                ];
            });

        // Progress
        $totalPeserta = $pesertaList->count();
        $sudahDinilai = $pesertaList->where('status', '!=', 'belum')->count();
        $sudahSubmit = $pesertaList->whereIn('status', ['submitted', 'verified'])->count();

        return view('penguji.ruangan', compact(
            'ruangUjian',
            'sesiUjian',
            'pesertaList',
            'totalPeserta',
            'sudahDinilai',
            'sudahSubmit'
        ));
    }

    /**
     * Show input nilai form
     */
    public function inputNilai(RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $user = Auth::user();

        // Check if penguji is assigned
        $isAssigned = PengujiRuang::where('user_id', $user->id)
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->where('is_active', true)
            ->exists();

        if (!$isAssigned) {
            return redirect()->route('penguji.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke ruangan ini.');
        }

        $sesiUjian = $ruangUjian->sesiUjian;
        $calonSiswa = $pesertaRuang->calonSiswa;

        // Eager load ortu with address relations
        $calonSiswa->load([
            'ortu.provinsiOrtu', 'ortu.kabupatenOrtu', 'ortu.kecamatanOrtu', 'ortu.kelurahanOrtu',
            'jalurPendaftaran', 'gelombangPendaftaran',
        ]);

        // Get or create nilai
        $nilai = NilaiSeleksi::firstOrNew([
            'sesi_ujian_id' => $sesiUjian->id,
            'calon_siswa_id' => $calonSiswa->id,
            'penguji_id' => $user->id,
        ], [
            'ruang_ujian_id' => $ruangUjian->id,
            'status' => NilaiSeleksi::STATUS_DRAFT,
        ]);

        // Get bobot for display
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $sesiUjian->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Get prev/next peserta
        $allPeserta = PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)
            ->orderBy('nomor_urut')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($pesertaRuang->id, $allPeserta);
        $prevPeserta = $currentIndex > 0 ? $allPeserta[$currentIndex - 1] : null;
        $nextPeserta = $currentIndex < count($allPeserta) - 1 ? $allPeserta[$currentIndex + 1] : null;

        // Dokumen pendaftar - split into utama and tambahan
        $semuaDokumen = $calonSiswa->dokumen()->orderBy('jenis_dokumen')->get();
        $dokumenTambahanKeys = array_keys(CalonDokumen::DOKUMEN_TAMBAHAN);
        $dokumenList = $semuaDokumen->filter(fn($d) => !in_array($d->jenis_dokumen, $dokumenTambahanKeys));
        $dokumenTambahan = $semuaDokumen->filter(fn($d) => in_array($d->jenis_dokumen, $dokumenTambahanKeys));
        $dokumenLabels = CalonDokumen::JENIS_DOKUMEN;
        $dokumenTambahanLabels = CalonDokumen::DOKUMEN_TAMBAHAN;

        // Auto-set status to in_progress when penguji opens this peserta
        if ($pesertaRuang->status === PesertaRuang::STATUS_WAITING) {
            // Reset any other in_progress peserta in this room
            PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)
                ->where('status', PesertaRuang::STATUS_IN_PROGRESS)
                ->update(['status' => PesertaRuang::STATUS_WAITING]);
            
            $pesertaRuang->update(['status' => PesertaRuang::STATUS_IN_PROGRESS]);
        }

        return view('penguji.input-nilai', compact(
            'ruangUjian',
            'sesiUjian',
            'pesertaRuang',
            'calonSiswa',
            'nilai',
            'bobotList',
            'prevPeserta',
            'nextPeserta',
            'dokumenList',
            'dokumenLabels',
            'dokumenTambahan',
            'dokumenTambahanLabels'
        ));
    }

    /**
     * Save nilai
     */
    public function saveNilai(Request $request, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $user = Auth::user();

        $request->validate([
            'nilai_wawancara' => 'nullable|numeric|min:0|max:100',
            'nilai_tajwid' => 'nullable|numeric|min:0|max:100',
            'nilai_makhroj' => 'nullable|numeric|min:0|max:100',
            'nilai_kelancaran' => 'nullable|numeric|min:0|max:100',
            'nilai_tulis_quran' => 'nullable|numeric|min:0|max:100',
            'nilai_hafalan' => 'nullable|numeric|min:0|max:100',
            'jumlah_juz_hafalan' => 'nullable|integer|min:0|max:30',
            'catatan_penguji' => 'nullable|string|max:1000',
            'action' => 'required|in:save,submit',
        ]);

        $sesiUjian = $ruangUjian->sesiUjian;
        $calonSiswa = $pesertaRuang->calonSiswa;

        // Get or create nilai
        $nilai = NilaiSeleksi::firstOrNew([
            'sesi_ujian_id' => $sesiUjian->id,
            'calon_siswa_id' => $calonSiswa->id,
            'penguji_id' => $user->id,
        ]);

        // Check if editable
        if ($nilai->exists && !$nilai->isEditable()) {
            return back()->with('error', 'Nilai sudah disubmit dan tidak bisa diubah.');
        }

        $nilai->fill([
            'ruang_ujian_id' => $ruangUjian->id,
            'nilai_wawancara' => $request->nilai_wawancara,
            'nilai_tajwid' => $request->nilai_tajwid,
            'nilai_makhroj' => $request->nilai_makhroj,
            'nilai_kelancaran' => $request->nilai_kelancaran,
            'nilai_tulis_quran' => $request->nilai_tulis_quran,
            'nilai_hafalan' => $request->nilai_hafalan,
            'jumlah_juz_hafalan' => $request->jumlah_juz_hafalan,
            'catatan_penguji' => $request->catatan_penguji,
            'status' => $request->action === 'submit' 
                ? NilaiSeleksi::STATUS_SUBMITTED 
                : NilaiSeleksi::STATUS_DRAFT,
        ]);

        $nilai->save();
        $nilai->updateTotalNilai();

        // Update peserta_ruang status
        if ($request->action === 'submit') {
            $pesertaRuang->update(['status' => PesertaRuang::STATUS_COMPLETED]);
        } elseif ($pesertaRuang->status === PesertaRuang::STATUS_WAITING) {
            // If saving draft and still waiting, mark in_progress
            $pesertaRuang->update(['status' => PesertaRuang::STATUS_IN_PROGRESS]);
        }

        $message = $request->action === 'submit' 
            ? 'Nilai berhasil disubmit.' 
            : 'Nilai berhasil disimpan sebagai draft.';

        // Redirect based on request
        if ($request->has('next') && $request->next) {
            return redirect()
                ->route('penguji.input-nilai', [$ruangUjian->id, $request->next])
                ->with('success', $message);
        }

        return redirect()
            ->route('penguji.ruangan', $ruangUjian->id)
            ->with('success', $message);
    }
}
