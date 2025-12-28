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
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            // Enable/disable pilihan program feature
            $table->boolean('pilihan_program_aktif')->default(false)->after('status');
            
            // Type: reguler_asrama, jurusan, custom
            $table->enum('pilihan_program_tipe', ['reguler_asrama', 'jurusan', 'custom'])
                  ->default('reguler_asrama')->after('pilihan_program_aktif');
            
            // JSON array untuk store options: ["Reguler", "Asrama"] atau ["IPA", "IPS"]
            $table->json('pilihan_program_options')->nullable()->after('pilihan_program_tipe');
            
            // Catatan/instruksi untuk pendaftar
            $table->text('pilihan_program_catatan')->nullable()->after('pilihan_program_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->dropColumn([
                'pilihan_program_aktif',
                'pilihan_program_tipe',
                'pilihan_program_options',
                'pilihan_program_catatan'
            ]);
        });
    }
};
