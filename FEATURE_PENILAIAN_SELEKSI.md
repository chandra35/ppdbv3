# FEATURE: Penilaian Seleksi (Selection Assessment)

## Ringkasan

Fitur Penilaian Seleksi memungkinkan penguji untuk melakukan input nilai ujian seleksi calon siswa dalam sistem PPDB. Fitur ini terintegrasi dengan Cetak Ruang Ujian untuk mendistribusikan peserta ke ruangan dan menugaskan penguji.

## Komponen Penilaian

| Komponen | Kode | Bobot Default |
|----------|------|---------------|
| Wawancara | `wawancara` | 25% |
| Baca Al-Qur'an | `baca_quran` | 25% |
| Tulis Al-Qur'an | `tulis_quran` | 25% |
| Hafalan Juz | `hafalan` | 25% |

> Bobot dapat dikonfigurasi melalui menu Admin > Seleksi > Bobot Nilai

## Alur Kerja (Workflow)

```
┌─────────────────────┐
│  Cetak Ruang Ujian  │
│  (Preview Distribusi)│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Simpan & Kunci      │
│ Distribusi          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│   Sesi Ujian        │
│   (Status: Locked)  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Assign Penguji     │
│  (per Ruangan)      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Mulai Sesi Ujian   │
│  (Status: In Progress)│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Penguji Input Nilai│
│  (Draft → Submit)   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Admin Verifikasi   │
│  Nilai              │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Selesaikan Sesi    │
│  (Status: Completed)│
└─────────────────────┘
```

## Struktur Database

### Tabel Baru

1. **`sesi_ujian`** - Master sesi ujian
   - `id` (UUID, PK)
   - `tahun_pelajaran_id` (FK)
   - `jalur_id` (FK, nullable)
   - `gelombang_id` (FK, nullable)
   - `nama_sesi`
   - `tanggal_ujian`
   - `waktu_mulai`, `waktu_selesai`
   - `kapasitas_per_ruang`
   - `status` (draft, locked, in_progress, completed)

2. **`ruang_ujian`** - Ruangan per sesi
   - `id` (UUID, PK)
   - `sesi_ujian_id` (FK)
   - `nama_ruang`
   - `kapasitas`
   - `nomor_urut`

3. **`peserta_ruang`** - Peserta per ruangan
   - `id` (UUID, PK)
   - `ruang_ujian_id` (FK)
   - `calon_siswa_id` (FK)
   - `nomor_urut`
   - `kehadiran`, `waktu_hadir`

4. **`penguji_ruang`** - Penugasan penguji
   - `id` (UUID, PK)
   - `sesi_ujian_id` (FK)
   - `ruang_ujian_id` (FK)
   - `user_id` (FK)
   - `is_ketua`
   - `is_active`

5. **`nilai_seleksi`** - Nilai peserta
   - `id` (UUID, PK)
   - `sesi_ujian_id` (FK)
   - `ruang_ujian_id` (FK)
   - `calon_siswa_id` (FK)
   - `penguji_id` (FK)
   - `nilai_wawancara`, `nilai_baca_quran`, `nilai_tulis_quran`, `nilai_hafalan`
   - `jumlah_juz_hafalan`
   - `total_nilai`
   - `status` (draft, submitted, verified)
   - `catatan_penguji`
   - `verified_at`, `verified_by`

6. **`bobot_nilai_seleksi`** - Konfigurasi bobot
   - `id` (UUID, PK)
   - `tahun_pelajaran_id` (FK)
   - `kode_komponen`
   - `nama_komponen`
   - `bobot` (percentage)
   - `urutan`
   - `is_active`

## File yang Dibuat

### Migrations
```
database/migrations/
├── 2026_01_15_151652_create_sesi_ujian_table.php
├── 2026_01_15_151657_create_ruang_ujian_table.php
├── 2026_01_15_151701_create_peserta_ruang_table.php
├── 2026_01_15_151705_create_penguji_ruang_table.php
├── 2026_01_15_151711_create_nilai_seleksi_table.php
└── 2026_01_15_151715_create_bobot_nilai_seleksi_table.php
```

