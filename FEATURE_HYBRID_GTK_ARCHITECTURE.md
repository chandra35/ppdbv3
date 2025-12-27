# Feature: Hybrid GTK Architecture

## Overview
Implementasi arsitektur hybrid untuk data GTK yang mendukung 2 mode operasi:
1. **Local Mode**: Menggunakan tabel `gtks` lokal di database ppdbv3
2. **SIMANSA Mode**: Koneksi langsung ke database SIMANSA (read-only)

Mode ini dapat dikonfigurasi melalui environment variable `GTK_SOURCE`.

## Background
- PPDB v3 membutuhkan data GTK untuk registrasi verifikator dan user
- Deployment dapat dilakukan di server yang berbeda dengan SIMANSA
- Perlu fleksibilitas untuk switch antara mode lokal dan mode SIMANSA
- Harus ada fallback mechanism jika koneksi SIMANSA tidak tersedia

## Architecture

### Database Structure

#### Local GTKs Table (ppdbv3)
```sql
gtks (
    id UUID PRIMARY KEY,
    nama_lengkap VARCHAR(255),
    nip VARCHAR(18),
    nuptk VARCHAR(16),
    nik VARCHAR(16),
    email VARCHAR(255),
    nomor_hp VARCHAR(20),
    jenis_kelamin ENUM('L', 'P'),
    tanggal_lahir DATE,
    tempat_lahir VARCHAR(255),
    kategori_ptk VARCHAR(50),
    jenis_ptk VARCHAR(100),
    jabatan VARCHAR(100),
    tugas_tambahan VARCHAR(100),
    status_kepegawaian VARCHAR(50),
    -- Sync tracking
    source ENUM('manual', 'simansa') DEFAULT 'manual',
    simansa_id VARCHAR(36),
    synced_at TIMESTAMP,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

#### PPDB Verifikators Table
```sql
ppdb_verifikators (
    id UUID PRIMARY KEY,
    user_id UUID,  -- Changed from gtk_id
    ppdb_settings_id UUID,
    is_aktif BOOLEAN,
    keterangan TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
)
```

### Models

#### 1. LocalGtk (app/Models/LocalGtk.php)
- Connection: `mysql` (ppdbv3)
- Table: `gtks`
- Fillable: All GTK fields + sync tracking
- Scopes:
  - `aktif()` - Not deleted
  - `manual()` - Manually created
  - `synced()` - Synced from SIMANSA

#### 2. SimansaGtk (app/Models/SimansaGtk.php)
- Connection: `simansav3`
- Table: `gtks`
- Read-only access
- Scopes:
  - `aktif()` - Not deleted

#### 3. Verifikator (app/Models/Verifikator.php)
- Relation changed: `gtk()` → `user()`
- Now uses User model instead of GTK model

### Repository Pattern

#### GtkRepository (app/Repositories/GtkRepository.php)
Abstraction layer untuk GTK data access:

**Methods:**
- `getSource()` - Get current GTK source (local/simansa)
- `all()` - Get all GTK based on current source
- `fromLocal()` - Get from local database
- `fromSimansa()` - Get from SIMANSA (with fallback)
- `find($id)` - Find GTK by ID
- `checkSimansaConnection()` - Test SIMANSA availability
- `isSimansaAvailable()` - Check if SIMANSA is configured and accessible
- `getAvailableForRegistration()` - Get GTK that can be registered as user

**Fallback Logic:**
```php
public function all()
{
    if ($this->getSource() === 'simansa') {
        if ($this->checkSimansaConnection()) {
            return $this->fromSimansa();
        }
        // Fallback to local if SIMANSA unavailable
        return $this->fromLocal();
    }
    
    return $this->fromLocal();
}
```

### Sync Service

#### GtkSyncService (app/Services/GtkSyncService.php)
Handles synchronization from SIMANSA to local:

**Methods:**
- `syncFromSimansa(?callable $progressCallback)` - Sync all GTK
- `syncSingleGtk($simansaGtk)` - Sync single GTK record
- `getStats()` - Get sync statistics
- `checkSimansaConnection()` - Validate connection

**Sync Logic:**
1. Check SIMANSA connection availability
2. Fetch all GTK from SIMANSA
3. For each GTK:
   - Check if exists in local (by simansa_id)
   - If not exists: Create new with source='simansa'
   - If exists: Update with latest data
4. Track synced/updated/error counts
5. Return result array

**Progress Callback:**
Optional callback for progress tracking in CLI command:
```php
$result = $syncService->syncFromSimansa(function($current, $total) {
    echo "Progress: {$current}/{$total}\n";
});
```

### Artisan Command

#### SyncGtkFromSimansa (app/Console/Commands/SyncGtkFromSimansa.php)
```bash
php artisan gtk:sync [--force]
```

**Features:**
- Progress bar untuk visual feedback
- Connection check sebelum sync
- Warning jika GTK_SOURCE = local
- `--force` flag untuk override warning
- Detailed statistics output

**Output Example:**
```
===========================================
   SYNC GTK FROM SIMANSA TO LOCAL
