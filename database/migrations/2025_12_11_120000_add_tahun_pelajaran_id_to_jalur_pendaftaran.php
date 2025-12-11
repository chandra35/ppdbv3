<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah column tahun_pelajaran_id
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->uuid('tahun_pelajaran_id')->nullable()->after('kode');
            $table->foreign('tahun_pelajaran_id')
                  ->references('id')
                  ->on('tahun_pelajarans')
                  ->nullOnDelete();
            $table->index('tahun_pelajaran_id');
        });

        // 2. Migrasi data: cocokkan tahun_ajaran dengan tahun_pelajaran.nama
        DB::statement("
            UPDATE jalur_pendaftaran jp
            SET jp.tahun_pelajaran_id = (
                SELECT tp.id FROM tahun_pelajarans tp 
                WHERE tp.nama = jp.tahun_ajaran
                LIMIT 1
            )
            WHERE jp.tahun_ajaran IS NOT NULL
        ");

        // 3. Hapus column tahun_ajaran
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->dropIndex(['tahun_ajaran']);
            $table->dropColumn('tahun_ajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Tambah kembali column tahun_ajaran
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->string('tahun_ajaran', 20)->nullable()->after('kode');
            $table->index('tahun_ajaran');
        });

        // 2. Migrasi data kembali
        DB::statement("
            UPDATE jalur_pendaftaran jp
            SET jp.tahun_ajaran = (
                SELECT tp.nama FROM tahun_pelajarans tp 
                WHERE tp.id = jp.tahun_pelajaran_id
                LIMIT 1
            )
            WHERE jp.tahun_pelajaran_id IS NOT NULL
        ");

        // 3. Hapus column tahun_pelajaran_id
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            $table->dropForeign(['tahun_pelajaran_id']);
            $table->dropIndex(['tahun_pelajaran_id']);
            $table->dropColumn('tahun_pelajaran_id');
        });
    }
};
