<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PendaftarController extends Controller
{
    public function index(Request $request)
    {
        $query = CalonSiswa::with('user')->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        $pendaftars = $query->paginate(20);

        return view('operator.pendaftar.index', compact('pendaftars'));
    }

    public function show($id)
    {
        $pendaftar = CalonSiswa::with(['user', 'dokumen'])->findOrFail($id);
        return view('operator.pendaftar.show', compact('pendaftar'));
    }

    public function verify(Request $request, $id)
    {
        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;
        
        $pendaftar->update([
            'status_verifikasi' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        ActivityLog::log('verify', "Memverifikasi pendaftar: {$pendaftar->nama_lengkap}", $pendaftar, 
            ['status' => $oldStatus], ['status' => 'verified']);

        return redirect()->back()->with('success', 'Pendaftar berhasil diverifikasi.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        $pendaftar->update([
            'status_verifikasi' => 'rejected',
            'rejection_reason' => $request->alasan,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        ActivityLog::log('reject', "Menolak pendaftar: {$pendaftar->nama_lengkap}. Alasan: {$request->alasan}", $pendaftar,
            ['status' => $oldStatus], ['status' => 'rejected']);

        return redirect()->back()->with('warning', 'Pendaftar ditolak.');
    }

    public function verifikasiDokumen(Request $request)
    {
        $query = CalonSiswa::with(['dokumen'])
            ->whereHas('dokumen')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->whereHas('dokumen', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        $pendaftars = $query->paginate(20);

        return view('operator.pendaftar.verifikasi-dokumen', compact('pendaftars'));
    }

    public function verifikasiDokumenDetail($id)
    {
        $pendaftar = CalonSiswa::with(['dokumen'])->findOrFail($id);
        return view('operator.pendaftar.verifikasi-dokumen-detail', compact('pendaftar'));
    }

    public function updateVerifikasiDokumen(Request $request, $id)
    {
        $dokumen = CalonDokumen::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,verified,rejected',
            'catatan' => 'nullable|string|max:500',
        ]);

        $oldStatus = $dokumen->status;

        $dokumen->update([
            'status' => $request->status,
            'catatan_verifikasi' => $request->catatan,
            'verified_at' => $request->status === 'verified' ? now() : null,
            'verified_by' => $request->status === 'verified' ? auth()->id() : null,
        ]);

        ActivityLog::log('update', "Memverifikasi dokumen {$dokumen->jenis_dokumen}", $dokumen,
            ['status' => $oldStatus], ['status' => $request->status]);

        return redirect()->back()->with('success', 'Verifikasi dokumen berhasil diupdate.');
    }
}