===========================================

✓ Koneksi ke SIMANSA: OK

Memulai sinkronisasi...
 120/120 [============================] 100%

===========================================
   SYNC SELESAI
===========================================

✓ Berhasil sync:    120 GTK
⟳ Diperbarui:       0 GTK
✗ Error:          0 GTK

Sync completed: 120 new, 0 updated, 0 errors
```

### Controller

#### GtkController (app/Http/Controllers/Admin/GtkController.php)
Updated to support hybrid mode:

**Methods:**
- `index()` - List GTK with filters and sync info
- `create()` - Form tambah GTK manual
- `store()` - Save manual GTK (source='manual')
- `show()` - Detail GTK
- `edit()` - Form edit GTK
- `update()` - Update GTK data
- `destroy()` - Soft delete GTK (validation: cannot delete if has user)
- `syncFromSimansa()` - Trigger sync via web UI
- `registerAsUser()` - Convert GTK to PPDB user
- `updateRoles()` - Update user roles
- `removeUser()` - Delete PPDB user
- `bulkRegister()` - Bulk register GTK as users

**Constructor Injection:**
```php
public function __construct(
    GtkRepository $gtkRepository,
    GtkSyncService $syncService
)
```

### Configuration

#### config/ppdb.php
```php
return [
    'gtk_source' => env('GTK_SOURCE', 'local'),
    'gtk_auto_sync' => env('GTK_AUTO_SYNC', false),
    'gtk_sync_interval' => env('GTK_SYNC_INTERVAL', 60),
    'simansa_available' => function() {
        try {
            DB::connection('simansav3')->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    },
];
```

#### .env Variables
```env
# GTK Hybrid Configuration
GTK_SOURCE=local              # local | simansa
GTK_AUTO_SYNC=false          # true | false
GTK_SYNC_INTERVAL=60         # minutes

# SIMANSA Database Connection
SIMANSA_DB_HOST=127.0.0.1
SIMANSA_DB_PORT=3306
SIMANSA_DB_DATABASE=simansav3
SIMANSA_DB_USERNAME=root
SIMANSA_DB_PASSWORD=
```

### Routes

```php
// GTK Management (Hybrid: Local + SIMANSA Sync)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/gtk', [GtkController::class, 'index'])->name('gtk.index');
    Route::get('/gtk/create', [GtkController::class, 'create'])->name('gtk.create');
    Route::post('/gtk', [GtkController::class, 'store'])->name('gtk.store');
    Route::get('/gtk/{id}', [GtkController::class, 'show'])->name('gtk.show');
    Route::get('/gtk/{id}/edit', [GtkController::class, 'edit'])->name('gtk.edit');
    Route::put('/gtk/{id}', [GtkController::class, 'update'])->name('gtk.update');
    Route::delete('/gtk/{id}', [GtkController::class, 'destroy'])->name('gtk.destroy');
    Route::post('/gtk/sync', [GtkController::class, 'syncFromSimansa'])->name('gtk.sync');
    Route::post('/gtk/{id}/register', [GtkController::class, 'registerAsUser'])->name('gtk.register');
    Route::put('/gtk/{id}/update-roles', [GtkController::class, 'updateRoles'])->name('gtk.update-roles');
    Route::delete('/gtk/{id}/remove', [GtkController::class, 'removeUser'])->name('gtk.remove');
    Route::post('/gtk/bulk-register', [GtkController::class, 'bulkRegister'])->name('gtk.bulk-register');
});
```

## Usage Scenarios

### Scenario 1: Same Server Deployment
PPDB v3 dan SIMANSA v3 di server yang sama.

**Configuration:**
```env
GTK_SOURCE=simansa
GTK_AUTO_SYNC=false
```

**Behavior:**
- Repository mengakses langsung database SIMANSA
- Data selalu real-time
- Tidak perlu sync manual
- Jika koneksi gagal, fallback ke local (jika ada)

### Scenario 2: Different Server Deployment
PPDB v3 dan SIMANSA v3 di server berbeda (tidak bisa koneksi database langsung).

**Configuration:**
```env
GTK_SOURCE=local
GTK_AUTO_SYNC=false
```

**Workflow:**
1. Admin sync data GTK via command: `php artisan gtk:sync`
2. Or sync via web UI: Button "Sync dari SIMANSA"
3. Data disimpan di local database
4. Repository menggunakan data local
5. Admin bisa CRUD manual jika diperlukan

### Scenario 3: Hybrid with Periodic Sync
PPDB v3 bisa akses SIMANSA tapi ingin cache data local.

**Configuration:**
```env
GTK_SOURCE=local
GTK_AUTO_SYNC=true
GTK_SYNC_INTERVAL=60
```

**Behavior:**
- Gunakan data local untuk performa
- Auto sync setiap 60 menit (jika diimplementasikan di controller)
- Manual sync tetap available

## Manual GTK CRUD

Untuk deployment di server berbeda dimana SIMANSA tidak accessible:

1. **Tambah GTK Manual:**
   - Navigate: Admin → GTK → Tambah GTK
   - Form input: nama, NIP, email, dll
   - Source automatically set to 'manual'

2. **Edit GTK:**
   - Hanya bisa edit GTK dengan source='manual'
   - GTK synced dari SIMANSA tidak bisa diedit (read-only)

3. **Hapus GTK:**
   - Validation: Tidak bisa hapus jika sudah jadi user PPDB
   - Soft delete

## Security & Validation

### Access Control
- Semua route GTK ada di dalam middleware admin
- Role-based access control via Spatie Permission

### Validation Rules
- Email unique check
- NIP unique check (optional)
- Cannot delete GTK if has associated user
- Cannot delete user if user has verifikator assignments

### Error Handling
- Try-catch di semua method controller
- Graceful fallback jika SIMANSA unavailable
- Logging untuk debugging

## Testing Checklist

### Unit Tests
- [ ] GtkRepository source switching
- [ ] GtkRepository fallback logic
- [ ] GtkSyncService sync logic
- [ ] LocalGtk scopes
- [ ] SimansaGtk connection

### Feature Tests
- [ ] Sync command execution
- [ ] Manual GTK CRUD
- [ ] GTK to user registration
- [ ] Verifikator assignment with user

### Integration Tests
- [ ] Different server scenario (mocked)
- [ ] SIMANSA connection failure
- [ ] Fallback behavior

## Migration Path

### From Old Implementation to Hybrid
1. Run migration: `2025_12_27_120033_alter_ppdb_verifikators_table_change_gtk_to_user.php`
   - Changes gtk_id to user_id in ppdb_verifikators
2. Run migration: `2025_12_27_120953_create_gtks_local_table.php`
   - Creates local gtks table
3. Run sync: `php artisan gtk:sync --force`
   - Populate local gtks from SIMANSA
4. Update .env with GTK_SOURCE configuration
5. Test verifikator assignment flow

## Troubleshooting

### Issue: "SIMANSA database connection not available"
**Solution:**
1. Check .env SIMANSA_DB_* configuration
2. Test connection: `php artisan tinker --execute="DB::connection('simansav3')->getPdo()"`
3. If not accessible, use GTK_SOURCE=local and manual CRUD

### Issue: "GTK already registered as user"
**Solution:**
- This is expected behavior
- GTK can only be registered once
- Use "Update Roles" instead if need to modify

### Issue: Sync fails midway
**Solution:**
- Check logs: `storage/logs/laravel.log`
- Retry sync: Command is transactional, safe to retry
- If specific GTK fails, check data integrity in SIMANSA

### Issue: Cannot delete GTK
**Solution:**
- Check if GTK has associated user
- Delete user first (if safe)
- Or just soft-delete (data preserved)

## Performance Considerations

### Indexing
Local gtks table has indexes on:
- email (unique)
- nip (nullable, unique)
- simansa_id (for sync lookup)
- source (for filtering)

### Caching (Future Enhancement)
- Cache GTK list for 5 minutes
- Invalidate on sync/CRUD
- Use Redis/Memcached for better performance

### Pagination
- All listing use pagination (20 per page)
- Prevents memory issues with large datasets

## Future Enhancements

1. **Auto Sync Scheduler:**
   ```php
   // kernel.php
   $schedule->command('gtk:sync')->hourly();
   ```

2. **Sync Status Dashboard:**
   - Last sync time
   - Sync health monitoring
   - Failed sync retry queue

3. **Differential Sync:**
   - Only sync changed records
   - Based on updated_at comparison

4. **Webhook Integration:**
   - SIMANSA notifies PPDB when GTK data changes
   - Real-time sync trigger

5. **Multi-School Support:**
   - Filter GTK by school
   - School-specific verifikators

## References

- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [Service Layer Pattern](https://martinfowler.com/eaaCatalog/serviceLayer.html)
- [Laravel Database Connections](https://laravel.com/docs/database#configuration)
- [Laravel Console Commands](https://laravel.com/docs/artisan#writing-commands)

---

**Created:** 2025-12-27  
**Last Updated:** 2025-12-27  
**Version:** 1.0  
**Author:** Development Team
