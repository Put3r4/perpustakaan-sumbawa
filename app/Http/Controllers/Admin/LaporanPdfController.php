<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanSirkulasiExport;
use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanPdfController extends Controller
{
    /**
     * Export Laporan Peminjaman to PDF.
     */
    public function exportPeminjamanPdf(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataPeminjaman($tanggalMulai, $tanggalAkhir);

        $pdf = Pdf::loadView('pdf.laporan-peminjaman', [
            'transaksi' => $data['transaksi'],
            'filter' => ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir],
            'summary' => $data['summary'],
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM YYYY'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-peminjaman-'.date('Y-m-d').'.pdf');
    }

    /**
     * Export Laporan Peminjaman to Excel.
     */
    public function exportPeminjamanExcel(Request $request): BinaryFileResponse
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataPeminjaman($tanggalMulai, $tanggalAkhir);

        return Excel::download(
            new LaporanSirkulasiExport(
                collect($data['transaksi']),
                'peminjaman',
                ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir]
            ),
            'laporan-peminjaman-'.date('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Laporan Pengembalian to PDF.
     */
    public function exportPengembalianPdf(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataPengembalian($tanggalMulai, $tanggalAkhir);

        $pdf = Pdf::loadView('pdf.laporan-pengembalian', [
            'transaksi' => $data['transaksi'],
            'filter' => ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir],
            'summary' => $data['summary'],
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM YYYY'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-pengembalian-'.date('Y-m-d').'.pdf');
    }

    /**
     * Export Laporan Pengembalian to Excel.
     */
    public function exportPengembalianExcel(Request $request): BinaryFileResponse
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataPengembalian($tanggalMulai, $tanggalAkhir);

        return Excel::download(
            new LaporanSirkulasiExport(
                collect($data['transaksi']),
                'pengembalian',
                ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir]
            ),
            'laporan-pengembalian-'.date('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Laporan Denda to PDF.
     */
    public function exportDendaPdf(Request $request): Response
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataDenda($tanggalMulai, $tanggalAkhir);

        $pdf = Pdf::loadView('pdf.laporan-denda', [
            'transaksi' => $data['transaksi'],
            'filter' => ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir],
            'summary' => $data['summary'],
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM YYYY'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-denda-'.date('Y-m-d').'.pdf');
    }

    /**
     * Export Laporan Denda to Excel.
     */
    public function exportDendaExcel(Request $request): BinaryFileResponse
    {
        $this->authorizeReport();

        $validated = $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $tanggalAkhir = $validated['tanggal_akhir'] ?? Carbon::now()->format('Y-m-d');

        $data = $this->getDataDenda($tanggalMulai, $tanggalAkhir);

        return Excel::download(
            new LaporanSirkulasiExport(
                collect($data['transaksi']),
                'denda',
                ['tanggal_mulai' => $tanggalMulai, 'tanggal_akhir' => $tanggalAkhir]
            ),
            'laporan-denda-'.date('Y-m-d').'.xlsx'
        );
    }

    /**
     * Export Laporan Buku to PDF.
     */
    public function exportBukuPdf(): Response
    {
        $this->authorizeReport();

        $data = $this->getDataBuku();

        $pdf = Pdf::loadView('pdf.laporan-buku', [
            'bukuTerpopuler' => $data['bukuTerpopuler'],
            'bukuJarangDipinjam' => $data['bukuJarangDipinjam'],
            'bukuPalingDilihat' => $data['bukuPalingDilihat'],
            'summary' => $data['summary'],
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM YYYY'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-buku-'.date('Y-m-d').'.pdf');
    }

    /**
     * Export Laporan Buku to Excel.
     */
    public function exportBukuExcel(): BinaryFileResponse
    {
        $this->authorizeReport();

        $data = $this->getDataBuku();

        return Excel::download(
            new LaporanSirkulasiExport(
                collect($data['bukuTerpopuler']),
                'buku'
            ),
            'laporan-buku-'.date('Y-m-d').'.xlsx'
        );
    }

    /**
     * Get data for peminjaman report.
     *
     * @return array<string, mixed>
     */
    private function getDataPeminjaman(string $tanggalMulai, string $tanggalAkhir): array
    {
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

        $transaksi = $transaksiPelajar->union($transaksiNonPelajar)
            ->orderBy('TglPinjam', 'desc')
            ->get();

        $today = Carbon::now()->startOfDay();
        $transaksi->transform(function ($item) use ($today) {
            $jatuhTempo = Carbon::parse($item->TglJatuhTempo)->startOfDay();
            $item->is_overdue = $today->gt($jatuhTempo);
            $item->hari_terlambat = $item->is_overdue ? $today->diffInDays($jatuhTempo) : 0;

            return $item;
        });

        return [
            'transaksi' => $transaksi,
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_terlambat' => $transaksi->where('is_overdue', true)->count(),
                'total_tepat_waktu' => $transaksi->where('is_overdue', false)->count(),
            ],
        ];
    }

    /**
     * Get data for pengembalian report.
     *
     * @return array<string, mixed>
     */
    private function getDataPengembalian(string $tanggalMulai, string $tanggalAkhir): array
    {
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

        return [
            'transaksi' => $transaksi,
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_denda' => $transaksi->sum('Denda'),
                'total_lunas' => $transaksi->where('StatusBayarDenda', 'Lunas')->sum('Denda'),
            ],
        ];
    }

    /**
     * Get data for denda report.
     *
     * @return array<string, mixed>
     */
    private function getDataDenda(string $tanggalMulai, string $tanggalAkhir): array
    {
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

        $today = Carbon::now()->startOfDay();
        $transaksi->transform(function ($item) use ($today) {
            if ($item->StatusBayarDenda === 'Belum_Lunas' && $item->TglKembali === null) {
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

        return [
            'transaksi' => $transaksi,
            'summary' => [
                'total_transaksi' => $transaksi->count(),
                'total_denda_lunas' => $dendaLunas,
                'total_piutang' => $dendaBelumLunas,
                'total_denda_keseluruhan' => $dendaLunas + $dendaBelumLunas,
            ],
        ];
    }

    /**
     * Get data for buku report.
     *
     * @return array<string, mixed>
     */
    private function getDataBuku(): array
    {
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

        $bukuPalingDilihat = Buku::query()
            ->orderBy('views_count', 'desc')
            ->take(20)
            ->get(['KodeBuku', 'Judul', 'Pengarang', 'SubjekUtama', 'views_count']);

        return [
            'bukuTerpopuler' => $bukuTerpopuler,
            'bukuJarangDipinjam' => $bukuJarangDipinjam,
            'bukuPalingDilihat' => $bukuPalingDilihat,
            'summary' => [
                'total_buku' => Buku::count(),
                'total_peminjaman' => TransaksiPelajar::count() + TransaksiNonPelajar::count(),
            ],
        ];
    }

    /**
     * Authorize report access.
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
