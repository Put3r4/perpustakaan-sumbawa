<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Rak Buku - Perpustakaan Sumbawa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 0;
            border-radius: 1rem;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .book-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .badge-available {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .badge-unavailable {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg border-b sticky top-0 z-40">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Perpustakaan Sumbawa</h1>
                        <p class="text-sm text-gray-500">Katalog Buku Digital</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h2 class="text-4xl font-bold text-gray-800 mb-2">Katalog Buku</h2>
            <p class="text-gray-600">Jelajahi koleksi {{ $books->total() }} buku di perpustakaan kami</p>
        </div>

        <!-- Books Grid -->
        @if($books->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($books as $book)
                    <div class="book-card bg-white rounded-xl shadow-md overflow-hidden" 
                         onclick="openModal('{{ $book->KodeBuku }}')"
                         data-book-id="{{ $book->KodeBuku }}"
                         data-book-noudc="{{ $book->NoUdc }}"
                         data-book-noreg="{{ $book->NoReg }}"
                         data-book-judul="{{ htmlspecialchars($book->Judul, ENT_QUOTES) }}"
                         data-book-pengarang="{{ htmlspecialchars($book->Pengarang, ENT_QUOTES) }}"
                         data-book-penerbit="{{ htmlspecialchars($book->Penerbit, ENT_QUOTES) }}"
                         data-book-tahun="{{ $book->TahunTerbit }}"
                         data-book-kota="{{ htmlspecialchars($book->KotaTerbit, ENT_QUOTES) }}"
                         data-book-bahasa="{{ $book->Bahasa }}"
                         data-book-edisi="{{ htmlspecialchars($book->Edisi ?? '-', ENT_QUOTES) }}"
                         data-book-isbn="{{ $book->Isbn }}"
                         data-book-deskripsi="{{ htmlspecialchars($book->Deskripsi ?? 'Tidak ada deskripsi.', ENT_QUOTES) }}"
                         data-book-subjek-utama="{{ htmlspecialchars($book->SubjekUtama, ENT_QUOTES) }}"
                         data-book-subjek-tambahan="{{ htmlspecialchars($book->SubjekTambahan ?? '-', ENT_QUOTES) }}"
                         data-book-eksemplar="{{ $book->JumEksemplar }}"
                         data-book-views="{{ $book->views_count }}">
                        
                        <!-- Book Cover Placeholder -->
                        <div class="h-56 bg-gradient-to-br from-blue-400 via-blue-500 to-blue-700 flex items-center justify-center relative overflow-hidden">
                            <svg class="w-24 h-24 text-white opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            
                            <!-- Status Badge -->
                            @if($book->JumEksemplar > 0)
                                <span class="absolute top-3 right-3 badge-available text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                    Tersedia
                                </span>
                            @else
                                <span class="absolute top-3 right-3 badge-unavailable text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                    Dipinjam
                                </span>
                            @endif
                        </div>

                        <!-- Book Info -->
                        <div class="p-5">
                            <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2 min-h-[3.5rem]">
                                {{ $book->Judul }}
                            </h3>
                            
                            <div class="space-y-1 text-sm text-gray-600 mb-3">
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="truncate">{{ $book->Pengarang }}</span>
                                </p>
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span class="truncate">{{ $book->Penerbit }}</span>
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t">
                                <span class="text-xs text-gray-500">Stok: {{ $book->JumEksemplar }}</span>
                                <span class="text-blue-600 text-sm font-semibold hover:text-blue-800">
                                    Lihat Detail →
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-8">
                {!! $books->links() !!}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-md p-12 text-center">
                <svg class="w-32 h-32 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Belum Ada Buku</h3>
                <p class="text-gray-500">Koleksi buku akan segera tersedia</p>
            </div>
        @endif
    </div>

    <!-- Modal Detail Buku -->
    <div id="bookModal" class="modal">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-t-xl">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 id="modalJudul" class="text-2xl font-bold mb-2"></h2>
                        <p id="modalPengarang" class="text-blue-100 text-sm"></p>
                    </div>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200 focus:outline-none ml-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Informasi Katalog -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Informasi Katalog</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">No. UDC:</span>
                                <span id="modalNoUdc" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">No. Registrasi:</span>
                                <span id="modalNoReg" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">ISBN:</span>
                                <span id="modalIsbn" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">Edisi:</span>
                                <span id="modalEdisi" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Informasi Publikasi</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">Penerbit:</span>
                                <span id="modalPenerbit" class="text-gray-800 font-medium text-sm text-right"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">Tahun Terbit:</span>
                                <span id="modalTahun" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">Kota Terbit:</span>
                                <span id="modalKota" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">Bahasa:</span>
                                <span id="modalBahasa" class="text-gray-800 font-medium text-sm"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjek -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Subjek & Kategori</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="text-gray-600 text-sm">Subjek Utama:</span>
                            <p id="modalSubjekUtama" class="text-gray-800 font-medium mt-1"></p>
                        </div>
                        <div>
                            <span class="text-gray-600 text-sm">Subjek Tambahan:</span>
                            <p id="modalSubjekTambahan" class="text-gray-800 font-medium mt-1"></p>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi/Sinopsis -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3 uppercase tracking-wide flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Sinopsis / Deskripsi
                    </h3>
                    <p id="modalDeskripsi" class="text-gray-700 text-sm leading-relaxed whitespace-pre-line"></p>
                </div>

                <!-- Ketersediaan Stok -->
                <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                    <h3 class="text-sm font-semibold text-green-900 mb-2 uppercase tracking-wide">Ketersediaan</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 text-sm">Jumlah Eksemplar:</span>
                        <span id="modalEksemplar" class="text-2xl font-bold text-green-600"></span>
                    </div>
                </div>

                <!-- Analytics Info (Hidden from users, for debugging) -->
                <div class="hidden">
                    <p class="text-xs text-gray-400">Views: <span id="modalViews"></span></p>
                </div>
            </div>

            <!-- Modal Footer - Sirkulasi Buttons (Only for Petugas) -->
            @can('processSirkulasi', \App\Models\Buku::class)
                <div class="bg-gray-50 p-6 rounded-b-xl border-t">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="prosesPeminjaman()" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Proses Peminjaman
                        </button>
                        <button onclick="prosesPengembalian()" class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-300 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Proses Pengembalian
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 text-center mt-3">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Hanya Petugas yang dapat memproses sirkulasi buku
                    </p>
                </div>
            @endcan
        </div>
    </div>

    <!-- JavaScript: Modal Control & Analytics Attention Tracker -->
    <script>
        // Global variables
        let currentBookId = null;
        let viewTimer = null;
        let modalOpenTime = null;
        const VIEW_THRESHOLD = 60000; // 60 seconds = 60000 milliseconds
        const API_ENDPOINT = '/api/buku/{id}/increment-view';

        /**
         * Open modal and display book details
         * Start analytics timer when modal opens
         */
        function openModal(bookId) {
            const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
            if (!bookCard) return;

            // Get all book data from data attributes
            const bookData = {
                id: bookCard.dataset.bookId,
                noudc: bookCard.dataset.bookNoudc,
                noreg: bookCard.dataset.bookNoreg,
                judul: bookCard.dataset.bookJudul,
                pengarang: bookCard.dataset.bookPengarang,
                penerbit: bookCard.dataset.bookPenerbit,
                tahun: bookCard.dataset.bookTahun,
                kota: bookCard.dataset.bookKota,
                bahasa: bookCard.dataset.bookBahasa,
                edisi: bookCard.dataset.bookEdisi,
                isbn: bookCard.dataset.bookIsbn,
                deskripsi: bookCard.dataset.bookDeskripsi,
                subjekUtama: bookCard.dataset.bookSubjekUtama,
                subjekTambahan: bookCard.dataset.bookSubjekTambahan,
                eksemplar: bookCard.dataset.bookEksemplar,
                views: bookCard.dataset.bookViews
            };

            // Populate modal with book data
            document.getElementById('modalJudul').textContent = bookData.judul;
            document.getElementById('modalPengarang').textContent = 'oleh ' + bookData.pengarang;
            document.getElementById('modalNoUdc').textContent = bookData.noudc;
            document.getElementById('modalNoReg').textContent = bookData.noreg;
            document.getElementById('modalIsbn').textContent = bookData.isbn;
            document.getElementById('modalEdisi').textContent = bookData.edisi;
            document.getElementById('modalPenerbit').textContent = bookData.penerbit;
            document.getElementById('modalTahun').textContent = bookData.tahun;
            document.getElementById('modalKota').textContent = bookData.kota;
            document.getElementById('modalBahasa').textContent = bookData.bahasa;
            document.getElementById('modalSubjekUtama').textContent = bookData.subjekUtama;
            document.getElementById('modalSubjekTambahan').textContent = bookData.subjekTambahan;
            document.getElementById('modalDeskripsi').textContent = bookData.deskripsi;
            document.getElementById('modalEksemplar').textContent = bookData.eksemplar;
            document.getElementById('modalViews').textContent = bookData.views;

            // Show modal
            const modal = document.getElementById('bookModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scroll

            // Store current book ID
            currentBookId = bookData.id;

            // Start analytics timer - increment view after 60 seconds
            startViewTimer(bookData.id);
        }

        /**
         * Close modal and cleanup
         * Cancel analytics timer if modal closed before 60 seconds
         */
        function closeModal() {
            const modal = document.getElementById('bookModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto'; // Restore scroll

            // Cancel timer if modal closed before threshold
            cancelViewTimer();

            // Reset current book
            currentBookId = null;
        }

        /**
         * Start analytics attention tracker timer
         * Only increment view count if user keeps modal open for 60 seconds
         */
        function startViewTimer(bookId) {
            // Clear any existing timer first
            cancelViewTimer();

            // Record modal open timestamp
            modalOpenTime = Date.now();

            console.log(`[Analytics] Timer started for book ${bookId}. Waiting ${VIEW_THRESHOLD / 1000} seconds...`);

            // Set timer for 60 seconds (60000ms)
            viewTimer = setTimeout(() => {
                const elapsedTime = Date.now() - modalOpenTime;
                console.log(`[Analytics] Threshold reached! Elapsed time: ${elapsedTime}ms`);
                
                // Send increment request to API
                incrementBookView(bookId);
            }, VIEW_THRESHOLD);
        }

        /**
         * Cancel view timer
         * Called when modal is closed before reaching 60-second threshold
         */
        function cancelViewTimer() {
            if (viewTimer) {
                clearTimeout(viewTimer);
                
                if (modalOpenTime) {
                    const elapsedTime = Date.now() - modalOpenTime;
                    console.log(`[Analytics] Timer cancelled. Modal was open for ${elapsedTime}ms (threshold: ${VIEW_THRESHOLD}ms)`);
                }
                
                viewTimer = null;
                modalOpenTime = null;
            }
        }

        /**
         * Send API request to increment book view count
         * Uses fetch API for async background request
         */
        async function incrementBookView(bookId) {
            const url = API_ENDPOINT.replace('{id}', bookId);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                console.log(`[Analytics] Sending increment request for book ${bookId}...`);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        timestamp: Date.now()
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    console.log(`[Analytics] ✓ View incremented successfully. New count: ${data.data.views_count}`);
                    
                    // Update the views count in modal (hidden debug info)
                    const modalViews = document.getElementById('modalViews');
                    if (modalViews) {
                        modalViews.textContent = data.data.views_count;
                    }
                } else {
                    console.error('[Analytics] ✗ Failed to increment view:', data.message);
                }
            } catch (error) {
                console.error('[Analytics] ✗ Network error:', error.message);
            }
        }

        /**
         * Process book borrowing (Peminjaman)
         * Only accessible by Petugas
         */
        function prosesPeminjaman() {
            if (!currentBookId) return;
            
            // TODO: Implement borrowing logic
            alert(`Proses peminjaman untuk buku: ${currentBookId}\n\nFitur ini akan segera tersedia.`);
        }

        /**
         * Process book return (Pengembalian)
         * Only accessible by Petugas
         */
        function prosesPengembalian() {
            if (!currentBookId) return;
            
            // TODO: Implement return logic
            alert(`Proses pengembalian untuk buku: ${currentBookId}\n\nFitur ini akan segera tersedia.`);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('bookModal');
                if (modal.classList.contains('active')) {
                    closeModal();
                }
            }
        });

        // Cleanup timer when user leaves page
        window.addEventListener('beforeunload', function() {
            cancelViewTimer();
        });

        // Log page load for debugging
        console.log('[Analytics] Rak Buku page loaded. View tracking active.');
        console.log(`[Analytics] View threshold: ${VIEW_THRESHOLD / 1000} seconds`);
    </script>
</body>
</html>
