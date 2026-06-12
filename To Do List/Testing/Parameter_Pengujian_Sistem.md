# To-Do List: Master Parameter Pengujian Keseluruhan Sistem (Black Box Testing)
Update Terakhir: Juni 2026
Metodologi Pengujian: Black Box Testing (Berorientasi Fungsi dan Keamanan Sisi Server)

Dokumen ini merangkum seluruh parameter pengujian terpadu untuk Aplikasi Perpustakaan Kota Sumbawa. Pengujian ini wajib dieksekusi secara berurutan untuk memastikan seluruh fungsi antarmuka, aturan bisnis perpustakaan, validasi database, dan sistem proteksi Laravel 13 berjalan 100% tanpa celah kebocoran hak akses.

---

## MODUL 1: DASHBOARD (TAMPILAN AWAL)

### PARAMETER 1.1: Uji Akses Pengunjung Anonim (Belum Login)
* **Skenario Pengujian:** Mengakses URL akar (`/`) aplikasi perpustakaan tanpa melakukan autentikasi akun.
* **Langkah Kerja:** Buka browser pada mode *Incognito*, ketik URL aplikasi, lalu periksa elemen halaman dari atas (Header) hingga bawah (Footer).
* **Hasil yang Diharapkan:** * Seluruh komponen UI (Header, Hero Animasi, Pameran Buku, Banner CTA, FAQ, Profil Petugas Piket, Grafik Statistik, dan Footer) dirender dengan sempurna.
  * Navigasi Header menampilkan tombol `Login`.
  * Tombol internal admin/petugas (`Sistem Peminjaman`, `Sistem Pengembalian`, `Menu Laporan`) serta tombol eksekusi sirkulasi **tidak muncul** di layar.

### PARAMETER 1.2: Uji Akses Dinamis Peran Petugas pada UI Header
* **Skenario Pengujian:** Memastikan sistem *Role-Based UI* mendeteksi akun internal dan memunculkan menu kontrol admin.
* **Langkah Kerja:** Login menggunakan kredensial akun dengan `HakAkses = 'Petugas'`, lalu kembali ke halaman utama Dashboard.
* **Hasil yang Diharapkan:** * Tombol `Login` pada Header otomatis hilang.
  * Sistem merubah navigasi dengan memunculkan menu internal eksklusif: `Sistem Peminjaman`, `Sistem Pengembalian`, dan `Laporan Perpustakaan`.

### PARAMETER 1.3: Uji Validasi Jadwal Piket Petugas Otomatis
* **Skenario Pengujian:** Memastikan profil petugas yang dipajang di halaman muka berganti secara dinamis mengikuti kalender hari kerja server.
* **Langkah Kerja:** Ubah tanggal/hari pada sistem operasi server (misal dari hari Rabu ke hari Kamis). Muat ulang (*refresh*) halaman Dashboard utama.
* **Hasil yang Diharapkan:** * Daftar nama, foto, dan jabatan petugas yang tampil di seksi "Petugas Piket Hari Ini" berubah secara otomatis, tepat mencerminkan data petugas yang memiliki kecocokan `HariPiket = 'Kamis'` di database `jadwal_piket`.

### PARAMETER 1.4: Uji Sinkronisasi Grafik Statistik Mingguan secara Real-Time
* **Skenario Pengujian:** Memastikan grafik batang (Chart.js) langsung merespon dan menangkap data kehadiran fisik baru.
* **Langkah Kerja:** Buka halaman `/admin/checkin` pada tab terpisah, daftarkan 1 pengunjung umum baru. Kembali ke Dashboard utama, lihat diagram batang hari berjalan.
* **Hasil yang Diharapkan:** * Jumlah baris diagram batang pada hari berjalan langsung naik secara otomatis (misal dari 40 pengunjung menjadi 41 pengunjung) tanpa merusak struktur koordinat grafik.

---

