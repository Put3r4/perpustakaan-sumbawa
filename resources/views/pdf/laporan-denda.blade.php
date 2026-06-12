<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Denda</title>
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
            background-color: #fffacd;
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
        
        .total-row {
            background-color: #d9e1f2 !important;
            font-weight: bold;
        }
        
        .badge-lunas {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-belum {
            background-color: #ff0000;
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
        <h3>LAPORAN DENDA KETERLAMBATAN PENGEMBALIAN BUKU</h3>
    </div>

    <!-- PERIODE -->
    <div class="periode">
        <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($filter['tanggal_mulai'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($filter['tanggal_akhir'])->format('d/m/Y') }}</p>
    </div>

    <!-- RINGKASAN KEUANGAN -->
    <div class="summary-box">
        <p><strong>Total Transaksi Denda:</strong> {{ number_format($summary['total_transaksi'], 0, ',', '.') }}</p>
        <p><strong>Total Denda Lunas:</strong> Rp {{ number_format($summary['total_denda_lunas'], 0, ',', '.') }}</p>
        <p><strong>Total Piutang Denda (Belum Lunas):</strong> Rp {{ number_format($summary['total_piutang'], 0, ',', '.') }}</p>
        <p><strong>TOTAL DENDA KESELURUHAN:</strong> Rp {{ number_format($summary['total_denda_keseluruhan'], 0, ',', '.') }}</p>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">No. Resi</th>
                <th width="8%">Tgl Pinjam</th>
                <th width="8%">Tgl J. Tempo</th>
                <th width="8%">Tgl Kembali</th>
                <th width="18%">Judul Buku</th>
                <th width="14%">Nama Anggota</th>
                <th width="9%">Kategori</th>
                <th width="6%">Hari Terlambat</th>
                <th width="8%">Denda (Rp)</th>
                <th width="7%">Status Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->resi }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->TglPinjam)->format('d/m/Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->TglJatuhTempo)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $item->TglKembali ? \Carbon\Carbon::parse($item->TglKembali)->format('d/m/Y') : 'Belum' }}</td>
                <td>{{ $item->buku->Judul ?? '-' }}</td>
                <td>{{ $item->anggotaPelajar->NamaLengkap ?? $item->anggotaNonPelajar->NamaLengkap ?? '-' }}</td>
                <td class="text-center">{{ $item->kategori_anggota }}</td>
                <td class="text-center">{{ $item->hari_terlambat }} hari</td>
                <td class="text-right">{{ number_format($item->denda_realtime, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($item->StatusBayarDenda === 'Lunas')
                        <span class="badge-lunas">LUNAS</span>
                    @else
                        <span class="badge-belum">BELUM</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">Tidak ada data transaksi denda</td>
            </tr>
            @endforelse
            
            @if($transaksi->count() > 0)
            <tr class="total-row">
                <td colspan="9" class="text-right"><strong>TOTAL DENDA KESELURUHAN:</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_denda_keseluruhan'], 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
            <tr class="total-row">
                <td colspan="9" class="text-right"><strong>DENDA LUNAS:</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_denda_lunas'], 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
            <tr class="total-row">
                <td colspan="9" class="text-right"><strong>PIUTANG (BELUM LUNAS):</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_piutang'], 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
            @endif
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
