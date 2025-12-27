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
        Schema::table('calon_dokumen', function (Blueprint $table) {
            // Tracking untuk revision request
            $table->uuid('revised_by')->nullable()->after('verified_at');
            $table->timestamp('revised_at')->nullable()->after('revised_by');
            
            // Tracking untuk cancellation
            $table->uuid('cancelled_by')->nullable()->after('revised_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            
            // Catatan verifikasi additional
            $table->text('verifikasi_note')->nullable()->after('cancelled_at');
            
            // Foreign keys
            $table->foreign('revised_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_dokumen', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['revised_by']);
            $table->dropForeign(['cancelled_by']);
            
            // Drop columns
            $table->dropColumn([
                'revised_by',
                'revised_at',
                'cancelled_by',
                'cancelled_at',
                'verifikasi_note'
            ]);
        });
    }
};
