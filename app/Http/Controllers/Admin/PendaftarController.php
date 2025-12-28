<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalonSiswa;
use App\Models\CalonDokumen;
use App\Models\DokumenVerifikasiHistory;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PendaftarController extends Controller
{
    public function index(Request $request)
    {
        $query = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'dokumen'])->orderBy('created_at', 'desc');

        // Filter by jalur
        if ($request->filled('jalur_id')) {
            $query->where('jalur_pendaftaran_id', $request->jalur_id);
        }

        // Filter by gelombang
        if ($request->filled('gelombang_id')) {
            $query->where('gelombang_pendaftaran_id', $request->gelombang_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_registrasi', 'like', "%{$search}%");
            });
        }

        $pendaftars = $query->paginate(20);
        
        // Get jalur list for filter
        $jalurList = JalurPendaftaran::with('tahunPelajaran')
            ->orderByDesc(function ($query) {
                $query->select('nama')
                      ->from('tahun_pelajarans')
                      ->whereColumn('tahun_pelajarans.id', 'jalur_pendaftaran.tahun_pelajaran_id')
                      ->limit(1);
            })
            ->orderBy('urutan')
            ->get();
            
        // Get gelombang list for filter
        $gelombangList = GelombangPendaftaran::with('jalur')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pendaftar.index', compact('pendaftars', 'jalurList', 'gelombangList'));
    }

    public function getDokumenList($id)
    {
        $pendaftar = CalonSiswa::with(['dokumen.verifiedBy'])->findOrFail($id);
        
        $dokumen = $pendaftar->dokumen->map(function ($dok) {
            return [
                'id' => $dok->id,
                'jenis_dokumen' => $dok->jenis_dokumen,
                'nama_dokumen_lengkap' => $dok->nama_dokumen_lengkap,
                'status_verifikasi' => $dok->status_verifikasi,
                'catatan_verifikasi' => $dok->catatan_verifikasi,
                'verified_by_name' => $dok->verifiedBy ? $dok->verifiedBy->name : null,
                'verified_at' => $dok->verified_at,
            ];
        });

        return response()->json([
            'success' => true,
            'dokumen' => $dokumen
        ]);
    }

    public function show($id)
    {
        $pendaftar = CalonSiswa::with([
            'user', 
            'ortu.provinsiOrtu',
            'ortu.kabupatenOrtu',
            'ortu.kecamatanOrtu',
            'ortu.kelurahanOrtu',
            'dokumen.histories.user',
            'dokumen.verifiedBy',
            'dokumen.revisedBy',
            'dokumen.cancelledBy',
            'jalurPendaftaran', 
            'gelombangPendaftaran',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa'
        ])->findOrFail($id);
        return view('admin.pendaftar.show', compact('pendaftar'));
    }

    public function verify(Request $request, $id)
    {
        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;
        
        $pendaftar->update([
            'status_verifikasi' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        ActivityLog::log('verify', "Memverifikasi pendaftar: {$pendaftar->nama_lengkap}", $pendaftar, 
            ['status' => $oldStatus], ['status' => 'verified']);

        return redirect()->back()->with('success', 'Pendaftar berhasil diverifikasi.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        $pendaftar->update([
            'status_verifikasi' => 'rejected',
            'rejection_reason' => $request->alasan,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        ActivityLog::log('reject', "Menolak pendaftar: {$pendaftar->nama_lengkap}. Alasan: {$request->alasan}", $pendaftar,
            ['status' => $oldStatus], ['status' => 'rejected']);

        return redirect()->back()->with('warning', 'Pendaftar ditolak.');
    }

    public function approve(Request $request, $id)
    {
        $pendaftar = CalonSiswa::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        $pendaftar->update([
            'status_verifikasi' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        ActivityLog::log('approve', "Menerima pendaftar: {$pendaftar->nama_lengkap}", $pendaftar,
            ['status' => $oldStatus], ['status' => 'approved']);

        return redirect()->back()->with('success', 'Pendaftar diterima.');
    }

    public function verifikasiDokumen(Request $request)
    {
        $query = CalonSiswa::with(['dokumen'])
            ->whereHas('dokumen')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->whereHas('dokumen', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        $pendaftars = $query->paginate(20);

        return view('admin.pendaftar.verifikasi-dokumen', compact('pendaftars'));
    }

    public function verifikasiDokumenDetail($id)
    {
        $pendaftar = CalonSiswa::with(['dokumen'])->findOrFail($id);
        return view('admin.pendaftar.verifikasi-dokumen-detail', compact('pendaftar'));
    }

    public function updateVerifikasiDokumen(Request $request, $id)
    {
        $dokumen = CalonDokumen::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,verified,rejected',
            'catatan' => 'nullable|string|max:500',
        ]);

        $oldStatus = $dokumen->status;

        $dokumen->update([
            'status' => $request->status,
            'catatan_verifikasi' => $request->catatan,
            'verified_at' => $request->status === 'verified' ? now() : null,
            'verified_by' => $request->status === 'verified' ? auth()->id() : null,
        ]);

        ActivityLog::log('update', "Memverifikasi dokumen {$dokumen->jenis_dokumen}", $dokumen,
            ['status' => $oldStatus], ['status' => $request->status]);

        return redirect()->back()->with('success', 'Verifikasi dokumen berhasil diupdate.');
    }

    public function approveDokumen($id)
    {
        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        $dokumen->update([
            'status_verifikasi' => 'valid',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'approve',
            'status_from' => $oldStatus,
            'status_to' => 'valid',
            'keterangan' => 'Dokumen disetujui',
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disetujui.',
                'status' => 'valid'
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui.');
    }

    public function rejectDokumen(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        $dokumen->update([
            'status_verifikasi' => 'invalid',
            'catatan_verifikasi' => $request->catatan,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'reject',
            'status_from' => $oldStatus,
            'status_to' => 'invalid',
            'keterangan' => $request->catatan,
        ]);
        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditolak.',
                'status' => 'invalid'
            ]);
        }

        return redirect()->back()->with('warning', 'Dokumen ditolak.');
    }

    public function revisiDokumen(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        $dokumen->update([
            'status_verifikasi' => 'revision',
            'catatan_verifikasi' => $request->catatan,
            'revised_by' => auth()->id(),
            'revised_at' => now(),
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'revisi',
            'status_from' => $oldStatus,
            'status_to' => 'revision',
            'keterangan' => $request->catatan,
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi dokumen telah dikirim.',
                'status' => 'revision'
            ]);
        }

        return redirect()->back()->with('info', 'Permintaan revisi dokumen telah dikirim.');
    }

    public function cancelVerifikasi(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        // Hanya bisa cancel jika statusnya valid atau invalid
        if (!in_array($oldStatus, ['valid', 'invalid'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dokumen yang sudah diverifikasi yang bisa dibatalkan.',
                ], 400);
            }
            return redirect()->back()->with('error', 'Hanya dokumen yang sudah diverifikasi yang bisa dibatalkan.');
        }
        
        $dokumen->update([
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'verifikasi_note' => $request->alasan,
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'cancel',
            'status_from' => $oldStatus,
            'status_to' => 'pending',
            'keterangan' => $request->alasan,
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Verifikasi dokumen berhasil dibatalkan.',
                'status' => 'pending'
            ]);
        }

        return redirect()->back()->with('info', 'Verifikasi dokumen berhasil dibatalkan.');
    }

    public function cancelRevisi(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $dokumen = CalonDokumen::findOrFail($id);
        $oldStatus = $dokumen->status_verifikasi;
        
        // Hanya bisa cancel revisi jika statusnya revision
        if ($oldStatus !== 'revision') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dokumen dengan status revisi yang bisa dibatalkan.',
                ], 400);
            }
            return redirect()->back()->with('error', 'Hanya dokumen dengan status revisi yang bisa dibatalkan.');
        }
        
        $dokumen->update([
            'status_verifikasi' => 'pending',
            'catatan_verifikasi' => null,
            'revised_by' => null,
            'revised_at' => null,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'verifikasi_note' => $request->alasan,
        ]);

        // Log history
        DokumenVerifikasiHistory::create([
            'dokumen_id' => $dokumen->id,
            'user_id' => auth()->id(),
            'action' => 'cancel_revisi',
            'status_from' => $oldStatus,
            'status_to' => 'pending',
            'keterangan' => $request->alasan,
        ]);

        // Auto-update status pendaftar
        $dokumen->calonSiswa->autoUpdateStatusVerifikasi();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi berhasil dibatalkan.',
                'status' => 'pending'
            ]);
        }

        return redirect()->back()->with('info', 'Permintaan revisi berhasil dibatalkan.');
    }
    
    /**
     * Edit pendaftar lengkap
     */
    public function edit($id)
    {
        $pendaftar = CalonSiswa::with(['user', 'jalurPendaftaran', 'gelombangPendaftaran', 'ortu'])->findOrFail($id);
        
        // Get jalur list
        $jalurList = JalurPendaftaran::with('tahunPelajaran')
            ->orderByDesc(function ($query) {
                $query->select('nama')
                      ->from('tahun_pelajarans')
                      ->whereColumn('tahun_pelajarans.id', 'jalur_pendaftaran.tahun_pelajaran_id')
                      ->limit(1);
            })
            ->orderBy('urutan')
            ->get();
            
        // Get gelombang list
        $gelombangList = GelombangPendaftaran::with('jalur')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get provinces for Laravolt
        $provinces = \Laravolt\Indonesia\Models\Province::orderBy('name')->get();
        
        return view('admin.pendaftar.edit', compact('pendaftar', 'jalurList', 'gelombangList', 'provinces'));
    }
    
    /**
     * Update pendaftar lengkap
     */
    public function update(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with(['user', 'ortu'])->findOrFail($id);
        $oldValues = $pendaftar->toArray();
        
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            // NISN tidak di-update (disabled di form)
            'nik' => 'required|digits:16',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|string',
            // Laravolt Indonesia fields
            'alamat_siswa' => 'nullable|string',
            'rt_siswa' => 'nullable|string|max:5',
            'rw_siswa' => 'nullable|string|max:5',
            'provinsi_id_siswa' => 'required|exists:indonesia_provinces,code',
            'kabupaten_id_siswa' => 'required|exists:indonesia_cities,code',
            'kecamatan_id_siswa' => 'required|exists:indonesia_districts,code',
            'kelurahan_id_siswa' => 'required|exists:indonesia_villages,code',
            'kodepos_siswa' => 'nullable|string|max:10',
            'nomor_hp' => 'required|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Email updated via user
            'jalur_pendaftaran_id' => 'nullable|exists:jalur_pendaftaran,id',
            'gelombang_pendaftaran_id' => 'nullable|exists:gelombang_pendaftaran,id',
            // Data Orang Tua
            'no_kk' => 'nullable|digits:16',
            // Data Orang Tua - Ayah
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah' => 'nullable|digits:16',
            'tempat_lahir_ayah' => 'nullable|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'penghasilan_ayah' => 'nullable|string|max:50',
            'hp_ayah' => 'nullable|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Data Orang Tua - Ibu
            'nama_ibu' => 'nullable|string|max:100',
            'nik_ibu' => 'nullable|digits:16',
            'tempat_lahir_ibu' => 'nullable|string|max:100',
            'tanggal_lahir_ibu' => 'nullable|date',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'penghasilan_ibu' => 'nullable|string|max:50',
            'hp_ibu' => 'nullable|string|regex:/^0[0-9]{9,12}$/|max:20',
            // Data Asal Sekolah
            'nama_sekolah_asal' => 'nullable|string|max:255',
            'npsn_asal_sekolah' => 'nullable|string|max:20',
        ], [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'nik_ayah.digits' => 'NIK Ayah harus 16 digit angka.',
            'nik_ibu.digits' => 'NIK Ibu harus 16 digit angka.',
            'no_kk.digits' => 'No. KK harus 16 digit angka.',
            'nomor_hp.regex' => 'Format No. HP harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ayah.regex' => 'Format No. HP Ayah harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
            'hp_ibu.regex' => 'Format No. HP Ibu harus 08xxxxxxxxxx (0 diikuti 9-12 digit).',
        ]);
        
        // Convert phone numbers from 08xx to +628xx format
        $phoneFields = ['nomor_hp', 'hp_ayah', 'hp_ibu'];
        foreach ($phoneFields as $field) {
            if (!empty($validated[$field])) {
                $phone = $validated[$field];
                // If starts with 0, convert to +62
                if (substr($phone, 0, 1) === '0') {
                    $validated[$field] = '+62' . substr($phone, 1);
                }
                // If already starts with +62, keep it
                // If starts with 62 without +, add +
                elseif (substr($phone, 0, 2) === '62') {
                    $validated[$field] = '+' . $phone;
                }
            }
        }
        
        // Update data siswa
        $pendaftar->update([
            'nama_lengkap' => $validated['nama_lengkap'],
            'nik' => $validated['nik'],
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
            'npsn_asal_sekolah' => $validated['npsn_asal_sekolah'] ?? null,
        ]);
        
        // Update atau create data orang tua
        if ($pendaftar->ortu) {
            $pendaftar->ortu->update([
                'no_kk' => $validated['no_kk'] ?? null,
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
                'nama_ibu' => $validated['nama_ibu'] ?? null,
                'nik_ibu' => $validated['nik_ibu'] ?? null,
                'tempat_lahir_ibu' => $validated['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $validated['tanggal_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $validated['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
                'hp_ibu' => $validated['hp_ibu'] ?? null,
            ]);
        } else {
            $pendaftar->ortu()->create([
                'no_kk' => $validated['no_kk'] ?? null,
                'nama_ayah' => $validated['nama_ayah'] ?? null,
                'nik_ayah' => $validated['nik_ayah'] ?? null,
                'tempat_lahir_ayah' => $validated['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $validated['tanggal_lahir_ayah'] ?? null,
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'hp_ayah' => $validated['hp_ayah'] ?? null,
                'nama_ibu' => $validated['nama_ibu'] ?? null,
                'nik_ibu' => $validated['nik_ibu'] ?? null,
                'tempat_lahir_ibu' => $validated['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $validated['tanggal_lahir_ibu'] ?? null,
                'pendidikan_ibu' => $validated['pendidikan_ibu'] ?? null,
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'] ?? null,
                'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
                'hp_ibu' => $validated['hp_ibu'] ?? null,
            ]);
        }
        
        // Update user email if provided and changed
        if ($request->filled('email') && $pendaftar->user && $pendaftar->user->email !== $request->email) {
            $pendaftar->user->update(['email' => $request->email]);
        }
        
        ActivityLog::log('update', "Mengupdate data pendaftar: {$pendaftar->nama_lengkap}", $pendaftar, 
            $oldValues, $pendaftar->fresh()->toArray());
        
        return redirect()->route('admin.pendaftar.show', $id)
            ->with('success', 'Data pendaftar berhasil diperbarui.');
    }
    
    /**
     * Reset password pendaftar
     */
    public function resetPassword(Request $request, $id)
    {
        $pendaftar = CalonSiswa::with('user')->findOrFail($id);
        
        if (!$pendaftar->user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }
        
        // Generate random password
        $newPassword = Str::random(8);
        
        $pendaftar->user->update([
            'password' => Hash::make($newPassword),
            'plain_password' => $newPassword, // Store plain password temporarily for admin to see
        ]);
        
        ActivityLog::log('update', "Reset password pendaftar: {$pendaftar->nama_lengkap}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset.',
                'password' => $newPassword
            ]);
        }
        
        return redirect()->back()->with([
            'success' => 'Password berhasil direset.',
            'new_password' => $newPassword
        ]);
    }
    
    /**
     * Show password pendaftar
     */
    public function showPassword($id)
    {
        $pendaftar = CalonSiswa::with('user')->findOrFail($id);
        
        if (!$pendaftar->user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'password' => $pendaftar->user->plain_password ?? 'Password tidak tersedia (gunakan reset password)',
            'email' => $pendaftar->user->email
        ]);
    }

    /**
     * Soft delete pendaftar (move to trash).
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $pendaftar = CalonSiswa::findOrFail($id);
            
            // Set deleted_by and deleted_reason before soft delete
            $pendaftar->deleted_by = auth()->id();
            $pendaftar->deleted_reason = $request->reason ?? 'Dihapus oleh admin';
            $pendaftar->save();
            
            // Soft delete (akan trigger cascade di model)
            $pendaftar->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'App\Models\CalonSiswa',
                'model_id' => $pendaftar->id,
                'description' => "Menghapus pendaftar: {$pendaftar->nama_lengkap} (NISN: {$pendaftar->nisn}). Alasan: " . ($request->reason ?? 'Dihapus oleh admin'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()
                ->route('admin.data.delete-list')
                ->with('success', 'Data pendaftar berhasil dihapus dan dipindah ke Data Terhapus. Data masih bisa di-restore.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}

