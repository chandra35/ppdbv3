<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\CalonSiswa;
use Illuminate\Http\Request;

class PesertaRuangController extends Controller
{
    /**
     * Halaman kelola peserta per ruangan
     */
    public function index(SesiUjian $sesiUjian, RuangUjian $ruangUjian)
    {
        $pesertaList = PesertaRuang::with(['calonSiswa'])
            ->where('ruang_ujian_id', $ruangUjian->id)
            ->orderBy('nomor_urut')
            ->get();

        // Semua ruangan di sesi ini untuk opsi pindah
        $semuaRuangan = RuangUjian::where('sesi_ujian_id', $sesiUjian->id)
            ->orderBy('nomor_ruang')
            ->get();

        return view('admin.peserta-ruang.index', compact(
            'sesiUjian',
            'ruangUjian',
            'pesertaList',
            'semuaRuangan'
        ));
    }

    /**
     * Cari pendaftar untuk ditambahkan ke ruangan (AJAX)
     */
    public function cariPendaftar(Request $request, SesiUjian $sesiUjian)
    {
        $q = $request->get('q', '');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        // Cari pendaftar yang sudah finalisasi tapi belum ada di sesi ini
        $sudahDiAssign = PesertaRuang::where('sesi_ujian_id', $sesiUjian->id)
            ->pluck('calon_siswa_id');

        $results = CalonSiswa::where('is_finalisasi', true)
            ->where(function ($query) use ($q) {
                $query->where('nama_lengkap', 'LIKE', "%{$q}%")
                    ->orWhere('nisn', 'LIKE', "%{$q}%")
                    ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%");
            })
            ->whereNotIn('id', $sudahDiAssign)
            ->limit(15)
            ->get(['id', 'nama_lengkap', 'nisn', 'no_pendaftaran', 'jenis_kelamin', 'nama_sekolah_asal']);

        return response()->json($results);
    }

    /**
     * Tambah peserta ke ruangan
     */
    public function tambahPeserta(Request $request, SesiUjian $sesiUjian, RuangUjian $ruangUjian)
    {
        $request->validate([
            'calon_siswa_id' => 'required|exists:calon_siswas,id',
        ]);

        // Cek apakah sudah ada di sesi ini
        $exists = PesertaRuang::where('sesi_ujian_id', $sesiUjian->id)
            ->where('calon_siswa_id', $request->calon_siswa_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Pendaftar sudah terdaftar di salah satu ruangan di sesi ini.');
        }

        // Tentukan nomor urut berikutnya
        $lastUrut = PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)
            ->max('nomor_urut') ?? 0;

        PesertaRuang::create([
            'sesi_ujian_id' => $sesiUjian->id,
            'ruang_ujian_id' => $ruangUjian->id,
            'calon_siswa_id' => $request->calon_siswa_id,
            'nomor_urut' => $lastUrut + 1,
        ]);

        // Update jumlah peserta di ruangan
        $ruangUjian->update([
            'jumlah_peserta' => PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)->count(),
        ]);

        $nama = CalonSiswa::find($request->calon_siswa_id)->nama_lengkap ?? '';

        return back()->with('success', "Peserta {$nama} berhasil ditambahkan ke ruangan.");
    }

    /**
     * Hapus peserta dari ruangan
     */
    public function hapusPeserta(SesiUjian $sesiUjian, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $nama = $pesertaRuang->calonSiswa->nama_lengkap ?? '';
        $pesertaRuang->delete();

        // Update jumlah peserta
        $ruangUjian->update([
            'jumlah_peserta' => PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)->count(),
        ]);

        return back()->with('success', "Peserta {$nama} berhasil dihapus dari ruangan.");
    }

    /**
     * Pindah peserta ke ruangan lain
     */
    public function pindahPeserta(Request $request, SesiUjian $sesiUjian, RuangUjian $ruangUjian, PesertaRuang $pesertaRuang)
    {
        $request->validate([
            'ruangan_tujuan_id' => 'required|exists:ruang_ujian,id',
        ]);

        $ruangTujuan = RuangUjian::findOrFail($request->ruangan_tujuan_id);

        // Cek ruangan tujuan di sesi yang sama
        if ($ruangTujuan->sesi_ujian_id !== $sesiUjian->id) {
            return back()->with('error', 'Ruangan tujuan bukan bagian dari sesi ini.');
        }

        // Tentukan nomor urut di ruangan tujuan
        $lastUrut = PesertaRuang::where('ruang_ujian_id', $ruangTujuan->id)
            ->max('nomor_urut') ?? 0;

        $nama = $pesertaRuang->calonSiswa->nama_lengkap ?? '';

        // Update peserta ke ruangan baru
        $pesertaRuang->update([
            'ruang_ujian_id' => $ruangTujuan->id,
            'nomor_urut' => $lastUrut + 1,
        ]);

        // Update jumlah peserta di kedua ruangan
        $ruangUjian->update([
            'jumlah_peserta' => PesertaRuang::where('ruang_ujian_id', $ruangUjian->id)->count(),
        ]);
        $ruangTujuan->update([
            'jumlah_peserta' => PesertaRuang::where('ruang_ujian_id', $ruangTujuan->id)->count(),
        ]);

        return back()->with('success', "Peserta {$nama} dipindahkan ke {$ruangTujuan->nama_ruang}.");
    }
}
