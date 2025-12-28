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
            // Pilihan Program/Jurusan
            if (!Schema::hasColumn('calon_siswas', 'pilihan_program')) {
                $table->string('pilihan_program', 50)->nullable()->after('data_dokumen_completed');
            }
            
            // Finalisasi
            if (!Schema::hasColumn('calon_siswas', 'is_finalisasi')) {
                $table->boolean('is_finalisasi')->default(false)->after('pilihan_program');
            }
            if (!Schema::hasColumn('calon_siswas', 'tanggal_finalisasi')) {
                $table->timestamp('tanggal_finalisasi')->nullable()->after('is_finalisasi');
            }
            
            // Nomor Tes
            if (!Schema::hasColumn('calon_siswas', 'nomor_tes')) {
                $table->string('nomor_tes', 50)->nullable()->unique()->after('tanggal_finalisasi');
            }
            
            // Nilai CBT (nilai_wawancara sudah ada dari migration sebelumnya)
            if (!Schema::hasColumn('calon_siswas', 'nilai_cbt')) {
                $table->decimal('nilai_cbt', 5, 2)->nullable()->after('nomor_tes');
            }
            if (!Schema::hasColumn('calon_siswas', 'nilai_akhir')) {
                $table->decimal('nilai_akhir', 5, 2)->nullable()->after('nilai_wawancara');
            }
            
            // Ranking & Status Admisi
            if (!Schema::hasColumn('calon_siswas', 'ranking')) {
                $table->integer('ranking')->nullable()->after('nilai_akhir');
            }
            if (!Schema::hasColumn('calon_siswas', 'status_admisi')) {
                $table->enum('status_admisi', ['belum_diproses', 'diterima', 'cadangan', 'ditolak'])
                      ->default('belum_diproses')->after('ranking');
            }
            if (!Schema::hasColumn('calon_siswas', 'catatan_admisi')) {
                $table->text('catatan_admisi')->nullable()->after('status_admisi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            $table->dropColumn([
                'pilihan_program',
                'is_finalisasi',
                'tanggal_finalisasi',
                'nomor_tes',
                'ranking',
                'status_admisi',
                'catatan_admisi'
            ]);
            
            // Only drop if exists
            if (Schema::hasColumn('calon_siswas', 'nilai_cbt')) {
                $table->dropColumn('nilai_cbt');
            }
            if (Schema::hasColumn('calon_siswas', 'nilai_akhir')) {
                $table->dropColumn('nilai_akhir');
            }
        });
    }
};
