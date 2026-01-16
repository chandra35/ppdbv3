# Perubahan Alur Finalisasi Pendaftaran PPDB

## Ringkasan Perubahan

Alur finalisasi pendaftaran diubah sehingga pendaftar dapat melakukan finalisasi sebelum dokumen diverifikasi. Nomor tes akan diberikan setelah admin memverifikasi semua dokumen.

## Alur Baru

### 1. Finalisasi oleh Pendaftar
- Pendaftar dapat melakukan finalisasi setelah semua data lengkap (data pribadi, ortu, dokumen, nilai rapor, pilihan program)
- **Verifikasi dokumen BUKAN lagi syarat finalisasi**
- Setelah finalisasi:
  - `is_finalisasi = true`
  - `tanggal_finalisasi = now()`
  - `status_verifikasi = 'pending_verification'`
  - `status_admisi = 'pending'`
- **Nomor tes TIDAK langsung diberikan**

### 2. Verifikasi Dokumen oleh Admin
- Admin memverifikasi dokumen satu per satu
- Ketika SEMUA dokumen disetujui (status = 'valid'):
  - `status_verifikasi = 'verified'`
  - `verified_at = now()`
  - **Nomor tes otomatis di-generate**
  - **Notifikasi WhatsApp dikirim ke pendaftar**

### 3. Tampilan di Dashboard Pendaftar
- **Bukti Registrasi**: Tersedia setelah finalisasi (tidak perlu nomor tes)
- **Kartu Ujian**: Tersedia HANYA jika nomor tes sudah ada
  - Jika belum ada nomor tes, tampil "Menunggu Verifikasi"

## File yang Dimodifikasi

### 1. `app/Http/Controllers/Pendaftar/DashboardController.php`

#### `checkFinalisasiRequirements()`
- Menghapus syarat verifikasi dari daftar requirement
- Pendaftar bisa finalisasi tanpa harus sudah terverifikasi

#### `storeFinalisasi()`
- Tidak lagi generate nomor tes saat finalisasi
- Set status ke `pending_verification`
- Jika dokumen sudah semua valid, langsung generate nomor tes

#### `generateNomorTes()` (NEW)
- Method baru untuk generate nomor tes
- Digunakan oleh storeFinalisasi dan autoUpdateStatusVerifikasi

### 2. `app/Models/CalonSiswa.php`

#### `autoUpdateStatusVerifikasi()`
- Dimodifikasi untuk generate nomor tes ketika semua dokumen valid DAN pendaftar sudah finalisasi
- Otomatis memanggil `generateNomorTesAfterVerification()`

#### `generateNomorTesAfterVerification()` (NEW)
- Generate nomor tes setelah verifikasi lengkap
- Memanggil `sendVerificationNotification()`

#### `sendVerificationNotification()` (NEW)
- Mengirim notifikasi WhatsApp ke pendaftar
- Berisi informasi bahwa dokumen sudah diverifikasi dan nomor tes

### 3. `resources/views/pendaftar/dashboard/index.blade.php`
- Tombol Bukti Registrasi: tampil jika `is_finalisasi = true`
- Tombol Kartu Ujian: tampil jika `nomor_tes` ada
- Jika belum ada nomor_tes: tampil card disabled "Menunggu Verifikasi"

### 4. `resources/views/pendaftar/dashboard/finalisasi.blade.php`
- Menampilkan card nomor tes hanya jika sudah ada
- Jika belum ada nomor tes: tampil info "Menunggu Verifikasi Dokumen"
- Tombol Kartu Ujian disabled jika belum ada nomor tes
- Update info finalisasi tentang proses verifikasi

### 5. `resources/views/layouts/pendaftar.blade.php`
- Menu sidebar: Kartu Ujian tampil jika nomor_tes ada
- Jika belum ada: tampil menu disabled dengan badge "Pending"

## Notifikasi WhatsApp

Setelah admin memverifikasi semua dokumen, sistem akan otomatis mengirim notifikasi WA ke pendaftar dengan format:

```
🎉 *DOKUMEN TERVERIFIKASI*

Assalamu'alaikum Wr. Wb.

Dokumen pendaftaran PPDB atas nama *[Nama Lengkap]* telah diverifikasi lengkap.

📋 *Detail Pendaftaran:*
• No. Registrasi: [nomor_registrasi]
• NISN: [nisn]
• Jalur: [nama_jalur]

🎫 *NOMOR TES ANDA:*
*[NOMOR_TES]*

Simpan nomor tes ini untuk keperluan ujian seleksi.
Silahkan login ke akun pendaftar untuk mencetak Kartu Ujian.

Terima kasih.
[Nama Sekolah]
```

## Status Verifikasi

| Status | Keterangan |
|--------|------------|
| `draft` | Data belum lengkap |
| `pending` | Data lengkap, belum finalisasi |
| `pending_verification` | Sudah finalisasi, menunggu verifikasi dokumen |
| `verified` | Semua dokumen valid, nomor tes tersedia |
| `rejected` | Pendaftaran ditolak |

## Diagram Alur

```
┌─────────────────────────────┐
│  Pendaftar Lengkapi Data    │
│  (Data Pribadi, Ortu, dll)  │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Klik Finalisasi           │
│   is_finalisasi = true      │
│   status = pending_verification │
│   (Belum ada Nomor Tes)     │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Admin Verifikasi Dokumen  │
│   Approve satu per satu     │
└─────────────┬───────────────┘
              │
              ▼
┌─────────────────────────────┐
│   Semua Dokumen Valid?      │
│         │                   │
│    ┌────┴────┐             │
│    │ TIDAK   │             │
│    ▼         │             │
│   Tunggu     │             │
│   dokumen    │             │
│   lain       │             │
│              │ YA           │
│              ▼             │
│   ┌───────────────────┐    │
│   │ Generate Nomor Tes│    │
│   │ status = verified │    │
│   │ Kirim Notif WA    │    │
│   └───────────────────┘    │
└─────────────────────────────┘
              │
              ▼
┌─────────────────────────────┐
│  Pendaftar Terima Notif WA  │
│  Nomor Tes Tersedia         │
│  Bisa Cetak Kartu Ujian     │
└─────────────────────────────┘
```

## Tanggal Implementasi
17 Januari 2025
