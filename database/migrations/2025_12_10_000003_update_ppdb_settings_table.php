<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('tahun_pelajaran_id');
            
            // Informasi PPDB
            $table->string('jenjang_target', 50); // 'MAN', 'MA', 'SMA', dll
            $table->integer('kuota_penerimaan')->default(200);
            $table->date('tanggal_dibuka');
            $table->date('tanggal_ditutup');
            $table->boolean('status_pendaftaran')->default(true);
            
            // Validasi NISN
            $table->boolean('validasi_nisn_aktif')->default(true);
            $table->string('grade_minimum', 50); // 'Grade 9 SMP/MTs'
            $table->boolean('izinkan_grade_lebih_tinggi')->default(false);
            $table->boolean('cegah_pendaftar_ganda')->default(true);
            
            // Dokumen yang aktif (JSON)
            $table->json('dokumen_aktif')->nullable();
            
            // Format nomor registrasi
            $table->string('nomor_registrasi_prefix', 20)->default('PPDB'); // Format: PPDB-2025-0001
            $table->integer('nomor_registrasi_counter')->default(0); // Counter untuk nomor registrasi
            
            $table->timestamps();
            
            // Indexes
            $table->index('tahun_pelajaran_id');
            $table->index('status_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_settings');
    }
};