### Models
```
app/Models/
├── SesiUjian.php
├── RuangUjian.php
├── PesertaRuang.php
├── PengujiRuang.php
├── NilaiSeleksi.php
└── BobotNilaiSeleksi.php
```

### Controllers
```
app/Http/Controllers/
├── Admin/
│   ├── SesiUjianController.php
│   └── NilaiSeleksiController.php
└── Penguji/
    └── DashboardController.php
```

### Views
```
resources/views/
├── admin/
│   ├── sesi-ujian/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   └── nilai-seleksi/
│       ├── index.blade.php
│       ├── show.blade.php
│       ├── bobot.blade.php
│       └── rekap.blade.php
└── penguji/
    ├── dashboard.blade.php
    ├── ruangan.blade.php
    └── input-nilai.blade.php
```

## Routes

### Admin Routes
```php
// Sesi Ujian
Route::prefix('sesi-ujian')->name('sesi-ujian.')->group(function () {
    Route::get('/', [SesiUjianController::class, 'index'])->name('index');
    Route::get('/{sesiUjian}', [SesiUjianController::class, 'show'])->name('show');
    Route::post('/{sesiUjian}/update-status', [SesiUjianController::class, 'updateStatus'])->name('update-status');
    Route::post('/{sesiUjian}/assign-penguji', [SesiUjianController::class, 'assignPenguji'])->name('assign-penguji');
    Route::get('/{sesiUjian}/ruangan/{ruangUjian}/penguji', [SesiUjianController::class, 'getPengujiRuangan'])->name('get-penguji');
    Route::delete('/{sesiUjian}', [SesiUjianController::class, 'destroy'])->name('destroy');
    Route::get('/{sesiUjian}/print-daftar-hadir', [SesiUjianController::class, 'printDaftarHadir'])->name('print-daftar-hadir');
});

// Nilai Seleksi
Route::prefix('nilai-seleksi')->name('nilai-seleksi.')->group(function () {
    Route::get('/', [NilaiSeleksiController::class, 'index'])->name('index');
    Route::get('/rekap', [NilaiSeleksiController::class, 'rekap'])->name('rekap');
    Route::get('/bobot', [NilaiSeleksiController::class, 'bobotIndex'])->name('bobot');
    Route::post('/bobot', [NilaiSeleksiController::class, 'bobotUpdate'])->name('bobot.update');
    Route::get('/{sesiUjian}', [NilaiSeleksiController::class, 'show'])->name('show');
    Route::post('/{sesiUjian}/verify/{nilaiSeleksi}', [NilaiSeleksiController::class, 'verify'])->name('verify');
    Route::post('/{sesiUjian}/bulk-verify', [NilaiSeleksiController::class, 'bulkVerify'])->name('bulk-verify');
});
```

### Penguji Routes
```php
Route::middleware(['auth'])->prefix('penguji')->name('penguji.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/ruangan/{ruangUjian}', [DashboardController::class, 'ruangan'])->name('ruangan');
    Route::get('/ruangan/{ruangUjian}/peserta/{pesertaRuang}', [DashboardController::class, 'inputNilai'])->name('input-nilai');
    Route::post('/ruangan/{ruangUjian}/peserta/{pesertaRuang}', [DashboardController::class, 'saveNilai'])->name('save-nilai');
});
```

## Menu Sidebar

Menu ditambahkan di `config/adminlte.php`:

```php
[
    'text' => 'Seleksi',
    'icon' => 'fas fa-fw fa-clipboard-list',
    'can' => 'admin',
    'submenu' => [
        [
            'text' => 'Sesi Ujian',
            'route' => 'admin.sesi-ujian.index',
            'icon' => 'fas fa-fw fa-calendar-check',
        ],
        [
            'text' => 'Nilai Seleksi',
            'route' => 'admin.nilai-seleksi.index',
            'icon' => 'fas fa-fw fa-chart-bar',
        ],
        [
            'text' => 'Bobot Nilai',
            'route' => 'admin.nilai-seleksi.bobot',
            'icon' => 'fas fa-fw fa-balance-scale',
        ],
        [
            'text' => 'Rekap Nilai',
            'route' => 'admin.nilai-seleksi.rekap',
            'icon' => 'fas fa-fw fa-file-excel',
        ],
    ],
],
```

