@extends('adminlte::page')

@section('title', 'Tambah Slider')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle mr-2"></i>Tambah Slider</h1>
        <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.settings.slider.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-image mr-1"></i> Gambar Slider</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="gambar">Upload Gambar <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*" required>
                                <label class="custom-file-label" for="gambar">Pilih gambar...</label>
                            </div>
                            @error('gambar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP. Maks: 5MB. Ukuran optimal: 1920x600px</small>
                        </div>
                        <div id="preview-container" class="text-center d-none mt-3">
                            <img id="preview-image" class="img-fluid img-thumbnail" style="max-height: 300px;">
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
                                   value="{{ old('judul') }}" 
                                   placeholder="Judul slider (tampil overlay di gambar)">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" 
                                      class="form-control @error('deskripsi') is-invalid @enderror" 
                                      placeholder="Deskripsi singkat...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="link">Link URL</label>
                            <input type="url" name="link" id="link" 
                                   class="form-control @error('link') is-invalid @enderror" 
                                   value="{{ old('link') }}" 
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
                                           value="{{ old('urutan', $maxUrutan + 1) }}" 
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
                                        <input type="checkbox" class="custom-control-input" id="aktif" name="aktif" value="1" {{ old('aktif', true) ? 'checked' : '' }}>
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
                            <i class="fas fa-save mr-1"></i> Simpan Slider
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
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Panduan</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-image text-primary mr-1"></i> Ukuran Gambar</h6>
                    <p class="small text-muted">Gunakan gambar dengan rasio lebar, idealnya 1920x600 pixel atau 3:1 ratio.</p>
                    
                    <h6><i class="fas fa-text-width text-info mr-1"></i> Judul & Deskripsi</h6>
                    <p class="small text-muted">Opsional. Jika diisi, akan ditampilkan sebagai overlay text di atas gambar.</p>
                    
                    <h6><i class="fas fa-link text-success mr-1"></i> Link</h6>
                    <p class="small text-muted">Jika diisi, pengunjung bisa klik slider untuk membuka link tersebut.</p>
                    
                    <h6><i class="fas fa-sort-numeric-up text-warning mr-1"></i> Urutan</h6>
                    <p class="small text-muted mb-0">Menentukan posisi slider. Urutan 1 akan tampil pertama, dst.</p>
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
