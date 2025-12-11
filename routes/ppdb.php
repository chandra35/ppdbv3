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
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\PendaftarController as OperatorPendaftarController;

// Public landing page
Route::get('/login', [LandingController::class, 'showLoginForm'])->name('login');
Route::get('/ppdb', [LandingController::class, 'index'])->name('ppdb.landing');
Route::get('/ppdb/berita/{slug}', [LandingController::class, 'showBerita'])->name('ppdb.berita.show');

// Authentication routes
Route::post('/ppdb/login', [LandingController::class, 'login'])->name('ppdb.login');
Route::post('/ppdb/logout', [LandingController::class, 'logout'])->name('ppdb.logout');

// Registration routes (4-step process)
Route::middleware('guest')->group(function () {
    Route::get('/ppdb/register/step1', [RegisterController::class, 'step1'])->name('ppdb.register.step1');
    Route::post('/ppdb/register/step1', [RegisterController::class, 'validateNisn'])->name('ppdb.register.step1.validate');

    Route::get('/ppdb/register/step2', [RegisterController::class, 'step2'])->name('ppdb.register.step2');
    Route::post('/ppdb/register/step2', [RegisterController::class, 'storePersonalData'])->name('ppdb.register.step2.store');

    Route::get('/ppdb/register/step3', [RegisterController::class, 'step3'])->name('ppdb.register.step3');
    Route::post('/ppdb/register/step3', [RegisterController::class, 'uploadDocuments'])->name('ppdb.register.step3.store');

    Route::get('/ppdb/register/step4', [RegisterController::class, 'step4'])->name('ppdb.register.step4');
    Route::post('/ppdb/register/step4', [RegisterController::class, 'confirmRegistration'])->name('ppdb.register.step4.confirm');

    Route::get('/ppdb/register/success', [RegisterController::class, 'success'])->name('ppdb.register.success');
});

// Calon Siswa Dashboard (protected with auth)
Route::middleware('auth')->group(function () {
    Route::get('/ppdb/dashboard', [DashboardController::class, 'index'])->name('ppdb.dashboard');
    Route::get('/ppdb/bukti-registrasi', [DashboardController::class, 'buktiRegistrasi'])->name('ppdb.bukti-registrasi');
    Route::post('/ppdb/bukti-registrasi/print', [DashboardController::class, 'printBuktiRegistrasi'])->name('ppdb.bukti-registrasi.print');
});

// ============================================
// OPERATOR ROUTES (untuk role operator/verifikator)
// ============================================
Route::middleware(['auth', 'operator'])->prefix('operator')->name('operator.')->group(function () {
    // Dashboard Operator
    Route::get('/', [OperatorDashboardController::class, 'index'])->name('dashboard');

    // Pendaftar Management
    Route::get('/pendaftar', [OperatorPendaftarController::class, 'index'])->name('pendaftar.index');
    Route::get('/pendaftar/{id}', [OperatorPendaftarController::class, 'show'])->name('pendaftar.show');
    Route::post('/pendaftar/{id}/verify', [OperatorPendaftarController::class, 'verify'])->name('pendaftar.verify');
    Route::post('/pendaftar/{id}/reject', [OperatorPendaftarController::class, 'reject'])->name('pendaftar.reject');

    // Verifikasi Dokumen
    Route::get('/verifikasi-dokumen', [OperatorPendaftarController::class, 'verifikasiDokumen'])->name('verifikasi-dokumen.index');
    Route::get('/verifikasi-dokumen/{id}', [OperatorPendaftarController::class, 'verifikasiDokumenDetail'])->name('verifikasi-dokumen.show');
    Route::post('/verifikasi-dokumen/{id}', [OperatorPendaftarController::class, 'updateVerifikasiDokumen'])->name('verifikasi-dokumen.update');
});

// ============================================
// ADMIN ROUTES (untuk role admin)
// ============================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Admin
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

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

    // ---- PENDAFTAR (Admin view - bisa approve/reject final) ----
    Route::get('/pendaftar', [PendaftarController::class, 'index'])->name('pendaftar.index');
    Route::get('/pendaftar/{id}', [PendaftarController::class, 'show'])->name('pendaftar.show');
    Route::post('/pendaftar/{id}/verify', [PendaftarController::class, 'verify'])->name('pendaftar.verify');
    Route::post('/pendaftar/{id}/reject', [PendaftarController::class, 'reject'])->name('pendaftar.reject');
    Route::post('/pendaftar/{id}/approve', [PendaftarController::class, 'approve'])->name('pendaftar.approve');

    // Verifikator
    Route::get('/verifikator', [VerifikatorController::class, 'index'])->name('verifikator.index');
    Route::post('/verifikator', [VerifikatorController::class, 'assign'])->name('verifikator.assign');
    Route::delete('/verifikator/{id}', [VerifikatorController::class, 'delete'])->name('verifikator.delete');

    // User Management
    Route::resource('users', UserController::class);

    // Role Management
    Route::resource('roles', RoleController::class);
    Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

    // Activity Logs
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{id}', [ActivityLogController::class, 'show'])->name('logs.show');
    Route::delete('/logs/clear', [ActivityLogController::class, 'clear'])->name('logs.clear');

    // GTK Management (from SIMANSA)
    Route::get('/gtk', [GtkController::class, 'index'])->name('gtk.index');
    Route::get('/gtk/{id}', [GtkController::class, 'show'])->name('gtk.show');
    Route::post('/gtk/{id}/register', [GtkController::class, 'registerAsUser'])->name('gtk.register');
    Route::put('/gtk/{id}/update-roles', [GtkController::class, 'updateRoles'])->name('gtk.update-roles');
    Route::delete('/gtk/{id}/remove', [GtkController::class, 'removeUser'])->name('gtk.remove');
    Route::post('/gtk/bulk-register', [GtkController::class, 'bulkRegister'])->name('gtk.bulk-register');
});

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
