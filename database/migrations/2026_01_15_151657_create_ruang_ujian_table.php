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
        Schema::create('ruang_ujian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            $table->integer('nomor_ruang');
            $table->string('nama_ruang', 100);
            $table->integer('kapasitas');
            $table->integer('jumlah_peserta')->default(0);
            $table->timestamps();
            
            $table->index('sesi_ujian_id');
            $table->unique(['sesi_ujian_id', 'nomor_ruang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruang_ujian');
    }
};
