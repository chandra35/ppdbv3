<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimansaGtk;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GtkController extends Controller
{
    /**
     * Display a listing of GTK from simansav3
     */
    public function index(Request $request)
    {
        $query = SimansaGtk::aktif();
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by kategori PTK
        if ($request->filled('kategori_ptk')) {
            $query->where('kategori_ptk', $request->kategori_ptk);
        }
        
        // Filter by jenis PTK
        if ($request->filled('jenis_ptk')) {
            $query->where('jenis_ptk', $request->jenis_ptk);
        }
        
        $gtks = $query->orderBy('nama_lengkap')->paginate(20);
        
        // Get existing PPDB users from GTK
        $existingEmails = User::pluck('email')->toArray();
        
        // Get unique kategori and jenis PTK for filter
        $kategoriPtks = SimansaGtk::aktif()->select('kategori_ptk')->distinct()->pluck('kategori_ptk')->filter();
        $jenisPtks = SimansaGtk::aktif()->select('jenis_ptk')->distinct()->pluck('jenis_ptk')->filter();
        
        return view('admin.gtk.index', compact('gtks', 'existingEmails', 'kategoriPtks', 'jenisPtks'));
    }

    /**
     * Show GTK detail
     */
    public function show($id)
    {
        $gtk = SimansaGtk::findOrFail($id);
        
        // Check if already registered in PPDB
        $ppdbUser = User::where('email', $gtk->email)->first();
        
        // Get available roles
        $roles = Role::orderBy('name')->get();
        
        return view('admin.gtk.show', compact('gtk', 'ppdbUser', 'roles'));
    }

    /**
     * Register GTK as PPDB user and assign roles
     */
    public function registerAsUser(Request $request, $id)
    {
        $gtk = SimansaGtk::findOrFail($id);
        
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'password' => 'nullable|min:6',
        ]);
        
        // Check if user already exists
        $existingUser = User::where('email', $gtk->email)->first();
        
        if ($existingUser) {
            return redirect()->back()
                ->with('error', 'GTK dengan email ' . $gtk->email . ' sudah terdaftar sebagai user PPDB.');
        }
        
        // Create new PPDB user
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $gtk->nama_lengkap,
            'email' => $gtk->email,
            'password' => Hash::make($request->password ?? 'ppdb123'),
            'email_verified_at' => now(),
        ]);
        
        // Assign roles
        $user->roles()->attach($request->roles);
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Mendaftarkan GTK {$gtk->nama_lengkap} sebagai user PPDB dengan role",
            'new_values' => [
                'gtk_id' => $gtk->id,
                'user_id' => $user->id,
                'roles' => $request->roles,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('admin.gtk.index')
            ->with('success', "GTK {$gtk->nama_lengkap} berhasil didaftarkan sebagai user PPDB.");
    }

    /**
     * Update roles for existing PPDB user from GTK
     */
    public function updateRoles(Request $request, $id)
    {
        $gtk = SimansaGtk::findOrFail($id);
        
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);
        
        $user = User::where('email', $gtk->email)->firstOrFail();
        $oldRoles = $user->roles->pluck('id')->toArray();
        
        // Sync roles
        $user->roles()->sync($request->roles);
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Mengubah role user {$user->name} dari GTK",
            'old_values' => ['roles' => $oldRoles],
            'new_values' => ['roles' => $request->roles],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('admin.gtk.index')
            ->with('success', "Role untuk {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove PPDB user created from GTK
     */
    public function removeUser(Request $request, $id)
    {
        $gtk = SimansaGtk::findOrFail($id);
        
        $user = User::where('email', $gtk->email)->firstOrFail();
        
        // Don't allow removing self
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        
        $userName = $user->name;
        
        // Log activity before deleting
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Menghapus user PPDB {$userName} (dari GTK)",
            'old_values' => [
                'gtk_id' => $gtk->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Detach roles and delete
        $user->roles()->detach();
        $user->delete();
        
        return redirect()->route('admin.gtk.index')
            ->with('success', "User PPDB {$userName} berhasil dihapus.");
    }

    /**
     * Bulk register GTK as PPDB users
     */
    public function bulkRegister(Request $request)
    {
        $request->validate([
            'gtk_ids' => 'required|array|min:1',
            'role_id' => 'required|exists:roles,id',
            'default_password' => 'nullable|min:6',
        ]);
        
        $gtkIds = $request->gtk_ids;
        $roleId = $request->role_id;
        $defaultPassword = $request->default_password ?? 'ppdb123';
        
        $registered = 0;
        $skipped = 0;
        
        foreach ($gtkIds as $gtkId) {
            $gtk = SimansaGtk::find($gtkId);
            
            if (!$gtk) {
                continue;
            }
            
            // Skip if already exists
            if (User::where('email', $gtk->email)->exists()) {
                $skipped++;
                continue;
            }
            
            // Create user
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $gtk->nama_lengkap,
                'email' => $gtk->email,
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            
            // Assign role
            $user->roles()->attach($roleId);
            
            $registered++;
        }
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_create',
            'model_type' => User::class,
            'model_id' => null,
            'description' => "Mendaftarkan {$registered} GTK sebagai user PPDB (skipped: {$skipped})",
            'new_values' => [
                'total_selected' => count($gtkIds),
                'registered' => $registered,
                'skipped' => $skipped,
                'role_id' => $roleId,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('admin.gtk.index')
            ->with('success', "{$registered} GTK berhasil didaftarkan sebagai user PPDB. {$skipped} dilewati (sudah terdaftar).");
    }
}
