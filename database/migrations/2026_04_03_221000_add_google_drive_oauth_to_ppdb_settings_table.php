<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->string('google_drive_auth_mode', 30)->default('service_account')->after('dokumen_storage_mode');
            $table->string('google_drive_oauth_client_id', 255)->nullable()->after('google_drive_make_public');
            $table->text('google_drive_oauth_client_secret')->nullable()->after('google_drive_oauth_client_id');
            $table->text('google_drive_oauth_refresh_token')->nullable()->after('google_drive_oauth_client_secret');
            $table->string('google_drive_oauth_email', 255)->nullable()->after('google_drive_oauth_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_drive_auth_mode',
                'google_drive_oauth_client_id',
                'google_drive_oauth_client_secret',
                'google_drive_oauth_refresh_token',
                'google_drive_oauth_email',
            ]);
        });
    }
};
