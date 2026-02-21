<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelulusan;
use App\Models\KelulusanSetting;
use App\Models\EnvelopeOpenLog;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KelulusanSettingController extends Controller
{
    /**
     * Halaman manajemen info kelulusan
     */
    public function index()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return redirect()->route('admin.dashboard')->with('error', 'Tahun pelajaran aktif tidak ditemukan');
        }

        $setting = KelulusanSetting::firstOrCreate(
            ['tahun_pelajaran_id' => $tahunAktif->id],
            [
                'judul_pengumuman' => 'Pengumuman Kelulusan PPDB',
                'pesan_lulus' => 'Selamat! Anda dinyatakan LULUS seleksi PPDB. Silakan bergabung ke grup WhatsApp dan lengkapi persyaratan daftar ulang.',
                'pesan_tidak_lulus' => 'Mohon maaf, Anda belum dinyatakan lulus pada seleksi PPDB tahun ini. Tetap semangat dan jangan menyerah!',
            ]
        );

        // Stats kelulusan
        $stats = [
            'total_lulus' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->lulus()->count(),
            'total_tidak_lulus' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->tidakLulus()->count(),
            'total_cadangan' => Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->cadangan()->count(),
        ];

        return view('admin.kelulusan.setting', compact('setting', 'tahunAktif', 'stats'));
    }

    /**
     * Update pengaturan kelulusan
     */
    public function update(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $request->validate([
            'judul_pengumuman' => 'required|string|max:255',
            'pesan_lulus' => 'nullable|string',
            'pesan_tidak_lulus' => 'nullable|string',
            'link_grup_wa' => 'nullable|url|max:500',
            'nama_grup_wa' => 'nullable|string|max:255',
            'dokumen_persyaratan' => 'nullable|array',
            'dokumen_persyaratan.*' => 'nullable|string|max:255',
            'template_surat_pernyataan' => 'nullable|string',
            'tanggal_pengumuman' => 'nullable|date',
            'tanggal_daftar_ulang_mulai' => 'nullable|date',
            'tanggal_daftar_ulang_selesai' => 'nullable|date|after_or_equal:tanggal_daftar_ulang_mulai',
            'catatan_daftar_ulang' => 'nullable|string',
        ]);

        $setting = KelulusanSetting::where('tahun_pelajaran_id', $tahunAktif->id)->first();

        // Filter out empty dokumen_persyaratan entries
        $dokumen = $request->dokumen_persyaratan ? array_values(array_filter($request->dokumen_persyaratan)) : [];

        $setting->update([
            'judul_pengumuman' => $request->judul_pengumuman,
            'pesan_lulus' => $request->pesan_lulus,
            'pesan_tidak_lulus' => $request->pesan_tidak_lulus,
            'link_grup_wa' => $request->link_grup_wa,
            'nama_grup_wa' => $request->nama_grup_wa,
            'dokumen_persyaratan' => $dokumen,
            'template_surat_pernyataan' => $request->template_surat_pernyataan,
            'tampilkan_pengumuman' => $request->has('tampilkan_pengumuman'),
            'tanggal_pengumuman' => $request->tanggal_pengumuman,
            'tampilkan_link_wa' => $request->has('tampilkan_link_wa'),
            'tampilkan_dokumen' => $request->has('tampilkan_dokumen'),
            'tanggal_daftar_ulang_mulai' => $request->tanggal_daftar_ulang_mulai,
            'tanggal_daftar_ulang_selesai' => $request->tanggal_daftar_ulang_selesai,
            'catatan_daftar_ulang' => $request->catatan_daftar_ulang,
        ]);

        return redirect()->route('admin.kelulusan.setting')
            ->with('success', 'Pengaturan kelulusan berhasil diperbarui');
    }

    /**
     * Upload file konsider via AJAX
     */
    public function uploadKonsider(Request $request)
    {
        $request->validate([
            'file_konsider' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'file_konsider.required' => 'File konsider wajib dipilih.',
            'file_konsider.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'file_konsider.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return response()->json(['success' => false, 'message' => 'Tahun pelajaran aktif tidak ditemukan.'], 422);
        }

        $setting = KelulusanSetting::where('tahun_pelajaran_id', $tahunAktif->id)->first();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Setting kelulusan tidak ditemukan.'], 422);
        }

        // Hapus file lama jika ada
        if ($setting->file_konsider) {
            Storage::disk('public')->delete($setting->file_konsider);
        }

        $file = $request->file('file_konsider');
        $path = $file->store('kelulusan/konsider', 'public');

        $setting->update(['file_konsider' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'File konsider berhasil diupload.',
            'filename' => $file->getClientOriginalName(),
            'filesize' => $this->formatFileSize($file->getSize()),
            'filepath' => $path,
            'view_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Delete file konsider via AJAX
     */
    public function deleteKonsider(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return response()->json(['success' => false, 'message' => 'Tahun pelajaran aktif tidak ditemukan.'], 422);
        }

        $setting = KelulusanSetting::where('tahun_pelajaran_id', $tahunAktif->id)->first();
        if (!$setting || !$setting->file_konsider) {
            return response()->json(['success' => false, 'message' => 'Tidak ada file konsider untuk dihapus.'], 422);
        }

        Storage::disk('public')->delete($setting->file_konsider);
        $setting->update(['file_konsider' => null]);

        return response()->json([
            'success' => true,
            'message' => 'File konsider berhasil dihapus.',
        ]);
    }

    /**
     * Format file size to human readable
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    /**
     * Halaman log buka amplop
     */
    public function envelopeLogs(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) {
            return redirect()->route('admin.dashboard')->with('error', 'Tahun pelajaran aktif tidak ditemukan');
        }

        $activeTab = $request->input('tab', 'sudah');

        // ===== Tab: Sudah Buka Amplop =====
        $query = EnvelopeOpenLog::with(['calonSiswa.jalurPendaftaran', 'calonSiswa.kelulusan', 'calonSiswa.gelombangPendaftaran'])
            ->where('tahun_pelajaran_id', $tahunAktif->id);

        // Filter pencarian
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('calonSiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort', 'opened_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['opened_at', 'ip_address', 'location_name', 'created_at'];
        $allowedRelationSorts = ['nama_lengkap', 'nisn', 'nomor_registrasi'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBy, $allowedRelationSorts)) {
            $query->orderBy(
                \App\Models\CalonSiswa::select($sortBy)
                    ->whereColumn('calon_siswas.id', 'envelope_open_logs.calon_siswa_id')
                    ->limit(1),
                $sortDir === 'asc' ? 'asc' : 'desc'
            );
        } else {
            $query->orderBy('opened_at', 'desc');
        }

        $logs = $query->paginate(25, ['*'], 'page')->withQueryString();

        // ===== Tab: Belum Buka Amplop =====
        $openedCalonSiswaIds = EnvelopeOpenLog::where('tahun_pelajaran_id', $tahunAktif->id)
            ->pluck('calon_siswa_id');

        $belumBukaQuery = Kelulusan::with(['calonSiswa.jalurPendaftaran', 'calonSiswa.gelombangPendaftaran', 'calonSiswa.user'])
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->whereNotIn('calon_siswa_id', $openedCalonSiswaIds);

        // Filter pencarian belum buka
        if ($request->search_belum) {
            $search = $request->search_belum;
            $belumBukaQuery->whereHas('calonSiswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%");
            });
        }

        // Sorting belum buka
        $sortBelum = $request->input('sort_belum', 'nama_lengkap');
        $dirBelum = $request->input('dir_belum', 'asc');
        $allowedBelumSorts = ['status', 'tanggal_kelulusan'];
        $allowedBelumRelSorts = ['nama_lengkap', 'nisn', 'nomor_registrasi'];

        if (in_array($sortBelum, $allowedBelumSorts)) {
            $belumBukaQuery->orderBy($sortBelum, $dirBelum === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBelum, $allowedBelumRelSorts)) {
            $belumBukaQuery->orderBy(
                \App\Models\CalonSiswa::select($sortBelum)
                    ->whereColumn('calon_siswas.id', 'kelulusan.calon_siswa_id')
                    ->limit(1),
                $dirBelum === 'asc' ? 'asc' : 'desc'
            );
        } else {
            $belumBukaQuery->orderBy(
                \App\Models\CalonSiswa::select('nama_lengkap')
                    ->whereColumn('calon_siswas.id', 'kelulusan.calon_siswa_id')
                    ->limit(1),
                'asc'
            );
        }

        $belumBuka = $belumBukaQuery->paginate(25, ['*'], 'page_belum')->withQueryString();

        // Stats
        $totalKelulusan = Kelulusan::where('tahun_pelajaran_id', $tahunAktif->id)->count();
        $totalOpened = EnvelopeOpenLog::where('tahun_pelajaran_id', $tahunAktif->id)->count();
        $totalBelumBuka = $totalKelulusan - $totalOpened;
        if ($totalBelumBuka < 0) $totalBelumBuka = 0;

        return view('admin.kelulusan.envelope-logs', compact(
            'logs', 'belumBuka', 'tahunAktif', 'totalKelulusan', 'totalOpened', 'totalBelumBuka', 'activeTab'
        ));
    }
}
