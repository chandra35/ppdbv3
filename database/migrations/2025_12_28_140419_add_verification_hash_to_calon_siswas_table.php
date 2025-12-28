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
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->string('verification_hash', 64)->unique()->nullable()->after('nomor_tes');
            $table->index('verification_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropIndex(['verification_hash']);
            $table->dropColumn('verification_hash');
        });
    }
};
