<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('urutan')->get();
        return view('admin.slider.index', compact('sliders'));
    }

    public function create()
    {
        $maxUrutan = Slider::max('urutan') ?? 0;
        return view('admin.slider.create', compact('maxUrutan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url',
            'urutan' => 'required|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('aktif') ? 'active' : 'inactive';
        unset($validated['aktif']);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('sliders', 'public');
        }

        $slider = Slider::create($validated);

        ActivityLog::log('create', "Menambah slider: {$slider->judul}", $slider);

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil ditambahkan');
    }

    public function edit(Slider $slider)
    {
        return view('admin.slider.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'judul' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'link' => 'nullable|url',
            'urutan' => 'required|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $validated['status'] = $request->boolean('aktif') ? 'active' : 'inactive';
        unset($validated['aktif']);

        if ($request->hasFile('gambar')) {
            // Delete old image
            if ($slider->gambar && Storage::disk('public')->exists($slider->gambar)) {
                Storage::disk('public')->delete($slider->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('sliders', 'public');
        }

        $slider->update($validated);

        ActivityLog::log('update', "Mengupdate slider: {$slider->judul}", $slider);

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil diupdate');
    }

    public function destroy(Slider $slider)
    {
        // Delete image
        if ($slider->gambar && Storage::disk('public')->exists($slider->gambar)) {
            Storage::disk('public')->delete($slider->gambar);
        }

        $sliderJudul = $slider->judul;
        $slider->delete();

        ActivityLog::log('delete', "Menghapus slider: {$sliderJudul}");

        return redirect()->route('admin.ppdb.slider.index')
            ->with('success', 'Slider berhasil dihapus');
    }

    /**
     * Toggle slider status
     */
    public function toggleStatus(Slider $slider)
    {
        $newStatus = $slider->status === 'active' ? 'inactive' : 'active';
        $slider->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('update', "Slider {$slider->judul} {$statusText}", $slider);

        return redirect()->back()->with('success', "Slider berhasil {$statusText}");
    }
}
