# To-Do List: Sistem Peminjaman Buku
Update Terakhir: Juni 2026
Komponen Utama: Laravel Controller, Validator, QR Code Generator Library (`simplesoftwareio/simple-qrcode`)

Dokumen ini berisi spesifikasi kebutuhan teknis dan alur kerja untuk Modul Peminjaman Buku pada Aplikasi Perpustakaan Kota Sumbawa. Fitur ini bersifat eksklusif dan hanya dapat diakses oleh pengguna dengan hak akses **Petugas** atau **SuperAdmin**.

---

## 1. Alur & Struktur Form Peminjaman (Petugas Interface)

Proses peminjaman diinisiasi oleh petugas melalui tombol aksi di Rak Buku atau menu navigasi internal `/admin/peminjaman/tambah`.

### a. Komponen Input Form Peminjaman
* **Pilihan Kategori Anggota:** Radio Button / Dropdown (`'Pelajar'` atau `'Non-Pelajar'`). Pilihan ini menentukan tabel mana yang akan dibaca oleh sistem.
* **Pencarian Anggota (Select2 / Autocomplete Ajax):** * Jika kategori Pelajar: Mencari berdasarkan `NIM_NIS` atau `NamaAnggotaP`.
  * Jika kategori Non-Pelajar: Mencari berdasarkan `NIK` atau `NamaAnggotaN`.
* **Pencarian Buku (Autocomplete Ajax):** Mencari berdasarkan `KodeBuku` atau `Judul`. Form ini dapat menambah hingga maksimal 2 buku sekaligus dalam satu transaksi pengajuan.

---

## 2. Logika Validasi Bisnis & Aturan Ketat (Server-Side)

Sebelum data disimpan ke database, Controller Laravel wajib mengeksekusi rangkaian validasi berlapis untuk menegakkan aturan perpustakaan secara ketat:

### a. Aturan Batas Maksimal Kuota Pinjam (Maksimal 2 Buku)
Sistem menghitung jumlah buku yang sedang dipinjam (belum dikembalikan) oleh anggota tersebut.
```php
// Contoh Logika Validasi Kuota Pelajar di Laravel Controller
$bukuDipinjam = TransaksiPelajar::where('NoAnggotaP', $request->NoAnggotaP)
    ->where('StatusTransaksi', 'Dipinjam')
    ->count();

if (($bukuDipinjam + count($request->buku_pilihan)) > 2) {
    return redirect()->back()->withErrors(['error' => 'Peminjaman Ditolak! Anggota telah melebihi batas maksimal kuota (Maksimal 2 Buku).']);
}