<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->boolean('moodle_sync_enabled')->default(false)->after('google_drive_oauth_email');
            $table->string('moodle_sync_mode', 30)->default('manual')->after('moodle_sync_enabled');
            $table->string('moodle_base_url')->nullable()->after('moodle_sync_mode');
            $table->text('moodle_webservice_token')->nullable()->after('moodle_base_url');
            $table->string('moodle_default_cohort_id', 50)->nullable()->after('moodle_webservice_token');
            $table->string('moodle_default_course_id', 50)->nullable()->after('moodle_default_cohort_id');
            $table->unsignedBigInteger('moodle_course_role_id')->default(5)->after('moodle_default_course_id');
            $table->boolean('moodle_assign_default_cohort')->default(true)->after('moodle_course_role_id');
            $table->boolean('moodle_enrol_default_course')->default(false)->after('moodle_assign_default_cohort');
            $table->text('moodle_sync_last_error')->nullable()->after('moodle_enrol_default_course');
            $table->timestamp('moodle_sync_last_success_at')->nullable()->after('moodle_sync_last_error');
        });

        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_user_id')->nullable()->after('nomor_tes');
            $table->string('moodle_username', 100)->nullable()->after('moodle_user_id');
            $table->string('moodle_sync_status', 30)->nullable()->after('moodle_username');
            $table->timestamp('moodle_synced_at')->nullable()->after('moodle_sync_status');
            $table->text('moodle_sync_error')->nullable()->after('moodle_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropColumn([
                'moodle_user_id',
                'moodle_username',
                'moodle_sync_status',
                'moodle_synced_at',
                'moodle_sync_error',
            ]);
        });

        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn([
                'moodle_sync_enabled',
                'moodle_sync_mode',
                'moodle_base_url',
                'moodle_webservice_token',
                'moodle_default_cohort_id',
                'moodle_default_course_id',
                'moodle_course_role_id',
                'moodle_assign_default_cohort',
                'moodle_enrol_default_course',
                'moodle_sync_last_error',
                'moodle_sync_last_success_at',
            ]);
        });
    }
};
