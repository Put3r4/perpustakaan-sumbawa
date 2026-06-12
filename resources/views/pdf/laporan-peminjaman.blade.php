<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman Buku</title>
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
        
        .periode {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
            background-color: #f5f5f5;
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
        
        .badge-terlambat {
            background-color: #ff0000;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-tepat {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
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
        <h3>LAPORAN PEMINJAMAN BUKU</h3>
    </div>

    <!-- PERIODE -->
    <div class="periode">
        <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($filter['tanggal_mulai'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filter['tanggal_akhir'])->format('d/m/Y') }}</p>
    </div>

    <!-- RINGKASAN -->
    <div class="summary-box">
        <p><strong>Total Transaksi:</strong> {{ number_format($summary['total_transaksi'], 0, ',', '.') }}</p>
        <p><strong>Total Terlambat:</strong> {{ number_format($summary['total_terlambat'], 0, ',', '.') }}</p>
        <p><strong>Total Tepat Waktu:</strong> {{ number_format($summary['total_tepat_waktu'], 0, ',', '.') }}</p>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No. Resi</th>
                <th width="10%">Tgl Pinjam</th>
                <th width="10%">Tgl Jatuh Tempo</th>
                <th width="23%">Judul Buku</th>
                <th width="15%">Nama Anggota</th>
                <th width="10%">Kategori</th>
                <th width="8%">Status</th>
                <th width="7%">Hari Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->resi }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->TglPinjam)->format('d/m/Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->TglJatuhTempo)->format('d/m/Y') }}</td>
                <td>{{ $item->buku->Judul ?? '-' }}</td>
                <td>{{ $item->anggotaPelajar->NamaLengkap ?? $item->anggotaNonPelajar->NamaLengkap ?? '-' }}</td>
                <td class="text-center">{{ $item->kategori_anggota }}</td>
                <td class="text-center">
                    @if($item->is_overdue)
                        <span class="badge-terlambat">TERLAMBAT</span>
                    @else
                        <span class="badge-tepat">DIPINJAM</span>
                    @endif
                </td>
                <td class="text-center">{{ $item->hari_terlambat }} hari</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data transaksi</td>
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
