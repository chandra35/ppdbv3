# 🚀 PPDB V3 - Setup & Development Guide

> Project PPDB Standalone yang share database dengan SIMANSA v3

---

## ✅ Status Setup

- ✅ Project Laravel 12 created
- ✅ Dependencies installed (Composer + NPM)
- ✅ .env configured (Database: simansav3)
- ✅ Models created: CalonSiswa, CalonDokumen, PpdbSettings, Verifikator, Gtk
- ✅ Migrations created
- ✅ Controllers created (Ppdb, Admin)
- ✅ Views folder structure ready

---

## 📁 Project Structure

```
d:\projek\ppdbv3\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Ppdb/
│   │   │   │   ├── LandingController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── DashboardController.php
│   │   │   └── Admin/
│   │   │       ├── PpdbSettingsController.php
│   │   │       ├── PpdbVerifikatorController.php
│   │   │       └── PpdbPendaftarController.php
│   │   └── Middleware/
│   │
│   └── Models/
│       ├── CalonSiswa.php
│       ├── CalonDokumen.php
│       ├── PpdbSettings.php
│       ├── Verifikator.php
│       └── Gtk.php (link ke SIMANSA)
│
├── database/
│   └── migrations/
│       ├── *_create_calon_siswas_table.php
│       ├── *_create_calon_dokumens_table.php
│       ├── *_create_ppdb_settings_table.php
│       └── *_create_verifikators_table.php
│
├── resources/
│   └── views/
│       ├── ppdb/
│       │   ├── landing.blade.php
│       │   ├── login.blade.php
│       │   ├── dashboard.blade.php
│       │   └── register/
│       │       ├── step1.blade.php
│       │       ├── step2.blade.php
│       │       ├── step3.blade.php
│       │       └── step4.blade.php
│       └── admin/
│           └── ppdb/
│               ├── settings.blade.php
│               ├── verifikator.blade.php
│               └── pendaftar.blade.php
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── ppdb.php (NEW - routes untuk PPDB)
│
├── .env (configured)
├── composer.json
├── package.json
└── artisan

SIMANSA (terpisah):
d:\projek\simansav3\
```

---

## 🔄 Database Connection

