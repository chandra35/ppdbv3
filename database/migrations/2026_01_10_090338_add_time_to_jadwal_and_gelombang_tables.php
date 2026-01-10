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
        // Tambah waktu ke jadwal_ppdb
        Schema::table('jadwal_ppdb', function (Blueprint $table) {
            $table->time('waktu_mulai')->nullable()->after('tanggal_mulai');
            $table->time('waktu_selesai')->nullable()->after('tanggal_selesai');
        });
        
        // Tambah waktu ke gelombang_pendaftaran
        Schema::table('gelombang_pendaftaran', function (Blueprint $table) {
            $table->time('waktu_buka')->default('00:00:00')->after('tanggal_buka');
            $table->time('waktu_tutup')->default('23:59:59')->after('tanggal_tutup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ppdb', function (Blueprint $table) {
            $table->dropColumn(['waktu_mulai', 'waktu_selesai']);
        });
        
        Schema::table('gelombang_pendaftaran', function (Blueprint $table) {
            $table->dropColumn(['waktu_buka', 'waktu_tutup']);
        });
    }
};
