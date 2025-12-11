<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPpdb;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = JadwalPpdb::orderBy('urutan')->orderBy('tanggal_mulai')->get();
        return view('admin.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $maxUrutan = JadwalPpdb::max('urutan') ?? 0;
        return view('admin.jadwal.create', compact('maxUrutan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'warna' => 'nullable|string|max:20',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $jadwal = JadwalPpdb::create($validated);

        ActivityLog::log('create', "Menambah jadwal: {$jadwal->nama_kegiatan}", $jadwal);

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(JadwalPpdb $jadwal)
    {
        return view('admin.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, JadwalPpdb $jadwal)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'warna' => 'nullable|string|max:20',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        
        $oldValues = $jadwal->toArray();
        $jadwal->update($validated);

        ActivityLog::log('update', "Mengupdate jadwal: {$jadwal->nama_kegiatan}", $jadwal, $oldValues, $jadwal->fresh()->toArray());

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil diupdate');
    }

    public function destroy(JadwalPpdb $jadwal)
    {
        $namaKegiatan = $jadwal->nama_kegiatan;
        $jadwal->delete();

        ActivityLog::log('delete', "Menghapus jadwal: {$namaKegiatan}");

        return redirect()->route('admin.ppdb.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /**
     * Toggle jadwal status
     */
    public function toggleStatus(JadwalPpdb $jadwal)
    {
        $jadwal->update(['is_active' => !$jadwal->is_active]);

        $status = $jadwal->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('update', "Jadwal {$jadwal->nama_kegiatan} {$status}", $jadwal);

        return redirect()->back()->with('success', "Jadwal berhasil {$status}");
    }
}
