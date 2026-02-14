<?php

namespace App\Imports;

use App\Models\JadwalUjian;
use App\Models\RuangUjian;
use App\Models\CalonSiswa;
use App\Models\NilaiSeleksi;
use App\Models\BobotNilaiSeleksi;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiPenilaianImport
{
    protected JadwalUjian $jadwalUjian;
    protected array $results = [];
    protected int $imported = 0;
    protected int $updated = 0;
    protected int $skipped = 0;
    protected array $errors = [];

    public function __construct(JadwalUjian $jadwalUjian)
    {
        $this->jadwalUjian = $jadwalUjian;
    }

    /**
     * Import from uploaded Excel file
     */
    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $jadwal = $this->jadwalUjian->load(['tahunPelajaran', 'sesiUjian']);

        // Build bobot/field mapping
        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $fieldMap = $this->buildFieldMap($bobotList);
        $user = Auth::user();

        // Process each sheet (one per ruang)
        foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
            $sheet = $spreadsheet->getSheet($sheetIndex);
            $this->processSheet($sheet, $sheetName, $jadwal, $fieldMap, $user);
        }

        return [
            'imported' => $this->imported,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total' => $this->imported + $this->updated + $this->skipped,
        ];
    }

    /**
     * Build field mapping from bobot list
     * Returns ordered array of field names matching Excel column order
     */
    protected function buildFieldMap($bobotList): array
    {
        $fields = [];

        foreach ($bobotList as $bobot) {
            if ($bobot->komponen === 'baca_quran') {
                // 4 sub-columns: tajwid, makhroj, kelancaran, rata2 (rata2 is skipped on import)
                $fields[] = ['field' => 'nilai_tajwid', 'type' => 'nilai'];
                $fields[] = ['field' => 'nilai_makhroj', 'type' => 'nilai'];
                $fields[] = ['field' => 'nilai_kelancaran', 'type' => 'nilai'];
                $fields[] = ['field' => null, 'type' => 'skip']; // Rata-rata (auto-calculated)
            } elseif ($bobot->komponen === 'hafalan') {
                $fields[] = ['field' => 'nilai_hafalan', 'type' => 'nilai'];
            } elseif ($bobot->komponen === 'tulis_quran') {
                $fields[] = ['field' => 'nilai_tulis_quran', 'type' => 'nilai'];
            } elseif ($bobot->komponen === 'wawancara') {
                $fields[] = ['field' => 'nilai_wawancara', 'type' => 'nilai'];
            }
        }

        return $fields;
    }

    /**
     * Process a single sheet
     */
    protected function processSheet($sheet, string $sheetName, $jadwal, array $fieldMap, $user): void
    {
        $highestRow = $sheet->getHighestRow();

        // Find data start row by looking for the first row with numeric value in column A
        $dataStartRow = null;
        for ($row = 1; $row <= min($highestRow, 20); $row++) {
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();
            // Data row: column A is numeric (nomor urut) and column B has value (nomor tes)
            if (is_numeric($cellA) && !empty($cellB)) {
                $dataStartRow = $row;
                break;
            }
        }

        if (!$dataStartRow) {
            $this->errors[] = "Sheet '{$sheetName}': Tidak ditemukan data peserta.";
            return;
        }

        // Try to identify the ruang+sesi from sheet name (format: "RuangName S1")
        $sesi = null;
        $ruang = null;
        $this->findRuangSesi($sheetName, $jadwal, $sesi, $ruang);

        if (!$sesi || !$ruang) {
            $this->errors[] = "Sheet '{$sheetName}': Tidak dapat mengidentifikasi ruang/sesi.";
            return;
        }

        // Process data rows
        $nilaiStartCol = 3; // Column D (0-indexed: A=0, B=1, C=2, D=3)

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $nomorUrut = $sheet->getCell("A{$row}")->getValue();
            $nomorTes = trim((string) $sheet->getCell("B{$row}")->getValue());
            $namaLengkap = trim((string) $sheet->getCell("C{$row}")->getValue());

            // Stop if no more data
            if (empty($nomorTes) && empty($namaLengkap)) {
                break;
            }

            if (empty($nomorTes)) {
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: Nomor tes kosong (nama: {$namaLengkap}).";
                $this->skipped++;
                continue;
            }

            // Find calon siswa by nomor_tes
            $calonSiswa = CalonSiswa::where('nomor_tes', $nomorTes)->first();
            if (!$calonSiswa) {
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: Peserta '{$nomorTes}' ({$namaLengkap}) tidak ditemukan.";
                $this->skipped++;
                continue;
            }

            // Read nilai from columns
            $nilaiData = [];
            $hasAnyValue = false;

            foreach ($fieldMap as $colOffset => $mapping) {
                $colIndex = $nilaiStartCol + $colOffset;
                $colLetter = $this->getColLetter($colIndex);
                $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();

                if ($mapping['type'] === 'skip') continue;

                if ($cellValue !== null && $cellValue !== '' && is_numeric($cellValue)) {
                    $nilaiData[$mapping['field']] = round((float) $cellValue, 2);
                    $hasAnyValue = true;
                } else {
                    $nilaiData[$mapping['field']] = null;
                }
            }

            if (!$hasAnyValue) {
                $this->skipped++;
                continue;
            }

            // Save/update NilaiSeleksi
            $nilai = NilaiSeleksi::firstOrNew([
                'sesi_ujian_id' => $sesi->id,
                'calon_siswa_id' => $calonSiswa->id,
                'penguji_id' => $user->id,
            ], [
                'ruang_ujian_id' => $ruang->id,
                'status' => NilaiSeleksi::STATUS_DRAFT,
            ]);

            $isNew = !$nilai->exists;

            // Skip if already submitted
            if ($nilai->exists && !$nilai->isEditable()) {
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: Nilai '{$nomorTes}' sudah disubmit, dilewati.";
                $this->skipped++;
                continue;
            }

            $nilai->ruang_ujian_id = $ruang->id;
            $nilai->fill($nilaiData);
            $nilai->status = NilaiSeleksi::STATUS_SUBMITTED;
            $nilai->save();
            $nilai->updateTotalNilai();

            if ($isNew) {
                $this->imported++;
            } else {
                $this->updated++;
            }
        }
    }

    /**
     * Find ruang and sesi from sheet name
     * Sheet name format: "RuangName S1" or "RuangName S2"
     */
    protected function findRuangSesi(string $sheetName, $jadwal, &$sesi, &$ruang): void
    {
        // Try to extract sesi number from sheet name (e.g. "Ruang A S1" -> S1)
        if (preg_match('/\bS(\d+)\s*$/i', $sheetName, $m)) {
            $nomorSesi = (int) $m[1];
            $ruangName = trim(preg_replace('/\bS\d+\s*$/i', '', $sheetName));

            // Find sesi by nomor_sesi
            foreach ($jadwal->sesiUjian as $s) {
                if ((int) $s->nomor_sesi === $nomorSesi) {
                    $sesi = $s;
                    break;
                }
            }

            if ($sesi) {
                // Find ruang in this sesi
                $ruang = RuangUjian::where('sesi_ujian_id', $sesi->id)
                    ->where('nama_ruang', 'LIKE', "%{$ruangName}%")
                    ->first();

                // Fallback: try exact match without sesi filter then check
                if (!$ruang) {
                    $ruang = RuangUjian::where('sesi_ujian_id', $sesi->id)
                        ->get()
                        ->first(function ($r) use ($ruangName) {
                            $clean = preg_replace('/[^\w\s\-]/', '', $r->nama_ruang);
                            return stripos($clean, $ruangName) !== false ||
                                   stripos($ruangName, $clean) !== false;
                        });
                }
            }
        }

        // Fallback: try matching ruang name across all sesi
        if (!$ruang) {
            foreach ($jadwal->sesiUjian as $s) {
                $r = RuangUjian::where('sesi_ujian_id', $s->id)
                    ->get()
                    ->first(function ($ruangItem) use ($sheetName) {
                        $cleanSheet = preg_replace('/[^\w\s\-]/', '', $sheetName);
                        $cleanRuang = preg_replace('/[^\w\s\-]/', '', $ruangItem->nama_ruang);
                        return stripos($cleanSheet, $cleanRuang) !== false;
                    });

                if ($r) {
                    $sesi = $s;
                    $ruang = $r;
                    break;
                }
            }
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
