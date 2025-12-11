<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identitas Sekolah
            $table->string('nama_sekolah', 255);
            $table->string('npsn', 20)->nullable();
            $table->string('nsm', 20)->nullable();
            $table->enum('jenjang', ['MI', 'MTs', 'MA', 'SD', 'SMP', 'SMA', 'SMK'])->default('MA');
            
            // Kontak
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('website', 100)->nullable();
            
            // Alamat dengan Laravolt Indonesia
            $table->string('alamat_jalan', 255)->nullable();
            $table->char('province_code', 2)->nullable(); // Kode Provinsi
            $table->char('city_code', 4)->nullable(); // Kode Kab/Kota
            $table->char('district_code', 7)->nullable(); // Kode Kecamatan
            $table->char('village_code', 10)->nullable(); // Kode Kelurahan/Desa
            $table->string('kode_pos', 10)->nullable();
            
            // Koordinat untuk Leaflet Maps
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Logo & Media
            $table->string('logo', 255)->nullable();
            
            // Kepala Sekolah
            $table->string('nama_kepala_sekolah', 100)->nullable();
            $table->string('nip_kepala_sekolah', 30)->nullable();
            
            $table->timestamps();
            
            // Index untuk foreign key reference
            $table->index('province_code');
            $table->index('city_code');
            $table->index('district_code');
            $table->index('village_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_settings');
    }
};
