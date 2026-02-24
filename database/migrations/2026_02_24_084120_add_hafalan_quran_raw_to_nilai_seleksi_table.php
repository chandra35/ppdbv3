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
        Schema::table('nilai_seleksi', function (Blueprint $table) {
            $table->string('hafalan_quran_raw', 255)->nullable()->after('nilai_hafalan')
                ->comment('Value asli dari kolom Hfln Quran di Excel (referensi)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_seleksi', function (Blueprint $table) {
            $table->dropColumn('hafalan_quran_raw');
        });
    }
};
