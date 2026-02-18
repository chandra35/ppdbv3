<?php

namespace App\Imports;

use App\Models\CalonSiswa;
use App\Models\NilaiCbt;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiCbtImport
{
    protected int $imported = 0;
    protected int $updated = 0;
    protected int $skipped = 0;
    protected array $errors = [];
    protected array $previewRows = [];
    protected bool $previewMode = false;
    protected string $tahunPelajaranId;
    protected string $mapelField;
    protected string $mapelLabel;

    /**
     * Format Excel per-mapel: A=Nama, B=NISN, C=Nilai
     */
    public function __construct(string $tahunPelajaranId, string $mapelField)
    {
        $this->tahunPelajaranId = $tahunPelajaranId;
        $this->mapelField = $mapelField;
        $this->mapelLabel = NilaiCbt::komponenList()[$mapelField] ?? $mapelField;
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
            'mapel_field' => $this->mapelField,
            'mapel_label' => $this->mapelLabel,
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
     * Format: A=Nama, B=NISN, C=Nilai
     */
    protected function processFile(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Find data start row - cari baris pertama yang kolom B berisi NISN (angka panjang)
        $dataStartRow = null;
        for ($row = 1; $row <= min($highestRow, 20); $row++) {
            $cellA = trim((string) $sheet->getCell("A{$row}")->getValue());
            $cellB = trim((string) $sheet->getCell("B{$row}")->getValue());

            // Data row: A=nama (non-empty text), B=NISN (numeric string)
            if (!empty($cellA) && !empty($cellB) && is_numeric($cellB) && strlen($cellB) >= 6) {
                $dataStartRow = $row;
                break;
            }
        }

        if (!$dataStartRow) {
            $this->errors[] = 'Tidak ditemukan data peserta. Pastikan format: Kolom A=Nama, B=NISN, C=Nilai.';
            return;
        }

        $user = Auth::user();

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $namaLengkap = trim((string) $sheet->getCell("A{$row}")->getValue());
            $nisn = trim((string) $sheet->getCell("B{$row}")->getValue());
            $cellValue = $sheet->getCell("C{$row}")->getValue();

            // Stop if no more data
            if (empty($nisn) && empty($namaLengkap)) {
                break;
            }

            $previewRow = null;
            if ($this->previewMode) {
                $previewRow = [
                    'baris' => $row,
                    'nama_excel' => $namaLengkap,
                    'nisn' => $nisn,
                    'nama_lengkap' => $namaLengkap,
                    'nomor_tes' => null,
                    'nilai_raw' => $cellValue,
                    'nilai_parsed' => null,
                    'cell_type' => 'valid',
                    'status' => 'valid',
                    'action' => 'baru',
                    'issues' => [],
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
                    $previewRow['issues'][] = "NISN '{$nisn}' tidak terdaftar";
                    $this->previewRows[] = $previewRow;
                }
                continue;
            }

            if ($this->previewMode) {
                $previewRow['nama_lengkap'] = $calonSiswa->nama_lengkap;
                $previewRow['nomor_tes'] = $calonSiswa->nomor_tes;
            }

            // Parse nilai
            $parsedVal = null;
            $cellType = 'empty';

            if ($cellValue === null || $cellValue === '') {
                $cellType = 'empty';
            } elseif (is_numeric($cellValue)) {
                $parsedVal = round((float) $cellValue, 2);
                if ($parsedVal < 0 || $parsedVal > 100) {
                    $originalVal = $parsedVal;
                    $parsedVal = max(0, min(100, $parsedVal));
                    $cellType = 'warning';
                    if ($this->previewMode) {
                        $previewRow['issues'][] = "Nilai {$originalVal} di luar 0-100, di-cap menjadi {$parsedVal}";
                    }
                } else {
                    $cellType = 'valid';
                }
            } else {
                $extracted = $this->extractNumber($cellValue);
                if ($extracted !== null) {
                    $parsedVal = max(0, min(100, round($extracted, 2)));
                    $cellType = 'extracted';
                    if ($this->previewMode) {
                        $previewRow['issues'][] = "\"{$cellValue}\" → diambil angka {$parsedVal}";
                    }
                } else {
                    $cellType = 'invalid';
                    if ($this->previewMode) {
                        $previewRow['issues'][] = "\"{$cellValue}\" tidak mengandung angka";
                    }
                }
            }

            if ($parsedVal === null) {
                $this->skipped++;
                if ($this->previewMode) {
                    $previewRow['status'] = $cellType === 'empty' ? 'skip' : 'error';
                    $previewRow['cell_type'] = $cellType;
                    $previewRow['issues'][] = $cellType === 'empty' ? 'Nilai kosong' : 'Tidak bisa diambil nilainya';
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
                $previewRow['nilai_parsed'] = $parsedVal;
                $previewRow['cell_type'] = $cellType;
                if ($cellType === 'warning' || $cellType === 'extracted') {
                    $previewRow['status'] = 'warning';
                }
                // Jika update, tampilkan nilai lama
                if (!$isNew && $nilaiCbt->{$this->mapelField} !== null) {
                    $previewRow['nilai_lama'] = (float) $nilaiCbt->{$this->mapelField};
                    $previewRow['issues'][] = "Nilai lama: {$previewRow['nilai_lama']} → {$parsedVal}";
                }
                $this->previewRows[] = $previewRow;
            } else {
                $nilaiCbt = $nilaiCbt ?? new NilaiCbt([
                    'calon_siswa_id' => $calonSiswa->id,
                    'tahun_pelajaran_id' => $this->tahunPelajaranId,
                    'uploaded_by' => $user->id,
                ]);

                $nilaiCbt->uploaded_by = $user->id;
                $nilaiCbt->{$this->mapelField} = $parsedVal;
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
