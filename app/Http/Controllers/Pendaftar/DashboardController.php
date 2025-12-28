<?php

namespace App\Http\Controllers\Pendaftar;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\NilaiRapor;
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

        $validated = $request->validate([
            'nik' => 'required|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'alamat_siswa' => 'nullable|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
            'kodepos_siswa' => 'nullable|string|max:10',
            'nomor_hp' => 'required|string|regex:/^0[0-9]{9,12}$/|max:20',
            'email' => 'nullable|email|max:255',
            'nama_sekolah_asal' => 'nullable|string|max:255',
        ], [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nomor_hp.regex' => 'Format No. HP harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
        ]);

        // Convert phone number from 08xx to +628xx format
        if (!empty($validated['nomor_hp'])) {
            $phone = $validated['nomor_hp'];
            if (substr($phone, 0, 1) === '0') {
                $validated['nomor_hp'] = '+62' . substr($phone, 1);
            } elseif (substr($phone, 0, 2) === '62') {
                $validated['nomor_hp'] = '+' . $phone;
            }
        }
        
        $calonSiswa->update([
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'agama' => $validated['agama'],
            'alamat_siswa' => $validated['alamat_siswa'],
            'rt_siswa' => $validated['rt_siswa'],
            'rw_siswa' => $validated['rw_siswa'],
            'provinsi_id_siswa' => $validated['provinsi_id_siswa'],
            'kabupaten_id_siswa' => $validated['kabupaten_id_siswa'],
            'kecamatan_id_siswa' => $validated['kecamatan_id_siswa'],
            'kelurahan_id_siswa' => $validated['kelurahan_id_siswa'],
            'kodepos_siswa' => $validated['kodepos_siswa'] ?? null,
            'nomor_hp' => $validated['nomor_hp'],
            'nama_sekolah_asal' => $validated['nama_sekolah_asal'] ?? null,
        ]);
        
        // Update email if provided
        if (!empty($validated['email']) && $calonSiswa->user) {
            $calonSiswa->user->update(['email' => $validated['email']]);
        }

        // Copy alamat siswa to ortu if requested
        if ($request->has('copy_alamat_to_ortu')) {
            $calonSiswa->ortu()->updateOrCreate(
                ['calon_siswa_id' => $calonSiswa->id],
                [
                    'alamat_ortu' => $validated['alamat_siswa'],
                    'provinsi_id' => $validated['provinsi_id_siswa'],
                    'kabupaten_id' => $validated['kabupaten_id_siswa'],
                    'kecamatan_id' => $validated['kecamatan_id_siswa'],
                    'kelurahan_id' => $validated['kelurahan_id_siswa'],
                ]
            );
        }

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
            // KK
            'no_kk' => 'nullable|string|size:16',
            // Ayah
            'nama_ayah' => 'required|string|max:100',
            'nik_ayah' => 'nullable|string|size:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'hp_ayah' => 'nullable|string|max:15',
            // Ibu
            'nama_ibu' => 'required|string|max:100',
            'nik_ibu' => 'nullable|string|size:16',
            'tempat_lahir_ibu' => 'nullable|string|max:100',
            'tanggal_lahir_ibu' => 'nullable|date',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'hp_ibu' => 'nullable|string|max:15',
            // Alamat
            'alamat_ortu' => 'required|string',
            'provinsi_id' => 'required|string',
            'kabupaten_id' => 'required|string',
            'kecamatan_id' => 'required|string',
            'kelurahan_id' => 'required|string',
            // Wali
            'nama_wali' => 'nullable|string|max:100',
            'hubungan_wali' => 'nullable|string|max:50',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'nomor_hp_wali' => 'nullable|string|max:15',
        ]);

        $calonSiswa->ortu()->updateOrCreate(
            ['calon_siswa_id' => $calonSiswa->id],
            [
                'no_kk' => $request->no_kk,
                'nama_ayah' => $request->nama_ayah,
                'nik_ayah' => $request->nik_ayah,
                'tempat_lahir_ayah' => $request->tempat_lahir_ayah,
                'tanggal_lahir_ayah' => $request->tanggal_lahir_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pendidikan_ayah' => $request->pendidikan_ayah,
                'penghasilan_ayah' => $request->penghasilan_ayah,
                'hp_ayah' => $request->hp_ayah,
                'nama_ibu' => $request->nama_ibu,
                'nik_ibu' => $request->nik_ibu,
                'tempat_lahir_ibu' => $request->tempat_lahir_ibu,
                'tanggal_lahir_ibu' => $request->tanggal_lahir_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'pendidikan_ibu' => $request->pendidikan_ibu,
                'penghasilan_ibu' => $request->penghasilan_ibu,
                'hp_ibu' => $request->hp_ibu,
                'alamat_ortu' => $request->alamat_ortu,
                'provinsi_id' => $request->provinsi_id,
                'kabupaten_id' => $request->kabupaten_id,
                'kecamatan_id' => $request->kecamatan_id,
                'kelurahan_id' => $request->kelurahan_id,
                'nama_wali' => $request->nama_wali,
                'hubungan_wali' => $request->hubungan_wali,
                'pekerjaan_wali' => $request->pekerjaan_wali,
                'nomor_hp_wali' => $request->nomor_hp_wali,
            ]
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
     * Show nilai rapor form
     */
    public function dataNilaiRapor()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with('nilaiRapor')
            ->first();

        // Prepare data untuk setiap semester (1-5)
        $nilaiRapor = [];
        for ($i = 1; $i <= 5; $i++) {
            $nilai = $calonSiswa->nilaiRapor->where('semester', $i)->first();
            $nilaiRapor[$i] = [
                'semester' => $i,
                'matematika' => $nilai->matematika ?? null,
                'ipa' => $nilai->ipa ?? null,
                'ips' => $nilai->ips ?? null,
                'rata_rata' => $nilai->rata_rata ?? null,
            ];
        }

        return view('pendaftar.dashboard.data-nilai-rapor', compact('calonSiswa', 'nilaiRapor'));
    }

    /**
     * Update nilai rapor
     */
    public function updateNilaiRapor(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        // Validate all 5 semesters
        $rules = [];
        $messages = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $rules["semester_{$i}_matematika"] = 'required|integer|min:1|max:100';
            $rules["semester_{$i}_ipa"] = 'required|integer|min:1|max:100';
            $rules["semester_{$i}_ips"] = 'required|integer|min:1|max:100';
            
            $messages["semester_{$i}_matematika.required"] = "Nilai Matematika semester {$i} harus diisi";
            $messages["semester_{$i}_matematika.min"] = "Nilai Matematika semester {$i} minimal 1";
            $messages["semester_{$i}_matematika.max"] = "Nilai Matematika semester {$i} maksimal 100";
            $messages["semester_{$i}_ipa.required"] = "Nilai IPA semester {$i} harus diisi";
            $messages["semester_{$i}_ipa.min"] = "Nilai IPA semester {$i} minimal 1";
            $messages["semester_{$i}_ipa.max"] = "Nilai IPA semester {$i} maksimal 100";
            $messages["semester_{$i}_ips.required"] = "Nilai IPS semester {$i} harus diisi";
            $messages["semester_{$i}_ips.min"] = "Nilai IPS semester {$i} minimal 1";
            $messages["semester_{$i}_ips.max"] = "Nilai IPS semester {$i} maksimal 100";
        }

        $validated = $request->validate($rules, $messages);

        // Save nilai untuk setiap semester
        for ($i = 1; $i <= 5; $i++) {
            NilaiRapor::updateOrCreate(
                [
                    'calon_siswa_id' => $calonSiswa->id,
                    'semester' => $i,
                ],
                [
                    'matematika' => $validated["semester_{$i}_matematika"],
                    'ipa' => $validated["semester_{$i}_ipa"],
                    'ips' => $validated["semester_{$i}_ips"],
                ]
            );
        }

        return redirect()->route('pendaftar.nilai-rapor')
            ->with('success', 'Nilai rapor berhasil disimpan');
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
        $nilaiRapor = $calonSiswa->nilai_rapor_progress;
        
        $verifikasi = match ($calonSiswa->status_verifikasi) {
            'verified' => 100,
            'revision' => 50,
            default => 0,
        };

        $overall = ($dataDiri + $dataOrtu + $dokumen + $nilaiRapor + $verifikasi) / 5;

        return [
            'data_diri' => $dataDiri,
            'data_ortu' => $dataOrtu,
            'dokumen' => $dokumen,
            'nilai_rapor' => $nilaiRapor,
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
