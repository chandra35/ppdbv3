<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelulusan_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('kelulusan_settings', 'jalur_pendaftaran_id')) {
                $table->foreignUuid('jalur_pendaftaran_id')
                    ->nullable()
                    ->after('tahun_pelajaran_id')
                    ->constrained('jalur_pendaftaran')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('kelulusan_settings', 'gelombang_pendaftaran_id')) {
                $table->foreignUuid('gelombang_pendaftaran_id')
                    ->nullable()
                    ->after('jalur_pendaftaran_id')
                    ->constrained('gelombang_pendaftaran')
                    ->nullOnDelete();
            }
        });

        Schema::table('kelulusan_settings', function (Blueprint $table) {
            $table->index(['tahun_pelajaran_id', 'jalur_pendaftaran_id'], 'kelulusan_settings_tahun_jalur_idx');
            $table->index(['tahun_pelajaran_id', 'jalur_pendaftaran_id', 'gelombang_pendaftaran_id'], 'kelulusan_settings_scope_idx');
            $table->unique(
                ['tahun_pelajaran_id', 'jalur_pendaftaran_id', 'gelombang_pendaftaran_id'],
                'kelulusan_settings_scope_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('kelulusan_settings', function (Blueprint $table) {
            $table->dropUnique('kelulusan_settings_scope_unique');
            $table->dropIndex('kelulusan_settings_tahun_jalur_idx');
            $table->dropIndex('kelulusan_settings_scope_idx');
        });

        Schema::table('kelulusan_settings', function (Blueprint $table) {
            if (Schema::hasColumn('kelulusan_settings', 'gelombang_pendaftaran_id')) {
                $table->dropConstrainedForeignId('gelombang_pendaftaran_id');
            }
            if (Schema::hasColumn('kelulusan_settings', 'jalur_pendaftaran_id')) {
                $table->dropConstrainedForeignId('jalur_pendaftaran_id');
            }
        });
    }
};
