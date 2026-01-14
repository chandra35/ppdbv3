# FEATURE: Export Data Pendaftar ke Excel

## Tanggal Implementasi
2025-01-XX

## Deskripsi
Fitur untuk mengekspor data pendaftar PPDB ke format Excel (.xlsx) dengan data lengkap termasuk data diri, alamat, orang tua, dan nilai rapor.

## Package Dependencies
- `maatwebsite/excel` (^3.1)

## Files yang Dibuat/Dimodifikasi

### File Baru
1. **app/Exports/PendaftarExport.php** - Class untuk handle export Excel

### File yang Dimodifikasi
1. **routes/ppdb.php** - Menambahkan route export
2. **app/Http/Controllers/Admin/PendaftarController.php** - Menambahkan method export
3. **resources/views/admin/pendaftar/index.blade.php** - Menambahkan tombol export dan modal filter

## Cara Penggunaan

### 1. Quick Export (Dropdown Button)
Di halaman Daftar Pendaftar, klik tombol **"Export Excel"** dan pilih:
- **Semua Pendaftar** - Export semua data pendaftar
- **Peserta Ujian (Dengan Nomor Tes)** - Export hanya pendaftar yang sudah memiliki nomor tes

### 2. Export dengan Filter (Modal)
Klik **"Export dengan Filter..."** untuk membuka modal filter:
- **Tipe Data**: Semua Pendaftar / Peserta Ujian
- **Jalur Pendaftaran**: Filter berdasarkan jalur
- **Gelombang**: Filter berdasarkan gelombang

## Struktur Data Excel

### Header Kolom (92 Kolom Total)

#### Data Diri (12 kolom)
- No, Nomor Tes, No Registrasi, NISN, NIK, Nama Lengkap, Jenis Kelamin, Tempat Lahir, Tanggal Lahir, Agama, Anak Ke, Jumlah Saudara

#### Alamat Siswa (8 kolom)
- Alamat Siswa, RT, RW, Kelurahan, Kecamatan, Kabupaten, Provinsi, Kode Pos

#### Kontak & Sekolah Asal (4 kolom)
- Nomor HP, Email, NPSN Asal, Nama Sekolah Asal

#### Jalur & Gelombang (3 kolom)
- Jalur Pendaftaran, Gelombang, Pilihan Program

#### Status (4 kolom)
- Status Verifikasi, Status Admisi, Finalisasi, Tanggal Registrasi

#### Data Orang Tua - Ayah (10 kolom)
- No KK, Status Ayah, NIK Ayah, Nama Ayah, Tempat Lahir Ayah, Tanggal Lahir Ayah, Pendidikan Ayah, Pekerjaan Ayah, Penghasilan Ayah, HP Ayah

#### Data Orang Tua - Ibu (9 kolom)
- Status Ibu, NIK Ibu, Nama Ibu, Tempat Lahir Ibu, Tanggal Lahir Ibu, Pendidikan Ibu, Pekerjaan Ibu, Penghasilan Ibu, HP Ibu

#### Data Wali (8 kolom)
- Tinggal Dengan Wali, Nama Wali, Hubungan Wali, NIK Wali, Pendidikan Wali, Pekerjaan Wali, Penghasilan Wali, HP Wali

#### Alamat Orang Tua (1 kolom)
- Alamat Orang Tua

#### Nilai Rapor (20 kolom)
- Semester 1-5: MTK, IPA, IPS, Rata-rata per semester

#### Nilai Akhir (4 kolom)
- Nilai CBT, Nilai Wawancara, Nilai Akhir, Ranking

## Styling Excel
- Header dengan background biru dan text putih
- Border pada semua cell
- Auto-size untuk kolom
- Text wrap pada header

## Routes
```php
GET /admin/pendaftar/export
    - Query params:
        - type: 'all' | 'with_nomor_tes' (default: 'all')
        - jalur_id: uuid (optional)
        - gelombang_id: uuid (optional)
```

## Nama File Output
- Semua Pendaftar: `Data_Pendaftar_PPDB_{tahun_pelajaran}.xlsx`
- Peserta Ujian: `Peserta_Ujian_PPDB_{tahun_pelajaran}.xlsx`

## Catatan Teknis
1. Export menggunakan stream download untuk efisiensi memory
2. Data diformat sebelum export (tanggal, enum values, dll)
3. Null values ditampilkan sebagai string kosong
4. Gelombang filter terhubung dengan jalur (filter dinamis)

## Testing
1. Akses halaman `/admin/pendaftar`
2. Klik tombol "Export Excel"
3. Pilih tipe export atau gunakan modal filter
4. File Excel akan terdownload otomatis
