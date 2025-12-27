# Analisa: Masalah Duplikasi Data Orang Tua

## 📋 Ringkasan Masalah

Ditemukan duplikasi penyimpanan data orang tua di **2 lokasi berbeda**:
1. **Table `calon_siswas`** - Field `nama_ayah`, `nama_ibu`, `status_dalam_keluarga`
2. **Table `calon_ortus`** - 16 field lengkap data orang tua sesuai EMIS

## 🔍 Akar Masalah

### 1. Model CalonSiswa Memiliki Field Orang Tua

**File:** `app/Models/CalonSiswa.php` (Line 47-48)

```php
protected $fillable = [
    // ...
    
    // Data keluarga (dari EMIS)
    'nama_ibu',
    'nama_ayah',
    'status_dalam_keluarga',
    
    // ...
];
```

**Masalah:** Field ini seharusnya tidak ada di table `calon_siswas` karena sudah ada relasi dengan table `calon_ortus`.

---

### 2. Proses Pendaftaran PPDB

**File:** `app/Http/Controllers/Ppdb/RegisterController.php`

#### Step 1: Validasi NISN dengan API EMIS (Line 97-130)

```php
public function validateNisn(Request $request)
{
    // ...
    
    // Validate NISN against EMIS API
    $emisService = new EmisNisnService();
    $emisResult = $emisService->cekNisn($validated['nisn']);
    
    // Store EMIS data if found (for pre-filling form in step 2)
    if ($emisResult['success'] && $emisResult['data']) {
        $sessionData['ppdb_nisn_valid'] = true;
        $sessionData['ppdb_emis_data'] = $emisService->extractStudentData($emisResult['data']);
        // ...
    }
}
```

**Data EMIS yang diambil dari API:**
- `nama` (nama siswa)
- `nisn`
- `nik`
- `tempat_lahir`
- `tanggal_lahir`
- `jenis_kelamin`
- **`nama_ibu`** ← Ini yang bermasalah
- **`nama_ayah`** ← Ini yang bermasalah
- `asal_sekolah`

#### Step 5: Confirm & Simpan Data (Line 460-580)

**TIDAK ADA KODE** yang menyimpan `nama_ayah` dan `nama_ibu` ke table `calon_siswas`:

```php
// 2. Create CalonSiswa (Line 480-500)
$calonSiswa = CalonSiswa::create([
    // ...
    'nik' => $dataDiri['nik'],
    'nama_lengkap' => $dataDiri['nama_lengkap'],
    // ...
    
    // ❌ TIDAK ADA: 'nama_ayah' => ...
    // ❌ TIDAK ADA: 'nama_ibu' => ...
    
    'data_ortu_completed' => true,
]);

// 3. Create CalonOrtu (Line 513-560)
CalonOrtu::create([
    'id' => Uuid::uuid4()->toString(),
    'calon_siswa_id' => $calonSiswa->id,
    'no_kk' => $dataOrtu['no_kk'],
    
    // ✅ Data lengkap tersimpan di calon_ortus
    'nama_ayah' => $dataOrtu['nama_ayah'],
    'nik_ayah' => $dataOrtu['nik_ayah'],
    // ... 14 field lainnya
    
    'nama_ibu' => $dataOrtu['nama_ibu'],
    'nik_ibu' => $dataOrtu['nik_ibu'],
    // ... 14 field lainnya
]);
```

---

## 🤔 Kenapa Data Ada di `calon_siswas`?

### Skenario 1: Data Lama (Sebelum Implementasi `calon_ortus`)
Kemungkinan besar pendaftar dengan NISN 0105433296 mendaftar **sebelum** table `calon_ortus` digunakan. Saat itu data orang tua disimpan langsung di `calon_siswas`.

### Skenario 2: Migration/Seeder
Ada kemungkinan ada **seeder atau migration** yang mengisi field `nama_ayah`/`nama_ibu` di table `calon_siswas` untuk testing.

### Skenario 3: Manual Input
Admin bisa saja manual update field `nama_ayah`/`nama_ibu` di table `calon_siswas` melalui query atau tool lain.

---

## 🎯 Bukti Temuan

### Data Pendaftar 019b13b2-2549-729d-8156-7f8a6c33cd1b

```
Nama: ACHMAD ALRIZQY AKBAR
Nama Ibu di calon_siswas: Nur Puspa Yanti  ← ADA
Nama Ayah di calon_siswas: NULL            ← KOSONG
Relasi ortu: Tidak Ada                     ← KOSONG
```

**Kesimpulan:** Data `nama_ibu` ada di `calon_siswas`, tapi **TIDAK ADA** di `calon_ortus`.

---

## 💡 Solusi Recommended

### Opsi 1: **Gunakan `calon_ortus` sebagai Single Source of Truth** (RECOMMENDED)

#### ✅ Kelebihan:
- **Data terpusat** di table `calon_ortus` yang sudah EMIS-compliant
- **Tidak ada duplikasi**
- **Lebih mudah maintain** karena 1 tempat saja
- **Lebih lengkap** (16 field vs 2 field)

