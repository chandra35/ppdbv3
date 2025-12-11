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
        // Add tampil_kuota to jalur_pendaftaran
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            if (!Schema::hasColumn('jalur_pendaftaran', 'tampil_kuota')) {
                $table->boolean('tampil_kuota')->default(true)->after('tampil_di_publik')
                    ->comment('Apakah kuota ditampilkan ke publik');
            }
        });

        // Add tampil_kuota to gelombang_pendaftaran
        Schema::table('gelombang_pendaftaran', function (Blueprint $table) {
            if (!Schema::hasColumn('gelombang_pendaftaran', 'tampil_kuota')) {
                $table->boolean('tampil_kuota')->default(false)->after('tampil_nama_gelombang')
                    ->comment('Apakah kuota gelombang ditampilkan ke publik');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jalur_pendaftaran', function (Blueprint $table) {
            if (Schema::hasColumn('jalur_pendaftaran', 'tampil_kuota')) {
                $table->dropColumn('tampil_kuota');
            }
        });

        Schema::table('gelombang_pendaftaran', function (Blueprint $table) {
            if (Schema::hasColumn('gelombang_pendaftaran', 'tampil_kuota')) {
                $table->dropColumn('tampil_kuota');
            }
        });
    }
};
