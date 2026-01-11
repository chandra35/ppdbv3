<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin Role
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Memiliki akses penuh ke semua fitur sistem',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        // Create Admin Role
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Administrator PPDB dengan akses penuh',
                'permissions' => ['*'],
                'is_system' => true,
            ]
        );

        // Create Verifikator Role
        Role::firstOrCreate(
            ['name' => 'verifikator'],
            [
                'display_name' => 'Verifikator',
                'description' => 'Verifikator pendaftaran siswa baru',
                'permissions' => [
                    'pendaftar.view',
                    'pendaftar.verify',
                    'pendaftar.approve',
                    'pendaftar.reject',
                ],
                'is_system' => false,
            ]
        );

        // Create Content Manager Role
        Role::firstOrCreate(
            ['name' => 'content-manager'],
            [
                'display_name' => 'Content Manager',
                'description' => 'Pengelola konten website (berita, slider)',
                'permissions' => [
                    'berita.view',
                    'berita.create',
                    'berita.edit',
                    'berita.delete',
                    'slider.view',
                    'slider.create',
                    'slider.edit',
                    'slider.delete',
                ],
                'is_system' => false,
            ]
        );

        // Create Pendaftar Role (for PPDB applicants)
        Role::firstOrCreate(
            ['name' => 'pendaftar'],
            [
                'display_name' => 'Pendaftar',
                'description' => 'Calon siswa yang mendaftar melalui PPDB',
                'permissions' => [
                    'pendaftar.dashboard',
                    'pendaftar.profile.view',
                    'pendaftar.profile.edit',
                    'pendaftar.dokumen.upload',
                    'pendaftar.status.view',
                ],
                'is_system' => true,
            ]
        );

        // Create Pengunjung Role (for public visitors/guests)
        Role::firstOrCreate(
            ['name' => 'pengunjung'],
            [
                'display_name' => 'Pengunjung',
                'description' => 'Pengunjung website (akses publik terbatas)',
                'permissions' => [
                    'public.view',
                    'public.info-ppdb',
                    'public.berita',
                    'public.pengumuman',
                    'public.kontak',
                ],
                'is_system' => true,
            ]
        );

        // Assign admin role to admin user
        $adminUser = User::where('email', 'admin@ppdb.local')->first();
        if ($adminUser) {
            $adminUser->roles()->syncWithoutDetaching([$admin->id]);
        }

        $this->command->info('Roles seeded successfully!');
    }
}
