# Fitur Penjadwalan Ujian CBT & Wawancara Paralel

## Deskripsi Fitur

Fitur ini memungkinkan admin untuk menjadwalkan ujian CBT dan Wawancara secara paralel dengan sistem rotasi grup. Peserta dibagi menjadi dua grup:
- **Grup A**: Mengikuti CBT terlebih dahulu, kemudian Wawancara
- **Grup B**: Mengikuti Wawancara terlebih dahulu, kemudian CBT

Dengan sistem ini, ruang CBT dan Wawancara dapat digunakan secara maksimal secara bersamaan.

## Alur Kerja

### 1. Konfigurasi
Admin mengatur parameter penjadwalan:
- **CBT**: Jumlah ruang, kapasitas per ruang, durasi, prefix nama ruang
- **Wawancara**: Jumlah ruang, kapasitas per ruang, durasi, prefix nama ruang
- **Waktu**: Tanggal ujian, jam mulai, jeda antar sesi
- **Filter**: Jalur, Gelombang (opsional)

### 2. Preview
Sistem menghitung:
- Kapasitas paralel = min(kapasitas CBT, kapasitas Wawancara)
- Jumlah gelombang = ceil(total peserta / (kapasitas paralel × 2))
- Jumlah sesi = gelombang × 2 (untuk rotasi)

### 3. Simpan & Kunci
Setelah preview, admin dapat menyimpan jadwal. Jadwal yang dikunci tidak dapat diubah.

### 4. Cetak Output
- **Kartu Peserta**: Kartu individual dengan jadwal CBT & Wawancara
- **Daftar Hadir**: Per ruang untuk pengawas
- **Nama Ruang**: Label untuk ditempel di pintu ruang
- **Jadwal Sesi**: Overview seluruh sesi
- **Export Excel**: Data lengkap dalam format CSV

## Database Schema

### Tabel: jadwal_ujian
Menyimpan konfigurasi jadwal utama.

