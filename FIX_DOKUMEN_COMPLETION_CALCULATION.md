# FIX: Dokumen Upload Completion Calculation

## Problem

Dashboard "Aksi Cepat" section menampilkan status yang tidak konsisten untuk "Upload Dokumen". Status "Lengkap" tidak muncul meskipun semua dokumen yang required sudah diupload.

## Root Cause

1. **Hardcoded Required Count**: Method `uploadDokumen()` menggunakan hardcoded `$requiredCount = 6` untuk validasi dokumen
2. **Inconsistent Logic**: Method `calculateDokumenProgress()` sudah menggunakan `PpdbSettings->dokumen_aktif` secara dinamis, tapi `uploadDokumen()` dan `deleteDokumen()` masih menggunakan logic lama
3. **Mismatch**: Ketika admin mengubah dokumen yang required di settings (misalnya dari 6 menjadi 3), flag `data_dokumen_completed` tidak di-update dengan benar karena menggunakan count yang berbeda

## Solution

### 1. Updated `uploadDokumen()` Method

**File**: `app/Http/Controllers/Pendaftar/DashboardController.php` (lines 327-344)

**Before**:
```php
// Check if all required documents uploaded
$requiredCount = 6; // kk, akta, ijazah, rapor, foto, surat_sehat
if ($calonSiswa->dokumen()->count() >= $requiredCount) {
    $calonSiswa->data_dokumen_completed = true;
    $calonSiswa->save();
}
```

**After**:
```php
// Check if all required documents uploaded - get from settings
$settings = PpdbSettings::first();
$requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
$requiredCount = count($requiredDokumen);

if ($requiredCount > 0) {
    $uploadedCount = $calonSiswa->dokumen()
        ->whereIn('jenis_dokumen', $requiredDokumen)
        ->count();
    
    if ($uploadedCount >= $requiredCount) {
        $calonSiswa->data_dokumen_completed = true;
        $calonSiswa->save();
    } else {
        $calonSiswa->data_dokumen_completed = false;
        $calonSiswa->save();
    }
}
```

### 2. Updated `deleteDokumen()` Method

**File**: `app/Http/Controllers/Pendaftar/DashboardController.php` (lines 383-396)

**Before**:
```php
// Delete file
Storage::disk('public')->delete($dokumen->path_file);

// Delete record
$dokumen->delete();

// Update completion status
$calonSiswa->data_dokumen_completed = false;
$calonSiswa->save();
```

**After**:
```php
// Delete file
Storage::disk('public')->delete($dokumen->file_path); // Fixed typo: path_file → file_path

// Delete record
$dokumen->delete();

// Re-check completion status after deletion
$settings = PpdbSettings::first();
$requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
$requiredCount = count($requiredDokumen);

if ($requiredCount > 0) {
    $uploadedCount = $calonSiswa->dokumen()
        ->whereIn('jenis_dokumen', $requiredDokumen)
        ->count();
    
    $calonSiswa->data_dokumen_completed = ($uploadedCount >= $requiredCount);
    $calonSiswa->save();
}
```

**Bonus Fix**: Typo `path_file` → `file_path` pada Storage::delete()

## Key Changes

1. **Dynamic Settings**: Semua method sekarang menggunakan `PpdbSettings->dokumen_aktif` sebagai single source of truth
2. **Filtered Count**: Menggunakan `whereIn('jenis_dokumen', $requiredDokumen)->count()` untuk hanya menghitung dokumen yang memang required
3. **Consistent Logic**: Upload, delete, dan calculate progress sekarang menggunakan logic yang sama
4. **Smart Re-check**: Method `deleteDokumen()` sekarang melakukan re-check dan bisa set flag menjadi `true` jika dokumen yang tersisa masih memenuhi requirement

## Testing

### Test Script

Created `test_dokumen_completion.php` untuk verifikasi konsistensi antara calculated value dan database flag.

**Output Before Fix**:
```
👤 ACHMAD ALRIZQY AKBAR
   Uploaded: 3/3
   Documents: foto, kk, ijazah
   Calculated: true
   DB Flag: false
   Status: ❌ MISMATCH
```

### Sync Script

Created `sync_dokumen_completion.php` untuk update flag pada existing data.

**Output**:
```
🔄 Updated: ACHMAD ALRIZQY AKBAR
   Old: false → New: true
   Uploaded: 3/3
```

**Output After Fix & Sync**:
```
👤 ACHMAD ALRIZQY AKBAR
   Uploaded: 3/3
   Documents: foto, kk, ijazah
   Calculated: true
   DB Flag: true
   Status: ✅ OK
```

## Impact

### Dashboard Display

**Before**: "Upload Dokumen" menampilkan "Belum Lengkap" meskipun 3/3 dokumen sudah diupload (requirement di settings adalah 3: kk, ijazah, foto)

**After**: "Upload Dokumen" menampilkan "✅ Lengkap" dengan benar karena 3/3 dokumen yang required sudah diupload

### Progress Calculation

Progress bar dan completion status sekarang konsisten di seluruh aplikasi:
- Dashboard index (Aksi Cepat)
- Sidebar menu badges
- Progress tracker
- Upload dokumen page

## Benefits

1. **Single Source of Truth**: Admin bisa mengubah required documents di settings dan system akan langsung adapt
2. **Flexible Configuration**: Tidak perlu hardcode jumlah dokumen di code
3. **Accurate Status**: User melihat status yang akurat sesuai dengan requirement aktual
4. **Better UX**: User tidak bingung kenapa status tidak update meskipun sudah upload dokumen yang diminta

## Related Files

- `app/Http/Controllers/Pendaftar/DashboardController.php` (uploadDokumen, deleteDokumen, calculateDokumenProgress)
- `app/Models/PpdbSettings.php` (dokumen_aktif field)
- `resources/views/pendaftar/dashboard/index.blade.php` (Aksi Cepat section)
- `resources/views/layouts/pendaftar.blade.php` (sidebar badges)

## Migration Notes

**For Existing Data**:

Run sync script to update existing data:
```bash
php sync_dokumen_completion.php
```

This will recalculate and update `data_dokumen_completed` flag for all calon siswa based on current settings.

## Commit Message

```
fix: correct dokumen upload completion calculation using dynamic settings

- Replace hardcoded required count with PpdbSettings->dokumen_aktif
- Add filtered count using whereIn() to match only required document types
- Implement re-check logic in deleteDokumen() method
- Fix typo: path_file → file_path in Storage::delete()
- Ensure consistency between upload, delete, and progress calculation
- Created sync script for updating existing data

Closes: Dashboard "Aksi Cepat" showing incorrect dokumen completion status
```

## Date

{{ date('Y-m-d H:i:s') }}
