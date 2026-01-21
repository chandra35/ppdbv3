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
        Schema::create('informasi_pendaftar', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->boolean('is_active')->default(true);
            $table->boolean('tampilkan_modal')->default(true)->comment('Jika true, tampilkan sebagai modal setelah login');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_pendaftar');
    }
};
