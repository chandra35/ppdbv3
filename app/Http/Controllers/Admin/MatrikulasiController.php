<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapNilaiExport;
use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\MatrikulasiPeserta;
use App\Support\AdminPpdbContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MatrikulasiController extends Controller
{
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id', 'all'),
            $request->get('gelombang_id', 'all')
        );

        return view('admin.matrikulasi.index', [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahun' => $context['selectedTahun'],
            'selectedJalur' => $context['selectedJalur'],
            'selectedGelombang' => $context['selectedGelombang'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'storedStats' => $this->storedStats($context['selectedTahunIdInput']),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'names' => ['required', 'string'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajarans,id'],
            'jalur_id' => ['nullable', 'exists:jalur_pendaftaran,id'],
            'gelombang_id' => ['nullable', 'exists:gelombang_pendaftaran,id'],
            'include_all_year' => ['nullable', 'boolean'],
        ]);

        $lines = $this->parseLines($validated['names']);
        $candidates = $this->candidatePool($validated, (bool) ($validated['include_all_year'] ?? false));
        $matches = $this->matchLines($lines, $candidates);

        return response()->json([
            'success' => true,
            'data' => [
                'total_lines' => count($lines),
                'found' => $matches->where('status', 'found')->count(),
                'not_found' => $matches->where('status', 'not_found')->count(),
                'duplicate' => $matches->where('status', 'duplicate')->count(),
                'matches' => $matches->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'names' => ['required', 'string'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajarans,id'],
            'jalur_id' => ['nullable', 'exists:jalur_pendaftaran,id'],
            'gelombang_id' => ['nullable', 'exists:gelombang_pendaftaran,id'],
            'include_all_year' => ['nullable', 'boolean'],
            'kategori' => ['nullable', 'in:reguler,asrama'],
            'is_smart_q' => ['nullable', 'boolean'],
        ]);

        $lines = $this->parseLines($validated['names']);
        $candidates = $this->candidatePool($validated, (bool) ($validated['include_all_year'] ?? false));
        $matches = $this->matchLines($lines, $candidates);
        $found = $matches->where('status', 'found');
        $saved = 0;

        foreach ($found as $match) {
            $candidate = (object) $match['candidate'];
            $existing = MatrikulasiPeserta::where('calon_siswa_id', $candidate->id)->first();
            $markSmartQ = (bool) ($validated['is_smart_q'] ?? false);
            $kategori = $markSmartQ
                ? $existing?->kategori
                : (($validated['kategori'] ?? null) ?: $existing?->kategori);

            MatrikulasiPeserta::updateOrCreate(
                ['calon_siswa_id' => $candidate->id],
                [
                    'tahun_pelajaran_id' => $candidate->tahun_pelajaran_id ?? ($validated['tahun_pelajaran_id'] ?? null),
                    'jalur_pendaftaran_id' => $candidate->jalur_pendaftaran_id ?? null,
                    'gelombang_pendaftaran_id' => $candidate->gelombang_pendaftaran_id ?? null,
                    'kategori' => $kategori ?: $existing?->kategori,
                    'is_smart_q' => $markSmartQ ? true : (bool) ($existing?->is_smart_q ?? false),
                    'input_text' => $match['input'],
                    'match_score' => (int) $match['score'],
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]
            );
            $saved++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$saved} peserta matrikulasi disimpan.",
            'data' => [
                'saved' => $saved,
                'not_found' => $matches->where('status', 'not_found')->count(),
                'duplicate' => $matches->where('status', 'duplicate')->count(),
                'stats' => $this->storedStats($validated['tahun_pelajaran_id'] ?? null),
            ],
        ]);
    }

    public function export(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $validated = $request->validate([
            'names' => ['required', 'string'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajarans,id'],
            'jalur_id' => ['nullable', 'exists:jalur_pendaftaran,id'],
            'gelombang_id' => ['nullable', 'exists:gelombang_pendaftaran,id'],
            'include_all_year' => ['nullable', 'boolean'],
        ]);

        $lines = $this->parseLines($validated['names']);
        $candidates = $this->candidatePool($validated, (bool) ($validated['include_all_year'] ?? false));
        $matches = $this->matchLines($lines, $candidates);
        $ids = $matches
            ->where('status', 'found')
            ->pluck('candidate.id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return back()->withInput()->with('error', 'Tidak ada pendaftar yang berhasil ditemukan untuk diexport.');
        }

        $tahun = (bool) ($validated['include_all_year'] ?? false) ? null : ($validated['tahun_pelajaran_id'] ?? null);
        $tahunLabel = str_replace(['/', '\\'], '-', $request->get('tahun_label', 'matrikulasi'));
        $filename = 'Matrikulasi_PPDB_' . $tahunLabel . '_' . count($ids) . '_pendaftar_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RekapNilaiExport($tahun, null, null, $ids), $filename);
    }

    public function exportStored(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $validated = $request->validate([
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajarans,id'],
            'jalur_id' => ['nullable', 'exists:jalur_pendaftaran,id'],
            'gelombang_id' => ['nullable', 'exists:gelombang_pendaftaran,id'],
            'kategori' => ['nullable', 'in:reguler,asrama'],
            'smart_q' => ['nullable', 'in:all,yes,no'],
            'tahun_label' => ['nullable', 'string', 'max:50'],
        ]);

        $query = MatrikulasiPeserta::query()
            ->when(!empty($validated['tahun_pelajaran_id']), fn ($q) => $q->where('tahun_pelajaran_id', $validated['tahun_pelajaran_id']))
            ->when(!empty($validated['jalur_id']), fn ($q) => $q->where('jalur_pendaftaran_id', $validated['jalur_id']))
            ->when(!empty($validated['gelombang_id']), fn ($q) => $q->where('gelombang_pendaftaran_id', $validated['gelombang_id']))
            ->when(!empty($validated['kategori']), fn ($q) => $q->where('kategori', $validated['kategori']))
            ->when(($validated['smart_q'] ?? 'all') === 'yes', fn ($q) => $q->where('is_smart_q', true))
            ->when(($validated['smart_q'] ?? 'all') === 'no', fn ($q) => $q->where('is_smart_q', false));

        $ids = $query->orderBy('created_at')->pluck('calon_siswa_id')->all();

        if (empty($ids)) {
            return back()->withInput()->with('error', 'Belum ada data matrikulasi tersimpan untuk diexport.');
        }

        $tahunLabel = str_replace(['/', '\\'], '-', $validated['tahun_label'] ?? 'matrikulasi');
        $filename = 'Matrikulasi_PPDB_Tersimpan_' . $tahunLabel . '_' . count($ids) . '_pendaftar_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new RekapNilaiExport($validated['tahun_pelajaran_id'] ?? null, null, null, $ids), $filename);
    }

    private function candidatePool(array $filters, bool $includeAllYear): Collection
    {
        return CalonSiswa::with([
            'jalurPendaftaran',
            'gelombangPendaftaran',
            'nilaiRapor',
            'nilaiCbt',
            'matrikulasiPeserta',
        ])
            ->when(!$includeAllYear && !empty($filters['tahun_pelajaran_id']), fn ($q) => $q->where('tahun_pelajaran_id', $filters['tahun_pelajaran_id']))
            ->when(!empty($filters['jalur_id']), fn ($q) => $q->where('jalur_pendaftaran_id', $filters['jalur_id']))
            ->when(!empty($filters['gelombang_id']), fn ($q) => $q->where('gelombang_pendaftaran_id', $filters['gelombang_id']))
            ->get()
            ->map(function (CalonSiswa $candidate) {
                $candidate->match_normalized_name = $this->normalizeName($candidate->nama_lengkap);
                $candidate->match_tokens = $this->tokens($candidate->nama_lengkap);

                return $candidate;
            });
    }

    private function matchLines(array $lines, Collection $candidates): Collection
    {
        $usedIds = [];

        return collect($lines)->map(function (string $line, int $index) use ($candidates, &$usedIds) {
            $match = $this->findBestCandidate($line, $candidates, $usedIds);

            if (!$match) {
                return [
                    'row' => $index + 1,
                    'input' => $line,
                    'status' => 'not_found',
                    'score' => 0,
                    'candidate' => null,
                ];
            }

            $candidate = $match['candidate'];
            $status = in_array($candidate->id, $usedIds, true) ? 'duplicate' : 'found';
            if ($status === 'found') {
                $usedIds[] = $candidate->id;
            }

            return [
                'row' => $index + 1,
                'input' => $line,
                'status' => $status,
                'score' => $match['score'],
                'candidate' => [
                    'id' => $candidate->id,
                    'tahun_pelajaran_id' => $candidate->tahun_pelajaran_id,
                    'jalur_pendaftaran_id' => $candidate->jalur_pendaftaran_id,
                    'gelombang_pendaftaran_id' => $candidate->gelombang_pendaftaran_id,
                    'nama_lengkap' => $candidate->nama_lengkap,
                    'nisn' => $candidate->nisn,
                    'nomor_tes' => $candidate->nomor_tes,
                    'nomor_registrasi' => $candidate->nomor_registrasi,
                    'jalur' => $candidate->jalurPendaftaran?->nama,
                    'gelombang' => $candidate->gelombangPendaftaran?->nama,
                    'status_admisi' => $candidate->status_admisi,
                    'rapor_count' => $candidate->nilaiRapor->count(),
                    'has_cbt' => (bool) $candidate->nilaiCbt,
                    'matrikulasi' => $candidate->matrikulasiPeserta ? [
                        'kategori' => $candidate->matrikulasiPeserta->kategori,
                        'is_smart_q' => $candidate->matrikulasiPeserta->is_smart_q,
                    ] : null,
                ],
            ];
        });
    }

    private function storedStats(?string $tahunPelajaranId): array
    {
        $query = MatrikulasiPeserta::query()
            ->when($tahunPelajaranId && $tahunPelajaranId !== 'all', fn ($q) => $q->where('tahun_pelajaran_id', $tahunPelajaranId));

        return [
            'total' => (clone $query)->count(),
            'reguler' => (clone $query)->where('kategori', 'reguler')->count(),
            'asrama' => (clone $query)->where('kategori', 'asrama')->count(),
            'smart_q' => (clone $query)->where('is_smart_q', true)->count(),
        ];
    }

    private function findBestCandidate(string $line, Collection $candidates, array $usedIds): ?array
    {
        $needle = trim($line);
        $digits = preg_replace('/\D+/', '', $needle);
        $normalized = $this->normalizeName($needle);
        $tokens = $this->tokens($needle);

        if ($digits !== '') {
            $byNumber = $candidates->first(fn ($candidate) => in_array($digits, [
                preg_replace('/\D+/', '', (string) $candidate->nisn),
                preg_replace('/\D+/', '', (string) $candidate->nomor_tes),
                preg_replace('/\D+/', '', (string) $candidate->nomor_registrasi),
            ], true));

            if ($byNumber) {
                return ['candidate' => $byNumber, 'score' => 100];
            }
        }

        $exact = $candidates->first(fn ($candidate) => $candidate->match_normalized_name === $normalized);
        if ($exact) {
            return ['candidate' => $exact, 'score' => 100];
        }

        $best = null;
        foreach ($candidates as $candidate) {
            $score = $this->nameScore($normalized, $tokens, $candidate->match_normalized_name, $candidate->match_tokens);
            if (in_array($candidate->id, $usedIds, true)) {
                $score -= 8;
            }

            if (!$best || $score > $best['score']) {
                $best = ['candidate' => $candidate, 'score' => $score];
            }
        }

        return $best && $best['score'] >= 72 ? $best : null;
    }

    private function nameScore(string $needle, array $needleTokens, string $candidate, array $candidateTokens): int
    {
        similar_text($needle, $candidate, $percent);

        $tokenHits = 0;
        foreach ($needleTokens as $token) {
            foreach ($candidateTokens as $candidateToken) {
                if ($token === $candidateToken || str_starts_with($candidateToken, $token) || str_starts_with($token, $candidateToken)) {
                    $tokenHits++;
                    break;
                }
            }
        }

        $tokenScore = count($needleTokens) > 0 ? ($tokenHits / count($needleTokens)) * 100 : 0;
        $levenshteinScore = 0;
        $maxLength = max(strlen($needle), strlen($candidate));
        if ($maxLength > 0) {
            $levenshteinScore = max(0, 100 - ((levenshtein($needle, $candidate) / $maxLength) * 100));
        }

        return (int) round(($percent * 0.45) + ($tokenScore * 0.4) + ($levenshteinScore * 0.15));
    }

    private function parseLines(string $value): array
    {
        $lines = preg_split('/\R+/', $value) ?: [];

        return collect($lines)
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', (string) $line)))
            ->filter()
            ->values()
            ->all();
    }

    private function tokens(?string $value): array
    {
        return array_values(array_filter(explode(' ', $this->normalizeName($value)), fn ($token) => strlen($token) > 1));
    }

    private function normalizeName(?string $value): string
    {
        $value = strtolower((string) $value);
        $value = str_replace(["'", '`', '’'], '', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
