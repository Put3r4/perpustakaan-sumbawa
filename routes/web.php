<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LaporanPdfController;
use App\Http\Controllers\Admin\PeminjamanController;
use App\Http\Controllers\Admin\PengembalianController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

// Dashboard Utama (Public - Universal Access)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Public Bookshelf Route (accessible by everyone including guests)
Route::get('/rak-buku', [BukuController::class, 'index'])->name('rak-buku');

// Public Authentication Routes
Route::middleware('guest')->group(function () {

    // Custom categorized registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Standard session login (leveraging Fortify's controllers with our unified MultiUserProvider)
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Authentication termination
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Admin Routes - Petugas Only
Route::middleware(['auth', 'petugas'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('peminjaman')->name('peminjaman.')->group(function () {
            Route::get('/tambah', [PeminjamanController::class, 'create'])->name('create');
            Route::post('/store', [PeminjamanController::class, 'store'])->name('store');
            Route::get('/success/{kategori}/{transaksi_ids}', [PeminjamanController::class, 'success'])->name('success');
        });

        Route::prefix('pengembalian')->name('pengembalian.')->group(function () {
            Route::get('/', [PengembalianController::class, 'index'])->name('index');
            Route::post('/cek-resi', [PengembalianController::class, 'cekResi'])->name('cekResi');
            Route::post('/store', [PengembalianController::class, 'storePengembalian'])->name('store');
        });

        // Laporan Routes (Report Module)
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::get('/peminjaman', [LaporanController::class, 'laporanPeminjaman'])->name('peminjaman');
            Route::get('/pengembalian', [LaporanController::class, 'laporanPengembalian'])->name('pengembalian');
            Route::get('/buku', [LaporanController::class, 'laporanBuku'])->name('buku');
            Route::get('/denda', [LaporanController::class, 'laporanDenda'])->name('denda');
            Route::get('/anggota', [LaporanController::class, 'laporanAnggota'])->name('anggota');

            // PDF Export Routes
            Route::get('/peminjaman/pdf', [LaporanPdfController::class, 'exportPeminjamanPdf'])->name('peminjaman.pdf');
            Route::get('/pengembalian/pdf', [LaporanPdfController::class, 'exportPengembalianPdf'])->name('pengembalian.pdf');
            Route::get('/denda/pdf', [LaporanPdfController::class, 'exportDendaPdf'])->name('denda.pdf');
            Route::get('/buku/pdf', [LaporanPdfController::class, 'exportBukuPdf'])->name('buku.pdf');

            // Excel Export Routes
            Route::get('/peminjaman/excel', [LaporanPdfController::class, 'exportPeminjamanExcel'])->name('peminjaman.excel');
            Route::get('/pengembalian/excel', [LaporanPdfController::class, 'exportPengembalianExcel'])->name('pengembalian.excel');
            Route::get('/denda/excel', [LaporanPdfController::class, 'exportDendaExcel'])->name('denda.excel');
            Route::get('/buku/excel', [LaporanPdfController::class, 'exportBukuExcel'])->name('buku.excel');
        });
    });

require __DIR__.'/settings.php';