#### 🛠️ Langkah-langkah:

1. **Hapus field `nama_ayah`, `nama_ibu`, `status_dalam_keluarga` dari `calon_siswas`**
   ```php
   // Migration
   Schema::table('calon_siswas', function (Blueprint $table) {
       $table->dropColumn(['nama_ayah', 'nama_ibu', 'status_dalam_keluarga']);
   });
   ```

2. **Update Model `CalonSiswa`** - Hapus dari $fillable:
   ```php
   protected $fillable = [
       // HAPUS:
       // 'nama_ibu',
       // 'nama_ayah',
       // 'status_dalam_keluarga',
   ];
   ```

3. **Migrate Data Existing** - Pindahkan data dari `calon_siswas` ke `calon_ortus`:
   ```php
   // Migration script
   $pendaftars = CalonSiswa::whereNotNull('nama_ayah')
       ->orWhereNotNull('nama_ibu')
       ->get();
   
   foreach ($pendaftars as $p) {
       if (!$p->ortu) {
           CalonOrtu::create([
               'id' => Uuid::uuid4()->toString(),
               'calon_siswa_id' => $p->id,
               'nama_ayah' => $p->nama_ayah,
               'nama_ibu' => $p->nama_ibu,
               'status_ayah' => 'masih_hidup',
               'status_ibu' => 'masih_hidup',
           ]);
       }
   }
   ```

4. **Update View `show.blade.php`** - Ganti dari `$pendaftar->nama_ibu` ke `$pendaftar->ortu->nama_ibu`:
   ```php
   // BEFORE:
   <dd>{{ $pendaftar->nama_ibu ?? '-' }}</dd>
   <dd>{{ $pendaftar->nama_ayah ?? '-' }}</dd>
   
   // AFTER:
   <dd>{{ optional($pendaftar->ortu)->nama_ibu ?? '-' }}</dd>
   <dd>{{ optional($pendaftar->ortu)->nama_ayah ?? '-' }}</dd>
   ```

5. **Fix Controller `PendaftarController`** - Sudah benar (sudah sync nama ke calon_siswas juga).

---

### Opsi 2: **Sync Otomatis Dua-Arah** (NOT RECOMMENDED)

Simpan di kedua table dan selalu sync:
- `calon_siswas.nama_ayah` = `calon_ortus.nama_ayah`
- `calon_siswas.nama_ibu` = `calon_ortus.nama_ibu`

#### ❌ Kekurangan:
- **Duplikasi data** (redundant)
- **Lebih kompleks** untuk maintain
- **Risk inconsistency** jika lupa sync
- **Pemborosan storage**

---

## 📊 Perbandingan Data Storage

| Location | Field Count | Purpose | EMIS Compliant |
|----------|-------------|---------|----------------|
| `calon_siswas` | 3 fields | Legacy, tidak lengkap | ❌ No |
| `calon_ortus` | 30 fields | Data lengkap ayah, ibu, wali | ✅ Yes |

---

## 🚀 Rekomendasi Akhir

**Gunakan Opsi 1** dengan langkah-langkah:

1. ✅ **[DONE]** Fix form edit untuk load data dari kedua tempat sebagai temporary fix
2. 🔄 **[TODO]** Migrate existing data dari `calon_siswas` ke `calon_ortus`
3. 🔄 **[TODO]** Drop column `nama_ayah`, `nama_ibu` dari `calon_siswas`
4. 🔄 **[TODO]** Update view `show.blade.php` untuk ambil dari relasi `ortu`
5. 🔄 **[TODO]** Cleanup code yang reference field yang sudah dihapus

**Timeline:** 1-2 jam untuk migrate + testing

**Risk Level:** 🟢 Low (data sudah ada di calon_ortus, tinggal migrate yang belum)

---

## 📝 Catatan Teknis

### Query untuk Cek Data Pendaftar Bermasalah

```sql
-- Cari pendaftar yang punya nama ortu di calon_siswas tapi tidak di calon_ortus
SELECT 
    cs.id,
    cs.nisn,
    cs.nama_lengkap,
    cs.nama_ayah AS ayah_di_siswas,
    cs.nama_ibu AS ibu_di_siswas,
    co.nama_ayah AS ayah_di_ortus,
    co.nama_ibu AS ibu_di_ortus
FROM calon_siswas cs
LEFT JOIN calon_ortus co ON co.calon_siswa_id = cs.id
WHERE (cs.nama_ayah IS NOT NULL OR cs.nama_ibu IS NOT NULL)
  AND co.id IS NULL;
```

### Estimated Affected Records

Perlu dijalankan query di atas untuk tau berapa banyak data yang perlu dimigrate.

---

**Dibuat oleh:** GitHub Copilot (Claude Sonnet 4.5)  
**Tanggal:** 28 Desember 2025  
**Status:** ✅ Issue Identified, 🔄 Solution Proposed
