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
        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
            $table->foreignUuid('jalur_pendaftaran_id')->nullable()->constrained('jalur_pendaftaran')->nullOnDelete();
            $table->foreignUuid('gelombang_pendaftaran_id')->nullable()->constrained('gelombang_pendaftaran')->nullOnDelete();
            $table->string('nama', 100)->comment('Nama sesi: Sesi Pagi, Sesi 1, dll');
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->integer('peserta_per_ruang')->default(20);
            $table->string('prefix_ruang', 50)->default('Ruang');
            $table->string('urutan_peserta', 20)->default('nomor_tes')->comment('nomor_tes, nama, tanggal_finalisasi');
            $table->enum('status', ['draft', 'locked', 'in_progress', 'completed'])->default('draft');
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            
            $table->index(['tahun_pelajaran_id', 'tanggal']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_ujian');
    }
};
