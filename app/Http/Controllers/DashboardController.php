<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AnggotaNonPelajar;
use App\Models\AnggotaPelajar;
use App\Models\JadwalPiket;
use App\Models\Kunjungan;
use App\Models\TransaksiNonPelajar;
use App\Models\TransaksiPelajar;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with library statistics.
     * Public access - shows library information for all visitors.
     */
    public function index(): Response
    {
        // 1. Get today's piket officer based on automatic day detection (Carbon)
        $daysMapping = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $currentDayIndex = Carbon::now()->dayOfWeekIso;
        $hariIni = $daysMapping[$currentDayIndex] ?? 'Senin';

        // Load today's piket officer profiles using with('petugas') relationship
        // Safe null handling with filter() to prevent null exceptions
        $petugasPiketData = JadwalPiket::with('petugas')
            ->where('HariPiket', $hariIni)
            ->get()
            ->pluck('petugas')
            ->filter()
            ->map(fn ($petugas) => [
                'KodePetugas' => $petugas->KodePetugas ?? '',
                'NamaLengkap' => $petugas->NamaLengkap ?? 'N/A',
                'HakAkses' => $petugas->HakAkses ?? 'Petugas',
            ])
            ->values()
            ->all();

        // 2. Calculate active members (Pelajar + Non-Pelajar)
        // Optimization: use count() instead of loading full collections to avoid slow query overhead
        $totalAnggotaPelajar = AnggotaPelajar::count();
        $totalAnggotaNonPelajar = AnggotaNonPelajar::count();
        $totalAnggotaAktif = $totalAnggotaPelajar + $totalAnggotaNonPelajar;

        // Calculate borrowed books from both tables where StatusTransaksi is 'Dipinjam'
        $bukuTerpinjamPelajar = TransaksiPelajar::where('StatusTransaksi', 'Dipinjam')->count();
        $bukuTerpinjamNonPelajar = TransaksiNonPelajar::where('StatusTransaksi', 'Dipinjam')->count();
        $totalBukuTerpinjam = $bukuTerpinjamPelajar + $bukuTerpinjamNonPelajar;

        // 3. Prepare weekly visits dataset (last 7 days) for Chart.js
        // Initialize default dataset array for the past 7 days (including today) to handle empty days safely
        $weeklyVisits = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyVisits->put($date->format('Y-m-d'), [
                'label' => $date->format('d M'), // e.g. "11 Jun"
                'dayName' => $daysMapping[$date->dayOfWeekIso] ?? 'Senin',
                'total' => 0,
            ]);
        }

        // Fetch visits within the last 7 days (from 6 days ago start of day until now)
        // Safe query with null check
        try {
            $kunjunganData = Kunjungan::where('WaktuMasuk', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->get()
                ->groupBy(fn ($kunjungan) => Carbon::parse($kunjungan->WaktuMasuk)->format('Y-m-d'));

            // Populate visit counts in our pre-allocated dates collection
            foreach ($kunjunganData as $dateKey => $visits) {
                if ($weeklyVisits->has($dateKey)) {
                    $dayData = $weeklyVisits->get($dateKey);
                    $dayData['total'] = $visits->count();
                    $weeklyVisits->put($dateKey, $dayData);
                }
            }
        } catch (\Exception $e) {
            // If Kunjungan table doesn't exist or error occurs, keep zeros
            // This prevents the entire dashboard from crashing
        }

        return Inertia::render('dashboard', [
            'petugasPiket' => $petugasPiketData,
            'stats' => [
                'totalAnggotaAktif' => $totalAnggotaAktif,
                'totalBukuTerpinjam' => $totalBukuTerpinjam,
            ],
            'kunjunganChart' => [
                'labels' => $weeklyVisits->pluck('dayName')->all(),
                'datasets' => $weeklyVisits->pluck('total')->all(),
            ],
        ]);
    }
}
