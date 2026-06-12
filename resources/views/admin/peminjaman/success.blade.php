<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Berhasil - Perpustakaan Sumbawa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
        .success-animation {
            animation: successPop 0.6s ease-out;
        }
        @keyframes successPop {
            0% { transform: scale(0.5); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 via-white to-green-50 min-h-screen">
    <!-- Navigation -->
    <nav class="no-print bg-white shadow-lg border-b">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-gray-800">Perpustakaan Sumbawa</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.peminjaman.create') }}" class="text-blue-600 hover:text-blue-800 font-medium">Peminjaman Baru</a>
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8 max-w-4xl">
        <!-- Success Icon -->
        <div class="success-animation text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Peminjaman Berhasil!</h2>
            <p class="text-gray-600">{{ count($transaksis) }} buku telah berhasil dipinjamkan</p>
        </div>

        @foreach($transaksis as $index => $transaksi)
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border-2 border-green-100">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 pb-6 border-b-2 border-dashed">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">RESI PEMINJAMAN #{{ $index + 1 }}</h3>
                        <p class="text-sm text-gray-500 mt-1">No. Transaksi: 
                            <span class="font-mono font-semibold text-blue-600">
                                {{ $kategori === 'pelajar' ? $transaksi->NoPinjamP : $transaksi->NoPinjamN }}
                            </span>
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-semibold text-sm">
                            ✓ AKTIF
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Left: Details -->
                    <div class="md:col-span-2 space-y-4">
                        <!-- Anggota Info -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900 mb-3 text-sm uppercase tracking-wide">Data Peminjam</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-semibold text-gray-800">
                                        @if($kategori === 'pelajar')
                                            {{ $transaksi->anggotaPelajar->NamaAnggotaP }}
                                        @else
                                            {{ $transaksi->anggotaNonPelajar->NamaAnggotaN }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">ID Anggota:</span>
                                    <span class="font-mono font-semibold text-gray-800">
                                        @if($kategori === 'pelajar')
                                            {{ $transaksi->NoAnggotaP }}
                                        @else
                                            {{ $transaksi->NoAnggotaN }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kategori:</span>
                                    <span class="font-semibold text-gray-800">{{ $kategori === 'pelajar' ? 'Pelajar' : 'Non-Pelajar' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Buku Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-3 text-sm uppercase tracking-wide">Data Buku</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kode Buku:</span>
                                    <span class="font-mono font-semibold text-gray-800">{{ $transaksi->KodeBuku }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Judul:</span>
                                    <span class="font-semibold text-gray-800 text-right">{{ $transaksi->buku->Judul }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pengarang:</span>
                                    <span class="text-gray-800">{{ $transaksi->buku->Pengarang }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tanggal Info -->
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <h4 class="font-semibold text-yellow-900 mb-3 text-sm uppercase tracking-wide">Informasi Waktu</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tanggal Pinjam:</span>
                                    <span class="font-semibold text-gray-800">{{ $transaksi->TglPinjam->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jatuh Tempo:</span>
                                    <span class="font-bold text-red-600">{{ $transaksi->TglJatuhTempo->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Durasi Pinjam:</span>
                                    <span class="font-semibold text-gray-800">7 Hari</span>
                                </div>
                            </div>
                        </div>

                        <!-- Petugas Info -->
                        <div class="text-xs text-gray-500 mt-4">
                            <p>Diproses oleh: <span class="font-semibold">{{ $transaksi->petugas->NamaPetugas }}</span></p>
                            <p>Waktu Proses: {{ now()->format('d M Y H:i:s') }}</p>
                        </div>
                    </div>

                    <!-- Right: QR Code -->
                    <div class="flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl">
                        <div class="bg-white p-4 rounded-lg shadow-lg">
                            @php
                                $qrData = $kategori === 'pelajar' ? $transaksi->NoPinjamP : $transaksi->NoPinjamN;
                                $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                                    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                                    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
                                );
                                $writer = new \BaconQrCode\Writer($renderer);
                                $qrCode = $writer->writeString($qrData);
                            @endphp
                            {!! $qrCode !!}
                        </div>
                        <p class="text-xs text-center text-gray-600 mt-3 font-medium">
                            Scan QR untuk verifikasi<br>pengembalian buku
                        </p>
                    </div>
                </div>

                <!-- Footer Warning -->
                <div class="mt-6 pt-6 border-t-2 border-dashed">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-red-800">
                                <p class="font-semibold mb-1">Perhatian Penting:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Kembalikan buku sebelum <strong>{{ $transaksi->TglJatuhTempo->format('d M Y') }}</strong></li>
                                    <li>Keterlambatan akan dikenakan denda sesuai peraturan perpustakaan</li>
                                    <li>Simpan resi ini sebagai bukti peminjaman</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Action Buttons -->
        <div class="no-print flex items-center justify-center space-x-4 mt-8">
            <button onclick="window.print()" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Resi
            </button>
            <a href="{{ route('admin.peminjaman.create') }}" class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors shadow-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Peminjaman Baru
            </a>
            <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
