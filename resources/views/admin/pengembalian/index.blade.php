<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengembalian Buku - Perpustakaan Kota Sumbawa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Sistem Pengembalian Buku</h1>
                    <p class="text-gray-600 mt-1">Perpustakaan Kota Sumbawa</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Petugas:</p>
                    <p class="font-semibold text-gray-800">{{ auth()->user()->NamaLengkap }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
                <p class="font-semibold">✓ {{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
                @foreach($errors->all() as $error)
                    <p class="font-semibold">✗ {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- QR Scanner Section -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📷 Pindai QR Code Resi</h2>
                
                <div id="reader" class="w-full border-4 border-dashed border-gray-300 rounded-lg overflow-hidden mb-4"></div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atau Masukkan UUID Manual:</label>
                    <input type="text" id="manual-uuid" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" maxlength="36">
                </div>
                
                <button onclick="checkManualUUID()" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    🔍 Cek Resi Manual
                </button>
            </div>

            <!-- Form Review Section -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Detail Pengembalian</h2>
                
                <div id="detail-container" class="hidden">
                    <form id="form-pengembalian" method="POST" action="{{ route('admin.pengembalian.store') }}">
                        @csrf
                        <input type="hidden" id="uuid_resi" name="uuid_resi">
                        <input type="hidden" id="kategori" name="kategori">

                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Nama Anggota</p>
                                <p id="nama_anggota" class="text-lg font-semibold text-gray-800"></p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">No. Identitas</p>
                                <p id="no_identitas" class="font-semibold text-gray-800"></p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Judul Buku</p>
                                <p id="judul_buku" class="text-lg font-semibold text-gray-800"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Tgl. Pinjam</p>
                                    <p id="tgl_pinjam" class="font-semibold text-gray-800"></p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600">Jatuh Tempo</p>
                                    <p id="tgl_jatuh_tempo" class="font-semibold text-gray-800"></p>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border-2 border-yellow-300 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Hari Terlambat</p>
                                <p id="hari_terlambat" class="text-2xl font-bold text-red-600"></p>
                            </div>

                            <div class="bg-red-50 border-2 border-red-300 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Total Denda</p>
                                <p id="nominal_denda" class="text-3xl font-bold text-red-600"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran Denda:</label>
                                <select name="status_bayar_denda" required 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="Lunas">✓ Kembalikan & Lunasi</option>
                                    <option value="Belum_Lunas">⚠️ Kembalikan Saja (Hutang Denda)</option>
                                </select>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 text-lg">
                                ✓ Konfirmasi Pengembalian
                            </button>
                        </div>
                    </form>
                </div>

                <div id="empty-state" class="text-center py-12">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-500">Scan QR Code resi untuk memulai</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;

        // Initialize QR Code Scanner
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Code matched = ${decodedText}`, decodedResult);
            checkUUID(decodedText);
        }

        function onScanFailure(error) {
            // Silent fail - normal when no QR code is detected
        }

        // Start scanner
        html5QrCode = new Html5Qrcode("reader");
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[0].id;
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onScanSuccess,
                    onScanFailure
                ).catch(err => {
                    console.error('Unable to start scanner:', err);
                });
            }
        }).catch(err => {
            console.error('Unable to get cameras:', err);
        });

        // Check UUID via AJAX
        function checkUUID(uuid) {
            fetch('{{ route('admin.pengembalian.cekResi') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ uuid_resi: uuid })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTransactionDetails(data);
                } else {
                    alert('⚠️ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Terjadi kesalahan saat memeriksa resi');
            });
        }

        // Check manual UUID input
        function checkManualUUID() {
            const uuid = document.getElementById('manual-uuid').value.trim();
            if (uuid.length === 36) {
                checkUUID(uuid);
            } else {
                alert('⚠️ Format UUID tidak valid (harus 36 karakter)');
            }
        }

        // Display transaction details
        function displayTransactionDetails(data) {
            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('detail-container').classList.remove('hidden');

            document.getElementById('uuid_resi').value = data.data.uuid_resi;
            document.getElementById('kategori').value = data.kategori;
            document.getElementById('nama_anggota').textContent = data.data.nama_anggota;
            document.getElementById('no_identitas').textContent = data.data.no_identitas;
            document.getElementById('judul_buku').textContent = data.data.judul_buku;
            document.getElementById('tgl_pinjam').textContent = data.data.tgl_pinjam;
            document.getElementById('tgl_jatuh_tempo').textContent = data.data.tgl_jatuh_tempo;
            document.getElementById('hari_terlambat').textContent = data.data.hari_terlambat + ' hari';
            document.getElementById('nominal_denda').textContent = 'Rp ' + data.data.nominal_denda.toLocaleString('id-ID');
        }

        // Allow Enter key on manual input
        document.getElementById('manual-uuid').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                checkManualUUID();
            }
        });
    </script>

</body>
</html>
