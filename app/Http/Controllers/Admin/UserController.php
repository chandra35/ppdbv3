<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'pendaftar');
            })
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate(20);
        // Exclude pendaftar role from filter options
        $roles = Role::where('name', '!=', 'pendaftar')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        // Exclude pendaftar role from create form
        $roles = Role::where('name', '!=', 'pendaftar')->orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($validated, &$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (!empty($validated['roles'])) {
                $user->roles()->attach($validated['roles']);
            }

            ActivityLog::log('create', "Membuat user: {$user->name}", $user);
        });

        return redirect()->route('admin.ppdb.users.index')
            ->with('success', 'User berhasil dibuat');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'activityLogs' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Prevent editing user with pendaftar role
        if ($user->roles->contains('name', 'pendaftar')) {
            return redirect()->route('admin.ppdb.users.index')
                ->with('error', 'User dengan role Pendaftar tidak dapat diedit di halaman ini. Silakan gunakan menu Pendaftar.');
        }
        
        // Exclude pendaftar role from edit form
        $roles = Role::where('name', '!=', 'pendaftar')->orderBy('name')->get();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $oldValues = $user->toArray();

        DB::transaction(function () use ($validated, $user) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
            $user->roles()->sync($validated['roles'] ?? []);

            ActivityLog::log('update', "Mengupdate user: {$user->name}", $user, $oldValues, $user->fresh()->toArray());
        });

        return redirect()->route('admin.ppdb.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.ppdb.users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri');
        }
        
        // Prevent deleting user with pendaftar role
        if ($user->roles->contains('name', 'pendaftar')) {
            return redirect()->route('admin.ppdb.users.index')
                ->with('error', 'User dengan role Pendaftar tidak dapat dihapus di halaman ini. Silakan gunakan menu Pendaftar.');
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::log('delete', "Menghapus user: {$userName}");

        return redirect()->route('admin.ppdb.users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
