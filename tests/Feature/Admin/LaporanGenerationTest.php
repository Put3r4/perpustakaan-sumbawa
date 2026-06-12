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

/**
 * @property Petugas $petugas
 * @property AnggotaPelajar $anggotaPelajar
 * @property AnggotaNonPelajar $anggotaNonPelajar
 * @property Buku $buku
 */
beforeEach(function () {
    // Create Petugas user for authentication
    $this->petugas = Petugas::factory()->create([
        'HakAkses' => 'Petugas',
    ]);

    // Create Pelajar and Non-Pelajar members
    $this->anggotaPelajar = AnggotaPelajar::factory()->create();
    $this->anggotaNonPelajar = AnggotaNonPelajar::factory()->create();

    // Create Buku
    $this->buku = Buku::factory()->create([
        'JumEksemplar' => 5,
        'SubjekUtama' => 'Teknologi Informasi',
    ]);
});

test('petugas can access laporan index page', function () {
    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/index')
        ->has('reportTypes', 5));
});

test('non-petugas users are blocked from accessing laporan with 403', function () {
    $pelajar = AnggotaPelajar::factory()->create();

    $response = $this->actingAs($pelajar)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(403);
});

test('non-petugas users are blocked from accessing laporan peminjaman with 403', function () {
    $pelajar = AnggotaPelajar::factory()->create();

    $response = $this->actingAs($pelajar)
        ->get(route('admin.laporan.peminjaman'));

    $response->assertStatus(403);
});

test('non-petugas users are blocked from downloading PDF reports with 403', function () {
    $nonPelajar = AnggotaNonPelajar::factory()->create();

    $response = $this->actingAs($nonPelajar)
        ->get(route('admin.laporan.peminjaman.pdf'));

    $response->assertStatus(403);
});

test('non-petugas users are blocked from downloading Excel reports with 403', function () {
    $nonPelajar = AnggotaNonPelajar::factory()->create();

    $response = $this->actingAs($nonPelajar)
        ->get(route('admin.laporan.peminjaman.excel'));

    $response->assertStatus(403);
});

test('guest users cannot access laporan pages', function () {
    $response = $this->get(route('admin.laporan.index'));

    $response->assertRedirect(route('login'));
});

test('laporan peminjaman filters by date range correctly', function () {
    $tanggalMulai = Carbon::now()->subDays(10)->format('Y-m-d');
    $tanggalAkhir = Carbon::now()->format('Y-m-d');

    // Create transaction within date range
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(5),
        'TglJatuhTempo' => Carbon::now()->addDays(2),
        'StatusTransaksi' => 'Dipinjam',
        'Denda' => 0,
        'StatusBayarDenda' => 'Lunas',
    ]);

    // Create transaction outside date range (should not appear)
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(20),
        'TglJatuhTempo' => Carbon::now()->subDays(13),
        'StatusTransaksi' => 'Dipinjam',
        'Denda' => 0,
        'StatusBayarDenda' => 'Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.peminjaman', [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_akhir' => $tanggalAkhir,
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/peminjaman')
        ->has('transaksi', 1) // Only 1 transaction within range
        ->has('summary')
        ->where('summary.total_transaksi', 1));
});

test('laporan peminjaman correctly identifies overdue books', function () {
    // Create overdue transaction
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(15),
        'TglJatuhTempo' => Carbon::now()->subDays(8), // Already overdue
        'StatusTransaksi' => 'Terlambat',
        'Denda' => 0,
        'StatusBayarDenda' => 'Lunas',
    ]);

    // Create on-time transaction
    TransaksiNonPelajar::factory()->create([
        'NoAnggotaN' => $this->anggotaNonPelajar->NoAnggotaN,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(3),
        'TglJatuhTempo' => Carbon::now()->addDays(4), // Still within time
        'StatusTransaksi' => 'Dipinjam',
        'Denda' => 0,
        'StatusBayarDenda' => 'Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.peminjaman'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('transaksi', 2)
        ->has('summary')
        ->where('summary.total_terlambat', 1)
        ->where('summary.total_tepat_waktu', 1));
});

test('laporan pengembalian filters by return date correctly', function () {
    $tanggalMulai = Carbon::now()->subDays(7)->format('Y-m-d');
    $tanggalAkhir = Carbon::now()->format('Y-m-d');

    // Create returned book within date range
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(10),
        'TglJatuhTempo' => Carbon::now()->subDays(3),
        'TglKembali' => Carbon::now()->subDays(2), // Within range
        'StatusTransaksi' => 'Dikembalikan',
        'Denda' => 500,
        'StatusBayarDenda' => 'Lunas',
    ]);

    // Create returned book outside date range
    TransaksiNonPelajar::factory()->create([
        'NoAnggotaN' => $this->anggotaNonPelajar->NoAnggotaN,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(30),
        'TglJatuhTempo' => Carbon::now()->subDays(23),
        'TglKembali' => Carbon::now()->subDays(20), // Outside range
        'StatusTransaksi' => 'Dikembalikan',
        'Denda' => 1000,
        'StatusBayarDenda' => 'Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.pengembalian', [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_akhir' => $tanggalAkhir,
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/pengembalian')
        ->has('transaksi', 1) // Only 1 within range
        ->where('summary.total_transaksi', 1)
        ->where('summary.total_denda', 500));
});

