<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanPpdbSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $title;
    protected $stats;
    protected $selectedTahun;
    protected $selectedJalur;
    protected $selectedGelombang;
    protected $sekolah;
    protected $section;

    protected $currentRow = 1;
    protected $headerRows = [];
    protected $sectionHeaderRows = [];
    protected $totalRows = [];

    public function __construct($title, $stats, $selectedTahun, $selectedJalur, $selectedGelombang, $sekolah, $section)
    {
        $this->title = $title;
        $this->stats = $stats;
        $this->selectedTahun = $selectedTahun;
        $this->selectedJalur = $selectedJalur;
        $this->selectedGelombang = $selectedGelombang;
        $this->sekolah = $sekolah;
        $this->section = $section;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $rows = [];

        // Header
        $sekolahNama = $this->sekolah?->nama_sekolah ?? 'SEKOLAH';
        $rows[] = [strtoupper($sekolahNama)];
        $rows[] = ['LAPORAN PENERIMAAN PESERTA DIDIK BARU (PPDB)'];
        $rows[] = ['Tahun Pelajaran: ' . ($this->selectedTahun?->nama ?? '-')];

        $filter = 'Jalur: ' . ($this->selectedJalur?->nama ?? 'Semua Jalur') . '  ';
        $filter .= 'Gelombang: ' . ($this->selectedGelombang?->nama ?? 'Semua Gelombang');
        $rows[] = [$filter];

        $rows[] = ['Dicetak: ' . now()->translatedFormat('d F Y H:i') . ' WIB'];
        $rows[] = ['']; // blank row
        $this->currentRow = 7;

        if ($this->section === 'ringkasan') {
            $rows = array_merge($rows, $this->buildRingkasan());
        } elseif ($this->section === 'kelulusan') {
            $rows = array_merge($rows, $this->buildKelulusan());
        } elseif ($this->section === 'sebaran_sekolah') {
            $rows = array_merge($rows, $this->buildSebaranSekolah());
        } else {
            $rows = array_merge($rows, $this->buildSectionDetail($this->stats[$this->section] ?? [], $this->title));
        }

        return $rows;
    }

    private function buildRingkasan(): array
    {
        $rows = [];

        // -- Ringkasan Umum --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['RINGKASAN STATISTIK PPDB'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Uraian', 'Jumlah'];
        $this->currentRow++;

        $items = [
            ['Total Pendaftar', $this->stats['total']],
            ['Mendapat Nomor Tes', $this->stats['dapat_nomor_tes']],
            ['Tidak Mendapat Nomor Tes', $this->stats['tidak_dapat_nomor_tes']],
            ['Sudah Finalisasi', $this->stats['finalisasi']],
            ['Mengikuti Tes (CBT/TBQ)', $this->stats['ikut_tes']],
            ['Ikut TBQ', $this->stats['ikut_tbq']],
            ['Ikut CBT', $this->stats['ikut_cbt']],
            ['Lulus', $this->stats['lulus_total']],
            ['Tidak Lulus', $this->stats['tidak_lulus_total']],
            ['Cadangan', $this->stats['cadangan_total']],
        ];

        foreach ($items as $item) {
            $rows[] = $item;
            $this->currentRow++;
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Per Jalur --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['STATISTIK PER JALUR PENDAFTARAN'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Jalur', 'Total', 'Laki-laki', 'Perempuan', 'Finalisasi', 'Nomor Tes'];
        $this->currentRow++;

        foreach ($this->stats['per_jalur'] as $nama => $data) {
            $rows[] = [$nama, $data['total'], $data['laki_laki'], $data['perempuan'], $data['finalisasi'], $data['nomor_tes']];
            $this->currentRow++;
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Per Gelombang --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['STATISTIK PER GELOMBANG'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Gelombang', 'Total', 'Laki-laki', 'Perempuan'];
        $this->currentRow++;

        foreach ($this->stats['per_gelombang'] as $nama => $data) {
            $rows[] = [$nama, $data['total'], $data['laki_laki'], $data['perempuan']];
            $this->currentRow++;
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Status Verifikasi --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['STATUS VERIFIKASI'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Status', 'Jumlah'];
        $this->currentRow++;

        $rows[] = ['Pending', $this->stats['status_verifikasi']['pending']];
        $rows[] = ['Terverifikasi', $this->stats['status_verifikasi']['verified']];
        $rows[] = ['Ditolak', $this->stats['status_verifikasi']['rejected']];
        $rows[] = ['Perlu Revisi', $this->stats['status_verifikasi']['revisi']];
        $this->currentRow += 4;

        $rows[] = [''];
        $this->currentRow++;

        // -- Status Admisi --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['STATUS ADMISI'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Status', 'Jumlah'];
        $this->currentRow++;

        $rows[] = ['Diterima', $this->stats['status_admisi']['diterima']];
        $rows[] = ['Cadangan', $this->stats['status_admisi']['cadangan']];
        $rows[] = ['Ditolak', $this->stats['status_admisi']['ditolak']];
        $rows[] = ['Pending', $this->stats['status_admisi']['pending']];
        $this->currentRow += 4;

        // -- Sebaran Sekolah Asal Top 20 --
        $sebaran = $this->stats['sebaran_sekolah'] ?? [];
        if (count($sebaran) > 0) {
            $rows[] = [''];
            $this->currentRow++;

            $this->sectionHeaderRows[] = $this->currentRow;
            $rows[] = ['SEBARAN SEKOLAH ASAL (TOP 20)'];
            $this->currentRow++;

            $this->headerRows[] = $this->currentRow;
            $rows[] = ['No', 'Nama Sekolah', 'NPSN', 'Bentuk', 'Status', 'Total', 'L', 'P'];
            $this->currentRow++;

            $top20 = array_slice($sebaran, 0, 20);
            foreach ($top20 as $idx => $sk) {
                $rows[] = [$idx + 1, $sk['nama'], $sk['npsn'], $sk['bentuk'], $sk['status'], $sk['total'], $sk['l'], $sk['p']];
                $this->currentRow++;
            }
        }

        return $rows;
    }

    private function buildSectionDetail(array $section, string $sectionTitle): array
    {
        $rows = [];

        // -- Jenis Kelamin --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = [strtoupper($sectionTitle) . ' - RINCIAN'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Uraian', 'Jumlah'];
        $this->currentRow++;

        $rows[] = ['Total', $section['total']];
        $rows[] = ['Laki-laki', $section['laki_laki']];
        $rows[] = ['Perempuan', $section['perempuan']];
        $this->currentRow += 3;

        $rows[] = [''];
        $this->currentRow++;

        // -- Pilihan Program --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['PILIHAN PROGRAM'];
        $this->currentRow++;

        if (!($section['program_stats']['enabled'] ?? false)) {
            $rows[] = ['Jalur pada konteks ini tidak menggunakan pilihan program.'];
            $this->currentRow++;
        } else {
            $this->headerRows[] = $this->currentRow;
            $rows[] = ['Program', 'Total', 'Laki-laki', 'Perempuan'];
            $this->currentRow++;

            foreach (($section['program_stats']['items'] ?? []) as $program) {
                $rows[] = [$program['label'], $program['total'], $program['l'], $program['p']];
                $this->currentRow++;
            }

            $this->totalRows[] = $this->currentRow;
            $rows[] = ['TOTAL', $section['total'], $section['laki_laki'], $section['perempuan']];
            $this->currentRow++;
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Asal Sekolah --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['ASAL SEKOLAH'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Kategori Sekolah', 'Total', 'Laki-laki', 'Perempuan'];
        $this->currentRow++;

        $asalSekolah = $section['asal_sekolah'] ?? [];
        $grandTotal = 0;
        $grandL = 0;
        $grandP = 0;
        foreach ($asalSekolah as $cat => $data) {
            $rows[] = [$cat, $data['total'], $data['l'], $data['p']];
            $this->currentRow++;
            $grandTotal += $data['total'];
            $grandL += $data['l'];
            $grandP += $data['p'];
        }

        $this->totalRows[] = $this->currentRow;
        $rows[] = ['TOTAL', $grandTotal, $grandL, $grandP];
        $this->currentRow++;

        return $rows;
    }

    private function buildKelulusan(): array
    {
        $rows = [];

        // -- Lulus --
        $lulusSection = $this->stats['kelulusan'] ?? [];
        $rows = array_merge($rows, $this->buildSectionDetail($lulusSection, 'LULUS'));

        $rows[] = [''];
        $this->currentRow++;

        // -- Tidak Lulus --
        $tidakLulusSection = $this->stats['kelulusan_tidak_lulus'] ?? [];

        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['TIDAK LULUS - RINCIAN'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Uraian', 'Jumlah'];
        $this->currentRow++;

        $rows[] = ['Total Tidak Lulus', $tidakLulusSection['total'] ?? 0];
        $rows[] = ['Laki-laki', $tidakLulusSection['laki_laki'] ?? 0];
        $rows[] = ['Perempuan', $tidakLulusSection['perempuan'] ?? 0];
        $this->currentRow += 3;

        $rows[] = [''];
        $this->currentRow++;

        // -- Tidak Lulus Program --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['TIDAK LULUS - PILIHAN PROGRAM'];
        $this->currentRow++;

        if (!($tidakLulusSection['program_stats']['enabled'] ?? false)) {
            $rows[] = ['Jalur pada konteks ini tidak menggunakan pilihan program.'];
            $this->currentRow++;
        } else {
            $this->headerRows[] = $this->currentRow;
            $rows[] = ['Program', 'Total', 'Laki-laki', 'Perempuan'];
            $this->currentRow++;

            foreach (($tidakLulusSection['program_stats']['items'] ?? []) as $program) {
                $rows[] = [$program['label'], $program['total'], $program['l'], $program['p']];
                $this->currentRow++;
            }
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Tidak Lulus Asal Sekolah --
        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['TIDAK LULUS - ASAL SEKOLAH'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['Kategori Sekolah', 'Total', 'Laki-laki', 'Perempuan'];
        $this->currentRow++;

        $asalSekolah = $tidakLulusSection['asal_sekolah'] ?? [];
        foreach ($asalSekolah as $cat => $data) {
            $rows[] = [$cat, $data['total'], $data['l'], $data['p']];
            $this->currentRow++;
        }

        $rows[] = [''];
        $this->currentRow++;

        // -- Cadangan --
        $cadanganSection = $this->stats['kelulusan_cadangan'] ?? [];
        $rows = array_merge($rows, $this->buildSectionDetail($cadanganSection, 'CADANGAN'));

        return $rows;
    }

    private function buildSebaranSekolah(): array
    {
        $rows = [];
        $sebaran = $this->stats['sebaran_sekolah'] ?? [];

        $this->sectionHeaderRows[] = $this->currentRow;
        $rows[] = ['SEBARAN SEKOLAH ASAL (' . count($sebaran) . ' SEKOLAH)'];
        $this->currentRow++;

        $this->headerRows[] = $this->currentRow;
        $rows[] = ['No', 'Nama Sekolah', 'NPSN', 'Bentuk', 'Status', 'Total', 'L', 'P'];
        $this->currentRow++;

        foreach ($sebaran as $idx => $sk) {
            $rows[] = [$idx + 1, $sk['nama'], $sk['npsn'], $sk['bentuk'], $sk['status'], $sk['total'], $sk['l'], $sk['p']];
            $this->currentRow++;
        }

        if (count($sebaran) === 0) {
            $rows[] = ['', 'Belum ada data'];
            $this->currentRow++;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title styles (rows 1-5)
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');
        $sheet->mergeCells('A4:H4');
        $sheet->mergeCells('A5:H5');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:A5')->getFont()->setSize(10);

        // Section header styles
        foreach ($this->sectionHeaderRows as $row) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }

        // Table header styles
        foreach ($this->headerRows as $row) {
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ECF0F1'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        // Total row styles
        foreach ($this->totalRows as $row) {
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D5F5E3'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        // Center align number columns
        $lastRow = $this->currentRow;
        $sheet->getStyle("B7:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
