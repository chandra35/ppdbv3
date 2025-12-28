# Feature: Backup & Restore + Soft Delete Management

## 📋 Status Implementasi

### ✅ SELESAI - Database & Models

#### 1. Migration Soft Deletes
**File:** `database/migrations/2025_12_28_004620_add_soft_deletes_to_ppdb_tables.php`

Menambahkan kolom:
- `deleted_at` - sudah ada di semua table
- `deleted_by` - UUID user yang menghapus (hanya di calon_siswas)
- `deleted_reason` - alasan penghapusan (hanya di calon_siswas)

#### 2. Models Updated dengan SoftDeletes Trait

**CalonSiswa.php** ✅
- Added `SoftDeletes` trait
- Added `deleted_by`, `deleted_reason` to fillable
- Added `deletedBy()` relationship
- **Boot method dengan Cascade Logic:**
  - `deleting()` event: Soft delete ortu, dokumen, user
  - `forceDeleting()` event: Hard delete + hapus files dari storage
  - `restoring()` event: Restore ortu, dokumen, user

**CalonOrtu.php** ✅
- Added `SoftDeletes` trait

**CalonDokumen.php** ✅
- Added `SoftDeletes` trait

**User.php** ✅
- Added `SoftDeletes` trait

---

## 🎯 NEXT STEPS - Implementation Plan

### Phase 1: Backup & Restore Feature

#### A. Install Laravel Backup Package
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

#### B. Configure Backup (config/backup.php)
```php
'backup' => [
    'name' => env('APP_NAME', 'ppdbv3'),
    'source' => [
        'files' => [
            'include' => [
                storage_path('app/dokumen_pendaftar'),
                storage_path('app/public'),
            ],
        ],
        'databases' => ['mysql'],
    ],
    'destination' => [
        'disks' => ['local'],
    ],
],
```

#### C. BackupController Methods

**File:** `app/Http/Controllers/Admin/BackupController.php`

Methods yang perlu dibuat:
1. `index()` - Tampilkan list backup + form create
2. `create()` - Execute backup (database + files)
3. `download($filename)` - Download file backup
4. `delete($filename)` - Hapus backup lama
5. `restore()` - Upload & restore dari backup

#### D. Routes untuk Backup
```php
// routes/web.php
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'delete'])->name('delete');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
    });
});
```

#### E. Views untuk Backup
- `resources/views/admin/backup/index.blade.php`
  - Card: Backup Manual (button dengan loading spinner)
  - Card: Riwayat Backup (table dengan download & delete)
  - Card: Restore Data (upload form)

---

### Phase 2: Data Management (Soft Delete & Restore)

#### A. DataManagementController Methods

**File:** `app/Http/Controllers/Admin/DataManagementController.php`

Methods yang perlu dibuat:
1. `index()` - Tampilkan data terhapus (list view + checkbox)
2. `restore($id)` - Restore 1 pendaftar
3. `restoreBulk(Request $request)` - Restore multiple (dari checkbox)
4. `forceDelete($id)` - Hard delete permanent (konfirmasi 2x)
5. `forceDeleteBulk(Request $request)` - Hard delete multiple
6. `bulkDeleteByGelombang(Request $request)` - Hapus massal berdasarkan gelombang (soft delete)

#### B. Routes untuk Data Management
```php
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('data-management')->name('data.')->group(function () {
        Route::get('/deleted', [DataManagementController::class, 'index'])->name('deleted');
        Route::post('/restore/{id}', [DataManagementController::class, 'restore'])->name('restore');
        Route::post('/restore-bulk', [DataManagementController::class, 'restoreBulk'])->name('restore.bulk');
        Route::delete('/force-delete/{id}', [DataManagementController::class, 'forceDelete'])->name('force.delete');
        Route::delete('/force-delete-bulk', [DataManagementController::class, 'forceDeleteBulk'])->name('force.delete.bulk');
        Route::post('/bulk-delete-gelombang', [DataManagementController::class, 'bulkDeleteByGelombang'])->name('bulk.delete.gelombang');
    });
});
```

#### C. Views untuk Data Management
- `resources/views/admin/data-management/index.blade.php`
  - Filter: Gelombang, Tanggal Hapus, Search
  - Table dengan checkbox untuk bulk operations
  - Columns: No, Nama, NISN, Gelombang, Dihapus Oleh, Tanggal Hapus, Alasan, Actions
  - Bulk Actions: Restore Selected, Delete Permanent Selected
  - Card Danger Zone: Hapus Data Massal Berdasarkan Gelombang

