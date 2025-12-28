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
        Schema::table('sekolah_settings', function (Blueprint $table) {
            $table->boolean('qr_enable')->default(true)->after('logo_display_height');
            $table->boolean('qr_with_logo')->default(true)->after('qr_enable');
            $table->integer('qr_size')->default(150)->after('qr_with_logo'); // 100, 150, 200
            $table->string('qr_position')->default('top-right')->after('qr_size'); // top-right, top-left, bottom-right
            $table->string('qr_error_level')->default('H')->after('qr_position'); // L, M, Q, H
            $table->string('qr_function')->default('both')->after('qr_error_level'); // public, admin, both
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah_settings', function (Blueprint $table) {
            $table->dropColumn(['qr_enable', 'qr_with_logo', 'qr_size', 'qr_position', 'qr_error_level', 'qr_function']);
        });
    }
};
