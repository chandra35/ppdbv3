# Dokumentasi Restructure Routes PPDB v3

## Ringkasan Perubahan

Perubahan dilakukan untuk memisahkan route berdasarkan role:
- **Admin** → `/admin/*` 
- **Operator** → `/operator/*`

## Struktur Route Baru

### Route Admin (`/admin/*`)
| Route Lama | Route Baru | Keterangan |
|------------|------------|------------|
| `/admin/ppdb` | `/admin` | Dashboard Admin |
| `/admin/ppdb/pendaftar/*` | `/admin/pendaftar/*` | Kelola Pendaftar |
| `/admin/ppdb/settings` | `/admin/settings` | PPDB Settings |
| `/admin/ppdb/site-settings` | `/admin/settings/halaman` | Pengaturan Halaman |
| `/admin/ppdb/berita/*` | `/admin/settings/berita/*` | Kelola Berita |
| `/admin/ppdb/slider/*` | `/admin/settings/slider/*` | Kelola Slider |
| `/admin/ppdb/jadwal/*` | `/admin/settings/jadwal/*` | Kelola Jadwal |
| `/admin/ppdb/users/*` | `/admin/users/*` | User Management |
| `/admin/ppdb/roles/*` | `/admin/roles/*` | Role Management |
| `/admin/ppdb/verifikator` | `/admin/verifikator` | Kelola Verifikator |
| `/admin/ppdb/gtk` | `/admin/gtk` | GTK SIMANSA |
| `/admin/ppdb/logs` | `/admin/logs` | Activity Logs |

### Route Operator (`/operator/*`)
| Route | Keterangan |
|-------|------------|
| `/operator` | Dashboard Operator |
| `/operator/pendaftar` | Daftar Pendaftar |
| `/operator/pendaftar/{id}` | Detail Pendaftar |
| `/operator/pendaftar/{id}/verify` | Verifikasi Pendaftar |
| `/operator/pendaftar/{id}/reject` | Tolak Pendaftar |
| `/operator/verifikasi-dokumen` | Daftar Verifikasi Dokumen |
| `/operator/verifikasi-dokumen/{id}` | Detail Verifikasi Dokumen |
| `/operator/verifikasi-dokumen/{id}` (POST) | Update Verifikasi Dokumen |

## Route Name Mapping

### Admin Routes
```
admin.ppdb.dashboard → admin.dashboard
admin.ppdb.pendaftar.* → admin.pendaftar.*
admin.ppdb.settings.* → admin.settings.*
admin.ppdb.site-settings.* → admin.settings.halaman.*
admin.ppdb.berita.* → admin.settings.berita.*
admin.ppdb.slider.* → admin.settings.slider.*
admin.ppdb.jadwal.* → admin.settings.jadwal.*
admin.ppdb.users.* → admin.users.*
admin.ppdb.roles.* → admin.roles.*
admin.ppdb.verifikator.* → admin.verifikator.*
admin.ppdb.gtk.* → admin.gtk.*
admin.ppdb.logs.* → admin.logs.*
```

### Operator Routes
```
operator.dashboard
operator.pendaftar.index
operator.pendaftar.show
operator.pendaftar.verify
operator.pendaftar.reject
operator.verifikasi-dokumen.index
operator.verifikasi-dokumen.show
operator.verifikasi-dokumen.update
```

## Backward Compatibility

Route lama (`/admin/ppdb/*`) tetap tersedia dan akan redirect ke route baru.
Hal ini memastikan bookmark dan link external tetap berfungsi.

## File yang Dibuat/Dimodifikasi

### File Baru
1. `app/Http/Middleware/OperatorMiddleware.php`
2. `app/Http/Middleware/PengujiMiddleware.php`
3. `app/Http/Controllers/Operator/DashboardController.php`
4. `app/Http/Controllers/Operator/PendaftarController.php`
5. `resources/views/operator/dashboard.blade.php`
6. `resources/views/operator/pendaftar/index.blade.php`
7. `resources/views/operator/pendaftar/show.blade.php`
8. `resources/views/operator/pendaftar/verifikasi-dokumen.blade.php`
9. `resources/views/operator/pendaftar/verifikasi-dokumen-detail.blade.php`

### File Dimodifikasi
1. `bootstrap/app.php` - Register middleware aliases
2. `routes/ppdb.php` - Restructure routes
3. `config/adminlte.php` - Update menu structure
4. `resources/views/admin/**/*.blade.php` - Update route names

## Middleware

### OperatorMiddleware
- Mengizinkan role: `operator`, `verifikator`, `admin`
- Redirect ke `ppdb.dashboard` jika tidak memiliki akses

### PengujiMiddleware
- Mengizinkan role: `penguji`, `admin`
- Redirect ke `ppdb.dashboard` jika tidak memiliki akses

## Menu AdminLTE

Menu dipisahkan berdasarkan permission:
- Menu Admin menggunakan `'can' => 'admin'`
- Menu Operator menggunakan `'can' => 'operator'`

## Testing

Setelah deployment, test route berikut:
1. `/admin` - Dashboard Admin
2. `/admin/settings` - Settings
3. `/admin/settings/berita` - Kelola Berita
4. `/operator` - Dashboard Operator
5. `/operator/pendaftar` - Daftar Pendaftar

## Catatan

- Dashboard Admin dan Operator terpisah dengan statistik masing-masing
- Operator hanya bisa verifikasi, tidak bisa approve final
- Admin bisa approve/reject final pendaftar
- Semua konten (berita, slider, jadwal) dikelompokkan dalam submenu Settings
