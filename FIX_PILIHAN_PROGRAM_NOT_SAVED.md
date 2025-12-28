# FIX: Pilihan Program Tidak Tersimpan

## Problem

User memilih pilihan program di http://127.0.0.1:7000/pendaftar/pilihan-program, mendapat pesan sukses, tapi setelah refresh halaman data pilihan tidak tersimpan.

## Root Cause

Field `pilihan_program` dan field-field finalisasi lainnya (is_finalisasi, tanggal_finalisasi, nomor_tes, nilai_cbt, nilai_wawancara, nilai_akhir, ranking, catatan_admisi) tidak ada di array `$fillable` pada model `CalonSiswa`.

Ketika controller memanggil:
```php
$calonSiswa->update([
    'pilihan_program' => $validated['pilihan_program']
]);
```

Laravel akan mengabaikan field tersebut karena mass assignment protection. Method `update()` akan return success tapi tidak ada data yang disimpan ke database.

## Solution

### 1. Update Model CalonSiswa - Add to $fillable

**File**: `app/Models/CalonSiswa.php`

**Added fields to $fillable**:
```php
protected $fillable = [
    // ... existing fields ...
    
    // Completion flags
    'data_diri_completed',
    'data_ortu_completed',
    'data_dokumen_completed',
    'nilai_rapor_completed',
    
    // Pilihan Program & Finalisasi
    'pilihan_program',
    'is_finalisasi',
    'tanggal_finalisasi',
    'nomor_tes',
    
    // Nilai & Ranking
    'nilai_cbt',
    'nilai_wawancara',
    'nilai_akhir',
    'ranking',
    'catatan_admisi',
    
    // Relations
    'user_id',
    'tahun_pelajaran_id',
    'tanggal_registrasi',
    
    // Soft delete fields
    'deleted_by',
    'deleted_reason',
];
```

### 2. Update Model CalonSiswa - Add to $casts

**File**: `app/Models/CalonSiswa.php`

**Added casts for new fields**:
```php
protected $casts = [
    'tanggal_lahir' => 'date',
    'tanggal_verifikasi' => 'datetime',
    'tanggal_finalisasi' => 'datetime',         // NEW
    'nisn_valid' => 'boolean',
    'data_diri_completed' => 'boolean',
    'data_ortu_completed' => 'boolean',
    'data_dokumen_completed' => 'boolean',
    'nilai_rapor_completed' => 'boolean',       // NEW
    'is_finalisasi' => 'boolean',               // NEW
    'jumlah_saudara' => 'integer',
    'anak_ke' => 'integer',
    'nilai_cbt' => 'decimal:2',                 // NEW
    'nilai_wawancara' => 'decimal:2',           // NEW
    'nilai_akhir' => 'decimal:2',               // NEW
    'ranking' => 'integer',                     // NEW
];
```

## Key Changes

1. **Added 9 fields to $fillable**: pilihan_program, is_finalisasi, tanggal_finalisasi, nomor_tes, nilai_cbt, nilai_wawancara, nilai_akhir, ranking, catatan_admisi, nilai_rapor_completed

2. **Added 7 fields to $casts**: tanggal_finalisasi, nilai_rapor_completed, is_finalisasi, nilai_cbt, nilai_wawancara, nilai_akhir, ranking

3. **Mass Assignment Protection**: Fields sekarang bisa diupdate melalui `update()`, `create()`, dan `fill()` methods

## Testing

### Test Script 1: test_pilihan_program_save.php

Test apakah update() method bisa menyimpan data:

**Output**:
```
📋 Testing dengan: ACHMAD ALRIZQY AKBAR
   Current pilihan_program: NULL

🔄 Mencoba update pilihan_program ke 'Test Program'...
✅ Update berhasil!

📊 Nilai setelah update:
   pilihan_program: Test Program

✅ DATA TERSIMPAN DENGAN BENAR!
```

### Test Script 2: verify_pilihan_persist.php

Verify data persist di database setelah fresh query:

**Output**:
```
📋 Calon Siswa: ACHMAD ALRIZQY AKBAR
📊 pilihan_program: Test Program

✅ DATA MASIH ADA DI DATABASE!
```

## Impact

### Before Fix:
- User pilih program → Success message → Refresh → Data hilang
- Frustrating user experience
- Data tidak bisa disimpan sama sekali

### After Fix:
- User pilih program → Success message → Refresh → Data tetap tersimpan ✅
- Normal expected behavior
- Finalisasi workflow bisa berfungsi dengan benar

## Related Features

Fix ini juga memungkinkan fitur-fitur lain yang menggunakan field-field tersebut:

1. **Finalisasi** - `is_finalisasi`, `tanggal_finalisasi`, `nomor_tes`
2. **Input Nilai** - `nilai_cbt`, `nilai_wawancara`, `nilai_akhir`
3. **Ranking** - `ranking`
4. **Admisi** - `catatan_admisi`
5. **Progress Tracker** - `nilai_rapor_completed`

Semua field ini sekarang bisa di-update dengan aman melalui controller methods.

## Related Files

- `app/Models/CalonSiswa.php` ($fillable, $casts)
- `app/Http/Controllers/Pendaftar/DashboardController.php` (storePilihanProgram, storeFinalisasi)
- `database/migrations/2025_12_28_074757_add_finalisasi_and_pilihan_program_fields_to_calon_siswas.php`

## Notes

**Important**: Setelah mengubah `$fillable` atau `$casts`, selalu jalankan:
```bash
php artisan config:clear
php artisan cache:clear
```

Untuk memastikan perubahan model terdeteksi oleh application.

## Commit Message

```
fix: add pilihan_program and finalisasi fields to CalonSiswa fillable

- Add pilihan_program, is_finalisasi, tanggal_finalisasi, nomor_tes to $fillable
- Add nilai_cbt, nilai_wawancara, nilai_akhir, ranking, catatan_admisi to $fillable
- Add nilai_rapor_completed to $fillable for progress tracking
- Update $casts for datetime and boolean fields
- Fix mass assignment protection blocking data save

This resolves the issue where pilihan program selection was not persisted
after page refresh due to mass assignment protection.

Closes: Pilihan program tidak tersimpan setelah submit form
```

## Date

2025-12-28
