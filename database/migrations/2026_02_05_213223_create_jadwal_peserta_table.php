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
        Schema::create('jadwal_peserta', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jadwal_ujian_id');
            $table->uuid('calon_siswa_id');
            
            // Jadwal CBT
            $table->uuid('sesi_cbt_id')->nullable();
            $table->uuid('ruang_cbt_id')->nullable();
            $table->integer('nomor_urut_cbt')->nullable();
            
            // Jadwal Wawancara
            $table->uuid('sesi_wawancara_id')->nullable();
            $table->uuid('ruang_wawancara_id')->nullable();
            $table->integer('nomor_urut_wawancara')->nullable();
            
            // Grup (A = CBT dulu, B = Wawancara dulu)
            $table->enum('grup', ['A', 'B'])->default('A');
            $table->integer('nomor_gelombang')->default(1);
            
            $table->timestamps();
            
            $table->foreign('jadwal_ujian_id')->references('id')->on('jadwal_ujian')->onDelete('cascade');
            $table->foreign('calon_siswa_id')->references('id')->on('calon_siswas')->onDelete('cascade');
            
            $table->unique(['jadwal_ujian_id', 'calon_siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_peserta');
    }
};
