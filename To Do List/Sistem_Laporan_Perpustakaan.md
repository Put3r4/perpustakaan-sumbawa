# To-Do List: Sistem Laporan Perpustakaan
Update Terakhir: Juni 2026
Komponen Utama: Laravel Excel (`maatwebsite/excel`), Laravel PDF (`barryvdh/laravel-dompdf`), Eloquent Aggregates

Dokumen ini berisi spesifikasi teknis dan kebutuhan fungsional untuk Modul Pelaporan pada Aplikasi Perpustakaan Kota Sumbawa. Fitur ini dirancang khusus untuk menghasilkan ringkasan eksekutif yang akan digunakan oleh Kepala Perpustakaan dalam pengambilan keputusan. Akses fitur ini sepenuhnya tertutup untuk pengunjung umum (Pelajar/Non-Pelajar).

---

## 1. Pembagian Kategori Konten Laporan

Laporan dibagi menjadi 5 kluster utama yang menarik data secara langsung dari tabel transaksi gabungan dan analitik:

### a. Laporan Peminjaman
* **Isi Data:** Menampilkan daftar buku yang saat ini sedang berada di tangan anggota (status `'Dipinjam'` atau `'Terlambat'`).
* **Fitur Highlight:** Sistem wajib memberikan penanda visual khusus (warna merah/teks tebal) untuk baris data peminjaman yang tanggal hari ini telah melewati `TglJatuhTempo` (Buku Jatuh Tempo).

### b. Laporan Pengembalian
* **Isi Data:** Menampilkan rekapitulasi buku-buku yang sudah sukses dipulangkan (status `'Dikembalikan'`).
* **Metrik Akumulasi:** Menampilkan total volume buku yang berhasil dikembalikan dalam periode tertentu, serta menyajikan daftar pengecualian buku yang *seharusnya kembali* namun statusnya masih tertahan.

### c. Laporan Buku (Analitik & Kondisi)
* **Isi Data & Statistik Kunci:**
  1. *Buku Paling Banyak Dipinjam:* Diurutkan berdasarkan frekuensi kemunculan `KodeBuku` tertinggi di kedua tabel transaksi.
  2. *Buku Paling Sedikit Dipinjam:* Diurutkan berdasarkan frekuensi terendah.
  3. *Paling Banyak Dilihat di Website:* Diambil langsung dari nilai tertinggi kolom `views_count` (hasil tracking durasi 1 menit).
  4. *Catatan Kondisi Buku:* Menampilkan data masukan ( feedback) terkait buku rusak, hilang, atau saran judul baru yang dikirimkan pengunjung.

### d. Laporan Denda
* **Isi Data:** Menampilkan rincian nominal keuangan denda yang menimpa setiap pengunjung.
* **Metrik Finansial:**
  1. *Akumulasi Denda Diterima:* Total nominal dari transaksi berstatus `StatusBayarDenda = 'Lunas'`.
  2. *Rincian Piutang Denda:* Daftar nama pengunjung yang masih berstatus `StatusBayarDenda = 'Belum_Lunas'`.
  3. *Proyeksi Denda Berjalan:* Estimasi jumlah denda yang akan diterima di hari esok dari buku-buku berstatus `'Terlambat'` yang belum dikembalikan (dihitung secara *real-time*: hari keterlambatan berjalan $\times$ Rp500).

### e. Laporan Keanggotaan & Preferensi
* **Isi Data:** Statistik demografi pengguna terdaftar.
* **Metrik Analisis:**
  1. *Akumulasi Pendaftaran:* Jumlah total baris pada `Tabel Pelajar` dan `Tabel Non Pelajar`.
  2. *Anggota Paling Aktif:* Pengunjung yang memiliki frekuensi peminjaman buku tertinggi.
  3. *Preferensi Pengunjung:* Pengelompokan tren `SubjekUtama` atau `SubjekTambahan` buku yang paling sering dipinjam oleh kelompok Pelajar dibandingkan kelompok Non-Pelajar.

---

## 2. Standardisasi Format Cetak Dokumen (PDF & Excel Layout)

Setiap laporan yang dipilih wajib menyediakan fungsi cetak fisik atau unduh digital dengan format dokumen resmi perpustakaan daerah yang baku:

1. **Kop Laporan Resmi:** Terletak di bagian paling atas dokumen, berisi logo daerah, nama instansi ("Pemerintah Kabupaten Sumbawa - Dinas Perpustakaan dan Kearsipan Kota Sumbawa"), alamat lengkap, dan garis pembatas tebal.
2. **Judul Laporan:** Menampilkan nama kluster laporan beserta filter rentang tanggalnya (Misal: *LAPORAN SIRKULASI PEMINJAMAN BUKU PERIODE 01 JUNI 2026 - 30 JUNI 2026*).
3. **Tanggal Laporan:** Tanggal dan waktu kapan dokumen tersebut dicetak oleh sistem.
4. **Isi Laporan:** Berupa tabel data terstruktur rapi dengan penomoran urut, penamaan kolom yang jelas, dan baris total akumulasi di bagian paling bawah tabel.
5. **Lembar Pengesahan (TTD):** Terletak di bagian kanan bawah halaman terakhir dokumen, berisi tempat dan tanggal penandatanganan, jabatan ("Kepala Perpustakaan Kota Sumbawa"), ruang tanda tangan fisik, serta Nama Lengkap dan NIP Pemilik/Pimpinan Perpustakaan.

---

## 3. Logika Query Gabungan (Laravel 13 Eloquent)

Karena data transaksi terpisah antara Pelajar dan Non-Pelajar atas permintaan *stakeholder*, sistem memanfaatkan metode penggabungan koleksi (*collection collection merging*) atau kueri `Union` untuk menyajikan data yang seragam tanpa mengorbankan performa server.

```php
// Contoh Query Menggabungkan Data Denda Pelajar & Non-Pelajar untuk Laporan Eksekutif
$dendaPelajar = DB::table('transaksi_pelajar')
    ->join('anggota_pelajar', 'transaksi_pelajar.NoAnggotaP', '=', 'anggota_pelajar.NoAnggotaP')
    ->select('anggota_pelajar.NamaAnggotaP as nama', 'transaksi_pelajar.Denda as denda', 'transaksi_pelajar.StatusBayarDenda as status');

$laporanDendaSistem = DB::table('transaksi_non_pelajar')
    ->join('anggota_non_pelajar', 'transaksi_non_pelajar.NoAnggotaN', '=', 'anggota_non_pelajar.NoAnggotaN')
    ->select('anggota_non_pelajar.NamaAnggotaN as nama', 'transaksi_non_pelajar.Denda as denda', 'transaksi_non_pelajar.StatusBayarDenda as status')
    ->union($dendaPelajar)
    ->get();