<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DataPendaftarSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected string $title;
    protected Collection $pendaftar;
    protected $selectedTahun;
    protected $selectedJalur;
    protected $selectedGelombang;
    protected $sekolah;
    protected ?Collection $nilaiSeleksiMap;
    protected ?Collection $nilaiCbtMap;
    protected int $dataStartRow;
    protected int $totalRows;
    protected string $lastCol;

    // Column layout mapping
    // A-T: Data Diri (20 cols)
    // U-AJ: Rapor Sem 1-5 (MTK,IPA,IPS,Rata) = 5x4 = 20 cols → U..AN (col 21..40)
    // AO: Rata-rata Rapor Keseluruhan (col 41)
    // AP-AW: CBT (MTK,IPA,IPS,B.Ing,Total,Rata) = 6 cols → col 42..47
    // AX-BF: TBQ (Wawancara,Tajwid,Makhroj,Kelancaran,Baca Quran,Tulis Quran,Hafalan,Juz Hafalan,Total) = 9 cols → col 48..56
    // BG-BJ: Nilai Akhir (Nilai CBT, Nilai Wawancara, Nilai Akhir, Ranking) = 4 cols → col 57..60

    public function __construct(
        string $title,
        Collection $pendaftar,
        $selectedTahun,
        $selectedJalur,
        $selectedGelombang,
        $sekolah,
        ?Collection $nilaiSeleksiMap = null,
        ?Collection $nilaiCbtMap = null
    ) {
        $this->title = $title;
        $this->pendaftar = $pendaftar;
        $this->selectedTahun = $selectedTahun;
        $this->selectedJalur = $selectedJalur;
        $this->selectedGelombang = $selectedGelombang;
        $this->sekolah = $sekolah;
        $this->nilaiSeleksiMap = $nilaiSeleksiMap ?? collect();
        $this->nilaiCbtMap = $nilaiCbtMap ?? collect();
        $this->lastCol = 'BJ';
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $rows = [];

        // Header kop
        $sekolahNama = $this->sekolah?->nama_sekolah ?? 'SEKOLAH';
        $rows[] = [strtoupper($sekolahNama)];
        $rows[] = ['DATA ' . strtoupper($this->title) . ' - PPDB'];
        $rows[] = ['Tahun Pelajaran: ' . ($this->selectedTahun?->nama ?? '-')];

        $filter = 'Jalur: ' . ($this->selectedJalur?->nama ?? 'Semua Jalur') . '  ';
        $filter .= 'Gelombang: ' . ($this->selectedGelombang?->nama ?? 'Semua Gelombang');
        $rows[] = [$filter];
        $rows[] = ['Dicetak: ' . now()->translatedFormat('d F Y H:i') . ' WIB  |  Total: ' . $this->pendaftar->count() . ' data'];
        $rows[] = []; // blank

        // Column headers (row 7)
        $this->dataStartRow = 7;
        $headers = [
            // Data Diri: A-T (20 cols)
            'No',                   // A
            'No. Registrasi',       // B
            'No. Tes',              // C
            'NISN',                 // D
            'NIK',                  // E
            'Nama Lengkap',         // F
            'Jenis Kelamin',        // G
            'Tempat Lahir',         // H
            'Tanggal Lahir',        // I
            'Alamat',               // J
            'No. HP',               // K
            'Email',                // L
            'Asal Sekolah',         // M
            'NPSN Asal',            // N
            'Jalur',                // O
            'Gelombang',            // P
            'Pilihan Program',      // Q
            'Finalisasi',           // R
            'Status Verifikasi',    // S
            'Tgl Registrasi',       // T
        ];

        // Rapor Sem 1-5: U-AN (20 cols)
        for ($sem = 1; $sem <= 5; $sem++) {
            $headers[] = "Rapor S{$sem} MTK";
            $headers[] = "Rapor S{$sem} IPA";
            $headers[] = "Rapor S{$sem} IPS";
            $headers[] = "Rapor S{$sem} Rata";
        }

        // Rata-rata rapor keseluruhan: AO
        $headers[] = 'Rata Rapor';

        // CBT: AP-AU (6 cols)
        $headers[] = 'CBT MTK';
        $headers[] = 'CBT IPA';
        $headers[] = 'CBT IPS';
        $headers[] = 'CBT B.Inggris';
        $headers[] = 'CBT Total';
        $headers[] = 'CBT Rata';

        // TBQ/Seleksi: AV-BD (9 cols)
        $headers[] = 'TBQ Wawancara';
        $headers[] = 'TBQ Tajwid';
        $headers[] = 'TBQ Makhroj';
        $headers[] = 'TBQ Kelancaran';
        $headers[] = 'TBQ Baca Quran';
        $headers[] = 'TBQ Tulis Quran';
        $headers[] = 'TBQ Hafalan';
        $headers[] = 'TBQ Juz Hafalan';
        $headers[] = 'TBQ Total';

        // Nilai Akhir: BE-BH (4 cols)
        $headers[] = 'N. CBT (Akhir)';
        $headers[] = 'N. Wawancara (Akhir)';
        $headers[] = 'Nilai Akhir';
        $headers[] = 'Ranking';

        // Status kelulusan: BI-BJ (2 cols)
        $headers[] = 'Status Kelulusan';
        $headers[] = 'Catatan Admisi';

        $rows[] = $headers;

        // Data rows
        $no = 0;
        foreach ($this->pendaftar->sortBy('nama_lengkap') as $p) {
            $no++;
            $row = [
                $no,
                $p->nomor_registrasi ?? '-',
                $p->nomor_tes ?? '-',
                "'" . ($p->nisn ?? '-'),
                "'" . ($p->nik ?? '-'),
                $p->nama_lengkap ?? '-',
                $p->jenis_kelamin === 'L' ? 'Laki-laki' : ($p->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                $p->tempat_lahir ?? '-',
                $p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : '-',
                $p->alamat_siswa ?? '-',
                "'" . ($p->nomor_hp ?? '-'),
                $p->email ?? '-',
                $p->nama_sekolah_asal ?? '-',
                "'" . ($p->npsn_asal_sekolah ?? '-'),
                $p->jalurPendaftaran?->nama ?? '-',
                $p->gelombangPendaftaran?->nama ?? '-',
                $p->pilihan_program ?? '-',
                $p->is_finalisasi ? 'Ya' : 'Belum',
                $this->formatStatusVerifikasi($p->status_verifikasi),
                $p->tanggal_registrasi ? $p->tanggal_registrasi->format('d/m/Y H:i') : '-',
            ];

            // Rapor Sem 1-5
            $raporBySem = $p->relationLoaded('nilaiRapor')
                ? $p->nilaiRapor->keyBy('semester')
                : collect();

            $raporRataTotal = [];
            for ($sem = 1; $sem <= 5; $sem++) {
                $r = $raporBySem->get($sem);
                $row[] = $r ? $this->numVal($r->matematika) : '';
                $row[] = $r ? $this->numVal($r->ipa) : '';
                $row[] = $r ? $this->numVal($r->ips) : '';
                $row[] = $r ? $this->numVal($r->rata_rata) : '';
                if ($r && $r->rata_rata !== null) {
                    $raporRataTotal[] = (float) $r->rata_rata;
                }
            }

            // Rata-rata rapor keseluruhan
            $row[] = count($raporRataTotal) > 0 ? round(array_sum($raporRataTotal) / count($raporRataTotal), 2) : '';

            // CBT
            $cbt = $this->nilaiCbtMap->get($p->id);
            $row[] = $cbt ? $this->numVal($cbt->nilai_mtk) : '';
            $row[] = $cbt ? $this->numVal($cbt->nilai_ipa) : '';
            $row[] = $cbt ? $this->numVal($cbt->nilai_ips) : '';
            $row[] = $cbt ? $this->numVal($cbt->nilai_bahasa_inggris) : '';
            $row[] = $cbt ? $this->numVal($cbt->total_nilai) : '';
            $row[] = $cbt ? $this->numVal($cbt->rata_rata) : '';

            // TBQ/Seleksi
            $tbq = $this->nilaiSeleksiMap->get($p->id);
            $row[] = $tbq ? $this->numVal($tbq->nilai_wawancara) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_tajwid) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_makhroj) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_kelancaran) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_baca_quran) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_tulis_quran) : '';
            $row[] = $tbq ? $this->numVal($tbq->nilai_hafalan) : '';
            $row[] = $tbq ? ($tbq->jumlah_juz_hafalan ?? '') : '';
            $row[] = $tbq ? $this->numVal($tbq->total_nilai) : '';

            // Nilai Akhir (dari CalonSiswa)
            $row[] = $this->numVal($p->nilai_cbt);
            $row[] = $this->numVal($p->nilai_wawancara);
            $row[] = $this->numVal($p->nilai_akhir);
            $row[] = $p->ranking ?? '';

            // Status kelulusan
            $row[] = $this->formatKelulusan($p->kelulusan?->status ?? null);
            $row[] = $p->catatan_admisi ?? '';

            $rows[] = $row;
        }

        $this->totalRows = $no;

        return $rows;
    }

    private function numVal($val): string
    {
        if ($val === null) return '';
        return (string) $val;
    }

    private function formatStatusVerifikasi(?string $status): string
    {
        return match ($status) {
            'verified' => 'Terverifikasi',
            'pending' => 'Pending',
            'rejected' => 'Ditolak',
            'revisi' => 'Perlu Revisi',
            default => $status ?? '-',
        };
    }

    private function formatKelulusan(?string $status): string
    {
        return match ($status) {
            'lulus' => 'Lulus',
            'tidak_lulus' => 'Tidak Lulus',
            'cadangan' => 'Cadangan',
            default => '-',
        };
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 18,  // No. Registrasi
            'C' => 14,  // No. Tes
            'D' => 14,  // NISN
            'E' => 18,  // NIK
            'F' => 30,  // Nama Lengkap
            'G' => 13,  // JK
            'H' => 16,  // Tempat Lahir
            'I' => 12,  // Tgl Lahir
            'J' => 30,  // Alamat
            'K' => 15,  // No. HP
            'L' => 22,  // Email
            'M' => 28,  // Asal Sekolah
            'N' => 12,  // NPSN Asal
            'O' => 18,  // Jalur
            'P' => 18,  // Gelombang
            'Q' => 15,  // Pilihan Program
            'R' => 10,  // Finalisasi
            'S' => 15,  // Status Verifikasi
            'T' => 16,  // Tgl Registrasi
        ];

        // Rapor cols (U-AN): 20 cols, width 8
        $col = 'U';
        for ($i = 0; $i < 20; $i++) {
            $widths[$col] = 8;
            $col++;
        }
        // AO: Rata rapor
        $widths[$col] = 9;
        $col++;
        // CBT cols: 6, width 9
        for ($i = 0; $i < 6; $i++) {
            $widths[$col] = 9;
            $col++;
        }
        // TBQ cols: 9, width 9
        for ($i = 0; $i < 9; $i++) {
            $widths[$col] = 9;
            $col++;
        }
        // N.Akhir cols: 4
        for ($i = 0; $i < 4; $i++) {
            $widths[$col] = 11;
            $col++;
        }
        // Kelulusan + Catatan: 2
        $widths[$col] = 14;
        $col++;
        $widths[$col] = 20;

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = $this->lastCol;
        $lastDataRow = $this->dataStartRow + $this->totalRows;

        // Merge header rows (A1:lastCol 5)
        for ($r = 1; $r <= 5; $r++) {
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
        }

        // Kop styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row (row 7)
        $headerRow = $this->dataStartRow;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(35);

        // Color-code header groups
        // Rapor: green
        $sheet->getStyle("U{$headerRow}:AO{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('27AE60');
        // CBT: orange
        $sheet->getStyle("AP{$headerRow}:AU{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E67E22');
        // TBQ: purple
        $sheet->getStyle("AV{$headerRow}:BD{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('8E44AD');
        // N Akhir: dark blue
        $sheet->getStyle("BE{$headerRow}:BH{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A5276');
        // Kelulusan: red
        $sheet->getStyle("BI{$headerRow}:BJ{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C0392B');

        // Data rows
        if ($this->totalRows > 0) {
            $dataStart = $headerRow + 1;
            $sheet->getStyle("A{$dataStart}:{$lastCol}{$lastDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
                'font' => ['size' => 9],
            ]);

            // Center No, JK, Tgl Lahir, Finalisasi, Status
            foreach (['A', 'G', 'I', 'R', 'S'] as $c) {
                $sheet->getStyle("{$c}{$dataStart}:{$c}{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Center all numeric nilai columns (U onwards)
            $sheet->getStyle("U{$dataStart}:{$lastCol}{$lastDataRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping
            for ($r = $dataStart; $r <= $lastDataRow; $r++) {
                if (($r - $dataStart) % 2 === 1) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F2F8FD');
                }
            }
        }

        // Freeze pane
        $sheet->freezePane('G' . ($headerRow + 1));

        // Auto-filter
        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$lastDataRow}");

        return [];
    }
}
