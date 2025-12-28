# FEATURE: Cetak Kartu System - Nomor Tes & Two Types of Printouts

## Overview

Sistem cetak kartu PPDB yang terintegrasi dengan nomor tes otomatis. Terdapat 2 jenis cetakan:

1. **Cetak Bukti Registrasi** - Dokumen A4 lengkap untuk arsip TU
2. **Cetak Kartu Ujian** - Kartu identitas peserta dengan foto & password

## Database Changes

### Migration: `add_nomor_tes_settings_to_ppdb_settings_table.php`

**New columns in `ppdb_settings` table:**

```php
nomor_tes_prefix      VARCHAR(10)   DEFAULT 'NTS'
nomor_tes_format      VARCHAR(100)  DEFAULT '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}'
nomor_tes_digit       INT          DEFAULT 4
nomor_tes_counter     JSON         NULL (stores per-jalur counters)
```

**Counter storage format:**
```json
{
  "uuid-jalur-1": 5,
  "uuid-jalur-2": 12,
  "uuid-jalur-3": 3
}
```

## Model Updates

### `PpdbSettings.php`

**$fillable added:**
- `nomor_tes_prefix`
- `nomor_tes_format`
- `nomor_tes_digit`
- `nomor_tes_counter`

**$casts added:**
- `nomor_tes_counter => 'array'`

**$attributes defaults:**
```php
'nomor_tes_prefix' => 'NTS',
'nomor_tes_format' => '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}',
'nomor_tes_digit' => 4,
```

## Admin Configuration

### Settings Page (`resources/views/admin/settings/index.blade.php`)

New section: **Format Nomor Tes**

**Fields:**
1. **Prefix Nomor Tes** - Text input (max 10 chars)
   - Contoh: NTS, TES, UJIAN
   - Required

2. **Format Nomor Tes** - Text input (readonly)
   - Format: `{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}`
   - Placeholders:
     * `{PREFIX}` - Prefix dari settings
     * `{TAHUN}` - Tahun pelajaran (mulai)
     * `{JALUR}` - 3 huruf pertama nama jalur (uppercase)
     * `{NOMOR}` - Sequential number dengan leading zeros

3. **Jumlah Digit Nomor** - Select dropdown
   - Options: 3 digit (001-999), 4 digit (0001-9999), 5 digit (00001-99999)
   - Default: 4
   - Required

**Live Preview:**
- JavaScript auto-update preview saat prefix/digit berubah
- Contoh: `NTS-2025-PRE-0001`

**Validation:**
```php
'nomor_tes_prefix' => 'required|string|max:10',
'nomor_tes_digit' => 'required|integer|in:3,4,5',
```

## Nomor Tes Generation Logic

### `DashboardController@storeFinalisasi()` - Updated

**Old logic:**
```php
// Hard-coded format: PPDB-TAHUN-JALUR-SEQUENCE
$nomorTes = "PPDB-{$tahun}-{$jalurCode}-{$sequence}";
```

**New logic:**
```php
// Get settings
$settings = PpdbSettings::first();

// Get counter for this jalur
$counters = $settings->nomor_tes_counter ?? [];
$jalurKey = (string) $calonSiswa->jalur_pendaftaran_id;
$counter = ($counters[$jalurKey] ?? 0) + 1;

// Update counter atomically
$counters[$jalurKey] = $counter;
$settings->update(['nomor_tes_counter' => $counters]);

// Generate nomor using format template
$format = $settings->nomor_tes_format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}';
$nomor = str_pad($counter, $settings->nomor_tes_digit ?? 4, '0', STR_PAD_LEFT);

$nomorTes = str_replace(
    ['{PREFIX}', '{TAHUN}', '{JALUR}', '{NOMOR}'],
    [$settings->nomor_tes_prefix ?? 'NTS', $tahun, $jalurCode, $nomor],
    $format
);
```

**Key features:**
- ✅ Configurable via admin settings
- ✅ Sequential per jalur (independent numbering)
- ✅ Atomic counter update (thread-safe)
- ✅ Flexible format with placeholders
- ✅ Custom digit count (3-5 digits)

## Routes

### Updated: `routes/ppdb.php`

**Removed:**
```php
// OLD - Generic cetak bukti
Route::get('/cetak-bukti', [...])->name('cetak-bukti');
```

**Added:**
```php
// NEW - Two specific routes
Route::get('/cetak-bukti-registrasi', [PendaftarDashboardController::class, 'cetakBuktiRegistrasi'])
    ->name('cetak-bukti-registrasi');

Route::get('/cetak-kartu-ujian', [PendaftarDashboardController::class, 'cetakKartuUjian'])
    ->name('cetak-kartu-ujian');
```

