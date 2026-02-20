<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_gelombang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calon_siswa_id');
            $table->uuid('dari_gelombang_id');
            $table->uuid('ke_gelombang_id');
            $table->uuid('jalur_pendaftaran_id');
            $table->uuid('tahun_pelajaran_id');
            $table->string('nomor_registrasi_lama', 50)->nullable();
            $table->string('nomor_registrasi_baru', 50)->nullable();
            $table->string('status_kelulusan_sebelumnya', 20)->nullable();
            $table->enum('dipindahkan_oleh', ['pendaftar', 'admin'])->default('pendaftar');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('calon_siswa_id')->references('id')->on('calon_siswas')->onDelete('cascade');
            $table->foreign('dari_gelombang_id')->references('id')->on('gelombang_pendaftaran')->onDelete('cascade');
            $table->foreign('ke_gelombang_id')->references('id')->on('gelombang_pendaftaran')->onDelete('cascade');
            $table->foreign('jalur_pendaftaran_id')->references('id')->on('jalur_pendaftaran')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->onDelete('cascade');

            $table->index(['calon_siswa_id', 'tahun_pelajaran_id']);
            $table->index('jalur_pendaftaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_gelombang');
    }
};
