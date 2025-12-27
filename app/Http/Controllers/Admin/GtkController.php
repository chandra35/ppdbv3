<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalGtk;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Repositories\GtkRepository;
use App\Services\GtkSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class GtkController extends Controller
{
    protected GtkRepository $gtkRepository;
    protected GtkSyncService $syncService;

    public function __construct(GtkRepository $gtkRepository, GtkSyncService $syncService)
    {
        $this->gtkRepository = $gtkRepository;
        $this->syncService = $syncService;
    }

    /**
     * Display a listing of GTK
     */
    public function index(Request $request)
    {
        $source = $this->gtkRepository->getSource();
        $simansaAvailable = $this->gtkRepository->isSimansaAvailable();
        
        $query = LocalGtk::aktif();
        
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
        
        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        $gtks = $query->orderBy('nama_lengkap')->paginate(20);
        
        // Get existing PPDB users from GTK
        $existingEmails = User::pluck('email')->toArray();
        
        // Get sync stats
        $syncStats = [
            'total' => LocalGtk::count(),
            'synced' => LocalGtk::synced()->count(),
            'manual' => LocalGtk::manual()->count(),
            'last_sync' => LocalGtk::synced()
                ->orderBy('synced_at', 'desc')
                ->first()
                ?->synced_at
                ?->diffForHumans() ?? 'Belum pernah',
        ];
        
        // Get filter options (empty arrays for now, will be populated if needed)
        $kategoriPtks = LocalGtk::select('kategori_ptk')->distinct()->whereNotNull('kategori_ptk')->pluck('kategori_ptk');
        $jenisPtks = LocalGtk::select('jenis_ptk')->distinct()->whereNotNull('jenis_ptk')->pluck('jenis_ptk');
        
        return view('admin.gtk.index', compact(
            'gtks',
            'existingEmails',
            'source',
            'simansaAvailable',
            'syncStats',
            'kategoriPtks',
            'jenisPtks'
        ));
    }

    /**
     * Show GTK detail
     */
    public function show($id)
    {
        $gtk = LocalGtk::findOrFail($id);
        
        // Check if already registered in PPDB
        $ppdbUser = User::where('email', $gtk->email)->first();
        
        // Get available roles
        $roles = Role::orderBy('name')->get();
        
        return view('admin.gtk.show', compact('gtk', 'ppdbUser', 'roles'));
    }

    /**
     * Show form for creating new GTK (manual entry)
     */
    public function create()
    {
        return view('admin.gtk.create');
    }

    /**
     * Store manually created GTK
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|max:18|unique:gtks,nip',
            'email' => 'required|email|unique:gtks,email',
            'nomor_hp' => 'nullable|string',
            'kategori_ptk' => 'nullable|in:Pendidik,Tenaga Kependidikan',
            'jenis_ptk' => 'nullable|string',
            'jabatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $gtk = LocalGtk::create([
                'nama_lengkap' => $request->nama_lengkap,
                'nip' => $request->nip,
                'email' => $request->email,
                'nomor_hp' => $request->nomor_hp,
                'kategori_ptk' => $request->kategori_ptk,
                'jenis_ptk' => $request->jenis_ptk,
                'jabatan' => $request->jabatan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'source' => 'manual',
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => LocalGtk::class,
                'model_id' => $gtk->id,
                'description' => "Menambahkan GTK manual: {$gtk->nama_lengkap}",
                'new_values' => $gtk->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.gtk.index')
                ->with('success', "GTK {$gtk->nama_lengkap} berhasil ditambahkan.");

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $gtk = LocalGtk::findOrFail($id);
        return view('admin.gtk.edit', compact('gtk'));
    }

    /**
     * Update GTK
     */
    public function update(Request $request, $id)
    {
        $gtk = LocalGtk::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|max:18|unique:gtks,nip,' . $id,
            'email' => 'required|email|unique:gtks,email,' . $id,
            'nomor_hp' => 'nullable|string',
            'kategori_ptk' => 'nullable|in:Pendidik,Tenaga Kependidikan',
            'jenis_ptk' => 'nullable|string',
            'jabatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $gtk->toArray();
            
            $gtk->update([
                'nama_lengkap' => $request->nama_lengkap,
                'nip' => $request->nip,
                'email' => $request->email,
                'nomor_hp' => $request->nomor_hp,
                'kategori_ptk' => $request->kategori_ptk,
                'jenis_ptk' => $request->jenis_ptk,
                'jabatan' => $request->jabatan,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => LocalGtk::class,
                'model_id' => $gtk->id,
                'description' => "Mengubah data GTK: {$gtk->nama_lengkap}",
                'old_values' => $oldData,
                'new_values' => $gtk->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.gtk.index')
                ->with('success', "GTK {$gtk->nama_lengkap} berhasil diupdate.");

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete GTK (soft delete)
     */
    public function destroy($id)
    {
        try {
            $gtk = LocalGtk::findOrFail($id);
            
            // Check if GTK has associated user
            $user = User::where('email', $gtk->email)->first();
            if ($user) {
                return redirect()->back()
                    ->with('error', "GTK {$gtk->nama_lengkap} tidak dapat dihapus karena sudah terdaftar sebagai user PPDB.");
            }

            $gtk->delete();

            return redirect()->route('admin.gtk.index')
                ->with('success', "GTK {$gtk->nama_lengkap} berhasil dihapus.");

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Sync GTK from SIMANSA
     */
    public function syncFromSimansa()
    {
        if (!$this->gtkRepository->isSimansaAvailable()) {
            return redirect()->back()
                ->with('error', 'Koneksi ke database SIMANSA tidak tersedia.');
        }

        $result = $this->syncService->syncFromSimansa();

        if ($result['success']) {
            return redirect()->route('admin.gtk.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Register GTK as PPDB user and assign roles
     */
    public function registerAsUser(Request $request, $id)
    {
        $gtk = LocalGtk::findOrFail($id);
        
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'password' => 'nullable|min:6',
        ]);
        
        // Generate email if not exists
        $email = $gtk->email;
        if (empty($email)) {
            // Priority: NIP > NIK > nama slug
            if (!empty($gtk->nip)) {
                $email = $gtk->nip . '@ppdb.local';
            } elseif (!empty($gtk->nik)) {
                $email = $gtk->nik . '@ppdb.local';
            } else {
                $slug = Str::slug($gtk->nama_lengkap);
                $email = $slug . '@ppdb.local';
            }
            
            // Check uniqueness, add counter if needed
            $counter = 1;
            $originalEmail = $email;
            while (User::where('email', $email)->exists()) {
                $email = str_replace('@ppdb.local', $counter . '@ppdb.local', $originalEmail);
                $counter++;
            }
            
            // Update GTK email
            $gtk->email = $email;
            $gtk->save();
        }
        
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            return redirect()->back()
                ->with('error', 'GTK dengan email ' . $email . ' sudah terdaftar sebagai user PPDB.');
        }
        
        // Generate username (Priority: NIP > NIK > email prefix)
        $username = null;
        if (!empty($gtk->nip)) {
            $username = $gtk->nip;
        } elseif (!empty($gtk->nik)) {
            $username = $gtk->nik;
        } else {
            $username = explode('@', $email)[0]; // Use email prefix
        }
        
        // Check username uniqueness, add counter if needed
        $counter = 1;
        $originalUsername = $username;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        // Create new PPDB user
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $gtk->nama_lengkap,
            'email' => $email,
            'username' => $username,
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
            ->with('success', "GTK {$gtk->nama_lengkap} berhasil didaftarkan sebagai user PPDB. Username: {$username}, Email: {$email}, Password: " . ($request->password ?? 'ppdb123'));
    }

    /**
     * Update roles for existing PPDB user from GTK
     */
    public function updateRoles(Request $request, $id)
    {
        $gtk = LocalGtk::findOrFail($id);
        
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
        $gtk = LocalGtk::findOrFail($id);
        
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
            $gtk = LocalGtk::find($gtkId);
            
            if (!$gtk) {
                continue;
            }
            
            // Generate email if not exists
            $email = $gtk->email;
            if (empty($email)) {
                // Priority: NIP > NIK > nama slug
                if (!empty($gtk->nip)) {
                    $email = $gtk->nip . '@ppdb.local';
                } elseif (!empty($gtk->nik)) {
                    $email = $gtk->nik . '@ppdb.local';
                } else {
                    $slug = Str::slug($gtk->nama_lengkap);
                    $email = $slug . '@ppdb.local';
                }
                
                // Check uniqueness, add counter if needed
                $counter = 1;
                $originalEmail = $email;
                while (User::where('email', $email)->exists()) {
                    $email = str_replace('@ppdb.local', $counter . '@ppdb.local', $originalEmail);
                    $counter++;
                }
                
                // Update GTK email
                $gtk->email = $email;
                $gtk->save();
            }
            
            // Skip if already exists
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }
            
            // Generate username (Priority: NIP > NIK > email prefix)
            $username = null;
            if (!empty($gtk->nip)) {
                $username = $gtk->nip;
            } elseif (!empty($gtk->nik)) {
                $username = $gtk->nik;
            } else {
                $username = explode('@', $email)[0]; // Use email prefix
            }
            
            // Check username uniqueness, add counter if needed
            $counter = 1;
            $originalUsername = $username;
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            // Create user
            $user = User::create([
                'id' => Str::uuid(),
                'name' => $gtk->nama_lengkap,
                'email' => $email,
                'username' => $username,
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
