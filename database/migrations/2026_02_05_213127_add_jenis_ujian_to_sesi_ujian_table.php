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
            $table->enum('jenis_ujian', ['cbt', 'wawancara', 'mixed'])->default('mixed')->after('nama');
            $table->integer('nomor_sesi')->nullable()->after('jenis_ujian');
            $table->uuid('jadwal_ujian_id')->nullable()->after('id');
            $table->integer('durasi')->nullable()->after('waktu_selesai')->comment('Durasi dalam menit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropColumn(['jenis_ujian', 'nomor_sesi', 'jadwal_ujian_id', 'durasi']);
        });
    }
};
