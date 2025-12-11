# 🎉 PPDB V3 - Project Initialization Complete!

**Date:** December 10, 2025  
**Status:** ✅ READY FOR DEVELOPMENT  
**Port:** 8001

---

## 📍 Project Location

```
Folder: d:\projek\ppdbv3
Repository: Will be separate from simansav3
Database: Shared with simansav3 (MySQL)
```

---

## ✅ What's Been Completed

### 1. **Project Creation**
✅ Created fresh Laravel 12 project  
✅ Installed all dependencies (Composer + NPM)  
✅ Generated encryption key  
✅ Ready for development

### 2. **Database Configuration**
✅ Setup MySQL connection  
✅ Connected to: `simansav3` (shared database)  
✅ Can access GTK, users, siswa from SIMANSA  
✅ Ready to create PPDB-specific tables

### 3. **Project Structure**
✅ Created Models: CalonSiswa, CalonDokumen, PpdbSettings, Verifikator, Gtk  
✅ Created Controllers: Landing, Register, Dashboard (Ppdb)  
✅ Created Admin Controllers: Settings, Verifikator, Pendaftar  
✅ Created View folder structure  
✅ Generated migration files for all PPDB tables

### 4. **Server Status**
✅ Server running on: **http://localhost:8001**  
✅ Can test connectivity  
✅ Both PPDB (8001) and SIMANSA (7000/8000) can run simultaneously

---

## 🗂️ Project Structure

```
d:\projek\ppdbv3
├── app/Models/
│   ├── CalonSiswa.php         (Calon siswa - belum lengkap)
│   ├── CalonDokumen.php       (Dokumen calon - belum lengkap)
│   ├── PpdbSettings.php       (Pengaturan PPDB - belum lengkap)
│   ├── Verifikator.php        (Verifikator GTK - belum lengkap)
│   ├── Gtk.php                (Link ke SIMANSA gtk table)
│   └── User.php               (Default Laravel)
│
├── app/Http/Controllers/
│   ├── Ppdb/
│   │   ├── LandingController.php
│   │   ├── RegisterController.php
│   │   └── DashboardController.php
│   └── Admin/
│       ├── PpdbSettingsController.php
│       ├── PpdbVerifikatorController.php
│       └── PpdbPendaftarController.php
│
├── database/migrations/
│   ├── *_create_calon_siswas_table.php          (Empty - perlu fill)
│   ├── *_create_calon_dokumens_table.php        (Empty - perlu fill)
│   ├── *_create_ppdb_settings_table.php         (Empty - perlu fill)
│   └── *_create_verifikators_table.php          (Empty - perlu fill)
│
├── resources/views/
│   ├── ppdb/
│   │   ├── landing.blade.php
│   │   ├── login.blade.php
│   │   ├── dashboard.blade.php
│   │   └── register/
│   │       ├── step1.blade.php
│   │       ├── step2.blade.php
│   │       ├── step3.blade.php
│   │       └── step4.blade.php
│   └── admin/ppdb/
│       ├── settings.blade.php
│       ├── verifikator.blade.php
│       └── pendaftar.blade.php
│
├── routes/
│   ├── web.php                (Perlu update)
│   ├── api.php                (Ready)
│   └── ppdb.php               (Perlu create)
│
├── .env                        (Configured ✅)
├── composer.json              (Updated)
├── package.json               (Updated)
└── SETUP_PPDBV3.md            (Documentation)
```

---

## 🔄 Database Sharing Concept

```
┌──────────────────────────────────────────────────┐
│  Database: simansav3 (MySQL)                     │
├──────────────────────────────────────────────────┤
│                                                  │
│  SIMANSA Tables:                PPDB Tables:    │
│  • users                        • calon_siswa   │
│  • gtk                          • calon_dokumen │
│  • siswa                        • ppdb_settings │
│  • kelas                        • ppdb_verifikator │
│  • tahun_pelajaran                              │
│  • permission, roles                            │
│  • custom_menu                                  │
│  • dll                                          │
│                                                  │
└──────────────────────────────────────────────────┘
    ↑                              ↑
    │                              │
SIMANSA V3 App              PPDB V3 App
Port 7000/8000              Port 8001
d:\projek\simansav3         d:\projek\ppdbv3
```

---

## 🚀 How to Run PPDB V3

### **Terminal 1 - PPDB Server**
```bash
cd d:\projek\ppdbv3
php artisan serve --host=0.0.0.0 --port=8001
```
Access: http://localhost:8001

### **Terminal 2 - Asset Watcher (Optional)**
```bash
cd d:\projek\ppdbv3
npm run dev
```

### **Terminal 3 - SIMANSA Server (if needed)**
```bash
cd d:\projek\simansav3
php artisan serve --host=0.0.0.0 --port=7000
```
Access: http://localhost:7000

---

## 📝 What's Next - Development Roadmap

