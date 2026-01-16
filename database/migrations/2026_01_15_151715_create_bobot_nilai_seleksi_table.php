<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bobot_nilai_seleksi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajarans')->cascadeOnDelete();
            $table->string('komponen', 50)->comment('wawancara, baca_quran, tulis_quran, hafalan');
            $table->string('nama_komponen', 100);
            $table->decimal('bobot', 5, 2)->comment('Persentase bobot (total harus 100)');
            $table->decimal('nilai_min', 5, 2)->default(0);
            $table->decimal('nilai_max', 5, 2)->default(100);
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
            
            $table->index('tahun_pelajaran_id');
            $table->unique(['tahun_pelajaran_id', 'komponen']);
        });
        
        // Insert default bobot
        $tahunAktif = DB::table('tahun_pelajarans')->where('is_active', true)->first();
        if ($tahunAktif) {
            $now = now();
            DB::table('bobot_nilai_seleksi')->insert([
                ['id' => Str::uuid(), 'tahun_pelajaran_id' => $tahunAktif->id, 'komponen' => 'wawancara', 'nama_komponen' => 'Wawancara', 'bobot' => 25, 'urutan' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'tahun_pelajaran_id' => $tahunAktif->id, 'komponen' => 'baca_quran', 'nama_komponen' => 'Baca Al-Qur\'an', 'bobot' => 25, 'urutan' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'tahun_pelajaran_id' => $tahunAktif->id, 'komponen' => 'tulis_quran', 'nama_komponen' => 'Tulis Al-Qur\'an', 'bobot' => 25, 'urutan' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['id' => Str::uuid(), 'tahun_pelajaran_id' => $tahunAktif->id, 'komponen' => 'hafalan', 'nama_komponen' => 'Hafalan Juz', 'bobot' => 25, 'urutan' => 4, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_nilai_seleksi');
    }
};
