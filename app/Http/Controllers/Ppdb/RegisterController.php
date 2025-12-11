<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class RegisterController extends Controller
{
    /**
     * Get active/open Jalur Pendaftaran
     * System automatically uses the single open jalur
     */
    protected function getJalurAktif()
    {
        return JalurPendaftaran::getAktif();
    }

    /**
     * Validate that registration is open
     */
    protected function validateRegistrasiDibuka()
    {
        $jalur = $this->getJalurAktif();
        
        if (!$jalur) {
            return [
                'status' => false,
                'message' => 'Maaf, pendaftaran PPDB sedang tidak dibuka.',
                'jalur' => null
            ];
        }
        
        if (!$jalur->bisaMenerimaPendaftar()) {
            return [
                'status' => false,
                'message' => 'Maaf, kuota pendaftaran jalur ini sudah penuh.',
                'jalur' => $jalur
            ];
        }
        
        return [
            'status' => true,
            'message' => null,
            'jalur' => $jalur
        ];
    }

    public function step1()
    {
        $validasi = $this->validateRegistrasiDibuka();
        
        if (!$validasi['status']) {
            return redirect()->route('ppdb.landing')
                ->with('warning', $validasi['message']);
        }
        
        $jalurAktif = $validasi['jalur'];
        
        return view('ppdb.step1', compact('jalurAktif'));
    }

    public function validateNisn(Request $request)
    {
        // Re-validate registration is still open
        $validasi = $this->validateRegistrasiDibuka();
        
        if (!$validasi['status']) {
            return redirect()->route('ppdb.landing')
                ->with('warning', $validasi['message']);
        }
        
        $jalur = $validasi['jalur'];
        
        $validated = $request->validate([
            'nisn' => 'required|digits:10|unique:calon_siswas,nisn',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'nisn.unique' => 'NISN sudah terdaftar di sistem.',
            'email.unique' => 'Email sudah digunakan. Silahkan gunakan email lain.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        // TODO: Validate NISN against Kemendikbud API
        // For now, mark as valid
        
        session([
            'ppdb_step' => 1,
            'ppdb_jalur_id' => $jalur->id,
            'ppdb_nisn' => $validated['nisn'],
            'ppdb_email' => $validated['email'],
            'ppdb_password' => $validated['password'],
            'ppdb_nisn_valid' => true,
        ]);

        return redirect()->route('ppdb.register.step2')->with('success', 'NISN valid! Lanjutkan ke step 2.');
    }

    public function step2()
    {
        if (session('ppdb_step') != 1) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        return view('ppdb.step2');
    }

    public function storePersonalData(Request $request)
    {
        if (session('ppdb_step') != 1) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'agama' => 'nullable|in:islam,kristen,katolik,hindu,budha,konghucu',
            'no_hp_pribadi' => 'nullable|string|max:15',
            'alamat_rumah' => 'required|string|max:500',
            'kelurahan' => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten_kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'no_hp_ortu' => 'nullable|string|max:15',
        ], [
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        session([
            'ppdb_step' => 2,
            'ppdb_nama_lengkap' => $validated['nama_lengkap'],
            'ppdb_tempat_lahir' => $validated['tempat_lahir'],
            'ppdb_tanggal_lahir' => $validated['tanggal_lahir'],
            'ppdb_jenis_kelamin' => $validated['jenis_kelamin'],
            'ppdb_agama' => $validated['agama'],
            'ppdb_no_hp_pribadi' => $validated['no_hp_pribadi'],
            'ppdb_alamat_rumah' => $validated['alamat_rumah'],
            'ppdb_kelurahan' => $validated['kelurahan'],
            'ppdb_kecamatan' => $validated['kecamatan'],
            'ppdb_kabupaten_kota' => $validated['kabupaten_kota'],
            'ppdb_provinsi' => $validated['provinsi'],
            'ppdb_no_hp_ortu' => $validated['no_hp_ortu'],
        ]);

        return redirect()->route('ppdb.register.step3')->with('success', 'Data pribadi tersimpan. Lanjutkan ke step 3.');
    }

    public function step3()
    {
        if (session('ppdb_step') != 2) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        return view('ppdb.step3');
    }

    public function uploadDocuments(Request $request)
    {
        if (session('ppdb_step') != 2) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $validated = $request->validate([
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'akta_kelahiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'kartu_keluarga' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto_4x6' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'piagam_prestasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'surat_sehat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'file' => 'File harus berformat PDF atau gambar.',
            'max' => 'Ukuran file maksimal 5MB.',
        ]);

        $uploadedDocs = [];
        $documentTypes = ['ijazah', 'akta_kelahiran', 'kartu_keluarga', 'foto_4x6', 'piagam_prestasi', 'surat_sehat'];

        foreach ($documentTypes as $docType) {
            if ($request->hasFile($docType)) {
                $file = $request->file($docType);
                $fileName = 'ppdb/' . session('ppdb_nisn') . '/' . $docType . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('ppdb/' . session('ppdb_nisn'), $file, $docType . '_' . time() . '.' . $file->getClientOriginalExtension());
                $uploadedDocs[$docType] = $path;
            }
        }

        session([
            'ppdb_step' => 3,
            'ppdb_uploaded_docs' => $uploadedDocs,
        ]);

        return redirect()->route('ppdb.register.step4')->with('success', 'Dokumen terupload. Lanjutkan ke step 4.');
    }

    public function step4()
    {
        if (session('ppdb_step') != 3) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $caalonSiswa = [
            'nisn' => session('ppdb_nisn'),
            'nama_lengkap' => session('ppdb_nama_lengkap'),
            'tempat_lahir' => session('ppdb_tempat_lahir'),
            'tanggal_lahir' => session('ppdb_tanggal_lahir'),
            'jenis_kelamin' => session('ppdb_jenis_kelamin'),
            'agama' => session('ppdb_agama'),
            'no_hp_pribadi' => session('ppdb_no_hp_pribadi'),
            'email' => session('ppdb_email'),
            'alamat_rumah' => session('ppdb_alamat_rumah'),
            'kelurahan' => session('ppdb_kelurahan'),
            'kecamatan' => session('ppdb_kecamatan'),
            'kabupaten_kota' => session('ppdb_kabupaten_kota'),
            'provinsi' => session('ppdb_provinsi'),
            'no_hp_ortu' => session('ppdb_no_hp_ortu'),
            'uploaded_docs_count' => count(session('ppdb_uploaded_docs', [])),
        ];

        return view('ppdb.step4', compact('caalonSiswa'));
    }

    public function confirmRegistration(Request $request)
    {
        if (session('ppdb_step') != 3) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Silahkan mulai dari step 1.');
        }

        $request->validate(['agree' => 'required|accepted']);

        // Validasi ulang gelombang
        $gelombangId = session('ppdb_gelombang_id');
        $jalurId = session('ppdb_jalur_id');
        
        $gelombang = $this->validateGelombang($gelombangId);
        
        if (!$gelombang) {
            return redirect()->route('ppdb.landing')
                ->with('error', 'Maaf, gelombang pendaftaran sudah ditutup atau kuota sudah penuh.');
        }

        try {
            // Create User
            $user = User::create([
                'id' => Uuid::uuid4()->toString(),
                'name' => session('ppdb_nama_lengkap'),
                'email' => session('ppdb_email'),
                'password' => Hash::make(session('ppdb_password')),
                'email_verified_at' => now(),
            ]);

            // Generate nomor_registrasi dari gelombang
            $nomorRegistrasi = $gelombang->generateNomorRegistrasi();

            // Create CalonSiswa
            $caalonSiswa = CalonSiswa::create([
                'id' => Uuid::uuid4()->toString(),
                'jalur_pendaftaran_id' => $jalurId,
                'gelombang_pendaftaran_id' => $gelombangId,
                'nisn' => session('ppdb_nisn'),
                'nisn_valid' => true,
                'nama_lengkap' => session('ppdb_nama_lengkap'),
                'tempat_lahir' => session('ppdb_tempat_lahir'),
                'tanggal_lahir' => session('ppdb_tanggal_lahir'),
                'jenis_kelamin' => session('ppdb_jenis_kelamin'),
                'agama' => session('ppdb_agama'),
                'no_hp_pribadi' => session('ppdb_no_hp_pribadi'),
                'email' => session('ppdb_email'),
                'alamat_rumah' => session('ppdb_alamat_rumah'),
                'kelurahan' => session('ppdb_kelurahan'),
                'kecamatan' => session('ppdb_kecamatan'),
                'kabupaten_kota' => session('ppdb_kabupaten_kota'),
                'provinsi' => session('ppdb_provinsi'),
                'no_hp_ortu' => session('ppdb_no_hp_ortu'),
                'user_id' => $user->id,
                'status_verifikasi' => 'pending',
                'status_admisi' => 'pending',
                'nomor_registrasi' => $nomorRegistrasi,
                'tanggal_registrasi' => now(),
            ]);

            // Upload documents to CalonDokumen
            $uploadedDocs = session('ppdb_uploaded_docs', []);
            foreach ($uploadedDocs as $jenisDoc => $filePath) {
                CalonDokumen::create([
                    'id' => Uuid::uuid4()->toString(),
                    'calon_siswa_id' => $caalonSiswa->id,
                    'jenis_dokumen' => $jenisDoc,
                    'file_path' => $filePath,
                    'file_size' => Storage::disk('public')->size($filePath),
                    'file_type' => Storage::disk('public')->mimeType($filePath),
                    'status_verifikasi' => 'pending',
                ]);
            }

            // Clear session
            session()->forget([
                'ppdb_step', 'ppdb_jalur_id', 'ppdb_gelombang_id', 'ppdb_nisn', 'ppdb_email', 'ppdb_password', 
                'ppdb_nisn_valid', 'ppdb_nama_lengkap', 'ppdb_tempat_lahir',
                'ppdb_tanggal_lahir', 'ppdb_jenis_kelamin', 'ppdb_agama',
                'ppdb_no_hp_pribadi', 'ppdb_alamat_rumah', 'ppdb_kelurahan',
                'ppdb_kecamatan', 'ppdb_kabupaten_kota', 'ppdb_provinsi',
                'ppdb_no_hp_ortu', 'ppdb_uploaded_docs',
            ]);

            return redirect()->route('ppdb.register.success', ['nomor_registrasi' => $nomorRegistrasi])
                ->with('success', 'Pendaftaran berhasil! Nomor registrasi Anda: ' . $nomorRegistrasi);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $nomor_registrasi = $request->query('nomor_registrasi', 'PPDB-' . date('Y') . '-00000');
        return view('ppdb.success', compact('nomor_registrasi'));
    }
}
