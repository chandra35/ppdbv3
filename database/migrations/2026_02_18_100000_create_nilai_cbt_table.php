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
        Schema::create('nilai_cbt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
            
            // Komponen Nilai CBT
            $table->decimal('nilai_mtk', 5, 2)->nullable()->comment('Matematika');
            $table->decimal('nilai_ipa', 5, 2)->nullable()->comment('IPA Terpadu');
            $table->decimal('nilai_ips', 5, 2)->nullable()->comment('IPS Terpadu');
            $table->decimal('nilai_bahasa_inggris', 5, 2)->nullable()->comment('Bahasa Inggris');
            
            // Total / Rata-rata
            $table->decimal('total_nilai', 5, 2)->nullable();
            $table->decimal('rata_rata', 5, 2)->nullable();
            
            // Metadata
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('calon_siswa_id');
            $table->index('tahun_pelajaran_id');
            $table->unique(['calon_siswa_id', 'tahun_pelajaran_id'], 'nilai_cbt_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_cbt');
    }
};
