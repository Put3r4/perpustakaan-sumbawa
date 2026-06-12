<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'KodeBuku' => 'BK'.fake()->unique()->numberBetween(1000, 9999),
            'NoUdc' => fake()->numerify('###.##'),
            'NoReg' => fake()->unique()->numerify('REG-####'),
            'Judul' => fake()->sentence(rand(3, 8)),
            'Penerbit' => fake()->company(),
            'Pengarang' => fake()->name(),
            'TahunTerbit' => fake()->year(),
            'KotaTerbit' => fake()->city(),
            'Bahasa' => fake()->randomElement(['Indonesia', 'Inggris', 'Arab', 'Jepang']),
            'Edisi' => fake()->optional()->randomElement(['Edisi 1', 'Edisi 2', 'Edisi Revisi', null]),
            'Deskripsi' => fake()->optional()->paragraph(3),
            'Isbn' => fake()->isbn13(),
            'JumEksemplar' => fake()->numberBetween(0, 10),
            'SubjekUtama' => fake()->word(),
            'SubjekTambahan' => fake()->optional()->word(),
            'views_count' => 0,
        ];
    }

    /**
     * Indicate that the book is available (has stock).
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'JumEksemplar' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the book is unavailable (no stock).
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'JumEksemplar' => 0,
        ]);
    }

    /**
     * Indicate that the book has many views.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(100, 1000),
        ]);
    }
}
