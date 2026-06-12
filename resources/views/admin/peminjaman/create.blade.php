<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Peminjaman Buku - Perpustakaan Sumbawa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .form-section {
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .book-item {
            transition: all 0.2s ease;
        }
        .book-item:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Form Peminjaman Buku</h1>
                        <p class="text-xs text-gray-500">Petugas: {{ auth()->user()->NamaPetugas }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600 font-medium">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-red-600 font-medium">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Proses Peminjaman Buku Baru</h2>
            <p class="text-gray-600">Silakan isi form di bawah ini untuk memproses peminjaman</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terjadi Kesalahan:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('admin.peminjaman.store') }}" id="peminjamanForm" class="space-y-6">
            @csrf

            <!-- Section 1: Kategori Anggota -->
            <div class="form-section bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">1</span>
                    Pilih Kategori Anggota
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer hover:border-blue-500 transition-all {{ old('kategori_anggota') === 'pelajar' ? 'border-blue-600 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" name="kategori_anggota" value="pelajar" class="mr-3" {{ old('kategori_anggota') === 'pelajar' ? 'checked' : '' }} required onchange="updateAnggotaPlaceholder()">
                        <div>
                            <div class="font-semibold text-gray-800">Pelajar / Mahasiswa</div>
                            <div class="text-sm text-gray-500">Anggota dengan status pelajar</div>
                        </div>
                    </label>
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer hover:border-blue-500 transition-all {{ old('kategori_anggota') === 'non_pelajar' ? 'border-blue-600 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" name="kategori_anggota" value="non_pelajar" class="mr-3" {{ old('kategori_anggota') === 'non_pelajar' ? 'checked' : '' }} required onchange="updateAnggotaPlaceholder()">
                        <div>
                            <div class="font-semibold text-gray-800">Non-Pelajar</div>
                            <div class="text-sm text-gray-500">Anggota umum / profesional</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Section 2: ID Anggota -->
            <div class="form-section bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">2</span>
                    Data Anggota Peminjam
                </h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ID Anggota <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="id_anggota" 
                        id="id_anggota"
                        value="{{ old('id_anggota') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        placeholder="Masukkan ID Anggota (misal: AP001 atau AN001)"
                        required>
                    <p class="mt-2 text-sm text-gray-500" id="anggota_hint">
                        Ketik ID anggota sesuai kategori yang dipilih
                    </p>
                </div>
            </div>

            <!-- Section 3: Pilih Buku -->
            <div class="form-section bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm">3</span>
                    Pilih Buku yang Dipinjam
                    <span class="ml-auto text-sm font-normal text-gray-500">(Maksimal 2 buku)</span>
                </h3>
                
                <div id="buku-container" class="space-y-3">
                    <!-- Buku 1 -->
                    <div class="book-item flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold text-sm">1</span>
                        <input 
                            type="text" 
                            name="buku_pilihan[]" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                            placeholder="Masukkan Kode Buku (misal: BK1001)"
                            value="{{ old('buku_pilihan.0') }}"
                            required>
                    </div>

                    <!-- Buku 2 -->
                    <div class="book-item flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold text-sm">2</span>
                        <input 
                            type="text" 
                            name="buku_pilihan[]" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                            placeholder="Masukkan Kode Buku (opsional)"
                            value="{{ old('buku_pilihan.1') }}">
                    </div>
                </div>

                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold">Perhatian:</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>Setiap anggota maksimal meminjam 2 buku secara bersamaan</li>
                                <li>Jatuh tempo otomatis 7 hari dari hari ini</li>
                                <li>Anggota dengan status "Terlambat" atau denda "Belum Lunas" akan diblokir</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Proses Peminjaman
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateAnggotaPlaceholder() {
            const kategori = document.querySelector('input[name="kategori_anggota"]:checked')?.value;
            const idInput = document.getElementById('id_anggota');
            const hint = document.getElementById('anggota_hint');
            
            if (kategori === 'pelajar') {
                idInput.placeholder = 'Contoh: AP001 (ID Anggota Pelajar)';
                hint.textContent = 'Masukkan ID Anggota Pelajar (format: APXXX)';
            } else if (kategori === 'non_pelajar') {
                idInput.placeholder = 'Contoh: AN001 (ID Anggota Non-Pelajar)';
                hint.textContent = 'Masukkan ID Anggota Non-Pelajar (format: ANXXX)';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAnggotaPlaceholder();
        });
    </script>
</body>
</html>
