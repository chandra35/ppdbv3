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
        // Tambah setting wajib lokasi di ppdb_settings
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->boolean('wajib_lokasi_registrasi')->default(false)->after('validasi_nisn_aktif');
        });

        // Tambah kolom location_source di calon_siswas untuk tracking asal data lokasi
        Schema::table('calon_siswas', function (Blueprint $table) {
            // Sumber lokasi: gps, ip, manual
            $table->string('registration_location_source', 10)->nullable()->after('registration_browser');
            
            // Data dari IP geolocation sebagai fallback
            $table->string('registration_country', 100)->nullable()->after('registration_region');
            $table->string('registration_isp', 100)->nullable()->after('registration_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn('wajib_lokasi_registrasi');
        });

        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropColumn([
                'registration_location_source',
                'registration_country',
                'registration_isp',
            ]);
        });
    }
};
