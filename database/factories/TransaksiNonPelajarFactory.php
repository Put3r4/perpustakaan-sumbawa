<?php

namespace Database\Factories;

use App\Models\AnggotaNonPelajar;
use App\Models\Buku;
use App\Models\Petugas;
use App\Models\TransaksiNonPelajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransaksiNonPelajar>
 */
class TransaksiNonPelajarFactory extends Factory
{
    protected $model = TransaksiNonPelajar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'TglPinjam' => now()->startOfDay(),
            'TglJatuhTempo' => now()->addDays(7)->startOfDay(),
            'TglKembali' => null,
            'NoAnggotaN' => AnggotaNonPelajar::factory(),
            'KodeBuku' => Buku::factory(),
            'KodePetugas' => Petugas::factory(),
            'KodePetugasKembali' => null,
            'Denda' => 0.00,
            'StatusBayarDenda' => 'Lunas',
            'StatusTransaksi' => 'Dipinjam',
        ];
    }

    /**
     * Indicate that the transaction is overdue (Terlambat).
     */
    public function terlambat(): static
    {
        return $this->state(fn () => [
            'TglPinjam' => now()->subDays(15)->startOfDay(),
            'TglJatuhTempo' => now()->subDays(8)->startOfDay(),
            'TglKembali' => null,
            'Denda' => 0.00,
            'StatusBayarDenda' => 'Lunas',
            'StatusTransaksi' => 'Terlambat',
        ]);
    }

    /**
     * Indicate that the transaction has unpaid fine (Belum_Lunas).
     */
    public function belumLunas(): static
    {
        return $this->state(fn () => [
            'TglPinjam' => now()->subDays(20)->startOfDay(),
            'TglJatuhTempo' => now()->subDays(13)->startOfDay(),
            'TglKembali' => now()->subDays(5)->startOfDay(),
            'Denda' => 5000.00,
            'StatusBayarDenda' => 'Belum_Lunas',
            'StatusTransaksi' => 'Dikembalikan',
        ]);
    }

    /**
     * Indicate that the transaction has been returned.
     */
    public function dikembalikan(): static
    {
        return $this->state(fn () => [
            'TglKembali' => now()->subDays(1)->startOfDay(),
            'Denda' => 0.00,
            'StatusBayarDenda' => 'Lunas',
            'StatusTransaksi' => 'Dikembalikan',
        ]);
    }
}
