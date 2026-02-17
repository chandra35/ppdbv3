<?php

namespace App\Imports;

use App\Models\CalonSiswa;
use App\Models\NilaiCbt;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiCbtImport
{
    protected array $results = [];
    protected int $imported = 0;
    protected int $updated = 0;
    protected int $skipped = 0;
    protected array $errors = [];
    protected array $previewRows = [];
    protected bool $previewMode = false;
    protected string $tahunPelajaranId;

    /**
     * Kolom CBT - urutan di Excel
     * Excel format: A=No, B=NISN, C=Nama, D=MTK, E=IPA, F=IPS, G=B.Inggris
     */
    protected array $fieldMap = [
        ['field' => 'nilai_mtk', 'label' => 'Matematika'],
        ['field' => 'nilai_ipa', 'label' => 'IPA Terpadu'],
        ['field' => 'nilai_ips', 'label' => 'IPS Terpadu'],
        ['field' => 'nilai_bahasa_inggris', 'label' => 'Bahasa Inggris'],
    ];

    public function __construct(string $tahunPelajaranId)
    {
        $this->tahunPelajaranId = $tahunPelajaranId;
    }

    /**
     * Preview - parse tanpa menyimpan
     */
    public function preview(string $filePath): array
    {
        $this->previewMode = true;
        $this->previewRows = [];
        $this->processFile($filePath);

        return [
            'rows' => $this->previewRows,
            'field_map' => $this->fieldMap,
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
     * Import - parse dan simpan
     */
    public function import(string $filePath): array
    {
        $this->previewMode = false;
        $this->processFile($filePath);

        return [
            'imported' => $this->imported,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total' => $this->imported + $this->updated + $this->skipped,
        ];
    }

    /**
     * Process Excel file
     */
    protected function processFile(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Find data start row
        $dataStartRow = null;
        for ($row = 1; $row <= min($highestRow, 20); $row++) {
            $cellA = $sheet->getCell("A{$row}")->getValue();
            $cellB = $sheet->getCell("B{$row}")->getValue();
            if (is_numeric($cellA) && !empty($cellB)) {
                $dataStartRow = $row;
                break;
            }
        }

        if (!$dataStartRow) {
            $this->errors[] = 'Tidak ditemukan data peserta. Pastikan kolom A=No, B=NISN, C=Nama, D-G=Nilai.';
            return;
        }

        $user = Auth::user();
        $nilaiStartCol = 3; // Column D (0-indexed: A=0, B=1, C=2, D=3)

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $nomorUrut = $sheet->getCell("A{$row}")->getValue();
            $nisn = trim((string) $sheet->getCell("B{$row}")->getValue());
            $namaLengkap = trim((string) $sheet->getCell("C{$row}")->getValue());

            // Stop if no more data
            if (empty($nisn) && empty($namaLengkap)) {
                break;
            }

            $previewRow = null;
            if ($this->previewMode) {
                $previewRow = [
                    'baris' => $row,
                    'nisn' => $nisn,
                    'nama_lengkap' => $namaLengkap,
                    'status' => 'valid',
                    'action' => 'baru',
                    'issues' => [],
                    'nilai_raw' => [],
                ];
            }

            if (empty($nisn)) {
                $this->errors[] = "Baris {$row}: NISN kosong (nama: {$namaLengkap}).";
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'error';
                    $previewRow['issues'][] = 'NISN kosong';
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            // Find calon siswa by NISN
            $calonSiswa = CalonSiswa::where('nisn', $nisn)->first();
            if (!$calonSiswa) {
                $this->errors[] = "Baris {$row}: NISN '{$nisn}' tidak ditemukan di database.";
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = 'error';
                    $previewRow['issues'][] = "NISN '{$nisn}' tidak ditemukan di database";
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            if ($this->previewMode) {
                $previewRow['nama_lengkap'] = $calonSiswa->nama_lengkap; // Use DB name
                $previewRow['nomor_tes'] = $calonSiswa->nomor_tes;
            }

            // Read nilai from columns
            $nilaiData = [];
            $hasAnyValue = false;
            $hasWarning = false;

            foreach ($this->fieldMap as $colOffset => $mapping) {
                $colIndex = $nilaiStartCol + $colOffset;
                $colLetter = chr(65 + $colIndex); // A=65
                $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                $rawValue = $cellValue;

                if ($cellValue === null || $cellValue === '') {
                    $nilaiData[$mapping['field']] = null;
                    if ($this->previewMode) {
                        $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => '', 'parsed' => null, 'type' => 'empty'];
                    }
                } elseif (is_numeric($cellValue)) {
                    $parsedVal = round((float) $cellValue, 2);
                    if ($parsedVal < 0 || $parsedVal > 100) {
                        $originalVal = $parsedVal;
                        $parsedVal = max(0, min(100, $parsedVal));
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$mapping['label']}: nilai {$originalVal} di luar rentang 0-100, di-cap menjadi {$parsedVal}";
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
                    // Try extract number from text
                    $extracted = $this->extractNumber($cellValue);
                    if ($extracted !== null) {
                        $parsedVal = max(0, min(100, round($extracted, 2)));
                        $nilaiData[$mapping['field']] = $parsedVal;
                        $hasAnyValue = true;
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$mapping['label']}: \"{$rawValue}\" → diambil angka {$parsedVal}";
                            $previewRow['nilai_raw'][] = ['field' => $mapping['field'], 'raw' => $rawValue, 'parsed' => $parsedVal, 'type' => 'extracted'];
                        }
                    } else {
                        $nilaiData[$mapping['field']] = null;
                        $hasWarning = true;
                        if ($this->previewMode) {
                            $previewRow['issues'][] = "{$mapping['label']}: \"{$rawValue}\" tidak mengandung angka";
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

            // Check existing
            $nilaiCbt = NilaiCbt::where('calon_siswa_id', $calonSiswa->id)
                ->where('tahun_pelajaran_id', $this->tahunPelajaranId)
                ->first();

            $isNew = !$nilaiCbt;

            if ($this->previewMode) {
                $previewRow['action'] = $isNew ? 'baru' : 'update';
                if ($hasWarning) {
                    $previewRow['status'] = 'warning';
                }
                $this->previewRows[] = $previewRow;
            } else {
                $nilaiCbt = $nilaiCbt ?? new NilaiCbt([
                    'calon_siswa_id' => $calonSiswa->id,
                    'tahun_pelajaran_id' => $this->tahunPelajaranId,
                    'uploaded_by' => $user->id,
                ]);

                $nilaiCbt->uploaded_by = $user->id;
                $nilaiCbt->fill($nilaiData);
                $nilaiCbt->calculateTotal();
                $nilaiCbt->save();

                if ($isNew) {
                    $this->imported++;
                } else {
                    $this->updated++;
                }
            }
        }
    }

    /**
     * Extract first number from mixed text
     */
    protected function extractNumber(string $text): ?float
    {
        if (preg_match('/(\d+[.,]?\d*)/', $text, $matches)) {
            $number = str_replace(',', '.', $matches[1]);
            return (float) $number;
        }
        return null;
    }
}
