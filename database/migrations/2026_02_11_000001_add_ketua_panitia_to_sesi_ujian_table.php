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
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->foreignUuid('ketua_panitia_id')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropForeign(['ketua_panitia_id']);
            $table->dropColumn('ketua_panitia_id');
        });
    }
};
