<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Jalur Pendaftaran (Prestasi, Reguler, Afirmasi, dll)
        Schema::create('jalur_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('nama', 100); // Jalur Prestasi, Jalur Reguler, dll
            $table->string('kode', 20)->unique(); // PRESTASI, REGULER, AFIRMASI
            $table->string('tahun_ajaran', 20); // 2025/2026
            $table->text('deskripsi')->nullable();
            $table->text('persyaratan')->nullable(); // Syarat khusus jalur ini
            
            // Kuota per jalur
            $table->integer('kuota')->default(0);
            $table->integer('kuota_terisi')->default(0);
            
            // Warna untuk badge/tampilan
            $table->string('warna', 20)->default('primary'); // primary, success, warning, info, danger
            $table->string('icon', 50)->default('fas fa-graduation-cap');
            
            // Status & visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('tampil_di_publik')->default(true);
            $table->integer('urutan')->default(1);
            
            $table->timestamps();
            
            $table->index('tahun_ajaran');
            $table->index('is_active');
        });

        // Tabel Gelombang/Periode Pendaftaran
        Schema::create('gelombang_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('jalur_id');
            $table->string('nama', 100); // Gelombang 1, Gelombang 2, Periode Januari
            $table->text('deskripsi')->nullable();
            
            // Periode
            $table->date('tanggal_buka');
            $table->date('tanggal_tutup');
            
            // Kuota per gelombang (opsional, jika null ikut kuota jalur)
            $table->integer('kuota')->nullable();
            $table->integer('kuota_terisi')->default(0);
            
            // Biaya (opsional)
            $table->decimal('biaya_pendaftaran', 12, 2)->default(0);
            
            // Nomor Registrasi
            $table->string('prefix_nomor', 20)->default('REG');
            $table->integer('counter_nomor')->default(0);
            
            // Status
            $table->enum('status', ['draft', 'upcoming', 'open', 'closed', 'finished'])->default('draft');
            $table->boolean('is_active')->default(false);
            
            // Visibility - apakah nama gelombang ditampilkan ke publik
            $table->boolean('tampil_nama_gelombang')->default(false);
            
            $table->integer('urutan')->default(1);
            $table->timestamps();
            
            $table->foreign('jalur_id')->references('id')->on('jalur_pendaftaran')->onDelete('cascade');
            $table->index('status');
            $table->index('is_active');
        });

        // Update tabel calon_siswa jika ada
        if (Schema::hasTable('calon_siswas')) {
            Schema::table('calon_siswas', function (Blueprint $table) {
                if (!Schema::hasColumn('calon_siswas', 'jalur_id')) {
                    $table->uuid('jalur_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('calon_siswas', 'gelombang_id')) {
                    $table->uuid('gelombang_id')->nullable()->after('jalur_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calon_siswas')) {
            Schema::table('calon_siswas', function (Blueprint $table) {
                if (Schema::hasColumn('calon_siswas', 'jalur_id')) {
                    $table->dropColumn('jalur_id');
                }
                if (Schema::hasColumn('calon_siswas', 'gelombang_id')) {
                    $table->dropColumn('gelombang_id');
                }
            });
        }
        
        Schema::dropIfExists('gelombang_pendaftaran');
        Schema::dropIfExists('jalur_pendaftaran');
    }
};
