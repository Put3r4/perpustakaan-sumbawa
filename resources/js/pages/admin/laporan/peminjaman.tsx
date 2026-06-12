import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

// TypeScript interfaces for Props
interface Anggota {
    NamaLengkap: string;
}

interface Buku {
    KodeBuku: string;
    Judul: string;
    Pengarang: string;
}

interface Transaksi {
    resi: string;
    TglPinjam: string;
    TglJatuhTempo: string;
    TglKembali: string | null;
    nomor_anggota: string;
    KodeBuku: string;
    StatusTransaksi: string;
    Denda: number;
    StatusBayarDenda: string;
    kategori_anggota: string;
    is_overdue: boolean;
    hari_terlambat: number;
    anggotaPelajar?: Anggota;
    anggotaNonPelajar?: Anggota;
    buku: Buku;
}

interface Filter {
    tanggal_mulai: string;
    tanggal_akhir: string;
}

interface Summary {
    total_transaksi: number;
    total_terlambat: number;
    total_tepat_waktu: number;
}

interface LaporanPeminjamanProps {
    transaksi: Transaksi[];
    filter: Filter;
    summary: Summary;
}

export default function LaporanPeminjaman({ transaksi, filter, summary }: LaporanPeminjamanProps) {
    const { data, setData, get, processing } = useForm({
        tanggal_mulai: filter.tanggal_mulai,
        tanggal_akhir: filter.tanggal_akhir,
    });

    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;

    const handleFilter: FormEventHandler = (e) => {
        e.preventDefault();
        get('/admin/laporan/peminjaman', {
            preserveState: true,
        });
    };

    // Pagination logic
    const indexOfLastItem = currentPage * itemsPerPage;
    const indexOfFirstItem = indexOfLastItem - itemsPerPage;
    const currentItems = transaksi.slice(indexOfFirstItem, indexOfLastItem);
    const totalPages = Math.ceil(transaksi.length / itemsPerPage);

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        });
    };

    return (
        <>
            <Head title="Laporan Peminjaman - Perpustakaan Kota Sumbawa" />

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
                {/* Header Navigation */}
                <header className="bg-white shadow-md border-b border-gray-200">
                    <div className="container mx-auto px-4 py-6">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-center md:text-left">
                                <h1 className="text-3xl font-bold text-gray-800">
                                    📤 Laporan Peminjaman Buku
                                </h1>
                                <p className="text-gray-600 mt-1">
                                    Data transaksi buku yang sedang dipinjam atau terlambat
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
                    {/* Filter Card */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <h2 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span className="text-2xl">🔍</span>
                            <span>Filter Rentang Tanggal</span>
                        </h2>
                        <form onSubmit={handleFilter} className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Mulai
                                </label>
                                <input
                                    type="date"
                                    value={data.tanggal_mulai}
                                    onChange={(e) => setData('tanggal_mulai', e.target.value)}
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Akhir
                                </label>
                                <input
                                    type="date"
                                    value={data.tanggal_akhir}
                                    onChange={(e) => setData('tanggal_akhir', e.target.value)}
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                />
                            </div>
                            <div className="flex items-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {processing ? 'Memuat...' : '🔎 Terapkan Filter'}
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Summary Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Total Transaksi
                                    </p>
                                    <h3 className="text-4xl font-bold text-gray-800 mt-2">
                                        {summary.total_transaksi}
                                    </h3>
                                </div>
                                <div className="text-5xl opacity-20">📚</div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-red-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Terlambat
                                    </p>
                                    <h3 className="text-4xl font-bold text-red-600 mt-2">
                                        {summary.total_terlambat}
                                    </h3>
                                </div>
                                <div className="text-5xl opacity-20">⚠️</div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Tepat Waktu
                                    </p>
                                    <h3 className="text-4xl font-bold text-green-600 mt-2">
                                        {summary.total_tepat_waktu}
                                    </h3>
                                </div>
                                <div className="text-5xl opacity-20">✅</div>
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
                                href={`/admin/laporan/peminjaman/pdf?tanggal_mulai=${data.tanggal_mulai}&tanggal_akhir=${data.tanggal_akhir}`}
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
                                href={`/admin/laporan/peminjaman/excel?tanggal_mulai=${data.tanggal_mulai}&tanggal_akhir=${data.tanggal_akhir}`}
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

                    {/* Data Table */}
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            No
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            No Resi
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Nama Anggota
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Kategori
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Judul Buku
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Tgl Pinjam
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Tgl Jatuh Tempo
                                        </th>
                                        <th className="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {currentItems.length > 0 ? (
                                        currentItems.map((item, index) => {
                                            const namaAnggota =
                                                item.anggotaPelajar?.NamaLengkap ||
                                                item.anggotaNonPelajar?.NamaLengkap ||
                                                '-';

                                            return (
                                                <tr
                                                    key={item.resi}
                                                    className={`hover:bg-blue-50 transition-colors ${
                                                        item.is_overdue ? 'bg-red-50' : ''
                                                    }`}
                                                >
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                        {indexOfFirstItem + index + 1}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-700 font-mono">
                                                        {item.resi}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900 font-semibold">
                                                        {namaAnggota}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            {item.kategori_anggota}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-800">
                                                        <div className="max-w-xs">
                                                            <p className="font-semibold truncate">
                                                                {item.buku.Judul}
                                                            </p>
                                                            <p className="text-xs text-gray-500">
                                                                {item.buku.Pengarang}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        {formatDate(item.TglPinjam)}
                                                    </td>
                                                    <td
                                                        className={`px-6 py-4 whitespace-nowrap text-sm ${
                                                            item.is_overdue
                                                                ? 'text-red-700 font-bold'
                                                                : 'text-gray-700'
                                                        }`}
                                                    >
                                                        {formatDate(item.TglJatuhTempo)}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        {item.is_overdue ? (
                                                            <span className="px-3 py-1 text-xs font-bold rounded-full bg-red-600 text-white animate-pulse">
                                                                ⚠️ TERLAMBAT ({item.hari_terlambat} hari)
                                                            </span>
                                                        ) : (
                                                            <span className="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                                ✓ Dipinjam
                                                            </span>
                                                        )}
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    ) : (
                                        <tr>
                                            <td colSpan={8} className="px-6 py-12 text-center">
                                                <div className="text-gray-500">
                                                    <div className="text-6xl mb-3 opacity-30">📭</div>
                                                    <p className="text-lg font-semibold">
                                                        Tidak ada data peminjaman
                                                    </p>
                                                    <p className="text-sm mt-1">
                                                        Belum ada transaksi pada rentang tanggal ini
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="bg-gray-50 px-6 py-4 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Menampilkan{' '}
                                        <span className="font-semibold">{indexOfFirstItem + 1}</span> -{' '}
                                        <span className="font-semibold">
                                            {Math.min(indexOfLastItem, transaksi.length)}
                                        </span>{' '}
                                        dari <span className="font-semibold">{transaksi.length}</span>{' '}
                                        data
                                    </div>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                                            disabled={currentPage === 1}
                                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                        >
                                            ← Sebelumnya
                                        </button>
                                        <div className="flex gap-1">
                                            {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                                                <button
                                                    key={page}
                                                    onClick={() => setCurrentPage(page)}
                                                    className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                                        currentPage === page
                                                            ? 'bg-blue-600 text-white'
                                                            : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                                                    }`}
                                                >
                                                    {page}
                                                </button>
                                            ))}
                                        </div>
                                        <button
                                            onClick={() =>
                                                setCurrentPage((prev) => Math.min(prev + 1, totalPages))
                                            }
                                            disabled={currentPage === totalPages}
                                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                        >
                                            Selanjutnya →
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}
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
                                Laporan Peminjaman - Dinas Perpustakaan dan Kearsipan
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
