<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->json('moodle_default_course_ids')->nullable()->after('moodle_default_course_id');
            $table->string('moodle_default_category_id', 50)->nullable()->after('moodle_default_course_ids');
        });

        Schema::create('moodle_sync_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->uuid('jalur_pendaftaran_id')->nullable();
            $table->uuid('gelombang_pendaftaran_id')->nullable();
            $table->string('moodle_cohort_id', 50)->nullable();
            $table->string('moodle_category_id', 50)->nullable();
            $table->json('moodle_course_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajarans')->nullOnDelete();
            $table->foreign('jalur_pendaftaran_id')->references('id')->on('jalur_pendaftaran')->nullOnDelete();
            $table->foreign('gelombang_pendaftaran_id')->references('id')->on('gelombang_pendaftaran')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodle_sync_mappings');

        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropColumn([
                'moodle_default_course_ids',
                'moodle_default_category_id',
            ]);
        });
    }
};
