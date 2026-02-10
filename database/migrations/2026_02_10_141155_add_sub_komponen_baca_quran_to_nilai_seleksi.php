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
            // Sub-komponen Membaca Al-Qur'an (Tajwid, Makhroj, Kelancaran)
            $table->decimal('nilai_tajwid', 5, 2)->nullable()->after('nilai_wawancara');
            $table->decimal('nilai_makhroj', 5, 2)->nullable()->after('nilai_tajwid');
            $table->decimal('nilai_kelancaran', 5, 2)->nullable()->after('nilai_makhroj');
            
            // Hapus verifikasi (tidak diperlukan)
            // Ubah status default: hanya draft & submitted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_seleksi', function (Blueprint $table) {
            $table->dropColumn(['nilai_tajwid', 'nilai_makhroj', 'nilai_kelancaran']);
        });
    }
};
