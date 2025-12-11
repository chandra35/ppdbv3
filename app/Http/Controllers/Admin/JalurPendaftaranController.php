<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JalurPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class JalurPendaftaranController extends Controller
{
    /**
     * Display listing
     */
    public function index(Request $request)
    {
        // Ambil semua tahun pelajaran
        $tahunPelajaranList = TahunPelajaran::orderByDesc('nama')->get();
        
        // Tahun pelajaran aktif (default: yang is_active = true)
        $tahunPelajaranAktif = $request->get('tahun_pelajaran_id');
        
        if (!$tahunPelajaranAktif) {
            $tpAktif = TahunPelajaran::getAktif();
            $tahunPelajaranAktif = $tpAktif?->id;
        }
        
        // Jika masih kosong, ambil yang pertama
        if (!$tahunPelajaranAktif && $tahunPelajaranList->isNotEmpty()) {
            $tahunPelajaranAktif = $tahunPelajaranList->first()->id;
        }
        
        $tahunPelajaranSelected = $tahunPelajaranList->firstWhere('id', $tahunPelajaranAktif);
        
        $jalurList = JalurPendaftaran::where('tahun_pelajaran_id', $tahunPelajaranAktif)
            ->with('tahunPelajaran')
            ->ordered()
            ->withCount('pendaftar')
            ->get();
        
        // Get jalur aktif saat ini
        $jalurAktif = JalurPendaftaran::getAktif();
        
        return view('admin.jalur.index', compact(
            'jalurList', 
            'tahunPelajaranList', 
            'tahunPelajaranAktif', 
            'tahunPelajaranSelected',
            'jalurAktif'
        ));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('nama')->get();
        
        // Default ke tahun pelajaran aktif
        $tahunPelajaranId = $request->get('tahun_pelajaran_id') ?? TahunPelajaran::getAktif()?->id;
        
        $warnaOptions = JalurPendaftaran::WARNA_OPTIONS;
        $iconOptions = JalurPendaftaran::ICON_OPTIONS;
        
        return view('admin.jalur.create', compact('tahunPelajaranList', 'tahunPelajaranId', 'warnaOptions', 'iconOptions'));
    }

    /**
     * Store new jalur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:jalur_pendaftaran,kode',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'deskripsi' => 'nullable|string',
            'persyaratan' => 'nullable|string',
            'tanggal_buka' => 'nullable|date',
            'tanggal_tutup' => 'nullable|date|after_or_equal:tanggal_buka',
            'kuota' => 'required|integer|min:1',
            'warna' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'prefix_nomor' => 'nullable|string|max:20',
            'tampil_di_publik' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);
        
        $validated['tampil_di_publik'] = $request->has('tampil_di_publik');
        $validated['is_active'] = false; // Selalu mulai sebagai non-aktif
        $validated['status'] = JalurPendaftaran::STATUS_DRAFT;
        $validated['urutan'] = $validated['urutan'] ?? (JalurPendaftaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])->max('urutan') + 1);
        
        $jalur = JalurPendaftaran::create($validated);
        
        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Jalur \"{$jalur->nama}\" berhasil dibuat!");
    }

    /**
     * Show detail
     */
    public function show(JalurPendaftaran $jalur)
    {
        $jalur->loadCount('pendaftar');
        
        return view('admin.jalur.show', compact('jalur'));
    }

    /**
     * Show edit form
     */
    public function edit(JalurPendaftaran $jalur)
    {
        $tahunPelajaranList = TahunPelajaran::orderByDesc('nama')->get();
        $warnaOptions = JalurPendaftaran::WARNA_OPTIONS;
        $iconOptions = JalurPendaftaran::ICON_OPTIONS;
        
        return view('admin.jalur.edit', compact('jalur', 'tahunPelajaranList', 'warnaOptions', 'iconOptions'));
    }

    /**
     * Update jalur
     */
    public function update(Request $request, JalurPendaftaran $jalur)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:jalur_pendaftaran,kode,' . $jalur->id,
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajarans,id',
            'deskripsi' => 'nullable|string',
            'persyaratan' => 'nullable|string',
            'tanggal_buka' => 'nullable|date',
            'tanggal_tutup' => 'nullable|date|after_or_equal:tanggal_buka',
            'kuota' => 'required|integer|min:1',
            'warna' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'prefix_nomor' => 'nullable|string|max:20',
            'tampil_di_publik' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);
        
        $validated['tampil_di_publik'] = $request->has('tampil_di_publik');
        
        $jalur->update($validated);
        
        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Jalur \"{$jalur->nama}\" berhasil diperbarui!");
    }

    /**
     * Delete jalur
     */
    public function destroy(JalurPendaftaran $jalur)
    {
        if ($jalur->kuota_terisi > 0) {
            return redirect()->back()->with('error', "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena sudah memiliki {$jalur->kuota_terisi} pendaftar!");
        }
        
        $nama = $jalur->nama;
        $jalur->delete();
        
        return redirect()
            ->route('admin.jalur.index')
            ->with('success', "Jalur \"{$nama}\" berhasil dihapus!");
    }

    /**
     * Aktifkan jalur - HANYA 1 JALUR YANG BISA AKTIF
     */
    public function aktifkanJalur(JalurPendaftaran $jalur)
    {
        // Cek apakah ada jalur lain yang aktif
        $jalurAktifLain = JalurPendaftaran::where('id', '!=', $jalur->id)
            ->where('tahun_pelajaran_id', $jalur->tahun_pelajaran_id)
            ->where('status', JalurPendaftaran::STATUS_OPEN)
            ->first();
        
        if ($jalurAktifLain) {
            return redirect()->back()->with('error', 
                "Tidak dapat mengaktifkan jalur ini! Jalur \"{$jalurAktifLain->nama}\" masih aktif. Tutup/selesaikan jalur tersebut terlebih dahulu."
            );
        }
        
        // Update status
        $jalur->update([
            'status' => JalurPendaftaran::STATUS_OPEN,
            'is_active' => true,
        ]);
        
        return redirect()->back()->with('success', 
            "Jalur \"{$jalur->nama}\" berhasil diaktifkan! Pendaftaran sekarang dibuka."
        );
    }

    /**
     * Tutup jalur sementara
     */
    public function tutupJalur(JalurPendaftaran $jalur)
    {
        $jalur->update([
            'status' => JalurPendaftaran::STATUS_CLOSED,
            'is_active' => false,
        ]);
        
        return redirect()->back()->with('success', 
            "Jalur \"{$jalur->nama}\" berhasil ditutup sementara. Pendaftaran dihentikan."
        );
    }

    /**
     * Selesaikan jalur (tidak bisa dibuka lagi)
     */
    public function selesaikanJalur(JalurPendaftaran $jalur)
    {
        $jalur->update([
            'status' => JalurPendaftaran::STATUS_FINISHED,
            'is_active' => false,
        ]);
        
        return redirect()->back()->with('success', 
            "Jalur \"{$jalur->nama}\" telah diselesaikan. Anda dapat mengaktifkan jalur lain sekarang."
        );
    }

    /**
     * Toggle status (for backward compatibility)
     */
    public function toggleStatus(JalurPendaftaran $jalur)
    {
        if ($jalur->status == JalurPendaftaran::STATUS_OPEN) {
            return $this->tutupJalur($jalur);
        } else if ($jalur->status == JalurPendaftaran::STATUS_CLOSED || $jalur->status == JalurPendaftaran::STATUS_DRAFT) {
            return $this->aktifkanJalur($jalur);
        }
        
        return redirect()->back()->with('error', 'Status jalur tidak dapat diubah.');
    }

    /**
     * Duplicate jalur untuk tahun pelajaran baru
     */
    public function duplicate(Request $request, JalurPendaftaran $jalur)
    {
        // Ambil tahun pelajaran aktif atau dari request
        $tahunPelajaranId = $request->get('tahun_pelajaran_id') ?? TahunPelajaran::getAktif()?->id;
        
        if (!$tahunPelajaranId) {
            return redirect()->back()->with('error', "Tidak ada tahun pelajaran yang tersedia. Buat tahun pelajaran terlebih dahulu.");
        }
        
        $tahunPelajaran = TahunPelajaran::find($tahunPelajaranId);
        
        // Cek apakah sudah ada jalur dengan kode sama di tahun pelajaran baru
        $existingJalur = JalurPendaftaran::where('kode', $jalur->kode)
            ->where('tahun_pelajaran_id', $tahunPelajaranId)
            ->first();
        
        if ($existingJalur) {
            return redirect()->back()->with('error', "Jalur dengan kode \"{$jalur->kode}\" sudah ada di tahun pelajaran {$tahunPelajaran->nama}!");
        }
        
        $newJalur = $jalur->replicate();
        $newJalur->tahun_pelajaran_id = $tahunPelajaranId;
        $newJalur->kuota_terisi = 0;
        $newJalur->counter_nomor = 0;
        $newJalur->is_active = false;
        $newJalur->status = JalurPendaftaran::STATUS_DRAFT;
        $newJalur->save();
        
        return redirect()
            ->route('admin.jalur.edit', $newJalur)
            ->with('success', "Jalur berhasil diduplikasi untuk tahun pelajaran {$tahunPelajaran->nama}. Silakan sesuaikan pengaturan.");
    }
}
