<?php

namespace App\Imports;

use App\Models\CalonSiswa;
use App\Models\Kelulusan;
use App\Models\Registrasi;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import & smart-matching data Registrasi.
 *
 * Format Excel: A=No, B=Notes (4 digit akhir nomor tes), C=Nama, D=Jurusan
 * Mencocokkan tiap baris ke CalonSiswa yang berstatus LULUS pada tahun aktif
 * berdasarkan kombinasi nomor tes (4 digit), kemiripan nama, dan jurusan.
 */
class RegistrasiImport
{
    protected string $tahunPelajaranId;

    /** @var array<int,object> */
    protected array $candidates = [];

    /** @var array<string,bool> calon_siswa_id => true (sudah teregistrasi) */
    protected array $alreadyRegistered = [];

    public function __construct(string $tahunPelajaranId)
    {
        $this->tahunPelajaranId = $tahunPelajaranId;
    }

    /**
     * Parse file Excel + jalankan smart matching, mengembalikan baris preview.
     */
    public function preview(string $filePath): array
    {
        $this->loadCandidates();

        $rows = $this->parseRows($filePath);
        $previewRows = [];
        $index = 0;

        foreach ($rows as $raw) {
            $previewRows[] = $this->matchRow($index++, $raw);
        }

        return [
            'rows' => $previewRows,
            'summary' => [
                'total' => count($previewRows),
                'exact' => collect($previewRows)->where('match_status', 'matched_exact')->count(),
                'fuzzy' => collect($previewRows)->where('match_status', 'matched_fuzzy')->count(),
                'conflict' => collect($previewRows)->where('match_status', 'conflict_jurusan')->count(),
                'unmatched' => collect($previewRows)->where('match_status', 'unmatched')->count(),
                'duplicate' => collect($previewRows)->where('already_registered', true)->count(),
            ],
        ];
    }

    /**
     * Muat kandidat = pendaftar yang LULUS pada tahun aktif.
     */
    protected function loadCandidates(): void
    {
        $lulusIds = Kelulusan::where('tahun_pelajaran_id', $this->tahunPelajaranId)
            ->where('status', 'lulus')
            ->pluck('calon_siswa_id')
            ->all();

        $siswa = CalonSiswa::with('gelombangPendaftaran:id,nama')
            ->whereIn('id', $lulusIds)
            ->get(['id', 'nomor_tes', 'nama_lengkap', 'pilihan_program', 'gelombang_pendaftaran_id', 'nisn']);

        $this->candidates = $siswa->map(function ($s) {
            return (object) [
                'id' => $s->id,
                'nomor_tes' => $s->nomor_tes,
                'last4' => $this->last4($s->nomor_tes),
                'nama_lengkap' => $s->nama_lengkap,
                'nama_norm' => $this->normalizeName($s->nama_lengkap),
                'pilihan_program' => $s->pilihan_program,
                'nisn' => $s->nisn,
                'gelombang' => $s->gelombangPendaftaran?->nama,
            ];
        })->all();

        $this->alreadyRegistered = Registrasi::where('tahun_pelajaran_id', $this->tahunPelajaranId)
            ->whereNotNull('calon_siswa_id')
            ->pluck('calon_siswa_id')
            ->mapWithKeys(fn ($id) => [$id => true])
            ->all();
    }

    /**
     * Baca baris data dari Excel (A=No, B=Notes, C=Nama, D=Jurusan).
     */
    protected function parseRows(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $rows = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $no = trim((string) $sheet->getCell("A{$row}")->getValue());
            $notes = trim((string) $sheet->getCell("B{$row}")->getValue());
            $nama = trim((string) $sheet->getCell("C{$row}")->getValue());
            $jurusan = trim((string) $sheet->getCell("D{$row}")->getValue());

            // Lewati baris kosong
            if ($no === '' && $notes === '' && $nama === '' && $jurusan === '') {
                continue;
            }

            // Lewati baris header
            $namaLower = strtolower($nama);
            if (in_array($namaLower, ['nama', 'nama lengkap', 'nama pendaftar'], true)) {
                continue;
            }
            if ($nama === '') {
                continue;
            }

            $rows[] = [
                'baris' => $row,
                'no' => $no,
                'notes' => $notes,
                'nama' => $nama,
                'jurusan' => $jurusan,
            ];
        }

