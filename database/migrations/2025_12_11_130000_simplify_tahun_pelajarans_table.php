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
        Schema::table('tahun_pelajarans', function (Blueprint $table) {
            // Drop columns yang tidak diperlukan
            if (Schema::hasColumn('tahun_pelajarans', 'tanggal_mulai')) {
                $table->dropColumn('tanggal_mulai');
            }
            if (Schema::hasColumn('tahun_pelajarans', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_pelajarans', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('nama');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }
};
