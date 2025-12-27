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
        Schema::create('gtks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic Info
            $table->string('nama_lengkap');
            $table->string('nip', 18)->nullable()->unique();
            $table->string('nuptk', 16)->nullable();
            $table->string('nik', 16)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            
            // Contact
            $table->string('email')->nullable()->unique();
            $table->string('nomor_hp')->nullable();
            
            // PTK Info
            $table->enum('kategori_ptk', ['Pendidik', 'Tenaga Kependidikan'])->nullable();
            $table->string('jenis_ptk')->nullable();
            $table->string('jabatan')->nullable();
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'GTY', 'PTY', 'Honorer'])->nullable();
            
            // Sync Info
            $table->string('source')->default('manual'); // 'manual' or 'simansa'
            $table->timestamp('synced_at')->nullable();
            $table->string('simansa_id')->nullable()->comment('ID from SIMANSA if synced');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('email');
            $table->index('nip');
            $table->index('source');
            $table->index('simansa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtks');
    }
};
