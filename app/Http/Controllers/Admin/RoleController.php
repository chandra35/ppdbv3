<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\CustomPermission;
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

    /**
     * Show permissions management page
     */
    public function permissions()
    {
        $hardcodedPermissions = Role::getHardcodedPermissions();
        $customPermissions = CustomPermission::orderBy('group')->orderBy('name')->get();
        $unregisteredPermissions = CustomPermission::getUnregisteredPermissions();
        
        // Get all unique groups
        $groups = array_unique(array_merge(
            array_keys($hardcodedPermissions),
            $customPermissions->pluck('group')->unique()->toArray()
        ));
        sort($groups);
        
        return view('admin.roles.permissions', compact(
            'hardcodedPermissions',
            'customPermissions',
            'unregisteredPermissions',
            'groups'
        ));
    }

    /**
     * Sync permissions - scan code for new permissions
     */
    public function syncPermissions()
    {
        $unregistered = CustomPermission::getUnregisteredPermissions();
        
        return response()->json([
            'success' => true,
            'unregistered' => $unregistered,
            'count' => count($unregistered),
        ]);
    }

    /**
     * Store a new custom permission
     */
    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:custom_permissions,name',
            'display_name' => 'required|string|max:150',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        // Check if permission exists in hardcoded
        if (CustomPermission::permissionExists($validated['name'])) {
            return response()->json([
                'success' => false,
                'message' => 'Permission dengan nama ini sudah ada',
            ], 422);
        }

        $permission = CustomPermission::create($validated);

        ActivityLog::log('create', "Menambah permission: {$permission->name}");

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil ditambahkan',
            'permission' => $permission,
        ]);
    }

    /**
     * Update a custom permission
     */
    public function updatePermission(Request $request, CustomPermission $permission)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:150',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $permission->update($validated);

        ActivityLog::log('update', "Mengupdate permission: {$permission->name}");

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil diupdate',
            'permission' => $permission,
        ]);
    }

    /**
     * Delete a custom permission
     */
    public function destroyPermission(CustomPermission $permission)
    {
        $name = $permission->name;
        $permission->delete();

        ActivityLog::log('delete', "Menghapus permission: {$name}");

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil dihapus',
        ]);
    }

    /**
     * Bulk add permissions from scan results
     */
    public function bulkAddPermissions(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:100',
            'permissions.*.display_name' => 'required|string|max:150',
            'permissions.*.group' => 'required|string|max:50',
        ]);

        $added = 0;
        $skipped = 0;

        foreach ($validated['permissions'] as $perm) {
            if (!CustomPermission::permissionExists($perm['name'])) {
                CustomPermission::create([
                    'name' => $perm['name'],
                    'display_name' => $perm['display_name'],
                    'group' => $perm['group'],
                ]);
                $added++;
            } else {
                $skipped++;
            }
        }

        ActivityLog::log('create', "Bulk add {$added} permissions");

        return response()->json([
            'success' => true,
            'message' => "{$added} permission ditambahkan" . ($skipped > 0 ? ", {$skipped} dilewati (sudah ada)" : ""),
            'added' => $added,
            'skipped' => $skipped,
        ]);
    }
}