        return $rows;
    }

    /**
     * Smart matching satu baris Excel terhadap daftar kandidat lulus.
     */
    protected function matchRow(int $index, array $raw): array
    {
        $notesExcel = $this->last4($raw['notes']);
        $namaNorm = $this->normalizeName($raw['nama']);
        $jurusanExcel = trim($raw['jurusan']);

        $scored = [];
        foreach ($this->candidates as $cand) {
            $nameScore = $this->nameSimilarity($namaNorm, $cand->nama_norm);
            $noteMatch = $notesExcel !== '' && $cand->last4 !== '' && $notesExcel === $cand->last4;

            // Skor gabungan: nomor tes adalah sinyal terkuat
            $confidence = $nameScore;
            if ($noteMatch) {
                $confidence = min(100, (int) round($nameScore * 0.4) + 60);
            }

            $scored[] = (object) [
                'cand' => $cand,
                'name_score' => $nameScore,
                'note_match' => $noteMatch,
                'confidence' => $confidence,
            ];
        }

        // Urutkan: note match dulu, lalu confidence
        usort($scored, function ($a, $b) {
            if ($a->note_match !== $b->note_match) {
                return $b->note_match <=> $a->note_match;
            }
            return $b->confidence <=> $a->confidence;
        });

        $best = $scored[0] ?? null;
        $candidatesList = [];
        foreach (array_slice($scored, 0, 10) as $s) {
            if ($s->name_score < 25 && !$s->note_match) {
                continue;
            }
            $candidatesList[] = [
                'id' => $s->cand->id,
                'nomor_tes' => $s->cand->nomor_tes,
                'nama_lengkap' => $s->cand->nama_lengkap,
                'pilihan_program' => $s->cand->pilihan_program,
                'gelombang' => $s->cand->gelombang,
                'name_score' => $s->name_score,
                'note_match' => $s->note_match,
                'already_registered' => isset($this->alreadyRegistered[$s->cand->id]),
            ];
        }

        // Tentukan status match
        $matchStatus = 'unmatched';
        $selectedId = null;
        $selectedProgram = null;
        $matchScore = 0;
        $issues = [];
        $alreadyRegistered = false;

        if ($best && ($best->note_match || $best->name_score >= 60)) {
            $selectedId = $best->cand->id;
            $selectedProgram = $best->cand->pilihan_program;
            $matchScore = $best->confidence;
            $alreadyRegistered = isset($this->alreadyRegistered[$selectedId]);

            $jurusanMatch = $this->jurusanMatch($jurusanExcel, $best->cand->pilihan_program);

            if ($best->note_match && $best->name_score >= 80) {
                if ($jurusanMatch) {
                    $matchStatus = 'matched_exact';
                } else {
                    $matchStatus = 'conflict_jurusan';
                    $issues[] = "Jurusan Excel \"{$jurusanExcel}\" berbeda dari data pendaftar \"" . ($best->cand->pilihan_program ?: '-') . "\" (kemungkinan pindah jurusan).";
                }
            } elseif ($best->note_match) {
                $matchStatus = $jurusanMatch ? 'matched_fuzzy' : 'conflict_jurusan';
                $issues[] = "Nomor tes cocok, tetapi nama hanya mirip {$best->name_score}%. Mohon periksa.";
                if (!$jurusanMatch) {
                    $issues[] = "Jurusan berbeda: Excel \"{$jurusanExcel}\" vs pendaftar \"" . ($best->cand->pilihan_program ?: '-') . "\".";
                }
            } else {
                // Hanya kemiripan nama (tanpa nomor tes)
                if ($best->name_score >= 85) {
                    $matchStatus = $jurusanMatch ? 'matched_fuzzy' : 'conflict_jurusan';
                } else {
                    $matchStatus = 'matched_fuzzy';
                }
                $issues[] = "Tidak ada nomor tes yang cocok; dicocokkan dari nama (kemiripan {$best->name_score}%).";
                if (!$jurusanMatch) {
                    $issues[] = "Jurusan berbeda: Excel \"{$jurusanExcel}\" vs pendaftar \"" . ($best->cand->pilihan_program ?: '-') . "\".";
                }
            }
        } else {
            $issues[] = 'Tidak ditemukan pendaftar lulus yang cocok. Pilih manual atau lewati.';
        }

        if ($alreadyRegistered) {
            $issues[] = 'Pendaftar ini sudah pernah diregistrasi pada tahun ini (akan diperbarui jika disimpan).';
        }

        $status = match ($matchStatus) {
            'matched_exact' => 'valid',
            'matched_fuzzy', 'conflict_jurusan' => 'warning',
            default => 'error',
        };

        return [
            'index' => $index,
            'baris' => $raw['baris'],
            'no' => $raw['no'],
            'notes' => $raw['notes'],
            'nama_excel' => $raw['nama'],
            'jurusan_excel' => $jurusanExcel,
            'match_status' => $matchStatus,
            'match_score' => $matchScore,
            'status' => $status,
            'selected_id' => $selectedId,
            'selected_program' => $selectedProgram,
            'jurusan_awal' => $selectedProgram,
            'jurusan_final' => $jurusanExcel !== '' ? $jurusanExcel : $selectedProgram,
            'candidates' => $candidatesList,
            'already_registered' => $alreadyRegistered,
            'issues' => $issues,
        ];
    }

    /**
     * Ambil 4 digit terakhir dari nomor tes (abaikan non-digit).
     */
    protected function last4(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        if ($digits === '' || $digits === null) {
            return '';
        }
        return substr($digits, -4);
    }

    /**
     * Normalisasi nama untuk perbandingan.
     */
    protected function normalizeName(?string $name): string
    {
        $name = strtoupper(trim((string) $name));
        // Hilangkan gelar/karakter non-huruf, rapikan spasi
        $name = preg_replace('/[^A-Z\s]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    /**
     * Kemiripan dua nama (0-100).
     *
     * Menggabungkan dua pendekatan dan mengambil skor tertinggi:
     *  1. similar_text pada string penuh (peka urutan karakter).
     *  2. Token/kata: tiap kata pada nama lebih pendek dicocokkan ke kata
     *     terbaik pada nama lain (tahan terhadap urutan kata yang berbeda,
     *     nama tertukar, atau salah ketik ringan).
     */
    protected function nameSimilarity(string $a, string $b): int
    {
        if ($a === '' || $b === '') {
            return 0;
        }
        if ($a === $b) {
            return 100;
        }

        // Pendekatan 1: string penuh
        $full = 0.0;
        similar_text($a, $b, $full);

        // Pendekatan 2: berbasis token (urutan kata diabaikan)
        $token = $this->tokenSimilarity($a, $b);

        return (int) round(max($full, $token));
    }

    /**
     * Kemiripan berbasis kata: untuk tiap kata pada nama yang lebih sedikit
     * katanya, ambil kecocokan terbaik di nama lain, lalu rata-rata.
     */
    protected function tokenSimilarity(string $a, string $b): float
    {
        $wa = array_values(array_filter(explode(' ', $a)));
        $wb = array_values(array_filter(explode(' ', $b)));
        if (empty($wa) || empty($wb)) {
            return 0.0;
        }

        // Pastikan $wa adalah set yang lebih kecil
        if (count($wa) > count($wb)) {
            [$wa, $wb] = [$wb, $wa];
        }

        $total = 0.0;
        foreach ($wa as $word) {
            $best = 0.0;
            foreach ($wb as $cand) {
                if ($word === $cand) {
                    $best = 100.0;
                    break;
                }
                $p = 0.0;
                similar_text($word, $cand, $p);
                if ($p > $best) {
                    $best = $p;
                }
            }
            $total += $best;
        }

        return $total / count($wa);
    }

    /**
     * Cek kecocokan jurusan (normalisasi). Jika pendaftar belum punya jurusan, dianggap cocok.
     */
    protected function jurusanMatch(string $excel, ?string $program): bool
    {
        $e = strtoupper(trim($excel));
        $p = strtoupper(trim((string) $program));
        if ($e === '' || $p === '') {
            return true;
        }
        return $e === $p;
    }
}
