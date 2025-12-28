<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataManagementController extends Controller
{
    /**
     * Display list of active pendaftar for deletion.
     */
    public function deleteList(Request $request)
    {
        $query = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran']);

        // Filter by gelombang
        if ($request->filled('gelombang_id')) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        // Search by name or NISN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $pendaftars = $query->latest('created_at')->paginate(20);

        // Get gelombang for filter
        $gelombangs = \App\Models\GelombangPendaftaran::all();

        return view('admin.data-management.delete-list', compact('pendaftars', 'gelombangs'));
    }

    /**
     * Display a listing of deleted data.
     */
    public function index(Request $request)
    {
        $query = CalonSiswa::onlyTrashed()
            ->with(['deletedBy', 'jalurPendaftaran', 'gelombangPendaftaran']);

        // Filter by gelombang
        if ($request->filled('gelombang_id')) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('deleted_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('deleted_at', '<=', $request->end_date);
        }

        // Search by name or NISN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $deletedData = $query->latest('deleted_at')->paginate(20);

        // Get gelombang for filter
        $gelombangs = \App\Models\GelombangPendaftaran::all();

        return view('admin.data-management.index', compact('deletedData', 'gelombangs'));
    }

    /**
     * Restore single deleted record.
     */
    public function restore($id)
    {
        try {
            $pendaftar = CalonSiswa::withTrashed()->findOrFail($id);
            
            if (!$pendaftar->trashed()) {
                return back()->with('error', 'Data tidak dalam status terhapus.');
            }

            // Restore akan trigger restoring event di model (cascade restore)
            $pendaftar->restore();

            return back()->with('success', "Data {$pendaftar->nama_lengkap} berhasil dipulihkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulihkan data: ' . $e->getMessage());
        }
    }

    /**
     * Restore multiple records.
     */
    public function restoreBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id'
        ]);

        try {
            DB::beginTransaction();

            $restored = 0;
            foreach ($request->ids as $id) {
                $pendaftar = CalonSiswa::withTrashed()->find($id);
                if ($pendaftar && $pendaftar->trashed()) {
                    $pendaftar->restore();
                    $restored++;
                }
            }

            DB::commit();

            return back()->with('success', "{$restored} data berhasil dipulihkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulihkan data: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete single record.
     */
    public function forceDelete($id)
    {
        try {
            $pendaftar = CalonSiswa::withTrashed()->findOrFail($id);
            
            if (!$pendaftar->trashed()) {
                return back()->with('error', 'Data harus dihapus (soft delete) terlebih dahulu.');
            }

            $nama = $pendaftar->nama_lengkap;

            // Force delete akan trigger forceDeleting event di model (cascade + hapus files)
            $pendaftar->forceDelete();

            return back()->with('success', "Data {$nama} berhasil dihapus permanen.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data permanen: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete multiple records.
     */
    public function forceDeleteBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id'
        ]);

        try {
            DB::beginTransaction();

            $deleted = 0;
            foreach ($request->ids as $id) {
                $pendaftar = CalonSiswa::withTrashed()->find($id);
                if ($pendaftar && $pendaftar->trashed()) {
                    $pendaftar->forceDelete();
                    $deleted++;
                }
            }

            DB::commit();

            return back()->with('success', "{$deleted} data berhasil dihapus permanen.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data permanen: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete all data from a specific gelombang.
     */
    public function bulkDeleteByGelombang(Request $request)
    {
        $request->validate([
            'gelombang_id' => 'required|exists:gelombang_pendaftarans,id',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $gelombang = \App\Models\GelombangPendaftaran::findOrFail($request->gelombang_id);
            $pendaftars = CalonSiswa::where('gelombang_pendaftaran_id', $request->gelombang_id)->get();

            $count = 0;
            foreach ($pendaftars as $pendaftar) {
                $pendaftar->deleted_by = auth()->id();
                $pendaftar->deleted_reason = $request->reason ?? "Hapus massal gelombang: {$gelombang->nama}";
                $pendaftar->save();
                $pendaftar->delete(); // Soft delete dengan cascade
                $count++;
            }

            DB::commit();

            return back()->with('success', "{$count} data dari gelombang {$gelombang->nama} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
