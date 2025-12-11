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
        Schema::create('alur_pendaftarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('urutan')->default(1);
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable()->default('fas fa-circle');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default data
        $defaultAlur = [
            ['urutan' => 1, 'judul' => 'Registrasi Online', 'deskripsi' => 'Isi formulir pendaftaran dengan data yang valid', 'icon' => 'fas fa-user-plus'],
            ['urutan' => 2, 'judul' => 'Upload Dokumen', 'deskripsi' => 'Unggah dokumen persyaratan yang diperlukan', 'icon' => 'fas fa-file-upload'],
            ['urutan' => 3, 'judul' => 'Verifikasi Berkas', 'deskripsi' => 'Panitia memverifikasi kelengkapan berkas', 'icon' => 'fas fa-clipboard-check'],
            ['urutan' => 4, 'judul' => 'Pengumuman', 'deskripsi' => 'Cek hasil seleksi sesuai jadwal', 'icon' => 'fas fa-bullhorn'],
            ['urutan' => 5, 'judul' => 'Daftar Ulang', 'deskripsi' => 'Peserta diterima melakukan daftar ulang', 'icon' => 'fas fa-check-circle'],
        ];

        foreach ($defaultAlur as $alur) {
            DB::table('alur_pendaftarans')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'urutan' => $alur['urutan'],
                'judul' => $alur['judul'],
                'deskripsi' => $alur['deskripsi'],
                'icon' => $alur['icon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alur_pendaftarans');
    }
};
