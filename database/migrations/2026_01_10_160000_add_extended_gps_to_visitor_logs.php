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
        Schema::table('visitor_logs', function (Blueprint $table) {
            // Extended GPS data
            $table->decimal('altitude', 10, 2)->nullable()->after('longitude'); // meters above sea level
            $table->decimal('accuracy', 10, 2)->nullable()->after('altitude'); // GPS accuracy in meters
            $table->decimal('altitude_accuracy', 10, 2)->nullable()->after('accuracy');
            $table->decimal('heading', 5, 2)->nullable()->after('altitude_accuracy'); // direction 0-360
            $table->decimal('speed', 8, 2)->nullable()->after('heading'); // meters per second
            $table->string('location_source', 20)->default('ip')->after('speed'); // 'gps' or 'ip'
            $table->string('address', 500)->nullable()->after('isp'); // full address from reverse geocoding
            $table->string('postal_code', 20)->nullable()->after('address');
            $table->string('district', 100)->nullable()->after('city'); // kecamatan
            $table->string('subdistrict', 100)->nullable()->after('district'); // kelurahan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'altitude',
                'accuracy',
                'altitude_accuracy',
                'heading',
                'speed',
                'location_source',
                'address',
                'postal_code',
                'district',
                'subdistrict',
            ]);
        });
    }
};
