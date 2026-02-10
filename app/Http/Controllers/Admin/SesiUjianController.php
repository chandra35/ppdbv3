<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\PengujiRuang;
use App\Models\NilaiSeleksi;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Models\SekolahSettings;
use App\Services\KopSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SesiUjianController extends Controller
{
    /**
     * Display a listing of sesi ujian
     */
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $query = SesiUjian::with(['tahunPelajaran', 'jalur', 'gelombang', 'creator'])
            ->withCount(['ruangan', 'pesertaRuang']);

        if ($request->tahun_pelajaran_id) {
            $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
        } elseif ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sesiUjians = $query->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'desc')
            ->paginate(15)
            ->withQueryString();

        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

        return view('admin.sesi-ujian.index', compact(
            'sesiUjians',
            'tahunAktif',
            'tahunPelajarans'
        ));
    }

    /**
     * Show sesi ujian details
     */
    public function show(SesiUjian $sesiUjian)
    {
        $sesiUjian->load([
            'tahunPelajaran',
            'jalur',
            'gelombang',
            'creator',
            'locker',
            'ruangan.penguji.user.roles',
            'ruangan.peserta.calonSiswa',
        ]);

        // Get users for penguji assignment
        // Include: dedicated penguji role AND admin/verifikator roles
        $pengujiList = User::with('roles')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['penguji', 'admin', 'verifikator', 'super-admin', 'mas-admin']);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate progress
        $totalPeserta = $sesiUjian->pesertaRuang()->count();
        $sudahDinilai = NilaiSeleksi::where('sesi_ujian_id', $sesiUjian->id)
            ->whereIn('status', ['submitted', 'verified'])
            ->distinct('calon_siswa_id')
            ->count('calon_siswa_id');
        
        $progressPenilaian = [
            'total' => $totalPeserta,
            'selesai' => $sudahDinilai,
            'percentage' => $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0,
        ];

        return view('admin.sesi-ujian.show', compact(
            'sesiUjian',
            'pengujiList',
            'progressPenilaian'
        ));
    }

    /**
     * Update sesi ujian status
     */
    public function updateStatus(Request $request, SesiUjian $sesiUjian)
    {
        $request->validate([
            'status' => 'required|in:locked,in_progress,completed',
        ]);

        $sesiUjian->update([
            'status' => $request->status,
        ]);

        $statusLabels = [
            'locked' => 'Terkunci',
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
        ];

        return back()->with('success', 'Status sesi ujian berhasil diubah menjadi: ' . $statusLabels[$request->status]);
    }

    /**
     * Assign penguji to ruangan (AJAX)
     */
    public function assignPenguji(Request $request, SesiUjian $sesiUjian)
    {
        $request->validate([
            'ruang_ujian_id' => 'required|exists:ruang_ujian,id',
            'penguji_ids' => 'required|array',
            'penguji_ids.*' => 'exists:users,id',
            'ketua_id' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $ruangId = $request->ruang_ujian_id;

            // Remove existing penguji for this ruangan
            PengujiRuang::where('sesi_ujian_id', $sesiUjian->id)
                ->where('ruang_ujian_id', $ruangId)
                ->delete();

            // Add new penguji
            foreach ($request->penguji_ids as $pengujiId) {
                PengujiRuang::create([
                    'sesi_ujian_id' => $sesiUjian->id,
                    'ruang_ujian_id' => $ruangId,
                    'user_id' => $pengujiId,
                    'is_ketua' => $pengujiId == $request->ketua_id,
                    'is_active' => true,
                ]);
            }

            DB::commit();

            // Get updated penguji list for response
            $ruang = RuangUjian::with('penguji.user')->find($ruangId);
            $pengujiNames = $ruang->penguji->pluck('user.name')->join(', ');

            return response()->json([
                'success' => true,
                'message' => 'Penguji berhasil di-assign ke ruangan.',
                'penguji_names' => $pengujiNames ?: '-',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign penguji: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get penguji for a ruangan (AJAX)
     */
    public function getPengujiRuangan(SesiUjian $sesiUjian, RuangUjian $ruangUjian)
    {
        $pengujiRuang = PengujiRuang::where('sesi_ujian_id', $sesiUjian->id)
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->with('user.roles')
            ->get();

        $pengujiData = $pengujiRuang->map(function ($pr) {
            return [
                'id' => $pr->id,
                'user_id' => $pr->user_id,
                'name' => $pr->user->name ?? 'Unknown',
                'email' => $pr->user->email ?? '',
                'is_ketua' => $pr->is_ketua,
                'roles' => $pr->user->roles->pluck('display_name')->join(', '),
            ];
        });

        return response()->json([
            'penguji' => $pengujiData,
            'penguji_ids' => $pengujiRuang->pluck('user_id'),
            'ketua_id' => $pengujiRuang->where('is_ketua', true)->first()?->user_id,
        ]);
    }

    /**
     * Delete sesi ujian (only if draft)
     */
    public function destroy(SesiUjian $sesiUjian)
    {
        if ($sesiUjian->status === 'in_progress') {
            return back()->with('error', 'Sesi ujian yang sedang berlangsung tidak bisa dihapus. Selesaikan dulu.');
        }

        if ($sesiUjian->status === 'completed') {
            return back()->with('error', 'Sesi ujian yang sudah selesai tidak bisa dihapus.');
        }

        try {
            DB::beginTransaction();

            // Delete related peserta_ruang
            PesertaRuang::where('sesi_ujian_id', $sesiUjian->id)->delete();

            // Delete related penguji_ruang
            PengujiRuang::where('sesi_ujian_id', $sesiUjian->id)->delete();

            // Delete related nilai_seleksi
            NilaiSeleksi::where('sesi_ujian_id', $sesiUjian->id)->delete();

            // Delete related ruang_ujian
            RuangUjian::where('sesi_ujian_id', $sesiUjian->id)->delete();

            // Delete sesi
            $sesiUjian->delete();

            DB::commit();

            return redirect()
                ->route('admin.sesi-ujian.index')
                ->with('success', 'Sesi ujian berhasil dihapus beserta semua data terkait.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus sesi ujian: ' . $e->getMessage());
        }
    }

    /**
     * Print daftar hadir from saved sesi (PDF with Kop Surat)
     */
    public function printDaftarHadir(SesiUjian $sesiUjian, Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $sesiUjian->load([
            'ruangan.peserta.calonSiswa',
            'tahunPelajaran',
        ]);

        // Build room list with peserta (same format as PenjadwalanUjianController)
        $ruangList = [];

        foreach ($sesiUjian->ruangan as $ruang) {
            $pesertaRuang = PesertaRuang::with('calonSiswa')
                ->where('ruang_ujian_id', $ruang->id)
                ->orderBy('nomor_urut')
                ->get();

            $ruangList[] = [
                'nama' => $ruang->nama_ruang,
                'jenis' => $sesiUjian->jenis_ujian,
                'sesi' => $sesiUjian->nomor_sesi,
                'waktu_mulai' => $sesiUjian->waktu_mulai?->format('H:i') ?? '-',
                'waktu_selesai' => $sesiUjian->waktu_selesai?->format('H:i') ?? '-',
                'kapasitas' => $ruang->kapasitas,
                'peserta' => $pesertaRuang,
            ];
        }

        // Filter specific room if requested
        if ($request->ruang) {
            $ruangList = collect($ruangList)->filter(fn($r) => $r['nama'] === $request->ruang)->values()->toArray();
        }

        $sekolah = SekolahSettings::first();
        $jadwal = (object) [
            'tanggal_ujian' => $sesiUjian->tanggal,
            'tahunPelajaran' => $sesiUjian->tahunPelajaran,
        ];
        $kopSuratService = app(KopSuratService::class);
        $kopSurat = $kopSuratService->renderKopHtml($sekolah, true);

        $pdf = Pdf::loadView('admin.penjadwalan-ujian.pdf.daftar-hadir', compact(
            'jadwal', 'ruangList', 'sekolah', 'kopSurat'
        ));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('daftar-hadir-' . $sesiUjian->nama . '.pdf');
    }

    /**
     * AJAX endpoint: Get peserta status per ruangan for real-time polling
     */
    public function statusPeserta(SesiUjian $sesiUjian)
    {
        $ruangan = $sesiUjian->ruangan()->with(['peserta.calonSiswa'])->get();

        $data = [];
        foreach ($ruangan as $ruang) {
            $pesertaData = [];
            foreach ($ruang->peserta as $pr) {
                $pesertaData[] = [
                    'id' => $pr->id,
                    'nomor_urut' => $pr->nomor_urut,
                    'nama' => $pr->calonSiswa->nama_lengkap ?? '-',
                    'status' => $pr->status ?? 'waiting',
                ];
            }
            $data[$ruang->id] = [
                'nama_ruang' => $ruang->nama_ruang,
                'peserta' => $pesertaData,
            ];
        }

        return response()->json($data);
    }
}
