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
    protected array $previewRows = [];
    protected bool $previewMode = false;

    public function __construct(JadwalUjian $jadwalUjian)
    {
        $this->jadwalUjian = $jadwalUjian;
    }

    /**
     * Preview - parse file tanpa menyimpan, return data preview per baris
     */
    public function preview(string $filePath): array
    {
        $this->previewMode = true;
        $this->previewRows = [];

        $spreadsheet = IOFactory::load($filePath);
        $jadwal = $this->jadwalUjian->load(['tahunPelajaran', 'sesiUjian']);

        $bobotList = BobotNilaiSeleksi::where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $fieldMap = $this->buildFieldMap($bobotList);
        $user = Auth::user();

        foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
            $sheet = $spreadsheet->getSheet($sheetIndex);
            $this->processSheet($sheet, $sheetName, $jadwal, $fieldMap, $user);
        }

        // Build komponen labels for preview header
        $komponenLabels = [];
        foreach ($fieldMap as $mapping) {
            if ($mapping['type'] === 'skip') {
                $komponenLabels[] = ['field' => null, 'label' => 'Rata-rata (auto)', 'type' => 'skip'];
            } else {
                $label = ucwords(str_replace(['nilai_', '_'], ['', ' '], $mapping['field']));
                $komponenLabels[] = ['field' => $mapping['field'], 'label' => $label, 'type' => 'nilai'];
            }
        }

        return [
            'rows' => $this->previewRows,
            'komponen_labels' => $komponenLabels,
            'summary' => [
                'total' => count($this->previewRows),
                'valid' => collect($this->previewRows)->where('status', 'valid')->count(),
                'warning' => collect($this->previewRows)->where('status', 'warning')->count(),
                'error' => collect($this->previewRows)->where('status', 'error')->count(),
                'skip' => collect($this->previewRows)->where('status', 'skip')->count(),
            ],
            'errors' => $this->errors,
        ];
    }

    /**
     * Import from uploaded Excel file
     */
    public function import(string $filePath): array
    {
        $this->previewMode = false;
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
        // Kolom: A=No Urut, B=No Tes, C=Nama, D=Pilihan Program, E+=Nilai
        $nilaiStartCol = 4; // Column E (0-indexed: A=0, B=1, C=2, D=3, E=4)

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $nomorUrut = $sheet->getCell("A{$row}")->getValue();
            $nomorTes = trim((string) $sheet->getCell("B{$row}")->getValue());
            $namaLengkap = trim((string) $sheet->getCell("C{$row}")->getValue());

            // Stop if no more data
            if (empty($nomorTes) && empty($namaLengkap)) {
                break;
            }

            // Build preview row base
            $previewRow = null;
            if ($this->previewMode) {
                $previewRow = [
                    'sheet' => $sheetName,
                    'ruang' => $ruang->nama_ruang,
                    'sesi' => $sesi->nomor_sesi,
                    'baris' => $row,
                    'nomor_tes' => $nomorTes,
                    'nama_lengkap' => $namaLengkap,
                    'status' => 'valid',
                    'action' => 'baru',
                    'issues' => [],
                    'nilai_raw' => [],
                    'nilai_parsed' => [],
                ];
            }

            if (empty($nomorTes)) {
                $errMsg = "Nomor tes kosong";
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: {$errMsg} (nama: {$namaLengkap}).";
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'error';
                    $previewRow['issues'][] = $errMsg;
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            // Find calon siswa by nomor_tes
            $calonSiswa = CalonSiswa::where('nomor_tes', $nomorTes)->first();
            if (!$calonSiswa) {
                $errMsg = "Peserta dengan nomor tes '{$nomorTes}' tidak ditemukan di database";
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: {$errMsg}.";
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'error';
                    $previewRow['issues'][] = $errMsg;
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            if ($this->previewMode) {
                $previewRow['nama_lengkap'] = $calonSiswa->nama_lengkap; // Use DB name
            }

            // Read nilai from columns
            $nilaiData = [];
            $hasAnyValue = false;
            $hasWarning = false;

            foreach ($fieldMap as $colOffset => $mapping) {
                $colIndex = $nilaiStartCol + $colOffset;
                $colLetter = $this->getColLetter($colIndex);
                $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                $rawValue = $cellValue;

                if ($mapping['type'] === 'skip') {
                    if ($this->previewMode) {
                        $previewRow['nilai_raw'][] = ['field' => null, 'raw' => $rawValue, 'parsed' => null, 'type' => 'skip'];
                    }
                    continue;
                }

                $fieldLabel = ucwords(str_replace(['nilai_', '_'], ['', ' '], $mapping['field']));

                if ($cellValue === null || $cellValue === '') {
                    // Kosong
                    $nilaiData[$mapping['field']] = null;
                    if ($this->previewMode) {
                        $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => '', 'parsed' => null, 'type' => 'empty'];
                    }
                } elseif (is_numeric($cellValue)) {
                    // Angka murni
                    $parsedVal = round((float) $cellValue, 2);
                    if ($parsedVal < 0 || $parsedVal > 100) {
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$fieldLabel}: nilai {$parsedVal} di luar rentang 0-100";
                            $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => $rawValue, 'parsed' => $parsedVal, 'type' => 'warning'];
                        }
                    } else {
                        if ($this->previewMode) {
                            $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => $rawValue, 'parsed' => $parsedVal, 'type' => 'valid'];
                        }
                    }
                    $nilaiData[$mapping['field']] = $parsedVal;
                    $hasAnyValue = true;
                } else {
                    // Non-numeric: coba ekstrak angka dari teks campuran (misal "70 (2 juz)", "85 bagus")
                    $extracted = $this->extractNumber($cellValue);
                    if ($extracted !== null) {
                        $parsedVal = round($extracted, 2);
                        $nilaiData[$mapping['field']] = $parsedVal;
                        $hasAnyValue = true;
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$fieldLabel}: isi \"{$rawValue}\" → diambil angka {$parsedVal}";
                            $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => $rawValue, 'parsed' => $parsedVal, 'type' => 'extracted'];
                        }
                    } else {
                        // Tidak ada angka sama sekali (misal "B+", "bagus")
                        $nilaiData[$mapping['field']] = null;
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$fieldLabel}: isi \"{$rawValue}\" tidak mengandung angka, diabaikan";
                            $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => $rawValue, 'parsed' => null, 'type' => 'invalid'];
                        }
                    }
                }
            }

            if (!$hasAnyValue) {
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'skip';
                    $previewRow['issues'][] = 'Tidak ada nilai yang terisi';
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            // Check existing NilaiSeleksi
            $nilai = NilaiSeleksi::where('sesi_ujian_id', $sesi->id)
                ->where('calon_siswa_id', $calonSiswa->id)
                ->first();

            $isNew = !$nilai;

            if ($nilai && !$nilai->isEditable()) {
                $errMsg = "Nilai sudah disubmit/verified, tidak bisa diubah";
                $this->errors[] = "Sheet '{$sheetName}' baris {$row}: Nilai '{$nomorTes}' sudah disubmit, dilewati.";
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'error';
                    $previewRow['action'] = 'skip';
                    $previewRow['issues'][] = $errMsg;
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            if ($this->previewMode) {
                $previewRow['action'] = $isNew ? 'baru' : 'update';
                $previewRow['nilai_parsed'] = $nilaiData;
                if ($hasWarning) {
                    $previewRow['status'] = 'warning';
                }
                $this->previewRows[] = $previewRow;
            } else {
                // Actually save
                $nilai = $nilai ?? new NilaiSeleksi([
                    'sesi_ujian_id' => $sesi->id,
                    'calon_siswa_id' => $calonSiswa->id,
                    'penguji_id' => $user->id,
                    'ruang_ujian_id' => $ruang->id,
                    'status' => NilaiSeleksi::STATUS_DRAFT,
                ]);

                $nilai->ruang_ujian_id = $ruang->id;
                $nilai->penguji_id = $user->id;
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
    }

    /**
     * Ekstrak angka dari teks campuran
     * "70 (2 juz)" → 70, "85 bagus" → 85, "B+" → null
     */
    protected function extractNumber($value): ?float
    {
        $str = trim((string) $value);
        // Coba ambil angka (integer/desimal) di awal atau di dalam string
        if (preg_match('/(\d+(?:[.,]\d+)?)/', $str, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }
        return null;
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
