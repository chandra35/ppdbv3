<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\GelombangPendaftaran;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataManagementController extends Controller
{
    public function deleteList(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $query = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran']);
        $this->applyContextFilters($query, $context);

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $pendaftars = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('admin.data-management.delete-list', [
            'pendaftars' => $pendaftars,
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->contextInfo($context),
        ]);
    }

    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );

        $query = CalonSiswa::onlyTrashed()
            ->with(['deletedBy', 'jalurPendaftaran', 'gelombangPendaftaran']);
        $this->applyContextFilters($query, $context);

        if ($request->filled('start_date')) {
            $query->whereDate('deleted_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('deleted_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $deletedData = $query->latest('deleted_at')->paginate(20)->withQueryString();

        return view('admin.data-management.index', [
            'deletedData' => $deletedData,
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => $this->contextInfo($context),
        ]);
    }

    public function restore($id)
    {
        try {
            $pendaftar = CalonSiswa::withTrashed()->findOrFail($id);

            if (!$pendaftar->trashed()) {
                return back()->with('error', 'Data tidak dalam status terhapus.');
            }

            $pendaftar->restore();

            return back()->with('success', "Data {$pendaftar->nama_lengkap} berhasil dipulihkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulihkan data: ' . $e->getMessage());
        }
    }

    public function restoreBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id',
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

    public function forceDelete($id)
    {
        try {
            $pendaftar = CalonSiswa::withTrashed()->findOrFail($id);

            if (!$pendaftar->trashed()) {
                return back()->with('error', 'Data harus dihapus (soft delete) terlebih dahulu.');
            }

            $nama = $pendaftar->nama_lengkap;
            $pendaftar->forceDelete();

            return back()->with('success', "Data {$nama} berhasil dihapus permanen.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data permanen: ' . $e->getMessage());
        }
    }

    public function forceDeleteBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id',
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

    public function bulkDeleteByGelombang(Request $request)
    {
        $request->validate([
            'gelombang_id' => 'required|exists:gelombang_pendaftarans,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $gelombang = GelombangPendaftaran::findOrFail($request->gelombang_id);
            $pendaftars = CalonSiswa::where('gelombang_pendaftaran_id', $request->gelombang_id)->get();

            $count = 0;
            foreach ($pendaftars as $pendaftar) {
                $pendaftar->deleted_by = auth()->id();
                $pendaftar->deleted_reason = $request->reason ?? "Hapus massal gelombang: {$gelombang->nama}";
                $pendaftar->save();
                $pendaftar->delete();
                $count++;
            }

            DB::commit();

            return back()->with('success', "{$count} data dari gelombang {$gelombang->nama} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function applyContextFilters($query, array $context): void
    {
        if ($context['selectedTahun']) {
            $query->where('tahun_pelajaran_id', $context['selectedTahun']->id);
        }

        if ($context['jalurFilterId']) {
            $query->where('jalur_pendaftaran_id', $context['jalurFilterId']);
        }

        if ($context['gelombangFilterId']) {
            $query->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
        }
    }

    private function contextInfo(array $context): array
    {
        return [
            'tahun' => $context['selectedTahun']?->nama ?? '-',
            'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
            'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
        ];
    }
}
