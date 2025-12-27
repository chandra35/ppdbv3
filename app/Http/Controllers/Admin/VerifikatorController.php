<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Verifikator;
use App\Models\User;
use App\Models\PpdbSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class VerifikatorController extends Controller
{
    /**
     * Display list of verifikators
     */
    public function index()
    {
        // Get all verifikators with User relation
        $verifikators = Verifikator::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available users (not yet assigned as verifikator)
        // Preferably users that are staff/admin/operator
        $availableUsers = User::whereDoesntHave('verifikator')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Get active PPDB settings (status_pendaftaran = true)
        $activePpdbSettings = PpdbSettings::where('status_pendaftaran', true)->first();

        return view('admin.ppdb.verifikator', compact('verifikators', 'availableUsers', 'activePpdbSettings'));
    }

    /**
     * Assign User as verifikator
     */
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'Silakan pilih user yang akan dijadikan verifikator.',
            'user_id.exists' => 'Data user tidak ditemukan.',
        ]);

        try {
            DB::beginTransaction();

            // Get active PPDB settings (status_pendaftaran = true)
            $activePpdbSettings = PpdbSettings::where('status_pendaftaran', true)->first();

            if (!$activePpdbSettings) {
                throw new Exception('Tidak ada PPDB settings aktif. Silakan aktifkan PPDB settings terlebih dahulu.');
            }

            // Check if user already assigned
            $existingVerifikator = Verifikator::where('user_id', $request->user_id)
                ->where('ppdb_settings_id', $activePpdbSettings->id)
                ->first();

            if ($existingVerifikator) {
                throw new Exception('User ini sudah terdaftar sebagai verifikator.');
            }

            // Get user data
            $user = User::findOrFail($request->user_id);

            // Create verifikator assignment
            Verifikator::create([
                'user_id' => $request->user_id,
                'ppdb_settings_id' => $activePpdbSettings->id,
                'jenis_dokumen_aktif' => [], // Default empty, bisa diatur nanti
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.verifikator.index')
                ->with('success', "User {$user->name} berhasil ditambahkan sebagai verifikator.");

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete verifikator assignment
     */
    public function delete($id)
    {
        try {
            $verifikator = Verifikator::with('user')->findOrFail($id);
            $userName = $verifikator->user->name ?? 'N/A';

            // Check if verifikator has verified any documents
            $verifiedCount = $verifikator->calonDokumen()->count();

            if ($verifiedCount > 0) {
                return redirect()->back()
                    ->with('warning', "Verifikator {$userName} tidak dapat dihapus karena sudah memverifikasi {$verifiedCount} dokumen.");
            }

            $verifikator->delete();

            return redirect()->route('admin.verifikator.index')
                ->with('success', "Verifikator {$userName} berhasil dihapus.");

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle verifikator status
     */
    public function toggleStatus($id)
    {
        try {
            $verifikator = Verifikator::with('user')->findOrFail($id);
            $verifikator->is_active = !$verifikator->is_active;
            $verifikator->save();

            $status = $verifikator->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $userName = $verifikator->user->name ?? 'N/A';

            return redirect()->back()
                ->with('success', "Verifikator {$userName} berhasil {$status}.");

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
