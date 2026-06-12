<?php

declare(strict_types=1);

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Petugas;
use App\Models\TransaksiPelajar;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('dashboard is accessible by everyone without authentication', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('dashboard'));
});

test('dashboard returns correct props structure', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('petugasPiket')
        ->has('stats')
        ->has('stats.totalAnggotaAktif')
        ->has('stats.totalBukuTerpinjam')
        ->has('kunjunganChart')
        ->has('kunjunganChart.labels')
        ->has('kunjunganChart.datasets')
    );
});

test('dashboard stats reflect database counts', function () {
    // Create test data
    AnggotaPelajar::factory()->count(5)->create();
    AnggotaNonPelajar::factory()->count(3)->create();

    $petugas = Petugas::factory()->create();
    $anggotaPelajar = AnggotaPelajar::factory()->create();

    // Create borrowed books
    TransaksiPelajar::factory()->count(2)->create([
        'NoAnggotaP' => $anggotaPelajar->NoAnggotaP,
        'KodePetugas' => $petugas->KodePetugas,
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('stats.totalAnggotaAktif', 9) // 5 + 3 + 1 from above
        ->where('stats.totalBukuTerpinjam', 2)
    );
});

test('dashboard kunjungan chart has 7 days of data', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('kunjunganChart.labels', fn ($labels) => count($labels) === 7)
        ->where('kunjunganChart.datasets', fn ($datasets) => count($datasets) === 7)
    );
});

test('dashboard works even with empty database', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('stats.totalAnggotaAktif', 0)
        ->where('stats.totalBukuTerpinjam', 0)
        ->where('petugasPiket', [])
    );
});
