import { Head, Link } from '@inertiajs/react';

// TypeScript interfaces for Props
interface ReportType {
    id: string;
    title: string;
    description: string;
    icon: string;
}

interface IndexLaporanProps {
    reportTypes: ReportType[];
}

export default function IndexLaporan({ reportTypes }: IndexLaporanProps) {
    return (
        <>
            <Head title="Dashboard Laporan - Perpustakaan Kota Sumbawa" />

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
                {/* Header Navigation */}
                <header className="bg-white shadow-md border-b border-gray-200">
                    <div className="container mx-auto px-4 py-6">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-center md:text-left">
                                <h1 className="text-3xl font-bold text-gray-800">
                                    📊 Dashboard Laporan Eksekutif
                                </h1>
                                <p className="text-gray-600 mt-1">
                                    Sistem Pelaporan Perpustakaan Kota Sumbawa
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-3 justify-center">
                                <Link
                                    href="/"
                                    className="px-6 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-800 transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    🏠 Dashboard Utama
                                </Link>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="container mx-auto px-4 py-8">
                    {/* Welcome Banner */}
                    <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-2xl p-8 mb-8 text-white">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-6">
                            <div className="flex-1">
                                <h2 className="text-3xl font-bold mb-3">
                                    Selamat Datang di Sistem Laporan
                                </h2>
                                <p className="text-blue-100 text-lg">
                                    Pilih jenis laporan yang ingin Anda generate untuk analisis dan dokumentasi resmi
                                </p>
                            </div>
                            <div className="text-8xl opacity-30">📈</div>
                        </div>
                    </div>

                    {/* Report Type Cards Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        {reportTypes.map((report) => (
                            <Link
                                key={report.id}
                                href={`/admin/laporan/${report.id}`}
                                className="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-2 border-transparent hover:border-blue-500 overflow-hidden"
                            >
                                <div className="p-8">
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="text-6xl group-hover:scale-110 transition-transform duration-300">
                                            {report.icon}
                                        </div>
                                        <div className="text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <svg
                                                className="w-8 h-8"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 className="text-xl font-bold text-gray-800 mb-3 group-hover:text-blue-600 transition-colors">
                                        {report.title}
                                    </h3>
                                    <p className="text-gray-600 text-sm leading-relaxed">
                                        {report.description}
                                    </p>
                                </div>
                                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-4 border-t border-gray-100">
                                    <span className="text-blue-600 font-semibold text-sm group-hover:underline">
                                        Buka Laporan →
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>

                    {/* Information Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                            <div className="flex items-start gap-4">
                                <div className="text-4xl">📄</div>
                                <div className="flex-1">
                                    <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                        Format PDF Resmi
                                    </h3>
                                    <p className="text-sm text-gray-600">
                                        Semua laporan dilengkapi dengan KOP Surat Resmi Pemerintah Kabupaten Sumbawa dan tanda tangan digital
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                            <div className="flex items-start gap-4">
                                <div className="text-4xl">📊</div>
                                <div className="flex-1">
                                    <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                        Export Excel
                                    </h3>
                                    <p className="text-sm text-gray-600">
                                        Unduh data dalam format spreadsheet untuk analisis lebih lanjut dan dokumentasi digital
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
                            <div className="flex items-start gap-4">
                                <div className="text-4xl">🔒</div>
                                <div className="flex-1">
                                    <h3 className="font-bold text-gray-800 mb-2 text-lg">
                                        Akses Terbatas
                                    </h3>
                                    <p className="text-sm text-gray-600">
                                        Hanya petugas dan admin yang memiliki akses untuk melihat dan mengunduh laporan eksekutif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Quick Stats Overview */}
                    <div className="mt-8 bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span className="text-3xl">ℹ️</span>
                            <span>Informasi Sistem Laporan</span>
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                                <h4 className="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                    <span className="text-2xl">📅</span>
                                    <span>Filter Rentang Tanggal</span>
                                </h4>
                                <p className="text-sm text-gray-700">
                                    Setiap laporan dapat difilter berdasarkan rentang tanggal untuk melihat data periode tertentu dengan akurasi 100%
                                </p>
                            </div>
                            <div className="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                                <h4 className="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                    <span className="text-2xl">💰</span>
                                    <span>Kalkulasi Real-time</span>
                                </h4>
                                <p className="text-sm text-gray-700">
                                    Denda keterlambatan dihitung secara real-time berdasarkan hari terlambat × Rp500 untuk transaksi yang belum lunas
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
                                Sistem Laporan Eksekutif - Dinas Perpustakaan dan Kearsipan
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
