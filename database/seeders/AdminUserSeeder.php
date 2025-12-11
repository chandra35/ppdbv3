<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create Admin User
        User::create([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Admin PPDB',
            'email' => 'admin@ppdb.local',
            'password' => bcrypt('AdminPPDB@123'),
            'email_verified_at' => now(),
        ]);

        // Create Verifikator User (sample)
        User::create([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Verifikator Dokumen',
            'email' => 'verifikator@ppdb.local',
            'password' => bcrypt('Verifikator@123'),
            'email_verified_at' => now(),
        ]);
    }
}