## MODUL 2: BUKU (RAK BUKU)

### PARAMETER 2.1: Uji Pembatasan Data & Navigasi Pagination
* **Skenario Pengujian:** Memastikan sistem membatasi muatan data katalog agar tidak membebani performa browser (*anti-page bloating*).
* **Langkah Kerja:** Isi tabel `buku` dengan 25 data sampel (*dummy*). Masuk ke halaman menu Rak Buku.
* **Hasil yang Diharapkan:** * Halaman pertama Rak Buku hanya menampilkan tepat **20 kartu buku**.
  * Komponen tautan navigasi nomor halaman (*Next*, *Previous*, `1`, `2`) muncul secara otomatis di bawah grid katalog.

### PARAMETER 2.2: Uji Manipulasi Elemen Tombol UI (Client-Side Inspection Protection)
* **Skenario Pengujian:** Menghalau pengguna luar yang mencoba menyuntikkan elemen tombol aksi sirkulasi secara ilegal lewat browser DevTools.
* **Skenario Langkah Kerja:** Login sebagai Pelajar, buka *Inspect Element* (F12) di browser, gandakan atau suntikkan baris kode HTML tombol `<a href="/admin/peminjaman/tambah">Proses Pinjam</a>` ke dalam komponen detail kartu buku, lalu klik tombol palsu tersebut.
* **Hasil yang Diharapkan:** * Sistem *Server-Side Authorization* (Laravel Policy) menangkap instruksi ilegal tersebut, memutus koneksi transaksi, dan mengembalikan layar respon kesalahan **403 Forbidden / Unauthorized**.

### PARAMETER 2.3: Uji Akurasi Durasi Pelacakan Minat Buku (Tracking 1 Menit)
* **Skenario Pengujian:** Memvalidasi presisi JavaScript `IntersectionObserver` dan API Laravel dalam mendeteksi minat baca pengunjung minimal selama 60 detik.
* **Langkah Kerja Skenario A (Gagal Batas):** Buka Modal Detail Buku, biarkan jendela terbuka selama 45 detik, lalu tutup modal. Periksa kolom `views_count` buku tersebut di database.
* **Hasil Skenario A:** Nilai `views_count` **tidak berubah (tetap)** karena tidak memenuhi ambang batas waktu.
* **Langkah Kerja Skenario B (Sukses Batas):** Buka Modal Detail Buku, biarkan jendela terbuka tanpa ditutup selama 65 detik, lalu tutup modal. Periksa kembali kolom database.
* **Hasil Skenario B:** Nilai `views_count` di database wajib **naik secara akurat sebesar +1**.

---

## MODUL 3: SISTEM PEMINJAMAN BUKU

### PARAMETER 3.1: Uji Validasi Batas Maksimal Kuota Pinjam (Aturan 2 Buku)
* **Skenario Pengujian:** Menegakkan aturan batas peminjaman maksimal agar anggota tidak menimbun inventaris buku.
* **Langkah Kerja:** Pilih data anggota Pelajar yang tercatat sedang memegang 1 buku pinjaman aktif. Buka Form Peminjaman Petugas, masukkan nama anggota tersebut, lalu coba input 2 judul buku baru sekaligus ke dalam keranjang pinjam form, kemudian klik simpan.
* **Hasil yang Diharapkan:** * Controller Laravel menolak pengiriman formulir, mendepak proses simpan, dan melempar pesan peringatan merah: *"Peminjaman Ditolak! Anggota telah melebihi batas maksimal kuota (Maksimal 2 Buku)."*

