# Fitur Jalur Pendaftaran PPDB (v2)

## Perubahan Utama dari v1

1. **Pendaftar tidak memilih jalur** - Sistem otomatis menentukan jalur aktif
2. **Hanya 1 jalur aktif** - Tidak bisa mengaktifkan jalur lain jika ada yang aktif
3. **Alur status lebih jelas** - Draft → Open → Closed/Finished
4. **Hapus periode PPDB di Settings** - Diganti dengan fitur Jalur Pendaftaran

## Konsep Utama

### Jalur = Periode Pendaftaran

Jalur Pendaftaran berfungsi sebagai periode/gelombang pendaftaran. Contoh penggunaan:
- **Jalur Reguler Gel. 1** (Juni)
- **Jalur Reguler Gel. 2** (Juli)
- **Jalur Prestasi** (Mei)

### Constraint: 1 Jalur Aktif

Dalam satu tahun ajaran, hanya **1 jalur** yang bisa status "Open/Dibuka". Ini memastikan:
- Pendaftar tidak bingung memilih jalur
- Admin punya kontrol penuh atas periode pendaftaran
- Data pendaftar jelas masuk ke jalur mana

## Alur Status

```
┌─────────┐      ┌──────┐      ┌────────┐      ┌──────────┐
│  DRAFT  │ ───► │ OPEN │ ───► │ CLOSED │ ───► │ FINISHED │
└─────────┘      └──────┘      └────────┘      └──────────┘
                    │              │
                    │              │ (bisa buka lagi)
                    │              ▼
                    └─────────────►│
```

- **Draft**: Jalur baru dibuat, belum terima pendaftar
- **Open**: Pendaftaran aktif, menerima pendaftar
- **Closed**: Ditutup sementara, bisa dibuka lagi
- **Finished**: Selesai permanen, tidak bisa dibuka lagi

## Struktur Database

### Tabel `jalur_pendaftaran`

| Field | Type | Description |
|-------|------|-------------|
| id | uuid | Primary key |
| nama | string(100) | Nama jalur |
| kode | string(20) | Kode unik jalur |
| tahun_ajaran | string(9) | Contoh: 2024/2025 |
| deskripsi | text | Deskripsi jalur |
| persyaratan | text | Persyaratan pendaftaran |
| tanggal_buka | date | Tanggal mulai (opsional) |
| tanggal_tutup | date | Tanggal selesai (opsional) |
| kuota | int | Total kuota |
| kuota_terisi | int | Jumlah yang sudah mendaftar |
| status | string | draft/open/closed/finished |
| is_active | boolean | Flag aktif |
| warna | string | Warna untuk UI |
| icon | string | Icon FontAwesome |
| tampil_di_publik | boolean | Tampil di landing page |
| urutan | int | Urutan tampilan |

### Tabel `gelombang_pendaftaran` (Opsional)

Jika membutuhkan sub-periode dalam jalur.

| Field | Type | Description |
|-------|------|-------------|
| id | uuid | Primary key |
| jalur_id | uuid | Foreign key ke jalur |
| nama | string(100) | Nama gelombang |
| kuota | int | Kuota gelombang ini |
| tanggal_dibuka | datetime | Tanggal mulai |
| tanggal_ditutup | datetime | Tanggal selesai |
| status | enum | draft/open/closed/finished |

## Routes

```php
// Jalur CRUD
Route::prefix('jalur')->name('jalur.')->group(function () {
    Route::get('/', [JalurPendaftaranController::class, 'index'])->name('index');
    Route::get('/create', [JalurPendaftaranController::class, 'create'])->name('create');
    Route::post('/', [JalurPendaftaranController::class, 'store'])->name('store');
    Route::get('/{jalur}', [JalurPendaftaranController::class, 'show'])->name('show');
    Route::get('/{jalur}/edit', [JalurPendaftaranController::class, 'edit'])->name('edit');
    Route::put('/{jalur}', [JalurPendaftaranController::class, 'update'])->name('update');
    Route::delete('/{jalur}', [JalurPendaftaranController::class, 'destroy'])->name('destroy');
    
    // Status Actions
    Route::post('/{jalur}/aktifkan', ...)->name('aktifkan');
    Route::post('/{jalur}/tutup', ...)->name('tutup');
    Route::post('/{jalur}/selesaikan', ...)->name('selesaikan');
    Route::post('/{jalur}/duplicate', ...)->name('duplicate');
});
```

## Cara Penggunaan

