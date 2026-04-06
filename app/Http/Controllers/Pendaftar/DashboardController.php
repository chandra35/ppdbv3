<?php

namespace App\Http\Controllers\Pendaftar;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\NilaiRapor;
use App\Models\PpdbSettings;
use App\Models\InformasiPendaftar;
use App\Models\EnvelopeOpenLog;
use App\Models\RiwayatGelombang;
use App\Models\GelombangPendaftaran;
use App\Services\KopSuratService;
use App\Services\NomorService;
use App\Services\DocumentStorageService;
use App\Support\AdminPpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    protected $kopSuratService;
    protected $nomorService;
    protected $documentStorageService;

    public function __construct(KopSuratService $kopSuratService, NomorService $nomorService, DocumentStorageService $documentStorageService)
    {
        $this->kopSuratService = $kopSuratService;
        $this->nomorService = $nomorService;
        $this->documentStorageService = $documentStorageService;
    }

    /**
     * Show dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran', 'ortu', 'dokumen', 'nilaiRapor', 'kelulusan'])
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.landing')
                ->with('error', 'Data pendaftaran tidak ditemukan');
        }

        // Calculate progress
        $progress = $this->calculateProgress($calonSiswa);
        
        // Get location setting
        $settings = \App\Models\PpdbSettings::first();
        $wajibLokasi = $settings ? ($settings->wajib_lokasi_registrasi ?? false) : false;

        // Get dokumen yang perlu direvisi atau ditolak
        $dokumenBermasalah = $calonSiswa->dokumen()
            ->whereIn('status_verifikasi', ['revision', 'invalid'])
            ->get();

        // Hitung status kelengkapan untuk info box
        $kelengkapan = $this->calculateKelengkapanStatus($calonSiswa);

        // Check if should show info modal (only after login)
        $showInfoModal = session()->pull('show_info_modal', false);
        $infoList = [];
        if ($showInfoModal) {
            $infoList = InformasiPendaftar::getModalInfo();
        }

        // Kelulusan info for dashboard
        $kelulusanSetting = \App\Models\KelulusanSetting::getActive($calonSiswa);
        $kelulusanData = null;
        if ($kelulusanSetting && $kelulusanSetting->isPengumumanAktif()) {
            $envelopeAlreadyOpened = EnvelopeOpenLog::hasOpened($calonSiswa->id, $calonSiswa->tahun_pelajaran_id);
            if ($envelopeAlreadyOpened) {
                session(['kelulusan_envelope_opened' => true]);
            }
            $kelulusanData = [
                'setting' => $kelulusanSetting,
                'kelulusan' => $calonSiswa->kelulusan,
                'envelope_opened' => $envelopeAlreadyOpened,
            ];
        }

        // Cek tujuan pendaftaran berikutnya (jalur/gelombang) untuk notifikasi pindah
        $gelombangBerikutnya = null;
        if ($this->bisaPindahGelombang($calonSiswa)) {
            $gelombangBerikutnya = $this->findGelombangBerikutnya($calonSiswa);
        }

        return view('pendaftar.dashboard.index', compact(
            'calonSiswa', 'progress', 'wajibLokasi', 'dokumenBermasalah',
            'kelengkapan', 'showInfoModal', 'infoList', 'kelulusanData', 'gelombangBerikutnya'
        ));
    }

    /**
     * Show profile/data pribadi form
     */
    public function dataPribadi()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        // Pre-load existing location data for cascade dropdowns
        $cities = collect();
        $districts = collect();
        $villages = collect();
        
        if ($calonSiswa->provinsi_id_siswa) {
            $cities = \Laravolt\Indonesia\Models\City::where('province_code', $calonSiswa->provinsi_id_siswa)
                ->orderBy('name')->get();
        }
        if ($calonSiswa->kabupaten_id_siswa) {
            $districts = \Laravolt\Indonesia\Models\District::where('city_code', $calonSiswa->kabupaten_id_siswa)
                ->orderBy('name')->get();
        }
        if ($calonSiswa->kecamatan_id_siswa) {
            $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $calonSiswa->kecamatan_id_siswa)
                ->orderBy('name')->get();
        }

        return view('pendaftar.dashboard.data-pribadi', compact('calonSiswa', 'provinces', 'cities', 'districts', 'villages'));
    }

    /**
     * Update data pribadi
     */
    public function updateDataPribadi(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return back()->with('error', 'Data sudah difinalisasi dan tidak dapat diubah');
        }

        // Convert tanggal_lahir from dd/mm/Y to Y-m-d before validation
        if ($request->filled('tanggal_lahir')) {
            $tanggalLahir = $request->input('tanggal_lahir');
            // Check if format is dd/mm/Y
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggalLahir)) {
                $parsed = \Carbon\Carbon::createFromFormat('d/m/Y', $tanggalLahir);
                if ($parsed) {
                    $request->merge(['tanggal_lahir' => $parsed->format('Y-m-d')]);
                }
            }
        }

        $validated = $request->validate([
            'nik' => 'required|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            'anak_ke' => 'nullable|integer|min:1|max:20',
            'jumlah_saudara' => 'nullable|integer|min:0|max:20',
            'alamat_siswa' => 'nullable|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
            'kodepos_siswa' => 'nullable|string|max:10',
            'nomor_hp' => ['required', 'string', 'max:20', 'regex:#^(0|62|\+62)[0-9]{9,13}$#'],
            'email' => 'nullable|email|max:255',
            'nama_sekolah_asal' => 'nullable|string|max:255',
        ], [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nomor_hp.regex' => 'Format No. HP harus 08xx, 628xx, atau +628xx.',
        ]);

        // Normalize phone number untuk pengecekan
        $phoneNormalized = $this->normalizePhoneNumber($validated['nomor_hp']);
        
        // Check if phone number already registered by other user
        $existingPhone = CalonSiswa::where('id', '!=', $calonSiswa->id)
            ->where(function($query) use ($phoneNormalized, $validated) {
                $query->where('nomor_hp', $phoneNormalized)
                      ->orWhere('nomor_hp', $validated['nomor_hp'])
                      ->orWhere('nomor_hp', '+62' . ltrim($validated['nomor_hp'], '0'))
                      ->orWhere('nomor_hp', '0' . substr($phoneNormalized, 3));
            })->first();
        
        if ($existingPhone) {
            return back()->withErrors(['nomor_hp' => 'Nomor WhatsApp sudah digunakan oleh pendaftar lain.'])->withInput();
        }

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
            'anak_ke' => $validated['anak_ke'] ?? null,
            'jumlah_saudara' => $validated['jumlah_saudara'] ?? null,
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
        
        // Pre-load existing location data for cascade dropdowns
        $cities = collect();
        $districts = collect();
        $villages = collect();
        
        if ($ortu->provinsi_id) {
            $cities = \Laravolt\Indonesia\Models\City::where('province_code', $ortu->provinsi_id)
                ->orderBy('name')->get();
        }
        if ($ortu->kabupaten_id) {
            $districts = \Laravolt\Indonesia\Models\District::where('city_code', $ortu->kabupaten_id)
                ->orderBy('name')->get();
        }
        if ($ortu->kecamatan_id) {
            $villages = \Laravolt\Indonesia\Models\Village::where('district_code', $ortu->kecamatan_id)
                ->orderBy('name')->get();
        }

        return view('pendaftar.dashboard.data-ortu', compact('calonSiswa', 'ortu', 'provinces', 'cities', 'districts', 'villages'));
    }

    /**
     * Update data orang tua
     */
    public function updateDataOrtu(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return back()->with('error', 'Data sudah difinalisasi dan tidak dapat diubah');
        }

        // Convert tanggal_lahir fields from dd/mm/Y to Y-m-d before validation
        foreach (['tanggal_lahir_ayah', 'tanggal_lahir_ibu'] as $field) {
            if ($request->filled($field)) {
                $tanggal = $request->input($field);
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggal)) {
                    $parsed = \Carbon\Carbon::createFromFormat('d/m/Y', $tanggal);
                    if ($parsed) {
                        $request->merge([$field => $parsed->format('Y-m-d')]);
                    }
                }
            }
        }

        $request->validate([
            // KK
            'no_kk' => 'nullable|string|size:16',
            // Ayah
            'status_ayah' => 'nullable|in:masih_hidup,meninggal',
            'nama_ayah' => 'required|string|max:100',
            'nik_ayah' => 'nullable|string|size:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'hp_ayah' => 'nullable|string|max:15',
            // Ibu
            'status_ibu' => 'nullable|in:masih_hidup,meninggal',
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
                'status_ayah' => $request->status_ayah ?: 'masih_hidup',
                'nama_ayah' => $request->nama_ayah,
                'nik_ayah' => $request->nik_ayah,
                'tempat_lahir_ayah' => $request->tempat_lahir_ayah,
                'tanggal_lahir_ayah' => $request->tanggal_lahir_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pendidikan_ayah' => $request->pendidikan_ayah,
                'penghasilan_ayah' => $request->penghasilan_ayah,
                'hp_ayah' => $request->hp_ayah,
                'status_ibu' => $request->status_ibu ?: 'masih_hidup',
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
        $izinkanDokumenTambahan = $settings ? $settings->izinkan_dokumen_tambahan : false;

        // All available documents
        $allDocs = [
            'kk' => 'Kartu Keluarga',
            'akta_lahir' => 'Akta Kelahiran',
            'ijazah' => 'Ijazah / SKL',
            'raport' => 'Raport Semester Terakhir',
            'foto' => 'Pas Foto 3x4',
            'ktp_ortu' => 'KTP Orang Tua',
            'skhun' => 'SKHUN',
            'surat_sehat' => 'Surat Keterangan Sehat',
            'surat_pernyataan' => 'Surat Pernyataan Orang Tua',
            'kartu_pkh' => 'Kartu PKH/KIP',
            'surat_kelakuan_baik' => 'Surat Kelakuan Baik',
            'kartu_pelajar' => 'Kartu Pelajar/NISN',
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
        
        // Get dokumen tambahan options
        $dokumenTambahanOptions = CalonDokumen::DOKUMEN_TAMBAHAN;
        $dokumenTambahanGroups = CalonDokumen::getDokumenTambahanGroups();
        
        // Get uploaded dokumen tambahan
        $uploadedDokumenTambahan = $calonSiswa->dokumen
            ->whereIn('jenis_dokumen', array_keys($dokumenTambahanOptions))
            ->values();

        return view('pendaftar.dashboard.dokumen', compact(
            'calonSiswa', 
            'requiredDocs', 
            'uploadedDocs',
            'izinkanDokumenTambahan',
            'dokumenTambahanOptions',
            'dokumenTambahanGroups',
            'uploadedDokumenTambahan'
        ));
    }

    /**
     * Upload dokumen
     */
    public function uploadDokumen(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah difinalisasi dan tidak dapat diubah'
            ], 403);
        }

        $jenisDokumen = $request->jenis_dokumen;
        
        // Check if upload from camera/cropped image (base64)
        if ($request->filled('camera_captured')) {
            $imageData = $request->input('camera_captured');
            
            // Validate base64 image
            if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $imageData)) {
                return response()->json(['success' => false, 'message' => 'Format gambar tidak valid'], 400);
            }
            
            // Decode base64 image
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
            
            if ($imageData === false) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses gambar'], 400);
            }
            
            $existingDokumen = CalonDokumen::where('calon_siswa_id', $calonSiswa->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->first();

            if ($existingDokumen) {
                $this->documentStorageService->delete($existingDokumen);
            }

            $stored = $this->documentStorageService->storeBase64Image($request->input('camera_captured'), $calonSiswa, $jenisDokumen, [
                'filename' => $jenisDokumen . '_' . $calonSiswa->nisn . '_' . time() . '.jpg',
                'original_name' => $jenisDokumen . '_cropped.jpg',
                'local_directory' => 'dokumen/' . $calonSiswa->id,
            ]);

            CalonDokumen::updateOrCreate(
                [
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDokumen,
                ],
                array_merge($stored, [
                    'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? $jenisDokumen,
                    'status_verifikasi' => 'pending',
                ])
            );
        } else {
            // Pas foto hanya boleh format gambar (tidak boleh PDF)
            if ($jenisDokumen === 'foto') {
                $request->validate([
                    'jenis_dokumen' => 'required|string',
                    'file' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                ], [
                    'file.mimes' => 'Pas foto harus berupa file gambar (JPG, JPEG, PNG). PDF tidak diperbolehkan untuk pas foto.',
                ]);
            } else {
                $request->validate([
                    'jenis_dokumen' => 'required|string',
                    'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);
            }

            $file = $request->file('file');

            $existingDokumen = CalonDokumen::where('calon_siswa_id', $calonSiswa->id)
                ->where('jenis_dokumen', $jenisDokumen)
                ->first();

            if ($existingDokumen) {
                $this->documentStorageService->delete($existingDokumen);
            }

            $stored = $this->documentStorageService->storeUploadedFile($file, $calonSiswa, $jenisDokumen, [
                'local_directory' => 'dokumen/' . $calonSiswa->id,
            ]);

            // Save or update document record
            CalonDokumen::updateOrCreate(
                [
                    'calon_siswa_id' => $calonSiswa->id,
                    'jenis_dokumen' => $jenisDokumen,
                ],
                array_merge($stored, [
                    'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? $jenisDokumen,
                    'status_verifikasi' => 'pending',
                ])
            );
        }

        // Check if all required documents uploaded - get from settings
        $settings = PpdbSettings::first();
        $requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
        $requiredCount = count($requiredDokumen);
        
        if ($requiredCount > 0) {
            $uploadedCount = $calonSiswa->dokumen()
                ->whereIn('jenis_dokumen', $requiredDokumen)
                ->count();
            
            if ($uploadedCount >= $requiredCount) {
                $calonSiswa->data_dokumen_completed = true;
                $calonSiswa->save();
            } else {
                $calonSiswa->data_dokumen_completed = false;
                $calonSiswa->save();
            }
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

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah difinalisasi dan tidak dapat diubah'
            ], 403);
        }

        $dokumen = CalonDokumen::where('id', $id)
            ->where('calon_siswa_id', $calonSiswa->id)
            ->first();

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
            ], 404);
        }

        $this->documentStorageService->delete($dokumen);
        
        // Delete record
        $dokumen->delete();

        // Re-check completion status after deletion
        $settings = PpdbSettings::first();
        $requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
        $requiredCount = count($requiredDokumen);
        
        if ($requiredCount > 0) {
            $uploadedCount = $calonSiswa->dokumen()
                ->whereIn('jenis_dokumen', $requiredDokumen)
                ->count();
            
            $calonSiswa->data_dokumen_completed = ($uploadedCount >= $requiredCount);
            $calonSiswa->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus',
        ]);
    }

    /**
     * Upload dokumen tambahan
     */
    public function uploadDokumenTambahan(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json(['success' => false, 'message' => 'Data sudah difinalisasi dan tidak dapat diubah'], 403);
        }

        // Check if dokumen tambahan is allowed
        $settings = PpdbSettings::first();
        if (!$settings || !$settings->izinkan_dokumen_tambahan) {
            return response()->json(['success' => false, 'message' => 'Fitur upload dokumen tambahan tidak diaktifkan'], 403);
        }

        $request->validate([
            'jenis_dokumen' => 'required|string|in:' . implode(',', array_keys(CalonDokumen::DOKUMEN_TAMBAHAN)),
            'keterangan' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
        ], [
            'jenis_dokumen.required' => 'Pilih jenis dokumen',
            'jenis_dokumen.in' => 'Jenis dokumen tidak valid',
            'file.required' => 'File harus diupload',
            'file.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG',
            'file.max' => 'Ukuran file maksimal 10MB',
        ]);

        $file = $request->file('file');
        $jenisDokumen = $request->jenis_dokumen;
        $keterangan = $request->keterangan;

        // Generate filename
        $extension = $file->getClientOriginalExtension();
        $filename = $jenisDokumen . '_' . $calonSiswa->nisn . '_' . time() . '.' . $extension;
        
        $stored = $this->documentStorageService->storeUploadedFile($file, $calonSiswa, $jenisDokumen, [
            'filename' => $filename,
            'local_directory' => 'dokumen_pendaftar/' . $calonSiswa->id . '/tambahan',
        ]);

        // Create dokumen record - dokumen tambahan langsung valid (tidak perlu verifikasi)
        $dokumen = CalonDokumen::create([
            'calon_siswa_id' => $calonSiswa->id,
            'jenis_dokumen' => $jenisDokumen,
            'nama_dokumen' => CalonDokumen::DOKUMEN_TAMBAHAN[$jenisDokumen] . ($keterangan ? ' - ' . $keterangan : ''),
            'nama_file' => $stored['nama_file'],
            'file_path' => $stored['file_path'],
            'remote_file_id' => $stored['remote_file_id'],
            'remote_file_url' => $stored['remote_file_url'],
            'file_size' => $stored['file_size'],
            'mime_type' => $stored['mime_type'],
            'storage_disk' => $stored['storage_disk'],
            'is_required' => false,
            'status_verifikasi' => 'valid', // Langsung valid karena opsional
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen tambahan berhasil diupload',
            'dokumen' => [
                'id' => $dokumen->id,
                'jenis' => $jenisDokumen,
                'nama' => $dokumen->nama_dokumen,
                'nama_file' => $dokumen->nama_file,
                'file_url' => $dokumen->file_url,
                'file_size' => $dokumen->file_size_formatted,
                'status' => $dokumen->status_verifikasi,
            ]
        ]);
    }

    /**
     * Delete dokumen tambahan
     */
    public function deleteDokumenTambahan($id)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json(['success' => false, 'message' => 'Data sudah difinalisasi dan tidak dapat diubah'], 403);
        }

        // Find dokumen - must be dokumen tambahan type
        $dokumen = $calonSiswa->dokumen()
            ->where('id', $id)
            ->whereIn('jenis_dokumen', array_keys(CalonDokumen::DOKUMEN_TAMBAHAN))
            ->first();

        if (!$dokumen) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan'], 404);
        }

        $this->documentStorageService->delete($dokumen);
        $dokumen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen tambahan berhasil dihapus'
        ]);
    }

    /**
     * Show status page
     */
    public function status()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'verifiedBy', 'kelulusan'])
            ->first();

        // Cek apakah ada kelulusan & pengumuman aktif tapi amplop belum dibuka
        $kelulusanSetting = \App\Models\KelulusanSetting::getActive($calonSiswa);
        $sembunyikanAdmisi = false;
        $pengumumanBelumWaktunya = false;
        $tidakAdaKelulusan = false;
        if ($calonSiswa->kelulusan && $kelulusanSetting) {
            if ($kelulusanSetting->isPengumumanAktif()) {
                // Pengumuman sudah aktif, cek apakah amplop sudah dibuka
                $envelopeOpened = EnvelopeOpenLog::hasOpened($calonSiswa->id, $calonSiswa->tahun_pelajaran_id)
                    || session('kelulusan_envelope_opened');
                if (!$envelopeOpened) {
                    $sembunyikanAdmisi = true;
                }
            } else {
                // Pengumuman belum waktunya (terjadwal tapi belum sampai waktunya)
                $sembunyikanAdmisi = true;
                $pengumumanBelumWaktunya = true;
            }
        } elseif (!$calonSiswa->kelulusan && $kelulusanSetting && $kelulusanSetting->isPengumumanAktif()) {
            // Pengumuman aktif tetapi hasil untuk pendaftar ini belum ditetapkan.
            // Untuk model one day one service, tampilkan status "masih diproses"
            // agar tidak terkesan pendaftar dikeluarkan dari pengumuman.
            $tidakAdaKelulusan = true;
            $sembunyikanAdmisi = true;
        }

        return view('pendaftar.dashboard.status', compact('calonSiswa', 'sembunyikanAdmisi', 'pengumumanBelumWaktunya', 'tidakAdaKelulusan'));
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
            $raporDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'rapor_sem_' . $i)->first();
            $nilaiRapor[$i] = [
                'semester' => $i,
                'matematika' => $nilai->matematika ?? null,
                'ipa' => $nilai->ipa ?? null,
                'ips' => $nilai->ips ?? null,
                'rata_rata' => $nilai->rata_rata ?? null,
                'dokumen' => $raporDokumen,
                'dokumen_path' => $nilai->dokumen_path ?? ($raporDokumen ? $raporDokumen->file_path : null),
                'status_validasi' => $nilai->status_validasi ?? 'pending',
                'catatan_validasi' => $nilai->catatan_validasi ?? null,
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

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return back()->with('error', 'Data sudah difinalisasi dan tidak dapat diubah');
        }

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
     * Upload file rapor per semester
     */
    public function uploadRaporSemester(Request $request, $semester)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json(['success' => false, 'message' => 'Data sudah difinalisasi dan tidak dapat diubah'], 403);
        }

        // Validate semester
        if ($semester < 1 || $semester > 5) {
            return response()->json(['success' => false, 'message' => 'Semester tidak valid'], 400);
        }

        $jenisDokumen = 'rapor_sem_' . $semester;
        
        // Delete existing file if any
        $existingDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', $jenisDokumen)->first();
        if ($existingDokumen) {
            $this->documentStorageService->delete($existingDokumen);
            $existingDokumen->delete();
        }

        // Check if upload from camera
        if ($request->filled('camera_captured')) {
            $imageData = $request->input('camera_captured');
            
            // Validate base64 image
            if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $imageData)) {
                return response()->json(['success' => false, 'message' => 'Format gambar tidak valid'], 400);
            }
            
            // Decode base64 image
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
            
            if ($imageData === false) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses gambar'], 400);
            }
            
            $stored = $this->documentStorageService->storeBase64Image($request->input('camera_captured'), $calonSiswa, $jenisDokumen, [
                'filename' => 'rapor_sem' . $semester . '_' . $calonSiswa->nisn . '_' . time() . '.jpg',
                'original_name' => 'rapor_semester_' . $semester . '_camera.jpg',
                'local_directory' => 'dokumen_pendaftar/' . $calonSiswa->id,
            ]);

            // Create dokumen record
            $dokumen = CalonDokumen::create([
                'calon_siswa_id' => $calonSiswa->id,
                'jenis_dokumen' => $jenisDokumen,
                'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? 'Rapor Semester ' . $semester,
                'nama_file' => $stored['nama_file'],
                'file_path' => $stored['file_path'],
                'remote_file_id' => $stored['remote_file_id'],
                'remote_file_url' => $stored['remote_file_url'],
                'file_size' => $stored['file_size'],
                'mime_type' => $stored['mime_type'],
                'storage_disk' => $stored['storage_disk'],
                'is_required' => false,
                'status_verifikasi' => 'pending',
            ]);
        } else {
            // Validate file upload
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            ], [
                'file.required' => 'File rapor harus diupload',
                'file.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG',
                'file.max' => 'Ukuran file maksimal 10MB',
            ]);

            $file = $request->file('file');

            // Generate filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'rapor_sem' . $semester . '_' . $calonSiswa->nisn . '_' . time() . '.' . $extension;
            
            $stored = $this->documentStorageService->storeUploadedFile($file, $calonSiswa, $jenisDokumen, [
                'filename' => $filename,
                'local_directory' => 'dokumen_pendaftar/' . $calonSiswa->id,
            ]);

            // Create dokumen record
            $dokumen = CalonDokumen::create([
                'calon_siswa_id' => $calonSiswa->id,
                'jenis_dokumen' => $jenisDokumen,
                'nama_dokumen' => CalonDokumen::JENIS_DOKUMEN[$jenisDokumen] ?? 'Rapor Semester ' . $semester,
                'nama_file' => $stored['nama_file'],
                'file_path' => $stored['file_path'],
                'remote_file_id' => $stored['remote_file_id'],
                'remote_file_url' => $stored['remote_file_url'],
                'file_size' => $stored['file_size'],
                'mime_type' => $stored['mime_type'],
                'storage_disk' => $stored['storage_disk'],
                'is_required' => false,
                'status_verifikasi' => 'pending',
            ]);
        }

        // Update nilai_rapor table with dokumen_path
        // Jika nilai_rapor belum ada untuk semester ini, buat record baru
        $nilaiRapor = \App\Models\NilaiRapor::where('calon_siswa_id', $calonSiswa->id)
            ->where('semester', $semester)
            ->first();
        
        if ($nilaiRapor) {
            $nilaiRapor->update([
                'dokumen_path' => $dokumen->file_path,
                'status_validasi' => 'pending', // Reset validation when new document uploaded
                'catatan_validasi' => null,
                'validated_by' => null,
                'validated_at' => null,
            ]);
        } else {
            // Buat record nilai_rapor baru dengan nilai default
            \App\Models\NilaiRapor::create([
                'calon_siswa_id' => $calonSiswa->id,
                'semester' => $semester,
                'matematika' => 0,
                'ipa' => 0,
                'ips' => 0,
                'rata_rata' => 0,
                'dokumen_path' => $dokumen->file_path,
                'status_validasi' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'File rapor semester ' . $semester . ' berhasil diupload',
            'dokumen' => [
                'id' => $dokumen->id,
                'nama_file' => $dokumen->nama_file,
                'file_url' => $dokumen->file_url,
                'file_size' => $dokumen->file_size_formatted,
                'status' => $dokumen->status_verifikasi,
            ]
        ]);
    }

    /**
     * Delete file rapor semester
     */
    public function deleteRaporSemester($semester)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json(['success' => false, 'message' => 'Data sudah difinalisasi dan tidak dapat diubah'], 403);
        }

        $jenisDokumen = 'rapor_sem_' . $semester;
        $dokumen = $calonSiswa->dokumen()->where('jenis_dokumen', $jenisDokumen)->first();

        if (!$dokumen) {
            return response()->json(['success' => false, 'message' => 'File rapor tidak ditemukan'], 404);
        }

        $this->documentStorageService->delete($dokumen);
        $dokumen->delete();

        // Clear dokumen_path in nilai_rapor table
        $nilaiRapor = \App\Models\NilaiRapor::where('calon_siswa_id', $calonSiswa->id)
            ->where('semester', $semester)
            ->first();
        
        if ($nilaiRapor) {
            $nilaiRapor->update([
                'dokumen_path' => null,
                'status_validasi' => 'pending',
                'catatan_validasi' => null,
                'validated_by' => null,
                'validated_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'File rapor semester ' . $semester . ' berhasil dihapus'
        ]);
    }

    /**
     * Cetak Bukti Registrasi (for TU archive)
     */
    public function cetakBuktiRegistrasi()
    {
        return $this->generateBuktiRegistrasi('download');
    }

    /**
     * Preview Bukti Registrasi
     */
    public function previewBuktiRegistrasi()
    {
        return $this->generateBuktiRegistrasi('stream');
    }

    /**
     * Generate Bukti Registrasi PDF
     */
    private function generateBuktiRegistrasi($mode = 'download')
    {
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '256M');
        
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with([
                'jalurPendaftaran', 
                'gelombangPendaftaran', 
                'tahunPelajaran', 
                'ortu'
            ])
            ->first();

        if (!$calonSiswa || !$calonSiswa->is_finalisasi) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data belum difinalisasi');
        }

        // Stabilkan render PDF di hosting saat resource remote sedang lambat.
        ini_set('memory_limit', '256M');
        @set_time_limit(120);

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        // Generate kop surat HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);
        
        // Generate or get verification hash for QR code
        $verificationHash = $calonSiswa->getOrGenerateHash();
        
        // Generate QR code if enabled
        $qrCode = null;
        if ($sekolahSettings && $sekolahSettings->qr_enable) {
            $qrCode = $this->generateQrCode($calonSiswa, $sekolahSettings, $verificationHash);
        }
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $this->getSchoolLogo(),
            'alamat' => $sekolahSettings ? trim(($sekolahSettings->alamat_jalan ?? '') . ' ' . ($sekolahSettings->city->name ?? '') . ' ' . ($sekolahSettings->province->name ?? '')) : config('app.school_address', ''),
            'telepon' => $sekolahSettings->telepon ?? config('app.school_phone', '-'),
            'email' => $sekolahSettings->email ?? config('app.school_email', '-'),
            'kota' => $sekolahSettings->city->name ?? config('app.school_city', ''),
        ];

        $fotoPdfSrc = $this->resolveFotoPdfSource($calonSiswa);
        
        $pdf = Pdf::loadView('pendaftar.pdf.bukti-registrasi', compact('calonSiswa', 'sekolah', 'kopHtml', 'qrCode', 'sekolahSettings', 'fotoPdfSrc'))
            ->setOption('isRemoteEnabled', true);
        
        $filename = 'bukti-registrasi-' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nomor_registrasi) . '.pdf';
        
        return $mode === 'stream' ? $pdf->stream($filename) : $pdf->download($filename);
    }

    private function resolveFotoPdfSource(CalonSiswa $calonSiswa): ?string
    {
        $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
        if (!$fotoDokumen) {
            return null;
        }

        if ($fotoDokumen->storage_disk === 'public' && $fotoDokumen->file_path) {
            $fotoPath = storage_path('app/public/' . $fotoDokumen->file_path);
            return file_exists($fotoPath) ? $fotoPath : null;
        }

        if ($fotoDokumen->storage_disk === 'gdrive') {
            try {
                $response = Http::timeout(20)->get($fotoDokumen->preview_url ?: $fotoDokumen->download_url);

                if ($response->successful()) {
                    $mimeType = $fotoDokumen->mime_type ?: 'image/jpeg';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($response->body());
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal memuat foto PDF dari Google Drive', [
                    'calon_siswa_id' => $calonSiswa->id,
                    'dokumen_id' => $fotoDokumen->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $fotoDokumen->preview_url ?: $fotoDokumen->download_url;
        }

        return null;
    }

    /**
     * Generate QR code with optional logo
     */
    private function generateQrCode($calonSiswa, $sekolahSettings, $verificationHash)
    {
        try {
            $qrSize = $sekolahSettings->qr_size ?? 150;
            $errorLevel = $sekolahSettings->qr_error_level ?? 'H';
            
            // Generate URL based on function setting
            $url = route('verify.bukti', $verificationHash);
            
            // Generate QR code as SVG (works without imagick)
            $qrSvg = \QrCode::size($qrSize)
                ->errorCorrection($errorLevel)
                ->generate($url);
            
            return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cetak Kartu Ujian (ID card with photo & password)
     */
    public function cetakKartuUjian()
    {
        return $this->generateKartuUjian('download');
    }

    /**
     * Preview Kartu Ujian (HTML view with print/download buttons)
     */
    public function previewKartuUjian()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with([
                'jalurPendaftaran', 
                'gelombangPendaftaran', 
                'tahunPelajaran'
            ])
            ->first();

        if (!$calonSiswa || !$calonSiswa->is_finalisasi) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data belum difinalisasi');
        }

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $sekolahSettings->logo ?? null,
        ];
        
        $password = $user->readable_password ?? '********';
        
        // Return HTML view for preview (not PDF)
        return view('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password'))
            ->with('isPdf', false);
    }

    /**
     * Generate Kartu Ujian PDF
     */
    private function generateKartuUjian($mode = 'download')
    {
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '256M');
        
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with([
                'jalurPendaftaran', 
                'gelombangPendaftaran', 
                'tahunPelajaran'
            ])
            ->first();

        if (!$calonSiswa || !$calonSiswa->is_finalisasi) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data belum difinalisasi');
        }

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();
        
        // Generate kop surat HTML
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);
        
        $sekolah = (object) [
            'nama_sekolah' => $sekolahSettings->nama_sekolah ?? config('app.school_name', config('app.name', 'SMK')),
            'logo' => $this->getSchoolLogo(),
        ];
        
        $password = $user->readable_password ?? '********';
        
        $isPdf = true;
        
        $pdf = Pdf::loadView('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'kopHtml', 'isPdf'))
            ->setOption('isRemoteEnabled', true)
            ->setPaper([0, 0, 298, 421], 'landscape');
        
        $filename = 'kartu-ujian-' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nomor_tes) . '.pdf';
        
        return $mode === 'stream' ? $pdf->stream($filename) : $pdf->download($filename);
    }

    /**
     * Get school logo path (optimized for PDF)
     */
    private function getSchoolLogo()
    {
        // Get logo from sekolah_settings table
        $sekolahSettings = \App\Models\SekolahSettings::first();
        
        if ($sekolahSettings && $sekolahSettings->logo) {
            $logoPath = storage_path('app/public/' . $sekolahSettings->logo);
            if (file_exists($logoPath)) {
                // For PDF, resize logo to reduce memory
                $resized = $this->resizeLogoForWatermark($logoPath);
                return $resized ?? $logoPath;
            }
        }

        // Fallback: check common logo locations
        $possiblePaths = [
            public_path('logo.png'),
            public_path('images/logo.png'),
            public_path('assets/logo.png'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $resized = $this->resizeLogoForWatermark($path);
                return $resized ?? $path;
            }
        }

        return null;
    }

    /**
     * Resize logo for watermark to reduce memory
     */
    private function resizeLogoForWatermark($filePath)
    {
        try {
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                return null;
            }

            list($width, $height, $type) = $imageInfo;

            // Create source image
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = @imagecreatefromjpeg($filePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = @imagecreatefrompng($filePath);
                    break;
                default:
                    return null;
            }

            if (!$source) {
                return null;
            }

            // Max watermark size
            $maxSize = 300;
            $ratio = min($maxSize / $width, $maxSize / $height);
            
            if ($ratio >= 1) {
                // Already small enough
                imagedestroy($source);
                return $filePath;
            }

            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Create resized image
            $destination = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency
            if ($type == IMAGETYPE_PNG) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }

            imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save to temp file
            $tempPath = sys_get_temp_dir() . '/logo_watermark_' . md5($filePath) . '.png';
            imagepng($destination, $tempPath, 6);

            imagedestroy($source);
            imagedestroy($destination);

            return $tempPath;

        } catch (\Exception $e) {
            \Log::error('Logo resize failed: ' . $e->getMessage());
            return null;
        }
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

        // Calculate pilihan program if enabled
        $pilihanProgram = 0;
        $jalur = $calonSiswa->jalurPendaftaran;
        $includePilihanProgram = $jalur && $jalur->pilihan_program_aktif;
        
        if ($includePilihanProgram) {
            $pilihanProgram = !empty($calonSiswa->pilihan_program) ? 100 : 0;
        }

        // Calculate overall progress with conditional inclusion
        if ($includePilihanProgram) {
            $overall = ($dataDiri + $dataOrtu + $dokumen + $nilaiRapor + $verifikasi + $pilihanProgram) / 6;
        } else {
            $overall = ($dataDiri + $dataOrtu + $dokumen + $nilaiRapor + $verifikasi) / 5;
        }

        $result = [
            'data_diri' => $dataDiri,
            'data_ortu' => $dataOrtu,
            'dokumen' => $dokumen,
            'nilai_rapor' => $nilaiRapor,
            'verifikasi' => $verifikasi,
            'overall' => round($overall),
        ];

        // Only include pilihan_program in result if feature is enabled
        if ($includePilihanProgram) {
            $result['pilihan_program'] = $pilihanProgram;
        }

        return $result;
    }

    protected function calculateDataDiriProgress(CalonSiswa $calonSiswa): int
    {
        $requiredFields = [
            'nama_lengkap', 
            'tempat_lahir', 
            'tanggal_lahir', 
            'jenis_kelamin', 
            'agama', 
            'alamat_siswa', 
            'nomor_hp',
            'provinsi_id_siswa',
            'kabupaten_id_siswa',
            'kecamatan_id_siswa',
            'kelurahan_id_siswa'
        ];
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
        // Get active documents from settings
        $settings = PpdbSettings::first();
        $requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
        
        $requiredCount = count($requiredDokumen);
        
        if ($requiredCount === 0) {
            return 0; // No documents required
        }
        
        // Count uploaded documents that match required types
        $uploadedCount = $calonSiswa->dokumen()
            ->whereIn('jenis_dokumen', $requiredDokumen)
            ->count();
            
        return (int) min(100, ($uploadedCount / $requiredCount) * 100);
    }

    /**
     * Calculate kelengkapan status for dashboard info box
     */
    protected function calculateKelengkapanStatus(CalonSiswa $calonSiswa): array
    {
        $settings = PpdbSettings::first();
        $requiredDokumen = $settings?->dokumen_aktif ?? ['foto', 'kk', 'akta_lahir', 'ktp_ortu', 'ijazah', 'raport'];
        
        // Hitung dokumen yang sudah diupload
        $uploadedDokumen = $calonSiswa->dokumen()
            ->whereIn('jenis_dokumen', $requiredDokumen)
            ->count();
        $totalDokumen = count($requiredDokumen);
        
        // Hitung rapor yang sudah diisi nilai dan diupload file
        $nilaiRaporTerisi = $calonSiswa->nilaiRapor()
            ->whereNotNull('matematika')
            ->whereNotNull('ipa')
            ->whereNotNull('ips')
            ->count();
        
        // Hitung file rapor yang sudah diupload (semester 1-5)
        $fileRaporUploaded = $calonSiswa->dokumen()
            ->where('jenis_dokumen', 'like', 'rapor_sem_%')
            ->count();
        
        // Cek pilihan program jika aktif
        $jalur = $calonSiswa->jalurPendaftaran;
        $pilihanProgramAktif = $jalur && $jalur->pilihan_program_aktif;
        $pilihanProgramLengkap = !empty($calonSiswa->pilihan_program);
        
        return [
            'data_diri' => $calonSiswa->data_diri_completed,
            'data_ortu' => $calonSiswa->data_ortu_completed,
            'dokumen' => $calonSiswa->data_dokumen_completed,
            'dokumen_count' => $uploadedDokumen,
            'dokumen_total' => $totalDokumen,
            'nilai_rapor' => $calonSiswa->nilai_rapor_completed,
            'nilai_rapor_terisi' => $nilaiRaporTerisi,
            'nilai_rapor_total' => 5, // Semester 1-5
            'file_rapor_uploaded' => $fileRaporUploaded,
            'pilihan_program_aktif' => $pilihanProgramAktif,
            'pilihan_program_lengkap' => $pilihanProgramLengkap,
            'finalisasi' => $calonSiswa->is_finalisasi,
            // Rapor lengkap = nilai terisi DAN file diupload (5 semester)
            'rapor_lengkap' => $calonSiswa->nilai_rapor_completed && $fileRaporUploaded >= 5,
            'semua_lengkap' => $calonSiswa->data_diri_completed 
                && $calonSiswa->data_ortu_completed 
                && $calonSiswa->data_dokumen_completed 
                && $calonSiswa->nilai_rapor_completed
                && $fileRaporUploaded >= 5
                && (!$pilihanProgramAktif || $pilihanProgramLengkap),
        ];
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

    /**
     * Show pilihan program form
     */
    public function pilihanProgram()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with('jalurPendaftaran')
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data pendaftaran tidak ditemukan');
        }

        $jalur = $calonSiswa->jalurPendaftaran;

        // Check if pilihan program is enabled
        if (!$jalur->pilihan_program_aktif) {
            return redirect()->route('pendaftar.dashboard')
                ->with('info', 'Fitur pilihan program tidak diaktifkan untuk jalur Anda');
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return redirect()->route('pendaftar.dashboard')
                ->with('warning', 'Data sudah difinalisasi, tidak dapat mengubah pilihan program');
        }

        return view('pendaftar.dashboard.pilihan-program', compact('calonSiswa', 'jalur'));
    }

    /**
     * Store pilihan program
     */
    public function storePilihanProgram(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan'
            ], 404);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah difinalisasi, tidak dapat mengubah pilihan'
            ], 403);
        }

        $jalur = $calonSiswa->jalurPendaftaran;

        // Check if feature is enabled
        if (!$jalur->pilihan_program_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur pilihan program tidak diaktifkan'
            ], 403);
        }

        $validated = $request->validate([
            'pilihan_program' => 'required|string|in:' . implode(',', $jalur->pilihan_program_options ?? [])
        ], [
            'pilihan_program.required' => 'Pilihan program wajib dipilih',
            'pilihan_program.in' => 'Pilihan program tidak valid'
        ]);

        $calonSiswa->update([
            'pilihan_program' => $validated['pilihan_program']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pilihan program berhasil disimpan'
        ]);
    }

    /**
     * Show finalisasi page
     */
    public function finalisasi()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data pendaftaran tidak ditemukan');
        }

        // Cek apakah gelombang pendaftaran sudah ditutup
        $gelombang = $calonSiswa->gelombangPendaftaran;
        if ($gelombang && !$calonSiswa->is_finalisasi && $gelombang->status !== 'open') {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Pendaftaran gelombang ' . $gelombang->nama . ' sudah ditutup. Finalisasi tidak dapat dilakukan.');
        }

        // Get progress to check completion
        $progress = $this->calculateProgress($calonSiswa);

        // Check requirements
        $requirements = $this->checkFinalisasiRequirements($calonSiswa, $progress);

        return view('pendaftar.dashboard.finalisasi', compact('calonSiswa', 'progress', 'requirements'));
    }

    /**
     * Process finalisasi
     */
    public function storeFinalisasi(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'ortu', 'dokumen'])
            ->first();

        if (!$calonSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan'
            ], 404);
        }

        // Cek apakah gelombang pendaftaran sudah ditutup
        $gelombang = $calonSiswa->gelombangPendaftaran;
        if ($gelombang && $gelombang->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran gelombang ' . $gelombang->nama . ' sudah ditutup. Finalisasi tidak dapat dilakukan.'
            ], 403);
        }

        // Check if already finalized
        if ($calonSiswa->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah difinalisasi sebelumnya'
            ], 403);
        }

        // Validate confirmation
        $request->validate([
            'confirmation' => 'required|accepted'
        ], [
            'confirmation.accepted' => 'Anda harus menyetujui pernyataan finalisasi'
        ]);

        // Check requirements
        $progress = $this->calculateProgress($calonSiswa);
        $requirements = $this->checkFinalisasiRequirements($calonSiswa, $progress);

        if (!$requirements['can_finalize']) {
            return response()->json([
                'success' => false,
                'message' => 'Belum memenuhi syarat finalisasi: ' . implode(', ', $requirements['missing'])
            ], 422);
        }

        // PERUBAHAN ALUR:
        // 1. Finalisasi tidak lagi generate nomor_tes
        // 2. Nomor tes akan diberikan setelah admin memverifikasi semua dokumen
        // 3. Status tetap pending sampai dokumen diverifikasi

        // Update finalisasi data tanpa nomor_tes
        // Status verifikasi tetap 'pending' jika belum verified
        $calonSiswa->update([
            'is_finalisasi' => true,
            'tanggal_finalisasi' => now(),
            'status_verifikasi' => $calonSiswa->status_verifikasi === 'verified' ? 'verified' : 'pending',
            'status_admisi' => 'pending'
        ]);

        app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
            $calonSiswa->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
            \App\Services\MoodleIntegrationService::TRIGGER_FINALISASI
        );

        // Jika pendaftar sudah punya status verified (semua dokumen valid), generate nomor tes
        if ($calonSiswa->fresh()->allDokumenValid() && !$calonSiswa->nomor_tes) {
            $calonSiswa = $this->generateNomorTes($calonSiswa);
            
            return response()->json([
                'success' => true,
                'message' => 'Finalisasi berhasil! Semua dokumen telah diverifikasi. Nomor Tes Anda: ' . $calonSiswa->nomor_tes,
                'nomor_tes' => $calonSiswa->nomor_tes
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Finalisasi berhasil! Data Anda akan diverifikasi oleh admin. Nomor tes akan dikirim setelah dokumen diverifikasi.',
            'nomor_tes' => null
        ]);
    }

    /**
     * Generate Nomor Tes untuk calon siswa
     */
    public function generateNomorTes(CalonSiswa $calonSiswa): CalonSiswa
    {
        if ($calonSiswa->nomor_tes) {
            return $calonSiswa;
        }

        $calonSiswa->update([
            'nomor_tes' => $this->nomorService->generateNomorTes($calonSiswa),
        ]);

        app(\App\Services\MoodleIntegrationService::class)->syncCandidateIfNeeded(
            $calonSiswa->fresh(['user', 'tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran']),
            \App\Services\MoodleIntegrationService::TRIGGER_NOMOR_TES
        );
        
        return $calonSiswa->fresh();
    }

    /**
     * Check finalisasi requirements
     */
    protected function checkFinalisasiRequirements(CalonSiswa $calonSiswa, array $progress): array
    {
        $requirements = [
            'data_pribadi' => [
                'status' => $progress['data_diri'] >= 100,
                'label' => 'Data Pribadi Lengkap'
            ],
            'data_ortu' => [
                'status' => $progress['data_ortu'] >= 100,
                'label' => 'Data Orang Tua Lengkap'
            ],
            'dokumen' => [
                'status' => $progress['dokumen'] >= 100,
                'label' => 'Dokumen Lengkap'
            ],
            'nilai_rapor' => [
                'status' => $progress['nilai_rapor'] >= 100,
                'label' => 'Nilai Rapor Lengkap'
            ]
        ];

        // Add pilihan program check if enabled
        if (isset($progress['pilihan_program'])) {
            $requirements['pilihan_program'] = [
                'status' => $progress['pilihan_program'] >= 100,
                'label' => 'Pilihan Program Dipilih'
            ];
        }

        // Verifikasi tidak lagi menjadi syarat finalisasi
        // Pendaftar bisa finalisasi sebelum verifikasi dokumen
        // Nomor tes akan diberikan setelah admin memverifikasi semua dokumen

        // Determine if can finalize
        $canFinalize = true;
        $missing = [];

        foreach ($requirements as $key => $req) {
            if (!$req['status']) {
                $canFinalize = false;
                $missing[] = $req['label'];
            }
        }

        return [
            'requirements' => $requirements,
            'can_finalize' => $canFinalize,
            'missing' => $missing
        ];
    }

    /**
     * Normalize phone number to +62 format
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Convert various formats to +62
        if (substr($phone, 0, 1) === '0') {
            return '+62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) === '62') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 3) === '+62') {
            return $phone;
        }
        
        return $phone;
    }

    /**
     * Update registration location from dashboard
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'location_source' => 'required|in:gps,ip',
        ]);

        $updateData = [
            'registration_location_source' => $validated['location_source'],
        ];

        if ($validated['location_source'] === 'gps' && $validated['latitude'] && $validated['longitude']) {
            $updateData['registration_latitude'] = $validated['latitude'];
            $updateData['registration_longitude'] = $validated['longitude'];
            $updateData['registration_accuracy'] = $validated['accuracy'] ?? null;
            $updateData['registration_altitude'] = $validated['altitude'] ?? null;
            
            // Get address from coordinates using reverse geocoding (server-side)
            try {
                $geoResponse = file_get_contents("https://nominatim.openstreetmap.org/reverse?format=json&lat={$validated['latitude']}&lon={$validated['longitude']}&zoom=18&addressdetails=1", false, stream_context_create([
                    'http' => ['header' => "User-Agent: PPDB-App\r\n"]
                ]));
                
                if ($geoResponse) {
                    $geoData = json_decode($geoResponse, true);
                    if (isset($geoData['address'])) {
                        $addr = $geoData['address'];
                        $updateData['registration_city'] = $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? null;
                        $updateData['registration_region'] = $addr['state'] ?? null;
                        $updateData['registration_country'] = $addr['country'] ?? null;
                        $updateData['registration_address'] = $geoData['display_name'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                // Ignore geocoding errors
            }
        } elseif ($validated['location_source'] === 'ip') {
            // Get location from IP
            $ip = $request->ip();
            if ($ip && $ip !== '127.0.0.1') {
                try {
                    $ipResponse = file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country,lat,lon,isp");
                    if ($ipResponse) {
                        $ipData = json_decode($ipResponse, true);
                        if ($ipData && $ipData['status'] === 'success') {
                            $updateData['registration_latitude'] = $ipData['lat'] ?? null;
                            $updateData['registration_longitude'] = $ipData['lon'] ?? null;
                            $updateData['registration_city'] = $ipData['city'] ?? null;
                            $updateData['registration_region'] = $ipData['regionName'] ?? null;
                            $updateData['registration_country'] = $ipData['country'] ?? null;
                            $updateData['registration_isp'] = $ipData['isp'] ?? null;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore IP lookup errors
                }
            }
            $updateData['registration_ip'] = $ip;
        }

        // Update device info
        $userAgent = $request->userAgent();
        if (stripos($userAgent, 'mobile') !== false || stripos($userAgent, 'android') !== false || stripos($userAgent, 'iphone') !== false) {
            $updateData['registration_device'] = 'mobile';
        } elseif (stripos($userAgent, 'tablet') !== false || stripos($userAgent, 'ipad') !== false) {
            $updateData['registration_device'] = 'tablet';
        } else {
            $updateData['registration_device'] = 'desktop';
        }

        // Extract browser
        if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)[\/\s](\d+)/i', $userAgent, $matches)) {
            $updateData['registration_browser'] = $matches[1] . ' ' . $matches[2];
        }

        $calonSiswa->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi berhasil disimpan',
            'data' => [
                'location_source' => $updateData['registration_location_source'],
                'city' => $updateData['registration_city'] ?? null,
                'region' => $updateData['registration_region'] ?? null,
                'country' => $updateData['registration_country'] ?? null,
            ]
        ]);
    }

    /**
     * Mark envelope as opened (AJAX)
     */
    public function markEnvelopeOpened(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)->first();

        if ($calonSiswa) {
            // Log ke database (hanya sekali per pendaftar per tahun pelajaran)
            $log = EnvelopeOpenLog::firstOrCreate(
                [
                    'calon_siswa_id' => $calonSiswa->id,
                    'tahun_pelajaran_id' => $calonSiswa->tahun_pelajaran_id,
                ],
                [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'location_name' => $request->input('location_name'),
                    'opened_at' => now(),
                ]
            );

            // Update lokasi jika belum ada dan sekarang dikirim
            if ($log->wasRecentlyCreated === false && !$log->latitude && $request->input('latitude')) {
                $log->update([
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'location_name' => $request->input('location_name'),
                ]);
            }
        }

        session(['kelulusan_envelope_opened' => true]);
        return response()->json(['success' => true]);
    }

    /**
     * Halaman info kelulusan pendaftar
     */
    public function kelulusan()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'tahunPelajaran', 'kelulusan'])
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data pendaftaran tidak ditemukan');
        }

        // Get kelulusan setting
        $setting = \App\Models\KelulusanSetting::getActive($calonSiswa);

        // Check if pengumuman is enabled
        if (!$setting || !$setting->isPengumumanAktif()) {
            return redirect()->route('pendaftar.dashboard')
                ->with('info', 'Pengumuman kelulusan belum tersedia');
        }

        $kelulusan = $calonSiswa->kelulusan;

        // Jika ada kelulusan tapi amplop belum dibuka → redirect ke dashboard
        $envelopeOpened = EnvelopeOpenLog::hasOpened($calonSiswa->id, $calonSiswa->tahun_pelajaran_id)
            || session('kelulusan_envelope_opened');
        if ($kelulusan && !$envelopeOpened) {
            return redirect()->route('pendaftar.dashboard')
                ->with('info', 'Silakan buka amplop pengumuman di dashboard terlebih dahulu! ✉️');
        }

        $namaSekolah = \App\Models\SekolahSettings::getNamaSekolah();

        return view('pendaftar.dashboard.kelulusan', compact('calonSiswa', 'kelulusan', 'setting', 'namaSekolah'));
    }

    /**
     * Download Surat Pernyataan Orang Tua/Wali (PDF)
     */
    public function cetakSuratPernyataan()
    {
        ini_set('memory_limit', '256M');

        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with([
                'jalurPendaftaran',
                'gelombangPendaftaran',
                'tahunPelajaran',
                'ortu',
                'kelulusan',
            ])
            ->first();

        if (!$calonSiswa || !$calonSiswa->kelulusan || $calonSiswa->kelulusan->status !== 'lulus') {
            return redirect()->route('pendaftar.kelulusan')
                ->with('error', 'Surat pernyataan hanya tersedia untuk peserta yang dinyatakan lulus.');
        }

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();

        // Generate kop surat
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);

        // Data sekolah
        $namaSekolah = $sekolahSettings->nama_sekolah ?? config('app.name', 'Sekolah');
        $kota = $sekolahSettings->city->name ?? config('app.school_city', '............');
        $kepalaSekolah = $sekolahSettings->nama_kepala_sekolah ?? null;
        $nipKepalaSekolah = $sekolahSettings->nip_kepala_sekolah ?? null;

        // Data orang tua / wali
        $ortu = $calonSiswa->ortu;
        if ($ortu && $ortu->tinggal_dengan_wali && $ortu->nama_wali) {
            // Jika tinggal dengan wali, gunakan data wali
            $namaOrtu = $ortu->nama_wali;
            $pekerjaanOrtu = $ortu->pekerjaan_wali ? (CalonOrtu::PEKERJAAN[$ortu->pekerjaan_wali] ?? ucwords(str_replace('_', ' ', $ortu->pekerjaan_wali))) : '-';
            $hpOrtu = $ortu->no_hp_wali ?? '-';
            $hubunganOrtu = $ortu->hubungan_wali_label ?? 'Wali';
        } else {
            // Default: data ayah, fallback ke ibu
            $namaOrtu = $ortu->nama_ayah ?? $ortu->nama_ibu ?? '-';
            if ($ortu && $ortu->nama_ayah) {
                $pekerjaanOrtu = $ortu->pekerjaan_ayah_label ?? '-';
                $hpOrtu = $ortu->hp_ayah ?? $ortu->hp_ibu ?? '-';
                $hubunganOrtu = 'Orang Tua (Ayah)';
            } else {
                $pekerjaanOrtu = $ortu ? ($ortu->pekerjaan_ibu_label ?? '-') : '-';
                $hpOrtu = $ortu->hp_ibu ?? '-';
                $hubunganOrtu = 'Orang Tua (Ibu)';
            }
        }

        $alamatOrtu = $ortu ? $ortu->alamat_lengkap_ortu : '-';

        $pdf = Pdf::loadView('pendaftar.pdf.surat-pernyataan-ortu', compact(
            'calonSiswa',
            'kopHtml',
            'namaSekolah',
            'kota',
            'kepalaSekolah',
            'nipKepalaSekolah',
            'namaOrtu',
            'pekerjaanOrtu',
            'alamatOrtu',
            'hpOrtu',
            'hubunganOrtu'
        ));

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Surat Pernyataan Ortu ' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nama_lengkap) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download Surat Pernyataan Peserta Didik Baru (PDF)
     */
    public function cetakSuratPernyataanSiswa()
    {
        ini_set('memory_limit', '256M');

        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with([
                'jalurPendaftaran',
                'gelombangPendaftaran',
                'tahunPelajaran',
                'ortu',
                'kelulusan',
            ])
            ->first();

        if (!$calonSiswa || !$calonSiswa->kelulusan || $calonSiswa->kelulusan->status !== 'lulus') {
            return redirect()->route('pendaftar.kelulusan')
                ->with('error', 'Surat pernyataan hanya tersedia untuk peserta yang dinyatakan lulus.');
        }

        $sekolahSettings = \App\Models\SekolahSettings::with(['province', 'city'])->first();

        // Generate kop surat
        $kopHtml = $this->kopSuratService->renderKopHtml($sekolahSettings, true);

        // Data sekolah
        $namaSekolah = $sekolahSettings->nama_sekolah ?? config('app.name', 'Sekolah');
        $kota = $sekolahSettings->city->name ?? config('app.school_city', '............');

        // Data orang tua / wali
        $ortu = $calonSiswa->ortu;
        if ($ortu && $ortu->tinggal_dengan_wali && $ortu->nama_wali) {
            $namaOrtu = $ortu->nama_wali;
            $pekerjaanOrtu = $ortu->pekerjaan_wali ? (CalonOrtu::PEKERJAAN[$ortu->pekerjaan_wali] ?? ucwords(str_replace('_', ' ', $ortu->pekerjaan_wali))) : '-';
        } else {
            $namaOrtu = $ortu->nama_ayah ?? $ortu->nama_ibu ?? '-';
            if ($ortu && $ortu->nama_ayah) {
                $pekerjaanOrtu = $ortu->pekerjaan_ayah_label ?? '-';
            } else {
                $pekerjaanOrtu = $ortu ? ($ortu->pekerjaan_ibu_label ?? '-') : '-';
            }
        }

        $pdf = Pdf::loadView('pendaftar.pdf.surat-pernyataan-siswa', compact(
            'calonSiswa',
            'kopHtml',
            'namaSekolah',
            'kota',
            'namaOrtu',
            'pekerjaanOrtu'
        ));

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Surat Pernyataan Siswa ' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nama_lengkap) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download file lampiran Konsider
     */
    public function downloadKonsider()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with('kelulusan')
            ->first();

        if (!$calonSiswa || !$calonSiswa->kelulusan || $calonSiswa->kelulusan->status !== 'lulus') {
            return redirect()->route('pendaftar.kelulusan')
                ->with('error', 'File konsider hanya tersedia untuk peserta yang dinyatakan lulus.');
        }

        $setting = \App\Models\KelulusanSetting::getActive($calonSiswa);

        if (!$setting || !$setting->file_konsider) {
            return redirect()->route('pendaftar.kelulusan')
                ->with('error', 'File konsider tidak tersedia.');
        }

        $filePath = storage_path('app/public/' . $setting->file_konsider);

        if (!file_exists($filePath)) {
            return redirect()->route('pendaftar.kelulusan')
                ->with('error', 'File konsider tidak ditemukan.');
        }

        return response()->download($filePath, 'Lampiran Konsider.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * API: Cek tujuan pendaftaran berikutnya yang tersedia untuk pindah
     */
    public function cekGelombangBerikutnya()
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'kelulusan'])
            ->first();

        if (!$calonSiswa || !$this->bisaPindahGelombang($calonSiswa)) {
            return response()->json(['available' => false]);
        }

        // Cari tujuan pendaftaran berikutnya
        $gelombangBerikutnya = $this->findGelombangBerikutnya($calonSiswa);

        if (!$gelombangBerikutnya) {
            return response()->json(['available' => false]);
        }

        $statusKelulusan = $calonSiswa->kelulusan->status ?? null;

        return response()->json([
            'available' => true,
            'gelombang' => [
                'id' => $gelombangBerikutnya->id,
                'nama' => $gelombangBerikutnya->nama,
                'tanggal_tutup' => $gelombangBerikutnya->tanggal_tutup,
                'sisa_kuota' => $gelombangBerikutnya->sisaKuota(),
                'jalur_nama' => $gelombangBerikutnya->jalur->nama ?? '',
            ],
            'status_sebelumnya' => $statusKelulusan ? strtoupper($statusKelulusan) : 'BELUM ADA',
        ]);
    }

    /**
     * Proses pindah jalur/gelombang
     */
    public function pindahGelombang(Request $request)
    {
        $user = Auth::user();
        $calonSiswa = CalonSiswa::where('user_id', $user->id)
            ->with(['jalurPendaftaran', 'gelombangPendaftaran', 'kelulusan'])
            ->first();

        if (!$calonSiswa) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
        }

        // Validasi: harus memenuhi syarat pindah jalur/gelombang
        if (!$this->bisaPindahGelombang($calonSiswa)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memenuhi syarat untuk pindah gelombang.'], 403);
        }

        // Cari tujuan pendaftaran berikutnya
        $gelombangBerikutnya = $this->findGelombangBerikutnya($calonSiswa);

        if (!$gelombangBerikutnya) {
            return response()->json(['success' => false, 'message' => 'Tidak ada gelombang berikutnya yang tersedia.'], 404);
        }

        // Validasi kuota
        if ($gelombangBerikutnya->sisaKuota() <= 0) {
            return response()->json(['success' => false, 'message' => 'Kuota gelombang berikutnya sudah penuh.'], 422);
        }

        // Simpan data lama
        $gelombangLama = $calonSiswa->gelombangPendaftaran;
        $jalurLama = $calonSiswa->jalurPendaftaran;
        $jalurBaru = $gelombangBerikutnya->jalur;
        $nomorRegistrasiLama = $calonSiswa->nomor_registrasi;
        $nomorTesLama = $calonSiswa->nomor_tes;
        $statusKelulusanRaw = $calonSiswa->kelulusan->status ?? null;
        $statusKelulusanLama = match ($statusKelulusanRaw) {
            'lulus' => 'lulus',
            'tidak_lulus' => 'tidak_lulus',
            'cadangan' => 'cadangan',
            default => 'belum_ada',
        };

        try {
            \DB::beginTransaction();

            // 1. Generate nomor registrasi baru dari jalur/gelombang baru
            $calonSiswa->jalur_pendaftaran_id = $jalurBaru?->id;
            $calonSiswa->gelombang_pendaftaran_id = $gelombangBerikutnya->id;
            $calonSiswa->nomor_registrasi = null;
            $calonSiswa->nomor_tes = null;
            $calonSiswa->unsetRelation('jalurPendaftaran');
            $calonSiswa->unsetRelation('gelombangPendaftaran');
            $calonSiswa->setRelation('jalurPendaftaran', $jalurBaru);
            $calonSiswa->setRelation('gelombangPendaftaran', $gelombangBerikutnya);
            $nomorRegistrasiBaru = $this->nomorService->generateNomorRegistrasi($calonSiswa);
            $nomorTesBaru = null;
            if ($calonSiswa->is_finalisasi && $calonSiswa->status_verifikasi === 'verified') {
                $nomorTesBaru = $this->nomorService->generateNomorTes($calonSiswa);
            }

            // 2. Simpan riwayat perpindahan
            RiwayatGelombang::create([
                'calon_siswa_id' => $calonSiswa->id,
                'dari_gelombang_id' => $gelombangLama->id,
                'ke_gelombang_id' => $gelombangBerikutnya->id,
                'jalur_pendaftaran_id' => $jalurLama?->id,
                'tahun_pelajaran_id' => $calonSiswa->tahun_pelajaran_id,
                'nomor_registrasi_lama' => $nomorRegistrasiLama,
                'nomor_registrasi_baru' => $nomorRegistrasiBaru,
                'status_kelulusan_sebelumnya' => $statusKelulusanLama,
                'dipindahkan_oleh' => 'pendaftar',
                'catatan' => trim(implode(' | ', array_filter([
                    $statusKelulusanRaw ? "Status kelulusan lama: {$statusKelulusanRaw}" : "Belum ada status kelulusan",
                    $jalurLama && $jalurBaru && $jalurLama->id !== $jalurBaru->id
                        ? "Pindah jalur: {$jalurLama->nama} -> {$jalurBaru->nama}"
                        : null,
                    $nomorTesLama ? "Nomor tes lama: {$nomorTesLama}" : null,
                    $nomorTesBaru ? "Nomor tes baru: {$nomorTesBaru}" : "Nomor tes akan digenerate ulang",
                ]))),
            ]);

            // 3. Update calon siswa
            $calonSiswa->update([
                'jalur_pendaftaran_id' => $jalurBaru?->id,
                'gelombang_pendaftaran_id' => $gelombangBerikutnya->id,
                'nomor_registrasi' => $nomorRegistrasiBaru,
                'nomor_tes' => $nomorTesBaru,
            ]);

            // 4. Hapus record kelulusan lama jika ada (agar bisa di-set ulang di konteks baru)
            if ($calonSiswa->kelulusan) {
                $calonSiswa->kelulusan()->delete();
            }

            // 5. Reset status admisi agar siap diproses lagi di jalur/gelombang baru
            $calonSiswa->update([
                'status_admisi' => 'pending',
                'catatan_admisi' => null,
            ]);

            // 6. Hapus envelope open log (agar bisa buka amplop lagi nanti)
            EnvelopeOpenLog::where('calon_siswa_id', $calonSiswa->id)
                ->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                ->delete();

            // 7. Reset session envelope
            session()->forget('kelulusan_envelope_opened');

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil pindah ke {$gelombangBerikutnya->nama}" . ($jalurBaru ? " jalur {$jalurBaru->nama}" : '') . "! Nomor registrasi baru Anda: {$nomorRegistrasiBaru}",
                'nomor_registrasi_baru' => $nomorRegistrasiBaru,
                'gelombang_baru' => $gelombangBerikutnya->nama,
                'jalur_baru' => $jalurBaru?->nama,
                'nomor_tes_baru' => $nomorTesBaru,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Gagal pindah jalur/gelombang: ' . $e->getMessage(), [
                'calon_siswa_id' => $calonSiswa->id,
                'ke_gelombang_id' => $gelombangBerikutnya->id,
                'ke_jalur_id' => $jalurBaru?->id,
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'], 500);
        }
    }

    /**
     * Helper: Cek apakah pendaftar bisa pindah gelombang
     * Syarat:
     * 1. Yang lulus tidak bisa pindah
     * 2. Selain lulus boleh pindah selama ada tujuan aktif yang valid
     */
    private function bisaPindahGelombang(CalonSiswa $calonSiswa): bool
    {
        // Harus punya konteks saat ini
        if (!$calonSiswa->gelombangPendaftaran || !$calonSiswa->jalurPendaftaran) {
            return false;
        }

        // Yang lulus TIDAK bisa pindah
        if ($calonSiswa->kelulusan && $calonSiswa->kelulusan->status === 'lulus') {
            return false;
        }

        return $this->findGelombangBerikutnya($calonSiswa) !== null;
    }

    /**
     * Helper: Cari tujuan jalur/gelombang aktif yang tersedia di tahun yang sama.
     * Prioritas mengikuti konteks aktif admin pada tahun tersebut.
     */
    private function findGelombangBerikutnya(CalonSiswa $calonSiswa): ?GelombangPendaftaran
    {
        if (!$calonSiswa->tahun_pelajaran_id || !$calonSiswa->gelombangPendaftaran) {
            return null;
        }

        $context = AdminPpdbContext::resolve($calonSiswa->tahun_pelajaran_id);
        $targetGelombang = $context['selectedGelombang'];

        if (
            $targetGelombang
            && $targetGelombang->id !== $calonSiswa->gelombang_pendaftaran_id
            && $targetGelombang->bisaMenerimaPendaftar()
        ) {
            return $targetGelombang->loadMissing('jalur');
        }

        return GelombangPendaftaran::with('jalur')
            ->whereHas('jalur', function ($query) use ($calonSiswa) {
                $query->where('tahun_pelajaran_id', $calonSiswa->tahun_pelajaran_id)
                    ->where('is_active', true);
            })
            ->where('is_active', true)
            ->where('id', '!=', $calonSiswa->gelombang_pendaftaran_id)
            ->get()
            ->first(function (GelombangPendaftaran $gelombang) {
                return $gelombang->bisaMenerimaPendaftar();
            });
    }
}
