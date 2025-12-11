<?php

namespace Database\Seeders;

use App\Models\Berita;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        $beritas = [
            [
                'id' => Uuid::uuid4()->toString(),
                'judul' => 'Pembukaan Pendaftaran PPDB Tahun 2025',
                'slug' => 'pembukaan-pendaftaran-ppdb-2025',
                'deskripsi' => 'Kami dengan senang hati mengumumkan bahwa pendaftaran PPDB untuk tahun pelajaran 2025/2026 telah dibuka. Semua calon siswa dapat mendaftar melalui portal online ini mulai dari tanggal 1 Januari 2025.',
                'konten' => 'Pendaftaran PPDB untuk tahun pelajaran 2025/2026 telah resmi dibuka. Dengan sistem pendaftaran online yang modern dan mudah diakses, calon siswa dapat melakukan pendaftaran dari mana saja dan kapan saja. Proses pendaftaran terdiri dari 4 langkah mudah yang dapat diselesaikan dalam waktu kurang dari 30 menit.',
                'status' => 'published',
                'tanggal_publikasi' => now(),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'judul' => 'Persyaratan Dokumen untuk Pendaftaran',
                'slug' => 'persyaratan-dokumen-pendaftaran',
                'deskripsi' => 'Untuk melengkapi proses pendaftaran PPDB, Anda perlu menyiapkan dokumen-dokumen berikut dengan baik. Semua dokumen harus dalam kondisi jelas dan dapat dibaca.',
                'konten' => 'Dokumen yang harus Anda siapkan untuk pendaftaran PPDB adalah: 1) Ijazah atau Sertifikat Kelulusan, 2) Akta Kelahiran, 3) Kartu Keluarga (KK), 4) Foto Pribadi 4x6 (warna atau B&W), 5) Piagam/Sertifikat Prestasi (optional), 6) Surat Keterangan Sehat (optional). Pastikan semua dokumen dalam format PDF atau JPG dengan ukuran maksimal 5MB per file.',
                'status' => 'published',
                'tanggal_publikasi' => now()->subDay(),
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'judul' => 'Panduan Validasi NISN dan Cara Daftar',
                'slug' => 'panduan-validasi-nisn',
                'deskripsi' => 'Memahami pentingnya validasi NISN yang akurat adalah langkah pertama dalam proses pendaftaran PPDB. Pelajari cara melakukan validasi dengan benar.',
                'konten' => 'NISN (Nomor Induk Siswa Nasional) adalah identitas unik yang diberikan Kemendikbud kepada setiap siswa. Sebelum melakukan pendaftaran, pastikan NISN Anda sudah terdaftar di sistem Kemendikbud. Jika NISN tidak valid, hubungi sekolah asal Anda untuk mengecek data NISN di sistem pusat. NISN yang valid akan memastikan data Anda dapat diverifikasi dengan benar.',
                'status' => 'published',
                'tanggal_publikasi' => now()->subDays(2),
            ],
        ];

        foreach ($beritas as $berita) {
            Berita::create($berita);
        }
    }
}
