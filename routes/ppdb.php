<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ppdb\LandingController;
use App\Http\Controllers\Ppdb\RegisterController;
use App\Http\Controllers\Ppdb\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SekolahSettingsController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\VerifikatorController;
use App\Http\Controllers\Admin\PendaftarController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\GtkController;
use App\Http\Controllers\Admin\JalurPendaftaranController;
use App\Http\Controllers\Admin\TahunPelajaranController;
use App\Http\Controllers\Admin\AlurPendaftaranController;
use App\Http\Controllers\Admin\EmisTokenController;
use App\Http\Controllers\Admin\PengaturanWaController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\PendaftarController as OperatorPendaftarController;
use App\Http\Controllers\Pendaftar\AuthController as PendaftarAuthController;
use App\Http\Controllers\Pendaftar\DashboardController as PendaftarDashboardController;

// Public landing page
Route::get('/login', [LandingController::class, 'showLoginForm'])->name('login');
Route::get('/ppdb', [LandingController::class, 'index'])->name('ppdb.landing');
Route::get('/ppdb/berita/{slug}', [LandingController::class, 'showBerita'])->name('ppdb.berita.show');

// Authentication routes
Route::post('/ppdb/login', [LandingController::class, 'login'])->name('ppdb.login');
Route::post('/ppdb/logout', [LandingController::class, 'logout'])->name('ppdb.logout');

// ============================================
// PENDAFTAR ROUTES (Dashboard Calon Siswa)
// ============================================

