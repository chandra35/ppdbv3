<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CustomPermission extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active custom permissions grouped by group name
     */
    public static function getGrouped(): array
    {
        return Cache::remember('custom_permissions_grouped', 3600, function () {
            $permissions = self::where('is_active', true)->get();
            $grouped = [];
            
            foreach ($permissions as $permission) {
                $grouped[$permission->group][$permission->name] = $permission->display_name;
            }
            
            return $grouped;
        });
    }

    /**
     * Clear cache when permission is updated
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('custom_permissions_grouped');
        });

        static::deleted(function () {
            Cache::forget('custom_permissions_grouped');
        });
    }

    /**
     * Check if a permission name already exists (including hardcoded)
     */
    public static function permissionExists(string $name): bool
    {
        // Check in hardcoded permissions
        $hardcoded = Role::getHardcodedPermissions();
        foreach ($hardcoded as $group => $permissions) {
            if (array_key_exists($name, $permissions)) {
                return true;
            }
        }
        
        // Check in custom permissions
        return self::where('name', $name)->exists();
    }

    /**
     * Scan routes for permissions used in middleware or controllers
     */
    public static function scanRoutesForPermissions(): array
    {
        $found = [];
        
        // Get all routes
        $routes = app('router')->getRoutes();
        
        foreach ($routes as $route) {
            // Check middleware for permission patterns
            $middleware = $route->middleware();
            foreach ($middleware as $mw) {
                if (str_starts_with($mw, 'permission:')) {
                    $permission = str_replace('permission:', '', $mw);
                    $found[$permission] = $permission;
                }
            }
        }
        
        return $found;
    }

    /**
     * Scan blade files for hasPermission checks
     */
    public static function scanBladeFilesForPermissions(): array
    {
        $found = [];
        $path = resource_path('views');
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                
                // Match hasPermission('xxx') patterns
                if (preg_match_all("/hasPermission\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches)) {
                    foreach ($matches[1] as $permission) {
                        $found[$permission] = $permission;
                    }
                }
            }
        }
        
        return $found;
    }

    /**
     * Scan PHP files for hasPermission checks
     */
    public static function scanControllerFilesForPermissions(): array
    {
        $found = [];
        $path = app_path('Http/Controllers');
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                
                // Match hasPermission('xxx') patterns
                if (preg_match_all("/hasPermission\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $matches)) {
                    foreach ($matches[1] as $permission) {
                        $found[$permission] = $permission;
                    }
                }
            }
        }
        
        return $found;
    }

    /**
     * Get all permissions used in code but not registered
     */
    public static function getUnregisteredPermissions(): array
    {
        // Scan all sources
        $fromRoutes = self::scanRoutesForPermissions();
        $fromBlades = self::scanBladeFilesForPermissions();
        $fromControllers = self::scanControllerFilesForPermissions();
        
        // Merge all found permissions
        $allFound = array_unique(array_merge(
            array_keys($fromRoutes),
            array_keys($fromBlades),
            array_keys($fromControllers)
        ));
        
        // Get registered permissions
        $hardcoded = Role::getHardcodedPermissions();
        $registered = [];
        foreach ($hardcoded as $group => $permissions) {
            $registered = array_merge($registered, array_keys($permissions));
        }
        
        // Add custom permissions
        $custom = self::pluck('name')->toArray();
        $registered = array_merge($registered, $custom);
        
        // Find unregistered
        $unregistered = [];
        foreach ($allFound as $permission) {
            if (!in_array($permission, $registered)) {
                // Try to guess the group from permission name
                $parts = explode('.', $permission);
                $group = $parts[0] ?? 'other';
                $unregistered[] = [
                    'name' => $permission,
                    'group' => $group,
                    'display_name' => self::generateDisplayName($permission),
                ];
            }
        }
        
        return $unregistered;
    }

    /**
     * Generate permission suggestions from admin routes
     * This analyzes routes to suggest permissions that SHOULD exist
     */
    public static function getSuggestedPermissionsFromRoutes(): array
    {
        $suggestions = [];
        $routes = app('router')->getRoutes();
        
        // Get all registered permissions
        $hardcoded = Role::getHardcodedPermissions();
        $registered = [];
        foreach ($hardcoded as $group => $permissions) {
            $registered = array_merge($registered, array_keys($permissions));
        }
        $custom = self::pluck('name')->toArray();
        $registered = array_merge($registered, $custom);
        
        // Map of route methods to permission actions
        $methodActionMap = [
            'GET' => 'view',
            'POST' => 'create',
            'PUT' => 'edit',
            'PATCH' => 'edit',
            'DELETE' => 'delete',
        ];
        
        // Skip these route patterns
        $skipPatterns = [
            'login', 'logout', 'register', 'password', 'csrf', 'api',
            'sanctum', 'ignition', 'livewire', 'horizon', 'telescope'
        ];
        
        // Group route name patterns to resource names
        $resourceGroups = [];
        
        foreach ($routes as $route) {
            $name = $route->getName();
            $uri = $route->uri();
            $methods = $route->methods();
            
            // Only process admin routes
            if (!$name || !str_starts_with($name, 'admin.')) {
                continue;
            }
            
            // Skip certain patterns
            $skip = false;
            foreach ($skipPatterns as $pattern) {
                if (str_contains($name, $pattern) || str_contains($uri, $pattern)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            
            // Parse route name to extract resource and action
            // e.g., admin.finalisasi.index -> finalisasi, index
            // e.g., admin.ppdb.berita.store -> berita, store
            $nameParts = explode('.', $name);
            
            // Remove 'admin' prefix
            array_shift($nameParts);
            
            // Remove 'ppdb' if present
            if (isset($nameParts[0]) && $nameParts[0] === 'ppdb') {
                array_shift($nameParts);
            }
            
            if (count($nameParts) < 1) continue;
            
            // Get action (last part) and resource (first part after admin/ppdb)
            $action = end($nameParts);
            $resource = $nameParts[0];
            
            // Skip if resource is too generic
            if (in_array($resource, ['dashboard', 'profile', 'home'])) {
                continue;
            }
            
            // Map route action to permission action
            $permAction = match($action) {
                'index', 'show' => 'view',
                'create', 'store' => 'create',
                'edit', 'update' => 'edit',
                'destroy' => 'delete',
                'export' => 'export',
                'import' => 'import',
                default => null
            };
            
            // If action wasn't mapped, try to use it directly for special actions
            if ($permAction === null) {
                // Special actions that should have their own permission
                $specialActions = ['verify', 'approve', 'reject', 'sync', 'process', 'restore', 'force-delete', 'bulk-delete'];
                if (in_array($action, $specialActions)) {
                    $permAction = $action;
                } else {
                    continue;
                }
            }
            
            // Build permission name
            $permName = "{$resource}.{$permAction}";
            
            // Check if already registered
            if (in_array($permName, $registered) || isset($suggestions[$permName])) {
                continue;
            }
            
            $suggestions[$permName] = [
                'name' => $permName,
                'group' => $resource,
                'display_name' => self::generateDisplayName($permName),
                'source' => 'route',
                'route_name' => $name,
            ];
        }
        
        return array_values($suggestions);
    }

    /**
     * Get all missing permissions (both from code scan and route analysis)
     */
    public static function getAllMissingPermissions(): array
    {
        $fromCode = self::getUnregisteredPermissions();
        $fromRoutes = self::getSuggestedPermissionsFromRoutes();
        
        // Merge and deduplicate
        $all = [];
        foreach ($fromCode as $perm) {
            $perm['source'] = 'code';
            $all[$perm['name']] = $perm;
        }
        foreach ($fromRoutes as $perm) {
            if (!isset($all[$perm['name']])) {
                $all[$perm['name']] = $perm;
            }
        }
        
        // Sort by group then name
        usort($all, function($a, $b) {
            $groupCmp = strcmp($a['group'], $b['group']);
            return $groupCmp !== 0 ? $groupCmp : strcmp($a['name'], $b['name']);
        });
        
        return array_values($all);
    }

    /**
     * Generate a display name from permission name
     */
    public static function generateDisplayName(string $name): string
    {
        // Split by dot
        $parts = explode('.', $name);
        
        if (count($parts) >= 2) {
            $action = $parts[count($parts) - 1];
            $resource = $parts[0];
            
            $actionMap = [
                'view' => 'Lihat',
                'create' => 'Tambah',
                'edit' => 'Edit',
                'update' => 'Update',
                'delete' => 'Hapus',
                'export' => 'Export',
                'import' => 'Import',
                'print' => 'Cetak',
                'verify' => 'Verifikasi',
                'approve' => 'Setujui',
                'reject' => 'Tolak',
                'sync' => 'Sinkronisasi',
                'manage' => 'Kelola',
                'clear' => 'Hapus Semua',
            ];
            
            $actionText = $actionMap[$action] ?? ucfirst($action);
            $resourceText = ucfirst(str_replace('-', ' ', $resource));
            
            return "{$actionText} {$resourceText}";
        }
        
        return ucfirst(str_replace(['.', '-', '_'], ' ', $name));
    }
}