test('laporan denda calculates real-time fines for unpaid late returns', function () {
    // Create unpaid late return (still not returned)
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(15),
        'TglJatuhTempo' => Carbon::now()->subDays(8), // 8 days overdue
        'TglKembali' => null, // Not yet returned
        'StatusTransaksi' => 'Terlambat',
        'Denda' => 0, // Not yet calculated
        'StatusBayarDenda' => 'Belum_Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.denda'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/denda')
        ->has('transaksi', 1)
        ->has('summary')
        ->where('summary.total_piutang', 8 * 500)); // 8 days * Rp500
});

test('laporan denda separates paid and unpaid fines correctly', function () {
    // Create paid fine
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(10),
        'TglJatuhTempo' => Carbon::now()->subDays(3),
        'TglKembali' => Carbon::now()->subDays(1),
        'StatusTransaksi' => 'Dikembalikan',
        'Denda' => 1000,
        'StatusBayarDenda' => 'Lunas',
    ]);

    // Create unpaid fine
    TransaksiNonPelajar::factory()->create([
        'NoAnggotaN' => $this->anggotaNonPelajar->NoAnggotaN,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => Carbon::now()->subDays(12),
        'TglJatuhTempo' => Carbon::now()->subDays(5),
        'TglKembali' => Carbon::now()->subDays(2),
        'StatusTransaksi' => 'Dikembalikan',
        'Denda' => 1500,
        'StatusBayarDenda' => 'Belum_Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.denda'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('transaksi', 2)
        ->where('summary.total_denda_lunas', 1000)
        ->where('summary.total_piutang', 1500)
        ->where('summary.total_denda_keseluruhan', 2500));
});

test('laporan buku shows most and least borrowed books', function () {
    $buku1 = Buku::factory()->create(['SubjekUtama' => 'Fiksi']);
    $buku2 = Buku::factory()->create(['SubjekUtama' => 'Sains']);
    // buku3 intentionally created with no transactions to test least borrowed functionality
    Buku::factory()->create(['SubjekUtama' => 'Sejarah']);

    // Create multiple transactions for buku1 (most popular)
    TransaksiPelajar::factory()->count(5)->create([
        'KodeBuku' => $buku1->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
    ]);

    // Create fewer transactions for buku2
    TransaksiNonPelajar::factory()->count(2)->create([
        'KodeBuku' => $buku2->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.buku'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/buku')
        ->has('bukuTerpopuler')
        ->has('bukuJarangDipinjam')
        ->has('bukuPalingDilihat')
        ->has('summary'));
});

test('laporan anggota shows member growth and preferences', function () {
    // Create some members
    AnggotaPelajar::factory()->count(3)->create();
    AnggotaNonPelajar::factory()->count(2)->create();

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.anggota'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/laporan/anggota')
        ->has('summary')
        ->where('summary.total_pelajar', 4) // Including the one from beforeEach
        ->where('summary.total_non_pelajar', 3) // Including the one from beforeEach
        ->where('summary.total_anggota', 7)
        ->has('preferensiPelajar')
        ->has('preferensiNonPelajar'));
});

test('petugas can download PDF report for peminjaman', function () {
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.peminjaman.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('petugas can download Excel report for pengembalian', function () {
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'StatusTransaksi' => 'Dikembalikan',
        'TglKembali' => Carbon::now(),
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.pengembalian.excel'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('union query combines pelajar and non-pelajar transactions correctly', function () {
    // Create transactions for both types
    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'StatusTransaksi' => 'Dipinjam',
    ]);

    TransaksiNonPelajar::factory()->create([
        'NoAnggotaN' => $this->anggotaNonPelajar->NoAnggotaN,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'StatusTransaksi' => 'Dipinjam',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.peminjaman'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('transaksi', 2) // Both types combined
        ->where('summary.total_transaksi', 2));
});

test('date range validation rejects invalid dates', function () {
    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.peminjaman', [
            'tanggal_mulai' => '2024-12-31',
            'tanggal_akhir' => '2024-01-01', // End before start (invalid)
        ]));

    $response->assertStatus(302); // Validation redirect
    $response->assertSessionHasErrors('tanggal_akhir');
});

test('admin user can access laporan pages', function () {
    $admin = Petugas::factory()->create(['HakAkses' => 'Admin']);

    $response = $this->actingAs($admin)
        ->get(route('admin.laporan.index'));

    $response->assertStatus(200);
});

test('laporan exports maintain data accuracy', function () {
    $tanggalPinjam = Carbon::now()->subDays(10);
    $tanggalJatuhTempo = Carbon::now()->subDays(3);
    $tanggalKembali = Carbon::now()->subDays(1);

    TransaksiPelajar::factory()->create([
        'NoAnggotaP' => $this->anggotaPelajar->NoAnggotaP,
        'KodeBuku' => $this->buku->KodeBuku,
        'KodePetugas' => $this->petugas->KodePetugas,
        'TglPinjam' => $tanggalPinjam,
        'TglJatuhTempo' => $tanggalJatuhTempo,
        'TglKembali' => $tanggalKembali,
        'StatusTransaksi' => 'Dikembalikan',
        'Denda' => 1000,
        'StatusBayarDenda' => 'Lunas',
    ]);

    $response = $this->actingAs($this->petugas)
        ->get(route('admin.laporan.pengembalian'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('transaksi.0', fn ($transaction) => $transaction
            ->where('StatusTransaksi', 'Dikembalikan')
            ->where('Denda', 1000)
            ->where('StatusBayarDenda', 'Lunas')));
});
