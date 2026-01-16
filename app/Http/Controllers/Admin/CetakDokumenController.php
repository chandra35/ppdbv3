<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakDokumenController extends Controller
{
    /**
     * Display list of pendaftar for document printing
     */
    public function index(Request $request)
    {
        // Get tahun pelajaran
        $tahunPelajaranList = TahunPelajaran::orderBy('is_active', 'desc')
            ->orderBy('nama', 'desc')
            ->get();
        
        $tahunAktif = $request->tahun_pelajaran_id 
            ? TahunPelajaran::find($request->tahun_pelajaran_id)
            : TahunPelajaran::where('is_active', true)->first();

        // Get jalur and gelombang for filters
        $jalurList = $tahunAktif 
            ? JalurPendaftaran::where('tahun_pelajaran_id', $tahunAktif->id)->get() 
            : collect();
        
        $gelombangList = $tahunAktif 
            ? GelombangPendaftaran::whereHas('jalur', function($q) use ($tahunAktif) {
                $q->where('tahun_pelajaran_id', $tahunAktif->id);
            })->get() 
            : collect();

        // Build query - hanya yang sudah finalisasi
        $query = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran'])
            ->where('is_finalisasi', true);

        if ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        // Filter jalur
        if ($request->jalur_id) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }

        // Filter gelombang
        if ($request->gelombang_id) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        // Filter jenis dokumen yang belum dicetak
        if ($request->belum_cetak == 'registrasi') {
            // Logika untuk filter yang belum cetak bukti registrasi (jika ada tracking)
        } elseif ($request->belum_cetak == 'kartu_tes') {
            // Logika untuk filter yang belum cetak kartu tes
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%")
                  ->orWhere('nomor_tes', 'like', "%{$search}%");
            });
        }

        $pendaftarList = $query->orderBy('tanggal_finalisasi', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total_final' => CalonSiswa::where('is_finalisasi', true)
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
            'dengan_nomor_tes' => CalonSiswa::where('is_finalisasi', true)
                ->whereNotNull('nomor_tes')
                ->when($tahunAktif, fn($q) => $q->where('tahun_pelajaran_id', $tahunAktif->id))
                ->count(),
        ];

        return view('admin.cetak-dokumen.index', compact(
            'pendaftarList',
            'tahunPelajaranList',
            'tahunAktif',
            'jalurList',
            'gelombangList',
            'stats'
        ));
    }

    /**
     * Batch print bukti registrasi
     */
    public function batchCetakRegistrasi(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id'
        ]);

        $pendaftarList = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran'])
            ->whereIn('id', $request->ids)
            ->where('is_finalisasi', true)
            ->get();

        if ($pendaftarList->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftar yang dipilih atau belum difinalisasi');
        }

        $sekolahSettings = SekolahSettings::first();
        $settings = [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.name'),
            'alamat_sekolah' => $sekolahSettings ? trim(($sekolahSettings->alamat_jalan ?? '') . ', ' . ($sekolahSettings->city->name ?? '')) : '',
        ];

        $pdf = Pdf::loadView('admin.cetak-dokumen.batch-registrasi', [
            'pendaftarList' => $pendaftarList,
            'settings' => $settings
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('bukti-registrasi-batch-' . now()->format('YmdHis') . '.pdf');
    }

    /**
     * Batch print kartu tes
     */
    public function batchCetakKartuTes(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:calon_siswas,id'
        ]);

        $pendaftarList = CalonSiswa::with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran'])
            ->whereIn('id', $request->ids)
            ->where('is_finalisasi', true)
            ->whereNotNull('nomor_tes')
            ->get();

        if ($pendaftarList->isEmpty()) {
            return back()->with('error', 'Tidak ada pendaftar yang dipilih atau belum memiliki nomor tes');
        }

        $sekolahSettings = SekolahSettings::first();
        $settings = [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.name'),
            'alamat_sekolah' => $sekolahSettings ? trim(($sekolahSettings->alamat_jalan ?? '') . ', ' . ($sekolahSettings->city->name ?? '')) : '',
        ];

        $pdf = Pdf::loadView('admin.cetak-dokumen.batch-kartu-tes', [
            'pendaftarList' => $pendaftarList,
            'settings' => $settings
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('kartu-tes-batch-' . now()->format('YmdHis') . '.pdf');
    }

    /**
     * Preview single bukti registrasi (redirect ke existing route)
     */
    public function previewRegistrasi($id)
    {
        return redirect()->route('admin.pendaftar.cetak-registrasi', $id);
    }

    /**
     * Preview single kartu tes (redirect ke existing route)
     */
    public function previewKartuTes($id)
    {
        return redirect()->route('admin.pendaftar.cetak-ujian.preview', $id);
    }
}
