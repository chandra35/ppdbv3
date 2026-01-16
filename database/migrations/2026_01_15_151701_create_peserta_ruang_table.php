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
        Schema::create('peserta_ruang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignUuid('ruang_ujian_id')->constrained('ruang_ujian')->cascadeOnDelete();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            $table->integer('nomor_urut')->comment('Urutan peserta di dalam ruang');
            $table->timestamps();
            
            $table->index('sesi_ujian_id');
            $table->index('ruang_ujian_id');
            $table->unique(['sesi_ujian_id', 'calon_siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_ruang');
    }
};
