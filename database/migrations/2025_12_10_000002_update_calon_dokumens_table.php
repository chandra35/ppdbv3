<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_dokumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('calon_siswa_id');
            
            // Jenis dokumen
            $table->enum('jenis_dokumen', [
                'ijazah',
                'akta_kelahiran',
                'kartu_keluarga',
                'foto_4x6',
                'piagam_prestasi',
                'surat_sehat'
            ]);
            
            // File
            $table->string('file_path', 255);
            $table->integer('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            
            // Verifikasi
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->uuid('verifikator_id')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->text('alasan_tolak')->nullable();
            
            $table->timestamps();
            
            // Foreign Keys & Indexes
            $table->foreign('calon_siswa_id')->references('id')->on('calon_siswas')->onDelete('cascade');
            $table->index('jenis_dokumen');
            $table->index('status_verifikasi');
            $table->index('verifikator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_dokumens');
    }
};
