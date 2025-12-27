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
            // Tambahan data keluarga dari EMIS
            $table->string('nama_ibu', 100)->nullable()->after('agama');
            $table->string('nama_ayah', 100)->nullable()->after('nama_ibu');
            $table->string('status_dalam_keluarga', 50)->nullable()->after('anak_ke'); // Anak kandung, angkat, dll
            
            // Transportasi dan jarak
            $table->string('transportasi', 50)->nullable()->after('kodepos_siswa');
            $table->integer('jarak_ke_sekolah')->nullable()->after('transportasi'); // dalam meter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropColumn([
                'nama_ibu',
                'nama_ayah',
                'status_dalam_keluarga',
                'transportasi',
                'jarak_ke_sekolah',
            ]);
        });
    }
};
