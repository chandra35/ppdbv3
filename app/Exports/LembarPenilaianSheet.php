<?php

namespace App\Exports;

use App\Models\JadwalUjian;
use App\Models\SesiUjian;
use App\Models\RuangUjian;
use App\Models\SekolahSettings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class LembarPenilaianSheet implements FromArray, WithTitle, WithStyles, WithEvents
{
    protected JadwalUjian $jadwal;
    protected SesiUjian $sesi;
    protected RuangUjian $ruang;
    protected $pesertaRuang;
    protected $bobotList;
    protected $nilaiMap;
    protected $sekolah;
    protected array $nilaiColumns = [];
    protected int $totalCols = 0;
    protected int $headerRow = 0;
    protected int $subHeaderRow = 0;
    protected int $dataStartRow = 0;

    public function __construct(
        JadwalUjian $jadwal,
        SesiUjian $sesi,
        RuangUjian $ruang,
        $pesertaRuang,
        $bobotList,
        $nilaiMap,
        $sekolah
    ) {
        $this->jadwal = $jadwal;
        $this->sesi = $sesi;
        $this->ruang = $ruang;
        $this->pesertaRuang = $pesertaRuang;
        $this->bobotList = $bobotList;
        $this->nilaiMap = $nilaiMap;
        $this->sekolah = $sekolah;
        $this->buildNilaiColumns();
    }

    /**
     * Build nilai columns based on active bobot
     */
    protected function buildNilaiColumns(): void
    {
        $this->nilaiColumns = [];

        foreach ($this->bobotList as $bobot) {
            if ($bobot->komponen === 'baca_quran') {
                $this->nilaiColumns[] = [
                    'header' => 'MEMBACA AL QUR\'AN',
                    'sub' => ['Tjwd', 'Mhrj', 'Lncr', 'Rata2'],
                    'komponen' => 'baca_quran',
                    'fields' => ['nilai_tajwid', 'nilai_makhroj', 'nilai_kelancaran', 'nilai_baca_quran'],
                ];
            } elseif ($bobot->komponen === 'hafalan') {
                $this->nilaiColumns[] = [
                    'header' => 'Hfln Qur\'an',
                    'sub' => null,
                    'komponen' => 'hafalan',
                    'fields' => ['nilai_hafalan'],
                ];
            } elseif ($bobot->komponen === 'tulis_quran') {
                $this->nilaiColumns[] = [
                    'header' => 'Tulis Arab',
                    'sub' => null,
                    'komponen' => 'tulis_quran',
                    'fields' => ['nilai_tulis_quran'],
                ];
            } elseif ($bobot->komponen === 'wawancara') {
                $this->nilaiColumns[] = [
                    'header' => 'Wawancara',
                    'sub' => null,
                    'komponen' => 'wawancara',
                    'fields' => ['nilai_wawancara'],
                ];
            }
        }

        // Calculate total columns: No Urut + No Peserta + Nama + nilai sub-columns + Sekolah Asal
        $nilaiSubCount = 0;
        foreach ($this->nilaiColumns as $nc) {
            $nilaiSubCount += $nc['sub'] ? count($nc['sub']) : 1;
        }
        $this->totalCols = 3 + $nilaiSubCount + 1; // 3 fixed + nilai + sekolah asal
    }

    public function array(): array
    {
        // We'll build the sheet manually via events, return empty
        return [[]];
    }

    public function title(): string
    {
        $title = $this->ruang->nama_ruang . ' S' . $this->sesi->nomor_sesi;
        return substr(preg_replace('/[^\w\s\-]/', '', $title), 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        // Styles applied in afterSheet event
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->buildSheet($sheet);
            },
        ];
    }

    protected function buildSheet(Worksheet $sheet): void
    {
        $row = 1;

        // ===== TITLE HEADERS =====
        $jenjangList = SekolahSettings::JENJANG_LIST ?? [];
        $namaSekolah = strtoupper($this->sekolah->nama_sekolah ?? 'SEKOLAH');
        $jenjang = $jenjangList[$this->sekolah->jenjang ?? ''] ?? '';

        $lastCol = $this->getColLetter($this->totalCols - 1);

        // Row 1: Title line 1
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", "MEMBACA AL QUR'AN, TULIS ARAB, MINAT DAN PIAGAM");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Row 2: Title line 2
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", "SELEKSI PENERIMAAN SISWA BARU " . $jenjang . " " . $namaSekolah);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Row 3: Tahun Pelajaran
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", "TAHUN PELAJARAN " . ($this->jadwal->tahunPelajaran->nama ?? date('Y')));
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // Row 4: Empty
        $row++;

        // Row 5: Room info
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $waktu = ($this->sesi->waktu_mulai?->format('H:i') ?? '') . ' - ' . ($this->sesi->waktu_selesai?->format('H:i') ?? '');
        $sheet->setCellValue("A{$row}", "RUANG  : " . $this->ruang->nama_ruang . " sesi " . $this->sesi->nomor_sesi);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
        $row++;

        // Row 6: Empty
        $row++;

        // ===== TABLE HEADER =====
        $this->headerRow = $row;
        $this->subHeaderRow = $row + 1;
        $this->dataStartRow = $row + 2;

        // Build header row 1 (merged headers)
        $col = 0;

        // NOMOR header (merged 2 rows, 2 cols: URUT + PSRTA)
        $nomorStartCol = $this->getColLetter($col);
        $nomorEndCol = $this->getColLetter($col + 1);
        $sheet->mergeCells("{$nomorStartCol}{$row}:{$nomorEndCol}{$row}");
        $sheet->setCellValue("{$nomorStartCol}{$row}", "NOMOR");
        $col += 2;

        // NAMA (merged 2 rows)
        $namaCol = $this->getColLetter($col);
        $sheet->mergeCells("{$namaCol}{$row}:{$namaCol}" . ($row + 1));
        $sheet->setCellValue("{$namaCol}{$row}", "N A M A");
        $col++;

        // NILAI group header
        $nilaiStartCol = $col;
        $nilaiSubCount = 0;
        foreach ($this->nilaiColumns as $nc) {
            $nilaiSubCount += $nc['sub'] ? count($nc['sub']) : 1;
        }
        $nilaiStartLetter = $this->getColLetter($nilaiStartCol);
        $nilaiEndLetter = $this->getColLetter($nilaiStartCol + $nilaiSubCount - 1);
        $sheet->mergeCells("{$nilaiStartLetter}{$row}:{$nilaiEndLetter}{$row}");
        $sheet->setCellValue("{$nilaiStartLetter}{$row}", "NILAI");

        // Sub-header row (row+1)
        $subRow = $row + 1;

        // NOMOR sub-headers
        $sheet->setCellValue("{$nomorStartCol}{$subRow}", "URUT");
        $sheet->setCellValue("{$nomorEndCol}{$subRow}", "PSRTA");

        // NILAI sub-headers
        $subCol = $nilaiStartCol;
        foreach ($this->nilaiColumns as $nc) {
            if ($nc['sub']) {
                // Merge header for multi-column komponen (e.g. baca_quran)
                $startLetter = $this->getColLetter($subCol);
                $endLetter = $this->getColLetter($subCol + count($nc['sub']) - 1);
                // We need a row between header "NILAI" and sub-headers
                // Actually header "NILAI" is row, and individual komponen headers need to be in sub-row
                // Let me restructure: NILAI is top, then komponen names, then sub-columns
                // Looking at the image: row1 has NOMOR + NAMA + NILAI + TANDATANGAN
                // row2 has URUT + PSRTA + then under NILAI: MEMBACA AL QUR'AN | Hfln | Tulis | Minat | Piagam
                // row3 (if baca_quran has sub): Tjwd | Mhrj | Lncr | Rata2

                // Actually need 3 header rows for this structure. Let me fix.
            }
        }

        // Let me restart with a 3-row header approach
        $sheet->removeRow($row, 2);
        $this->buildTableHeaders($sheet, $row);
    }

    protected function buildTableHeaders(Worksheet $sheet, int $startRow): void
    {
        $row1 = $startRow;     // NOMOR | (empty/NAMA) | NILAI | TANDA TANGAN
        $row2 = $startRow + 1; // URUT|PSRTA | NAMA | komponen headers | (empty)
        $row3 = $startRow + 2; // (empty) | (empty) | sub-komponen (Tjwd,Mhrj,Lncr,Rata2) or komponen label
        $dataStart = $startRow + 3;

        $this->headerRow = $row1;
        $this->dataStartRow = $dataStart;

        $lastCol = $this->getColLetter($this->totalCols - 1);

        // === ROW 1: Top headers ===
        $col = 0;

        // NOMOR (spans 2 cols, 1 row)
        $sheet->mergeCells($this->getColLetter($col) . $row1 . ':' . $this->getColLetter($col + 1) . $row1);
        $sheet->setCellValue($this->getColLetter($col) . $row1, "NOMOR");
        $col += 2;

        // NAMA (spans 3 rows)
        $namaCol = $this->getColLetter($col);
        $sheet->mergeCells("{$namaCol}{$row1}:{$namaCol}{$row3}");
        $sheet->setCellValue("{$namaCol}{$row1}", "N A M A");
        $col++;

        // NILAI group (spans all nilai sub-columns)
        $nilaiStart = $col;
        $nilaiSubCount = 0;
        foreach ($this->nilaiColumns as $nc) {
            $nilaiSubCount += $nc['sub'] ? count($nc['sub']) : 1;
        }
        $nilaiStartLetter = $this->getColLetter($nilaiStart);
        $nilaiEndLetter = $this->getColLetter($nilaiStart + $nilaiSubCount - 1);
        $sheet->mergeCells("{$nilaiStartLetter}{$row1}:{$nilaiEndLetter}{$row1}");
        $sheet->setCellValue("{$nilaiStartLetter}{$row1}", "NILAI");

        // SEKOLAH ASAL (spans 3 rows, after NILAI)
        $asalColIdx = $nilaiStart + $nilaiSubCount;
        $asalCol = $this->getColLetter($asalColIdx);
        $sheet->mergeCells("{$asalCol}{$row1}:{$asalCol}{$row3}");
        $sheet->setCellValue("{$asalCol}{$row1}", "SEKOLAH\nASAL");
        $sheet->getStyle("{$asalCol}{$row1}")->getAlignment()->setWrapText(true);

        // === ROW 2: Sub-headers (URUT, PSRTA, komponen names) ===
        $sheet->setCellValue($this->getColLetter(0) . $row2, "URUT");
        // Merge URUT row2+row3
        $sheet->mergeCells($this->getColLetter(0) . $row2 . ':' . $this->getColLetter(0) . $row3);
        
        $sheet->setCellValue($this->getColLetter(1) . $row2, "PSRTA");
        // Merge PSRTA row2+row3
        $sheet->mergeCells($this->getColLetter(1) . $row2 . ':' . $this->getColLetter(1) . $row3);

        // Komponen headers in row2, sub-komponen in row3
        $col = $nilaiStart;
        foreach ($this->nilaiColumns as $nc) {
            if ($nc['sub']) {
                // Multi-column: merge across sub-columns in row2
                $startLetter = $this->getColLetter($col);
                $endLetter = $this->getColLetter($col + count($nc['sub']) - 1);
                $sheet->mergeCells("{$startLetter}{$row2}:{$endLetter}{$row2}");
                $sheet->setCellValue("{$startLetter}{$row2}", $nc['header']);

                // Sub-headers in row3
                foreach ($nc['sub'] as $i => $subName) {
                    $sheet->setCellValue($this->getColLetter($col + $i) . $row3, $subName);
                }
                $col += count($nc['sub']);
            } else {
                // Single column: merge row2+row3
                $letter = $this->getColLetter($col);
                $sheet->mergeCells("{$letter}{$row2}:{$letter}{$row3}");
                $sheet->setCellValue("{$letter}{$row2}", $nc['header']);
                $col++;
            }
        }

        // === DATA ROWS ===
        $currentRow = $dataStart;
        foreach ($this->pesertaRuang as $index => $pr) {
            $cs = $pr->calonSiswa;
            if (!$cs) continue;

            $col = 0;
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $index + 1);
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $cs->nomor_tes ?? '');
            $sheet->setCellValue($this->getColLetter($col++) . $currentRow, $cs->nama_lengkap ?? '');

            // Nilai columns
            $nilai = $this->nilaiMap[$cs->id] ?? null;
            foreach ($this->nilaiColumns as $nc) {
                foreach ($nc['fields'] as $field) {
                    $val = $nilai ? $nilai->{$field} : null;
                    if ($val !== null && $val > 0) {
                        $sheet->setCellValue($this->getColLetter($col) . $currentRow, $val);
                    }
                    $col++;
                }
            }

            // Sekolah Asal column
            $sheet->setCellValue($this->getColLetter($col) . $currentRow, $cs->nama_sekolah_asal ?? '');

            $currentRow++;
        }

        // === STYLING ===
        $lastDataRow = $currentRow - 1;
        $lastColLetter = $this->getColLetter($this->totalCols - 1);

        // Header style (row1 to row3)
        $headerRange = "A{$row1}:{$lastColLetter}{$row3}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Data style
        if ($lastDataRow >= $dataStart) {
            $dataRange = "A{$dataStart}:{$lastColLetter}{$lastDataRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => ['size' => 10],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center align No Urut, No Peserta, JK-like columns
            $sheet->getStyle("A{$dataStart}:B{$lastDataRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Center align nilai columns (not sekolah asal)
            $nilaiStartLetter = $this->getColLetter(3);
            $nilaiEndLetter = $this->getColLetter($this->totalCols - 2); // exclude last col (sekolah asal)
            $sheet->getStyle("{$nilaiStartLetter}{$dataStart}:{$nilaiEndLetter}{$lastDataRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);   // URUT
        $sheet->getColumnDimension('B')->setWidth(8);   // PSRTA
        $sheet->getColumnDimension('C')->setWidth(35);  // NAMA

        $col = 3;
        foreach ($this->nilaiColumns as $nc) {
            $fieldCount = $nc['sub'] ? count($nc['sub']) : 1;
            for ($i = 0; $i < $fieldCount; $i++) {
                $sheet->getColumnDimension($this->getColLetter($col + $i))->setWidth(7);
            }
            $col += $fieldCount;
        }
        // Sekolah Asal column
        $sheet->getColumnDimension($this->getColLetter($col))->setWidth(25);

        // Set row heights for header
        $sheet->getRowDimension($row1)->setRowHeight(20);
        $sheet->getRowDimension($row2)->setRowHeight(20);
        $sheet->getRowDimension($row3)->setRowHeight(20);

        // === KETERANGAN (Footer) ===
        $footerStart = $lastDataRow + 2;
        $this->buildKeterangan($sheet, $footerStart, $lastColLetter);
    }

    protected function buildKeterangan(Worksheet $sheet, int $startRow, string $lastCol): void
    {
        $row = $startRow;
        $midCol = $this->getColLetter(intval($this->totalCols / 2));

        // Keterangan header
        $sheet->setCellValue("A{$row}", "Keterangan : Penilaian dibubuhkan dalam bentuk angka");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9)->setUnderline(true);
        $row++;

        $sheet->setCellValue("A{$row}", "          Kriteria Penilaian :");
        $sheet->getStyle("A{$row}")->getFont()->setSize(9);
        $row++;

        // Penilaian Piagam
        $sheet->setCellValue("A{$row}", "  1. Penilaian Piagam:");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9);
        $row++;

        $piagamItems = [
            ['a. Tk. Sekolah', ': 5', 'e. Tk. Provinsi', ': 20'],
            ['b. Tk. Desa/Kelurahan', ': 7.5', 'f. Tk. Nasional', ': 25'],
            ['c. Tk Kecamatan', ': 10', 'g. Tk. Internasional', ': 30'],
            ['d. Tk. Kabupaten/Kota', ': 15', '', ''],
        ];

        foreach ($piagamItems as $item) {
            $sheet->setCellValue("A{$row}", "      " . $item[0] . "  " . $item[1]);
            $sheet->setCellValue($midCol . $row, $item[2] . "  " . $item[3]);
            $sheet->getStyle("A{$row}")->getFont()->setSize(9);
            $sheet->getStyle($midCol . $row)->getFont()->setSize(9);
            $row++;
        }

        // Rentang Penilaian
        $sheet->setCellValue("A{$row}", "  2. Rentang Penilaian : Baca, Al Qur'an, tulis arab dan minat :");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9);
        $row++;

        $rentangItems = [
            ['a. Sangat Kurang (SK)', ': 00 - 45', 'c. Cukup (C)', ': 56 - 75'],
            ['b. Kurang         (K)', ': 46 - 55', 'd. Baik (B)', ': 76 - 85'],
        ];

        foreach ($rentangItems as $item) {
            $sheet->setCellValue("A{$row}", "      " . $item[0] . "  " . $item[1]);
            $sheet->setCellValue($midCol . $row, $item[2] . "  " . $item[3]);
            $sheet->getStyle("A{$row}")->getFont()->setSize(9);
            $sheet->getStyle($midCol . $row)->getFont()->setSize(9);
            $row++;
        }

        // Hafalan
        $sheet->setCellValue("A{$row}", "  3. Hafalan Al-Qur'an :");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9);
        $row++;

        $sheet->setCellValue("A{$row}", "      a. Cukup      : 75-85");
        $sheet->getStyle("A{$row}")->getFont()->setSize(9);
        $row++;
        $sheet->setCellValue("A{$row}", "      b. Baik        : 85-100");
        $sheet->getStyle("A{$row}")->getFont()->setSize(9);
        $row++;

        // Peminatan
        $pemRow = $startRow + 2;
        $pemCol = $this->getColLetter(intval($this->totalCols / 2));
        $sheet->setCellValue("{$pemCol}{$pemRow}", "3. Peminatan IPA/IPS disesuaikan");
        $sheet->getStyle("{$pemCol}{$pemRow}")->getFont()->setSize(9);
        $pemRow++;
        $sheet->setCellValue("{$pemCol}{$pemRow}", "   dengan rencana studi lanjut");
        $sheet->getStyle("{$pemCol}{$pemRow}")->getFont()->setSize(9);

        // Signature area (right side)
        $sigCol = $this->getColLetter($this->totalCols - 3);
        $sigRow = $startRow + 1;
        $tanggal = $this->jadwal->tanggal_ujian?->translatedFormat('d F Y') ?? now()->format('d F Y');
        $sheet->setCellValue("{$sigCol}{$sigRow}", "Metro, " . $tanggal);
        $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setSize(10);

        // Get penguji for this ruang
        $pengujiList = \App\Models\PengujiRuang::with('user')
            ->where('ruang_ujian_id', $this->ruang->id)
            ->where('is_active', true)
            ->orderByDesc('is_ketua')
            ->get();

        if ($pengujiList->count() > 0) {
            foreach ($pengujiList as $pIdx => $pgj) {
                $sigRow++;
                $label = $pgj->is_ketua ? 'KETUA PENGUJI' : 'PENGUJI';
                $sheet->setCellValue("{$sigCol}{$sigRow}", $label);
                $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setBold(true)->setSize(10);
                $sigRow += 4;
                $nama = $pgj->user->name ?? '...................................';
                $sheet->setCellValue("{$sigCol}{$sigRow}", $nama);
                $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setBold(true)->setSize(10);
                $sigRow++;
                $nip = ($pgj->user && is_numeric($pgj->user->username)) ? $pgj->user->username : '';
                $sheet->setCellValue("{$sigCol}{$sigRow}", "NIP. " . $nip);
                $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setSize(10);
                $sigRow++;
            }
        } else {
            $sigRow++;
            $sheet->setCellValue("{$sigCol}{$sigRow}", "PENGUJI");
            $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setBold(true)->setSize(10);
            $sigRow += 4;
            $sheet->setCellValue("{$sigCol}{$sigRow}", "...................................");
            $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setSize(10);
            $sigRow++;
            $sheet->setCellValue("{$sigCol}{$sigRow}", "NIP.");
            $sheet->getStyle("{$sigCol}{$sigRow}")->getFont()->setSize(10);
        }
    }

    /**
     * Convert column index (0-based) to Excel letter(s)
     */
    protected function getColLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }
}