// Landing & Auth (Guest)
Route::prefix('pendaftar')->name('pendaftar.')->group(function () {
    // Landing page dengan cek NISN
    Route::get('/', [PendaftarAuthController::class, 'landing'])->name('landing');
    Route::post('/cek-nisn', [PendaftarAuthController::class, 'cekNisn'])->name('cek-nisn');
    
    // Registration
    Route::get('/register', [PendaftarAuthController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('/register', [PendaftarAuthController::class, 'register'])->name('register.post');
    Route::get('/register/success', [PendaftarAuthController::class, 'registrationSuccess'])->name('register.success');
    
    // Login
    Route::get('/login', [PendaftarAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PendaftarAuthController::class, 'login'])->name('login.post');
});

// Dashboard Pendaftar (Auth Required)
Route::middleware(['auth'])->prefix('pendaftar')->name('pendaftar.')->group(function () {
    // Logout
    Route::post('/logout', [PendaftarAuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [PendaftarDashboardController::class, 'index'])->name('dashboard');
    
    // Data Pribadi
    Route::get('/data-pribadi', [PendaftarDashboardController::class, 'dataPribadi'])->name('data-pribadi');
    Route::put('/data-pribadi', [PendaftarDashboardController::class, 'updateDataPribadi'])->name('data-pribadi.update');
    
    // Data Orang Tua
    Route::get('/data-ortu', [PendaftarDashboardController::class, 'dataOrtu'])->name('data-ortu');
    Route::put('/data-ortu', [PendaftarDashboardController::class, 'updateDataOrtu'])->name('data-ortu.update');
    
    // Nilai Rapor
    Route::get('/nilai-rapor', [PendaftarDashboardController::class, 'dataNilaiRapor'])->name('nilai-rapor');
    Route::put('/nilai-rapor', [PendaftarDashboardController::class, 'updateNilaiRapor'])->name('nilai-rapor.update');
    
    // Dokumen
    Route::get('/dokumen', [PendaftarDashboardController::class, 'dokumen'])->name('dokumen');
    Route::post('/dokumen', [PendaftarDashboardController::class, 'uploadDokumen'])->name('dokumen.upload');
    Route::delete('/dokumen/{id}', [PendaftarDashboardController::class, 'deleteDokumen'])->name('dokumen.delete');
    
    // Pilihan Program (conditional - only if enabled in jalur)
    Route::get('/pilihan-program', [PendaftarDashboardController::class, 'pilihanProgram'])->name('pilihan-program');
    Route::post('/pilihan-program', [PendaftarDashboardController::class, 'storePilihanProgram'])->name('pilihan-program.store');
    
    // Finalisasi Pendaftaran
    Route::get('/finalisasi', [PendaftarDashboardController::class, 'finalisasi'])->name('finalisasi');
    Route::post('/finalisasi', [PendaftarDashboardController::class, 'storeFinalisasi'])->name('finalisasi.store');
    
    // Profile & Password
    Route::get('/profile', [PendaftarDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [PendaftarDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/password', [PendaftarDashboardController::class, 'password'])->name('password');
    Route::put('/password', [PendaftarDashboardController::class, 'updatePassword'])->name('password.update');
    
    // Status & Cetak
    Route::get('/status', [PendaftarDashboardController::class, 'status'])->name('status');
    Route::get('/cetak-bukti', [PendaftarDashboardController::class, 'cetakBukti'])->name('cetak-bukti');
});

// ============================================
// OLD REGISTRATION ROUTES (5-step process) - Backward compatibility
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/ppdb/register/step1', [RegisterController::class, 'step1'])->name('ppdb.register.step1');
    Route::post('/ppdb/register/step1', [RegisterController::class, 'validateNisn'])->name('ppdb.register.step1.validate');

    Route::get('/ppdb/register/step2', [RegisterController::class, 'step2'])->name('ppdb.register.step2');
    Route::post('/ppdb/register/step2', [RegisterController::class, 'storePersonalData'])->name('ppdb.register.step2.store');

    Route::get('/ppdb/register/step3', [RegisterController::class, 'step3'])->name('ppdb.register.step3');
    Route::post('/ppdb/register/step3', [RegisterController::class, 'storeParentData'])->name('ppdb.register.step3.store');

    Route::get('/ppdb/register/step4', [RegisterController::class, 'step4'])->name('ppdb.register.step4');
    Route::post('/ppdb/register/step4', [RegisterController::class, 'uploadDocuments'])->name('ppdb.register.step4.store');

    Route::get('/ppdb/register/step5', [RegisterController::class, 'step5'])->name('ppdb.register.step5');
    Route::post('/ppdb/register/step5', [RegisterController::class, 'confirmRegistration'])->name('ppdb.register.step5.confirm');

    Route::get('/ppdb/register/success', [RegisterController::class, 'success'])->name('ppdb.register.success');
    
    // API for cascading address dropdowns
    Route::get('/ppdb/api/kabupaten', [RegisterController::class, 'getKabupaten'])->name('ppdb.api.kabupaten');
    Route::get('/ppdb/api/kecamatan', [RegisterController::class, 'getKecamatan'])->name('ppdb.api.kecamatan');
    Route::get('/ppdb/api/kelurahan', [RegisterController::class, 'getKelurahan'])->name('ppdb.api.kelurahan');
    
    // API for NISN check
    Route::post('/ppdb/api/cek-nisn', [RegisterController::class, 'apiCekNisn'])->name('ppdb.api.cek-nisn');
});

// Calon Siswa Dashboard (protected with auth)
Route::middleware('auth')->group(function () {
    Route::get('/ppdb/dashboard', [DashboardController::class, 'index'])->name('ppdb.dashboard');
    Route::get('/ppdb/bukti-registrasi', [DashboardController::class, 'buktiRegistrasi'])->name('ppdb.bukti-registrasi');
    Route::post('/ppdb/bukti-registrasi/print', [DashboardController::class, 'printBuktiRegistrasi'])->name('ppdb.bukti-registrasi.print');
});

// ============================================
// OPERATOR ROUTES - Redirect to Admin
// (Backward compatibility - sekarang unified di /admin)
// ============================================
Route::middleware(['auth'])->prefix('operator')->name('operator.')->group(function () {
    // Redirect semua route operator ke admin
    Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/pendaftar', fn() => redirect()->route('admin.pendaftar.index'))->name('pendaftar.index');
    Route::get('/pendaftar/{id}', fn($id) => redirect()->route('admin.pendaftar.show', $id))->name('pendaftar.show');
    Route::get('/verifikasi-dokumen', fn() => redirect()->route('admin.pendaftar.index'))->name('verifikasi-dokumen.index');
});

// ============================================
// ADMIN ROUTES (untuk role admin + operator + verifikator)
// ============================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - Accessible by all (admin, operator, verifikator)
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ============================================
    // SHARED ROUTES - Accessible by operator/verifikator
    // ============================================
    
    // ---- PROFILE (Shared access) ----
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.updateProfile');
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.deletePhoto');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    
    // ---- PENDAFTAR (Shared access) ----
    Route::get('/pendaftar', [PendaftarController::class, 'index'])->name('pendaftar.index');
    Route::get('/pendaftar/{id}', [PendaftarController::class, 'show'])->name('pendaftar.show');
    Route::get('/pendaftar/{id}/edit', [PendaftarController::class, 'edit'])->name('pendaftar.edit');
    Route::put('/pendaftar/{id}', [PendaftarController::class, 'update'])->name('pendaftar.update');
    Route::delete('/pendaftar/{id}', [PendaftarController::class, 'destroy'])->name('pendaftar.destroy');
    Route::post('/pendaftar/{id}/reset-password', [PendaftarController::class, 'resetPassword'])->name('pendaftar.reset-password');
    Route::get('/pendaftar/{id}/show-password', [PendaftarController::class, 'showPassword'])->name('pendaftar.show-password');
    Route::get('/pendaftar/{id}/dokumen-list', [PendaftarController::class, 'getDokumenList'])->name('pendaftar.dokumen-list');
    Route::post('/pendaftar/{id}/verify', [PendaftarController::class, 'verify'])->name('pendaftar.verify');
    Route::post('/pendaftar/{id}/reject', [PendaftarController::class, 'reject'])->name('pendaftar.reject');
    Route::post('/pendaftar/{id}/approve', [PendaftarController::class, 'approve'])->name('pendaftar.approve');
    
    // Verifikasi Dokumen Pendaftar (Shared access)
    Route::post('/pendaftar/dokumen/{id}/approve', [PendaftarController::class, 'approveDokumen'])->name('pendaftar.dokumen.approve');
    Route::post('/pendaftar/dokumen/{id}/reject', [PendaftarController::class, 'rejectDokumen'])->name('pendaftar.dokumen.reject');
    Route::post('/pendaftar/dokumen/{id}/revisi', [PendaftarController::class, 'revisiDokumen'])->name('pendaftar.dokumen.revisi');
    Route::post('/pendaftar/dokumen/{id}/cancel', [PendaftarController::class, 'cancelVerifikasi'])->name('pendaftar.dokumen.cancel');
    Route::post('/pendaftar/dokumen/{id}/cancel-revisi', [PendaftarController::class, 'cancelRevisi'])->name('pendaftar.dokumen.cancel-revisi');

    // ============================================
    // ADMIN-ONLY ROUTES - Only accessible by admin
    // ============================================
    Route::middleware('can:admin')->group(function () {
        
        // ---- PENGATURAN SEKOLAH ----
        Route::prefix('sekolah')->name('sekolah.')->group(function () {
            Route::get('/', [SekolahSettingsController::class, 'index'])->name('index');
            Route::post('/', [SekolahSettingsController::class, 'update'])->name('update');
            // Laravolt Indonesia API untuk cascade dropdown
            Route::get('/cities', [SekolahSettingsController::class, 'getCities'])->name('cities');
            Route::get('/districts', [SekolahSettingsController::class, 'getDistricts'])->name('districts');
            Route::get('/villages', [SekolahSettingsController::class, 'getVillages'])->name('villages');
        });

        // ---- TAHUN PELAJARAN ----
        Route::prefix('tahun-pelajaran')->name('tahun-pelajaran.')->group(function () {
            Route::get('/', [TahunPelajaranController::class, 'index'])->name('index');
            Route::get('/create', [TahunPelajaranController::class, 'create'])->name('create');
            Route::post('/', [TahunPelajaranController::class, 'store'])->name('store');
            Route::get('/{tahunPelajaran}/edit', [TahunPelajaranController::class, 'edit'])->name('edit');
            Route::put('/{tahunPelajaran}', [TahunPelajaranController::class, 'update'])->name('update');
            Route::delete('/{tahunPelajaran}', [TahunPelajaranController::class, 'destroy'])->name('destroy');
            Route::post('/{tahunPelajaran}/aktifkan', [TahunPelajaranController::class, 'aktifkan'])->name('aktifkan');
        });

        // ---- SETTINGS GROUP ----
        Route::prefix('settings')->name('settings.')->group(function () {
            // PPDB Settings
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/', [SettingsController::class, 'update'])->name('update');

            // Site Settings (Frontend Configuration) 
            Route::get('/halaman', [SiteSettingsController::class, 'index'])->name('halaman.index');
            Route::post('/halaman/general', [SiteSettingsController::class, 'updateGeneral'])->name('halaman.update.general');
            Route::post('/halaman/social', [SiteSettingsController::class, 'updateSocial'])->name('halaman.update.social');
            Route::post('/halaman/landing', [SiteSettingsController::class, 'updateLanding'])->name('halaman.update.landing');
            Route::post('/halaman/seo', [SiteSettingsController::class, 'updateSeo'])->name('halaman.update.seo');
            Route::post('/halaman/theme', [SiteSettingsController::class, 'updateTheme'])->name('halaman.update.theme');
            Route::post('/halaman/maps', [SiteSettingsController::class, 'updateMaps'])->name('halaman.update.maps');
            Route::post('/halaman/verify-facebook', [SiteSettingsController::class, 'verifyFacebookToken'])->name('halaman.verify-facebook');

            // Berita
            Route::resource('berita', BeritaController::class);
            Route::post('/berita/{berita}/share-facebook', [BeritaController::class, 'shareToFacebook'])->name('berita.share-facebook');
            Route::post('/berita/{berita}/toggle-featured', [BeritaController::class, 'toggleFeatured'])->name('berita.toggle-featured');

            // Slider
            Route::resource('slider', SliderController::class);
            Route::post('/slider/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('slider.toggle-status');

            // Jadwal PPDB
            Route::resource('jadwal', JadwalController::class);
            Route::post('/jadwal/{jadwal}/toggle-status', [JadwalController::class, 'toggleStatus'])->name('jadwal.toggle-status');

            // Alur Pendaftaran
            Route::prefix('alur-pendaftaran')->name('alur-pendaftaran.')->group(function () {
                Route::get('/', [AlurPendaftaranController::class, 'index'])->name('index');
                Route::post('/', [AlurPendaftaranController::class, 'store'])->name('store');
            Route::put('/{alurPendaftaran}', [AlurPendaftaranController::class, 'update'])->name('update');
            Route::delete('/{alurPendaftaran}', [AlurPendaftaranController::class, 'destroy'])->name('destroy');
            Route::post('/update-order', [AlurPendaftaranController::class, 'updateOrder'])->name('update-order');
        });
    });

        // ---- JALUR PENDAFTARAN ----
        Route::prefix('jalur')->name('jalur.')->group(function () {
            // CRUD Jalur
            Route::get('/', [JalurPendaftaranController::class, 'index'])->name('index');
            Route::get('/create', [JalurPendaftaranController::class, 'create'])->name('create');
            Route::post('/', [JalurPendaftaranController::class, 'store'])->name('store');
            Route::get('/{jalur}', [JalurPendaftaranController::class, 'show'])->name('show');
            Route::get('/{jalur}/edit', [JalurPendaftaranController::class, 'edit'])->name('edit');
            Route::put('/{jalur}', [JalurPendaftaranController::class, 'update'])->name('update');
            Route::delete('/{jalur}', [JalurPendaftaranController::class, 'destroy'])->name('destroy');
            Route::post('/{jalur}/duplicate', [JalurPendaftaranController::class, 'duplicate'])->name('duplicate');
            
            // Jalur Status Actions
            Route::post('/{jalur}/aktifkan', [JalurPendaftaranController::class, 'aktifkanJalur'])->name('aktifkan');
            Route::post('/{jalur}/tutup', [JalurPendaftaranController::class, 'tutupJalur'])->name('tutup');
            Route::post('/{jalur}/selesaikan', [JalurPendaftaranController::class, 'selesaikanJalur'])->name('selesaikan');
            Route::post('/{jalur}/toggle-status', [JalurPendaftaranController::class, 'toggleStatus'])->name('toggle-status');
            
            // Gelombang Management (nested) - Optional sub-periods
            Route::post('/{jalur}/gelombang', [JalurPendaftaranController::class, 'storeGelombang'])->name('gelombang.store');
            Route::put('/{jalur}/gelombang/{gelombang}', [JalurPendaftaranController::class, 'updateGelombang'])->name('gelombang.update');
            Route::delete('/{jalur}/gelombang/{gelombang}', [JalurPendaftaranController::class, 'destroyGelombang'])->name('gelombang.destroy');
            
            // Gelombang Actions
            Route::post('/{jalur}/gelombang/{gelombang}/buka', [JalurPendaftaranController::class, 'bukaGelombang'])->name('gelombang.buka');
            Route::post('/{jalur}/gelombang/{gelombang}/tutup', [JalurPendaftaranController::class, 'tutupGelombang'])->name('gelombang.tutup');
            Route::post('/{jalur}/gelombang/{gelombang}/selesaikan', [JalurPendaftaranController::class, 'selesaikanGelombang'])->name('gelombang.selesaikan');
        });

        // ---- VERIFIKATOR MANAGEMENT ----
        Route::prefix('verifikator')->name('verifikator.')->group(function () {
            Route::get('/', [VerifikatorController::class, 'index'])->name('index');
            Route::post('/assign', [VerifikatorController::class, 'assign'])->name('assign');
            Route::put('/{verifikator}/toggle-status', [VerifikatorController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{verifikator}', [VerifikatorController::class, 'delete'])->name('delete');
        });

        // ---- USER MANAGEMENT ----
        Route::resource('users', UserController::class);

        // ---- ROLE & PERMISSION ----
        Route::resource('roles', RoleController::class);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        // ---- GTK MANAGEMENT ----
        Route::prefix('gtk')->name('gtk.')->group(function () {
            Route::get('/', [GtkController::class, 'index'])->name('index');
            Route::get('/create', [GtkController::class, 'create'])->name('create');
            Route::post('/', [GtkController::class, 'store'])->name('store');
            Route::get('/{id}', [GtkController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [GtkController::class, 'edit'])->name('edit');
            Route::put('/{id}', [GtkController::class, 'update'])->name('update');
            Route::delete('/{id}', [GtkController::class, 'destroy'])->name('destroy');
            Route::post('/sync', [GtkController::class, 'syncFromSimansa'])->name('sync');
            Route::post('/{id}/register', [GtkController::class, 'registerAsUser'])->name('register');
            Route::put('/{id}/update-roles', [GtkController::class, 'updateRoles'])->name('update-roles');
            Route::delete('/{id}/remove', [GtkController::class, 'removeUser'])->name('remove');
            Route::post('/bulk-register', [GtkController::class, 'bulkRegister'])->name('bulk-register');
        });

        // ---- ACTIVITY LOGS ----
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

        // ---- EMIS TOKEN MANAGEMENT ----
        Route::get('/update-emis-token', [EmisTokenController::class, 'index'])->name('update-emis-token.index');
        Route::post('/update-emis-token', [EmisTokenController::class, 'update'])->name('update-emis-token.update');
        
        // ---- WhatsApp API Settings ----
        Route::get('/whatsapp', [PengaturanWaController::class, 'index'])->name('whatsapp.index');
        Route::put('/whatsapp', [PengaturanWaController::class, 'update'])->name('whatsapp.update');
        Route::post('/whatsapp/test-connection', [PengaturanWaController::class, 'testConnection'])->name('whatsapp.test-connection');
        Route::post('/whatsapp/send-test', [PengaturanWaController::class, 'sendTest'])->name('whatsapp.send-test');
        Route::get('/whatsapp/reset-templates', [PengaturanWaController::class, 'resetTemplates'])->name('whatsapp.reset-templates');
        
        // ---- BACKUP & RESTORE ----
        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
            Route::post('/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
            Route::delete('/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('destroy');
        });
        
        // ---- DATA MANAGEMENT (Soft Delete) ----
        Route::prefix('data-management')->name('data.')->group(function () {
            Route::get('/delete-list', [\App\Http\Controllers\Admin\DataManagementController::class, 'deleteList'])->name('delete-list');
            Route::get('/deleted', [\App\Http\Controllers\Admin\DataManagementController::class, 'index'])->name('deleted');
            Route::post('/restore/{id}', [\App\Http\Controllers\Admin\DataManagementController::class, 'restore'])->name('restore');
            Route::post('/restore-bulk', [\App\Http\Controllers\Admin\DataManagementController::class, 'restoreBulk'])->name('restore.bulk');
            Route::delete('/force-delete/{id}', [\App\Http\Controllers\Admin\DataManagementController::class, 'forceDelete'])->name('force.delete');
            Route::delete('/force-delete-bulk', [\App\Http\Controllers\Admin\DataManagementController::class, 'forceDeleteBulk'])->name('force.delete.bulk');
            Route::post('/bulk-delete-gelombang', [\App\Http\Controllers\Admin\DataManagementController::class, 'bulkDeleteByGelombang'])->name('bulk.delete.gelombang');
        });
    }); // End of can:admin middleware group
}); // End of admin routes group

// ============================================
// BACKWARD COMPATIBILITY - Redirect old routes
// ============================================
Route::middleware(['auth', 'admin'])->prefix('admin/ppdb')->name('admin.ppdb.')->group(function () {
    // Redirect old admin.ppdb.* routes to new admin.* routes
    Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/settings', fn() => redirect()->route('admin.settings.index'))->name('settings.index');
    Route::get('/pendaftar', fn() => redirect()->route('admin.pendaftar.index'))->name('pendaftar.index');
    Route::get('/pendaftar/{id}', fn($id) => redirect()->route('admin.pendaftar.show', $id))->name('pendaftar.show');
    Route::get('/berita', fn() => redirect()->route('admin.settings.berita.index'))->name('berita.index');
    Route::get('/slider', fn() => redirect()->route('admin.settings.slider.index'))->name('slider.index');
    Route::get('/jadwal', fn() => redirect()->route('admin.settings.jadwal.index'))->name('jadwal.index');
    Route::get('/users', fn() => redirect()->route('admin.users.index'))->name('users.index');
    Route::get('/roles', fn() => redirect()->route('admin.roles.index'))->name('roles.index');
    Route::get('/verifikator', fn() => redirect()->route('admin.verifikator.index'))->name('verifikator.index');
    Route::get('/logs', fn() => redirect()->route('admin.logs.index'))->name('logs.index');
    Route::get('/gtk', fn() => redirect()->route('admin.gtk.index'))->name('gtk.index');
    Route::get('/site-settings', fn() => redirect()->route('admin.settings.halaman.index'))->name('site-settings.index');
});
