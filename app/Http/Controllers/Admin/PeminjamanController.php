<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PeminjamanStoreRequest;
use App\Models\Buku;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * Display form for creating new peminjaman transaction.
     * Shows AJAX autocomplete search for anggota and buku.
     */
    public function create(): View
    {
        return view('admin.peminjaman.create');
    }

    /**
     * Store new peminjaman transaction with strict business rules.
     *
     * Business Rules:
     * 1. Maksimal 2 buku aktif per anggota
     * 2. Blokir jika ada status 'Terlambat' atau 'Belum_Lunas'
     * 3. Auto-set TglPinjam = today, TglJatuhTempo = today + 7 days
     * 4. Auto-decrement JumEksemplar on success
     */
    public function store(PeminjamanStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $kategori = $validated['kategori_anggota'];
        $idAnggota = $validated['id_anggota'];
        $bukuPilihan = $validated['buku_pilihan'];

        /** @var Petugas $petugas */
        $petugas = auth()->user();

        $sisaKuota = $this->checkKuotaPinjam($kategori, $idAnggota);

        if ($sisaKuota < count($bukuPilihan)) {
            return back()->withErrors([
                'buku_pilihan' => "Anggota sudah memiliki {$sisaKuota} slot tersisa. Tidak dapat meminjam ".count($bukuPilihan).' buku sekaligus. Maksimal peminjaman aktif adalah 2 buku.',
            ])->withInput();
        }

        if ($this->isAnggotaBlocked($kategori, $idAnggota)) {
            return back()->withErrors([
                'id_anggota' => 'Anggota diblokir! Terdapat riwayat peminjaman dengan status "Terlambat" atau denda "Belum_Lunas". Selesaikan dulu transaksi sebelumnya.',
            ])->withInput();
        }

        $tglPinjam = Carbon::today();
        $tglJatuhTempo = Carbon::today()->addDays(7);

        try {
            $transaksiIds = DB::transaction(function () use ($kategori, $idAnggota, $bukuPilihan, $petugas, $tglPinjam, $tglJatuhTempo): array {
                $ids = [];

                foreach ($bukuPilihan as $kodeBuku) {
                    $buku = Buku::where('KodeBuku', $kodeBuku)->lockForUpdate()->first();

                    if (! $buku || $buku->JumEksemplar <= 0) {
                        throw new RuntimeException("Buku dengan kode {$kodeBuku} tidak tersedia atau stok habis.");
                    }

                    if ($kategori === 'pelajar') {
                        $transaksi = TransaksiPelajar::create([
                            'TglPinjam' => $tglPinjam,
                            'TglJatuhTempo' => $tglJatuhTempo,
                            'NoAnggotaP' => $idAnggota,
                            'KodeBuku' => $kodeBuku,
                            'KodePetugas' => $petugas->KodePetugas,
                            'Denda' => 0.00,
                            'StatusBayarDenda' => 'Lunas',
                            'StatusTransaksi' => 'Dipinjam',
                        ]);

                        $ids[] = $transaksi->NoPinjamP;
                    } else {
                        $transaksi = TransaksiNonPelajar::create([
                            'TglPinjam' => $tglPinjam,
                            'TglJatuhTempo' => $tglJatuhTempo,
                            'NoAnggotaN' => $idAnggota,
                            'KodeBuku' => $kodeBuku,
                            'KodePetugas' => $petugas->KodePetugas,
                            'Denda' => 0.00,
                            'StatusBayarDenda' => 'Lunas',
                            'StatusTransaksi' => 'Dipinjam',
                        ]);

                        $ids[] = $transaksi->NoPinjamN;
                    }

                    $buku->decrement('JumEksemplar');
                }

                return $ids;
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'buku_pilihan' => $e->getMessage(),
            ])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat memproses peminjaman: '.$e->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('admin.peminjaman.success', [
                'kategori' => $kategori,
                'transaksi_ids' => implode(',', $transaksiIds),
            ])
            ->with('success', 'Peminjaman berhasil! '.count($bukuPilihan).' buku telah dipinjamkan.');
    }

    /**
     * Check kuota pinjam anggota (maksimal 2 buku aktif).
     * Returns: sisa slot yang tersedia.
     */
    private function checkKuotaPinjam(string $kategori, string $idAnggota): int
    {
        $maxKuota = 2;

        if ($kategori === 'pelajar') {
            $aktivePinjam = TransaksiPelajar::where('NoAnggotaP', $idAnggota)
                ->where('StatusTransaksi', 'Dipinjam')
                ->count();
        } else {
            $aktivePinjam = TransaksiNonPelajar::where('NoAnggotaN', $idAnggota)
                ->where('StatusTransaksi', 'Dipinjam')
                ->count();
        }

        return $maxKuota - $aktivePinjam;
    }

    /**
     * Check if anggota is blocked due to 'Terlambat' or 'Belum_Lunas' status.
     * Returns: true if blocked, false if clean.
     */
    private function isAnggotaBlocked(string $kategori, string $idAnggota): bool
    {
        if ($kategori === 'pelajar') {
            $blocked = TransaksiPelajar::where('NoAnggotaP', $idAnggota)
                ->where(function ($query) {
                    $query->where('StatusTransaksi', 'Terlambat')
                        ->orWhere('StatusBayarDenda', 'Belum_Lunas');
                })
                ->exists();
        } else {
            $blocked = TransaksiNonPelajar::where('NoAnggotaN', $idAnggota)
                ->where(function ($query) {
                    $query->where('StatusTransaksi', 'Terlambat')
                        ->orWhere('StatusBayarDenda', 'Belum_Lunas');
                })
                ->exists();
        }

        return $blocked;
    }

    /**
     * Display success page with QR code resi.
     */
    public function success(string $kategori, string $transaksiIds): View
    {
        $ids = explode(',', $transaksiIds);

        if ($kategori === 'pelajar') {
            $transaksis = TransaksiPelajar::with(['anggotaPelajar', 'buku', 'petugas'])
                ->whereIn('NoPinjamP', $ids)
                ->get();
        } else {
            $transaksis = TransaksiNonPelajar::with(['anggotaNonPelajar', 'buku', 'petugas'])
                ->whereIn('NoPinjamN', $ids)
                ->get();
        }

        return view('admin.peminjaman.success', [
            'kategori' => $kategori,
            'transaksis' => $transaksis,
        ]);
    }
}
