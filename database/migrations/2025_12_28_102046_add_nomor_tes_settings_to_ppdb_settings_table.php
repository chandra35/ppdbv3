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
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->string('nomor_tes_prefix', 10)->default('NTS')->after('nomor_registrasi_counter');
            $table->string('nomor_tes_format', 100)->default('{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}')->after('nomor_tes_prefix');
            $table->integer('nomor_tes_digit')->default(4)->after('nomor_tes_format');
            $table->json('nomor_tes_counter')->nullable()->after('nomor_tes_digit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn(['nomor_tes_prefix', 'nomor_tes_format', 'nomor_tes_digit', 'nomor_tes_counter']);
        });
    }
};
