<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class PengujiRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'penguji'],
            [
                'display_name' => 'Penguji',
                'description' => 'Penguji ujian seleksi PPDB. Dapat mengakses portal penguji untuk menilai peserta.',
            ]
        );

        $this->command->info('Role "penguji" berhasil dibuat/diverifikasi.');
    }
}
