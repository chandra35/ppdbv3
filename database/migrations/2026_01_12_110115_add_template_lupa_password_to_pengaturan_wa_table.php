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
        Schema::table('pengaturan_wa', function (Blueprint $table) {
            $table->text('template_lupa_password')->nullable()->after('template_ditolak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_wa', function (Blueprint $table) {
            $table->dropColumn('template_lupa_password');
        });
    }
};
