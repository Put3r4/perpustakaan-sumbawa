<?php

use App\Models\Buku;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('guests can view rak buku page', function () {
    $response = $this->get(route('rak-buku'));

    $response->assertOk();
    $response->assertViewIs('rak-buku');
    $response->assertViewHas('books');
});

test('rak buku displays books with pagination', function () {
    // Create 25 books (more than pagination limit of 20)
    Buku::factory()->count(25)->create();

    $response = $this->get(route('rak-buku'));

    $response->assertOk();
    $response->assertViewIs('rak-buku');

    $books = $response->viewData('books');
    expect($books)->toHaveCount(20); // Should only show 20 per page
    expect($books->total())->toBe(25); // Total should be 25
});

test('api can increment book view count', function () {
    $buku = Buku::factory()->create(['views_count' => 0]);

    $response = $this->postJson("/api/buku/{$buku->KodeBuku}/increment-view");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'KodeBuku' => $buku->KodeBuku,
                'views_count' => 1,
            ],
        ]);

    $this->assertDatabaseHas('buku', [
        'KodeBuku' => $buku->KodeBuku,
        'views_count' => 1,
    ]);
});

test('api returns error for invalid book id', function () {
    $response = $this->postJson('/api/buku/INVALID_ID/increment-view');

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('api increment view is throttled', function () {
    $buku = Buku::factory()->create(['views_count' => 0]);

    // Make 11 requests (throttle is set to 10 per minute)
    for ($i = 0; $i < 11; $i++) {
        $response = $this->postJson("/api/buku/{$buku->KodeBuku}/increment-view");

        if ($i < 10) {
            $response->assertOk();
        } else {
            $response->assertStatus(429); // Too Many Requests
        }
    }
});

test('petugas can see sirkulasi buttons', function () {
    $petugas = Petugas::factory()->create(['HakAkses' => 'Petugas']);

    $response = $this->actingAs($petugas)->get(route('rak-buku'));

    $response->assertOk();
    $response->assertSee('Proses Peminjaman');
    $response->assertSee('Proses Pengembalian');
});

test('non petugas cannot see sirkulasi buttons', function () {
    Buku::factory()->create();

    $response = $this->get(route('rak-buku'));

    $response->assertOk();
    $response->assertDontSee('Proses Peminjaman');
    $response->assertDontSee('Proses Pengembalian');
});
