# Fitur: Dynamic Admin Panel Access

## Deskripsi

Sistem sekarang menggunakan kolom `can_access_admin` di tabel `roles` untuk menentukan apakah suatu role dapat mengakses admin panel atau tidak. Ini menggantikan pendekatan hardcoded yang sebelumnya mengharuskan pengeditan kode setiap kali ada role baru.

## Perubahan Database

### Tabel `roles`
Ditambahkan kolom baru:
- `can_access_admin` (boolean, default: false)

### Nilai Default
Role berikut di-set `can_access_admin = true`:
- `admin`
- `super-admin`
- `operator`
- `verifikator`
- `mas-admin`
- `content-manager`
- `penguji`

Role berikut di-set `can_access_admin = false`:
- `pendaftar`
- `pengunjung`
- Role baru lainnya (default)

## Cara Kerja

### 1. Method `canAccessAdminPanel()` di User Model
```php
public function canAccessAdminPanel(): bool
{
    // Super admin dan admin selalu bisa akses
    if ($this->isAdmin()) {
        return true;
    }

    // Cek apakah salah satu role punya can_access_admin = true
    foreach ($this->roles as $role) {
        if ($role->can_access_admin) {
            return true;
        }
    }

    return false;
}
```

### 2. AdminMiddleware
```php
if (!$user->canAccessAdminPanel()) {
    return redirect()->route('pendaftar.dashboard');
}
```

### 3. Login Redirect (LandingController)
```php
private function redirectBasedOnRole(User $user)
{
    if ($user->canAccessAdminPanel()) {
        return redirect('/admin');
    }

    return redirect('/pendaftar');
}
```

### 4. Gate Definitions (AuthServiceProvider)
```php
Gate::define('admin-panel', function (User $user) {
    return $user->canAccessAdminPanel();
});

Gate::define('operator-or-verifikator', function (User $user) {
    return $user->canAccessAdminPanel();
});
```

## Cara Menambah Role Baru dengan Akses Admin

### Via UI
1. Buka menu **Pengaturan Sistem > Roles**
2. Klik **Tambah Role** atau edit role yang ada
3. Aktifkan toggle **"Dapat Akses Admin Panel"**
4. Simpan

### Via Database
```sql
UPDATE roles SET can_access_admin = 1 WHERE name = 'nama-role-baru';
```

### Via Tinker
```php
$role = Role::where('name', 'nama-role-baru')->first();
$role->can_access_admin = true;
$role->save();
```

## Alur Login

```
User Login
    ↓
Check canAccessAdminPanel()
    ↓
┌─────────────────────────────────────┐
│ Punya role dengan can_access_admin? │
└─────────────────────────────────────┘
    ↓ YA                    ↓ TIDAK
    ↓                       ↓
Redirect ke /admin    Redirect ke /pendaftar
```

## File yang Dimodifikasi

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_01_18_111143_add_can_access_admin_to_roles_table.php` | Migrasi baru |
| `app/Models/Role.php` | Tambah `can_access_admin` ke fillable & casts |
| `app/Models/User.php` | Tambah method `canAccessAdminPanel()` |
| `app/Http/Middleware/AdminMiddleware.php` | Gunakan `canAccessAdminPanel()` |
| `app/Providers/AuthServiceProvider.php` | Update semua gate terkait |
| `app/Http/Controllers/Ppdb/LandingController.php` | Update redirect logic |
| `app/Http/Controllers/Admin/RoleController.php` | Handle field baru |
| `resources/views/admin/roles/create.blade.php` | Toggle UI |
| `resources/views/admin/roles/edit.blade.php` | Toggle UI |

## Keuntungan

1. **Tidak perlu edit kode** - Role baru cukup di-set via database/UI
2. **Fleksibel** - Akses bisa dicabut/diberikan kapan saja
3. **Aman** - Default `false` untuk role baru (principle of least privilege)
4. **Audit trail** - Perubahan tercatat di Activity Log

## Migration Rollback

Jika perlu rollback:
```bash
php artisan migrate:rollback --step=1
```

Ini akan menghapus kolom `can_access_admin` dari tabel `roles`.
