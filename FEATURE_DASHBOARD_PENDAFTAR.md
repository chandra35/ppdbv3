# FEATURE: Dashboard Pendaftar (Calon Siswa)

## Tanggal Implementasi
2025-01-xx

## Deskripsi Fitur
Dashboard khusus untuk calon siswa (pendaftar) PPDB dengan flow:
1. Landing Page - Input NISN untuk validasi via EMIS API
2. Registrasi - Buat akun dengan auto-generate password
3. Login - Akses dashboard dengan NISN/email dan password
4. Dashboard - Kelola data pendaftaran

## Komponen yang Dibuat

### 1. Database Migration
**File:** `database/migrations/2025_12_11_220000_create_pengaturan_wa_table.php`

Tabel `pengaturan_wa`:
- `provider` - Provider WhatsApp API (fonnte/wablas/wabotapi/twilio/other)
- `api_key` - API key dari provider
- `api_url` - Custom API URL (opsional)
- `sender_number` - Nomor pengirim
- `is_active` - Status aktif/nonaktif
- `template_registrasi` - Template pesan registrasi
- `template_verifikasi` - Template pesan verifikasi
- `template_diterima` - Template pesan diterima
- `template_ditolak` - Template pesan ditolak

### 2. Model
**File:** `app/Models/PengaturanWa.php`

Methods:
- `getApiUrlAttribute()` - Get provider-specific API URL
- `getSettings()` - Get settings instance
- `isActive()` - Check if WA is active
- `getDefaultTemplates()` - Get default message templates

### 3. Services
**File:** `app/Services/WhatsAppService.php`

Multi-provider WhatsApp service:
- `send($phone, $message)` - Send message
- `sendCredentials($phone, $data)` - Send registration credentials
- `sendStatusNotification($phone, $status, $data)` - Send status notification
- `testConnection()` - Test API connection

