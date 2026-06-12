<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\Buku;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LaporanController extends Controller
{
    /**
     * Display the report dashboard with selection options.
     */
    public function index(): Response
    {
        // Authorization check - only petugas can access reports
        if (! auth()->check() || ! in_array(auth()->user()->HakAkses ?? '', ['Admin', 'Petugas'])) {
            abort(403, 'Akses ditolak. Hanya petugas yang dapat mengakses laporan.');
        }

        return Inertia::render('admin/laporan/index', [
            'reportTypes' => [
                [
                    'id' => 'peminjaman',
                    'title' => 'Laporan Peminjaman',
                    'description' => 'Data transaksi buku yang sedang dipinjam atau terlambat',
                    'icon' => '📤',
                ],
                [
                    'id' => 'pengembalian',
                    'title' => 'Laporan Pengembalian',
                    'description' => 'Rekapitulasi buku yang sudah dikembalikan',
                    'icon' => '📥',
                ],
                [
                    'id' => 'buku',
                    'title' => 'Laporan Analitik Buku',
                    'description' => 'Statistik peminjaman dan popularitas buku',
                    'icon' => '📊',
                ],
                [
                    'id' => 'denda',
                    'title' => 'Laporan Denda',
                    'description' => 'Akumulasi denda lunas dan piutang berjalan',
                    'icon' => '💰',
                ],
                [
                    'id' => 'anggota',
                    'title' => 'Laporan Anggota & Preferensi',
                    'description' => 'Statistik pertumbuhan anggota dan tren subjek buku',
                    'icon' => '👥',
                ],
            ],
        ]);
    }

    /**
     * Query and process data for borrowing report (Laporan Peminjaman).
     * Status: 'Dipinjam' or 'Terlambat'
     */
    public function laporanPeminjaman(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        // Union query for both Pelajar and Non-Pelajar transactions
        $transaksiPelajar = TransaksiPelajar::query()
            ->select([
                'NoPinjamP as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaP as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaPelajar:NoAnggotaP,NamaLengkap', 'buku:KodeBuku,Judul,Pengarang'])
            ->whereIn('StatusTransaksi', ['Dipinjam', 'Terlambat'])
            ->whereBetween('TglPinjam', [$tanggalMulai, $tanggalAkhir]);

        $transaksiNonPelajar = TransaksiNonPelajar::query()
            ->select([
                'NoPinjamN as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaN as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Non-Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaNonPelajar:NoAnggotaN,NamaLengkap', 'buku:KodeBuku,Judul,Pengarang'])
            ->whereIn('StatusTransaksi', ['Dipinjam', 'Terlambat'])
            ->whereBetween('TglPinjam', [$tanggalMulai, $tanggalAkhir]);

        // Union both queries
        $transaksi = $transaksiPelajar->union($transaksiNonPelajar)
            ->orderBy('TglPinjam', 'desc')
            ->get();

        // Add is_overdue property
        $today = Carbon::now()->startOfDay();
        $transaksi->transform(function ($item) use ($today) {
            $jatuhTempo = Carbon::parse($item->TglJatuhTempo)->startOfDay();
            $item->is_overdue = $today->gt($jatuhTempo);
            $item->hari_terlambat = $item->is_overdue ? $today->diffInDays($jatuhTempo) : 0;

            return $item;
        });

        return Inertia::render('admin/laporan/peminjaman', [
            'transaksi' => $transaksi,
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ],
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_terlambat' => $transaksi->where('is_overdue', true)->count(),
                'total_tepat_waktu' => $transaksi->where('is_overdue', false)->count(),
            ],
        ]);
    }

    /**
     * Query and process data for return report (Laporan Pengembalian).
     * Status: 'Dikembalikan'
     */
    public function laporanPengembalian(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        // Union query for returned books
        $transaksiPelajar = TransaksiPelajar::query()
            ->select([
                'NoPinjamP as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaP as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaPelajar:NoAnggotaP,NamaLengkap', 'buku:KodeBuku,Judul,Pengarang'])
            ->where('StatusTransaksi', 'Dikembalikan')
            ->whereBetween('TglKembali', [$tanggalMulai, $tanggalAkhir]);

        $transaksiNonPelajar = TransaksiNonPelajar::query()
            ->select([
                'NoPinjamN as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaN as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Non-Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaNonPelajar:NoAnggotaN,NamaLengkap', 'buku:KodeBuku,Judul,Pengarang'])
            ->where('StatusTransaksi', 'Dikembalikan')
            ->whereBetween('TglKembali', [$tanggalMulai, $tanggalAkhir]);

        $transaksi = $transaksiPelajar->union($transaksiNonPelajar)
            ->orderBy('TglKembali', 'desc')
            ->get();

        return Inertia::render('admin/laporan/pengembalian', [
            'transaksi' => $transaksi,
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ],
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_denda' => $transaksi->sum('Denda'),
                'total_lunas' => $transaksi->where('StatusBayarDenda', 'Lunas')->sum('Denda'),
            ],
        ]);
    }

    /**
     * Query and process data for book analytics report (Laporan Buku).
     */
    public function laporanBuku(Request $request): Response
    {
        $this->authorizeReport();

        // Most borrowed books (combining both tables)
        $bukuTerpopuler = Buku::query()
            ->withCount(['transaksiPelajar', 'transaksiNonPelajar'])
            ->get()
            ->map(fn ($buku) => [
                'KodeBuku' => $buku->KodeBuku,
                'Judul' => $buku->Judul,
                'Pengarang' => $buku->Pengarang,
                'SubjekUtama' => $buku->SubjekUtama,
                'total_peminjaman' => $buku->transaksi_pelajar_count + $buku->transaksi_non_pelajar_count,
                'views_count' => $buku->views_count,
            ])
            ->sortByDesc('total_peminjaman')
            ->take(20)
            ->values();

        // Least borrowed books
        $bukuJarangDipinjam = Buku::query()
            ->withCount(['transaksiPelajar', 'transaksiNonPelajar'])
            ->get()
            ->map(fn ($buku) => [
                'KodeBuku' => $buku->KodeBuku,
                'Judul' => $buku->Judul,
                'Pengarang' => $buku->Pengarang,
                'SubjekUtama' => $buku->SubjekUtama,
                'total_peminjaman' => $buku->transaksi_pelajar_count + $buku->transaksi_non_pelajar_count,
                'views_count' => $buku->views_count,
            ])
            ->sortBy('total_peminjaman')
            ->take(20)
            ->values();

        // Most viewed books (based on views_count tracking)
        $bukuPalingDilihat = Buku::query()
            ->orderBy('views_count', 'desc')
            ->take(20)
            ->get(['KodeBuku', 'Judul', 'Pengarang', 'SubjekUtama', 'views_count']);

        return Inertia::render('admin/laporan/buku', [
            'bukuTerpopuler' => $bukuTerpopuler,
            'bukuJarangDipinjam' => $bukuJarangDipinjam,
            'bukuPalingDilihat' => $bukuPalingDilihat,
            'summary' => [
                'total_buku' => Buku::count(),
                'total_peminjaman' => TransaksiPelajar::count() + TransaksiNonPelajar::count(),
            ],
        ]);
    }

    /**
     * Query and process data for fine report (Laporan Denda).
     */
    public function laporanDenda(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        // Query for fines (paid and unpaid)
        $transaksiPelajar = TransaksiPelajar::query()
            ->select([
                'NoPinjamP as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaP as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaPelajar:NoAnggotaP,NamaLengkap', 'buku:KodeBuku,Judul'])
            ->where('Denda', '>', 0)
            ->whereBetween(DB::raw('COALESCE(TglKembali, TglPinjam)'), [$tanggalMulai, $tanggalAkhir]);

        $transaksiNonPelajar = TransaksiNonPelajar::query()
            ->select([
                'NoPinjamN as resi',
                'TglPinjam',
                'TglJatuhTempo',
                'TglKembali',
                'NoAnggotaN as nomor_anggota',
                'KodeBuku',
                'StatusTransaksi',
                'Denda',
                'StatusBayarDenda',
                DB::raw("'Non-Pelajar' as kategori_anggota"),
            ])
            ->with(['anggotaNonPelajar:NoAnggotaN,NamaLengkap', 'buku:KodeBuku,Judul'])
            ->where('Denda', '>', 0)
            ->whereBetween(DB::raw('COALESCE(TglKembali, TglPinjam)'), [$tanggalMulai, $tanggalAkhir]);

        $transaksi = $transaksiPelajar->union($transaksiNonPelajar)
            ->orderBy('TglKembali', 'desc')
            ->get();

        // Calculate real-time running fines for unpaid items
        $today = Carbon::now()->startOfDay();
        $transaksi->transform(function ($item) use ($today) {
            if ($item->StatusBayarDenda === 'Belum_Lunas' && $item->TglKembali === null) {
                // Calculate real-time fine for ongoing late returns
                $jatuhTempo = Carbon::parse($item->TglJatuhTempo)->startOfDay();
                if ($today->gt($jatuhTempo)) {
                    $hariTerlambat = $today->diffInDays($jatuhTempo);
                    $item->denda_realtime = $hariTerlambat * 500;
                    $item->hari_terlambat = $hariTerlambat;
                } else {
                    $item->denda_realtime = 0;
                    $item->hari_terlambat = 0;
                }
            } else {
                $item->denda_realtime = (float) $item->Denda;
                $item->hari_terlambat = $item->Denda / 500;
            }

            return $item;
        });

        $dendaLunas = $transaksi->where('StatusBayarDenda', 'Lunas')->sum('Denda');
        $dendaBelumLunas = $transaksi->where('StatusBayarDenda', 'Belum_Lunas')->sum('denda_realtime');

        return Inertia::render('admin/laporan/denda', [
            'transaksi' => $transaksi,
            'filter' => [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_akhir' => $tanggalAkhir,
            ],
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_denda_lunas' => $dendaLunas,
                'total_piutang' => $dendaBelumLunas,
                'total_denda_keseluruhan' => $dendaLunas + $dendaBelumLunas,
            ],
        ]);
    }

    /**
     * Query and process data for member and preference report (Laporan Anggota & Preferensi).
     */
    public function laporanAnggota(Request $request): Response
    {
        $this->authorizeReport();

        // Member statistics
        $totalPelajar = AnggotaPelajar::count();
        $totalNonPelajar = AnggotaNonPelajar::count();

        // Subject preferences for Pelajar
        $preferensiPelajar = TransaksiPelajar::query()
            ->join('buku', 'transaksi_pelajar.KodeBuku', '=', 'buku.KodeBuku')
            ->select('buku.SubjekUtama', DB::raw('COUNT(*) as total'))
            ->groupBy('buku.SubjekUtama')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Subject preferences for Non-Pelajar
        $preferensiNonPelajar = TransaksiNonPelajar::query()
            ->join('buku', 'transaksi_non_pelajar.KodeBuku', '=', 'buku.KodeBuku')
            ->select('buku.SubjekUtama', DB::raw('COUNT(*) as total'))
            ->groupBy('buku.SubjekUtama')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Growth statistics (last 12 months)
        $pertumbuhanPelajar = AnggotaPelajar::query()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $pertumbuhanNonPelajar = AnggotaNonPelajar::query()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as bulan'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return Inertia::render('admin/laporan/anggota', [
            'summary' => [
                'total_pelajar' => $totalPelajar,
                'total_non_pelajar' => $totalNonPelajar,
                'total_anggota' => $totalPelajar + $totalNonPelajar,
            ],
            'preferensiPelajar' => $preferensiPelajar,
            'preferensiNonPelajar' => $preferensiNonPelajar,
            'pertumbuhanPelajar' => $pertumbuhanPelajar,
            'pertumbuhanNonPelajar' => $pertumbuhanNonPelajar,
        ]);
    }

    /**
     * Authorize report access - only petugas can access.
     */
    private function authorizeReport(): void
    {
        if (! auth()->check()) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        $hakAkses = auth()->user()->HakAkses ?? '';

        if (! in_array($hakAkses, ['Admin', 'Petugas'])) {
            abort(403, 'Akses ditolak. Hanya petugas yang dapat mengakses laporan.');
        }
    }
}
