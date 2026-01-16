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
        Schema::create('nilai_seleksi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignUuid('ruang_ujian_id')->constrained('ruang_ujian')->cascadeOnDelete();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            $table->foreignUuid('penguji_id')->constrained('users')->cascadeOnDelete();
            
            // Komponen Nilai
            $table->decimal('nilai_wawancara', 5, 2)->nullable();
            $table->decimal('nilai_baca_quran', 5, 2)->nullable();
            $table->decimal('nilai_tulis_quran', 5, 2)->nullable();
            $table->decimal('nilai_hafalan', 5, 2)->nullable();
            $table->integer('jumlah_juz_hafalan')->nullable()->comment('Jumlah juz yang dihafal');
            
            // Nilai Total
            $table->decimal('total_nilai', 5, 2)->nullable();
            
            // Catatan & Status
            $table->text('catatan_penguji')->nullable();
            $table->enum('status', ['draft', 'submitted', 'verified', 'revision'])->default('draft');
            
            // Verifikasi
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            
            $table->timestamps();
            
            $table->index('sesi_ujian_id');
            $table->index('ruang_ujian_id');
            $table->index('calon_siswa_id');
            $table->index('penguji_id');
            $table->index('status');
            $table->unique(['sesi_ujian_id', 'calon_siswa_id', 'penguji_id'], 'nilai_seleksi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_seleksi');
    }
};
