<?php

declare(strict_types=1);

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Buku;
use App\Models\Petugas;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('pengembalian tepat waktu has zero denda and dikembalikan status', function () {
    Carbon::setTestNow('2026-06-15');

    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 5]);

    // Create active loan (not late)
    $transaksi = TransaksiPelajar::create([
        'TglPinjam' => Carbon::parse('2026-06-10'),
        'TglJatuhTempo' => Carbon::parse('2026-06-17'), // Still has 2 days
        'NoAnggotaP' => $anggota->NoAnggotaP,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 0.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $initialStock = $buku->JumEksemplar;

    // Process return
    $response = $this->actingAs($petugas)->post(route('admin.pengembalian.store'), [
        'uuid_resi' => $transaksi->NoPinjamP,
        'kategori' => 'pelajar',
        'status_bayar_denda' => 'Lunas',
    ]);

    $response->assertRedirect(route('admin.pengembalian.index'));

    // Verify transaction updated
    $transaksi->refresh();
    expect($transaksi->TglKembali->toDateString())->toBe('2026-06-15');
    expect((float) $transaksi->Denda)->toBe(0.0);
    expect($transaksi->StatusTransaksi)->toBe('Dikembalikan');
    expect($transaksi->StatusBayarDenda)->toBe('Lunas');
    expect($transaksi->KodePetugasKembali)->toBe($petugas->KodePetugas);

    // Verify book stock incremented
    $buku->refresh();
    expect($buku->JumEksemplar)->toBe($initialStock + 1);

    Carbon::setTestNow();
});

test('pengembalian terlambat calculates correct denda 500 per day', function () {
    Carbon::setTestNow('2026-06-20'); // 3 days late

    $petugas = Petugas::factory()->create();
    $anggota = AnggotaNonPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 3]);

    // Create late loan
    $transaksi = TransaksiNonPelajar::create([
        'TglPinjam' => Carbon::parse('2026-06-10'),
        'TglJatuhTempo' => Carbon::parse('2026-06-17'), // Due 3 days ago
        'NoAnggotaN' => $anggota->NoAnggotaN,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 0.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $initialStock = $buku->JumEksemplar;

    // Process return with payment
    $response = $this->actingAs($petugas)->post(route('admin.pengembalian.store'), [
        'uuid_resi' => $transaksi->NoPinjamN,
        'kategori' => 'non_pelajar',
        'status_bayar_denda' => 'Lunas',
    ]);

    $response->assertRedirect(route('admin.pengembalian.index'));

    // Verify denda calculation (3 days * Rp500)
    $transaksi->refresh();
    expect($transaksi->TglKembali->toDateString())->toBe('2026-06-20');
    expect((float) $transaksi->Denda)->toBe(1500.0);
    expect($transaksi->StatusTransaksi)->toBe('Terlambat');
    expect($transaksi->StatusBayarDenda)->toBe('Lunas');

    // Verify book stock incremented
    $buku->refresh();
    expect($buku->JumEksemplar)->toBe($initialStock + 1);

    Carbon::setTestNow();
});

test('pengembalian terlambat with belum lunas keeps unpaid status', function () {
    Carbon::setTestNow('2026-06-25'); // 8 days late

    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 10]);

    // Create very late loan
    $transaksi = TransaksiPelajar::create([
        'TglPinjam' => Carbon::parse('2026-06-10'),
        'TglJatuhTempo' => Carbon::parse('2026-06-17'), // Due 8 days ago
        'NoAnggotaP' => $anggota->NoAnggotaP,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 0.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dipinjam',
    ]);

    // Process return without payment
    $response = $this->actingAs($petugas)->post(route('admin.pengembalian.store'), [
        'uuid_resi' => $transaksi->NoPinjamP,
        'kategori' => 'pelajar',
        'status_bayar_denda' => 'Belum_Lunas',
    ]);

    $response->assertRedirect(route('admin.pengembalian.index'));

    // Verify denda calculation (8 days * Rp500)
    $transaksi->refresh();
    expect((float) $transaksi->Denda)->toBe(4000.0);
    expect($transaksi->StatusTransaksi)->toBe('Terlambat');
    expect($transaksi->StatusBayarDenda)->toBe('Belum_Lunas');

    Carbon::setTestNow();
});

