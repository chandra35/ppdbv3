<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\NilaiRapor;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SystemResetController extends Controller
{
    /**
     * Display the reset system page.
     */
    public function index()
    {
        // Count data for display
        $stats = [
            'pendaftar' => CalonSiswa::withTrashed()->count(),
            'pendaftar_aktif' => CalonSiswa::count(),
            'pendaftar_terhapus' => CalonSiswa::onlyTrashed()->count(),
            'users' => User::whereHas('roles', fn($q) => $q->where('name', 'pendaftar'))->count(),
            'dokumen' => CalonDokumen::withTrashed()->count(),
            'nilai_rapor' => NilaiRapor::count(),
            'ortu' => CalonOrtu::count(),
        ];

        // Get storage size
        $stats['storage_size'] = $this->getStorageSize();

        return view('admin.data-management.reset-system', compact('stats'));
    }

    /**
     * Verify admin password before reset.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah!'
            ], 422);
        }

        // Generate one-time token
        $token = bin2hex(random_bytes(16));
        session(['reset_token' => $token, 'reset_token_expires' => now()->addMinutes(5)]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Password terverifikasi. Token berlaku 5 menit.'
        ]);
    }

    /**
     * Reset ALL pendaftar data (for production deployment).
     */
    public function resetAllPendaftar(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'confirm_text' => 'required|in:RESET SEMUA DATA',
        ]);

        // Validate token
        if (!$this->validateToken($request->token)) {
            return back()->with('error', 'Token tidak valid atau sudah kadaluarsa.');
        }

        try {
            DB::beginTransaction();

            // 1. Delete all dokumen files
            $dokumenPaths = CalonDokumen::withTrashed()->pluck('file_path')->filter();
            foreach ($dokumenPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            // 2. Delete all nilai rapor files
            $nilaiRaporPaths = NilaiRapor::pluck('file_path')->filter();
            foreach ($nilaiRaporPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            // 3. Force delete all data (including soft deleted)
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Delete in correct order
            CalonDokumen::withTrashed()->forceDelete();
            NilaiRapor::query()->forceDelete();
            CalonOrtu::query()->forceDelete();
            
            // Get all pendaftar user IDs before deleting
            $userIds = CalonSiswa::withTrashed()->pluck('user_id')->filter();
            
            CalonSiswa::withTrashed()->forceDelete();
            
            // Delete pendaftar users
            User::whereIn('id', $userIds)->forceDelete();

            // Reset auto-increment counters
            DB::table('ppdb_settings')->update([
                'nomor_registrasi_counter' => 0,
                'nomor_tes_counter' => '[]',
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reset',
                'model_type' => 'System',
                'model_id' => null,
                'description' => 'Reset semua data pendaftar untuk production deployment',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Clear session token
            session()->forget(['reset_token', 'reset_token_expires']);

            return back()->with('success', 'Semua data pendaftar berhasil direset. Sistem siap untuk production.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reset data: ' . $e->getMessage());
        }
    }

    /**
     * Reset only specific gelombang data.
     */
    public function resetByGelombang(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'gelombang_id' => 'required|exists:gelombang_pendaftarans,id',
        ]);

        if (!$this->validateToken($request->token)) {
            return back()->with('error', 'Token tidak valid atau sudah kadaluarsa.');
        }

        try {
            DB::beginTransaction();

            $gelombang = \App\Models\GelombangPendaftaran::findOrFail($request->gelombang_id);
            $pendaftars = CalonSiswa::withTrashed()
                ->where('gelombang_pendaftaran_id', $request->gelombang_id)
                ->get();

            $count = 0;
            foreach ($pendaftars as $pendaftar) {
                // Delete files
                foreach ($pendaftar->dokumen as $dok) {
                    Storage::disk('public')->delete($dok->file_path);
                }
                foreach ($pendaftar->nilaiRapor as $rapor) {
                    if ($rapor->file_path) {
                        Storage::disk('public')->delete($rapor->file_path);
                    }
                }

                // Force delete cascade
                $userId = $pendaftar->user_id;
                $pendaftar->forceDelete();
                
                if ($userId) {
                    User::where('id', $userId)->forceDelete();
                }
                
                $count++;
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reset',
                'model_type' => 'GelombangPendaftaran',
                'model_id' => $request->gelombang_id,
                'description' => "Reset {$count} data pendaftar dari gelombang: {$gelombang->nama}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            session()->forget(['reset_token', 'reset_token_expires']);

            return back()->with('success', "{$count} data dari gelombang {$gelombang->nama} berhasil dihapus permanen.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reset data: ' . $e->getMessage());
        }
    }

    /**
     * Clean uploaded files that are orphaned (no database reference).
     */
    public function cleanOrphanedFiles(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        if (!$this->validateToken($request->token)) {
            return back()->with('error', 'Token tidak valid atau sudah kadaluarsa.');
        }

        try {
            $cleaned = 0;
            $storagePath = storage_path('app/public/dokumen');

            if (File::isDirectory($storagePath)) {
                $allFiles = File::allFiles($storagePath);
                $dbPaths = CalonDokumen::withTrashed()->pluck('file_path')->map(fn($p) => basename($p))->toArray();

                foreach ($allFiles as $file) {
                    if (!in_array($file->getFilename(), $dbPaths)) {
                        File::delete($file->getPathname());
                        $cleaned++;
                    }
                }
            }

            return back()->with('success', "{$cleaned} file orphan berhasil dibersihkan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membersihkan file: ' . $e->getMessage());
        }
    }

    /**
     * Validate reset token.
     */
    private function validateToken(string $token): bool
    {
        $storedToken = session('reset_token');
        $expires = session('reset_token_expires');

        if (!$storedToken || !$expires) {
            return false;
        }

        if ($token !== $storedToken) {
            return false;
        }

        if (now()->isAfter($expires)) {
            session()->forget(['reset_token', 'reset_token_expires']);
            return false;
        }

        return true;
    }

    /**
     * Calculate storage size used by uploads.
     */
    private function getStorageSize(): string
    {
        $size = 0;
        $paths = ['dokumen', 'rapor', 'nilai'];

        foreach ($paths as $path) {
            $fullPath = storage_path('app/public/' . $path);
            if (File::isDirectory($fullPath)) {
                foreach (File::allFiles($fullPath) as $file) {
                    $size += $file->getSize();
                }
            }
        }

        // Convert to human readable
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
