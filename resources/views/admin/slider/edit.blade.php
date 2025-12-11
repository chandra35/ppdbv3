@extends('adminlte::page')

@section('title', 'Edit Slider')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Edit Slider</h1>
        <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.settings.slider.update', $slider) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-image mr-1"></i> Gambar Slider</h3>
                    </div>
                    <div class="card-body">
                        @if($slider->gambar)
                        <div class="mb-3">
                            <label class="small text-muted">Gambar Saat Ini:</label>
                            <img src="{{ asset('storage/' . $slider->gambar) }}" class="img-fluid img-thumbnail d-block" style="max-height: 200px;">
                        </div>
                        @endif
                        <div class="form-group mb-0">
                            <label for="gambar">{{ $slider->gambar ? 'Ganti Gambar' : 'Upload Gambar' }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="gambar">{{ $slider->gambar ? 'Pilih gambar baru...' : 'Pilih gambar...' }}</label>
                            </div>
                            @error('gambar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP. Maks: 5MB. Ukuran optimal: 1920x600px</small>
                        </div>
                        <div id="preview-container" class="text-center d-none mt-3">
                            <label class="small text-muted">Preview Gambar Baru:</label>
                            <img id="preview-image" class="img-fluid img-thumbnail d-block mx-auto" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Detail Slider (Opsional)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="judul">Judul</label>
                            <input type="text" name="judul" id="judul" 
                                   class="form-control @error('judul') is-invalid @enderror" 
                                   value="{{ old('judul', $slider->judul) }}" 
                                   placeholder="Judul slider (tampil overlay di gambar)">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" 
                                      class="form-control @error('deskripsi') is-invalid @enderror" 
                                      placeholder="Deskripsi singkat...">{{ old('deskripsi', $slider->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="link">Link URL</label>
                            <input type="url" name="link" id="link" 
                                   class="form-control @error('link') is-invalid @enderror" 
                                   value="{{ old('link', $slider->link) }}" 
                                   placeholder="https://example.com">
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Link yang dibuka saat slider diklik</small>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-success">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Pengaturan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" name="urutan" id="urutan" 
                                           class="form-control @error('urutan') is-invalid @enderror" 
                                           value="{{ old('urutan', $slider->urutan) }}" 
                                           min="0" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Angka lebih kecil tampil lebih dulu</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="aktif" name="aktif" value="1" {{ old('aktif', $slider->status === 'active') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="aktif">Aktifkan Slider</label>
                                    </div>
                                    <small class="text-muted">Slider hanya tampil jika aktif</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update Slider
                        </button>
                        <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Info Slider</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ID:</span>
                        <code>{{ Str::limit($slider->id, 8) }}...</code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge badge-{{ $slider->status === 'active' ? 'success' : 'secondary' }}">
                            {{ $slider->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dibuat:</span>
                        <small>{{ $slider->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Diupdate:</span>
                        <small>{{ $slider->updated_at->format('d M Y H:i') }}</small>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-danger">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Zona Bahaya</h3>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Hapus slider ini secara permanen</p>
                    <form action="{{ route('admin.settings.slider.destroy', $slider) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slider ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-block">
                            <i class="fas fa-trash mr-1"></i> Hapus Slider
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@stop

@section('js')
<script>
    document.getElementById('gambar').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih gambar...';
        document.querySelector('.custom-file-label').textContent = fileName;
        
        if (e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('preview-image').src = event.target.result;
                document.getElementById('preview-container').classList.remove('d-none');
            };
            reader.readAsDataURL(e.target.files[0]);
        } else {
            document.getElementById('preview-container').classList.add('d-none');
        }
    });
</script>
@stop
