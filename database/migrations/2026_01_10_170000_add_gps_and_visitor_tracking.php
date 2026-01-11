<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add GPS coordinates to calon_siswas for tracking registration location
 * and link visitor_logs to calon_siswas for registration tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add GPS coordinates to calon_siswas
        Schema::table('calon_siswas', function (Blueprint $table) {
            // GPS coordinates when registering
            $table->decimal('registration_latitude', 10, 8)->nullable()->after('tanggal_registrasi');
            $table->decimal('registration_longitude', 11, 8)->nullable()->after('registration_latitude');
            $table->decimal('registration_altitude', 8, 2)->nullable()->after('registration_longitude');
            $table->decimal('registration_accuracy', 8, 2)->nullable()->after('registration_altitude');
            $table->string('registration_address', 500)->nullable()->after('registration_accuracy');
            $table->string('registration_city', 100)->nullable()->after('registration_address');
            $table->string('registration_region', 100)->nullable()->after('registration_city');
            
            // IP and device info during registration
            $table->string('registration_ip', 45)->nullable()->after('registration_region');
            $table->string('registration_device', 20)->nullable()->after('registration_ip');
            $table->string('registration_browser', 100)->nullable()->after('registration_device');
            
            // Visitor session tracking
            $table->string('visitor_session_id')->nullable()->after('registration_browser');
            
            // Index for GPS coordinates
            $table->index(['registration_latitude', 'registration_longitude'], 'calon_siswas_gps_idx');
        });

        // Add calon_siswa_id to visitor_logs for tracking conversions
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->foreignUuid('calon_siswa_id')->nullable()->after('user_id')->constrained('calon_siswas')->nullOnDelete();
            $table->boolean('converted_to_registration')->default(false)->after('calon_siswa_id');
            $table->dateTime('conversion_at')->nullable()->after('converted_to_registration');
            
            // Index for conversion tracking
            $table->index('converted_to_registration');
            $table->index('calon_siswa_id');
        });
    }

    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropIndex('calon_siswas_gps_idx');
            $table->dropColumn([
                'registration_latitude',
                'registration_longitude',
                'registration_altitude',
                'registration_accuracy',
                'registration_address',
                'registration_city',
                'registration_region',
                'registration_ip',
                'registration_device',
                'registration_browser',
                'visitor_session_id',
            ]);
        });

        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropForeign(['calon_siswa_id']);
            $table->dropIndex(['converted_to_registration']);
            $table->dropIndex(['calon_siswa_id']);
            $table->dropColumn([
                'calon_siswa_id',
                'converted_to_registration',
                'conversion_at',
            ]);
        });
    }
};