**ppdbv3 .env:**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simansav3        ← SAME DATABASE
DB_USERNAME=root
DB_PASSWORD=
```

✅ Shared database dengan SIMANSA
✅ Query langsung ke tabel `gtk`, `users`, `siswa`, dll

---

## 🛠️ Next Steps

### **1. Update Migrations**

Edit migration files untuk definisi tabel yang benar:

```bash
cd d:\projek\ppdbv3
```

**File yang perlu di-update:**
- `database/migrations/*_create_calon_siswas_table.php`
- `database/migrations/*_create_calon_dokumens_table.php`
- `database/migrations/*_create_ppdb_settings_table.php`
- `database/migrations/*_create_verifikators_table.php`

---

### **2. Update Models dengan Relationships**

**CalonSiswa.php:**
```php
public function dokumen()
{
    return $this->hasMany(CalonDokumen::class);
}

public function tahunPelajaran()
{
    return $this->belongsTo(TahunPelajaran::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
```

**CalonDokumen.php:**
```php
public function calonSiswa()
{
    return $this->belongsTo(CalonSiswa::class);
}

public function verifikator()
{
    return $this->belongsTo(Verifikator::class, 'verifikator_id');
}
```

**Verifikator.php:**
```php
public function gtk()
{
    return $this->belongsTo(Gtk::class, 'gtk_id');
}

public function ppdbSettings()
{
    return $this->belongsTo(PpdbSettings::class);
}

public function calonDokumen()
{
    return $this->hasMany(CalonDokumen::class);
}
```

**Gtk.php:**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Gtk extends Model
{
    use HasUuids;
    
    protected $table = 'gtk';
    protected $guarded = [];
    
    public function verifikator()
    {
        return $this->hasOne(Verifikator::class, 'gtk_id');
    }
}
```

---

### **3. Run Migrations**

```bash
php artisan migrate
```

Migrations akan membuat tabel baru di database `simansav3`:
- `calon_siswa`
- `calon_dokumen`
- `ppdb_settings`
- `ppdb_verifikator`

---

### **4. Setup Routes**

Create `routes/ppdb.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ppdb\LandingController;
use App\Http\Controllers\Ppdb\RegisterController;
use App\Http\Controllers\Ppdb\DashboardController;

// Public routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/login', [LandingController::class, 'login'])->name('login');

// Registration routes
Route::post('/register/nisn-validate', [RegisterController::class, 'validateNisn']);
Route::post('/register/store', [RegisterController::class, 'store']);

// Dashboard calon siswa
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/dokumen', [DashboardController::class, 'dokumen']);
    Route::post('/dashboard/dokumen/upload', [DashboardController::class, 'uploadDokumen']);
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('ppdb/settings', 'Admin\PpdbSettingsController');
    Route::resource('ppdb/verifikator', 'Admin\PpdbVerifikatorController');
    Route::resource('ppdb/pendaftar', 'Admin\PpdbPendaftarController');
});
```

Update `routes/web.php`:
```php
Route::group(['prefix' => 'ppdb'], function () {
    require __DIR__.'/ppdb.php';
});
```

---

### **5. Copy Services dari SIMANSA**

Copy files dari SIMANSA ke PPDB:

```bash
# Copy NIS Validation Service
Copy-Item "d:\projek\simansav3\app\Services\NisValidationService.php" `
          "d:\projek\ppdbv3\app\Services\NisValidationService.php"

# Copy NIS Validation Controller (dari API)
Copy-Item "d:\projek\simansav3\app\Http\Controllers\Api\NisValidationController.php" `
          "d:\projek\ppdbv3\app\Http\Controllers\Api\NisValidationController.php"
```

---

### **6. Install Additional Packages (Optional)**

```bash
# Upload file handling
composer require spatie/laravel-medialibrary

# Excel import/export
composer require maatwebsite/excel

# UUID support (already in Laravel 12)
# composer require ramsey/uuid
```

---

## 🚀 Development Commands

```bash
# Start PPDB server on port 8001
php artisan serve --host=0.0.0.0 --port=8001

# Generate key (already done)
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets (Vite)
npm run build
npm run dev

# Tinker shell
php artisan tinker
```

---

## 🔗 Integration dengan SIMANSA

### **Database Query Langsung**

```php
// Di PPDB, ambil GTK dari SIMANSA
$gtkList = \App\Models\Gtk::all();

// Ambil siswa dari SIMANSA
$siswaSudahDaftar = \App\Models\Siswa::all();

// Ambil tahun pelajaran
$tahunAktif = \App\Models\TahunPelajaran::where('is_active', true)->first();
```

### **Import ke SIMANSA**

```php
// Di PPDB, query calon yang sudah diterima
$calonDiterima = CalonSiswa::where('status_admisi', 'diterima')->get();

// Transform & insert ke SIMANSA siswa table
foreach ($calonDiterima as $calon) {
    \App\Models\Siswa::create([
        'nisn' => $calon->nisn,
        'nama' => $calon->nama_lengkap,
        'tempat_lahir' => $calon->tempat_lahir,
        'tanggal_lahir' => $calon->tanggal_lahir,
        'kelas_id' => $kelasId, // assign kelas
        'tahun_pelajaran_id' => $tahunAktif->id,
        'status' => 'active',
    ]);
}
```

---

## 📋 Files to Create Next

Priority order:

1. **Migrations** - Update tabel schema
2. **Models** - Add relationships
3. **Controllers** - Business logic
4. **Routes** - URL mappings
5. **Views** - Frontend UI
6. **Services** - Copy dari SIMANSA
7. **Middleware** - Authentication/authorization

---

## 🔍 Comparing Files dengan SIMANSA

### **VS Code Side-by-Side Compare:**
```
1. Open ppdbv3 folder in VS Code
2. Open simansav3 folder in another VS Code window
3. Ctrl+K Ctrl+O → Open to the Side
4. Compare file-by-file
```

### **Command Line Compare:**
```powershell
# Compare NisValidationService
diff "d:\projek\simansav3\app\Services\NisValidationService.php" `
     "d:\projek\ppdbv3\app\Services\NisValidationService.php"
```

---

## ✅ Checklist Development

- [ ] Update migrations dengan schema lengkap
- [ ] Add relationships di semua models
- [ ] Create API service untuk NISN validation
- [ ] Setup authentication
- [ ] Create landing page UI
- [ ] Create registration form (4 steps)
- [ ] Create admin dashboard
- [ ] Create verifikator interface
- [ ] Create document upload system
- [ ] Create approval workflow
- [ ] Create export/import to SIMANSA
- [ ] Testing & debugging
- [ ] Deployment to production

---

## 📞 Quick Reference

| Command | Purpose |
|---------|---------|
| `php artisan serve --port=8001` | Start server |
| `php artisan migrate` | Run migrations |
| `php artisan make:model Name -m` | Create model + migration |
| `php artisan tinker` | Interactive shell |
| `npm run dev` | Watch Vite assets |
| `npm run build` | Build production assets |

---

**Ready to start development!** 🎯

Next: Customize migrations untuk tabel PPDB

