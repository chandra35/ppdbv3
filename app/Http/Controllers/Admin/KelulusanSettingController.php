<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelulusan;
use App\Models\KelulusanSetting;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class KelulusanSettingController extends Controller
{
    /**
     * Halaman manajemen info kelulusan
     */
    public function index()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return redirect()->route('admin.dashboard')->with('error', 'Tahun pelajaran aktif tidak ditemukan');
        }

        $setting = KelulusanSetting::firstOrCreate(
            ['tahun_pelajaran_id' => $tahunAktif->id],
            [
                'judul_pengumuman' => 'Pengumuman Kelulusan PPDB',
                'pesan_lulus' => 'Selamat! Anda dinyatakan LULUS seleksi PPDB. Silakan bergabung ke grup WhatsApp dan lengkapi persyaratan daftar ulang.',
                'pesan_tidak_lulus' => 'Mohon maaf, Anda belum dinyatakan lulus pada seleksi PPDB tahun ini. Tetap semangat dan jangan menyerah!',
            ]
        );

        // Stats kelulusan
        $stats = [
            'total_lulus' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->lulus()->count(),
            'total_tidak_lulus' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->tidakLulus()->count(),
            'total_cadangan' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->cadangan()->count(),
        ];

        return view('admin.kelulusan.setting', compact('setting', 'tahunAktif', 'stats'));
    }

    /**
     * Update pengaturan kelulusan
     */
    public function update(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $request->validate([
            'judul_pengumuman' => 'required|string|max:255',
            'pesan_lulus' => 'nullable|string',
            'pesan_tidak_lulus' => 'nullable|string',
            'link_grup_wa' => 'nullable|url|max:500',
            'nama_grup_wa' => 'nullable|string|max:255',
            'dokumen_persyaratan' => 'nullable|array',
            'dokumen_persyaratan.*' => 'nullable|string|max:255',
            'template_surat_pernyataan' => 'nullable|string',
            'tanggal_daftar_ulang_mulai' => 'nullable|date',
            'tanggal_daftar_ulang_selesai' => 'nullable|date|after_or_equal:tanggal_daftar_ulang_mulai',
            'catatan_daftar_ulang' => 'nullable|string',
        ]);

        $setting = KelulusanSetting::where('tahun_pelajaran_id', $tahunAktif->id)->first();

        // Filter out empty dokumen_persyaratan entries
        $dokumen = $request->dokumen_persyaratan ? array_values(array_filter($request->dokumen_persyaratan)) : [];

        $setting->update([
            'judul_pengumuman' => $request->judul_pengumuman,
            'pesan_lulus' => $request->pesan_lulus,
            'pesan_tidak_lulus' => $request->pesan_tidak_lulus,
            'link_grup_wa' => $request->link_grup_wa,
            'nama_grup_wa' => $request->nama_grup_wa,
            'dokumen_persyaratan' => $dokumen,
            'template_surat_pernyataan' => $request->template_surat_pernyataan,
            'tampilkan_pengumuman' => $request->has('tampilkan_pengumuman'),
            'tampilkan_link_wa' => $request->has('tampilkan_link_wa'),
            'tampilkan_dokumen' => $request->has('tampilkan_dokumen'),
            'tanggal_daftar_ulang_mulai' => $request->tanggal_daftar_ulang_mulai,
            'tanggal_daftar_ulang_selesai' => $request->tanggal_daftar_ulang_selesai,
            'catatan_daftar_ulang' => $request->catatan_daftar_ulang,
        ]);

        return redirect()->route('admin.kelulusan.setting')
            ->with('success', 'Pengaturan kelulusan berhasil diperbarui');
    }
}
