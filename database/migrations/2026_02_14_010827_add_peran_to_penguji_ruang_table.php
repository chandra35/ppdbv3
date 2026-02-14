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
        Schema::table('penguji_ruang', function (Blueprint $table) {
            $table->string('peran', 20)->default('penguji')->after('user_id')
                ->comment('pengawas, proktor, penguji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penguji_ruang', function (Blueprint $table) {
            $table->dropColumn('peran');
        });
    }
};
