<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\RegistrasiImport;
use App\Models\CalonSiswa;
use App\Models\Kelulusan;
use App\Models\Registrasi;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

                $jurusanFinal = trim((string) ($row['jurusan_final'] ?? '')) ?: null;

                $existing = Registrasi::where('calon_siswa_id', $calonId)
                    ->where('tahun_pelajaran_id', $tahunAktif->id)
                    ->first();

                $payload = [
                    'tahun_pelajaran_id' => $tahunAktif->id,
                    'notes' => $row['notes'] ?? null,
                    'nama_excel' => $row['nama_excel'] ?? null,
                    'jurusan_excel' => $row['jurusan_excel'] ?? null,
                    'jurusan_final' => $jurusanFinal,
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

                // Jurusan Excel jadi acuan akhir: perbarui pilihan_program pendaftar bila berbeda.
                if ($jurusanFinal && strcasecmp(trim((string) $calon->pilihan_program), $jurusanFinal) !== 0) {
                    $calon->pilihan_program = $jurusanFinal;
                    $calon->save();
                    $jurusanUpdated++;
                }
            }
        });

        $message = "Registrasi tersimpan: {$saved} baru, {$updated} diperbarui.";
        if ($jurusanUpdated > 0) {
            $message .= " {$jurusanUpdated} jurusan pendaftar diperbarui.";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati.";
        }

        return redirect()->route('admin.registrasi.index', [
            'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
        ])->with('success', $message);
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
