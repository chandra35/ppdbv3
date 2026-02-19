<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envelope_open_logs', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('user_agent');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('location_name', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('envelope_open_logs', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'location_name']);
        });
    }
};