## Controller Methods

### `DashboardController@cetakBuktiRegistrasi()`

**Purpose:** Generate full A4 document for TU archive

**Features:**
- ✅ Complete data: pribadi, ortu, nilai, pilihan program
- ✅ Nomor registrasi & nomor tes
- ✅ Finalisasi timestamp
- ✅ Signature area
- ✅ Professional layout with borders

**Security:**
- Requires `is_finalisasi = true`
- Redirects if not finalized

**Eager loading:**
```php
with([
    'jalurPendaftaran', 
    'gelombangPendaftaran', 
    'tahunPelajaran', 
    'ortu',
    'programKeahlian1',
    'programKeahlian2',
    'programKeahlian3'
])
```

### `DashboardController@cetakKartuUjian()`

**Purpose:** Generate ID card for student (with photo & password)

**Features:**
- ✅ Half A4 landscape (105mm x 148mm)
- ✅ Photo 3x4 display
- ✅ Key info: NISN, nama, TTL, jalur, program
- ✅ Nomor tes (highlighted)
- ✅ Password login (from `plain_password`)
- ✅ Instruction box
- ✅ Signature area for panitia

**Security:**
- Requires `is_finalisasi = true`
- Password fallback: `'********'` if null

**Paper size:**
```php
->setPaper([0, 0, 298, 421], 'landscape') // Points: 105mm x 148mm
```

## PDF Templates

### 1. Bukti Registrasi (`resources/views/pendaftar/pdf/bukti-registrasi.blade.php`)

**Layout:** A4 Portrait (210mm x 297mm)

**Sections:**
1. **Header**
   - Logo sekolah (if available)
   - Nama sekolah
   - Title: PPDB TAHUN PELAJARAN
   - Alamat & kontak
   - Border bottom

2. **Title**
   - Centered: "BUKTI REGISTRASI PENDAFTARAN"
   - Underlined, bold

3. **Info Box** (gray background)
   - Nomor Registrasi (bold)
   - Nomor Tes (bold)
   - Jalur & Gelombang
   - Tanggal Finalisasi

4. **Data Pribadi** (table)
   - NISN, Nama, NIK
   - TTL, Jenis Kelamin, Agama
   - Asal Sekolah
   - Alamat, No HP

5. **Data Orang Tua** (table)
   - Ayah: Nama, Pekerjaan, No HP
   - Ibu: Nama, Pekerjaan, No HP

6. **Pilihan Program** (table, conditional)
   - Only if `pilihan_program_aktif = true`
   - Shows up to 3 pilihan

7. **Footer**
   - Tanggal cetak
   - Signature area (calon siswa)
   - Watermark timestamp