## Cara Penggunaan

### 1. Membuat Sesi Ujian

1. Buka menu **Pendaftar > Cetak Ruang Ujian**
2. Atur filter (Jalur, Gelombang) dan jumlah peserta per ruang
3. Klik **Preview Pembagian Ruang**
4. Jika sudah sesuai, klik **Simpan & Kunci Distribusi**
5. Isi nama sesi dan jadwal ujian
6. Klik **Simpan & Kunci**

### 2. Menugaskan Penguji

1. Buka menu **Seleksi > Sesi Ujian**
2. Pilih sesi yang baru dibuat
3. Pada setiap ruangan, klik **Kelola Penguji**
4. Pilih penguji menggunakan Select2
5. Tentukan ketua penguji
6. Klik **Simpan**

### 3. Memulai Sesi Ujian

1. Pada detail sesi, klik **Mulai Sesi Ujian**
2. Status sesi berubah menjadi "Berlangsung"
3. Penguji dapat mulai melakukan penilaian

### 4. Input Nilai (Penguji)

1. Penguji login ke sistem
2. Akses `/penguji` untuk melihat dashboard
3. Pilih ruangan yang ditugaskan
4. Pilih peserta untuk dinilai
5. Input nilai untuk setiap komponen
6. Klik **Simpan Draft** (bisa diubah) atau **Submit** (final)

### 5. Verifikasi Nilai (Admin)

1. Buka menu **Seleksi > Nilai Seleksi**
2. Pilih sesi ujian
3. Verifikasi nilai yang sudah disubmit
4. Atau klik **Verifikasi Semua** untuk bulk verify

### 6. Export Rekap Nilai

1. Buka menu **Seleksi > Rekap Nilai**
2. Filter berdasarkan tahun, jalur, status
3. Klik **Export Excel** atau **Print**

## Rumus Perhitungan Nilai

```
Total Nilai = Σ (Nilai Komponen × Bobot Komponen / 100)
```

Contoh:
- Wawancara: 80 × 25% = 20
- Baca Qur'an: 85 × 25% = 21.25
- Tulis Qur'an: 75 × 25% = 18.75
- Hafalan: 90 × 25% = 22.5
- **Total = 82.5**

## Status Nilai

| Status | Deskripsi |
|--------|-----------|
| `draft` | Nilai disimpan sementara, masih bisa diubah |
| `submitted` | Nilai sudah final dari penguji, menunggu verifikasi |
| `verified` | Nilai sudah diverifikasi admin |

## Fitur Select2

Penugasan penguji menggunakan Select2 untuk UI yang interaktif:

```javascript
$('#selectPenguji').select2({
    theme: 'bootstrap4',
    placeholder: 'Cari dan pilih penguji...',
    allowClear: true,
    multiple: true
});
```

## Catatan Implementasi

1. Semua tabel menggunakan UUID sebagai primary key
2. Foreign key mengikuti konvensi Laravel (nama_tabel_id)
3. Soft delete tidak diimplementasikan untuk tabel nilai
4. Bobot nilai di-seed otomatis saat migrasi dengan default 25% per komponen
5. Penguji yang bisa ditugaskan adalah user dengan role tertentu (configurable)

## Screenshot

### Dashboard Sesi Ujian
![Sesi Ujian](screenshots/sesi-ujian-index.png)

### Detail Sesi dengan Assign Penguji
![Assign Penguji](screenshots/sesi-ujian-show.png)

### Portal Penguji
![Portal Penguji](screenshots/penguji-dashboard.png)

### Input Nilai
![Input Nilai](screenshots/penguji-input-nilai.png)

### Rekap Nilai
![Rekap Nilai](screenshots/nilai-seleksi-rekap.png)

---

## Changelog

- **v1.0.0** (2025-01-15)
  - Initial implementation
  - 6 database tables
  - 6 models
  - 3 controllers
  - 9 views
  - Select2 integration for penguji assignment
  - Integration with Cetak Ruang Ujian