### PARAMETER 3.2: Uji Blokir Otomatis Anggota Pemegang Buku Jatuh Tempo
* **Skenario Pengujian:** Mengunci hak pinjam anggota yang memiliki rekam jejak sirkulasi buruk (terlambat memulangkan buku).
* **Langkah Kerja:** Manipulasi 1 transaksi lama milik anggota Non-Pelajar di database dengan mengubah kolom `StatusTransaksi = 'Terlambat'` dan `TglKembali = NULL`. Masuk ke Form Peminjaman Baru, coba daftarkan peminjaman buku baru untuk anggota tersebut.
* **Hasil yang Diharapkan:** * Sistem membaca riwayat buruk anggota, memblokir pengajuan di tempat, dan memunculkan notifikasi penolakan transaksi baru sampai buku lama dikembalikan dan denda dilunasi.

### PARAMETER 3.3: Uji Otomatisasi Perhitungan Kalender (Aturan Batas Waktu 7 Hari)
* **Skenario Pengujian:** Memastikan sistem mengunci durasi sirkulasi selama tepat 1 minggu tanpa intervensi input manual petugas.
* **Langkah Kerja:** Eksekusi peminjaman buku normal pada tanggal berjalan hari ini (Misal: 11 Juni 2026). Periksa baris data baru yang masuk ke database `transaksi_pelajar`.
* **Hasil yang Diharapkan:** * Kolom `TglPinjam` wajib terisi otomatis nilai string tanggal hari ini (`2026-06-11`) dan kolom `TglJatuhTempo` wajib terisi otomatis nilai maju 7 hari kalender (`2026-06-18`).

### PARAMETER 3.4: Uji Enkripsi Keamanan UUID pada Kode QR Code Resi
* **Skenario Pengujian:** Memastikan resi peminjaman menggunakan kode token acak yang aman dari serangan tebakan ID berurutan (*ID Enumeration*).
* **Langkah Kerja:** Selesaikan satu proses transaksi peminjaman, cetak resi digital yang memuat gambar QR Code. Ambil perangkat smartphone, jalankan aplikasi scan barcode umum (bukan aplikasi internal), lalu scan QR Code tersebut.
* **Hasil yang Diharapkan:** * Hasil pembacaan scan kamera wajib mengeluarkan untaian string format **UUID acak sepanjang 36 karakter** (Contoh: `4f3b89e1-bc72-46d9-9a11-c354eef7b812`), bukan angka ID tunggal urut database (`1`, `2`, `3`).

---

## MODUL 4: SISTEM PENGEMBALIAN BUKU

### PARAMETER 4.1: Uji Validasi Pemulangan Tepat Waktu (Bebas Denda)
* **Skenario Pengujian:** Memproses pengembalian buku yang patuh aturan di mana tanggal kembali belum melewati jatuh tempo.
* **Langkah Kerja:** Lakukan scan QR Resi transaksi peminjaman yang rentang waktunya baru berjalan 3 hari dari tanggal pinjam. Selesaikan konfirmasi form pengembalian.
* **Hasil yang Diharapkan:** * Pada database transaksi terkait, kolom `TglKembali` terisi tanggal hari ini, nilai kolom `Denda = 0`, status berubah menjadi `StatusTransaksi = 'Dikembalikan'`, dan `StatusBayarDenda = 'Tidak_Ada'`.

### PARAMETER 4.2: Uji Akurasi Hitung Otomatis Denda Keterlambatan (Aturan Rp500/Hari)
* **Skenario Pengujian:** Memvalidasi ketepatan kalkulasi denda finansial sirkulasi yang telat dikembalikan berdasarkan manipulasi waktu server.
* **Langkah Kerja:** Ambil contoh resi transaksi peminjaman yang memiliki nilai kolom `TglJatuhTempo` tepat 4 hari yang lalu dari tanggal hari ini. Lakukan pemindaian (*scanning*) QR Code resi tersebut di meja sirkulasi petugas.
* **Hasil yang Diharapkan:** * Sistem membaca selisih keterlambatan sebanyak 4 hari. Form pengembalian wajib memunculkan kalkulasi matematis otomatis: $4 \times 500 = \text{Rp2.000}$.
  * Setelah disubmit, database memperbarui kolom transaksi menjadi `Denda = 2000`, `StatusTransaksi = 'Terlambat'`, dan `StatusBayarDenda = 'Belum_Lunas'`.

