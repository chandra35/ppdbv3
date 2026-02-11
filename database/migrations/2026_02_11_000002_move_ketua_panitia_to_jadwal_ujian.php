<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move ketua_panitia_id from sesi_ujian to jadwal_ujian.
     * One ketua panitia per jadwal, not per sesi.
     */
    public function up(): void
    {
        // Add ketua_panitia_id to jadwal_ujian
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            $table->foreignUuid('ketua_panitia_id')->nullable()->after('catatan')->constrained('users')->nullOnDelete();
        });

        // Migrate existing data: copy first non-null ketua_panitia_id from sesi_ujian to jadwal_ujian
        $sesiWithKetua = DB::table('sesi_ujian')
            ->whereNotNull('ketua_panitia_id')
            ->whereNotNull('jadwal_ujian_id')
            ->get()
            ->groupBy('jadwal_ujian_id');

        foreach ($sesiWithKetua as $jadwalId => $sesiList) {
            $ketuaId = $sesiList->first()->ketua_panitia_id;
            DB::table('jadwal_ujian')->where('id', $jadwalId)->update(['ketua_panitia_id' => $ketuaId]);
        }

        // Remove ketua_panitia_id from sesi_ujian
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropForeign(['ketua_panitia_id']);
            $table->dropColumn('ketua_panitia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add to sesi_ujian
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->foreignUuid('ketua_panitia_id')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
        });

        // Remove from jadwal_ujian
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            $table->dropForeign(['ketua_panitia_id']);
            $table->dropColumn('ketua_panitia_id');
        });
    }
};
