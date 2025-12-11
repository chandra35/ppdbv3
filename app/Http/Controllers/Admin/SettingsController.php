<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbSettings;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = PpdbSettings::first();
        
        if (!$settings) {
            // Buat UUID untuk tahun_pelajaran_id sementara (nanti bisa diganti dengan relasi)
            $tahunPelajaranId = (string) Str::uuid();
            
            $settings = new PpdbSettings();
            $settings->tahun_pelajaran_id = $tahunPelajaranId;
            $settings->kuota_penerimaan = 200;
            $settings->tanggal_dibuka = now();
            $settings->tanggal_ditutup = now()->addMonths(3);
            $settings->status_pendaftaran = true;
            $settings->validasi_nisn_aktif = true;
            $settings->cegah_pendaftar_ganda = true;
            $settings->dokumen_aktif = ['kk', 'akta', 'ijazah', 'foto'];
            $settings->nomor_registrasi_prefix = 'PPDB';
            $settings->nomor_registrasi_counter = 0;
            $settings->save();
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'kuota_penerimaan' => 'required|integer|min:1',
            'tanggal_dibuka' => 'required|date',
            'tanggal_ditutup' => 'required|date|after:tanggal_dibuka',
            'status_pendaftaran' => 'required|boolean',
            'validasi_nisn_aktif' => 'nullable|boolean',
            'cegah_pendaftar_ganda' => 'nullable|boolean',
            'dokumen_aktif' => 'nullable|array',
            'nomor_registrasi_prefix' => 'required|string|max:20',
        ]);

        // Convert checkbox values
        $validated['validasi_nisn_aktif'] = $request->has('validasi_nisn_aktif');
        $validated['cegah_pendaftar_ganda'] = $request->has('cegah_pendaftar_ganda');
        $validated['status_pendaftaran'] = $request->input('status_pendaftaran') == '1';

        $settings = PpdbSettings::first();
        
        if (!$settings) {
            $validated['tahun_pelajaran_id'] = (string) Str::uuid();
            $settings = PpdbSettings::create($validated);
            ActivityLog::log('create', "Membuat pengaturan PPDB", $settings);
        } else {
            $oldValues = $settings->toArray();
            $settings->update($validated);
            ActivityLog::log('update', "Mengupdate pengaturan PPDB", $settings, $oldValues, $settings->fresh()->toArray());
        }

        return redirect()->back()->with('success', 'Pengaturan PPDB berhasil diupdate.');
    }
}
