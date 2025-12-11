<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Slider::create([
            'judul' => 'Selamat Datang di Portal PPDB',
            'deskripsi' => 'Penerimaan Peserta Didik Baru Tahun Pelajaran 2025/2026',
            'gambar' => 'sliders/slide1.jpg',
            'link' => null,
            'urutan' => 1,
            'status' => 'active',
        ]);

        \App\Models\Slider::create([
            'judul' => 'Pendaftaran Dibuka!',
            'deskripsi' => '01 Januari - 15 Januari 2025. Segera daftarkan diri Anda!',
            'gambar' => 'sliders/slide2.jpg',
            'link' => null,
            'urutan' => 2,
            'status' => 'active',
        ]);

        \App\Models\Slider::create([
            'judul' => 'Proses Mudah & Cepat',
            'deskripsi' => 'Validasi NISN otomatis dengan Kemendikbud. Gratis & Aman.',
            'gambar' => 'sliders/slide3.jpg',
            'link' => null,
            'urutan' => 3,
            'status' => 'active',
        ]);
    }
}
