# To-Do List: Sistem Pengembalian Buku
Update Terakhir: Juni 2026
Komponen Utama: Laravel Controller, Carbon Date Manipulation, QR Scanner Interface (HTML5-QRCode Library)

Dokumen ini berisi spesifikasi kebutuhan teknis dan alur fungsional untuk Modul Pengembalian Buku pada Aplikasi Perpustakaan Kota Sumbawa. Sistem ini mendasari proses pemindaian QR Code resi, kalkulasi denda, dan pencetakan bukti pengembalian.

---

## 1. Alur & Struktur Antarmuka Pengembalian (Petugas Interface)

Fitur ini hanya dapat diakses oleh Petugas/Admin melalui rute `/admin/pengembalian`.

### a. Mode Pemindaian (Scan QR Code Resi)
* **Komponen:** Kamera Scanner terintegrasi pada halaman web menggunakan library JavaScript `html5-qrcode`.
* **Cara Kerja:** Petugas mengarahkan kamera ke QR Code Resi Peminjaman yang dibawa oleh pengunjung. Kamera akan membaca kode acak 36 karakter (UUID) yang merupakan nilai dari `NoPinjamP` atau `NoPinjamN`.
* **Fitur Cadangan (Fallback):** Jika kamera bermasalah, disediakan kolom input manual untuk mengetikkan string UUID resi secara langsung.

### b. Form Review Pengembalian (Otomatis Terisi setelah Scan)
Setelah UUID berhasil dicocokkan oleh server, sistem akan memuat data sirkulasi ke dalam form review:
* **Identitas Anggota:** Menampilkan Nama, NIM/NIS, atau NIK secara otomatis sesuai kategori tabel relasinya.
* **Informasi Buku:** Menampilkan Judul Buku, Penerbit, dan `KodeBuku`.
* **Kronologi Waktu:** Menampilkan `TglPinjam` dan `TglJatuhTempo`.
* **Waktu Pengembalian:** Kolom tanggal hari ini (Otomatis terkunci/Read-only).
* **Total Denda:** Komponen angka yang dihitung otomatis oleh sistem (*real-time computation*).

---

## 2. Logika Hitung Denda Keterlambatan (Server-Side Carbon)

Sistem akan membandingkan tanggal hari ini (`TglKembali`) dengan `TglJatuhTempo` dari database. Aturan yang berlaku di Perpustakaan Kota Sumbawa adalah denda **Rp500 per hari** untuk setiap buku yang terlambat dikembalikan.

```php
// Logika Perhitungan Denda di PengembalianController Laravel 13
$tglJatuhTempo = Carbon::parse($transaksi->TglJatuhTempo);
$tglKembali = Carbon::now(); // Hari ini

$totalDenda = 0;
$statusTransaksi = 'Dikembalikan';

if ($tglKembali->gt($tglJatuhTempo)) {
    // Menghitung selisih hari keterlambatan
    $selisihHari = $tglKembali->diffInDays($tglJatuhTempo); 
    
    // Rumus denda aturan perpustakaan
    $totalDenda = $selisihHari * 500; 
    $statusTransaksi = 'Terlambat';
}

// Update baris data transaksi yang sama
$transaksi->update([
    'TglKembali' => $tglKembali->format('Y-m-d'),
    'Denda' => $totalDenda,
    'KodePetugasKembali' => Auth::user()->KodePetugas,
    'StatusTransaksi' => $statusTransaksi,
    'StatusBayarDenda' => $totalDenda > 0 ? 'Belum_Lunas' : 'Tidak_Ada'
]);