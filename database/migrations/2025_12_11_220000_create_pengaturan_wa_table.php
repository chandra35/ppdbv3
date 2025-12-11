<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for WhatsApp API settings
 * Supports multiple providers: Fonnte, WAblas, Wabotapi, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_wa', function (Blueprint $table) {
            $table->id();
            
            // Provider settings
            $table->enum('provider', ['fonnte', 'wablas', 'wabotapi', 'twilio', 'other'])->default('fonnte');
            $table->string('api_key')->nullable();
            $table->string('api_url')->nullable(); // Custom API URL if needed
            $table->string('sender_number', 20)->nullable(); // Nomor pengirim
            
            // Status
            $table->boolean('is_active')->default(false);
            
            // Message templates
            $table->text('template_registrasi')->nullable(); // Template untuk kredensial baru
            $table->text('template_verifikasi')->nullable(); // Template notif verifikasi
            $table->text('template_diterima')->nullable(); // Template notif diterima
            $table->text('template_ditolak')->nullable(); // Template notif ditolak
            
            // Settings
            $table->json('settings')->nullable(); // Additional settings per provider
            
            // Audit
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_wa');
    }
};
