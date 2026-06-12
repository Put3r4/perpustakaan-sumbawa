<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analitik Buku</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .kop-surat h1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        .kop-surat h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .kop-surat p {
            font-size: 11px;
            margin: 2px 0;
        }
        
        .judul-laporan {
            text-align: center;
            margin: 20px 0;
        }
        
        .judul-laporan h3 {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            background-color: #e9ecef;
            padding: 8px;
            border-left: 4px solid #4472C4;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            padding: 8px 5px;
            text-align: left;
            border: 1px solid #000;
            font-size: 10px;
        }
        
        table td {
            padding: 6px 5px;
            border: 1px solid #000;
            font-size: 10px;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary-box {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #000;
            background-color: #e3f2fd;
        }
        
        .summary-box p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .summary-box strong {
            font-weight: bold;
        }
        
        .ttd-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .ttd-box {
            display: inline-block;
            text-align: center;
            min-width: 250px;
        }
        
        .ttd-tempat {
            margin-bottom: 5px;
        }
        
        .ttd-ruang {
            margin: 60px 0 10px 0;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT RESMI -->
    <div class="kop-surat">
        <h1>PEMERINTAH KABUPATEN SUMBAWA</h1>
        <h2>DINAS PERPUSTAKAAN DAN KEARSIPAN KOTA SUMBAWA</h2>
        <p>Jl. Garuda No. 1, Sumbawa Besar, Nusa Tenggara Barat 84311</p>
        <p>Telp: (0371) 621234 | Email: perpustakaan@sumbawakab.go.id</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="judul-laporan">
        <h3>LAPORAN ANALITIK BUKU</h3>
    </div>

    <!-- RINGKASAN -->
    <div class="summary-box">
        <p><strong>Total Buku Koleksi:</strong> {{ number_format($summary['total_buku'], 0, ',', '.') }} judul</p>
        <p><strong>Total Transaksi Peminjaman:</strong> {{ number_format($summary['total_peminjaman'], 0, ',', '.') }}</p>
    </div>

    <!-- SECTION 1: BUKU TERPOPULER -->
    <div class="section-title">
        📈 BUKU PALING BANYAK DIPINJAM (TOP 20)
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Kode Buku</th>
                <th width="35%">Judul Buku</th>
                <th width="23%">Pengarang</th>
                <th width="15%">Subjek Utama</th>
                <th width="10%">Total Peminjaman</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bukuTerpopuler as $index => $buku)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $buku['KodeBuku'] }}</td>
                <td>{{ $buku['Judul'] }}</td>
                <td>{{ $buku['Pengarang'] }}</td>
                <td>{{ $buku['SubjekUtama'] }}</td>
                <td class="text-center"><strong>{{ number_format($buku['total_peminjaman'], 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- KOP SURAT HALAMAN 2 -->
    <div class="kop-surat">
        <h1>PEMERINTAH KABUPATEN SUMBAWA</h1>
        <h2>DINAS PERPUSTAKAAN DAN KEARSIPAN KOTA SUMBAWA</h2>
        <p>Jl. Garuda No. 1, Sumbawa Besar, Nusa Tenggara Barat 84311</p>
        <p>Telp: (0371) 621234 | Email: perpustakaan@sumbawakab.go.id</p>
    </div>

    <!-- SECTION 2: BUKU PALING SEDIKIT DIPINJAM -->
    <div class="section-title">
        📉 BUKU PALING SEDIKIT DIPINJAM (BOTTOM 20)
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Kode Buku</th>
                <th width="35%">Judul Buku</th>
                <th width="23%">Pengarang</th>
                <th width="15%">Subjek Utama</th>
                <th width="10%">Total Peminjaman</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bukuJarangDipinjam as $index => $buku)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $buku['KodeBuku'] }}</td>
                <td>{{ $buku['Judul'] }}</td>
                <td>{{ $buku['Pengarang'] }}</td>
                <td>{{ $buku['SubjekUtama'] }}</td>
                <td class="text-center">{{ number_format($buku['total_peminjaman'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- KOP SURAT HALAMAN 3 -->
    <div class="kop-surat">
        <h1>PEMERINTAH KABUPATEN SUMBAWA</h1>
        <h2>DINAS PERPUSTAKAAN DAN KEARSIPAN KOTA SUMBAWA</h2>
        <p>Jl. Garuda No. 1, Sumbawa Besar, Nusa Tenggara Barat 84311</p>
        <p>Telp: (0371) 621234 | Email: perpustakaan@sumbawakab.go.id</p>
    </div>

    <!-- SECTION 3: BUKU PALING BANYAK DILIHAT -->
    <div class="section-title">
        👁️ BUKU PALING BANYAK DILIHAT (TOP 20 VIEWS)
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Kode Buku</th>
                <th width="38%">Judul Buku</th>
                <th width="25%">Pengarang</th>
                <th width="15%">Subjek Utama</th>
                <th width="10%">Total Views</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bukuPalingDilihat as $index => $buku)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $buku->KodeBuku }}</td>
                <td>{{ $buku->Judul }}</td>
                <td>{{ $buku->Pengarang }}</td>
                <td>{{ $buku->SubjekUtama }}</td>
                <td class="text-center"><strong>{{ number_format($buku->views_count, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="ttd-section">
        <div class="ttd-box">
            <div class="ttd-tempat">
                Sumbawa Besar, {{ $tanggal_cetak }}
            </div>
            <div>
                <strong>Kepala Perpustakaan</strong><br>
                <strong>Kota Sumbawa</strong>
            </div>
            <div class="ttd-ruang">
                <!-- Ruang untuk tanda tangan basah -->
            </div>
            <div class="ttd-nama">
                <u>Drs. H. Ahmad Syarifuddin, M.Si</u>
            </div>
            <div>
                NIP. 197205121995031004
            </div>
        </div>
    </div>
</body>
</html>