### 1. Buat Jalur Baru

```
Admin → Jalur Pendaftaran → Tambah Jalur
```

Isi:
- Nama: "Jalur Reguler Gel. 1"
- Kode: "REG-GEL1"
- Tahun Ajaran: 2025/2026
- Kuota: 100
- Tanggal Buka: 2025-06-01
- Tanggal Tutup: 2025-06-30

### 2. Buka Pendaftaran

Klik tombol **"Buka Pendaftaran"** pada jalur yang sudah dibuat.

### 3. Tutup / Selesaikan

- **Tutup Sementara**: Jika perlu pause pendaftaran
- **Selesaikan**: Jika gelombang sudah selesai

### 4. Buka Gelombang Berikutnya

1. Buat jalur baru: "Jalur Reguler Gel. 2"
2. Klik "Buka Pendaftaran"

## Perubahan di Halaman Settings

**Dihapus:**
- Card "Periode PPDB" (tanggal_dibuka, tanggal_ditutup, status_pendaftaran, kuota)

**Ditambahkan:**
- Link ke halaman Jalur Pendaftaran untuk mengatur periode

## Model Methods

```php
// Get jalur yang aktif
$jalur = JalurPendaftaran::getAktif();

// Cek bisa terima pendaftar
if ($jalur && $jalur->bisaMenerimaPendaftar()) {
    // Proses pendaftaran
}

// Sisa kuota
$sisa = $jalur->sisaKuota();

// Persentase kuota
$persen = $jalur->persentaseKuota();
```

## Untuk Pendaftar

Pendaftar tidak perlu memilih jalur. Sistem akan:
1. Mengecek apakah ada jalur yang sedang dibuka
2. Jika tidak ada → Redirect dengan pesan "Pendaftaran belum dibuka"
3. Jika ada → Tampilkan info jalur dan form pendaftaran

```php
// Di RegisterController
public function step1()
{
    $validasi = $this->validateRegistrasiDibuka();
    
    if (!$validasi['status']) {
        return redirect()->route('ppdb.landing')
            ->with('warning', $validasi['message']);
    }
    
    $jalurAktif = $validasi['jalur'];
    return view('ppdb.step1', compact('jalurAktif'));
}
```
|--------|-------------|
| `draft` | Gelombang masih dalam persiapan |
| `upcoming` | Akan dibuka dalam waktu dekat |
| `open` | Sedang dibuka untuk pendaftaran |
| `closed` | Pendaftaran ditutup sementara |
| `finished` | Gelombang selesai |

## Fitur Utama

### 1. Manajemen Jalur
- CRUD jalur pendaftaran
- Setting kuota per jalur
- Kustomisasi warna dan icon
- Aktivasi/deaktivasi jalur

### 2. Manajemen Gelombang
- CRUD gelombang dalam setiap jalur
- Setting tanggal buka/tutup
- Setting kuota per gelombang
- Opsi tampilkan/sembunyikan nama gelombang ke publik
- Auto-generate nomor registrasi dengan prefix

### 3. Workflow Status Gelombang
- **Buka Gelombang**: Draft/Upcoming → Open
- **Tutup Gelombang**: Open → Closed
- **Selesaikan Gelombang**: Any → Finished

### 4. Fitur Tampilkan Nama
Opsi `tampilkan_nama` pada gelombang memungkinkan:
- `true`: Nama gelombang ditampilkan ke calon pendaftar (misal: "Gelombang 1")
- `false`: Nama gelombang disembunyikan, hanya tampil "Pendaftaran Dibuka"

## Cara Penggunaan

### 1. Membuat Jalur Pendaftaran

1. Login sebagai admin
2. Buka menu **Settings** → **Jalur Pendaftaran**
3. Klik tombol **Tambah Jalur**
4. Isi form:
   - Nama jalur (misal: Jalur Prestasi)
   - Kode jalur (misal: JP)
   - Deskripsi
   - Persyaratan
   - Kuota
   - Tahun ajaran
   - Warna dan icon
5. Simpan

### 2. Menambah Gelombang dalam Jalur

1. Buka detail jalur
2. Pada panel **Daftar Gelombang**, klik **Tambah Gelombang**
3. Isi form:
   - Nama gelombang
   - Kode
   - Kuota
   - Tanggal dibuka & ditutup
   - Prefix nomor registrasi
   - Centang "Tampilkan nama gelombang" jika ingin ditampilkan ke publik
4. Simpan

