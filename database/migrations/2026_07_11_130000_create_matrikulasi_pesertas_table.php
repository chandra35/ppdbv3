<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matrikulasi_pesertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            $table->foreignUuid('tahun_pelajaran_id')->nullable()->constrained('tahun_pelajarans')->nullOnDelete();
            $table->foreignUuid('jalur_pendaftaran_id')->nullable()->constrained('jalur_pendaftaran')->nullOnDelete();
            $table->foreignUuid('gelombang_pendaftaran_id')->nullable()->constrained('gelombang_pendaftaran')->nullOnDelete();
            $table->string('kategori', 20)->nullable();
            $table->boolean('is_smart_q')->default(false);
            $table->string('input_text')->nullable();
            $table->unsignedTinyInteger('match_score')->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('calon_siswa_id');
            $table->index(['tahun_pelajaran_id', 'kategori']);
            $table->index('is_smart_q');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matrikulasi_pesertas');
    }
};
