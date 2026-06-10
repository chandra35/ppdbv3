<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registrasis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('calon_siswa_id')->nullable()->constrained('calon_siswas')->nullOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();

            // Data mentah dari Excel
            $table->string('notes', 20)->nullable();          // 4 digit akhir nomor tes
            $table->string('nama_excel')->nullable();
            $table->string('jurusan_excel')->nullable();

            // Hasil registrasi (jurusan Excel jadi acuan akhir)
            $table->string('jurusan_final')->nullable();

            // Smart matching meta
            $table->string('match_status', 30)->default('matched_exact'); // matched_exact|matched_fuzzy|conflict_jurusan|manual
            $table->unsignedTinyInteger('match_score')->default(0);

            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_registrasi')->nullable();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['calon_siswa_id', 'tahun_pelajaran_id'], 'registrasis_calon_tahun_unique');
            $table->index(['tahun_pelajaran_id', 'match_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrasis');
    }
};
