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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform', 100)->nullable(); // Windows, Android, iOS, etc
            $table->string('platform_version', 50)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('isp', 200)->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('page_title', 200)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('ip_address');
            $table->index('visited_at');
            $table->index('device_type');
            $table->index('country_code');
            $table->index('session_id');
            $table->index(['visited_at', 'page_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
