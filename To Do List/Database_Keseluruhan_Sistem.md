# To-Do List: Database Keseluruhan Sistem
Update Terakhir: Juni 2026
Target Framework: Laravel 13 & MySQL / MariaDB

Dokumen ini berisi spesifikasi teknis migrasi database untuk Aplikasi Perpustakaan Kota Sumbawa. Seluruh nama field disesuaikan 100% dengan dokumen Jurnal (Tabel 4.2 - 4.9) dengan penyesuaian tipe data modern untuk mendukung sistem QR Code, sistem Keamanan 2FA, dan Tracking Analitik Website.

---

## 1. Skema Tabel Master (Entitas Utama)

### a. Tabel Petugas (Sesuai Tabel 4.5 Jurnal)
Tabel untuk menyimpan data autentikasi internal (SuperAdmin dan Petugas).
* **Nama Tabel di Laravel:** `petugas`
* **Struktur Kolom:**
  * `KodePetugas` (Int, 10, Primary Key, Auto Increment)
  * `NamaPetugas` (Varchar, 20)
  * `Jabatan` (Varchar, 15)
  * `HakAkses` (Enum: `'SuperAdmin'`, `'Petugas'`)
  * `Password` (Varchar, 255) -> *Dinaikkan untuk enkripsi Bcrypt Laravel*
  * `Email` (Varchar, 255, Unique) -> *Kebutuhan sistem login modern*
  * `two_factor_secret` (Text, Nullable) -> *Fitur keamanan 2-Step Verification Laravel 13*

### b. Tabel Anggota Pelajar (Sesuai Tabel 4.2 Jurnal)
Tabel untuk meregistrasikan akun kelompok Pelajar/Mahasiswa dengan atribut data spesifik sekolah.
* **Nama Tabel di Laravel:** `anggota_pelajar`
* **Struktur Kolom:**
  * `NoAnggotaP` (Int, 11, Primary Key, Auto Increment)
  * `NIM_NIS` (Varchar, 15, Unique) -> *Penyesuaian format penulisan karakter*
  * `NamaAnggotaP` (Varchar, 25)
  * `AsalSekolah` (Varchar, 20)
  * `TTL` (Varchar, 50)
  * `Alamat` (Varchar, 100)
  * `KodePos` (Int, 5)
  * `NoTelp1` (Varchar, 15) -> *Diubah menjadi string agar angka 0 di depan tidak hilang*
  * `NoTelp2` (Varchar, 15, Nullable)
  * `TglDaftar` (Date)
  * `NamaOrtu` (Varchar, 25)
  * `AlamatOrtu` (Varchar, 100)
  * `NoTelpOrtu` (Varchar, 15)
  * `Email` (Varchar, 255, Unique) -> *Untuk login Pelajar di Web*
  * `Password` (Varchar, 255) -> *Untuk keamanan login web*
  * `two_factor_secret` (Text, Nullable)

### c. Tabel Anggota Non Pelajar (Sesuai Tabel 4.3 Jurnal)
Tabel untuk meregistrasikan akun kelompok umum / masyarakat Sumbawa.
* **Nama Tabel di Laravel:** `anggota_non_pelajar`
* **Struktur Kolom:**
  * `NoAnggotaN` (Int, 11, Primary Key, Auto Increment)
  * `NIK` (Varchar, 16, Unique) -> *Menggunakan Varchar untuk mencegah batasan nilai Int32*
  * `NamaAnggotaN` (Varchar, 20)
  * `Pekerjaan` (Varchar, 25)
  * `TTL` (Varchar, 50)
  * `Alamat` (Varchar, 100)
  * `KodePos` (Int, 5)
  * `NoTelp1` (Varchar, 15)
  * `NoTelp2` (Varchar, 15, Nullable)
  * `TglDaftar` (Date)
  * `Email` (Varchar, 255, Unique)
  * `Password` (Varchar, 255)
  * `two_factor_secret` (Text, Nullable)

### d. Tabel Buku (Sesuai Tabel 4.4 Jurnal)
Tabel penyimpanan katalog buku fisik perpustakaan beserta pelacak performa digital.
* **Nama Tabel di Laravel:** `buku`
* **Struktur Kolom:**
  * `KodeBuku` (Int, 10, Primary Key, Auto Increment)
  * `NoUdc` (Int, 10)
  * `NoReg` (Varchar, 10)
  * `Judul` (Varchar, 100) -> *Dinaikkan dari 35 agar judul panjang tidak terpotong*
  * `Penerbit` (Varchar, 50)
  * `Pengarang` (Varchar, 50)
  * `TahunTerbit` (Integer, 4)
  * `KotaTerbit` (Varchar, 20)
  * `Bahasa` (Varchar, 10)
  * `Edisi` (Varchar, 15)
  * `Deskripsi` (Text) -> *Diubah ke tipe Text agar mampu menampung ringkasan sinopsis*
  * `Isbn` (Varchar, 20)
  * `JumEksemplar` (Int, 7)
  * `SubjekUtama` (Varchar, 20)
  * `SubjekTambahan` (Varchar, 25)
  * `views_count` (Int, 11, Default: 0) -> *Metrik pelacakan: bertambah +1 jika card buku dilihat minimal 1 menit*

---

## 2. Skema Tabel Transaksi Sirkulasi Terintegrasi

