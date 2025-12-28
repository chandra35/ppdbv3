<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\SekolahSettings;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verify bukti registrasi by hash
     */
    public function verifyBukti($hash)
    {
        // Find calon siswa by verification hash
        $calonSiswa = CalonSiswa::where('verification_hash', $hash)
            ->with([
                'jalurPendaftaran', 
                'gelombangPendaftaran', 
                'tahunPelajaran',
                'ortu'
            ])
            ->first();

        if (!$calonSiswa) {
            return view('verification.not-found');
        }

        // Check if already finalized
        if (!$calonSiswa->is_finalisasi) {
            return view('verification.not-finalized', compact('calonSiswa'));
        }

        $sekolahSettings = SekolahSettings::first();

        return view('verification.bukti-registrasi', compact('calonSiswa', 'sekolahSettings'));
    }

    /**
     * Quick admin access to pendaftar detail (requires auth)
     */
    public function adminAccess($hash)
    {
        // Find calon siswa by verification hash
        $calonSiswa = CalonSiswa::where('verification_hash', $hash)->first();

        if (!$calonSiswa) {
            abort(404, 'Data tidak ditemukan');
        }

        // Redirect to admin pendaftar detail page
        return redirect()->route('admin.pendaftar.show', $calonSiswa->id);
    }
}
