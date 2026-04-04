<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->string('dokumen_storage_mode', 50)->default('local')->after('izinkan_dokumen_tambahan');
            $table->string('google_drive_root_folder_id', 255)->nullable()->after('dokumen_storage_mode');
            $table->string('google_drive_credentials_path', 255)->nullable()->after('google_drive_root_folder_id');
            $table->boolean('google_drive_make_public')->default(true)->after('google_drive_credentials_path');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn([
                'dokumen_storage_mode',
                'google_drive_root_folder_id',
                'google_drive_credentials_path',
                'google_drive_make_public',
            ]);
        });
    }
};