### 3. Membuka Gelombang Pendaftaran

1. Buka detail jalur
2. Pada gelombang yang ingin dibuka, klik tombol **Buka**
3. Status akan berubah menjadi `open`
4. Pendaftar dapat mulai mendaftar

### 4. Membuka Gelombang 2

Jika Gelombang 1 sudah selesai dan masih ada kuota:

1. Buka detail jalur
2. Klik tombol **Selesaikan** pada Gelombang 1
3. Tambah gelombang baru **Gelombang 2**
4. Set tanggal dan kuota
5. Klik **Buka** untuk membuka pendaftaran

## Integrasi dengan Pendaftaran

### Landing Page
- Menampilkan semua jalur aktif
- Menampilkan gelombang yang sedang dibuka
- Menampilkan kuota tersisa

### Form Pendaftaran Step 1
- Pendaftar memilih jalur dan gelombang
- Validasi kuota dan periode aktif
- Data jalur dan gelombang disimpan ke calon_siswa

### Data Pendaftar
- Setiap pendaftar terhubung dengan:
  - `jalur_pendaftaran_id`
  - `gelombang_pendaftaran_id`
- Nomor registrasi di-generate dari prefix gelombang

## Routes

```
GET     /admin/jalur                          - Daftar jalur
GET     /admin/jalur/create                   - Form tambah jalur
POST    /admin/jalur                          - Simpan jalur baru
GET     /admin/jalur/{jalur}                  - Detail jalur + gelombang
GET     /admin/jalur/{jalur}/edit             - Form edit jalur
PUT     /admin/jalur/{jalur}                  - Update jalur
DELETE  /admin/jalur/{jalur}                  - Hapus jalur
POST    /admin/jalur/{jalur}/toggle-active    - Toggle status aktif

# Nested Gelombang
POST    /admin/jalur/{jalur}/gelombang                      - Tambah gelombang
PUT     /admin/jalur/{jalur}/gelombang/{gelombang}          - Update gelombang
DELETE  /admin/jalur/{jalur}/gelombang/{gelombang}          - Hapus gelombang
POST    /admin/jalur/{jalur}/gelombang/{gelombang}/buka     - Buka gelombang
POST    /admin/jalur/{jalur}/gelombang/{gelombang}/tutup    - Tutup gelombang
POST    /admin/jalur/{jalur}/gelombang/{gelombang}/selesaikan - Selesaikan gelombang
```

## File Terkait

### Models
- `app/Models/JalurPendaftaran.php`
- `app/Models/GelombangPendaftaran.php`
- `app/Models/CalonSiswa.php` (updated)

### Controller
- `app/Http/Controllers/Admin/JalurPendaftaranController.php`

### Views
- `resources/views/admin/jalur/index.blade.php`
- `resources/views/admin/jalur/create.blade.php`
- `resources/views/admin/jalur/show.blade.php`
- `resources/views/admin/jalur/edit.blade.php`

### Migrations
- `database/migrations/2025_12_11_110001_create_jalur_pendaftaran_table.php`
- `database/migrations/2025_12_11_023809_add_jalur_gelombang_to_calon_siswas_table.php`

## Contoh Skenario

### Skenario 1: Buka Pendaftaran Reguler dengan Gelombang

1. Buat jalur "Jalur Reguler" dengan kuota 150
2. Tambah "Gelombang 1" dengan kuota 100, buka 1-31 Jan
3. Buka Gelombang 1
4. Setelah 31 Jan atau kuota penuh:
   - Selesaikan Gelombang 1
   - Tambah "Gelombang 2" dengan kuota 50, buka 1-15 Feb
   - Buka Gelombang 2

### Skenario 2: Jalur Prestasi Tanpa Gelombang Terlihat

1. Buat jalur "Jalur Prestasi" dengan kuota 30
2. Tambah gelombang dengan `tampilkan_nama = false`
3. Pendaftar hanya melihat "Jalur Prestasi - Pendaftaran Dibuka"
4. Nama gelombang tersembunyi dari publik

## Catatan Penting

1. **Kuota Gelombang vs Kuota Jalur**: Total kuota semua gelombang tidak boleh melebihi kuota jalur
2. **Satu Gelombang Aktif per Jalur**: Sebaiknya hanya 1 gelombang berstatus `open` per jalur pada satu waktu
3. **Backup Data**: Lakukan backup database sebelum menghapus jalur/gelombang yang sudah memiliki pendaftar
