@if(session('registrasi_sukses'))
<div id="onboarding-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all duration-300 scale-100" id="modal-content">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-2xl text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-2">Selamat Datang!</h2>
            <p class="text-green-50">Akun Anda telah berhasil didaftarkan</p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Rekomendasi Keamanan
                </h3>
                <p class="text-sm text-blue-800">
                    Kami sangat menyarankan Anda untuk mengaktifkan autentikasi dua faktor (2FA) untuk melindungi akun Anda.
                </p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('settings.security') }}" 
                   class="block w-full bg-blue-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300">
                    Aktifkan 2FA Sekarang
                </a>
                
                <button 
                    onclick="closeModal()"
                    type="button"
                    class="block w-full bg-gray-200 text-gray-700 text-center py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-300">
                    Nanti Saja, Jelajahi Buku
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        const modal = document.getElementById('onboarding-modal');
        const modalContent = document.getElementById('modal-content');
        
        // Add fade out animation
        modalContent.classList.add('scale-95', 'opacity-0');
        modal.classList.add('opacity-0');
        
        // Remove modal after animation
        setTimeout(() => {
            modal.remove();
        }, 300);
    }

    // Close on outside click
    document.getElementById('onboarding-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endif
