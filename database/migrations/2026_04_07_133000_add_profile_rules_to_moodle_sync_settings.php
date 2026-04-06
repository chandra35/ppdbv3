<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->string('moodle_lastname_template', 255)->nullable()->after('moodle_default_category_id');
            $table->string('moodle_password_mode', 20)->default('account')->after('moodle_lastname_template');
            $table->string('moodle_password_custom', 255)->nullable()->after('moodle_password_mode');
            $table->string('moodle_email_mode', 20)->default('account')->after('moodle_password_custom');
            $table->string('moodle_email_domain', 255)->nullable()->after('moodle_email_mode');
        });

        Schema::table('moodle_sync_mappings', function (Blueprint $table) {
            $table->string('moodle_lastname_template', 255)->nullable()->after('moodle_course_ids');
            $table->string('moodle_password_mode', 20)->nullable()->after('moodle_lastname_template');
            $table->string('moodle_password_custom', 255)->nullable()->after('moodle_password_mode');
            $table->string('moodle_email_mode', 20)->nullable()->after('moodle_password_custom');
            $table->string('moodle_email_domain', 255)->nullable()->after('moodle_email_mode');
        });
    }

    public function down(): void
    {
        Schema::table('moodle_sync_mappings', function (Blueprint $table) {
            $table->dropColumn([
                'moodle_lastname_template',
                'moodle_password_mode',
                'moodle_password_custom',
                'moodle_email_mode',
                'moodle_email_domain',
            ]);
        });

        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn([
                'moodle_lastname_template',
                'moodle_password_mode',
                'moodle_password_custom',
                'moodle_email_mode',
                'moodle_email_domain',
            ]);
        });
    }
};
