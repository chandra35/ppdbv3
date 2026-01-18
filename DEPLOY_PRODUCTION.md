# Panduan Deploy PPDB ke Production
## ppdb.man1metro.sch.id

---

## 1. PERSIAPAN DI KOMPUTER LOKAL

### 1.1 Bersihkan File Debug
Hapus file-file berikut sebelum upload (JANGAN upload ke production):
```
check_*.php
verify_*.php
test_*.php
fix_*.php
reset_*.php
sync_*.php
list_*.php
generate_*.php
migrate_*.php
backup_database.php
temp_*.json
```

### 1.2 Export Database (Opsional)
Jika ingin migrate data existing:
```powershell
cd d:\projek\ppdbv3
mysqldump -u root ppdbv3 > ppdbv3_backup.sql
```

---

## 2. UPLOAD KE SERVER

### Opsi A: Via Git (Rekomendasi)
```bash
# Di server
cd ~/ppdb.man1metro.sch.id
git clone https://github.com/username/ppdbv3.git .
```

### Opsi B: Via SCP
```powershell
# Di komputer lokal
scp -r d:\projek\ppdbv3\* manmetr1@arzano:~/ppdb.man1metro.sch.id/
```

### Opsi C: Via FTP/File Manager
1. Zip folder ppdbv3 (exclude: vendor, node_modules, .git)
2. Upload via File Manager
3. Extract di server

---

## 3. SETUP DI SERVER (SSH)

### 3.1 Masuk ke Server
```bash
ssh manmetr1@arzano
cd ~/ppdb.man1metro.sch.id
```

### 3.2 Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3.3 Setup Environment
```bash
# Copy template production
cp .env.production .env

# Edit sesuai kredensial hosting
nano .env
```

**Yang WAJIB diedit di .env:**
```env
APP_KEY=                          # Kosongkan, nanti di-generate
DB_DATABASE=manmetr1_ppdb         # Nama database di hosting
DB_USERNAME=manmetr1_ppdb         # Username database
DB_PASSWORD=password_sebenarnya   # Password database

# Jika pakai SIMANSA:
SIMANSA_DB_DATABASE=manmetr1_simansa
SIMANSA_DB_USERNAME=manmetr1_simansa
SIMANSA_DB_PASSWORD=password_simansa
```

### 3.4 Generate App Key
```bash
php artisan key:generate
```

### 3.5 Buat Symlink Storage
```bash
php artisan storage:link
```

### 3.6 Setup Database

**Opsi A: Migrasi Fresh (Database Kosong)**
```bash
php artisan migrate --force
php artisan db:seed --force
```

**Opsi B: Import dari Lokal**
```bash
# Upload file SQL dulu, lalu:
mysql -u manmetr1_ppdb -p manmetr1_ppdb < ppdbv3_backup.sql
```

### 3.7 Set Permission
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R manmetr1:manmetr1 storage
chown -R manmetr1:manmetr1 bootstrap/cache
```

### 3.8 Optimasi Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 4. KONFIGURASI WEB SERVER

### 4.1 Pastikan Document Root
Domain harus mengarah ke folder `public/`:
```
Document Root: /home/manmetr1/ppdb.man1metro.sch.id/public
```

### 4.2 Jika Document Root ke Root (bukan public)
Buat file `.htaccess` di root project:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 4.3 SSL/HTTPS
Pastikan SSL sudah aktif untuk domain. Jika pakai Let's Encrypt:
```bash
# Biasanya sudah otomatis di cloud hosting
# Atau via cPanel: SSL/TLS Status → AutoSSL
```

---

## 5. VERIFIKASI

### 5.1 Test Koneksi Database
```bash
php artisan tinker
>>> \DB::connection()->getPdo();
>>> exit
```

### 5.2 Test Website
Buka browser: https://ppdb.man1metro.sch.id

### 5.3 Login Admin
- URL: https://ppdb.man1metro.sch.id/login
- Email: admin@madrasah.sch.id (atau sesuai seeder)
- Password: password (SEGERA GANTI!)

---

## 6. SETUP DATA SEKOLAH

Setelah login admin:
1. **Pengaturan → Sekolah**: Isi nama, alamat, logo
2. **Pengaturan → Tahun Pelajaran**: Set tahun aktif
3. **Pengaturan → Jalur Pendaftaran**: Buat jalur PPDB
4. **Pengaturan → Gelombang**: Set periode pendaftaran
5. **Pengaturan → EMIS Token**: Isi token jika pakai EMIS

---

## 7. MAINTENANCE

### Clear Cache (jika ada perubahan)
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Update dari Git
```bash
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

### Backup Database
```bash
mysqldump -u manmetr1_ppdb -p manmetr1_ppdb > backup_$(date +%Y%m%d).sql
```

---

## 8. TROUBLESHOOTING

### Error 500
```bash
# Cek log
tail -f storage/logs/laravel.log

# Pastikan permission benar
chmod -R 775 storage bootstrap/cache
```

### Halaman Blank
```bash
# Clear semua cache
php artisan optimize:clear
```

### Session Error
```bash
# Pastikan tabel sessions ada
php artisan migrate --force
```

### Storage Tidak Muncul
```bash
# Buat ulang symlink
rm -rf public/storage
php artisan storage:link
```

---

## CHECKLIST SEBELUM GO LIVE

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Database credentials benar
- [ ] Migrasi berhasil
- [ ] Storage link dibuat
- [ ] Permission 775 untuk storage/
- [ ] HTTPS aktif
- [ ] Logo sekolah diupload
- [ ] Tahun pelajaran aktif di-set
- [ ] Jalur pendaftaran dibuat
- [ ] Gelombang pendaftaran dibuat
- [ ] Admin password sudah diganti
- [ ] File debug (check_*.php) sudah dihapus
