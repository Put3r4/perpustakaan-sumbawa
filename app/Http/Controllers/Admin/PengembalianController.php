<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    /**
     * Display QR code scanner page for pengembalian.
     */
    public function index(): View
    {
        return view('admin.pengembalian.index');
    }

    /**
     * Check UUID resi from QR scan and return transaction details via AJAX.
     */
    public function cekResi(Request $request): JsonResponse
    {
        $request->validate([
            'uuid_resi' => ['required', 'string', 'size:36'],
        ]);

        $uuidResi = $request->input('uuid_resi');

        // Try to find transaction in TransaksiPelajar first
        $transaksi = TransaksiPelajar::with(['anggotaPelajar', 'buku', 'petugas'])
            ->where('NoPinjamP', $uuidResi)
            ->where('StatusTransaksi', 'Dipinjam')
            ->first();

        if ($transaksi) {
            return response()->json([
                'success' => true,
                'kategori' => 'pelajar',
                'data' => [
                    'uuid_resi' => $transaksi->NoPinjamP,
                    'nama_anggota' => $transaksi->anggotaPelajar->NamaLengkap,
                    'no_identitas' => $transaksi->anggotaPelajar->Nis,
                    'judul_buku' => $transaksi->buku->Judul,
                    'kode_buku' => $transaksi->buku->KodeBuku,
                    'penerbit' => $transaksi->buku->Penerbit,
                    'tgl_pinjam' => $transaksi->TglPinjam->format('d-m-Y'),
                    'tgl_jatuh_tempo' => $transaksi->TglJatuhTempo->format('d-m-Y'),
                    'hari_terlambat' => $this->calculateLateDays($transaksi->TglJatuhTempo),
                    'nominal_denda' => $this->calculateFine($transaksi->TglJatuhTempo),
                ],
            ]);
        }

        // Try TransaksiNonPelajar
        $transaksi = TransaksiNonPelajar::with(['anggotaNonPelajar', 'buku', 'petugas'])
            ->where('NoPinjamN', $uuidResi)
            ->where('StatusTransaksi', 'Dipinjam')
            ->first();

        if ($transaksi) {
            return response()->json([
                'success' => true,
                'kategori' => 'non_pelajar',
                'data' => [
                    'uuid_resi' => $transaksi->NoPinjamN,
                    'nama_anggota' => $transaksi->anggotaNonPelajar->NamaLengkap,
                    'no_identitas' => $transaksi->anggotaNonPelajar->Nik,
                    'judul_buku' => $transaksi->buku->Judul,
                    'kode_buku' => $transaksi->buku->KodeBuku,
                    'penerbit' => $transaksi->buku->Penerbit,
                    'tgl_pinjam' => $transaksi->TglPinjam->format('d-m-Y'),
                    'tgl_jatuh_tempo' => $transaksi->TglJatuhTempo->format('d-m-Y'),
                    'hari_terlambat' => $this->calculateLateDays($transaksi->TglJatuhTempo),
                    'nominal_denda' => $this->calculateFine($transaksi->TglJatuhTempo),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Resi UUID tidak ditemukan atau buku sudah dikembalikan.',
        ], 404);
    }

    /**
     * Store pengembalian transaction with denda calculation.
     */
    public function storePengembalian(Request $request): RedirectResponse
    {
        $request->validate([
            'uuid_resi' => ['required', 'string', 'size:36'],
            'kategori' => ['required', 'in:pelajar,non_pelajar'],
            'status_bayar_denda' => ['required', 'in:Lunas,Belum_Lunas'],
        ]);

        $uuidResi = $request->input('uuid_resi');
        $kategori = $request->input('kategori');
        $statusBayarDenda = $request->input('status_bayar_denda');

        $petugas = auth()->user();

        try {
            DB::beginTransaction();

            if ($kategori === 'pelajar') {
                $transaksi = TransaksiPelajar::where('NoPinjamP', $uuidResi)
                    ->where('StatusTransaksi', 'Dipinjam')
                    ->lockForUpdate()
                    ->first();

                if (! $transaksi) {
                    return back()->withErrors(['error' => 'Transaksi tidak ditemukan atau sudah dikembalikan.']);
                }

                // Calculate fine
                $tglKembali = Carbon::today();
                $tglJatuhTempo = $transaksi->TglJatuhTempo;
                $denda = $this->calculateFine($tglJatuhTempo);
                $statusTransaksi = $denda > 0 ? 'Terlambat' : 'Dikembalikan';

                // Update transaction
                $transaksi->update([
                    'TglKembali' => $tglKembali,
                    'Denda' => $denda,
                    'StatusBayarDenda' => $denda > 0 ? $statusBayarDenda : 'Lunas',
                    'StatusTransaksi' => $statusTransaksi,
                    'KodePetugasKembali' => $petugas->KodePetugas,
                ]);

                // Increment book stock
                Buku::where('KodeBuku', $transaksi->KodeBuku)->increment('JumEksemplar');
            } else {
                $transaksi = TransaksiNonPelajar::where('NoPinjamN', $uuidResi)
                    ->where('StatusTransaksi', 'Dipinjam')
                    ->lockForUpdate()
                    ->first();

                if (! $transaksi) {
                    return back()->withErrors(['error' => 'Transaksi tidak ditemukan atau sudah dikembalikan.']);
                }

                // Calculate fine
                $tglKembali = Carbon::today();
                $tglJatuhTempo = $transaksi->TglJatuhTempo;
                $denda = $this->calculateFine($tglJatuhTempo);
                $statusTransaksi = $denda > 0 ? 'Terlambat' : 'Dikembalikan';

                // Update transaction
                $transaksi->update([
                    'TglKembali' => $tglKembali,
                    'Denda' => $denda,
                    'StatusBayarDenda' => $denda > 0 ? $statusBayarDenda : 'Lunas',
                    'StatusTransaksi' => $statusTransaksi,
                    'KodePetugasKembali' => $petugas->KodePetugas,
                ]);

                // Increment book stock
                Buku::where('KodeBuku', $transaksi->KodeBuku)->increment('JumEksemplar');
            }

            DB::commit();

            return redirect()
                ->route('admin.pengembalian.index')
                ->with('success', 'Pengembalian berhasil! Denda: Rp '.number_format($denda, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat memproses pengembalian: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate late days based on jatuh tempo date.
     */
    private function calculateLateDays(CarbonInterface $tglJatuhTempo): int
    {
        $today = Carbon::today();

        if ($today->gt($tglJatuhTempo)) {
            return (int) abs($today->diffInDays($tglJatuhTempo));
        }

        return 0;
    }

    /**
     * Calculate fine amount (Rp500 per day).
     */
    private function calculateFine(CarbonInterface $tglJatuhTempo): float
    {
        $lateDays = $this->calculateLateDays($tglJatuhTempo);

        return (float) ($lateDays * 500);
    }
}