Mekanisme ini menggabungkan pencatatan Pinjam dan Kembali menjadi satu siklus pelaporan terpadu guna menghindari penurunan performa kueri (*slow query*) pada database. Primary key diubah menggunakan string format UUID agar aman ditransformasikan menjadi bentuk gambar QR Code Resi.

### a. Tabel Transaksi Pelajar (Kombinasi Tabel 4.6 & 4.8 Jurnal)
* **Nama Tabel di Laravel:** `transaksi_pelajar`
* **Struktur Kolom:**
  * `NoPinjamP` (Char, 36, Primary Key) -> *Menggunakan UUID untuk generate unik QR Code*
  * `TglPinjam` (Date)
  * `TglJatuhTempo` (Date) -> *Logika sistem: TglPinjam + 7 Hari*
  * `TglKembali` (Date, Nullable) -> *Default null sewaktu pinjam, terisi otomatis saat scan pengembalian*
  * `NoAnggotaP` (Int, 11, Foreign Key -> `anggota_pelajar.NoAnggotaP`)
  * `KodeBuku` (Int, 10, Foreign Key -> `buku.KodeBuku`)
  * `KodePetugas` (Int, 10, Foreign Key -> `petugas.KodePetugas`) -> *Petugas peminjaman*
  * `KodePetugasKembali` (Int, 10, Foreign Key -> `petugas.KodePetugas`, Nullable) -> *Petugas pengembalian*
  * `Denda` (Integer, Default: 0) -> *Otomatis terhitung Rp500/hari saat TglKembali melewati TglJatuhTempo*
  * `StatusBayarDenda` (Enum: `'Lunas'`, `'Belum_Lunas'`, `'Tidak_Ada'`, Default: `'Tidak_Ada'`)
  * `StatusTransaksi` (Enum: `'Dipinjam'`, `'Dikembalikan'`, `'Terlambat'`, Default: `'Dipinjam'`)

### b. Tabel Transaksi Non Pelajar (Kombinasi Tabel 4.7 & 4.9 Jurnal)
* **Nama Tabel di Laravel:** `transaksi_non_pelajar`
* **Struktur Kolom:**
  * `NoPinjamN` (Char, 36, Primary Key) -> *Menggunakan UUID untuk generate unik QR Code*
  * `TglPinjam` (Date)
  * `TglJatuhTempo` (Date)
  * `TglKembali` (Date, Nullable)
  * `NoAnggotaN` (Int, 11, Foreign Key -> `anggota_non_pelajar.NoAnggotaN`)
  * `KodeBuku` (Int, 10, Foreign Key -> `buku.KodeBuku`)
  * `KodePetugas` (Int, 10, Foreign Key -> `petugas.KodePetugas`)
  * `KodePetugasKembali` (Int, 10, Foreign Key -> `petugas.KodePetugas`, Nullable)
  * `Denda` (Integer, Default: 0)
  * `StatusBayarDenda` (Enum: `'Lunas'`, `'Belum_Lunas'`, `'Tidak_Ada'`, Default: `'Tidak_Ada'`)
  * `StatusTransaksi` (Enum: `'Dipinjam'`, `'Dikembalikan'`, `'Terlambat'`, Default: `'Dipinjam'`)

---

## 3. Skema Tabel Tambahan (Dashboard & Analitika)

### a. Tabel Kunjungan
Berfungsi menyuplai data monitoring kunjungan fisik secara real-time ke diagram batang mingguan pada Dashboard.
* **Nama Tabel di Laravel:** `kunjungan`
* **Struktur Kolom:**
  * `IdKunjungan` (Int, 11, Primary Key, Auto Increment)
  * `TipePengunjung` (Enum: `'Pelajar'`, `'Non_Pelajar'`, `'Umum'`)
  * `IdentitasID` (Varchar, 20, Nullable) -> *Berisi NIM_NIS atau NIK jika anggota resmi terdaftar*
  * `NamaPengunjung` (Varchar, 50) -> *Input manual jika umum, otomatis terisi via sistem jika scan kartu anggota*
  * `WaktuMasuk` (Timestamp) -> *Default: CURRENT_TIMESTAMP*

### b. Tabel Jadwal Piket Petugas
Digunakan untuk melacak dan menampilkan profil petugas yang berjaga secara dinamis pada halaman Dashboard awal.
* **Nama Tabel di Laravel:** `jadwal_piket`
* **Struktur Kolom:**
  * `IdPiket` (Int, 11, Primary Key, Auto Increment)
  * `KodePetugas` (Int, 10, Foreign Key -> `petugas.KodePetugas`)
  * `HariPiket` (Enum: `'Senin'`, `'Selasa'`, `'Rabu'`, `'Kamis'`, `'Jumat'`, `'Sabtu'`, `'Minggu'`)

---

## 4. Aturan Integritas Data & Relasi Kontrol (Constraint)
1. **On Delete Restrict:** Jika data `Buku` atau `Anggota` masih memiliki keterikatan catatan di `Tabel Transaksi`, maka data master tersebut tidak boleh dihapus dari sistem demi menjaga keaslian riwayat laporan.
2. **UUID Generation:** Kolom `NoPinjamP` dan `NoPinjamN` tidak boleh diisi manual lewat form, melainkan wajib diinjeksi secara otomatis oleh model Laravel menggunakan fungsi `Str::uuid()` saat *event* data baru dibuat.