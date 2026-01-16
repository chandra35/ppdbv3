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
        Schema::create('penguji_ruang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sesi_ujian_id')->constrained('sesi_ujian')->cascadeOnDelete();
            $table->foreignUuid('ruang_ujian_id')->constrained('ruang_ujian')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_ketua')->default(false)->comment('Ketua penguji ruangan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('sesi_ujian_id');
            $table->index('ruang_ujian_id');
            $table->index('user_id');
            $table->unique(['sesi_ujian_id', 'ruang_ujian_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penguji_ruang');
    }
};
