import { Head, Link } from '@inertiajs/react';

// TypeScript interfaces for Props
interface Summary {
    total_pelajar: number;
    total_non_pelajar: number;
    total_anggota: number;
}

interface PreferensiData {
    SubjekUtama: string;
    total: number;
}

interface PertumbuhanData {
    bulan: string;
    total: number;
}

interface LaporanAnggotaProps {
    summary: Summary;
    preferensiPelajar: PreferensiData[];
    preferensiNonPelajar: PreferensiData[];
    pertumbuhanPelajar: PertumbuhanData[];
    pertumbuhanNonPelajar: PertumbuhanData[];
}

export default function LaporanAnggota({
    summary,
    preferensiPelajar,
    preferensiNonPelajar,
    pertumbuhanPelajar,
    pertumbuhanNonPelajar,
}: LaporanAnggotaProps) {
    // Calculate percentages for preferensi
    const totalPreferensiPelajar = preferensiPelajar.reduce((sum, item) => sum + item.total, 0);
    const totalPreferensiNonPelajar = preferensiNonPelajar.reduce(
        (sum, item) => sum + item.total,
        0
    );

    const calculatePercentage = (value: number, total: number): number => {
        return total > 0 ? Math.round((value / total) * 100) : 0;
    };

    const formatBulan = (bulanString: string): string => {
        const [year, month] = bulanString.split('-');
        const months = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des',
        ];
        return `${months[parseInt(month) - 1]} ${year}`;
    };

    return (
        <>
            <Head title="Laporan Keanggotaan & Preferensi - Perpustakaan Kota Sumbawa" />

            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-cyan-50">
                {/* Header Navigation */}
                <header className="bg-white shadow-md border-b border-gray-200">
                    <div className="container mx-auto px-4 py-6">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-center md:text-left">
                                <h1 className="text-3xl font-bold text-gray-800">
                                    👥 Laporan Keanggotaan & Preferensi
                                </h1>
                                <p className="text-gray-600 mt-1">
                                    Visualisasi demografi dan tren minat baca anggota
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
                    {/* Summary Statistics Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Anggota Pelajar
                                    </p>
                                    <h3 className="text-5xl font-bold text-blue-600 mt-2">
                                        {summary.total_pelajar.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Pelajar Terdaftar
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">🎓</div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Anggota Non-Pelajar
                                    </p>
                                    <h3 className="text-5xl font-bold text-green-600 mt-2">
                                        {summary.total_non_pelajar.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Umum Terdaftar
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">👤</div>
                            </div>
                        </div>

                        <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-gray-600 text-sm font-medium uppercase tracking-wide">
                                        Total Keseluruhan
                                    </p>
                                    <h3 className="text-5xl font-bold text-purple-600 mt-2">
                                        {summary.total_anggota.toLocaleString('id-ID')}
                                    </h3>
                                    <p className="text-xs text-gray-500 mt-1">
                                        Total Anggota Aktif
                                    </p>
                                </div>
                                <div className="text-7xl opacity-20">👥</div>
                            </div>
                        </div>
                    </div>

                    {/* Grafik Pertumbuhan Anggota 12 Bulan Terakhir */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        {/* Pertumbuhan Pelajar */}
                        <div className="bg-white rounded-2xl shadow-lg p-6">
                            <h2 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                <span className="text-2xl">📈</span>
                                <span>Pertumbuhan Anggota Pelajar (12 Bulan)</span>
                            </h2>
                            <div className="space-y-3">
                                {pertumbuhanPelajar.length > 0 ? (
                                    pertumbuhanPelajar.map((item, index) => {
                                        const maxValue = Math.max(
                                            ...pertumbuhanPelajar.map((d) => d.total)
                                        );
                                        const percentage =
                                            maxValue > 0 ? (item.total / maxValue) * 100 : 0;

                                        return (
                                            <div key={index} className="space-y-1">
                                                <div className="flex justify-between items-center text-sm">
                                                    <span className="font-semibold text-gray-700">
                                                        {formatBulan(item.bulan)}
                                                    </span>
                                                    <span className="font-bold text-blue-600">
                                                        {item.total} anggota
                                                    </span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-3">
                                                    <div
                                                        className="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                                                        style={{ width: `${percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-12 text-gray-500">
                                        <div className="text-5xl mb-3 opacity-30">📊</div>
                                        <p className="text-sm">Belum ada data pertumbuhan</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Pertumbuhan Non-Pelajar */}
                        <div className="bg-white rounded-2xl shadow-lg p-6">
                            <h2 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                <span className="text-2xl">📈</span>
                                <span>Pertumbuhan Anggota Non-Pelajar (12 Bulan)</span>
                            </h2>
                            <div className="space-y-3">
                                {pertumbuhanNonPelajar.length > 0 ? (
                                    pertumbuhanNonPelajar.map((item, index) => {
                                        const maxValue = Math.max(
                                            ...pertumbuhanNonPelajar.map((d) => d.total)
                                        );
                                        const percentage =
                                            maxValue > 0 ? (item.total / maxValue) * 100 : 0;

                                        return (
                                            <div key={index} className="space-y-1">
                                                <div className="flex justify-between items-center text-sm">
                                                    <span className="font-semibold text-gray-700">
                                                        {formatBulan(item.bulan)}
                                                    </span>
                                                    <span className="font-bold text-green-600">
                                                        {item.total} anggota
                                                    </span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-3">
                                                    <div
                                                        className="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500"
                                                        style={{ width: `${percentage}%` }}
                                                    ></div>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-12 text-gray-500">
                                        <div className="text-5xl mb-3 opacity-30">📊</div>
                                        <p className="text-sm">Belum ada data pertumbuhan</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Preferensi Subjek Buku */}
                    <div className="bg-white rounded-2xl shadow-lg p-6 mb-8">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span className="text-3xl">📚</span>
                            <span>Perbandingan Tren Preferensi Subjek Buku</span>
                        </h2>
                        <p className="text-sm text-gray-600 mb-6">
                            Top 10 Kategori subjek buku yang paling sering dipinjam berdasarkan
                            kelompok anggota
                        </p>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {/* Preferensi Pelajar */}
                            <div>
                                <h3 className="text-lg font-bold text-blue-600 mb-4 flex items-center gap-2">
                                    <span className="text-xl">🎓</span>
                                    <span>Preferensi Anggota Pelajar</span>
                                </h3>
                                <div className="space-y-3">
                                    {preferensiPelajar.length > 0 ? (
                                        preferensiPelajar.map((item, index) => {
                                            const percentage = calculatePercentage(
                                                item.total,
                                                totalPreferensiPelajar
                                            );

                                            return (
                                                <div
                                                    key={index}
                                                    className="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:shadow-md transition-shadow"
                                                >
                                                    <div className="flex justify-between items-center mb-2">
                                                        <span className="font-semibold text-gray-800">
                                                            {item.SubjekUtama}
                                                        </span>
                                                        <div className="flex items-center gap-3">
                                                            <span className="text-sm font-bold text-blue-600">
                                                                {percentage}%
                                                            </span>
                                                            <span className="px-3 py-1 text-sm font-bold rounded-full bg-blue-600 text-white">
                                                                {item.total}×
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="w-full bg-blue-200 rounded-full h-2">
                                                        <div
                                                            className="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500"
                                                            style={{ width: `${percentage}%` }}
                                                        ></div>
                                                    </div>
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <div className="text-center py-12 text-gray-500">
                                            <div className="text-5xl mb-3 opacity-30">📖</div>
                                            <p className="text-sm">Belum ada data preferensi</p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Preferensi Non-Pelajar */}
                            <div>
                                <h3 className="text-lg font-bold text-green-600 mb-4 flex items-center gap-2">
                                    <span className="text-xl">👤</span>
                                    <span>Preferensi Anggota Non-Pelajar</span>
                                </h3>
                                <div className="space-y-3">
                                    {preferensiNonPelajar.length > 0 ? (
                                        preferensiNonPelajar.map((item, index) => {
                                            const percentage = calculatePercentage(
                                                item.total,
                                                totalPreferensiNonPelajar
                                            );

                                            return (
                                                <div
                                                    key={index}
                                                    className="p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-xl hover:shadow-md transition-shadow"
                                                >
                                                    <div className="flex justify-between items-center mb-2">
                                                        <span className="font-semibold text-gray-800">
                                                            {item.SubjekUtama}
                                                        </span>
                                                        <div className="flex items-center gap-3">
                                                            <span className="text-sm font-bold text-green-600">
                                                                {percentage}%
                                                            </span>
                                                            <span className="px-3 py-1 text-sm font-bold rounded-full bg-green-600 text-white">
                                                                {item.total}×
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="w-full bg-green-200 rounded-full h-2">
                                                        <div
                                                            className="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500"
                                                            style={{ width: `${percentage}%` }}
                                                        ></div>
                                                    </div>
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <div className="text-center py-12 text-gray-500">
                                            <div className="text-5xl mb-3 opacity-30">📖</div>
                                            <p className="text-sm">Belum ada data preferensi</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Insight Card */}
                    <div className="bg-gradient-to-r from-teal-600 to-cyan-600 rounded-2xl shadow-lg p-8 text-white">
                        <h3 className="text-2xl font-bold mb-4 flex items-center gap-3">
                            <span className="text-3xl">💡</span>
                            <span>Insight Strategis</span>
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                                <h4 className="font-semibold mb-2 flex items-center gap-2">
                                    <span className="text-xl">👥</span>
                                    <span>Segmentasi Anggota</span>
                                </h4>
                                <p className="text-sm text-teal-100">
                                    Data menunjukkan perbandingan komposisi anggota antara pelajar dan
                                    masyarakat umum, membantu strategi pengembangan koleksi
                                </p>
                            </div>
                            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                                <h4 className="font-semibold mb-2 flex items-center gap-2">
                                    <span className="text-xl">📊</span>
                                    <span>Analisis Tren</span>
                                </h4>
                                <p className="text-sm text-teal-100">
                                    Preferensi subjek buku yang berbeda antara kelompok dapat menjadi
                                    acuan untuk pengadaan koleksi buku yang lebih tepat sasaran
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
                                Laporan Keanggotaan & Preferensi - Dinas Perpustakaan dan Kearsipan
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
