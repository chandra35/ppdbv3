<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_ujian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id');
            $table->uuid('jalur_pendaftaran_id')->nullable();
            $table->uuid('gelombang_pendaftaran_id')->nullable();
            
            // Tanggal dan Waktu
            $table->date('tanggal_ujian');
            $table->time('jam_mulai')->default('08:00');
            $table->integer('jeda_sesi')->default(30)->comment('Jeda antar sesi dalam menit');
            
            // Konfigurasi CBT
            $table->integer('jumlah_ruang_cbt')->default(1);
            $table->integer('kapasitas_cbt')->default(30);
            $table->integer('durasi_cbt')->default(90)->comment('Durasi CBT dalam menit');
            $table->string('prefix_ruang_cbt', 50)->default('Ruang CBT');
            
            // Konfigurasi Wawancara
            $table->integer('jumlah_ruang_wawancara')->default(1);
            $table->integer('kapasitas_wawancara')->default(15);
            $table->integer('durasi_wawancara')->default(60)->comment('Durasi wawancara dalam menit');
            $table->string('prefix_ruang_wawancara', 50)->default('Ruang Wawancara');
            
            // Hasil Generate
            $table->integer('total_peserta')->default(0);
            $table->integer('total_sesi')->default(0);
            $table->time('estimasi_selesai')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'preview', 'locked'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->uuid('generated_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->uuid('locked_by')->nullable();
            
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian');
    }
};