Supported Providers:
- Fonnte (https://fonnte.com)
- Wablas (https://wablas.com)
- Wabotapi (https://wabotapi.com)
- Twilio
- Custom (other)

### 4. Controllers

#### AuthController (Pendaftar)
**File:** `app/Http/Controllers/Pendaftar/AuthController.php`

Routes:
- `GET /pendaftar` - Landing page
- `POST /pendaftar/cek-nisn` - Check NISN via EMIS API
- `GET /pendaftar/register` - Show registration form
- `POST /pendaftar/register` - Process registration
- `GET /pendaftar/register/success` - Success page with credentials
- `GET /pendaftar/login` - Login form
- `POST /pendaftar/login` - Process login
- `POST /pendaftar/logout` - Logout

#### DashboardController (Pendaftar)
**File:** `app/Http/Controllers/Pendaftar/DashboardController.php`

Routes:
- `GET /pendaftar/dashboard` - Dashboard overview
- `GET /pendaftar/data-pribadi` - Personal data form
- `PUT /pendaftar/data-pribadi` - Update personal data
- `GET /pendaftar/data-ortu` - Parent data form
- `PUT /pendaftar/data-ortu` - Update parent data
- `GET /pendaftar/dokumen` - Document upload
- `POST /pendaftar/dokumen` - Upload document
- `DELETE /pendaftar/dokumen/{id}` - Delete document
- `GET /pendaftar/status` - Registration status
- `GET /pendaftar/cetak-bukti` - Print registration proof

#### PengaturanWaController (Admin)
**File:** `app/Http/Controllers/Admin/PengaturanWaController.php`

Routes:
- `GET /admin/pengaturan/whatsapp` - Settings form
- `PUT /admin/pengaturan/whatsapp` - Update settings
- `POST /admin/pengaturan/whatsapp/test-connection` - Test API
- `POST /admin/pengaturan/whatsapp/send-test` - Send test message
- `GET /admin/pengaturan/whatsapp/reset-templates` - Reset templates

### 5. Views

#### Layout
**File:** `resources/views/layouts/pendaftar.blade.php`
- AdminLTE 3 based layout untuk pendaftar
- Sidebar dengan progress indicator
- Menu: Dashboard, Data Pribadi, Data Orang Tua, Dokumen, Status, Cetak Bukti

#### Auth Views
- `resources/views/pendaftar/landing.blade.php` - NISN check
- `resources/views/pendaftar/login.blade.php` - Login form
- `resources/views/pendaftar/register.blade.php` - Registration form
- `resources/views/pendaftar/register-success.blade.php` - Success + credentials

#### Dashboard Views
- `resources/views/pendaftar/dashboard/index.blade.php` - Overview
- `resources/views/pendaftar/dashboard/data-pribadi.blade.php` - Personal data
- `resources/views/pendaftar/dashboard/data-ortu.blade.php` - Parent data
- `resources/views/pendaftar/dashboard/dokumen.blade.php` - Documents
- `resources/views/pendaftar/dashboard/status.blade.php` - Status timeline
- `resources/views/pendaftar/dashboard/cetak-bukti.blade.php` - Print proof

#### Admin Views
- `resources/views/admin/pengaturan/whatsapp.blade.php` - WA settings

### 6. Seeder
**File:** `database/seeders/RoleSeeder.php`

Role 'pendaftar' dengan permissions:
- `pendaftar.dashboard`
- `pendaftar.profile.view`
- `pendaftar.profile.edit`
- `pendaftar.dokumen.upload`
- `pendaftar.dokumen.delete`
- `pendaftar.status.view`
- `pendaftar.cetak-bukti`

## Flow Registrasi

```
┌──────────────────┐
│  Landing Page    │
│  Input NISN      │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐     ┌──────────────────┐
│  Cek NISN via    │────▶│  Tidak Ditemukan │
│  EMIS API        │     │  (Error Message) │
└────────┬─────────┘     └──────────────────┘
         │ Ditemukan
         ▼
┌──────────────────┐
│  Form Registrasi │
│  - No. WA        │
│  - Email         │
│  - Nama Ortu     │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Create Account  │
│  - User account  │
│  - CalonSiswa    │
│  - Role pendaftar│
└────────┬─────────┘
         │
         ▼
┌──────────────────┐     ┌──────────────────┐
│  WA Aktif?       │─Yes─▶│  Kirim via WA    │
└────────┬─────────┘     │  (Credentials)   │
         │ No            └──────────────────┘
         ▼
┌──────────────────┐
│  Success Page    │
│  Show Credentials│
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Login Page      │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  Dashboard       │
│  Pendaftar       │
└──────────────────┘
```

## URL Endpoints

### Pendaftar (Public/Auth)
| Method | URL | Name | Description |
|--------|-----|------|-------------|
| GET | /pendaftar | pendaftar.landing | Landing dengan cek NISN |
| POST | /pendaftar/cek-nisn | pendaftar.cek-nisn | AJAX cek NISN |
| GET | /pendaftar/login | pendaftar.login | Form login |
| POST | /pendaftar/login | pendaftar.login.post | Process login |
| GET | /pendaftar/register | pendaftar.register.form | Form registrasi |
| POST | /pendaftar/register | pendaftar.register.post | Process registrasi |
| GET | /pendaftar/register/success | pendaftar.register.success | Halaman sukses |
| POST | /pendaftar/logout | pendaftar.logout | Logout |
| GET | /pendaftar/dashboard | pendaftar.dashboard | Dashboard |
| GET | /pendaftar/data-pribadi | pendaftar.data-pribadi | Data pribadi |
| PUT | /pendaftar/data-pribadi | pendaftar.data-pribadi.update | Update data pribadi |
| GET | /pendaftar/data-ortu | pendaftar.data-ortu | Data orang tua |
| PUT | /pendaftar/data-ortu | pendaftar.data-ortu.update | Update data ortu |
| GET | /pendaftar/dokumen | pendaftar.dokumen | Upload dokumen |
| POST | /pendaftar/dokumen | pendaftar.dokumen.upload | Process upload |
| DELETE | /pendaftar/dokumen/{id} | pendaftar.dokumen.delete | Hapus dokumen |
| GET | /pendaftar/status | pendaftar.status | Status pendaftaran |
| GET | /pendaftar/cetak-bukti | pendaftar.cetak-bukti | Cetak bukti |

### Admin (WhatsApp Settings)
| Method | URL | Name | Description |
|--------|-----|------|-------------|
| GET | /admin/pengaturan/whatsapp | admin.pengaturan.whatsapp.index | Form settings |
| PUT | /admin/pengaturan/whatsapp | admin.pengaturan.whatsapp.update | Update settings |
| POST | /admin/pengaturan/whatsapp/test-connection | admin.pengaturan.whatsapp.test-connection | Test API |
| POST | /admin/pengaturan/whatsapp/send-test | admin.pengaturan.whatsapp.send-test | Send test |
| GET | /admin/pengaturan/whatsapp/reset-templates | admin.pengaturan.whatsapp.reset-templates | Reset templates |

## Konfigurasi WhatsApp API

### Default: Disabled
WhatsApp API default dalam kondisi nonaktif. Untuk mengaktifkan:
1. Login sebagai Admin
2. Menu: System → Pengaturan → WhatsApp API
3. Pilih provider (Fonnte/Wablas/Wabotapi/dll)
4. Masukkan API Key
5. Masukkan nomor pengirim
6. Aktifkan toggle "Status"
7. Test koneksi
8. Simpan

### Provider Support
- **Fonnte** - https://fonnte.com (Rp50.000/bulan)
- **Wablas** - https://wablas.com (Rp35.000/bulan)
- **Wabotapi** - https://wabotapi.com (Free tier available)
- **Twilio** - https://twilio.com/whatsapp (Pay as you go)
- **Custom** - Gunakan API URL sendiri

### Template Variables
```
{nama} - Nama siswa
{nisn} - NISN
{password} - Password (hanya untuk registrasi)
{no_pendaftaran} - Nomor registrasi
{tanggal} - Tanggal
{madrasah} - Nama sekolah
{url} - URL login
```

## Testing

### Test Landing Page
```
http://127.0.0.1:7000/pendaftar
```

### Test NISN Check
- Input NISN valid yang terdaftar di EMIS
- Sistem akan menampilkan data siswa dari EMIS

### Test Registration
1. Cek NISN → Klik "Daftar Sekarang"
2. Isi form: No. WA, Email, Nama Orang Tua
3. Submit → Lihat credentials

### Test Login
```
http://127.0.0.1:7000/pendaftar/login
Username: [NISN]
Password: [Generated password]
```

### Test Dashboard
Setelah login, akses semua menu:
- Dashboard
- Data Pribadi
- Data Orang Tua
- Upload Dokumen
- Status Pendaftaran
- Cetak Bukti

## Notes
- Password auto-generate 8 karakter (huruf + angka)
- NISN digunakan sebagai username
- WhatsApp notification opsional (disabled by default)
- Credentials ditampilkan di success page jika WA tidak aktif
- Data NISN diambil dari EMIS API menggunakan token yang sudah dikonfigurasi
