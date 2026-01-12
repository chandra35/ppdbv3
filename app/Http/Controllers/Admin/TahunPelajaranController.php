<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class TahunPelajaranController extends Controller
{
    /**
     * Display listing
     */
    public function index()
    {
        $tahunPelajaranList = TahunPelajaran::orderBy('nama', 'desc')->get();
        $tahunAktif = TahunPelajaran::getAktif();
        
        return view('admin.tahun-pelajaran.index', compact('tahunPelajaranList', 'tahunAktif'));
    }

    /**
     * Show create form - redirect ke index karena pakai modal
     */
    public function create()
    {
        return redirect()->route('admin.tahun-pelajaran.index');
    }

    /**
     * Store new tahun pelajaran
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_pelajarans,nama|regex:/^\d{4}\/\d{4}$/',
            'keterangan' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ], [
            'nama.regex' => 'Format tahun pelajaran harus TAHUN/TAHUN (contoh: 2025/2026)',
            'nama.unique' => 'Tahun pelajaran ini sudah ada',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        $tahunPelajaran = TahunPelajaran::create($validated);
        
        return redirect()
            ->route('admin.tahun-pelajaran.index')
            ->with('success', "Tahun Pelajaran \"{$tahunPelajaran->nama}\" berhasil dibuat!");
    }

    /**
     * Show edit form - redirect ke index karena pakai modal
     */
    public function edit(TahunPelajaran $tahunPelajaran)
    {
        return redirect()->route('admin.tahun-pelajaran.index');
    }

    /**
     * Update tahun pelajaran
     */
    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_pelajarans,nama,' . $tahunPelajaran->id . '|regex:/^\d{4}\/\d{4}$/',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'nama.regex' => 'Format tahun pelajaran harus TAHUN/TAHUN (contoh: 2025/2026)',
            'nama.unique' => 'Tahun pelajaran ini sudah ada',
        ]);
        
        $tahunPelajaran->update($validated);
        
        return redirect()
            ->route('admin.tahun-pelajaran.index')
            ->with('success', "Tahun Pelajaran \"{$tahunPelajaran->nama}\" berhasil diperbarui!");
    }

    /**
     * Delete tahun pelajaran
     */
    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        // Proteksi 1: Tidak bisa hapus tahun pelajaran aktif
        if ($tahunPelajaran->is_active) {
            return redirect()->back()->with('error', 
                "Tahun Pelajaran \"{$tahunPelajaran->nama}\" tidak dapat dihapus karena sedang aktif! Aktifkan tahun pelajaran lain terlebih dahulu."
            );
        }
        
        // Proteksi 2: Cek apakah ada jalur yang menggunakan tahun pelajaran ini
        $jalurCount = $tahunPelajaran->jalurPendaftaran()->count();
        if ($jalurCount > 0) {
            return redirect()->back()->with('error', 
                "Tahun Pelajaran \"{$tahunPelajaran->nama}\" tidak dapat dihapus karena sudah digunakan oleh {$jalurCount} jalur pendaftaran! Hapus semua jalur terkait terlebih dahulu."
            );
        }
        
        // Proteksi 3: Cek apakah ada pendaftar di tahun pelajaran ini
        $pendaftarCount = \App\Models\CalonSiswa::where('tahun_pelajaran_id', $tahunPelajaran->id)->count();
        if ($pendaftarCount > 0) {
            return redirect()->back()->with('error', 
                "Tahun Pelajaran \"{$tahunPelajaran->nama}\" tidak dapat dihapus karena sudah memiliki {$pendaftarCount} data pendaftar!"
            );
        }
        
        $nama = $tahunPelajaran->nama;
        $tahunPelajaran->delete();
        
        return redirect()
            ->route('admin.tahun-pelajaran.index')
            ->with('success', "Tahun Pelajaran \"{$nama}\" berhasil dihapus!");
    }

    /**
     * Aktifkan tahun pelajaran
     */
    public function aktifkan(TahunPelajaran $tahunPelajaran)
    {
        if ($tahunPelajaran->aktifkan()) {
            return redirect()->back()->with('success', 
                "Tahun Pelajaran \"{$tahunPelajaran->nama}\" berhasil diaktifkan!"
            );
        }
        
        return redirect()->back()->with('error', 'Gagal mengaktifkan tahun pelajaran!');
    }
}
