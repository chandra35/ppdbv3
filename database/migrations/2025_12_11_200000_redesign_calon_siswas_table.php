<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Redesign calon_siswas table to match siswa table structure in SIMANSAV3
 * This enables easy data transfer between PPDB and SIMANSA systems
 */
return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop dependent table first
        Schema::dropIfExists('calon_dokumens');
        
        // Drop existing table and recreate with new structure
        Schema::dropIfExists('calon_siswas');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        Schema::create('calon_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // ============================================
            // PPDB SPECIFIC FIELDS
            // ============================================
            $table->foreignUuid('jalur_pendaftaran_id')->nullable()->constrained('jalur_pendaftaran')->nullOnDelete();
            $table->foreignUuid('gelombang_pendaftaran_id')->nullable()->constrained('gelombang_pendaftaran')->nullOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajarans')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Nomor Pendaftaran & Registrasi
            $table->string('nomor_registrasi', 50)->unique()->nullable();
            $table->dateTime('tanggal_registrasi')->nullable();
            
            // Status PPDB
            $table->enum('status_verifikasi', ['pending', 'verified', 'revision', 'rejected'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            
            $table->enum('status_admisi', ['pending', 'diterima', 'cadangan', 'ditolak'])->default('pending');
            $table->text('catatan_admisi')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            
            // Nilai & Ranking (opsional)
            $table->decimal('nilai_tes', 5, 2)->nullable();
            $table->decimal('nilai_wawancara', 5, 2)->nullable();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable();
            $table->integer('ranking')->nullable();
            
            // ============================================
            // SISWA FIELDS (SAME AS SIMANSAV3)
            // ============================================
            
            // Identitas Utama
            $table->string('nisn', 10)->unique();
            $table->boolean('nisn_valid')->default(false);
            $table->string('nik', 16)->nullable();
            $table->string('nama_lengkap', 100);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            
            // Data Tambahan
            $table->integer('jumlah_saudara')->nullable();
            $table->integer('anak_ke')->nullable();
            $table->string('hobi', 100)->nullable();
            $table->string('cita_cita', 100)->nullable();
            
            // Kontak
            $table->string('nomor_hp', 15)->nullable();
            $table->string('email', 100)->unique();
            
            // Foto
            $table->string('foto_profile')->nullable();
            
            // Asal Sekolah
            $table->string('npsn_asal_sekolah', 8)->nullable();
            $table->string('nama_sekolah_asal', 150)->nullable(); // Untuk yang tidak ada di database sekolah
            
            // ============================================
            // ALAMAT SISWA (SAME AS SIMANSAV3)
            // ============================================
            $table->boolean('alamat_sama_ortu')->default(true);
            $table->enum('jenis_tempat_tinggal', ['Bersama Orang Tua', 'Asrama', 'Kost/Kontrakan', 'Saudara'])->nullable();
            $table->text('alamat_siswa')->nullable();
            $table->string('rt_siswa', 5)->nullable();
            $table->string('rw_siswa', 5)->nullable();
            $table->char('provinsi_id_siswa', 2)->nullable();
            $table->char('kabupaten_id_siswa', 4)->nullable();
            $table->char('kecamatan_id_siswa', 7)->nullable();
            $table->char('kelurahan_id_siswa', 10)->nullable();
            $table->string('kodepos_siswa', 10)->nullable();
            
            // ============================================
            // DATA COMPLETION STATUS
            // ============================================
            $table->boolean('data_diri_completed')->default(false);
            $table->boolean('data_ortu_completed')->default(false);
            $table->boolean('data_dokumen_completed')->default(false);
            
            // ============================================
            // TIMESTAMPS & SOFT DELETE
            // ============================================
            $table->timestamps();
            $table->softDeletes();
            
            // ============================================
            // INDEXES
            // ============================================
            $table->index('status_verifikasi');
            $table->index('status_admisi');
            $table->index('tahun_pelajaran_id');
            $table->index('jalur_pendaftaran_id');
            $table->index('gelombang_pendaftaran_id');
            $table->index('nomor_registrasi');
            $table->index(['jalur_pendaftaran_id', 'gelombang_pendaftaran_id'], 'calon_siswas_jalur_gelombang_idx');
            
            // Foreign keys for Laravolt Indonesia
            $table->foreign('provinsi_id_siswa')->references('code')->on('indonesia_provinces')->nullOnDelete();
            $table->foreign('kabupaten_id_siswa')->references('code')->on('indonesia_cities')->nullOnDelete();
            $table->foreign('kecamatan_id_siswa')->references('code')->on('indonesia_districts')->nullOnDelete();
            $table->foreign('kelurahan_id_siswa')->references('code')->on('indonesia_villages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_siswas');
    }
};
