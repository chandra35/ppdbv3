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
        // Add deleted_by and deleted_reason to calon_siswas (deleted_at already exists)
        Schema::table('calon_siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('calon_siswas', 'deleted_by')) {
                $table->uuid('deleted_by')->nullable()->after('deleted_at');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('calon_siswas', 'deleted_reason')) {
                $table->text('deleted_reason')->nullable()->after('deleted_by');
            }
        });

        // Note: deleted_at already exists in calon_ortus, dokumen_pendaftars, and users tables
        // No need to add soft deletes again
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            if (Schema::hasColumn('calon_siswas', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn(['deleted_by', 'deleted_reason']);
            }
        });
    }
};
