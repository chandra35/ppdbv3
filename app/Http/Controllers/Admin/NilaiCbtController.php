<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\NilaiCbtImport;
use App\Models\NilaiCbt;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NilaiCbtController extends Controller
{
    /**
     * Index - rekap data CBT
     */
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $selectedTahunId = $request->tahun_pelajaran_id ?: $tahunAktif?->id;

        $query = NilaiCbt::with('calonSiswa')
            ->where('tahun_pelajaran_id', $selectedTahunId);

        $data = $query->orderBy('rata_rata', 'desc')->get();

        $tahunPelajarans = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();

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
            'tahunPelajarans',
            'selectedTahunId',
            'mapelProgress'
        ));
    }

    /**
     * Upload form - pilih mapel dulu
     */
    public function upload(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $komponenList = NilaiCbt::komponenList();
        $selectedMapel = $request->mapel;

        return view('admin.nilai-cbt.upload', compact('tahunAktif', 'komponenList', 'selectedMapel'));
    }

    /**
     * Process upload - preview mode
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'mapel' => 'required|in:nilai_mtk,nilai_ipa,nilai_ips,nilai_bahasa_inggris',
        ]);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
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
            return redirect()->route('admin.nilai-cbt.upload')
                ->with('error', 'File temporary tidak ditemukan. Silakan upload ulang.');
        }

        if (!$mapel || !in_array($mapel, array_keys(NilaiCbt::komponenList()))) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload')
                ->with('error', 'Mapel tidak valid.');
        }

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            @unlink($tempPath);
            return redirect()->route('admin.nilai-cbt.upload')
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

        return redirect()->route('admin.nilai-cbt.index')
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

        return redirect()->route('admin.nilai-cbt.upload')
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
