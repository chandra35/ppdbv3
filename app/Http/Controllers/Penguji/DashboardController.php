<?php

namespace App\Http\Controllers\Penguji;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\PengujiRuang;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use App\Models\CalonSiswa;
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

        $autoSaveRoute = route('penguji.auto-save-nilai', [$ruangUjian->id, $pesertaRuang->id]);

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
            'dokumenTambahanLabels',
            'autoSaveRoute'
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

    /**
     * Auto-save nilai (AJAX) - saves individual field changes as draft
     * Called automatically when penguji changes any input value
     */
    public function autoSaveNilai(Request $request, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $user = Auth::user();

        // Check if penguji is assigned
        $isAssigned = PengujiRuang::where('user_id', $user->id)
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->where('is_active', true)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Allowed fields
        $allowedFields = [
            'nilai_wawancara', 'nilai_tajwid', 'nilai_makhroj', 'nilai_kelancaran',
            'nilai_tulis_quran', 'nilai_hafalan', 'jumlah_juz_hafalan', 'catatan_penguji',
        ];

        $field = $request->input('field');
        $value = $request->input('value');

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid.'], 422);
        }

        // Validate value
        if (in_array($field, ['nilai_wawancara', 'nilai_tajwid', 'nilai_makhroj', 'nilai_kelancaran', 'nilai_tulis_quran', 'nilai_hafalan'])) {
            if ($value !== null && $value !== '') {
                if (!is_numeric($value) || $value < 0 || $value > 100) {
                    return response()->json(['success' => false, 'message' => 'Nilai harus 0-100.'], 422);
                }
            } else {
                $value = null;
            }
        } elseif ($field === 'jumlah_juz_hafalan') {
            $value = $value !== null && $value !== '' ? (int) $value : null;
        }

        $sesiUjian = $ruangUjian->sesiUjian;
        $calonSiswa = $pesertaRuang->calonSiswa;

        // Get or create nilai
        $nilai = NilaiSeleksi::firstOrNew([
            'sesi_ujian_id' => $sesiUjian->id,
            'calon_siswa_id' => $calonSiswa->id,
            'penguji_id' => $user->id,
        ], [
            'ruang_ujian_id' => $ruangUjian->id,
            'status' => NilaiSeleksi::STATUS_DRAFT,
        ]);

        // Check if editable
        if ($nilai->exists && !$nilai->isEditable()) {
            return response()->json(['success' => false, 'message' => 'Nilai sudah disubmit.'], 422);
        }

        $nilai->ruang_ujian_id = $ruangUjian->id;
        $nilai->$field = $value;
        $nilai->status = NilaiSeleksi::STATUS_DRAFT;
        $nilai->save();
        $nilai->updateTotalNilai();

        // Mark peserta as in_progress if still waiting
        if ($pesertaRuang->status === PesertaRuang::STATUS_WAITING) {
            $pesertaRuang->update(['status' => PesertaRuang::STATUS_IN_PROGRESS]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tersimpan otomatis.',
            'saved_at' => now()->format('H:i:s'),
            'field' => $field,
            'total_nilai' => $nilai->fresh()->total_nilai,
        ]);
    }

    /**
     * Cari peserta susulan (AJAX) - pendaftar yang sudah finalisasi tapi belum di ruangan ini
     */
    public function cariPeserta(Request $request, RuangUjian $ruangUjian)
    {
        $user = Auth::user();

        // Check if penguji is assigned
        $isAssigned = PengujiRuang::where('user_id', $user->id)
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->where('is_active', true)
            ->exists();

        if (!$isAssigned) {
            return response()->json([], 403);
        }

        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $sesiUjian = $ruangUjian->sesiUjian;

        // Pendaftar yang sudah ada di sesi ini
        $sudahDiAssign = PesertaRuang::where('sesi_ujian_id', $sesiUjian->id)
            ->pluck('calon_siswa_id');

        $results = CalonSiswa::where('is_finalisasi', true)
            ->where(function ($query) use ($q) {
                $query->where('nama_lengkap', 'LIKE', "%{$q}%")
                    ->orWhere('nisn', 'LIKE', "%{$q}%")
                    ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%")
                    ->orWhere('nomor_tes', 'LIKE', "%{$q}%");
            })
            ->whereNotIn('id', $sudahDiAssign)
            ->limit(10)
            ->get(['id', 'nama_lengkap', 'nisn', 'no_pendaftaran', 'nomor_tes', 'jenis_kelamin']);

        return response()->json($results);
    }

    /**
     * Tambah peserta susulan ke ruangan
     */
    public function tambahPeserta(Request $request, RuangUjian $ruangUjian)
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

        $request->validate([
            'calon_siswa_id' => 'required|exists:calon_siswas,id',
        ]);

        $sesiUjian = $ruangUjian->sesiUjian;

        // Cek sudah terdaftar
        $exists = PesertaRuang::where('sesi_ujian_id', $sesiUjian->id)
            ->where('calon_siswa_id', $request->calon_siswa_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Peserta sudah terdaftar di sesi ini.');
        }

        // Nomor urut berikutnya
        $lastUrut = PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)
            ->max('nomor_urut') ?? 0;

        PesertaRuang::create([
            'sesi_ujian_id' => $sesiUjian->id,
            'ruang_ujian_id' => $ruangUjian->id,
            'calon_siswa_id' => $request->calon_siswa_id,
            'nomor_urut' => $lastUrut + 1,
        ]);

        // Update jumlah peserta
        $ruangUjian->update([
            'jumlah_peserta' => PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)->count(),
        ]);

        $nama = CalonSiswa::find($request->calon_siswa_id)->nama_lengkap ?? '';

        return back()->with('success', "Peserta susulan {$nama} berhasil ditambahkan.");
    }
}
