<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel nilai_rapor
 * Menyimpan nilai rapor semester 1-5 untuk mapel Matematika, IPA, IPS
 * Nilai akan digunakan untuk perhitungan ranking bersama nilai CBT dan wawancara
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_rapor', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            
            // Semester (1-5, karena semester 6 biasanya belum ada saat pendaftaran)
            $table->tinyInteger('semester')->comment('Semester 1-5');
            
            // Nilai mata pelajaran utama (1-100, integer tanpa koma)
            $table->tinyInteger('matematika')->comment('Nilai Matematika (1-100)');
            $table->tinyInteger('ipa')->comment('Nilai IPA (1-100)');
            $table->tinyInteger('ips')->comment('Nilai IPS (1-100)');
            
            // Rata-rata per semester (auto-calculated)
            $table->decimal('rata_rata', 5, 2)->comment('Rata-rata 3 mapel');
            
            $table->timestamps();
            
            // Indexes
            $table->index('calon_siswa_id');
            $table->unique(['calon_siswa_id', 'semester'], 'unique_siswa_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_rapor');
    }
};
