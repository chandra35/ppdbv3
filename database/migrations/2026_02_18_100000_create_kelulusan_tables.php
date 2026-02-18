<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel kelulusan - menyimpan data siswa yang dinyatakan lulus
        Schema::create('kelulusan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calon_siswa_id');
            $table->uuid('tahun_pelajaran_id');
            $table->enum('status', ['lulus', 'tidak_lulus', 'cadangan'])->default('lulus');
            $table->text('catatan')->nullable();
            $table->uuid('diluluskan_oleh')->nullable(); // user_id admin
            $table->timestamp('tanggal_kelulusan')->nullable();
            $table->timestamps();

            $table->foreign('calon_siswa_id')->references('id')->on('calon_siswas')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->onDelete('cascade');
            $table->foreign('diluluskan_oleh')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['calon_siswa_id', 'tahun_pelajaran_id']);
        });

        // Tabel pengaturan kelulusan - link WA group, dokumen, konfigurasi
        Schema::create('kelulusan_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->string('judul_pengumuman')->default('Pengumuman Kelulusan PPDB');
            $table->text('pesan_lulus')->nullable(); // Pesan untuk yang lulus
            $table->text('pesan_tidak_lulus')->nullable(); // Pesan untuk yang tidak lulus
            $table->string('link_grup_wa')->nullable(); // Link group WhatsApp
            $table->string('nama_grup_wa')->nullable();
            $table->text('dokumen_persyaratan')->nullable(); // JSON: daftar dokumen yang perlu disiapkan
            $table->text('template_surat_pernyataan')->nullable(); // Template surat (HTML)
            $table->boolean('tampilkan_pengumuman')->default(false); // Toggle pengumuman
            $table->boolean('tampilkan_link_wa')->default(false);
            $table->boolean('tampilkan_dokumen')->default(false);
            $table->date('tanggal_daftar_ulang_mulai')->nullable();
            $table->date('tanggal_daftar_ulang_selesai')->nullable();
            $table->text('catatan_daftar_ulang')->nullable();
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelulusan_settings');
        Schema::dropIfExists('kelulusan');
    }
};
