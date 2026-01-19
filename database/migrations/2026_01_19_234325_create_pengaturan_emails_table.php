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
        Schema::create('pengaturan_email', function (Blueprint $table) {
            $table->id();
            
            // Enable/Disable per jenis notifikasi
            $table->boolean('enable_registrasi')->default(true);
            $table->boolean('enable_revisi')->default(true);
            $table->boolean('enable_nomor_tes')->default(true);
            $table->boolean('enable_diterima')->default(true);
            $table->boolean('enable_ditolak')->default(true);
            
            // Template subject
            $table->string('subject_registrasi')->nullable();
            $table->string('subject_revisi')->nullable();
            $table->string('subject_nomor_tes')->nullable();
            $table->string('subject_diterima')->nullable();
            $table->string('subject_ditolak')->nullable();
            
            // Template body (HTML)
            $table->text('template_registrasi')->nullable();
            $table->text('template_revisi')->nullable();
            $table->text('template_nomor_tes')->nullable();
            $table->text('template_diterima')->nullable();
            $table->text('template_ditolak')->nullable();
            
            // Global settings
            $table->boolean('is_active')->default(true);
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            
            // Footer/signature
            $table->text('footer_text')->nullable();
            
            // Tracking
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_email');
    }
};