### **Phase 1: Database & Models (Week 1)**
1. [ ] Edit migrations - define proper table schema
   - `calon_siswa` table
   - `calon_dokumen` table
   - `ppdb_settings` table
   - `ppdb_verifikator` table
2. [ ] Add relationships in models
3. [ ] Run migrations: `php artisan migrate`
4. [ ] Test database connectivity

### **Phase 2: Authentication & Landing (Week 1-2)**
1. [ ] Setup authentication (use Laravel Breeze or custom)
2. [ ] Create landing page UI
3. [ ] Create login page
4. [ ] Setup user roles (admin, verifikator, calon_siswa)

### **Phase 3: Registration Flow (Week 2-3)**
1. [ ] Copy NisValidationService from SIMANSA
2. [ ] Create NISN validation API endpoint
3. [ ] Build registration Step 1 - NISN validation
4. [ ] Build registration Step 2 - Data pribadi
5. [ ] Build registration Step 3 - Upload dokumen
6. [ ] Build registration Step 4 - Review & submit
7. [ ] Save calon_siswa to database

### **Phase 4: Admin Dashboard (Week 3-4)**
1. [ ] Create PPDB settings page
   - Tahun pelajaran
   - Jenjang target
   - Kuota
   - Tanggal buka/tutup
   - Dokumen yang aktif
2. [ ] Create verifikator management page
   - Assign GTK as verifikator
   - Assign dokumen types
3. [ ] Create pendaftar list page
   - List all calon siswa
   - Filter by status
   - Search

### **Phase 5: Verifikasi Workflow (Week 4-5)**
1. [ ] Create verifikasi dokumen page
2. [ ] Show dokumen berdasarkan jenis
3. [ ] Approve/Reject functionality
4. [ ] Generate nomor pendaftaran
5. [ ] Send notification to calon

### **Phase 6: Calon Dashboard (Week 5)**
1. [ ] Create calon dashboard main page
2. [ ] Show registration status
3. [ ] Show document verification status
4. [ ] Show nomor pendaftaran (after approved)
5. [ ] Print nomor pendaftaran

### **Phase 7: Integration & Export (Week 6)**
1. [ ] Create export function untuk calon yang diterima
2. [ ] Create import service ke SIMANSA siswa table
3. [ ] Test data migration
4. [ ] Validation & error handling

### **Phase 8: Testing & Polish (Week 6-7)**
1. [ ] Unit tests
2. [ ] Integration tests
3. [ ] UI/UX refinement
4. [ ] Performance optimization
5. [ ] Documentation

---

## 🔗 How to Access GTK from SIMANSA

**In your PPDB code:**

```php
// Get all GTK
$gtkList = \App\Models\Gtk::all();

// Get specific GTK
$gtk = \App\Models\Gtk::find($gtkId);

// Get GTK with verifikator relation
$verifikator = \App\Models\Verifikator::with('gtk')->get();
```

Same database = direct query, no API needed!

---

## 📚 Reference Files

### **In PPDB V3:**
- `SETUP_PPDBV3.md` - Detailed setup guide
- `PROJECT_STATUS.md` - Current status & checklist

### **In SIMANSA (for reference):**
- `PPDB_SPECIFICATION.md` - Full requirements
- `PPDB_ARCHITECTURE.md` - Architecture diagram
- `DEVELOPMENT_GUIDE.md` - General dev guide

---

## 🛠️ Common Commands

```bash
# Start server
php artisan serve --port=8001

# Run migrations
php artisan migrate

# Create new model
php artisan make:model ModelName -m

# Create new controller
php artisan make:controller NameController

# Create new migration
php artisan make:migration migration_name

# Watch assets
npm run dev

# Build assets
npm run build

# Database query
php artisan tinker
```

---

## 📞 Quick Reference

| Item | Value |
|------|-------|
| **PPDB Folder** | `d:\projek\ppdbv3` |
| **SIMANSA Folder** | `d:\projek\simansav3` |
| **Shared Database** | `simansav3` |
| **PPDB Server Port** | 8001 |
| **SIMANSA Server Port** | 7000 or 8000 |
| **PHP Version** | 8.2.29 |
| **Laravel Version** | 12.42.0 |

---

## ✨ Key Points to Remember

✅ **One Database** - Both apps share database `simansav3`  
✅ **Direct Queries** - No REST API needed between apps  
✅ **GTK Management** - Import GTK directly from SIMANSA  
✅ **Scalable** - Can develop each app independently  
✅ **Version Control** - ppdbv3 and simansav3 are separate repos  
✅ **Production Ready** - Can deploy separately  

---

## 🎯 Current Status

```
✅ Project initialized
✅ Dependencies installed
✅ Database configured
✅ Server running (port 8001)
⏳ Migrations pending (need schema definition)
⏳ Models pending (need relationships)
⏳ Controllers pending (need logic)
⏳ Views pending (need UI)
```

---

**Ready to start development? Start with migrations!** 🚀

Next Step: Edit migration files to define table schema, then run `php artisan migrate`