test('cek resi returns correct data for pelajar transaction', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create([
        'Judul' => 'Pemrograman Laravel',
        'Penerbit' => 'Gramedia',
    ]);

    $transaksi = TransaksiPelajar::create([
        'TglPinjam' => Carbon::today()->subDays(5),
        'TglJatuhTempo' => Carbon::today()->subDays(2), // 2 days late
        'NoAnggotaP' => $anggota->NoAnggotaP,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 0.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $response = $this->actingAs($petugas)->postJson(route('admin.pengembalian.cekResi'), [
        'uuid_resi' => $transaksi->NoPinjamP,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'kategori' => 'pelajar',
    ]);

    $data = $response->json('data');
    expect($data['nama_anggota'])->toBe($anggota->NamaLengkap);
    expect($data['judul_buku'])->toBe('Pemrograman Laravel');
    expect($data['hari_terlambat'])->toBe(2);
    expect((float) $data['nominal_denda'])->toBe(1000.0);
});

test('cek resi returns 404 for invalid uuid', function () {
    $petugas = Petugas::factory()->create();

    $response = $this->actingAs($petugas)->postJson(route('admin.pengembalian.cekResi'), [
        'uuid_resi' => '00000000-0000-0000-0000-000000000000',
    ]);

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
    ]);
});

test('cek resi returns 404 for already returned book', function () {
    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create();

    // Create already returned transaction
    $transaksi = TransaksiPelajar::create([
        'TglPinjam' => Carbon::today()->subDays(10),
        'TglJatuhTempo' => Carbon::today()->subDays(3),
        'TglKembali' => Carbon::today()->subDays(1),
        'NoAnggotaP' => $anggota->NoAnggotaP,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 1000.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dikembalikan',
    ]);

    $response = $this->actingAs($petugas)->postJson(route('admin.pengembalian.cekResi'), [
        'uuid_resi' => $transaksi->NoPinjamP,
    ]);

    $response->assertNotFound();
});

test('book stock is restored after pengembalian', function () {
    Carbon::setTestNow('2026-06-15');

    $petugas = Petugas::factory()->create();
    $anggota = AnggotaPelajar::factory()->create();
    $buku = Buku::factory()->create(['JumEksemplar' => 7]);

    $transaksi = TransaksiPelajar::create([
        'TglPinjam' => Carbon::parse('2026-06-10'),
        'TglJatuhTempo' => Carbon::parse('2026-06-17'),
        'NoAnggotaP' => $anggota->NoAnggotaP,
        'KodeBuku' => $buku->KodeBuku,
        'KodePetugas' => $petugas->KodePetugas,
        'Denda' => 0.00,
        'StatusBayarDenda' => 'Lunas',
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $stockBefore = $buku->JumEksemplar;

    $this->actingAs($petugas)->post(route('admin.pengembalian.store'), [
        'uuid_resi' => $transaksi->NoPinjamP,
        'kategori' => 'pelajar',
        'status_bayar_denda' => 'Lunas',
    ]);

    $buku->refresh();
    expect($buku->JumEksemplar)->toBe($stockBefore + 1);

    Carbon::setTestNow();
});

test('guests and non-petugas cannot access pengembalian routes', function () {
    // Guest test
    $response = $this->get(route('admin.pengembalian.index'));
    $response->assertRedirect(route('login'));

    // Pelajar test
    $pelajar = AnggotaPelajar::factory()->create();
    $response = $this->actingAs($pelajar)->get(route('admin.pengembalian.index'));
    $response->assertForbidden();

    // Non-Pelajar test
    $nonPelajar = AnggotaNonPelajar::factory()->create();
    $response = $this->actingAs($nonPelajar)->get(route('admin.pengembalian.index'));
    $response->assertForbidden();
});
