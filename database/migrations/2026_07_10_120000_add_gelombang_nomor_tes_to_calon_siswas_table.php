<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('calon_siswas', 'gelombang_nomor_tes_id')) {
                $table->uuid('gelombang_nomor_tes_id')
                    ->nullable()
                    ->after('gelombang_pendaftaran_id');

                $table->foreign('gelombang_nomor_tes_id')
                    ->references('id')
                    ->on('gelombang_pendaftaran')
                    ->nullOnDelete();

                $table->index('gelombang_nomor_tes_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calon_siswas', function (Blueprint $table) {
            if (Schema::hasColumn('calon_siswas', 'gelombang_nomor_tes_id')) {
                $table->dropForeign(['gelombang_nomor_tes_id']);
                $table->dropIndex(['gelombang_nomor_tes_id']);
                $table->dropColumn('gelombang_nomor_tes_id');
            }
        });
    }
};
