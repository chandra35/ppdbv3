<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlurPendaftaran;
use Illuminate\Http\Request;

class AlurPendaftaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alurs = AlurPendaftaran::ordered()->get();
        $iconList = AlurPendaftaran::ICON_LIST;
        
        return view('admin.alur-pendaftaran.index', compact('alurs', 'iconList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['urutan'] = AlurPendaftaran::getNextUrutan();
        $validated['is_active'] = $request->has('is_active');
        $validated['icon'] = $validated['icon'] ?: 'fas fa-circle';

        AlurPendaftaran::create($validated);

        return redirect()->route('admin.alur-pendaftaran.index')
            ->with('success', 'Alur pendaftaran berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlurPendaftaran $alurPendaftaran)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['icon'] = $validated['icon'] ?: 'fas fa-circle';

        $alurPendaftaran->update($validated);

        return redirect()->route('admin.alur-pendaftaran.index')
            ->with('success', 'Alur pendaftaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlurPendaftaran $alurPendaftaran)
    {
        $alurPendaftaran->delete();

        // Reorder remaining items
        $alurs = AlurPendaftaran::ordered()->get();
        foreach ($alurs as $index => $alur) {
            $alur->update(['urutan' => $index + 1]);
        }

        return redirect()->route('admin.alur-pendaftaran.index')
            ->with('success', 'Alur pendaftaran berhasil dihapus');
    }

    /**
     * Update order of alur items
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|uuid',
        ]);

        foreach ($request->order as $index => $id) {
            AlurPendaftaran::where('id', $id)->update(['urutan' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan berhasil diperbarui']);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(AlurPendaftaran $alurPendaftaran)
    {
        $alurPendaftaran->update(['is_active' => !$alurPendaftaran->is_active]);

        return redirect()->route('admin.alur-pendaftaran.index')
            ->with('success', 'Status alur pendaftaran berhasil diubah');
    }
}
