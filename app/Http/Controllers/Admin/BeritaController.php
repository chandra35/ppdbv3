<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\ActivityLog;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $query = Berita::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $beritas = $query->latest('tanggal_publikasi')->paginate(10)->withQueryString();
        $kategoris = Berita::kategoris();
        
        // Facebook service for status check
        $facebookService = new FacebookService();
        $facebookConfigured = $facebookService->isConfigured();

        return view('admin.ppdb.berita.index', compact('beritas', 'kategoris', 'facebookConfigured'));
    }

    public function create()
    {
        $kategoris = Berita::kategoris();
        return view('admin.ppdb.berita.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'required|string',
            'konten' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:draft,published,archived',
            'tanggal_publikasi' => 'nullable|date',
            'kategori' => 'nullable|string|max:50',
            'penulis' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'share_to_facebook' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['judul']) . '-' . time();
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['penulis'] = $validated['penulis'] ?? auth()->user()->name;
        
        // Set default kategori if empty
        if (empty($validated['kategori'])) {
            $validated['kategori'] = 'umum';
        }

        // Handle image upload
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        // Auto set publication date if published
        if ($validated['status'] === 'published' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        $berita = Berita::create($validated);

        ActivityLog::log('create', "Menambah berita: {$berita->judul}", $berita);

        // Share to Facebook if requested
        if ($request->boolean('share_to_facebook') && $validated['status'] === 'published') {
            $facebookService = new FacebookService();
            $result = $facebookService->shareBerita($berita);
            
            if ($result['success']) {
                $berita->update([
                    'shared_to_facebook' => true,
                    'facebook_post_id' => $result['post_id'] ?? null,
                ]);
                
                return redirect()->route('admin.ppdb.berita.index')
                    ->with('success', 'Berita berhasil ditambahkan dan dishare ke Facebook!');
            } else {
                return redirect()->route('admin.ppdb.berita.index')
                    ->with('success', 'Berita berhasil ditambahkan.')
                    ->with('warning', 'Gagal share ke Facebook: ' . $result['message']);
            }
        }

        return redirect()->route('admin.ppdb.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function show(Berita $berita)
    {
        return view('admin.ppdb.berita.show', compact('berita'));
    }

    public function edit(Berita $berita)
    {
        $kategoris = Berita::kategoris();
        return view('admin.ppdb.berita.edit', compact('berita', 'kategoris'));
    }

    public function update(Request $request, Berita $berita)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'required|string',
            'konten' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:draft,published,archived',
            'tanggal_publikasi' => 'nullable|date',
            'kategori' => 'nullable|string|max:50',
            'penulis' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        
        // Set default kategori if empty
        if (empty($validated['kategori'])) {
            $validated['kategori'] = 'umum';
        }

        // Handle image upload
        if ($request->hasFile('gambar')) {
            // Delete old image
            if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        // Auto set publication date if published
        if ($validated['status'] === 'published' && empty($validated['tanggal_publikasi']) && !$berita->tanggal_publikasi) {
            $validated['tanggal_publikasi'] = now();
        }

        $oldValues = $berita->toArray();
        $berita->update($validated);

        ActivityLog::log('update', "Mengupdate berita: {$berita->judul}", $berita, $oldValues, $berita->fresh()->toArray());

        return redirect()->route('admin.ppdb.berita.index')
            ->with('success', 'Berita berhasil diupdate.');
    }

    public function destroy(Berita $berita)
    {
        // Delete image
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        // Delete Facebook post if exists
        if ($berita->shared_to_facebook && $berita->facebook_post_id) {
            $facebookService = new FacebookService();
            $facebookService->deletePost($berita->facebook_post_id);
        }

        $beritaJudul = $berita->judul;
        $berita->delete();

        ActivityLog::log('delete', "Menghapus berita: {$beritaJudul}");

        return redirect()->back()->with('success', 'Berita berhasil dihapus.');
    }

    /**
     * Share berita to Facebook
     */
    public function shareToFacebook(Berita $berita)
    {
        if ($berita->status !== 'published') {
            return redirect()->back()->with('error', 'Hanya berita yang sudah dipublish yang dapat dishare.');
        }

        $facebookService = new FacebookService();
        $result = $facebookService->shareBerita($berita);

        if ($result['success']) {
            $berita->update([
                'shared_to_facebook' => true,
                'facebook_post_id' => $result['post_id'] ?? null,
            ]);

            ActivityLog::log('share', "Share berita ke Facebook: {$berita->judul}", $berita);

            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Berita $berita)
    {
        $berita->update(['is_featured' => !$berita->is_featured]);

        $status = $berita->is_featured ? 'ditampilkan sebagai featured' : 'dihapus dari featured';
        ActivityLog::log('update', "Berita {$berita->judul} {$status}", $berita);

        return redirect()->back()->with('success', "Berita berhasil {$status}.");
    }
}
