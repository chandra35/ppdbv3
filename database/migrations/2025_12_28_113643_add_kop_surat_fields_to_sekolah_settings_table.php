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
            // Logo Kemenag
            $table->string('logo_kemenag_path')->nullable()->after('logo');
            $table->integer('logo_kemenag_height')->default(100)->after('logo_kemenag_path');
            
            // Logo Display Settings
            $table->integer('logo_display_height')->default(80)->after('logo_kemenag_height');
            $table->integer('logo_column_width')->default(20)->after('logo_display_height');
            
            // Kop Surat Mode & Config
            $table->string('kop_mode')->default('builder')->after('logo_column_width'); // builder or custom
            $table->json('kop_surat_config')->nullable()->after('kop_mode');
            $table->string('kop_surat_custom_path')->nullable()->after('kop_surat_config');
            
            // Kop Margins
            $table->integer('kop_margin_top')->default(10)->after('kop_surat_custom_path');
            $table->integer('kop_height')->default(100)->after('kop_margin_top');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_kemenag_path',
                'logo_kemenag_height',
                'logo_display_height',
                'logo_column_width',
                'kop_mode',
                'kop_surat_config',
                'kop_surat_custom_path',
                'kop_margin_top',
                'kop_height',
            ]);
        });
    }
};
