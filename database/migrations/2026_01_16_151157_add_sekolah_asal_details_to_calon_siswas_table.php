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
            // Detail Sekolah Asal dari Kemdikdasmen
            $table->text('alamat_sekolah_asal')->nullable()->after('nama_sekolah_asal');
            $table->string('kelurahan_sekolah_asal', 100)->nullable()->after('alamat_sekolah_asal');
            $table->string('kecamatan_sekolah_asal', 100)->nullable()->after('kelurahan_sekolah_asal');
            $table->string('kabupaten_sekolah_asal', 100)->nullable()->after('kecamatan_sekolah_asal');
            $table->string('provinsi_sekolah_asal', 100)->nullable()->after('kabupaten_sekolah_asal');
            $table->enum('status_sekolah_asal', ['NEGERI', 'SWASTA'])->nullable()->after('provinsi_sekolah_asal');
            $table->string('bentuk_sekolah_asal', 50)->nullable()->after('status_sekolah_asal');
            $table->char('akreditasi_sekolah_asal', 1)->nullable()->after('bentuk_sekolah_asal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropColumn([
                'alamat_sekolah_asal',
                'kelurahan_sekolah_asal',
                'kecamatan_sekolah_asal',
                'kabupaten_sekolah_asal',
                'provinsi_sekolah_asal',
                'status_sekolah_asal',
                'bentuk_sekolah_asal',
                'akreditasi_sekolah_asal',
            ]);
        });
    }
};
