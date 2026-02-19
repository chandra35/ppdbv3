<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envelope_open_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calon_siswa_id');
            $table->uuid('user_id');
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('opened_at');
            $table->timestamps();

            $table->foreign('calon_siswa_id')->references('id')->on('calon_siswas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->onDelete('set null');

            // Satu pendaftar hanya bisa buka amplop sekali per tahun pelajaran
            $table->unique(['calon_siswa_id', 'tahun_pelajaran_id'], 'envelope_unique_per_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envelope_open_logs');
    }
};
