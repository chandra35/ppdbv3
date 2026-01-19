# FEATURE: Email Notification System

## Deskripsi
Sistem notifikasi email yang lengkap untuk PPDB sebagai alternatif WhatsApp. Sistem ini mengirimkan notifikasi pada setiap tahapan penting proses pendaftaran.

## Tipe Email yang Tersedia

### 1. Email Registrasi (`TYPE_REGISTRASI`)
- **Trigger**: Saat calon siswa berhasil registrasi
- **Isi**: Username, password, dan langkah selanjutnya
- **File**: `App\Mail\RegistrasiNotification.php`
- **Template**: `resources/views/emails/registrasi.blade.php`
- **Lokasi Integrasi**: `AuthController@store()`

### 2. Email Revisi Dokumen (`TYPE_REVISI`)
- **Trigger**: Saat admin meminta revisi dokumen
- **Isi**: Nama dokumen yang perlu direvisi dan catatan
- **File**: `App\Mail\RevisiDokumenNotification.php`
- **Template**: `resources/views/emails/revisi-dokumen.blade.php`
- **Lokasi Integrasi**: `PendaftarController@revisiDokumen()`

### 3. Email Nomor Tes (`TYPE_NOMOR_TES`)
- **Trigger**: Saat nomor tes digenerate (finalisasi)
- **Isi**: Nomor tes, informasi ujian
- **File**: `App\Mail\NomorTesNotification.php`
- **Template**: `resources/views/emails/nomor-tes.blade.php`
- **Lokasi Integrasi**: `CalonSiswa@sendVerificationNotification()`

### 4. Email Hasil Seleksi - Diterima (`TYPE_DITERIMA`)
- **Trigger**: Saat status admisi diubah ke "diterima"
- **Isi**: Ucapan selamat, langkah daftar ulang
- **File**: `App\Mail\HasilSeleksiNotification.php`
- **Template**: `resources/views/emails/hasil-seleksi.blade.php`
- **Lokasi Integrasi**: `NilaiSeleksiController@updateAdmisi()` dan `bulkUpdateAdmisi()`

### 5. Email Hasil Seleksi - Ditolak (`TYPE_DITOLAK`)
- **Trigger**: Saat status admisi diubah ke "ditolak"
- **Isi**: Informasi tidak diterima dengan catatan
- **File**: `App\Mail\HasilSeleksiNotification.php`
- **Template**: `resources/views/emails/hasil-seleksi.blade.php`
- **Lokasi Integrasi**: `NilaiSeleksiController@updateAdmisi()` dan `bulkUpdateAdmisi()`

## EmailNotificationService

Semua pengiriman email diproses melalui service centralized:

```php
use App\Services\EmailNotificationService;

// Kirim email registrasi
EmailNotificationService::sendRegistrasi($calonSiswa, $username, $password);

// Kirim email revisi dokumen
EmailNotificationService::sendRevisiDokumen($calonSiswa, $dokumen, $catatan);

// Kirim email nomor tes
EmailNotificationService::sendNomorTes($calonSiswa, $nomorTes);

// Kirim email hasil seleksi (diterima/ditolak)
EmailNotificationService::sendHasilSeleksi($calonSiswa, 'diterima', $keterangan);
EmailNotificationService::sendHasilSeleksi($calonSiswa, 'ditolak', $keterangan);
```

## Email Log

Semua email dicatat di tabel `email_logs` dengan informasi:
- `to_email` - Alamat email tujuan
- `to_name` - Nama penerima
- `subject` - Subject email
- `type` - Tipe email (registrasi, revisi, nomor_tes, diterima, ditolak)
- `status` - Status pengiriman (sent/failed)
- `error_message` - Pesan error jika gagal
- `message_preview` - Preview isi pesan
- `calon_siswa_id` - Relasi ke pendaftar

## Halaman Pengumuman

Fitur baru untuk mengatur status admisi pendaftar:

**Route**: `admin.nilai-seleksi.pengumuman`
**URL**: `/admin/nilai-seleksi/pengumuman`

### Fitur:
1. **Statistik**: Total kandidat, diterima, ditolak, pending
2. **Tabel Kandidat**: Dengan filter dan sorting
3. **Update Individual**: Modal untuk update satu per satu
4. **Bulk Update**: Update banyak kandidat sekaligus
5. **Kirim Email**: Checkbox untuk mengirim email notifikasi

### Endpoint API:
- `POST /admin/nilai-seleksi/update-admisi/{calonSiswa}` - Update individual
- `POST /admin/nilai-seleksi/bulk-update-admisi` - Bulk update

## Konfigurasi SMTP

Konfigurasi di `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.man1metro.sch.id
MAIL_PORT=465
MAIL_USERNAME=ppdb@man1metro.sch.id
MAIL_PASSWORD=xxxxx
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=ppdb@man1metro.sch.id
MAIL_FROM_NAME="PPDB MAN 1 Metro"
```

## Testing

Untuk test kirim email:

```bash
php artisan tinker

# Test kirim email registrasi
$cs = App\Models\CalonSiswa::first();
App\Services\EmailNotificationService::sendRegistrasi($cs, 'testuser', 'testpass123');

# Test kirim email hasil seleksi
App\Services\EmailNotificationService::sendHasilSeleksi($cs, 'diterima', 'Selamat!');
```

## Files Created/Modified

### Created:
- `app/Services/EmailNotificationService.php` - Service centralized
- `app/Mail/RegistrasiNotification.php` - Mailable registrasi
- `app/Mail/RevisiDokumenNotification.php` - Mailable revisi
- `app/Mail/HasilSeleksiNotification.php` - Mailable hasil seleksi
- `resources/views/emails/registrasi.blade.php` - Template registrasi
- `resources/views/emails/revisi-dokumen.blade.php` - Template revisi
- `resources/views/emails/hasil-seleksi.blade.php` - Template hasil seleksi
- `resources/views/admin/nilai-seleksi/pengumuman.blade.php` - View pengumuman

### Modified:
- `app/Http/Controllers/Admin/NilaiSeleksiController.php` - Tambah method pengumuman
- `app/Http/Controllers/Admin/PendaftarController.php` - Integrasi email revisi
- `app/Http/Controllers/Pendaftar/AuthController.php` - Integrasi email registrasi
- `app/Models/CalonSiswa.php` - Update sendVerificationNotification
- `routes/ppdb.php` - Tambah route pengumuman
- `config/adminlte.php` - Tambah menu pengumuman

## Author
Generated by AI Assistant

## Date
2026-01-16
