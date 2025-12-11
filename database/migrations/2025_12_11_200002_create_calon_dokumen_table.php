<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create calon_dokumen table for PPDB document uploads
 * Structure similar to dokumen_siswa in SIMANSAV3 but with PPDB-specific fields
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_dokumen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            
            // ============================================
            // DOKUMEN INFO
            // ============================================
            $table->string('jenis_dokumen', 50); // kk, akta_kelahiran, ijazah, skhun, foto, kip, pkh, rapor, dll
            $table->string('nama_dokumen', 100); // Nama tampilan dokumen
            $table->string('nama_file', 255); // Original filename
            $table->string('file_path', 500); // Storage path
            $table->string('file_size', 20)->nullable(); // File size in KB
            $table->string('mime_type', 100)->nullable();
            $table->string('storage_disk', 20)->default('public'); // local, public, s3
            
            // ============================================
            // VERIFIKASI DOKUMEN
            // ============================================
            $table->enum('status_verifikasi', ['pending', 'valid', 'invalid', 'revision'])->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            
            // ============================================
            // REQUIRED FLAG
            // ============================================
            $table->boolean('is_required')->default(true);
            
            // ============================================
            // KETERANGAN
            // ============================================
            $table->text('keterangan')->nullable();
            
            // ============================================
            // TIMESTAMPS & SOFT DELETE
            // ============================================
            $table->timestamps();
            $table->softDeletes();
            
            // ============================================
            // INDEXES
            // ============================================
            $table->index('calon_siswa_id');
            $table->index('jenis_dokumen');
            $table->index('status_verifikasi');
            $table->index(['calon_siswa_id', 'jenis_dokumen'], 'calon_dokumen_siswa_jenis_idx');
            
            // Foreign key for verified_by
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_dokumen');
    }
};
