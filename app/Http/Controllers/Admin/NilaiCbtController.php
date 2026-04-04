<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\NilaiCbtImport;
use App\Models\NilaiCbt;
use App\Models\TahunPelajaran;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NilaiCbtController extends Controller
{
    /**
     * Index - rekap data CBT
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

        $query = NilaiCbt::with('calonSiswa')
            ->where('tahun_pelajaran_id', $selectedTahunId);

        if ($context['jalurFilterId']) {
            $query->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('jalur_pendaftaran_id', $context['jalurFilterId']);
            });
        }

        if ($context['gelombangFilterId']) {
            $query->whereHas('calonSiswa', function ($q) use ($context) {
                $q->where('gelombang_pendaftaran_id', $context['gelombangFilterId']);
            });
        }

        $data = $query->orderBy('rata_rata', 'desc')->get();

        // Hitung progress per mapel
        $komponenList = NilaiCbt::komponenList();
        $totalPeserta = $data->count();
        $mapelProgress = [];
        foreach ($komponenList as $field => $label) {
            $filled = $data->whereNotNull($field)->count();
            $mapelProgress[$field] = [
                'label' => $label,
                'filled' => $filled,
                'total' => $totalPeserta,
                'percent' => $totalPeserta > 0 ? round($filled / $totalPeserta * 100) : 0,
            ];
        }

        return view('admin.nilai-cbt.index', compact(
            'data',
            'tahunAktif',
            'selectedTahunId',
            'mapelProgress'
        ) + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Upload form - pilih mapel dulu
     */
    public function upload(Request $request)
    {
        $context = AdminPpdbContext::resolve(
            $request->get('tahun_pelajaran_id'),
            $request->get('jalur_id'),
            $request->get('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        $komponenList = NilaiCbt::komponenList();
        $selectedMapel = $request->mapel;

        return view('admin.nilai-cbt.upload', compact('tahunAktif', 'komponenList', 'selectedMapel') + [
            'tahunPelajarans' => $context['tahunPelajarans'],
            'jalurs' => $context['jalurs'],
            'gelombangs' => $context['gelombangs'],
            'selectedTahunIdInput' => $context['selectedTahunIdInput'],
            'selectedJalurIdInput' => $context['selectedJalurIdInput'],
            'selectedGelombangIdInput' => $context['selectedGelombangIdInput'],
            'contextInfo' => [
                'tahun' => $context['selectedTahun']?->nama ?? '-',
                'jalur' => $context['selectedJalur']?->nama ?? 'Semua Jalur',
                'gelombang' => $context['selectedGelombang']?->nama ?? 'Semua Gelombang',
            ],
        ]);
    }

    /**
     * Process upload - preview mode
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable',
            'jalur_id' => 'nullable',
            'gelombang_id' => 'nullable',
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'mapel' => 'required|in:nilai_mtk,nilai_ipa,nilai_ips,nilai_bahasa_inggris',
        ]);

        $context = AdminPpdbContext::resolve(
            $request->input('tahun_pelajaran_id'),
            $request->input('jalur_id'),
            $request->input('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $mapel = $request->mapel;
        $komponenList = NilaiCbt::komponenList();
        $mapelLabel = $komponenList[$mapel] ?? $mapel;

        $file = $request->file('file');
        $token = Str::random(32);
        $tempDir = storage_path('app/temp');
        $tempName = "cbt_{$token}." . $file->getClientOriginalExtension();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $file->move($tempDir, $tempName);
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempName;

        $importer = new NilaiCbtImport($tahunAktif->id, $mapel);
        $preview = $importer->preview($tempPath);

        return view('admin.nilai-cbt.preview-upload', [
            'preview' => $preview,
            'token' => $token,
            'extension' => $file->getClientOriginalExtension(),
            'originalName' => $file->getClientOriginalName(),
            'mapel' => $mapel,
            'mapelLabel' => $mapelLabel,
            'returnContext' => [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ],
        ]);
    }

    /**
     * Confirm upload - actually import
     */
    public function confirmUpload(Request $request)
    {
        $token = $request->input('token');
        $ext = $request->input('extension', 'xlsx');
        $mapel = $request->input('mapel');
        $tempPath = storage_path("app/temp/cbt_{$token}.{$ext}");

        if (!file_exists($tempPath)) {
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'File temporary tidak ditemukan. Silakan upload ulang.');
        }

        if (!$mapel || !in_array($mapel, array_keys(NilaiCbt::komponenList()))) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'Mapel tidak valid.');
        }

        $context = AdminPpdbContext::resolve(
            $request->input('tahun_pelajaran_id'),
            $request->input('jalur_id'),
            $request->input('gelombang_id')
        );
        $tahunAktif = $context['selectedTahun'];
        if (!$tahunAktif) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload', [
                    'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                    'jalur_id' => $request->input('jalur_id'),
                    'gelombang_id' => $request->input('gelombang_id'),
                ])
                ->with('error', 'Tahun pelajaran aktif tidak ditemukan.');
        }

        $komponenList = NilaiCbt::komponenList();
        $mapelLabel = $komponenList[$mapel] ?? $mapel;

        $importer = new NilaiCbtImport($tahunAktif->id, $mapel);
        $result = $importer->import($tempPath);

        @unlink($tempPath);

        $message = "Import {$mapelLabel} berhasil! {$result['imported']} baru, {$result['updated']} diupdate.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} dilewati.";
        }

        return redirect()->route('admin.nilai-cbt.index', [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])
            ->with('success', $message);
    }

    /**
     * Cancel upload
     */
    public function cancelUpload(Request $request)
    {
        $token = $request->input('token');
        $ext = $request->input('extension', 'xlsx');
        $tempPath = storage_path("app/temp/cbt_{$token}.{$ext}");

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return redirect()->route('admin.nilai-cbt.upload', [
                'tahun_pelajaran_id' => $request->input('tahun_pelajaran_id'),
                'jalur_id' => $request->input('jalur_id'),
                'gelombang_id' => $request->input('gelombang_id'),
            ])
            ->with('info', 'Upload dibatalkan.');
    }

    /**
     * Delete single record
     */
    public function destroy(NilaiCbt $nilaiCbt)
    {
        $nilaiCbt->delete();
        return back()->with('success', 'Data nilai CBT berhasil dihapus.');
    }
}
