# Fitur Permission-Based Menu & Route Protection

## Tanggal: Implementasi Sistem Menu Dinamis Berdasarkan Permission

## Deskripsi
Implementasi sistem menu dinamis dan proteksi route berdasarkan permission yang dimiliki user.
Menu akan tampil/sembunyi berdasarkan permission yang diatur di Role Management.

## Komponen yang Diubah

### 1. AuthServiceProvider.php
- Menambahkan `registerPermissionGates()` untuk register Gate dinamis dari database
- Setiap permission yang ada di `Role::getAvailablePermissions()` akan diregister sebagai Gate
- Admin selalu punya akses ke semua Gate

### 2. CheckPermission Middleware
File baru: `app/Http/Middleware/CheckPermission.php`
- Middleware untuk proteksi route berdasarkan permission
- Jika user tidak punya permission, redirect ke dashboard sesuai role
- Admin bypass semua permission check

### 3. Config AdminLTE (config/adminlte.php)
Menu sekarang menggunakan `'can' => 'permission.name'`:
- `'can' => 'admin-panel'` - Akses admin panel
- `'can' => 'pendaftar.view'` - Lihat pendaftar
- `'can' => 'verifikasi.finalisasi'` - Finalisasi
- `'can' => 'verifikasi.cetak'` - Cetak dokumen
- `'can' => 'statistik.view'` - Lihat statistik
- `'can' => 'settings.view'` - Lihat pengaturan
- `'can' => 'settings.edit'` - Edit pengaturan
- `'can' => 'user.view'` - Lihat user
- `'can' => 'role.view'` - Lihat role
- `'can' => 'logs.view'` - Lihat activity log
- `'can' => 'admin'` - Khusus admin only

### 4. Routes (routes/ppdb.php)
Route groups sekarang menggunakan middleware permission:
```php
Route::middleware(['permission:pendaftar.view'])->group(function () {
    // Routes yang butuh permission pendaftar.view
});

Route::middleware(['permission:verifikasi.verify'])->group(function () {
    // Routes verifikasi dokumen
});

Route::middleware(['permission:verifikasi.finalisasi'])->group(function () {
    // Routes finalisasi
});
```

### 5. Role Model - Permissions yang Tersedia
```php
'pendaftar' => [
    'pendaftar.view' => 'Lihat Pendaftar',
    'pendaftar.create' => 'Tambah Pendaftar',
    'pendaftar.edit' => 'Edit Pendaftar',
    'pendaftar.delete' => 'Hapus Pendaftar',
    'pendaftar.export' => 'Export Data Pendaftar',
],
'verifikasi' => [
    'verifikasi.view' => 'Lihat Status Verifikasi',
    'verifikasi.verify' => 'Verifikasi Dokumen',
    'verifikasi.approve' => 'Terima Pendaftar',
    'verifikasi.reject' => 'Tolak Pendaftar',
    'verifikasi.finalisasi' => 'Finalisasi Pendaftar',
    'verifikasi.cetak' => 'Cetak Dokumen',
],
'statistik' => [
    'statistik.view' => 'Lihat Statistik',
    'statistik.export' => 'Export Statistik',
],
// ... dan lainnya
```

## Cara Kerja

### Menu Display
1. AdminLTE membaca atribut `'can'` dari menu config
2. Laravel Gate memeriksa apakah user punya permission
3. Menu tampil jika Gate return true

### Route Protection
1. Request masuk ke route dengan middleware `permission:xxx`
2. `CheckPermission` middleware memeriksa permission user
3. Jika tidak punya akses → redirect ke dashboard dengan pesan error
4. Admin bypass semua check

### Hierarchy
- Admin → Akses semua (bypass permission check)
- Operator/Verifikator → Sesuai permission yang diset di role
- Pendaftar → Tidak bisa akses admin panel

## Penggunaan di Role Management

Saat membuat/edit role, centang permission yang diinginkan:
- ✅ `pendaftar.view` → User bisa lihat list pendaftar
- ✅ `verifikasi.verify` → User bisa verifikasi dokumen
- ❌ `pendaftar.delete` → User tidak bisa hapus pendaftar (menu tidak muncul)

## Testing

1. Login sebagai role Verifikator
2. Pastikan menu yang muncul sesuai permission
3. Coba akses URL langsung yang tidak punya permission
4. Seharusnya redirect ke dashboard dengan pesan error

## Files Modified
- `app/Providers/AuthServiceProvider.php`
- `app/Http/Middleware/CheckPermission.php` (NEW)
- `bootstrap/app.php` (register middleware)
- `config/adminlte.php`
- `routes/ppdb.php`
- `app/Models/Role.php` (updated permissions)
