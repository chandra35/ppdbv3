<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // NISN & Validasi
            $table->string('nisn', 10)->unique();
            $table->boolean('nisn_valid')->default(false);
            
            // Data Pribadi
            $table->string('nama_lengkap', 100);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
            $table->enum('agama', ['islam', 'kristen', 'katolik', 'hindu', 'budha', 'konghucu'])->nullable();
            
            // Kontak
            $table->string('no_hp_pribadi', 15)->nullable();
            $table->string('no_hp_ortu', 15)->nullable();
            $table->string('email', 100)->unique();
            
            // Alamat
            $table->text('alamat_rumah')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            
            // Asal Sekolah
            $table->string('asal_sekolah', 150)->nullable();
            
            // Status Verifikasi & Admisi
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_admisi', ['pending', 'diterima', 'cadangan', 'ditolak'])->default('pending');
            
            // Nilai
            $table->decimal('nilai_tes', 5, 2)->nullable();
            $table->decimal('nilai_wawancara', 5, 2)->nullable();
            $table->decimal('rata_rata_nilai', 5, 2)->nullable();
            $table->integer('ranking')->nullable();
            
            // Nomor Pendaftaran
            $table->string('nomor_pendaftaran_sementara', 50)->nullable();
            $table->string('nomor_pendaftaran_final', 50)->unique()->nullable();
            
            // Bukti Registrasi (NEW REQUIREMENT)
            $table->string('nomor_registrasi', 50)->unique()->nullable();
            $table->dateTime('tanggal_registrasi')->nullable();
            $table->string('bukti_registrasi_path', 255)->nullable(); // Path ke PDF bukti registrasi
            
            // Foreign Keys
            $table->uuid('tahun_pelajaran_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('kelas_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status_verifikasi');
            $table->index('status_admisi');
            $table->index('tahun_pelajaran_id');
            $table->index('nomor_registrasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_siswas');
    }
};