---

### Phase 3: Update PendaftarController untuk Soft Delete

#### A. Update destroy() method
```php
public function destroy($id)
{
    $pendaftar = CalonSiswa::findOrFail($id);
    
    // Soft delete dengan tracking
    $pendaftar->deleted_by = auth()->id();
    $pendaftar->deleted_reason = request('reason', 'Dihapus oleh admin');
    $pendaftar->save();
    
    $pendaftar->delete(); // Soft delete (akan trigger cascade)
    
    return redirect()->route('admin.pendaftar.index')
        ->with('success', 'Pendaftar berhasil dihapus dan dipindah ke Data Terhapus');
}
```

#### B. Tambah button "Hapus" di show page
- Modal SweetAlert2 dengan textarea untuk alasan
- Konfirmasi: "Data akan dipindah ke Data Terhapus dan masih bisa di-restore"

---

### Phase 4: UI/UX dengan SweetAlert2

#### A. Soft Delete Modal
```javascript
async function deletePendaftar(id) {
    const { value: reason } = await Swal.fire({
        title: 'Hapus Pendaftar?',
        html: 'Data akan dipindah ke <b>Data Terhapus</b> dan bisa di-restore.<br><br>Alasan (opsional):',
        input: 'textarea',
        inputPlaceholder: 'Alasan penghapusan...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
    });
    
    if (reason !== undefined) {
        // Submit delete form dengan reason
    }
}
```

#### B. Hard Delete Modal (Konfirmasi 2x)
```javascript
async function forceDelete(id) {
    const { value: confirm } = await Swal.fire({
        title: 'HAPUS PERMANEN?',
        html: `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Data akan <b>DIHAPUS PERMANEN</b> dan <b>TIDAK BISA DIKEMBALIKAN!</b>
            </div>
            <p>Ketik <code class="bg-dark text-white px-2 py-1">HAPUS</code> untuk konfirmasi</p>
        `,
        input: 'text',
        inputPlaceholder: 'Ketik: HAPUS',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'HAPUS PERMANEN',
        preConfirm: (value) => {
            if (value !== 'HAPUS') {
                Swal.showValidationMessage('Ketik "HAPUS" untuk konfirmasi')
                return false;
            }
            return true;
        }
    });
    
    if (confirm) {
        // Submit force delete
    }
}
```

#### C. Bulk Operations
```javascript
function getSelectedIds() {
    return $('input[name="pendaftar_ids[]"]:checked').map(function() {
        return $(this).val();
    }).get();
}

function restoreBulk() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        Swal.fire('Error', 'Pilih minimal 1 data', 'error');
        return;
    }
    
    Swal.fire({
        title: `Restore ${ids.length} data?`,
        text: 'Data akan dikembalikan ke daftar pendaftar aktif',
        icon: 'question',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit bulk restore
        }
    });
}
```

---

### Phase 5: Menu Structure di AdminLTE

#### Update config/adminlte.php
```php
[
    'text' => 'Pengaturan',
    'icon' => 'fas fa-cogs',
    'can' => 'manage-settings',
    'submenu' => [
        [
            'text' => 'Backup & Restore',
            'url' => 'admin/backup',
            'icon' => 'fas fa-database',
            'can' => 'backup-system',
        ],
        [
            'text' => 'Kelola Data',
            'icon' => 'fas fa-exclamation-triangle text-danger',
            'can' => 'delete-permanent',
            'submenu' => [
                [
                    'text' => 'Data Terhapus',
                    'url' => 'admin/data-management/deleted',
                    'icon' => 'fas fa-trash-restore',
                ],
                [
                    'text' => 'Hapus Data Massal',
                    'url' => 'admin/data-management/bulk-delete',
                    'icon' => 'fas fa-exclamation-circle',
                ],
            ],
        ],
    ],
],
```

---

## 🔒 Security & Permissions

### A. Gate Permissions
```php
// app/Providers/AuthServiceProvider.php
Gate::define('backup-system', function ($user) {
    return $user->hasRole('super-admin');
});

Gate::define('restore-data', function ($user) {
    return $user->hasRole('super-admin');
});

Gate::define('delete-permanent', function ($user) {
    return $user->hasRole('super-admin');
});

Gate::define('soft-delete-pendaftar', function ($user) {
    return $user->hasAnyRole(['super-admin', 'admin']);
});
```

