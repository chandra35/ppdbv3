@extends('adminlte::page')

@section('title', 'Tambah Berita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle mr-2"></i>Tambah Berita</h1>
        <a href="{{ route('admin.settings.berita.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.settings.berita.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- Main Content --}}
            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Konten Berita</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="judul">Judul Berita <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" 
                                   class="form-control @error('judul') is-invalid @enderror" 
                                   value="{{ old('judul') }}" 
                                   placeholder="Masukkan judul berita..." required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" id="deskripsi" rows="3" 
                                      class="form-control @error('deskripsi') is-invalid @enderror" 
                                      placeholder="Ringkasan singkat untuk preview..." required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deskripsi ini akan ditampilkan di halaman daftar berita</small>
                        </div>

                        <div class="form-group">
                            <label for="konten">Konten Lengkap</label>
                            <textarea name="konten" id="konten" rows="10" 
                                      class="form-control @error('konten') is-invalid @enderror" 
                                      placeholder="Tulis konten lengkap berita...">{{ old('konten') }}</textarea>
                            @error('konten')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-md-4">
                {{-- Publish Settings --}}
                <div class="card card-outline card-info">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Pengaturan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $key => $label)
                                    <option value="{{ $key }}" {{ old('kategori') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_publikasi">Tanggal Publikasi</label>
                            <input type="datetime-local" name="tanggal_publikasi" id="tanggal_publikasi" 
                                   class="form-control @error('tanggal_publikasi') is-invalid @enderror"
                                   value="{{ old('tanggal_publikasi') }}">
                            @error('tanggal_publikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kosongkan untuk menggunakan waktu saat ini</small>
                        </div>

                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input type="text" name="penulis" id="penulis" 
                                   class="form-control @error('penulis') is-invalid @enderror"
                                   value="{{ old('penulis', auth()->user()->name) }}"
                                   placeholder="Nama penulis">
                            @error('penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">
                                    <i class="fas fa-star text-warning mr-1"></i> Featured (Tampilkan di Beranda)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Upload --}}
                <div class="card card-outline card-success">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-image mr-1"></i> Gambar</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="gambar">Pilih gambar...</label>
                            </div>
                            @error('gambar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP. Maks: 5MB</small>
                        </div>
                        <div id="preview-container" class="text-center d-none">
                            <img id="preview-image" class="img-fluid img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                </div>

                {{-- Facebook Share --}}
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fab fa-facebook mr-1"></i> Share ke Facebook</h3>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="share_to_facebook" name="share_to_facebook" value="1" {{ old('share_to_facebook') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="share_to_facebook">
                                Share otomatis ke Facebook saat publish
                            </label>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Hanya berlaku jika status "Published" dan Facebook sudah dikonfigurasi
                        </small>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="card">
                    <div class="card-body py-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Simpan Berita
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    // File input label update
    document.getElementById('gambar').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih gambar...';
        document.querySelector('.custom-file-label').textContent = fileName;
        
        // Preview image
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
