# FEATURE: Manajemen Penguji

## Ringkasan

Fitur Manajemen Penguji memungkinkan admin untuk mengelola penguji ujian seleksi PPDB dengan interface **dual-listbox** yang interaktif. GTK dapat langsung di-assign sebagai penguji dengan otomatis membuat user dan role.

## Interface Dual-Listbox

```
┌─────────────────────────────────────────────────────────────┐
│                DUAL-LISTBOX INTERFACE                       │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────┐          ┌──────────────────┐         │
│  │   GTK TERSEDIA   │   ──►    │ PENGUJI TERDAFTAR│         │
│  │                  │  ASSIGN  │                  │         │
│  │  □ Ahmad         │          │  ☑ Budi (penguji)│         │
│  │  □ Citra         │  ◄──     │  ☑ Dewi (penguji)│         │
│  │  □ Dina          │  REMOVE  │                  │         │
│  └──────────────────┘          └──────────────────┘         │
│                                                              │
│  Klik ASSIGN = GTK otomatis jadi User + Role Penguji        │
│  Klik REMOVE = Hapus role penguji (user tetap ada)          │
└─────────────────────────────────────────────────────────────┘
```

## Fitur Utama

### 1. Interface Dual-Listbox (`/admin/penguji`)

- **Kolom Kiri**: Data GTK yang belum menjadi penguji
- **Kolom Kanan**: User yang sudah menjadi penguji
- **Tombol Assign** (→): Jadikan GTK sebagai penguji
- **Tombol Remove** (←): Hapus role penguji dari user

### 2. Proses Assign Otomatis

Ketika GTK di-assign sebagai penguji:
1. Jika belum ada user → **Create user baru** dari data GTK
2. **Attach role "Penguji TES Wawancara"**
3. Set password default (bisa dikustomisasi)
4. Email diambil dari GTK

### 3. Statistik Real-time

- Total penguji terdaftar
- Penguji aktif
- Penguji yang ditugaskan saat ini
- GTK tersedia (belum jadi penguji)

### 4. Manajemen Penguji

- Reset password
- Toggle status aktif/nonaktif
- Lihat detail penguji (statistik penilaian, riwayat penugasan)
- Hapus role penguji

## Routes

```php
Route::prefix('penguji')->name('penguji.')->group(function () {
    Route::get('/', 'index');                    // Halaman utama dual-listbox
    Route::get('/available-gtk', 'getAvailableGtk'); // AJAX: GTK tersedia
    Route::post('/assign', 'assignPenguji');     // AJAX: Assign GTK sebagai penguji
    Route::post('/remove', 'removePenguji');     // AJAX: Hapus role penguji
    Route::get('/list', 'getPengujiList');       // AJAX: Daftar penguji
    Route::get('/{penguji}', 'show');            // Detail penguji
    Route::delete('/{penguji}', 'destroy');      // Hapus role
    Route::post('/{penguji}/toggle-status', 'toggleStatus');
    Route::post('/{penguji}/reset-password', 'resetPassword');
});
```

## Files

### Controller
- `app/Http/Controllers/Admin/PengujiController.php`

### Views
- `resources/views/admin/penguji/index.blade.php` - Dual-listbox + tabel
- `resources/views/admin/penguji/show.blade.php` - Detail penguji

## Cara Penggunaan

### Menambah Penguji

1. Buka `/admin/penguji`
2. Di kolom kiri, centang GTK yang ingin dijadikan penguji
3. (Opsional) Atur password default di input kanan atas
4. Klik tombol hijau **→** (Assign)
5. GTK akan otomatis menjadi user dengan role penguji

### Menghapus Role Penguji

1. Di kolom kanan, centang penguji yang ingin dihapus role-nya
2. Klik tombol merah **←** (Remove)
3. Role penguji akan dicabut, tapi user tetap ada di sistem

### Reset Password

1. Di tabel bawah, klik ikon 🔑 pada penguji
2. Masukkan password baru
3. Klik "Reset Password"

### Toggle Status

1. Di tabel bawah, klik ikon user (hijau/kuning)
2. Status akan toggle aktif ↔ nonaktif

## Perbandingan dengan Verifikator

| Aspek | Verifikator | Penguji |
|-------|-------------|---------|
| Interface | Modal assign dari user existing | Dual-listbox dari GTK |
| Sumber data | User existing | GTK → Create user |
| Tabel khusus | `verifikators` | Tidak ada (hanya role) |
| Auto-create user | Tidak | Ya, otomatis |

## Changelog

- **v2.0.0** (2026-02-05)
  - Complete rewrite dengan dual-listbox interface
  - Assign GTK langsung dengan auto-create user
  - Bulk assign/remove
  - AJAX interaction tanpa reload

- **v1.1.0** (2026-02-05)
  - Refactor: Penguji dibuat melalui GTK
  - Removed: CRUD terpisah

- **v1.0.0** (2026-02-05)
  - Initial implementation
