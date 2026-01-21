<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformasiPendaftar;
use Illuminate\Http\Request;

class InformasiPendaftarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $informasiList = InformasiPendaftar::orderBy('urutan')->get();
        return view('admin.settings.informasi-pendaftar.index', compact('informasiList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'is_active' => 'boolean',
            'tampilkan_modal' => 'boolean',
        ]);

        $maxUrutan = InformasiPendaftar::max('urutan') ?? 0;

        InformasiPendaftar::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'is_active' => $request->boolean('is_active', true),
            'tampilkan_modal' => $request->boolean('tampilkan_modal', true),
            'urutan' => $maxUrutan + 1,
        ]);

        return redirect()->route('admin.settings.informasi-pendaftar.index')
            ->with('success', 'Informasi berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'is_active' => 'boolean',
            'tampilkan_modal' => 'boolean',
        ]);

        $informasi = InformasiPendaftar::findOrFail($id);
        $informasi->update([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'is_active' => $request->boolean('is_active'),
            'tampilkan_modal' => $request->boolean('tampilkan_modal'),
        ]);

        return redirect()->route('admin.settings.informasi-pendaftar.index')
            ->with('success', 'Informasi berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $informasi = InformasiPendaftar::findOrFail($id);
        $informasi->delete();

        return redirect()->route('admin.settings.informasi-pendaftar.index')
            ->with('success', 'Informasi berhasil dihapus');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $informasi = InformasiPendaftar::findOrFail($id);
        $informasi->update(['is_active' => !$informasi->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $informasi->is_active,
            'message' => $informasi->is_active ? 'Informasi diaktifkan' : 'Informasi dinonaktifkan'
        ]);
    }

    /**
     * Toggle modal status
     */
    public function toggleModal($id)
    {
        $informasi = InformasiPendaftar::findOrFail($id);
        $informasi->update(['tampilkan_modal' => !$informasi->tampilkan_modal]);

        return response()->json([
            'success' => true,
            'tampilkan_modal' => $informasi->tampilkan_modal,
            'message' => $informasi->tampilkan_modal ? 'Tampilkan di modal' : 'Tidak tampil di modal'
        ]);
    }

    /**
     * Update order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:informasi_pendaftar,id',
        ]);

        foreach ($request->order as $index => $id) {
            InformasiPendaftar::where('id', $id)->update(['urutan' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan berhasil diupdate'
        ]);
    }
}
