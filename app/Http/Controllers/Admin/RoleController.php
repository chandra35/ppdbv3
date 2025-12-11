<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Role::getAvailablePermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $validated['permissions'] = $validated['permissions'] ?? [];

        $role = Role::create($validated);

        ActivityLog::log('create', "Membuat role: {$role->display_name}", $role);

        return redirect()->route('admin.ppdb.roles.index')
            ->with('success', 'Role berhasil dibuat');
    }

    public function show(Role $role)
    {
        $role->load('users');
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.ppdb.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit');
        }

        $permissions = Role::getAvailablePermissions();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.ppdb.roles.index')
                ->with('error', 'Role sistem tidak dapat diedit');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $oldValues = $role->toArray();
        $validated['permissions'] = $validated['permissions'] ?? [];

        $role->update($validated);

        ActivityLog::log('update', "Mengupdate role: {$role->display_name}", $role, $oldValues, $role->fresh()->toArray());

        return redirect()->route('admin.ppdb.roles.index')
            ->with('success', 'Role berhasil diupdate');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.ppdb.roles.index')
                ->with('error', 'Role sistem tidak dapat dihapus');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.ppdb.roles.index')
                ->with('error', 'Role masih digunakan oleh user');
        }

        $roleName = $role->display_name;
        $role->delete();

        ActivityLog::log('delete', "Menghapus role: {$roleName}");

        return redirect()->route('admin.ppdb.roles.index')
            ->with('success', 'Role berhasil dihapus');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->is_system) {
            return response()->json(['error' => 'Role sistem tidak dapat diedit'], 403);
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update(['permissions' => $validated['permissions'] ?? []]);

        ActivityLog::log('update', "Mengupdate permissions role: {$role->display_name}", $role);

        return response()->json(['success' => true]);
    }
}
