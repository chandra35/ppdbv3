<?php

namespace App\Http\Controllers\Pendaftar;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran', 'ortu', 'dokumen'])
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.landing')
                ->with('error', 'Data pendaftaran tidak ditemukan');
        }

        // Calculate progress
        $progress = $this->calculateProgress($calonSiswa);

        return view('pendaftar.dashboard.index', compact('calonSiswa', 'progress'));
    }

    /**
     * Show profile/data pribadi form
     */
    public function dataPribadi()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();

        return view('pendaftar.dashboard.data-pribadi', compact('calonSiswa', 'provinces'));
    }

    /**
     * Update data pribadi
     */
    public function updateDataPribadi(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $request->validate([
            'nik' => 'nullable|string|size:16',
            'nama_lengkap' => 'required|string|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string|max:20',
            'jumlah_saudara' => 'nullable|integer|min:0',
            'anak_ke' => 'nullable|integer|min:1',
            'alamat_siswa' => 'required|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|string',
            'kabupaten_id_siswa' => 'required|string',
            'kecamatan_id_siswa' => 'required|string',
            'kelurahan_id_siswa' => 'required|string',
            'kode_pos_siswa' => 'nullable|string|max:10',
            'nomor_hp' => 'required|string|max:15',
            'nama_sekolah_asal' => 'required|string|max:150',
        ]);

        $calonSiswa->update($request->only([
            'nik',
            'nama_lengkap',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama',
            'jumlah_saudara',
            'anak_ke',
            'alamat_siswa',
            'rt_siswa',
            'rw_siswa',
            'provinsi_id_siswa',
            'kabupaten_id_siswa',
            'kecamatan_id_siswa',
            'kelurahan_id_siswa',
            'kode_pos_siswa',
            'nomor_hp',
            'nama_sekolah_asal',
        ]));

        // Mark as completed
        $calonSiswa->data_diri_completed = true;
        $calonSiswa->save();

        return redirect()->route('pendaftar.data-pribadi')
            ->with('success', 'Data pribadi berhasil disimpan');
    }

    /**
     * Show data orang tua form
     */
    public function dataOrtu()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->with('ortu')->first();
        $ortu = $calonSiswa->ortu ?? new CalonOrtu();

        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();

        return view('pendaftar.dashboard.data-ortu', compact('calonSiswa', 'ortu', 'provinces'));
    }

    /**
     * Update data orang tua
     */
    public function updateDataOrtu(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $request->validate([
            // Ayah
            'nama_ayah' => 'required|string|max:100',
            'nik_ayah' => 'nullable|string|size:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'nomor_hp_ayah' => 'nullable|string|max:15',
            // Ibu
            'nama_ibu' => 'required|string|max:100',
            'nik_ibu' => 'nullable|string|size:16',
            'tempat_lahir_ibu' => 'nullable|string|max:100',
            'tanggal_lahir_ibu' => 'nullable|date',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'nomor_hp_ibu' => 'nullable|string|max:15',
            // Alamat
            'alamat_ortu' => 'required|string',
            'provinsi_id_ortu' => 'required|string',
            'kabupaten_id_ortu' => 'required|string',
            'kecamatan_id_ortu' => 'required|string',
            'kelurahan_id_ortu' => 'required|string',
            // Wali
            'nama_wali' => 'nullable|string|max:100',
            'hubungan_wali' => 'nullable|string|max:50',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'nomor_hp_wali' => 'nullable|string|max:15',
        ]);

        $calonSiswa->ortu()->updateOrCreate(
            ['calon_siswa_id' => $calonSiswa->id],
            $request->only([
                'nama_ayah', 'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah',
                'pekerjaan_ayah', 'pendidikan_ayah', 'penghasilan_ayah', 'nomor_hp_ayah',
                'nama_ibu', 'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu',
                'pekerjaan_ibu', 'pendidikan_ibu', 'penghasilan_ibu', 'nomor_hp_ibu',
                'alamat_ortu', 'provinsi_id_ortu', 'kabupaten_id_ortu',
                'kecamatan_id_ortu', 'kelurahan_id_ortu',
                'nama_wali', 'hubungan_wali', 'pekerjaan_wali', 'nomor_hp_wali',
            ])
        );

        // Mark as completed
        $calonSiswa->data_ortu_completed = true;
        $calonSiswa->save();

        return redirect()->route('pendaftar.data-ortu')
            ->with('success', 'Data orang tua berhasil disimpan');
    }

    /**
     * Show dokumen upload page
     */
    public function dokumen()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->with('dokumen')->first();

        // Get active documents from settings
        $settings = \App\Models\PpdbSettings::first();
        $dokumenAktif = $settings ? $settings->dokumen_aktif : [];

        // All available documents
        $allDocs = [
            'kk' => 'Kartu Keluarga',
            'akta_kelahiran' => 'Akta Kelahiran',
            'ijazah' => 'Ijazah / SKL',
            'rapor' => 'Rapor Semester Terakhir',
            'foto' => 'Pas Foto 3x4',
            'surat_sehat' => 'Surat Keterangan Sehat',
            'surat_pernyataan' => 'Surat Pernyataan Orang Tua',
            'kartu_pkh' => 'Kartu PKH/KIP',
        ];

        // Filter only active documents
        $requiredDocs = [];
        foreach ($allDocs as $key => $label) {
            if (in_array($key, $dokumenAktif)) {
                $requiredDocs[$key] = $label;
            }
        }

        // Get uploaded documents
        $uploadedDocs = $calonSiswa->dokumen->keyBy('jenis_dokumen');

        return view('pendaftar.dashboard.dokumen', compact('calonSiswa', 'requiredDocs', 'uploadedDocs'));
    }

    /**
     * Upload dokumen
     */
    public function uploadDokumen(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $request->validate([
            'jenis_dokumen' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $jenisDokumen = $request->jenis_dokumen;
        $file = $request->file('file');

        // Store file
        $path = $file->store('dokumen/' . $calonSiswa->id, 'public');

        // Save or update document record
        CalonDokumen::updateOrCreate(
            [
                'calon_siswa_id' => $calonSiswa->id,
                'jenis_dokumen' => $jenisDokumen,
            ],
            [
                'nama_file' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status_verifikasi' => 'pending',
            ]
        );

        // Check if all required documents uploaded
        $requiredCount = 6; // kk, akta, ijazah, rapor, foto, surat_sehat
        $uploadedCount = $calonSiswa->dokumen()->count();
        
        if ($uploadedCount >= $requiredCount) {
            $calonSiswa->data_dokumen_completed = true;
            $calonSiswa->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diupload',
        ]);
    }

    /**
     * Delete dokumen
     */
    public function deleteDokumen(Request $request, $id)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $dokumen = CalonDokumen::where('id', $id)
            ->where('calon_siswa_id', $calonSiswa->id)
            ->first();

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
            ], 404);
        }

        // Delete file
        Storage::disk('public')->delete($dokumen->path_file);
        
        // Delete record
        $dokumen->delete();

        // Update completion status
        $calonSiswa->data_dokumen_completed = false;
        $calonSiswa->save();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus',
        ]);
    }

    /**
     * Show status page
     */
    public function status()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'verifiedBy'])
            ->first();

        return view('pendaftar.dashboard.status', compact('calonSiswa'));
    }

    /**
     * Print bukti pendaftaran
     */
    public function cetakBukti()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran', 'ortu'])
            ->first();

        return view('pendaftar.dashboard.cetak-bukti', compact('calonSiswa'));
    }

    /**
     * Calculate registration progress
     */
    protected function calculateProgress(CalonSiswa $calonSiswa): array
    {
        $dataDiri = $calonSiswa->data_diri_completed ? 100 : $this->calculateDataDiriProgress($calonSiswa);
        $dataOrtu = $calonSiswa->data_ortu_completed ? 100 : $this->calculateDataOrtuProgress($calonSiswa);
        $dokumen = $calonSiswa->data_dokumen_completed ? 100 : $this->calculateDokumenProgress($calonSiswa);
        
        $verifikasi = match ($calonSiswa->status_verifikasi) {
            'verified' => 100,
            'revision' => 50,
            default => 0,
        };

        $overall = ($dataDiri + $dataOrtu + $dokumen + $verifikasi) / 4;

        return [
            'data_diri' => $dataDiri,
            'data_ortu' => $dataOrtu,
            'dokumen' => $dokumen,
            'verifikasi' => $verifikasi,
            'overall' => round($overall),
        ];
    }

    protected function calculateDataDiriProgress(CalonSiswa $calonSiswa): int
    {
        $requiredFields = ['nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'alamat_siswa', 'nomor_hp'];
        $filled = 0;
        foreach ($requiredFields as $field) {
            if (!empty($calonSiswa->$field)) {
                $filled++;
            }
        }
        return (int) (($filled / count($requiredFields)) * 100);
    }

    protected function calculateDataOrtuProgress(CalonSiswa $calonSiswa): int
    {
        $ortu = $calonSiswa->ortu;
        if (!$ortu) return 0;

        $requiredFields = ['nama_ayah', 'nama_ibu', 'alamat_ortu'];
        $filled = 0;
        foreach ($requiredFields as $field) {
            if (!empty($ortu->$field)) {
                $filled++;
            }
        }
        return (int) (($filled / count($requiredFields)) * 100);
    }

    protected function calculateDokumenProgress(CalonSiswa $calonSiswa): int
    {
        $requiredCount = 6;
        $uploadedCount = $calonSiswa->dokumen->count();
        return (int) min(100, ($uploadedCount / $requiredCount) * 100);
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();
        
        return view('pendaftar.dashboard.profile', compact('calonSiswa'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show password page
     */
    public function password()
    {
        return view('pendaftar.dashboard.password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai');
        }
        
        $user->update([
            'password' => \Hash::make($request->password),
        ]);
        
        return back()->with('success', 'Password berhasil diubah');
    }
}