```sql
CREATE TABLE jadwal_ujian (
    id CHAR(36) PRIMARY KEY,
    tahun_pelajaran_id CHAR(36) NOT NULL,
    jalur_pendaftaran_id CHAR(36) NULL,
    gelombang_pendaftaran_id CHAR(36) NULL,
    tanggal_ujian DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jeda_sesi INT DEFAULT 30,
    jumlah_ruang_cbt INT NOT NULL,
    kapasitas_cbt INT NOT NULL,
    durasi_cbt INT NOT NULL,
    prefix_ruang_cbt VARCHAR(50) DEFAULT 'Ruang CBT',
    jumlah_ruang_wawancara INT NOT NULL,
    kapasitas_wawancara INT NOT NULL,
    durasi_wawancara INT NOT NULL,
    prefix_ruang_wawancara VARCHAR(50) DEFAULT 'Ruang Wawancara',
    total_peserta INT DEFAULT 0,
    total_sesi INT DEFAULT 0,
    estimasi_selesai TIME NULL,
    status ENUM('draft', 'preview', 'locked') DEFAULT 'draft',
    generated_at TIMESTAMP NULL,
    generated_by CHAR(36) NULL,
    locked_at TIMESTAMP NULL,
    locked_by CHAR(36) NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabel: jadwal_peserta
Menyimpan jadwal per peserta.

```sql
CREATE TABLE jadwal_peserta (
    id CHAR(36) PRIMARY KEY,
    jadwal_ujian_id CHAR(36) NOT NULL,
    calon_siswa_id CHAR(36) NOT NULL,
    sesi_cbt_id CHAR(36) NOT NULL,
    ruang_cbt_id CHAR(36) NOT NULL,
    nomor_urut_cbt INT NULL,
    sesi_wawancara_id CHAR(36) NOT NULL,
    ruang_wawancara_id CHAR(36) NOT NULL,
    nomor_urut_wawancara INT NULL,
    grup ENUM('A', 'B') NOT NULL,
    nomor_gelombang INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Modifikasi: sesi_ujian
Kolom tambahan:

```sql
ALTER TABLE sesi_ujian ADD COLUMN jadwal_ujian_id CHAR(36) NULL;
ALTER TABLE sesi_ujian ADD COLUMN jenis_ujian ENUM('cbt', 'wawancara', 'mixed') DEFAULT 'mixed';
ALTER TABLE sesi_ujian ADD COLUMN nomor_sesi INT NULL;
ALTER TABLE sesi_ujian ADD COLUMN durasi INT NULL;
```

## Routes

```php
Route::prefix('penjadwalan-ujian')->name('penjadwalan-ujian.')->group(function () {
    Route::get('/', [PenjadwalanUjianController::class, 'index'])->name('index');
    Route::post('/preview', [PenjadwalanUjianController::class, 'preview'])->name('preview');
    Route::post('/store', [PenjadwalanUjianController::class, 'store'])->name('store');
    Route::get('/list', [PenjadwalanUjianController::class, 'list'])->name('list');
    Route::get('/{jadwalUjian}', [PenjadwalanUjianController::class, 'show'])->name('show');
    Route::delete('/{jadwalUjian}', [PenjadwalanUjianController::class, 'destroy'])->name('destroy');
    
    // Print routes
    Route::get('/{jadwalUjian}/print/kartu-peserta', [PenjadwalanUjianController::class, 'printKartuPeserta'])->name('print.kartu-peserta');
    Route::get('/{jadwalUjian}/print/daftar-hadir', [PenjadwalanUjianController::class, 'printDaftarHadir'])->name('print.daftar-hadir');
    Route::get('/{jadwalUjian}/print/nama-ruang', [PenjadwalanUjianController::class, 'printNamaRuang'])->name('print.nama-ruang');
    Route::get('/{jadwalUjian}/print/jadwal-sesi', [PenjadwalanUjianController::class, 'printJadwalSesi'])->name('print.jadwal-sesi');
    Route::get('/{jadwalUjian}/export/excel', [PenjadwalanUjianController::class, 'exportExcel'])->name('export.excel');
});
```

## Algoritma Penjadwalan

```
1. Ambil peserta yang eligible (finalisasi = true, nomor_tes tidak null)
2. Urutkan berdasarkan nomor_tes
3. Hitung kapasitas paralel = min(kapasitas_cbt_total, kapasitas_wawancara_total)
4. Bagi peserta ke dalam gelombang (masing-masing gelombang = kapasitas_paralel × 2)
5. Untuk setiap gelombang:
   a. Split menjadi Grup A (separuh pertama) dan Grup B (separuh kedua)
   b. Sesi ganjil: Grup A → CBT, Grup B → Wawancara
   c. Sesi genap: Grup A → Wawancara, Grup B → CBT (swap)
6. Assign ruang berdasarkan nomor urut dalam grup
7. Simpan ke database
```

## Contoh Perhitungan

**Konfigurasi:**
- 200 peserta
- CBT: 3 ruang × 30 kapasitas = 90/sesi
- Wawancara: 4 ruang × 20 kapasitas = 80/sesi

**Perhitungan:**
- Kapasitas paralel = min(90, 80) = 80
- Peserta per gelombang = 80 × 2 = 160
- Jumlah gelombang = ceil(200/160) = 2
- Jumlah sesi = 2 × 2 = 4

**Hasil:**
- Gelombang 1 (160 peserta): Sesi 1 & 2
- Gelombang 2 (40 peserta): Sesi 3 & 4

## File yang Dibuat

### Migrations
- `database/migrations/2026_02_05_213127_add_jenis_ujian_to_sesi_ujian_table.php`
- `database/migrations/2026_02_05_213202_create_jadwal_ujian_table.php`
- `database/migrations/2026_02_05_213223_create_jadwal_peserta_table.php`

### Models
- `app/Models/JadwalUjian.php`
- `app/Models/JadwalPeserta.php`

### Controller
- `app/Http/Controllers/Admin/PenjadwalanUjianController.php`

### Views
- `resources/views/admin/penjadwalan-ujian/index.blade.php`
- `resources/views/admin/penjadwalan-ujian/show.blade.php`
- `resources/views/admin/penjadwalan-ujian/list.blade.php`
- `resources/views/admin/penjadwalan-ujian/print/kartu-peserta.blade.php`
- `resources/views/admin/penjadwalan-ujian/print/daftar-hadir.blade.php`
- `resources/views/admin/penjadwalan-ujian/print/nama-ruang.blade.php`
- `resources/views/admin/penjadwalan-ujian/print/jadwal-sesi.blade.php`

## Menu Sidebar

Fitur ini ditambahkan ke menu **Seleksi** di sidebar dengan icon `fas fa-calendar-alt`.

## Peringatan Kapasitas

Sistem akan menampilkan warning jika:
- Kapasitas CBT > Kapasitas Wawancara (beberapa ruang CBT tidak terpakai penuh)
- Kapasitas Wawancara > Kapasitas CBT (beberapa ruang Wawancara tidak terpakai penuh)

## Kriteria Peserta

Peserta yang masuk ke jadwal harus:
1. `is_finalisasi = true`
2. `nomor_tes IS NOT NULL`
3. Sesuai filter tahun pelajaran, jalur, dan gelombang (jika dipilih)

## Status Jadwal

- **Draft**: Jadwal belum lengkap
- **Preview**: Jadwal sudah di-generate tapi belum disimpan
- **Locked**: Jadwal sudah disimpan dan tidak dapat diubah
