<?php

namespace App\Http\Controllers\Pendaftar;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonOrtu;
use App\Models\CalonDokumen;
use App\Models\NilaiRapor;
use App\Models\PpdbSettings;
use App\Services\KopSuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    protected $kopSuratService;

    public function __construct(KopSuratService $kopSuratService)
    {
        $this->kopSuratService = $kopSuratService;
    }

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

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return back()->with('error', 'Data sudah difinalisasi dan tidak dapat diubah');
        }

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

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return back()->with('error', 'Data sudah difinalisasi dan tidak dapat diubah');
        }

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

        // Check if already finalized
        if ($calonSiswa && $calonSiswa->is_finalisasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah difinalisasi dan tidak dapat diubah'
            ], 403);
        }

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

        // Delete file
        Storage::disk('public')->delete($dokumen->file_path);
        
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
        
        $pdf = Pdf::loadView('pendaftar.pdf.bukti-registrasi', compact('calonSiswa', 'sekolah', 'kopHtml', 'qrCode', 'sekolahSettings'));
        
        $filename = 'bukti-registrasi-' . preg_replace('/[\/\\\:*?"<>|]/', '-', $calonSiswa->nomor_registrasi) . '.pdf';
        
        return $mode === 'stream' ? $pdf->stream($filename) : $pdf->download($filename);
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
        
        $password = $user->plain_password ?? '********';
        
        // Return HTML view for preview (not PDF)
        return view('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password'));
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
        
        $password = $user->plain_password ?? '********';
        
        $pdf = Pdf::loadView('pendaftar.pdf.kartu-ujian', compact('calonSiswa', 'sekolah', 'password', 'kopHtml'))
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
            ->with(['jalurPendaftaran', 'ortu', 'dokumen'])
            ->first();

        if (!$calonSiswa) {
            return redirect()->route('pendaftar.dashboard')
                ->with('error', 'Data pendaftaran tidak ditemukan');
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
            ->with(['jalurPendaftaran', 'ortu', 'dokumen'])
            ->first();

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

        // Check if already has nomor_tes (re-finalisasi after cancel)
        $nomorTes = $calonSiswa->nomor_tes;
        
        if (!$nomorTes) {
            // Generate nomor tes using settings (only if doesn't have one yet)
            $settings = \App\Models\PpdbSettings::first();
            $tahun = $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y');
            $jalurCode = strtoupper(substr($calonSiswa->jalurPendaftaran->nama ?? 'REG', 0, 3));
            
            // Get and update counter for this jalur
            $counters = $settings->nomor_tes_counter ?? [];
            $jalurKey = (string) $calonSiswa->jalur_pendaftaran_id;
            $counter = ($counters[$jalurKey] ?? 0) + 1;
            
            // Update counter atomically
            $counters[$jalurKey] = $counter;
            $settings->update(['nomor_tes_counter' => $counters]);
            
            // Generate nomor using format template
            $format = $settings->nomor_tes_format ?? '{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}';
            $nomor = str_pad($counter, $settings->nomor_tes_digit ?? 4, '0', STR_PAD_LEFT);
            
            $nomorTes = str_replace(
                ['{PREFIX}', '{TAHUN}', '{JALUR}', '{NOMOR}'],
                [$settings->nomor_tes_prefix ?? 'NTS', $tahun, $jalurCode, $nomor],
                $format
            );
        }

        // Update finalisasi data
        $calonSiswa->update([
            'is_finalisasi' => true,
            'tanggal_finalisasi' => now(),
            'nomor_tes' => $nomorTes,
            'status_admisi' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Finalisasi berhasil! Nomor Tes Anda: ' . $nomorTes,
            'nomor_tes' => $nomorTes
        ]);
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
            ],
            'verifikasi' => [
                'status' => $calonSiswa->status_verifikasi === 'verified',
                'label' => 'Data Terverifikasi'
            ]
        ];

        // Add pilihan program check if enabled
        if (isset($progress['pilihan_program'])) {
            $requirements['pilihan_program'] = [
                'status' => $progress['pilihan_program'] >= 100,
                'label' => 'Pilihan Program Dipilih'
            ];
        }

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
}
