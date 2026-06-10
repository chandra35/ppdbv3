<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\RegistrasiExport;
use App\Imports\RegistrasiImport;
use App\Models\ActivityLog;
use App\Models\CalonSiswa;
use App\Models\Kelulusan;
use App\Models\Registrasi;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RegistrasiController extends Controller
{
    /**
     * Daftar pendaftar yang sudah melakukan registrasi administrasi.
     */
    public function index(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $selectedTahunId = $tahunAktif?->id;

        $query = Registrasi::with(['calonSiswa.gelombangPendaftaran', 'calonSiswa.jalurPendaftaran', 'creator'])
            ->where('tahun_pelajaran_id', $selectedTahunId);

        if ($status = $request->get('match_status')) {
            $query->where('match_status', $status);
        }

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_excel', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('calonSiswa', function ($cs) use ($search) {
                        $cs->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nomor_tes', 'like', "%{$search}%");
                    });
            });
        }

        $registrasis = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Statistik
        $totalLulus = Kelulusan::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('status', 'lulus')->count();
        $totalRegistrasi = Registrasi::where('tahun_pelajaran_id', $selectedTahunId)->count();
        $totalKonflik = Registrasi::where('tahun_pelajaran_id', $selectedTahunId)
            ->where('match_status', 'conflict_jurusan')->count();
        $belumRegistrasi = max(0, $totalLulus - $totalRegistrasi);

        return view('admin.registrasi.index', compact(
            'registrasis',
            'tahunAktif',
            'selectedTahunId',
            'totalLulus',
            'totalRegistrasi',
            'totalKonflik',
            'belumRegistrasi'
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'filterStatus' => $request->get('match_status'),
            'searchQ' => $request->get('q'),
        ]);
    }

    /**
     * Halaman upload file Excel registrasi.
     */
    public function upload(Request $request)
    {
        $context = AdminPpdbContext::resolve($request->get('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];

        return view('admin.registrasi.upload', [
            'tahunAktif' => $tahunAktif,
            'tahunPelajarans' => $context['tahunPelajarans'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
        ]);
    }

    /**
     * Proses upload -> mode preview (smart matching).
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $context = AdminPpdbContext::resolve($request->input('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $file = $request->file('file');
        $token = Str::random(32);
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $extension = $file->getClientOriginalExtension();
        $tempName = "registrasi_{$token}.{$extension}";
        $file->move($tempDir, $tempName);
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempName;

        $importer = new RegistrasiImport($tahunAktif->id);
        $preview = $importer->preview($tempPath);

        // Data preview sudah lengkap di-form, file temp tidak diperlukan lagi.
        @unlink($tempPath);

        return view('admin.registrasi.preview-upload', [
            'preview' => $preview,
            'originalName' => $file->getClientOriginalName(),
            'tahunAktif' => $tahunAktif,
            'returnTahunId' => $request->input('tahun_pelajaran_id'),
        ]);
    }

    /**
     * Simpan hasil registrasi dari preview.
     */
    public function confirmUpload(Request $request)
    {
        $context = AdminPpdbContext::resolve($request->input('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return redirect()->route('admin.registrasi.upload')
                ->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $rows = $request->input('rows', []);
        // Form preview mengirim seluruh baris sebagai satu field JSON ("payload")
        // untuk menghindari batas PHP max_input_vars. Fallback ke array "rows" lama.
        if ($json = $request->input('payload')) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $rows = array_map(function ($r) {
                    $r['include'] = true; // payload hanya berisi baris terpilih
                    return $r;
                }, $decoded);
            }
        }
        $userId = Auth::id();

        $saved = 0;
        $updated = 0;
        $skipped = 0;
        $jurusanUpdated = 0;

        DB::transaction(function () use ($rows, $tahunAktif, $userId, &$saved, &$updated, &$skipped, &$jurusanUpdated) {
            foreach ($rows as $row) {
                $include = !empty($row['include']);
                $calonId = $row['calon_siswa_id'] ?? null;

                if (!$include || empty($calonId)) {
                    $skipped++;
                    continue;
                }

                $calon = CalonSiswa::find($calonId);
                if (!$calon) {
                    $skipped++;
                    continue;
                }

                // Jurusan asal = pilihan_program pendaftar saat ini (sebelum diubah).
                $jurusanAwal = trim((string) $calon->pilihan_program) ?: null;
                $jurusanFinal = trim((string) ($row['jurusan_final'] ?? '')) ?: $jurusanAwal;
                $pindahJurusan = $jurusanAwal && $jurusanFinal
                    && strcasecmp($jurusanAwal, $jurusanFinal) !== 0;

                $existing = Registrasi::where('calon_siswa_id', $calonId)
                    ->where('tahun_pelajaran_id', $tahunAktif->id)
                    ->first();

                $payload = [
                    'tahun_pelajaran_id' => $tahunAktif->id,
                    'notes' => $row['notes'] ?? null,
                    'nama_excel' => $row['nama_excel'] ?? null,
                    'jurusan_excel' => $row['jurusan_excel'] ?? null,
                    'jurusan_awal' => $jurusanAwal,
                    'jurusan_final' => $jurusanFinal,
                    'pindah_jurusan' => $pindahJurusan,
                    'match_status' => $row['match_status'] ?? 'manual',
                    'match_score' => (int) ($row['match_score'] ?? 0),
                    'tanggal_registrasi' => now(),
                    'created_by' => $userId,
                ];

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Registrasi::create($payload + ['calon_siswa_id' => $calonId]);
                    $saved++;
                }

                // Jurusan final (dari Excel) jadi acuan akhir: perbarui pilihan_program
                // pendaftar bila berbeda, dan catat jejak perpindahannya ke ActivityLog.
                if ($pindahJurusan) {
                    $calon->pilihan_program = $jurusanFinal;
                    $calon->save();
                    $jurusanUpdated++;

                    ActivityLog::log(
                        'update',
                        "Perpindahan jurusan saat registrasi: {$calon->nama_lengkap} ({$calon->nomor_tes}) "
                            . "dari \"{$jurusanAwal}\" menjadi \"{$jurusanFinal}\".",
                        $calon,
                        ['pilihan_program' => $jurusanAwal],
                        ['pilihan_program' => $jurusanFinal]
                    );
                }
            }
        });

        $message = "Registrasi tersimpan: {$saved} baru, {$updated} diperbarui.";
        if ($jurusanUpdated > 0) {
            $message .= " {$jurusanUpdated} pendaftar pindah jurusan (tercatat di Activity Log).";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati.";
        }

        return redirect()->route('admin.registrasi.index', [
            'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
        ])->with('success', $message);
    }

    /**
     * Endpoint pencarian kandidat (Select2 AJAX) — pendaftar LULUS pada tahun aktif.
     */
    public function searchCandidates(Request $request)
    {
        $context = AdminPpdbContext::resolve($request->get('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return response()->json(['results' => []]);
        }

        $term = trim((string) $request->get('q'));

        $lulusIds = Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)
            ->where('status', 'lulus')
            ->pluck('calon_siswa_id')
            ->flip();

        $query = CalonSiswa::with('gelombangPendaftaran:id,nama')
            ->where('tahun_pelajaran_id', $tahunAktif->id);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('nama_lengkap', 'like', "%{$term}%")
                    ->orWhere('nomor_tes', 'like', "%{$term}%");
            });
        }

        $registeredIds = Registrasi::where('tahun_pelajaran_id', $tahunAktif->id)
            ->whereNotNull('calon_siswa_id')
            ->pluck('calon_siswa_id')
            ->flip();

        $results = $query->orderBy('nama_lengkap')
            ->limit(30)
            ->get(['id', 'nomor_tes', 'nama_lengkap', 'pilihan_program', 'gelombang_pendaftaran_id'])
            ->map(function ($s) use ($registeredIds, $lulusIds) {
                $reg = isset($registeredIds[$s->id]) ? ' [terdaftar]' : '';
                $lulus = isset($lulusIds[$s->id]) ? ' [lulus]' : ' [belum lulus]';
                $prog = $s->pilihan_program ?: '-';
                $tes = $s->nomor_tes ?: '-';
                return [
                    'id' => $s->id,
                    'text' => "{$s->nama_lengkap} ({$tes}) · {$prog}{$lulus}{$reg}",
                    'nama_lengkap' => $s->nama_lengkap,
                    'nomor_tes' => $s->nomor_tes,
                    'pilihan_program' => $s->pilihan_program,
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Export Excel lengkap pendaftar yang sudah registrasi/bayar.
     */
    public function export(Request $request)
    {
        $context = AdminPpdbContext::resolve($request->get('tahun_pelajaran_id'));
        $tahunAktif = $context['selectedTahun'];
        $tahunLabel = $tahunAktif ? str_replace('/', '-', $tahunAktif->nama) : date('Y');

        $filename = "Data_Registrasi_PPDB_{$tahunLabel}.xlsx";

        // Samakan dengan halaman Data Registrasi: hanya filter per tahun.
        // Jalur/gelombang hanya diterapkan bila DIPILIH eksplisit di query string.
        return Excel::download(
            new RegistrasiExport(
                $tahunAktif?->id,
                $request->get('jalur_id') ?: null,
                $request->get('gelombang_id') ?: null,
                $request->get('match_status') ?: null,
                trim((string) $request->get('q')) ?: null
            ),
            $filename
        );
    }

    /**
     * Hapus satu data registrasi.
     */
    public function destroy(Request $request, Registrasi $registrasi)
    {
        $registrasi->delete();

        return back()->with('success', 'Data registrasi berhasil dihapus.');
    }
}
