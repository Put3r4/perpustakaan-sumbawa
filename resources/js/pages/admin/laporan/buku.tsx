import { Head, Link } from '@inertiajs/react';

// TypeScript interfaces for Props
interface BukuData {
    KodeBuku: string;
    Judul: string;
    Pengarang: string;
    SubjekUtama: string;
    total_peminjaman?: number;
    views_count: number;
}

interface Summary {
    total_buku: number;
    total_peminjaman: number;
}

interface LaporanBukuProps {
    bukuTerpopuler: BukuData[];
    bukuJarangDipinjam: BukuData[];
    bukuPalingDilihat: BukuData[];
    summary: Summary;
}

export default function LaporanBuku({
    bukuTerpopuler,
    bukuJarangDipinjam,
    bukuPalingDilihat,
    summary,
}: LaporanBukuProps) {
    return (
        <>
            <Head title="Laporan Analitik Buku - Perpustakaan Kota Sumbawa" />

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-purple-50 to-pink-50">
                {/* Header Navigation */}
                <header className="bg-white shadow-md border-b border-gray-200">
                    <div className="container mx-auto px-4 py-6">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-center md:text-left">
                                <h1 className="text-3xl font-bold text-gray-800">
                                    📊 Laporan Analitik & Kondisi Buku
                                </h1>
                                <p className="text-gray-600 mt-1">
                                    Metrik performa inventaris dan popularitas koleksi buku
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-3 justify-center">
                                <Link
                                    href="/admin/laporan"
                                    className="px-6 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-800 transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    ← Kembali
                                </Link>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="container mx-auto px-4 py-8">
                    {/* Summary Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Total Koleksi Buku
                                    </p>
                                    <h3 className="text-5xl font-bold text-gray-800 mt-2">
                                        {summary.total_buku.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Judul Buku Tersedia
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">📚</div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Total Peminjaman
                                    </p>
                                    <h3 className="text-5xl font-bold text-gray-800 mt-2">
                                        {summary.total_peminjaman.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Transaksi Keseluruhan
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">📖</div>
                            </div>
                        </div>
                    </div>

                    {/* Export Buttons */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <h3 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span className="text-2xl">📥</span>
                            <span>Ekspor Dokumen Resmi</span>
                        </h3>
                        <div className="flex flex-wrap gap-4">
                            <a
                                href="/admin/laporan/buku/pdf"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
                            >
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fillRule="evenodd"
                                        d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                <span>Unduh PDF</span>
                            </a>
                            <a
                                href="/admin/laporan/buku/excel"
                                className="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
                            >
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fillRule="evenodd"
                                        d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                <span>Unduh Excel</span>
                            </a>
                        </div>
                    </div>

                    {/* Top 5 Buku Paling Banyak Dipinjam */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span className="text-3xl">🏆</span>
                            <span>Top 5 Buku Paling Banyak Dipinjam</span>
                        </h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Ranking
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Kode Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Judul Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Pengarang
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Subjek Utama
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Frekuensi Dipinjam
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {bukuTerpopuler.slice(0, 5).map((buku, index) => (
                                        <tr
                                            key={buku.KodeBuku}
                                            className={`hover:bg-purple-50 transition-colors ${
                                                index === 0 ? 'bg-yellow-50' : ''
                                            }`}
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {index === 0 && (
                                                    <span className="text-3xl">🥇</span>
                                                )}
                                                {index === 1 && (
                                                    <span className="text-3xl">🥈</span>
                                                )}
                                                {index === 2 && (
                                                    <span className="text-3xl">🥉</span>
                                                )}
                                                {index >= 3 && (
                                                    <span className="text-lg font-bold text-gray-600">
                                                        #{index + 1}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700 font-mono">
                                                {buku.KodeBuku}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-semibold">
                                                {buku.Judul}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700">
                                                {buku.Pengarang}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    {buku.SubjekUtama}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-4 py-2 text-lg font-bold rounded-lg bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800">
                                                    {buku.total_peminjaman || 0}×
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Top 5 Buku Paling Sedikit Dipinjam (Koleksi Pasif) */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span className="text-3xl">📉</span>
                            <span>Top 5 Buku Paling Sedikit Dipinjam (Koleksi Pasif)</span>
                        </h2>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gradient-to-r from-gray-600 to-gray-700 text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            No
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Kode Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Judul Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Pengarang
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Subjek Utama
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Frekuensi Dipinjam
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {bukuJarangDipinjam.slice(0, 5).map((buku, index) => (
                                        <tr key={buku.KodeBuku} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                {index + 1}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700 font-mono">
                                                {buku.KodeBuku}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-semibold">
                                                {buku.Judul}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700">
                                                {buku.Pengarang}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {buku.SubjekUtama}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-4 py-2 text-sm font-bold rounded-lg bg-gray-100 text-gray-700">
                                                    {buku.total_peminjaman || 0}×
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Buku Paling Banyak Dilihat (Views Count) */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span className="text-3xl">👀</span>
                            <span>Buku Paling Banyak Dilihat (Tracking Views)</span>
                        </h2>
                        <p className="text-sm text-gray-600 mb-4 italic">
                            * Berdasarkan tracking durasi 1 menit pada halaman detail buku
                        </p>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Ranking
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Kode Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Judul Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Pengarang
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Subjek Utama
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Jumlah Views
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {bukuPalingDilihat.map((buku, index) => (
                                        <tr
                                            key={buku.KodeBuku}
                                            className={`hover:bg-blue-50 transition-colors ${
                                                index === 0 ? 'bg-blue-50' : ''
                                            }`}
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {index === 0 && (
                                                    <span className="text-3xl">⭐</span>
                                                )}
                                                {index === 1 && (
                                                    <span className="text-3xl">✨</span>
                                                )}
                                                {index === 2 && (
                                                    <span className="text-3xl">💫</span>
                                                )}
                                                {index >= 3 && (
                                                    <span className="text-lg font-bold text-gray-600">
                                                        #{index + 1}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700 font-mono">
                                                {buku.KodeBuku}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900 font-semibold">
                                                {buku.Judul}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700">
                                                {buku.Pengarang}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {buku.SubjekUtama}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-4 py-2 text-lg font-bold rounded-lg bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800">
                                                    {buku.views_count.toLocaleString('id-ID')}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Info Card */}
                    <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-lg p-8 text-white">
                        <h3 className="text-2xl font-bold mb-4 flex items-center gap-3">
                            <span className="text-3xl">ℹ️</span>
                            <span>Informasi Metrik Analitik</span>
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                                <h4 className="font-semibold mb-2 flex items-center gap-2">
                                    <span className="text-xl">📈</span>
                                    <span>Frekuensi Peminjaman</span>
                                </h4>
                                <p className="text-sm text-blue-100">
                                    Dihitung berdasarkan gabungan total transaksi dari tabel
                                    transaksi_pelajar dan transaksi_non_pelajar untuk setiap buku
                                </p>
                            </div>
                            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                                <h4 className="font-semibold mb-2 flex items-center gap-2">
                                    <span className="text-xl">👁️</span>
                                    <span>Tracking Views</span>
                                </h4>
                                <p className="text-sm text-blue-100">
                                    Sistem tracking otomatis mencatat 1 view ketika pengunjung membuka
                                    halaman detail buku dan bertahan minimal 1 menit
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
                                Laporan Analitik Buku - Dinas Perpustakaan dan Kearsipan
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
