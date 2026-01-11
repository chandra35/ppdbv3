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
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('visited_at');
            $table->string('current_url', 500)->nullable()->after('page_url');
            $table->string('current_page_title', 255)->nullable()->after('current_url');
            $table->boolean('is_online')->default(false)->after('last_activity_at');
            
            // Index for online tracking queries
            $table->index(['session_id', 'last_activity_at']);
            $table->index('is_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropIndex(['session_id', 'last_activity_at']);
            $table->dropIndex(['is_online']);
            $table->dropColumn(['last_activity_at', 'current_url', 'current_page_title', 'is_online']);
        });
    }
};
