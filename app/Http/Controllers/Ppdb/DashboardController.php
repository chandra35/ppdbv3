<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $calonSiswa = Auth::user()->calonSiswa()->first();
        
        if (!$calonSiswa) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Anda belum melakukan pendaftaran. Silahkan daftar terlebih dahulu.');
        }

        return view('dashboard.calon-siswa', compact('calonSiswa'));
    }

    public function buktiRegistrasi()
    {
        $calonSiswa = Auth::user()->calonSiswa()->first();
        
        if (!$calonSiswa) {
            return redirect()->route('ppdb.register.step1')->with('warning', 'Anda belum melakukan pendaftaran.');
        }

        return view('dashboard.bukti-registrasi', compact('calonSiswa'));
    }

    public function printBuktiRegistrasi()
    {
        $calonSiswa = Auth::user()->calonSiswa()->first();
        
        if (!$calonSiswa) {
            return redirect()->route('ppdb.register.step1');
        }

        // Generate PDF or print
        return view('dashboard.bukti-registrasi', compact('calonSiswa'));
    }
}