### PARAMETER 4.3: Uji Penguncian Akun Atas Kasus Penundaan Bayar Denda (Hutang Denda)
* **Skenario Pengujian:** Memastikan sistem mengingat status keuangan anggota yang belum melunasi denda keterlambatannya.
* **Langkah Kerja:** Pada proses tes Parameter 4.2 di atas, saat Modal Konfirmasi Akhir muncul, pilih opsi tombol *"Kembalikan Saja (Hutang Denda)"*. Beralihlah ke Form Peminjaman Buku Baru, lalu coba daftarkan anggota yang berhutang denda tersebut untuk meminjam buku lain.
* **Hasil yang Ditolak:** * Sistem peminjaman wajib memblokir pengajuan transaksi baru karena status keuangan anggota tersebut masih memiliki ganjalan rekam data `StatusBayarDenda = 'Belum_Lunas'`.

### PARAMETER 4.4: Uji Validasi Penolakan UUID QR Code Palsu/Manipulasi
* **Skenario Pengujian:** Memastikan form input manual pengembalian kebal dari serangan injeksi teks acak atau token palsu.
* **Langkah Kerja:** Pada kolom input manual nomor resi pengembalian petugas, ketik teks string acak (Contoh: `'KODE-BUKU-PALSU-123'`), lalu tekan enter/submit.
* **Hasil yang Diharapkan:** * Sistem menolak eksekusi, database aman tanpa perubahan, dan antarmuka memunculkan notifikasi peringatan: *"Resi Tidak Ditemukan / Tidak Valid!"*

---

## MODUL 5: SISTEM LAPORAN PERPUSTAKAAN

### PARAMETER 5.1: Uji Akurasi Penyaringan Rentang Waktu Laporan (Filter Date)
* **Skenario Pengujian:** Memastikan laporan eksekutif memuat data yang jujur sesuai jangka waktu yang diminta pimpinan perpustakaan.
* **Langkah Kerja:** Isi form filter tanggal laporan pimpinan dari rentang *01 Juni 2026* s.d *05 Juni 2026*, lalu tekan tombol Generate.
* **Hasil yang Diharapkan:** * Tabel rekapitulasi data hanya menampilkan baris transaksi sirkulasi yang terjadi di dalam rentang tanggal 1 sampai 5 Juni. Data transaksi yang bertanggal 06 Juni wajib dikeluarkan dari tabel.

### PARAMETER 5.2: Uji Penandaan Visual (Highlight) Jatuh Tempo Laporan
* **Skenario Pengujian:** Memudahkan Kepala Perpustakaan dalam mengidentifikasi aset buku yang tertahan di luar batas waktu aman.
* **Langkah Kerja:** Jalankan perintah cetak PDF untuk "Laporan Peminjaman", pastikan di dalam dataset terdapat data peminjaman yang sudah melewati batas 7 hari dan belum kembali.
* **Hasil yang Diharapkan:** * Dokumen cetak laporan wajib memberikan pembeda visual yang mencolok (seperti baris diberi latar belakang warna abu-abu atau teks dicetak tebal warna merah) khusus pada data yang terlambat tersebut.

### PARAMETER 5.3: Uji Keutuhan Tata Letak Ekspor Dokumen Resmi (PDF & Excel Format)
* **Skenario Pengujian:** Menjamin berkas hasil unduhan rapi, profesional, dan memenuhi standar birokrasi pemerintahan daerah Sumbawa.
* **Langkah Kerja:** Klik tombol "Cetak PDF" dan "Cetak Excel" pada kluster laporan keuangan denda. Buka berkas dokumen `.pdf` atau `.xlsx` yang terunduh ke komputer.
* **Hasil yang Diharapkan:** * Struktur komponen tidak boleh bergeser atau berantakan (*layout overflow*).
  * Dokumen wajib memuat susunan: Kop Resmi Dinas Perpustakaan Kabupaten Sumbawa di paling atas, tabel isi di tengah, dan Lembar Pengesahan nama terang + NIP Kepala Perpustakaan Kota Sumbawa untuk tanda tangan fisik di pojok kanan bawah.

