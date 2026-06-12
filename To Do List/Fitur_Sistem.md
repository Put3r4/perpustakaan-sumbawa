# To-Do List: Fitur Sistem (Keamanan, Autentikasi, & Otorisasi)
Update Terakhir: Juni 2026
Target Lingkungan: Laravel 13 Core, Middleware, Laravel Policies, Laravel Fortify / 2FA Engine

Dokumen ini berisi spesifikasi teknis menyeluruh mengenai sistem keamanan, manajemen hak akses, alur registrasi bertingkat, otorisasi berlapis, dan konfigurasi verifikasi 2-arah pada Aplikasi Perpustakaan Kota Sumbawa. Dokumen ini berfungsi sebagai acuan mutlak untuk menutup segala celah keamanan bypass URL pada aplikasi.

---

## 1. Matriks Akses Pengguna (Role-Based Access Control)

Sistem membagi tingkat kewenangan pengguna menjadi 3 peran dengan pembatasan hak akses yang tegas pada level antarmuka (UI) maupun basis data:

| Peran (Role) | Hak Akses Fitur Buku | Hak Akses Peminjaman | Hak Akses Pengembalian | Hak Akses Laporan |
| :--- | :---: | :---: | :---: | :---: |
| **Petugas / SuperAdmin** | **CRUD** (Penuh) | **CRUD** (Penuh) | **CRUD** (Penuh) | **CRUD** (Penuh) |
| **Pelajar** | **Read-Only** (Lihat) | **Read-Only** (Riwayat Mandiri) | **Read-Only** (Riwayat Mandiri) | **No Access** (Blokir Total) |
| **Non-Pelajar** | **Read-Only** (Lihat) | **Read-Only** (Riwayat Mandiri) | **Read-Only** (Riwayat Mandiri) | **No Access** (Blokir Total) |

### Aturan Utama Tampilan:
1. Jika pengguna tidak memiliki hak akses CRUD (Pelajar dan Non-Pelajar), maka aplikasi secara otomatis **tidak akan merender atau menampilkan** tombol aksi seperti "Tambah Buku", "Edit Buku", "Proses Peminjaman", dan "Proses Pengembalian" pada halaman browser mereka.
2. Menu navigasi "Laporan" hanya akan muncul jika guard yang aktif mendeteksi akun Petugas atau SuperAdmin.

---

## 2. Alur Registrasi Akun Berorientasi Kategori & Proteksi Injeksi

Formulir registrasi dirancang secara dinamis di halaman depan khusus untuk pengunjung mandiri (Pelajar dan Non-Pelajar). 

### a. Urutan Langkah Pendaftaran (User Flow)
1. **Akses Register:** Pengunjung mengklik tombol "Daftar Akun" di halaman Dashboard awal.
2. **Pemilihan Kategori:** Pengunjung dihadapkan pada opsi wajib: *"Mendaftar sebagai apa?"* (Pilihan: **Pelajar** atau **Non-Pelajar**).
3. **Formulir Dinamis:**
   * Jika memilih **Pelajar**, sistem menampilkan kolom input: `NIM_NIS`, `NamaAnggotaP`, `AsalSekolah`, `TTL`, `Alamat`, `KodePos`, `NoTelp1`, `NamaOrtu`, `AlamatOrtu`, `NoTelpOrtu`, `Email`, dan `Password`.
   * Jika memilih **Non-Pelajar**, sistem menampilkan kolom input: `NIK`, `NamaAnggotaN`, `Pekerjaan`, `TTL`, `Alamat`, `KodePos`, `NoTelp1`, `Email`, dan `Password`.
4. **Penyimpanan:** Data divalidasi dan disimpan ke tabel masing-masing dengan `status_akun = 'Aktif'`.

### b. Mekanisme Proteksi Injeksi Akun SuperAdmin/Petugas
Untuk mencegah peretas membuat akun petugas secara ilegal lewat manipulasi data request formulir publik (Mass Assignment Injection), sistem menerapkan aturan berikut:
1. **Isolasi Formulir:** Jalur *Controller* registrasi publik sama sekali tidak dihubungkan dengan model atau tabel `Petugas`.
2. **Kunci Server-Side (Form Request):** Sistem menggunakan Laravel Form Request untuk menyaring input data secara ketat. Parameter `HakAkses` atau `role` dibuang dari data request sebelum query eksekusi dijalankan.
3. **Jalur Injeksi Internal (Seeder):** Pembuatan akun SuperAdmin dan Petugas tidak memiliki antarmuka publik, melainkan wajib dilakukan dari backend oleh sistem administrator via terminal menggunakan **Database Seeder** atau **Artisan Command Custom**.
```bash
   php artisan library:create-petugas --name="Admin Sumbawa" --email="admin@sumbawa.go.id" --role="SuperAdmin"