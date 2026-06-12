<?php

namespace Database\Seeders;

use App\Models\Petugas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SuperAdmin
        Petugas::updateOrCreate(
            ['KodePetugas' => 'ADM001'],
            [
                'NamaPetugas' => 'Super Administrator',
                'Jabatan' => 'SuperAdmin',
                'HakAkses' => 'SuperAdmin',
                'Password' => Hash::make('superadmin123'),
                'Email' => 'admin@perpustakaan.sumbawakab.go.id',
            ]
        );

        // Petugas
        Petugas::updateOrCreate(
            ['KodePetugas' => 'PTG001'],
            [
                'NamaPetugas' => 'Petugas Perpustakaan',
                'Jabatan' => 'Pustakawan',
                'HakAkses' => 'Petugas',
                'Password' => Hash::make('petugas123'),
                'Email' => 'petugas@perpustakaan.sumbawakab.go.id',
            ]
        );
    }
}