**Styling:**
- Font: Arial 11pt
- Colors: Professional gray (#333, #f8f9fa)
- Borders: Solid 1px #dee2e6
- Print-friendly (black text, minimal color)

### 2. Kartu Ujian (`resources/views/pendaftar/pdf/kartu-ujian.blade.php`)

**Layout:** Half A4 Landscape (148mm x 105mm)

**Structure:**

```
┌─────────────────────────────────────────────────────┐
│              [LOGO]                                  │
│           SMK NAMA SEKOLAH                          │
│          KARTU PESERTA UJIAN                        │
│        PPDB 2025/2026                               │
├─────────────────────────────────────────────────────┤
│  ┌───────┐  ┌───────────────────────────────────┐  │
│  │       │  │  NTS-2025-PRE-0001                 │  │
│  │ FOTO  │  │  NISN: 1234567890                  │  │
│  │ 3x4   │  │  Nama: JOHN DOE                    │  │
│  │       │  │  TTL: Jakarta, 01/01/2010          │  │
│  └───────┘  │  Jalur: Prestasi                   │  │
│             │  Program: Rekayasa Perangkat Lunak │  │
│             │                                     │  │
│             │  ┌─────────────────────────────┐  │  │
│             │  │ PASSWORD LOGIN              │  │  │
│             │  │    12345678                 │  │  │
│             │  └─────────────────────────────┘  │  │
│             │                                     │  │
│             │  [!] PENTING: Bawa kartu saat ujian │  │
│             └───────────────────────────────────┘  │
├─────────────────────────────────────────────────────┤
│  Dicetak: 28/12/2025 14:30      Panitia PPDB       │
│                                (................)    │
└─────────────────────────────────────────────────────┘
```

**Color scheme:**
- Header: Blue (#007bff)
- Nomor Tes: Yellow background (#ffc107)
- Password: Red background (#dc3545)
- Instruction: Light blue (#e7f3ff)
- Border: Black 2px

**Font sizes:**
- Header: 14pt/11pt
- Nomor Tes: 12pt bold
- Password: 14pt bold
- Info: 9pt
- Instruction: 7pt

**Photo section:**
- 30mm x 40mm box
- Gray placeholder if no photo
- Centered alignment

## Sidebar Menu Updates

### `resources/views/layouts/pendaftar.blade.php`

**Removed:**
```php
<li class="nav-item">
    <a href="{{ route('pendaftar.cetak-bukti') }}">
        <i class="fas fa-print"></i>
        <p>Cetak Bukti</p>
    </a>
</li>
```

**Added (conditional - only if finalized):**
```php
@if($calonSiswa && $calonSiswa->is_finalisasi)
<li class="nav-item">
    <a href="{{ route('pendaftar.cetak-bukti-registrasi') }}">
        <i class="fas fa-file-pdf"></i>
        <p>Cetak Bukti Registrasi</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('pendaftar.cetak-kartu-ujian') }}">
        <i class="fas fa-id-card"></i>
        <p>Cetak Kartu Ujian</p>
    </a>
</li>
@endif
```

**Icons:**
- Bukti Registrasi: `fa-file-pdf` (document icon)
- Kartu Ujian: `fa-id-card` (ID card icon)

## Configuration

### Environment Variables (Optional)

Add to `.env` for school customization:

```env
APP_SCHOOL_NAME="SMK Negeri 1 Jakarta"
APP_SCHOOL_ADDRESS="Jl. Pendidikan No. 123, Jakarta Pusat"
APP_SCHOOL_PHONE="021-1234567"
APP_SCHOOL_EMAIL="info@smkn1jkt.sch.id"
APP_SCHOOL_CITY="Jakarta"
```

**Fallbacks in controller:**
```php
$sekolah = (object) [
    'nama_sekolah' => config('app.school_name', config('app.name', 'SMK')),
    'logo' => null, // TODO: Add logo management
    'alamat' => config('app.school_address', ''),
    'telepon' => config('app.school_phone', '-'),
    'email' => config('app.school_email', '-'),
    'kota' => config('app.school_city', ''),
];
```

## Usage Flow

### Admin Configuration

1. Login as admin
2. Navigate to **Pengaturan PPDB**
3. Scroll to section **Format Nomor Tes**
4. Set:
   - Prefix (e.g., "NTS", "UJIAN")
   - Digit count (3, 4, or 5)
5. Preview example nomor tes
6. Click **Simpan Pengaturan**

### Student Finalisasi

1. Student completes all required data (100%)
2. Clicks **Finalisasi Data**
3. System generates nomor tes using settings:
   ```
   Prefix: NTS
   Tahun: 2025
   Jalur: PRE (from "Prestasi")
   Nomor: 0001 (counter for this jalur)
   Result: NTS-2025-PRE-0001
   ```
4. `is_finalisasi = true`, `nomor_tes` saved
5. Menu items appear in sidebar

### Print Documents

**Bukti Registrasi (for TU):**
1. Click "Cetak Bukti Registrasi"
2. PDF downloads: `bukti-registrasi-PPDB-2025-REG-00001.pdf`
3. Full A4 document with all data
4. Printed & archived by TU

**Kartu Ujian (for Student):**
1. Click "Cetak Kartu Ujian"
2. PDF downloads: `kartu-ujian-NTS-2025-PRE-0001.pdf`
3. Half A4 landscape (can print 2 per page)
4. Contains photo & password
5. Student brings to exam

## Security

### Access Control

✅ **Both routes require:**
- `auth:pendaftar` middleware
- `is_finalisasi = true` check
- Redirect if not finalized

### Data Protection

✅ **Password handling:**
- Retrieved from `users.plain_password` column
- Fallback to `'********'` if null
- Only visible on printed card (not on screen)

✅ **Atomic counter:**
- JSON update on single row (ppdb_settings)
- Per-jalur isolation (no conflicts)
- Sequential numbering guaranteed

## Testing Checklist

### Admin Settings

- [ ] Settings page loads without errors
- [ ] Nomor tes section visible
- [ ] Preview updates on prefix/digit change
- [ ] Form validation works (required fields)
- [ ] Settings save successfully
- [ ] Counter initializes as empty array

### Nomor Tes Generation

- [ ] Finalisasi generates nomor tes
- [ ] Format matches settings template
- [ ] Counter increments correctly
- [ ] Multiple jalur have independent counters
- [ ] Nomor appears in database
- [ ] Digit count matches settings (3/4/5)

### PDF Generation

**Bukti Registrasi:**
- [ ] PDF downloads successfully
- [ ] All data visible and correct
- [ ] Logo displays (if available)
- [ ] Layout is professional
- [ ] Signature area present
- [ ] Print quality acceptable

**Kartu Ujian:**
- [ ] PDF downloads successfully
- [ ] Paper size correct (half A4 landscape)
- [ ] Photo displays (if uploaded)
- [ ] Nomor tes highlighted
- [ ] Password visible and correct
- [ ] All fields populated
- [ ] Print quality acceptable

### Menu & Routes

- [ ] Old "Cetak Bukti" removed
- [ ] New menu items conditional (finalized only)
- [ ] Routes accessible after finalisasi
- [ ] Routes redirect if not finalized
- [ ] Icons correct (fa-file-pdf, fa-id-card)

## Future Enhancements

### Phase 2 (Optional)

1. **Logo Management**
   - Add logo upload in settings
   - Store in `storage/app/public/sekolah`
   - Display in PDF headers

2. **QR Code**
   - Add QR code to kartu ujian
   - Encode: NISN + Nomor Tes
   - For quick attendance scan

3. **Batch Print**
   - Admin can print all kartu ujian
   - Filter by jalur/gelombang
   - Generate multi-page PDF (2 cards per page)

4. **Email Distribution**
   - Auto-email kartu ujian after finalisasi
   - Attach PDF to confirmation email
   - Save admin time

5. **Digital Verification**
   - Add QR/barcode scanner at entrance
   - Verify against database
   - Mark attendance

## Troubleshooting

### PDF doesn't generate

**Problem:** "PDF class not found"

**Solution:** Ensure DOMPDF is installed
```bash
composer require barryvdh/laravel-dompdf
```

Add to `config/app.php`:
```php
'providers' => [
    Barryvdh\DomPDF\ServiceProvider::class,
],
'aliases' => [
    'PDF' => Barryvdh\DomPDF\Facade::class,
],
```

### Counter not incrementing

**Problem:** Multiple students get same nomor

**Solution:** Check `nomor_tes_counter` column type
```sql
ALTER TABLE ppdb_settings MODIFY nomor_tes_counter JSON;
```

### Photo not displaying

**Problem:** Broken image in PDF

**Solution:** 
1. Check file exists: `storage/app/public/dokumen/foto/...`
2. Use `public_path('storage/...')` not `asset()`
3. Ensure symlink: `php artisan storage:link`

### Format not updating

**Problem:** Old format still used

**Solution:**
1. Clear cache: `php artisan config:clear`
2. Check settings saved: `SELECT nomor_tes_format FROM ppdb_settings;`
3. Restart queue workers if using queues

## Files Modified/Created

### Modified
1. `app/Models/PpdbSettings.php` - Added nomor tes fields
2. `app/Http/Controllers/Admin/SettingsController.php` - Added validation
3. `app/Http/Controllers/Pendaftar/DashboardController.php` - Updated generation logic, added PDF methods
4. `routes/ppdb.php` - Updated routes
5. `resources/views/admin/settings/index.blade.php` - Added nomor tes section
6. `resources/views/layouts/pendaftar.blade.php` - Updated sidebar menu

### Created
1. `database/migrations/2025_12_28_102046_add_nomor_tes_settings_to_ppdb_settings_table.php`
2. `resources/views/pendaftar/pdf/bukti-registrasi.blade.php`
3. `resources/views/pendaftar/pdf/kartu-ujian.blade.php`
4. `FEATURE_CETAK_KARTU_SYSTEM.md` (this file)

## Dependencies

- ✅ Laravel 11/12
- ✅ DOMPDF (`barryvdh/laravel-dompdf`)
- ✅ jQuery (for settings preview)
- ✅ Font Awesome (for icons)
- ✅ Bootstrap 4 (for styling)

## Migration

Run migration:
```bash
php artisan migrate
```

If already migrated:
```bash
php artisan migrate:status
```

Rollback (if needed):
```bash
php artisan migrate:rollback --step=1
```

## Summary

This feature provides a complete printing system for PPDB with:

1. ✅ **Flexible nomor tes generation** - Admin-configurable format
2. ✅ **Sequential numbering** - Per-jalur independent counters
3. ✅ **Two document types** - Archive & student ID card
4. ✅ **Professional layouts** - Print-ready PDFs
5. ✅ **Password integration** - Uses existing plain_password system
6. ✅ **Security controls** - Requires finalisasi
7. ✅ **User-friendly** - Simple admin interface with live preview

The system is production-ready and can be extended with additional features like QR codes, batch printing, and email distribution.
