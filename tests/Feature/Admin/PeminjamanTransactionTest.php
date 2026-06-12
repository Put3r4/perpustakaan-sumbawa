<?php

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Buku;
use App\Models\Petugas;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('guest is redirected and non-petugas get 403 on peminjaman routes', function () {
    $this->get(route('admin.peminjaman.create'))
        ->assertRedirect(route('login'));

    $pelajar = AnggotaPelajar::factory()->create();

    $this->actingAs($pelajar)
        ->get(route('admin.peminjaman.create'))
        ->assertForbidden();

    $nonPelajar = AnggotaNonPelajar::factory()->create();

    $this->actingAs($nonPelajar)
        ->get(route('admin.peminjaman.create'))
        ->assertForbidden();

    $this->actingAs($pelajar)
        ->post(route('admin.peminjaman.store'), [
            'kategori_anggota' => 'pelajar',
            'id_anggota' => $pelajar->NoAnggotaP,
            'buku_pilihan' => [Buku::factory()->create()->KodeBuku],
        ])
        ->assertForbidden();
});

test('peminjaman is rejected when anggota already has 2 active books', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $bukuBaru = Buku::factory()->create(['JumEksemplar' => 5]);

    TransaksiPelajar::factory()
        ->count(2)
        ->create([
            'NoAnggotaP' => $anggota->NoAnggotaP,
            'KodePetugas' => $petugas->KodePetugas,
            'StatusTransaksi' => 'Dipinjam',
            'StatusBayarDenda' => 'Lunas',
        ]);

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'pelajar',
        'id_anggota' => $anggota->NoAnggotaP,
        'buku_pilihan' => [$bukuBaru->KodeBuku],
    ]);

    $response->assertSessionHasErrors('buku_pilihan');
});

test('peminjaman is blocked when anggota has belum lunas status', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaNonPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 5]);

    TransaksiNonPelajar::factory()
        ->for($anggota, 'anggotaNonPelajar')
        ->belumLunas()
        ->create([
            'NoAnggotaN' => $anggota->NoAnggotaN,
            'KodePetugas' => $petugas->KodePetugas,
        ]);

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'non_pelajar',
        'id_anggota' => $anggota->NoAnggotaN,
        'buku_pilihan' => [$buku->KodeBuku],
    ]);

    $response->assertSessionHasErrors('id_anggota');
    expect($response->getSession()->get('errors')->first('id_anggota'))
        ->toContain('diblokir');
});

test('peminjaman is blocked when anggota has terlambat status', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 5]);

    TransaksiPelajar::factory()
        ->for($anggota, 'anggotaPelajar')
        ->terlambat()
        ->create([
            'NoAnggotaP' => $anggota->NoAnggotaP,
            'KodePetugas' => $petugas->KodePetugas,
        ]);

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'pelajar',
        'id_anggota' => $anggota->NoAnggotaP,
        'buku_pilihan' => [$buku->KodeBuku],
    ]);

    $response->assertSessionHasErrors('id_anggota');
    expect($response->getSession()->get('errors')->first('id_anggota'))
        ->toContain('Terlambat');
});

test('tgl jatuh tempo is correctly set to 7 days from today', function () {
    Carbon::setTestNow('2026-06-11');

    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 5]);

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'pelajar',
        'id_anggota' => $anggota->NoAnggotaP,
        'buku_pilihan' => [$buku->KodeBuku],
    ]);

    $response->assertRedirect();

    $transaksi = TransaksiPelajar::where('NoAnggotaP', $anggota->NoAnggotaP)
        ->where('KodeBuku', $buku->KodeBuku)
        ->first();

    expect($transaksi)->not->toBeNull();
    expect($transaksi->TglPinjam->toDateString())->toBe('2026-06-11');
    expect($transaksi->TglJatuhTempo->toDateString())->toBe('2026-06-18');
    expect((int) $transaksi->TglPinjam->diffInDays($transaksi->TglJatuhTempo))->toBe(7);
    expect($transaksi->StatusTransaksi)->toBe('Dipinjam');

    Carbon::setTestNow();
});

test('successful peminjaman decrements book stock', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaNonPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 10]);

    $initialStock = $buku->JumEksemplar;

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'non_pelajar',
        'id_anggota' => $anggota->NoAnggotaN,
        'buku_pilihan' => [$buku->KodeBuku],
    ]);

    $response->assertRedirect();

    $buku->refresh();
    expect($buku->JumEksemplar)->toBe($initialStock - 1);
});

test('peminjaman with 2 books decrements both stocks', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku1 = Buku::factory()->create(['JumEksemplar' => 5]);
    $buku2 = Buku::factory()->create(['JumEksemplar' => 3]);

    $initialStock1 = $buku1->JumEksemplar;
    $initialStock2 = $buku2->JumEksemplar;

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'pelajar',
        'id_anggota' => $anggota->NoAnggotaP,
        'buku_pilihan' => [$buku1->KodeBuku, $buku2->KodeBuku],
    ]);

    $response->assertRedirect();

    $buku1->refresh();
    $buku2->refresh();

    expect($buku1->JumEksemplar)->toBe($initialStock1 - 1);
    expect($buku2->JumEksemplar)->toBe($initialStock2 - 1);

    expect(TransaksiPelajar::where('NoAnggotaP', $anggota->NoAnggotaP)->count())->toBe(2);
});

test('uuid is auto generated for transaction', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 5]);

    $response = $this->actingAs($petugas)->post(route('admin.peminjaman.store'), [
        'kategori_anggota' => 'pelajar',
        'id_anggota' => $anggota->NoAnggotaP,
        'buku_pilihan' => [$buku->KodeBuku],
    ]);

    $response->assertRedirect();

    $transaksi = TransaksiPelajar::where('NoAnggotaP', $anggota->NoAnggotaP)->first();

    expect($transaksi->NoPinjamP)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});
