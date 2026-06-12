<?php

namespace Database\Factories;

use App\Models\AnggotaNonPelajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnggotaNonPelajar>
 */
class AnggotaNonPelajarFactory extends Factory
{
    protected $model = AnggotaNonPelajar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'NoAnggotaN' => 'AN'.fake()->unique()->numberBetween(100, 999),
            'NIK' => fake()->unique()->numerify('################'),
            'NamaAnggotaN' => fake()->name(),
            'Pekerjaan' => fake()->jobTitle(),
            'TTL' => fake()->city().', '.fake()->date(),
            'Alamat' => fake()->address(),
            'KodePos' => fake()->postcode(),
            'NoTelp1' => fake()->phoneNumber(),
            'NoTelp2' => fake()->optional()->phoneNumber(),
            'TglDaftar' => now()->startOfDay(),
            'Email' => fake()->unique()->safeEmail(),
            'Password' => bcrypt('password'),
        ];
    }
}
