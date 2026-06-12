<?php

namespace Database\Factories;

use App\Models\Petugas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Petugas>
 */
class PetugasFactory extends Factory
{
    protected $model = Petugas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'KodePetugas' => 'PTG'.fake()->unique()->numberBetween(100, 999),
            'NamaPetugas' => fake()->name(),
            'Jabatan' => fake()->randomElement(['Kepala Perpustakaan', 'Pustakawan', 'Staff Administrasi']),
            'HakAkses' => 'Petugas',
            'Email' => fake()->unique()->safeEmail(),
            'Password' => bcrypt('password'),
        ];
    }

    /**
     * Indicate that the petugas is a SuperAdmin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'HakAkses' => 'SuperAdmin',
            'Jabatan' => 'Administrator Sistem',
        ]);
    }

    /**
     * Indicate that the petugas is a regular Petugas.
     */
    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'HakAkses' => 'Petugas',
        ]);
    }
}
