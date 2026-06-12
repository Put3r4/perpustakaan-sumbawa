import { Head } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components globally
Chart.register(...registerables);

// TypeScript interfaces for Props
interface Petugas {
    KodePetugas: string;
    NamaLengkap: string;
    HakAkses: string;
}

interface Stats {
    totalAnggotaAktif: number;
    totalBukuTerpinjam: number;
}

interface KunjunganChart {
    labels: string[];
    datasets: number[];
}

interface DashboardProps {
    petugasPiket: Petugas[];
    stats: Stats;
    kunjunganChart: KunjunganChart;
}

export default function Dashboard({ petugasPiket, stats, kunjunganChart }: DashboardProps) {
    const chartRef = useRef<HTMLCanvasElement>(null);
    const chartInstance = useRef<Chart | null>(null);

    // Initialize Chart.js on component mount
    useEffect(() => {
        if (!chartRef.current) return;

        // Destroy existing chart instance to prevent memory leaks
        if (chartInstance.current) {
            chartInstance.current.destroy();
        }

        const ctx = chartRef.current.getContext('2d');
        if (!ctx) return;

        // Create new Chart.js instance
        chartInstance.current = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: kunjunganChart.labels,
                datasets: [
                    {
                        label: 'Jumlah Kunjungan',
                        data: kunjunganChart.datasets,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: 'Grafik Kunjungan 7 Hari Terakhir',
                        font: {
                            size: 16,
                            weight: 'bold',
                        },
                        color: '#1f2937',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#6b7280',
                        },
                        grid: {
                            color: '#e5e7eb',
                        },
                    },
                    x: {
                        ticks: {
                            color: '#6b7280',
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });

        // Cleanup function - destroy chart on component unmount
        return () => {
            if (chartInstance.current) {
                chartInstance.current.destroy();
            }
        };
    }, [kunjunganChart]);

    return (
        <>
            <Head title="Dashboard - Perpustakaan Kota Sumbawa" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
                {/* Header Navigation */}
                <header className="bg-white shadow-md">
                    <div className="container mx-auto px-4 py-6">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-center md:text-left">
                                <h1 className="text-3xl font-bold text-gray-800">
                                    📚 Perpustakaan Kota Sumbawa
                                </h1>
                                <p className="text-gray-600 mt-1">
                                    Sistem Informasi Perpustakaan Digital
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-3 justify-center">
                                <a
                                    href="/rak-buku"
                                    className="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    📖 Rak Buku
                                </a>
                                <a
                                    href="/register"
                                    className="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    ✍️ Daftar
                                </a>
                                <a
                                    href="/login"
                                    className="px-6 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-800 transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    🔐 Login
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="container mx-auto px-4 py-8">
                    {/* Statistics Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {/* Total Anggota Aktif Card */}
                        <div className="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-blue-500 hover:shadow-2xl transition duration-300">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Total Anggota Aktif
                                    </p>
                                    <h3 className="text-5xl font-bold text-gray-800 mt-3">
                                        {stats.totalAnggotaAktif.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-gray-500 text-xs mt-2">
                                        Pelajar & Non-Pelajar Terdaftar
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">👥</div>
                            </div>
                        </div>

                        {/* Total Buku Dipinjam Card */}
                        <div className="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-green-500 hover:shadow-2xl transition duration-300">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Buku Sedang Dipinjam
                                    </p>
                                    <h3 className="text-5xl font-bold text-gray-800 mt-3">
                                        {stats.totalBukuTerpinjam.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-gray-500 text-xs mt-2">
                                        Dari Seluruh Koleksi Perpustakaan
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">📖</div>
                            </div>
                        </div>
                    </div>

                    {/* Second Row: Petugas Piket & Chart */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        {/* Petugas Piket Card */}
                        <div className="bg-white rounded-2xl shadow-xl p-6">
                            <h2 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span className="text-2xl">👮</span>
                                <span>Petugas Piket Hari Ini</span>
                            </h2>

                            {petugasPiket.length > 0 ? (
                                <div className="space-y-3">
                                    {petugasPiket.map((petugas) => (
                                        <div
                                            key={petugas.KodePetugas}
                                            className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200 hover:shadow-md transition duration-200"
                                        >
                                            <p className="font-semibold text-gray-800 text-lg">
                                                {petugas.NamaLengkap}
                                            </p>
                                            <div className="flex items-center justify-between mt-2">
                                                <span className="text-sm text-gray-600 bg-white px-3 py-1 rounded-full">
                                                    {petugas.HakAkses}
                                                </span>
                                                <span className="text-xs text-gray-500">
                                                    ID: {petugas.KodePetugas}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12 text-gray-500">
                                    <div className="text-5xl mb-3 opacity-50">📅</div>
                                    <p className="text-sm">Tidak ada petugas piket hari ini</p>
                                </div>
                            )}
                        </div>

                        {/* Chart Card */}
                        <div className="bg-white rounded-2xl shadow-xl p-6 lg:col-span-2">
                            <div className="h-80 md:h-96">
                                <canvas ref={chartRef}></canvas>
                            </div>
                        </div>
                    </div>

                    {/* Information Section */}
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">
                            🌟 Selamat Datang di Perpustakaan Kota Sumbawa
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl text-center hover:shadow-lg transition duration-300">
                                <div className="text-5xl mb-4">📚</div>
                                <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                    Koleksi Lengkap
                                </h3>
                                <p className="text-sm text-gray-600">
                                    Ribuan buku dari berbagai kategori siap untuk Anda pinjam
                                </p>
                            </div>
                            <div className="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl text-center hover:shadow-lg transition duration-300">
                                <div className="text-5xl mb-4">⚡</div>
                                <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                    Layanan Cepat
                                </h3>
                                <p className="text-sm text-gray-600">
                                    Peminjaman dan pengembalian buku dengan sistem digital QR Code
                                </p>
                            </div>
                            <div className="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl text-center hover:shadow-lg transition duration-300">
                                <div className="text-5xl mb-4">🎯</div>
                                <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                    Mudah Diakses
                                </h3>
                                <p className="text-sm text-gray-600">
                                    Daftar sebagai anggota dan nikmati fasilitas perpustakaan
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="bg-gray-800 text-white mt-12 py-8">
                    <div className="container mx-auto px-4">
                        <div className="text-center">
                            <p className="text-sm font-semibold">
                                © 2026 Perpustakaan Kota Sumbawa
                            </p>
                            <p className="text-xs text-gray-400 mt-2">
                                Sistem Informasi Perpustakaan Digital
                            </p>
                            <div className="mt-4 flex items-center justify-center gap-2 text-xs text-gray-500">
                                <span>Dibangun dengan</span>
                                <span className="text-red-400">❤️</span>
                                <span>menggunakan Laravel 13 & Inertia.js React</span>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
