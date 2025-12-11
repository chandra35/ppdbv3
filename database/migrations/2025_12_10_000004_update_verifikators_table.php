<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_verifikators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Foreign Keys - using UUID
            $table->uuid('gtk_id');
            $table->uuid('ppdb_settings_id');
            
            // Jenis dokumen yang diverifikasi (JSON array)
            $table->json('jenis_dokumen_aktif')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes & Constraints
            $table->index(['gtk_id', 'ppdb_settings_id']);
            $table->index('is_active');
            
            // Unique constraint
            $table->unique(['gtk_id', 'ppdb_settings_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_verifikators');
    }
};
