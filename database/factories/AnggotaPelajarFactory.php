<?php

namespace Database\Factories;

use App\Models\AnggotaPelajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnggotaPelajar>
 */
class AnggotaPelajarFactory extends Factory
{
    protected $model = AnggotaPelajar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'NoAnggotaP' => 'AP'.fake()->unique()->numberBetween(100, 999),
            'NIM_NIS' => fake()->unique()->numerify('##########'),
            'NamaAnggotaP' => fake()->name(),
            'AsalSekolah' => fake()->company().' School',
            'TTL' => fake()->city().', '.fake()->date(),
            'Alamat' => fake()->address(),
            'KodePos' => fake()->postcode(),
            'NoTelp1' => fake()->phoneNumber(),
            'NoTelp2' => fake()->optional()->phoneNumber(),
            'TglDaftar' => now()->startOfDay(),
            'NamaOrtu' => fake()->name(),
            'AlamatOrtu' => fake()->address(),
            'NoTelpOrtu' => fake()->phoneNumber(),
            'Email' => fake()->unique()->safeEmail(),
            'Password' => bcrypt('password'),
        ];
    }
}
