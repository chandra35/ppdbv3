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
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->foreignUuid('jalur_pendaftaran_id')
                ->nullable()
                ->after('id')
                ->constrained('jalur_pendaftaran')
                ->nullOnDelete();
            
            $table->foreignUuid('gelombang_pendaftaran_id')
                ->nullable()
                ->after('jalur_pendaftaran_id')
                ->constrained('gelombang_pendaftaran')
                ->nullOnDelete();

            // Index untuk performa query
            $table->index(['jalur_pendaftaran_id', 'gelombang_pendaftaran_id'], 'calon_siswas_jalur_gelombang_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropIndex('calon_siswas_jalur_gelombang_index');
            $table->dropForeign(['gelombang_pendaftaran_id']);
            $table->dropForeign(['jalur_pendaftaran_id']);
            $table->dropColumn(['jalur_pendaftaran_id', 'gelombang_pendaftaran_id']);
        });
    }
};
