# Fitur Dokumen Tambahan

## Deskripsi
Fitur ini memungkinkan pendaftar untuk mengupload dokumen tambahan seperti sertifikat prestasi, KIP, PIP, SKTM, dan dokumen pendukung lainnya. Fitur ini dapat diaktifkan/nonaktifkan oleh admin melalui halaman settings.

## Jenis Dokumen Tambahan
1. **Sertifikat Prestasi/Lomba** - Untuk sertifikat juara/peserta lomba atau kompetisi
2. **KIP (Kartu Indonesia Pintar)** - Bukti kepemilikan KIP
3. **PIP (Program Indonesia Pintar)** - Bukti penerima PIP
4. **SKTM (Surat Keterangan Tidak Mampu)** - Surat dari kelurahan/desa
5. **Piagam Penghargaan** - Piagam dari sekolah atau institusi
6. **Surat Keterangan Domisili** - Surat keterangan tempat tinggal
7. **Surat Rekomendasi** - Surat rekomendasi dari guru/sekolah
8. **Dokumen Lainnya** - Dokumen pendukung lainnya

## Cara Mengaktifkan Fitur (Admin)
1. Login sebagai admin
2. Buka menu **Settings** > **Settings Utama**
3. Aktifkan checkbox **Izinkan Upload Dokumen Tambahan**
4. Klik **Simpan Perubahan**

## Cara Menggunakan (Pendaftar)
1. Login sebagai pendaftar
2. Buka menu **Upload Dokumen**
3. Scroll ke bawah ke bagian **Dokumen Tambahan (Opsional)**
4. Klik tombol **Tambah Dokumen**
5. Pilih jenis dokumen dari dropdown
6. Isi keterangan jika diperlukan (opsional)
7. Pilih file (PDF, JPG, JPEG, PNG, max 5MB)
8. Klik **Upload**

## Alur Verifikasi
- Dokumen tambahan akan diverifikasi oleh admin/verifikator
- Status dokumen: **Pending** → **Valid** / **Revisi**
- Jika status "Revisi", pendaftar dapat menghapus dan upload ulang
- Dokumen yang sudah "Valid" tidak dapat dihapus

## File yang Dimodifikasi
1. `database/migrations/2026_01_12_233555_add_izinkan_dokumen_tambahan_to_ppdb_settings_table.php` - Migration kolom baru
2. `app/Models/PpdbSettings.php` - Tambah field izinkan_dokumen_tambahan
3. `app/Models/CalonDokumen.php` - Konstanta DOKUMEN_TAMBAHAN
4. `app/Http/Controllers/Admin/SettingsController.php` - Handle checkbox admin
5. `resources/views/admin/settings/index.blade.php` - UI checkbox admin
6. `app/Http/Controllers/Pendaftar/DashboardController.php` - Method upload & delete
7. `routes/ppdb.php` - Route dokumen tambahan
8. `resources/views/pendaftar/dashboard/dokumen.blade.php` - UI pendaftar

## Database
Dokumen tambahan disimpan di tabel `calon_dokumen` dengan kolom:
- `jenis_dokumen` - Berisi key dari DOKUMEN_TAMBAHAN (e.g., 'sertifikat_prestasi')
- `nama_file` - Nama file asli
- `keterangan` - Keterangan opsional dari pendaftar
- `file_path` - Path file di storage
- `status_verifikasi` - pending/valid/revision
- `catatan_revisi` - Catatan dari admin jika perlu revisi

## Penyimpanan File
File disimpan di folder `storage/app/public/dokumen/{calon_siswa_id}/tambahan/`

## Validasi
- Format file: PDF, JPG, JPEG, PNG
- Ukuran maksimal: 5MB
- Pendaftar dapat mengupload multiple dokumen dengan jenis yang sama
