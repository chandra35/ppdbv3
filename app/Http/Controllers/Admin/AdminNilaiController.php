<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\CalonDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNilaiController extends Controller
{
    /**
     * Show input nilai form (admin - tanpa harus di-assign sebagai penguji)
     */
    public function inputNilai(SesiUjian $sesiUjian, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $user = Auth::user();
        $calonSiswa = $pesertaRuang->calonSiswa;

        // Get or create nilai (admin sebagai penguji)
        $nilai = NilaiSeleksi::firstOrNew([
            'sesi_ujian_id' => $sesiUjian->id,
            'calon_siswa_id' => $calonSiswa->id,
            'penguji_id' => $user->id,
        ], [
            'ruang_ujian_id' => $ruangUjian->id,
            'status' => NilaiSeleksi::STATUS_DRAFT,
        ]);

        // Get bobot
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $sesiUjian->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Prev/next peserta
        $allPeserta = PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)
            ->orderBy('nomor_urut')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($pesertaRuang->id, $allPeserta);
        $prevPeserta = $currentIndex > 0 ? $allPeserta[$currentIndex - 1] : null;
        $nextPeserta = $currentIndex < count($allPeserta) - 1 ? $allPeserta[$currentIndex + 1] : null;

        // Dokumen
        $dokumenList = $calonSiswa->dokumen()->orderBy('jenis_dokumen')->get();
        $dokumenLabels = CalonDokumen::JENIS_DOKUMEN;

        // Custom routes for admin (reuse the same view)
        $saveRoute = route('admin.sesi-ujian.admin-save-nilai', [$sesiUjian->id, $ruangUjian->id, $pesertaRuang->id]);
        $backRoute = route('admin.sesi-ujian.peserta-ruang', [$sesiUjian->id, $ruangUjian->id]);
        $prevRoute = $prevPeserta 
            ? route('admin.sesi-ujian.admin-input-nilai', [$sesiUjian->id, $ruangUjian->id, $prevPeserta]) 
            : null;
        $nextRoute = $nextPeserta 
            ? route('admin.sesi-ujian.admin-input-nilai', [$sesiUjian->id, $ruangUjian->id, $nextPeserta]) 
            : null;

        // Reuse same view as penguji
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
            'saveRoute',
            'backRoute',
            'prevRoute',
            'nextRoute'
        ));
    }

    /**
     * Save nilai (admin direct)
     */
    public function saveNilai(Request $request, SesiUjian $sesiUjian, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
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

        $calonSiswa = $pesertaRuang->calonSiswa;

        $nilai = NilaiSeleksi::firstOrNew([
            'sesi_ujian_id' => $sesiUjian->id,
            'calon_siswa_id' => $calonSiswa->id,
            'penguji_id' => $user->id,
        ]);

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

        $message = $request->action === 'submit' 
            ? 'Nilai berhasil disubmit.' 
            : 'Nilai berhasil disimpan sebagai draft.';

        // Next peserta
        if ($request->has('next') && $request->next) {
            return redirect()
                ->route('admin.sesi-ujian.admin-input-nilai', [$sesiUjian->id, $ruangUjian->id, $request->next])
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.sesi-ujian.peserta-ruang', [$sesiUjian->id, $ruangUjian->id])
            ->with('success', $message);
    }
}
