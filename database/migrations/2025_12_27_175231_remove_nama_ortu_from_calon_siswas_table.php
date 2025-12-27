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
            $table->dropColumn(['nama_ayah', 'nama_ibu', 'status_dalam_keluarga']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->string('nama_ayah', 100)->nullable()->after('agama');
            $table->string('nama_ibu', 100)->nullable()->after('nama_ayah');
            $table->string('status_dalam_keluarga', 50)->nullable()->after('nama_ibu');
        });
    }
};
