<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            // Hapus kolom jenjang_target karena sudah ada di sekolah_settings
            if (Schema::hasColumn('ppdb_settings', 'jenjang_target')) {
                $table->dropColumn('jenjang_target');
            }
            
            // Hapus kolom grade_minimum karena akan dihitung otomatis dari jenjang sekolah
            if (Schema::hasColumn('ppdb_settings', 'grade_minimum')) {
                $table->dropColumn('grade_minimum');
            }
            
            // Hapus kolom izinkan_grade_lebih_tinggi
            if (Schema::hasColumn('ppdb_settings', 'izinkan_grade_lebih_tinggi')) {
                $table->dropColumn('izinkan_grade_lebih_tinggi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->string('jenjang_target', 50)->nullable()->after('kuota_penerimaan');
            $table->string('grade_minimum', 50)->nullable()->after('validasi_nisn_aktif');
            $table->boolean('izinkan_grade_lebih_tinggi')->default(false)->after('grade_minimum');
        });
    }
};
