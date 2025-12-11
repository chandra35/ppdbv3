<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create calon_ortus table with same structure as ortu table in SIMANSAV3
 * This enables easy data transfer between PPDB and SIMANSA systems
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_ortus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            
            // ============================================
            // NOMOR KARTU KELUARGA
            // ============================================
            $table->string('no_kk', 16)->nullable();
            
            // ============================================
            // DATA AYAH (SAME AS SIMANSAV3)
            // ============================================
            $table->enum('status_ayah', ['masih_hidup', 'meninggal'])->default('masih_hidup');
            $table->string('nik_ayah', 16)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('tempat_lahir_ayah', 100)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('pendidikan_ayah', 50)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('penghasilan_ayah', 50)->nullable();
            $table->string('hp_ayah', 15)->nullable();
            
            // ============================================
            // DATA IBU (SAME AS SIMANSAV3)
            // ============================================
            $table->enum('status_ibu', ['masih_hidup', 'meninggal'])->default('masih_hidup');
            $table->string('nik_ibu', 16)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('tempat_lahir_ibu', 100)->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('pendidikan_ibu', 50)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('penghasilan_ibu', 50)->nullable();
            $table->string('hp_ibu', 15)->nullable();
            
            // ============================================
            // DATA WALI (OPTIONAL)
            // ============================================
            $table->boolean('tinggal_dengan_wali')->default(false);
            $table->string('nik_wali', 16)->nullable();
            $table->string('nama_wali', 100)->nullable();
            $table->string('hubungan_wali', 50)->nullable(); // Paman, Bibi, Kakek, Nenek, dll
            $table->string('tempat_lahir_wali', 100)->nullable();
            $table->date('tanggal_lahir_wali')->nullable();
            $table->string('pendidikan_wali', 50)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->string('penghasilan_wali', 50)->nullable();
            $table->string('hp_wali', 15)->nullable();
            
            // ============================================
            // ALAMAT ORANG TUA (SAME AS SIMANSAV3)
            // ============================================
            $table->text('alamat_ortu')->nullable();
            $table->string('rt_ortu', 5)->nullable();
            $table->string('rw_ortu', 5)->nullable();
            $table->char('provinsi_id', 2)->nullable();
            $table->char('kabupaten_id', 4)->nullable();
            $table->char('kecamatan_id', 7)->nullable();
            $table->char('kelurahan_id', 10)->nullable();
            $table->string('kodepos', 10)->nullable();
            
            // ============================================
            // TIMESTAMPS & SOFT DELETE
            // ============================================
            $table->timestamps();
            $table->softDeletes();
            
            // ============================================
            // INDEXES
            // ============================================
            $table->index('calon_siswa_id');
            $table->index('no_kk');
            
            // Foreign keys for Laravolt Indonesia
            $table->foreign('provinsi_id')->references('code')->on('indonesia_provinces')->nullOnDelete();
            $table->foreign('kabupaten_id')->references('code')->on('indonesia_cities')->nullOnDelete();
            $table->foreign('kecamatan_id')->references('code')->on('indonesia_districts')->nullOnDelete();
            $table->foreign('kelurahan_id')->references('code')->on('indonesia_villages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_ortus');
    }
};
