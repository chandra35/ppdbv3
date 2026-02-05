<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\LocalGtk;
use App\Models\PengujiRuang;
use App\Models\SesiUjian;
use App\Models\NilaiSeleksi;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengujiController extends Controller
{
    /**
     * Role name for penguji
     */
    protected $pengujiRoleName = 'penguji';

    /**
     * Display a listing of penguji with dual-listbox interface
     */
    public function index(Request $request)
    {
        // Get penguji role
        $pengujiRole = Role::where('name', $this->pengujiRoleName)->first();

        if (!$pengujiRole) {
            return view('admin.penguji.index', [
                'pengujiList' => collect(),
                'availableGtk' => collect(),
                'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0, 'assigned_today' => 0],
                'pengujiRole' => null,
            ])->with('warning', 'Role penguji belum tersedia. Hubungi administrator.');
        }

        // Get existing penguji emails
        $pengujiEmails = User::whereHas('roles', fn($q) => $q->where('name', $this->pengujiRoleName))
            ->pluck('email')
            ->toArray();

        // Get GTK that are NOT yet penguji (available to be assigned)
        $availableGtk = LocalGtk::aktif()
            ->whereNotIn('email', $pengujiEmails)
            ->orderBy('nama_lengkap')
            ->get();

        // Get penguji list with optional search
        $query = User::with(['roles'])
            ->whereHas('roles', function ($q) {
                $q->where('name', $this->pengujiRoleName);
            })
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $pengujiList = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total' => User::whereHas('roles', fn($q) => $q->where('name', $this->pengujiRoleName))->count(),
            'active' => User::whereHas('roles', fn($q) => $q->where('name', $this->pengujiRoleName))->where('is_active', true)->count(),
            'inactive' => User::whereHas('roles', fn($q) => $q->where('name', $this->pengujiRoleName))->where('is_active', false)->count(),
            'assigned_today' => PengujiRuang::whereHas('sesiUjian', fn($q) => $q->whereIn('status', ['locked', 'in_progress']))->distinct('user_id')->count('user_id'),
        ];

        return view('admin.penguji.index', compact('pengujiList', 'availableGtk', 'stats', 'pengujiRole'));
    }

    /**
     * Get available GTK for AJAX (not yet penguji)
     */
    public function getAvailableGtk(Request $request)
    {
        $pengujiEmails = User::whereHas('roles', fn($q) => $q->where('name', $this->pengujiRoleName))
            ->pluck('email')
            ->toArray();

        $query = LocalGtk::aktif()
            ->whereNotIn('email', $pengujiEmails)
            ->orderBy('nama_lengkap');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $gtks = $query->limit(50)->get()->map(function ($gtk) {
            return [
                'id' => $gtk->id,
                'nama_lengkap' => $gtk->nama_lengkap,
                'nip' => $gtk->nip,
                'email' => $gtk->email,
                'jabatan' => $gtk->jabatan,
                'kategori_ptk' => $gtk->kategori_ptk,
                'nomor_hp' => $gtk->nomor_hp,
            ];
        });

        return response()->json($gtks);
    }

    /**
     * Assign GTK as penguji (create user if not exists, assign role)
     */
    public function assignPenguji(Request $request)
    {
        $request->validate([
            'gtk_ids' => 'required|array|min:1',
            'gtk_ids.*' => 'exists:gtks,id',
            'password' => 'nullable|string|min:6',
        ]);

        $pengujiRole = Role::where('name', $this->pengujiRoleName)->first();
        if (!$pengujiRole) {
            return response()->json(['success' => false, 'message' => 'Role penguji tidak ditemukan.'], 400);
        }

        $defaultPassword = $request->password ?? 'ppdb123';
        $assigned = [];
        $errors = [];

        foreach ($request->gtk_ids as $gtkId) {
            try {
                DB::beginTransaction();

                $gtk = LocalGtk::findOrFail($gtkId);
                
                // Check if user already exists
                $user = User::where('email', $gtk->email)->first();

                if (!$user) {
                    // Generate username
                    $username = $gtk->nip ?? $gtk->nik ?? Str::slug($gtk->nama_lengkap);
                    $counter = 1;
                    $originalUsername = $username;
                    while (User::where('username', $username)->exists()) {
                        $username = $originalUsername . $counter;
                        $counter++;
                    }

                    // Create new user
                    $user = User::create([
                        'id' => Str::uuid(),
                        'name' => $gtk->nama_lengkap,
                        'email' => $gtk->email,
                        'username' => $username,
                        'phone' => $gtk->nomor_hp,
                        'password' => Hash::make($defaultPassword),
                        'email_verified_at' => now(),
                        'is_active' => true,
                    ]);
                }

                // Check if already has penguji role
                if (!$user->roles->contains('name', $this->pengujiRoleName)) {
                    $user->roles()->attach($pengujiRole->id);
                }

                $assigned[] = $gtk->nama_lengkap;

                ActivityLog::log('create', "Menambahkan GTK {$gtk->nama_lengkap} sebagai penguji", $user);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Error pada {$gtk->nama_lengkap}: " . $e->getMessage();
            }
        }

        $message = count($assigned) > 0 
            ? count($assigned) . ' GTK berhasil ditambahkan sebagai penguji.' 
            : 'Tidak ada GTK yang ditambahkan.';

        if (count($errors) > 0) {
            $message .= ' Errors: ' . implode(', ', $errors);
        }

        return response()->json([
            'success' => count($assigned) > 0,
            'message' => $message,
            'assigned' => $assigned,
            'errors' => $errors,
            'default_password' => $defaultPassword,
        ]);
    }

    /**
     * Remove penguji role (AJAX)
     */
    public function removePenguji(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if penguji has active assignments
        $hasActiveAssignments = PengujiRuang::where('user_id', $user->id)
            ->whereHas('sesiUjian', fn($q) => $q->whereIn('status', ['locked', 'in_progress']))
            ->exists();

        if ($hasActiveAssignments) {
            return response()->json([
                'success' => false,
                'message' => "Penguji {$user->name} masih memiliki tugas aktif.",
            ], 400);
        }

        $pengujiRole = Role::where('name', $this->pengujiRoleName)->first();
        if ($pengujiRole) {
            $user->roles()->detach($pengujiRole->id);
        }

        ActivityLog::log('delete', "Menghapus role penguji dari: {$user->name}");

        return response()->json([
            'success' => true,
            'message' => "Role penguji berhasil dihapus dari {$user->name}.",
        ]);
    }

    /**
     * Display the specified penguji
     */
    public function show(User $penguji)
    {
        // Verify user is penguji
        if (!$penguji->roles->contains('name', $this->pengujiRoleName)) {
            return redirect()->route('admin.penguji.index')
                ->with('error', 'User bukan penguji.');
        }

        $penguji->load(['roles']);

        // Get GTK data if available
        $gtkData = LocalGtk::where('email', $penguji->email)->first();

        // Get assignment history
        $assignments = PengujiRuang::with(['sesiUjian', 'ruangUjian'])
            ->where('user_id', $penguji->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get nilai statistics
        $nilaiStats = [
            'total_dinilai' => NilaiSeleksi::where('penguji_id', $penguji->id)->count(),
            'submitted' => NilaiSeleksi::where('penguji_id', $penguji->id)->where('status', 'submitted')->count(),
            'verified' => NilaiSeleksi::where('penguji_id', $penguji->id)->where('status', 'verified')->count(),
            'draft' => NilaiSeleksi::where('penguji_id', $penguji->id)->where('status', 'draft')->count(),
        ];

        // Get active sesi assignments
        $activeSesi = PengujiRuang::with(['sesiUjian', 'ruangUjian'])
            ->where('user_id', $penguji->id)
            ->where('is_active', true)
            ->whereHas('sesiUjian', fn($q) => $q->whereIn('status', ['locked', 'in_progress']))
            ->get();

        return view('admin.penguji.show', compact('penguji', 'gtkData', 'assignments', 'nilaiStats', 'activeSesi'));
    }

    /**
     * Remove role penguji from user
     * 
     * Tidak menghapus user, hanya mencabut role penguji
     */
    public function destroy(User $penguji)
    {
        // Check if penguji has active assignments
        $hasActiveAssignments = PengujiRuang::where('user_id', $penguji->id)
            ->whereHas('sesiUjian', fn($q) => $q->whereIn('status', ['locked', 'in_progress']))
            ->exists();

        if ($hasActiveAssignments) {
            return redirect()->route('admin.penguji.index')
                ->with('error', "Penguji {$penguji->name} masih memiliki tugas aktif. Selesaikan sesi ujian terlebih dahulu.");
        }

        $userName = $penguji->name;
        
        // Remove penguji role only, don't delete user
        $pengujiRole = Role::where('name', $this->pengujiRoleName)->first();
        if ($pengujiRole) {
            $penguji->roles()->detach($pengujiRole->id);
        }

        ActivityLog::log('delete', "Menghapus role penguji dari: {$userName}");

        return redirect()->route('admin.penguji.index')
            ->with('success', "Role penguji berhasil dihapus dari {$userName}.");
    }

    /**
     * Toggle penguji active status
     */
    public function toggleStatus(User $penguji)
    {
        $penguji->update(['is_active' => !$penguji->is_active]);

        $status = $penguji->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::log('update', "Penguji {$penguji->name} {$status}");

        return redirect()->back()
            ->with('success', "Penguji {$penguji->name} berhasil {$status}.");
    }

    /**
     * Reset password for penguji
     */
    public function resetPassword(Request $request, User $penguji)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $penguji->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log('update', "Reset password penguji: {$penguji->name}");

        return redirect()->back()
            ->with('success', "Password penguji {$penguji->name} berhasil direset.");
    }

    /**
     * Get penguji list for AJAX (used in assign penguji modal)
     */
    public function getPengujiList(Request $request)
    {
        $query = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', [$this->pengujiRoleName, 'admin', 'verifikator', 'super-admin', 'mas-admin']);
            })
            ->where('is_active', true)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pengujiList = $query->get()->map(function ($user) {
            $roles = $user->roles->pluck('display_name')->join(', ');
            $isPenguji = $user->roles->contains('name', $this->pengujiRoleName);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $roles,
                'is_dedicated_penguji' => $isPenguji,
                'text' => $user->name . ' (' . ($isPenguji ? 'Penguji' : $roles) . ')',
            ];
        });

        return response()->json($pengujiList);
    }

    /**
     * Get penguji assignment summary
     */
    public function getAssignmentSummary(Request $request, SesiUjian $sesiUjian)
    {
        $assignments = PengujiRuang::with(['ruangUjian', 'user'])
            ->where('sesi_ujian_id', $sesiUjian->id)
            ->get()
            ->groupBy('ruang_ujian_id')
            ->map(function ($items, $ruangId) {
                $ruang = $items->first()->ruangUjian;
                return [
                    'ruang_id' => $ruangId,
                    'ruang_nama' => $ruang->nama_ruang,
                    'penguji' => $items->map(function ($pr) {
                        return [
                            'user_id' => $pr->user_id,
                            'name' => $pr->user->name ?? 'Unknown',
                            'email' => $pr->user->email ?? '',
                            'is_ketua' => $pr->is_ketua,
                        ];
                    })->values(),
                    'has_ketua' => $items->where('is_ketua', true)->count() > 0,
                ];
            })
            ->values();

        return response()->json([
            'sesi' => [
                'id' => $sesiUjian->id,
                'nama' => $sesiUjian->nama,
                'tanggal' => $sesiUjian->tanggal?->format('d M Y'),
            ],
            'assignments' => $assignments,
        ]);
    }
}
