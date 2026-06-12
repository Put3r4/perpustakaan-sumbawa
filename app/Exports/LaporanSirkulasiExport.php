<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanSirkulasiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $data;

    protected string $reportType;

    protected array $filter;

    /**
     * @param  array<string, mixed>  $filter
     */
    public function __construct(Collection $data, string $reportType, array $filter = [])
    {
        $this->data = $data;
        $this->reportType = $reportType;
        $this->filter = $filter;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        return $this->data;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return match ($this->reportType) {
            'peminjaman' => [
                'No. Resi',
                'Tanggal Pinjam',
                'Tanggal Jatuh Tempo',
                'Kode Buku',
                'Judul Buku',
                'Nomor Anggota',
                'Nama Anggota',
                'Kategori Anggota',
                'Status',
                'Terlambat',
                'Hari Terlambat',
            ],
            'pengembalian' => [
                'No. Resi',
                'Tanggal Pinjam',
                'Tanggal Jatuh Tempo',
                'Tanggal Kembali',
                'Kode Buku',
                'Judul Buku',
                'Nomor Anggota',
                'Nama Anggota',
                'Kategori Anggota',
                'Denda (Rp)',
                'Status Bayar',
            ],
            'denda' => [
                'No. Resi',
                'Tanggal Pinjam',
                'Tanggal Jatuh Tempo',
                'Tanggal Kembali',
                'Kode Buku',
                'Judul Buku',
                'Nomor Anggota',
                'Nama Anggota',
                'Kategori Anggota',
                'Hari Terlambat',
                'Denda (Rp)',
                'Status Bayar',
            ],
            'buku' => [
                'Kode Buku',
                'Judul Buku',
                'Pengarang',
                'Subjek Utama',
                'Total Peminjaman',
                'Jumlah Views',
            ],
            default => ['Data'],
        };
    }

    /**
     * @param  mixed  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return match ($this->reportType) {
            'peminjaman' => [
                $row->resi ?? $row->NoPinjamP ?? $row->NoPinjamN ?? '-',
                $row->TglPinjam ?? '-',
                $row->TglJatuhTempo ?? '-',
                $row->KodeBuku ?? '-',
                $row->buku->Judul ?? '-',
                $row->nomor_anggota ?? $row->NoAnggotaP ?? $row->NoAnggotaN ?? '-',
                $row->anggotaPelajar->NamaLengkap ?? $row->anggotaNonPelajar->NamaLengkap ?? '-',
                $row->kategori_anggota ?? '-',
                $row->StatusTransaksi ?? '-',
                $row->is_overdue ? 'Ya' : 'Tidak',
                $row->hari_terlambat ?? 0,
            ],
            'pengembalian' => [
                $row->resi ?? $row->NoPinjamP ?? $row->NoPinjamN ?? '-',
                $row->TglPinjam ?? '-',
                $row->TglJatuhTempo ?? '-',
                $row->TglKembali ?? '-',
                $row->KodeBuku ?? '-',
                $row->buku->Judul ?? '-',
                $row->nomor_anggota ?? $row->NoAnggotaP ?? $row->NoAnggotaN ?? '-',
                $row->anggotaPelajar->NamaLengkap ?? $row->anggotaNonPelajar->NamaLengkap ?? '-',
                $row->kategori_anggota ?? '-',
                number_format((float) ($row->Denda ?? 0), 0, ',', '.'),
                $row->StatusBayarDenda ?? '-',
            ],
            'denda' => [
                $row->resi ?? $row->NoPinjamP ?? $row->NoPinjamN ?? '-',
                $row->TglPinjam ?? '-',
                $row->TglJatuhTempo ?? '-',
                $row->TglKembali ?? '-',
                $row->KodeBuku ?? '-',
                $row->buku->Judul ?? '-',
                $row->nomor_anggota ?? $row->NoAnggotaP ?? $row->NoAnggotaN ?? '-',
                $row->anggotaPelajar->NamaLengkap ?? $row->anggotaNonPelajar->NamaLengkap ?? '-',
                $row->kategori_anggota ?? '-',
                $row->hari_terlambat ?? 0,
                number_format((float) ($row->denda_realtime ?? $row->Denda ?? 0), 0, ',', '.'),
                $row->StatusBayarDenda ?? '-',
            ],
            'buku' => [
                $row['KodeBuku'] ?? $row->KodeBuku ?? '-',
                $row['Judul'] ?? $row->Judul ?? '-',
                $row['Pengarang'] ?? $row->Pengarang ?? '-',
                $row['SubjekUtama'] ?? $row->SubjekUtama ?? '-',
                $row['total_peminjaman'] ?? 0,
                $row['views_count'] ?? $row->views_count ?? 0,
            ],
            default => [$row],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 1;

        return [
            // Header styling
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
            // Data rows styling
            "A2:Z{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return match ($this->reportType) {
            'peminjaman' => 'Laporan Peminjaman',
            'pengembalian' => 'Laporan Pengembalian',
            'denda' => 'Laporan Denda',
            'buku' => 'Laporan Analitik Buku',
            'anggota' => 'Laporan Anggota',
            default => 'Laporan Sirkulasi',
        };
    }
}
