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
            'tampil_kuota' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
        ]);
        
        $validated['tampil_di_publik'] = $request->has('tampil_di_publik');
        $validated['tampil_kuota'] = $request->has('tampil_kuota');
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
            'tampil_kuota' => 'boolean',
            'urutan' => 'nullable|integer|min:1',
            // Pilihan Program fields
            'pilihan_program_aktif' => 'nullable|boolean',
            'pilihan_program_tipe' => 'nullable|string|in:reguler_asrama,jurusan,custom',
            'pilihan_program_options' => 'nullable|array',
            'pilihan_program_options.*' => 'nullable|string|max:50',
            'pilihan_program_catatan' => 'nullable|string',
        ]);
        
        $validated['tampil_di_publik'] = $request->has('tampil_di_publik');
        $validated['tampil_kuota'] = $request->has('tampil_kuota');
        $validated['pilihan_program_aktif'] = $request->has('pilihan_program_aktif');
        
        // Handle pilihan_program_options JSON encoding
        if ($validated['pilihan_program_aktif'] && isset($validated['pilihan_program_options'])) {
            // Filter empty values
            $validated['pilihan_program_options'] = array_filter($validated['pilihan_program_options']);
        } else {
            $validated['pilihan_program_options'] = null;
        }
        
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
        // Proteksi 1: Tidak bisa hapus jalur yang sedang aktif/dibuka
        if ($jalur->status === JalurPendaftaran::STATUS_OPEN) {
            return redirect()->back()->with('error', 
                "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena sedang dibuka! Tutup atau selesaikan jalur terlebih dahulu."
            );
        }
        
        // Proteksi 2: Tidak bisa hapus jalur yang sudah memiliki pendaftar
        if ($jalur->kuota_terisi > 0) {
            return redirect()->back()->with('error', 
                "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena sudah memiliki {$jalur->kuota_terisi} pendaftar!"
            );
        }
        
        // Proteksi 3: Cek pendaftar dari relasi calon_siswa
        $pendaftarCount = \App\Models\CalonSiswa::where('jalur_pendaftaran_id', $jalur->id)->count();
        if ($pendaftarCount > 0) {
            return redirect()->back()->with('error', 
                "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena sudah memiliki {$pendaftarCount} data pendaftar terkait!"
            );
        }
        
        // Proteksi 4: Cek gelombang yang masih aktif
        $gelombangAktif = $jalur->gelombang()->where('status', 'open')->count();
        if ($gelombangAktif > 0) {
            return redirect()->back()->with('error', 
                "Jalur \"{$jalur->nama}\" tidak dapat dihapus karena masih memiliki {$gelombangAktif} gelombang yang aktif!"
            );
        }
        
        $nama = $jalur->nama;
        
        // Hapus semua gelombang terlebih dahulu
        $jalur->gelombang()->delete();
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

    // ========================================
    // GELOMBANG MANAGEMENT
    // ========================================

    /**
     * Store a new gelombang for jalur
     */
    public function storeGelombang(Request $request, JalurPendaftaran $jalur)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tanggal_buka' => 'required|date',
            'waktu_buka' => 'nullable|date_format:H:i',
            'tanggal_tutup' => 'required|date|after_or_equal:tanggal_buka',
            'waktu_tutup' => 'nullable|date_format:H:i',
            'kuota' => 'nullable|integer|min:1',
            'biaya_pendaftaran' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['jalur_id'] = $jalur->id;
        $validated['waktu_buka'] = $validated['waktu_buka'] ?? '00:00:00';
        $validated['waktu_tutup'] = $validated['waktu_tutup'] ?? '23:59:59';
        $validated['is_active'] = $request->has('is_active');
        $validated['status'] = $validated['is_active'] ? GelombangPendaftaran::STATUS_OPEN : GelombangPendaftaran::STATUS_DRAFT;
        $validated['urutan'] = $jalur->gelombang()->max('urutan') + 1;
        $validated['prefix_nomor'] = $jalur->prefix_nomor ?? 'REG';

        $gelombang = GelombangPendaftaran::create($validated);

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$gelombang->nama}\" berhasil ditambahkan!");
    }

    /**
     * Update gelombang
     */
    public function updateGelombang(Request $request, JalurPendaftaran $jalur, GelombangPendaftaran $gelombang)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tanggal_buka' => 'required|date',
            'waktu_buka' => 'nullable|date_format:H:i',
            'tanggal_tutup' => 'required|date|after_or_equal:tanggal_buka',
            'waktu_tutup' => 'nullable|date_format:H:i',
            'kuota' => 'nullable|integer|min:1',
            'biaya_pendaftaran' => 'nullable|numeric|min:0',
            'tampil_nama_gelombang' => 'boolean',
            'tampil_kuota' => 'boolean',
        ]);

        $validated['waktu_buka'] = $validated['waktu_buka'] ?? '00:00:00';
        $validated['waktu_tutup'] = $validated['waktu_tutup'] ?? '23:59:59';
        $validated['tampil_nama_gelombang'] = $request->has('tampil_nama_gelombang');
        $validated['tampil_kuota'] = $request->has('tampil_kuota');

        $gelombang->update($validated);

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$gelombang->nama}\" berhasil diperbarui!");
    }

    /**
     * Delete gelombang
     */
    public function destroyGelombang(JalurPendaftaran $jalur, GelombangPendaftaran $gelombang)
    {
        // Proteksi 1: Tidak bisa hapus gelombang yang sedang aktif/dibuka
        if ($gelombang->status === GelombangPendaftaran::STATUS_OPEN) {
            return redirect()
                ->route('admin.jalur.show', $jalur)
                ->with('error', "Gelombang \"{$gelombang->nama}\" tidak dapat dihapus karena sedang dibuka! Tutup atau selesaikan gelombang terlebih dahulu.");
        }
        
        // Proteksi 2: Tidak bisa hapus gelombang yang sudah memiliki kuota terisi
        if ($gelombang->kuota_terisi > 0) {
            return redirect()
                ->route('admin.jalur.show', $jalur)
                ->with('error', "Gelombang \"{$gelombang->nama}\" tidak dapat dihapus karena sudah memiliki {$gelombang->kuota_terisi} pendaftar!");
        }
        
        // Proteksi 3: Cek pendaftar dari relasi calon_siswa
        $pendaftarCount = \App\Models\CalonSiswa::where('gelombang_pendaftaran_id', $gelombang->id)->count();
        if ($pendaftarCount > 0) {
            return redirect()
                ->route('admin.jalur.show', $jalur)
                ->with('error', "Gelombang \"{$gelombang->nama}\" tidak dapat dihapus karena sudah memiliki {$pendaftarCount} data pendaftar terkait!");
        }

        $nama = $gelombang->nama;
        $gelombang->delete();

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$nama}\" berhasil dihapus!");
    }

    /**
     * Open gelombang (set status to open)
     */
    public function bukaGelombang(JalurPendaftaran $jalur, GelombangPendaftaran $gelombang)
    {
        $gelombang->update([
            'status' => GelombangPendaftaran::STATUS_OPEN,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$gelombang->nama}\" berhasil dibuka!");
    }

    /**
     * Close gelombang (set status to closed)
     */
    public function tutupGelombang(JalurPendaftaran $jalur, GelombangPendaftaran $gelombang)
    {
        $gelombang->update([
            'status' => GelombangPendaftaran::STATUS_CLOSED,
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$gelombang->nama}\" berhasil ditutup!");
    }

    /**
     * Finish gelombang (set status to finished)
     */
    public function selesaikanGelombang(JalurPendaftaran $jalur, GelombangPendaftaran $gelombang)
    {
        $gelombang->update([
            'status' => GelombangPendaftaran::STATUS_FINISHED,
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.jalur.show', $jalur)
            ->with('success', "Gelombang \"{$gelombang->nama}\" telah diselesaikan!");
    }
}
