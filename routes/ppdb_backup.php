<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ppdb\LandingController;
use App\Http\Controllers\Ppdb\RegisterController;
use App\Http\Controllers\Ppdb\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\VerifikatorController;
use App\Http\Controllers\Admin\PendaftarController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\GtkController;

// Public landing page
Route::get('/login', [LandingController::class, 'showLoginForm'])->name('login');
Route::get('/ppdb', [LandingController::class, 'index'])->name('ppdb.landing');

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

// Admin routes (protected with auth + admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin/ppdb')->name('admin.ppdb.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Verifikator
    Route::get('/verifikator', [VerifikatorController::class, 'index'])->name('verifikator.index');
    Route::post('/verifikator', [VerifikatorController::class, 'assign'])->name('verifikator.assign');
    Route::delete('/verifikator/{id}', [VerifikatorController::class, 'delete'])->name('verifikator.delete');
    
    // Pendaftar
    Route::get('/pendaftar', [PendaftarController::class, 'index'])->name('pendaftar.index');
    Route::get('/pendaftar/{id}', [PendaftarController::class, 'show'])->name('pendaftar.show');
    Route::post('/pendaftar/{id}/verify', [PendaftarController::class, 'verify'])->name('pendaftar.verify');
    Route::post('/pendaftar/{id}/reject', [PendaftarController::class, 'reject'])->name('pendaftar.reject');
    Route::post('/pendaftar/{id}/approve', [PendaftarController::class, 'approve'])->name('pendaftar.approve');
    
    // Verifikasi Dokumen
    Route::get('/verifikasi-dokumen', [PendaftarController::class, 'verifikasiDokumen'])->name('verifikasi-dokumen.index');
    Route::get('/verifikasi-dokumen/{id}', [PendaftarController::class, 'verifikasiDokumenDetail'])->name('verifikasi-dokumen.show');
    Route::post('/verifikasi-dokumen/{id}', [PendaftarController::class, 'updateVerifikasiDokumen'])->name('verifikasi-dokumen.update');
    
    // Berita
    Route::resource('berita', BeritaController::class);
    
    // Slider
    Route::resource('slider', SliderController::class);
    
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



