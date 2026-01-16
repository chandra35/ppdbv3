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
        Schema::table('nilai_rapor', function (Blueprint $table) {
            // Dokumen rapor (path file)
            $table->string('dokumen_path')->nullable()->after('rata_rata')->comment('Path file dokumen rapor');
            
            // Validasi dokumen oleh admin
            $table->enum('status_validasi', ['pending', 'valid', 'invalid'])->default('pending')->after('dokumen_path')->comment('Status validasi dokumen');
            $table->text('catatan_validasi')->nullable()->after('status_validasi')->comment('Catatan dari verifikator');
            $table->foreignUuid('validated_by')->nullable()->after('catatan_validasi')->constrained('users')->nullOnDelete()->comment('User yang memvalidasi');
            $table->timestamp('validated_at')->nullable()->after('validated_by')->comment('Waktu validasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_rapor', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'dokumen_path',
                'status_validasi',
                'catatan_validasi',
                'validated_by',
                'validated_at'
            ]);
        });
    }
};