### B. Middleware pada Routes
- Backup & Restore: `role:super-admin`
- Data Terhapus (View & Restore): `role:super-admin`
- Force Delete: `role:super-admin` + konfirmasi password
- Soft Delete: `role:super-admin|admin`

---

## 📊 Database Queries

### A. Get Deleted Data
```php
$deleted = CalonSiswa::onlyTrashed()
    ->with(['deletedBy', 'jalurPendaftaran', 'gelombangPendaftaran'])
    ->latest('deleted_at')
    ->paginate(20);
```

### B. Restore Data
```php
$pendaftar = CalonSiswa::withTrashed()->findOrFail($id);
$pendaftar->restore(); // Akan trigger restoring event di boot()
```

### C. Force Delete
```php
$pendaftar = CalonSiswa::withTrashed()->findOrFail($id);
$pendaftar->forceDelete(); // Akan trigger forceDeleting event
```

### D. Bulk Delete by Gelombang (Soft Delete)
```php
$pendaftars = CalonSiswa::where('gelombang_pendaftaran_id', $gelombangId)->get();
foreach ($pendaftars as $pendaftar) {
    $pendaftar->deleted_by = auth()->id();
    $pendaftar->deleted_reason = "Hapus massal gelombang: {$gelombang->nama}";
    $pendaftar->save();
    $pendaftar->delete();
}
```

---

## 🎨 UI Components

### A. Status Badge
```blade
@if($pendaftar->trashed())
    <span class="badge badge-danger">
        <i class="fas fa-trash"></i> Terhapus
    </span>
@endif
```

### B. Action Buttons
```blade
<!-- Soft Delete -->
<button onclick="deletePendaftar('{{ $pendaftar->id }}')" class="btn btn-danger btn-sm">
    <i class="fas fa-trash"></i> Hapus
</button>

<!-- Restore -->
<button onclick="restore('{{ $pendaftar->id }}')" class="btn btn-success btn-sm">
    <i class="fas fa-undo"></i> Restore
</button>

<!-- Force Delete -->
<button onclick="forceDelete('{{ $pendaftar->id }}')" class="btn btn-dark btn-sm">
    <i class="fas fa-trash-alt"></i> Hapus Permanen
</button>
```

---

## ✅ Testing Checklist

### Soft Delete
- [ ] Soft delete 1 pendaftar
- [ ] Cascade delete ortu, dokumen, user
- [ ] Data tidak muncul di list pendaftar
- [ ] Data muncul di "Data Terhapus"
- [ ] deleted_by & deleted_reason tersimpan

### Restore
- [ ] Restore 1 pendaftar
- [ ] Cascade restore ortu, dokumen, user
- [ ] Data kembali ke list pendaftar
- [ ] Relationship tetap utuh

### Force Delete
- [ ] Hard delete 1 pendaftar
- [ ] File dokumen terhapus dari storage
- [ ] Cascade hard delete ortu, dokumen, user, histories
- [ ] Data hilang permanen dari database

### Backup & Restore
- [ ] Create backup (database + files)
- [ ] Download backup berhasil
- [ ] Restore from backup berhasil
- [ ] File dokumen terupload kembali

---

## 📝 Notes

1. **Backup Location**: `storage/app/backups/`
2. **Max Backup Size**: Configure max zip size di backup config
3. **Cron Job**: Set schedule backup otomatis
4. **Retention**: Auto delete backup > 30 hari
5. **Logging**: Log semua operasi delete & restore ke activity log

---

## 🚀 Estimasi Waktu

- Phase 1 (Backup & Restore): 2-3 jam
- Phase 2 (Data Management): 2-3 jam
- Phase 3 (Update Controller): 1 jam
- Phase 4 (UI/UX): 2 jam
- Phase 5 (Menu & Permissions): 1 jam
- Testing: 2 jam

**Total: 10-13 jam**

---

## 🎯 Priority

1. **HIGH**: Soft Delete + Cascade (DONE ✅)
2. **HIGH**: Data Terhapus + Restore UI
3. **MEDIUM**: Backup & Restore
4. **LOW**: Auto Archive (Phase 2 - future)

---

**Status: Foundation Complete - Ready for UI Implementation**