### PARAMETER 5.4: Uji Blokir Akses URL Dokumen Laporan Pengunjung Umum
* **Skenario Pengujian:** Melindungi kerahasiaan data internal perpustakaan dari kebocoran ke pihak luar.
* **Langkah Kerja:** Login sebagai Pelajar, salin secara paksa URL khusus unduh laporan ke address bar browser (Contoh: `perpustakaan.test/admin/laporan/denda/pdf`), lalu tekan Enter.
* **Hasil yang Diharapkan:** * Laravel Policy menggagalkan request tersebut, meredam proses ekspor data, dan browser menampilkan kode penolakan **403 Forbidden**.

---

## MODUL 6: FITUR SISTEM (KEAMANAN & AUTENTIKASI)

### PARAMETER 6.1: Uji Penembakan Endpoint API Langsung (API Bypass Protection)
* **Skenario Pengujian:** Menguji kekokohan gerbang keamanan rute server menggunakan aplikasi tester pihak ketiga (Postman).
* **Langkah Kerja:** Ambil token akses sesi login milik user kategori Pelajar. Buka aplikasi Postman, buat request POST ke endpoint internal petugas `/admin/peminjaman/proses` dengan menempelkan token Pelajar tersebut ke dalam komponen header request, lalu klik Send.
* **Hasil yang Diharapkan:** * Server Laravel 13 wajib membaca pelanggaran hak akses tersebut melalui intersep *Middleware* dan memberikan respon balasan mentah JSON berupa **403 Status Code: Unauthorized Action**.

### PARAMETER 6.2: Uji Injeksi Parameter Form Register (Mass Assignment Attack Prevention)
* **Skenario Pengujian:** Menguji ketahanan form registrasi publik dari upaya peretas menaikkan derajat akun menjadi admin via manipulasi kolom input tersembunyi.
* **Langkah Kerja:** Buka Form Register Pelajar, lakukan klik kanan -> *Inspect Element*, buat satu tag input siluman baru secara paksa ke dalam formulir HTML: `<input type="hidden" name="HakAkses" value="SuperAdmin">`. Isi seluruh data form secara normal, lalu klik tombol submit pendaftaran. Periksa hasil data buatan baru tersebut di dalam basis data `anggota_pelajar`.
* **Hasil yang Diharapkan:** * Akun baru sukses terdaftar namun tipe baris kolom mutlak tersimpan sebagai anggota biasa. Parameter siluman `HakAkses = 'SuperAdmin'` dibuang total dan diabaikan oleh server berkat penyaringan ketat Laravel Form Request.

### PARAMETER 6.3: Uji Validasi Token OTP Palsu pada Login Challenge (Keamanan 2FA)
* **Skenario Pengujian:** Memastikan sistem keamanan dua faktor (2-Step Verification) kebal dari teknik tebakan digit token acak.
* **Langkah Kerja:** Aktifkan fitur 2FA pada profil akun Petugas. Lakukan logout, lalu lakukan login kembali menggunakan email dan password yang benar. Ketika halaman berganti ke `/login/two-factor-challenge`, masukkan 6 digit angka palsu asal-asalan (Contoh: `123456`), lalu klik verifikasi.
* **Hasil yang Diharapkan:** * Sistem menolak kode tersebut, memblokir akses ke dashboard internal, dan menampilkan pesan kesalahan token tidak valid. Petugas tetap tertahan di luar sistem sampai 6 digit kode yang dimasukkan cocok dengan aplikasi authenticator di ponselnya.