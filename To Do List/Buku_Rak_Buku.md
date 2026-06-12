# To-Do List: Buku (Rak Buku)
Update Terakhir: Juni 2026
Komponen Utama: Blade View / Livewire, Tailwind CSS, JavaScript (IntersectionObserver API)

Dokumen ini berisi spesifikasi kebutuhan fungsional dan teknis untuk fitur Rak Buku pada Aplikasi Perpustakaan Kota Sumbawa. Fitur ini berfungsi sebagai katalog digital interaktif bagi pengunjung sekaligus pusat kendali sirkulasi bagi petugas.

---

## 1. Struktur Antarmuka & Tata Letak (UI/UX)

### a. Grid Katalog Buku
* **Komponen:** Responsive Grid Layout (Tailwind CSS: `grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5`).
* **Aturan Tampilan:** Menampilkan maksimal **20 kartu buku per halaman**. Jika jumlah koleksi buku melebihi 20, sistem wajib menampilkan navigasi *Pagination* standar Laravel di bagian bawah grid.
* **Elemen Kartu Buku (Book Card):**
  * Sampul Buku (Ilustrasi/Gambar Cover)
  * Judul Buku (`Judul`)
  * Nama Pengarang (`Pengarang`)
  * Status Stok (`JumEksemplar` > 0 ? "Tersedia" : "Kosong")

### b. Komponen Modal Detail Buku
* Ketika salah satu kartu buku diklik, sistem tidak dialihkan ke halaman baru, melainkan membuka jendela **Modal Detail Buku** (*Pop-up*) yang berisi informasi lengkap:
  * Klasifikasi Buku (`NoUdc` & `NoReg`)
  * Penerbit, Tahun Terbit, Kota Terbit, Bahasa, Edisi, dan ISBN.
  * Sinopsis lengkap (`Deskripsi`).

---

## 2. Kontrol Akses UI Berbasis Peran (Role-Based Display)

Keamanan visual wajib diterapkan pada Modal Detail Buku untuk membedakan hak aksi antara pengunjung umum (Pelajar/Non-Pelajar) dan pihak internal (Petugas/Admin):

* **Tampilan Pengaruh (Pelajar / Non-Pelajar / Anonim):**
  * Pengenang hanya berhak membaca informasi dan deskripsi buku.
  * **TIDAK ADA** tombol "Pinjam Buku" atau "Kembalikan Buku" pada layar mereka.
* **Tampilan Internal (Petugas / SuperAdmin):**
  * Di dalam Modal Detail Buku wajib memunculkan 2 tombol aksi utama berwarna kontras:
    1. **Tombol "Proses Peminjaman"** (Mengarah ke Form Peminjaman)
    2. **Tombol "Proses Pengembalian"** (Mengarah ke Form Pengembalian)
  * Petugas juga memiliki akses ke menu sunting data buku (*CRUD Link* jika hak aksesnya terpenuhi).

---

## 3. Fitur Tracking Analitik: Durasi Baca 1 Menit

Untuk memenuhi kebutuhan "Laporan Buku" mengenai buku yang paling banyak dilihat, sistem memanfaatkan JavaScript untuk melacak perhatian (*attention tracking*) pengunjung secara presisi.

### a. Logika Frontend (JavaScript IntersectionObserver)
Skrip ini mendeteksi kapan Modal Detail Buku dibuka dan menghitung durasi keaktifannya. Jika modal dibiarkan terbuka oleh pengunjung selama minimal **60 detik (1 menit)**, sistem mengasumsikan pengunjung sedang membaca deskripsi dan mengirim data ke server.

```javascript
// Ilustrasi Logika JavaScript di dalam Blade View Detail Buku
let startTime;
let trackingTimer;
const durationRequired = 60000; // 60 detik (1 menit)

function openBookModal(kodeBuku) {
    startTime = new Date().getTime();
    
    // Set timer untuk mendeteksi pembacaan selama 1 menit
    trackingTimer = setTimeout(() => {
        sendViewMetricToServer(kodeBuku);
    }, durationRequired);
}

function closeBookModal() {
    clearTimeout(trackingTimer); // Batalkan jika modal ditutup sebelum 1 menit
}

function sendViewMetricToServer(kodeBuku) {
    fetch(`/api/buku/${kodeBuku}/increment-view`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    });
}