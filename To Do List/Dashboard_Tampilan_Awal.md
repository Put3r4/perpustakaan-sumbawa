# To-Do List: Dashboard (Tampilan Awal)
Update Terakhir: Juni 2026
Komponen Utama: Blade Components / Livewire, Tailwind CSS, Chart.js

Dokumen ini berisi spesifikasi kebutuhan fungsional dan desain untuk halaman muka utama Aplikasi Perpustakaan Kota Sumbawa. Dashboard ini dirancang sebagai pusat aksi (*landing hub*) yang dinamis, interaktif, dan menyesuaikan tampilan berdasarkan hak akses pengguna (*Role-Based UI*).

---

## 1. Struktur Layout & Tata Letak Antarmuka (UI)

Halaman Dashboard wajib disusun secara vertikal dengan urutan seksi sebagai berikut:

### a. Header (Navigasi Utama)
* **Komponen:** Sticky Navbar (tetap berada di atas saat halaman di-scroll).
* **Menu Pilihan:**
  * `About Us` (Scroll otomatis ke profil/animasi perpustakaan)
  * `Contact` (Scroll otomatis ke footer)
  * `FAQ` (Scroll otomatis ke seksi FAQ)
  * `Rak Buku` (Link menuju halaman katalog utama)
  * `Login` / `Dashboard Admin` 
* **Logika Otorisasi UI:** * Jika pengguna **belum login**, tampilkan tombol `Login`.
  * Jika pengguna **sudah login sebagai Pelajar/Non-Pelajar**, ganti tombol Login menjadi nama pengguna + Dropdown berisi `Profil` dan `Logout`. Tombol aksi peminjaman/pengembalian **tetap disembunyikan**.
  * Jika pengguna **sudah login sebagai Petugas/SuperAdmin**, tambahkan menu navigasi internal: `Sistem Peminjaman`, `Sistem Pengembalian`, dan `Laporan` di sebelah menu Rak Buku.

### b. Hero Section (Animasi Profil Perpustakaan Sumbawa)
* **Komponen:** Tailwind CSS Carousel atau slider interaktif yang mengombinasikan teks sambutan dan video pendek/gambar gedung Perpustakaan Kota Sumbawa.
* **Efek Khusus:** Menerapkan animasi masuk (*fade-in up* & *staggered text*) menggunakan transisi CSS modern untuk memberikan impresi digital yang profesional.

### c. Pameran Buku (Showcase & Rekomendasi)
* **Komponen:** Grid System (menampilkan maksimal 8 kartu buku pilihan).
* **Kategori Data:**
  * **Rekomendasi:** Diambil berdasarkan buku yang memiliki peringkat bintang tinggi atau pilihan pustakawan.
  * **Sering Dipinjam:** Diambil dari kalkulasi akumulasi data terbanyak pada `transaksi_pelajar` dan `transaksi_non_pelajar`.
* **Interaktivitas:** Setiap kartu buku dapat diklik untuk membuka ringkasan deskripsi (menuju ke halaman Rak Buku).

### d. Call to Action (CTA) Keanggotaan
* **Komponen:** Banner berlatar kontras dengan ilustrasi menarik.
* **Konten Teks:** Ajakan persuasif untuk menjadi anggota resmi Perpustakaan Kota Sumbawa beserta keuntungan yang didapat (Akses pinjam buku online, kuota baca, pelacakan denda transparan).
* **Tombol Aksi:** Tombol `Daftar Sekarang` yang mengarah langsung ke halaman Form Register. Jika pengguna sudah login, seksi ini otomatis disembunyikan dari UI.

### e. Seksi FAQ (Frequently Asked Questions)
* **Komponen:** Accordion menu (dapat membuka dan menutup secara halus menggunakan Tailwind/Alpine.js).
* **Daftar Pertanyaan Utama:** Aturan batas maksimal pinjam (2 buku), durasi pengembalian (7 hari), penghitungan denda keterlambatan (Rp500/hari), dan cara verifikasi akun 2-Arah.

### f. Profil Petugas Piket Hari Ini
* **Komponen:** Horizontal Card Row (menampilkan foto profil, nama petugas, dan jabatan).
* **Konsep Bisnis:** Menampilkan secara dinamis siapa saja petugas yang bertugas berdasarkan hari kalender saat ini. Jika hari ini adalah 'Kamis', sistem akan mengambil data petugas dari database yang terjadwal di hari Kamis.

### g. Statistik Perpustakaan (Library Statistics Diagram)
* **Komponen:** Widget Card terintegrasi dengan **Chart.js** (Diagram Batang / Bar Chart).
* **Indikator yang Ditampilkan:**
  1. **Monitoring Pengunjung Mingguan (Real-time):** Grafik batang interaktif yang menghitung jumlah total pengunjung fisik yang melakukan *check-in* dari hari Senin sampai Minggu pada minggu berjalan.
  2. **Jumlah Anggota Aktif:** Total baris berstatus aktif dari tabel `anggota_pelajar` dan `anggota_non_pelajar`.
  3. **Jumlah Buku Terpinjam:** Angka total transaksi yang saat ini memiliki status `'Dipinjam'` di kedua tabel transaksi.

### h. Footer (Kaki Halaman)
* **Konten Teks:** Hak Cipta Perpustakaan Kota Sumbawa, Alamat Fisik Gedung Perpustakaan, Email Resmi.
* **Menu Informasi:** `Contact Us`, `Privacy Policy`, dan `Terms of Service`.

---

## 2. Alur Logika Backend (Controller & Query Laravel 13)

### a. Logika Pengambilan Data Petugas Piket (Hari Ini)
Gunakan Eloquent Laravel untuk mendeteksi nama hari saat ini dalam bahasa Indonesia, lalu lakukan *query join* ke tabel petugas.
```php
// Kode Logika di DashboardController
$hariIni = Carbon::now()->locale('id')->dayName; // Mengambil nama hari (misal: 'Kamis')

$petugasPiket = Petugas::join('jadwal_piket', 'petugas.KodePetugas', '=', 'jadwal_piket.KodePetugas')
    ->where('jadwal_piket.HariPiket', $hariIni)
    ->select('petugas.NamaPetugas', 'petugas.Jabatan')
    ->get();

b. Logika Penyuplai Data Grafik Mingguan (Chart.js Data)

Menghitung total check-in harian dari tabel kunjungan dalam rentang 7 hari terakhir pada minggu berjalan untuk dimasukkan ke dalam dataset diagram batang.

PHP
// Query menghitung kunjungan mingguan
$kunjunganMingguan = Kunjungan::whereBetween('waktu_masuk', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
    ->selectRaw('DAYNAME(waktu_masuk) as hari, COUNT(*) as total')
    ->groupBy('hari')
    ->pluck('total', 'hari');
3. Sub-Fitur: Check-In Pengunjung Fisik (Pintu Masuk)
Untuk memastikan data pada diagram batang bergerak secara real-time, buat sebuah rute tersembunyi khusus atau form mini di meja piket petugas bernama /admin/checkin.

Fungsi: Petugas mengetikkan NIM_NIS / NIK anggota yang datang, atau memilih opsi 'Umum' jika pengunjung bukan anggota.

Proses Sistem: Begitu disubmit, sistem akan melakukan operasi INSERT ke Tabel Kunjungan. Ini secara otomatis mengubah grafik batang di halaman depan Dashboard secara instan melalui pembaruan cache database.