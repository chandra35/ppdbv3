@extends('adminlte::page')

@section('title', 'Edit Berita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Edit Berita</h1>
        <a href="{{ route('admin.settings.berita.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.settings.berita.update', $berita) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
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
                                   value="{{ old('judul', $berita->judul) }}" 
                                   placeholder="Masukkan judul berita..." required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" id="deskripsi" rows="3" 
                                      class="form-control @error('deskripsi') is-invalid @enderror" 
                                      placeholder="Ringkasan singkat untuk preview..." required>{{ old('deskripsi', $berita->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deskripsi ini akan ditampilkan di halaman daftar berita</small>
                        </div>

                        <div class="form-group">
                            <label for="konten">Konten Lengkap</label>
                            <textarea name="konten" id="konten" rows="10" 
                                      class="form-control @error('konten') is-invalid @enderror" 
                                      placeholder="Tulis konten lengkap berita...">{{ old('konten', $berita->konten) }}</textarea>
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
                                <option value="draft" {{ old('status', $berita->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $berita->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $berita->status) == 'archived' ? 'selected' : '' }}>Archived</option>
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
                                    <option value="{{ $key }}" {{ old('kategori', $berita->kategori) == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                   value="{{ old('tanggal_publikasi', $berita->tanggal_publikasi ? $berita->tanggal_publikasi->format('Y-m-d\TH:i') : '') }}">
                            @error('tanggal_publikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input type="text" name="penulis" id="penulis" 
                                   class="form-control @error('penulis') is-invalid @enderror"
                                   value="{{ old('penulis', $berita->penulis) }}"
                                   placeholder="Nama penulis">
                            @error('penulis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $berita->is_featured) ? 'checked' : '' }}>
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
                        @if($berita->gambar)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $berita->gambar) }}" class="img-fluid img-thumbnail" style="max-height: 150px;">
                            <small class="d-block text-muted mt-1">Gambar saat ini</small>
                        </div>
                        @endif
                        <div class="form-group mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('gambar') is-invalid @enderror" 
                                       id="gambar" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="gambar">{{ $berita->gambar ? 'Ganti gambar...' : 'Pilih gambar...' }}</label>
                            </div>
                            @error('gambar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPG, PNG, GIF, WEBP. Maks: 5MB</small>
                        </div>
                        <div id="preview-container" class="text-center d-none">
                            <img id="preview-image" class="img-fluid img-thumbnail" style="max-height: 200px;">
                            <small class="d-block text-muted mt-1">Preview gambar baru</small>
                        </div>
                    </div>
                </div>

                {{-- Facebook Status --}}
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fab fa-facebook mr-1"></i> Status Facebook</h3>
                    </div>
                    <div class="card-body">
                        @if($berita->shared_to_facebook)
                            <div class="alert alert-success py-2 mb-0">
                                <i class="fas fa-check-circle mr-1"></i>
                                Berita sudah dishare ke Facebook
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Belum dishare ke Facebook
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Statistik</h3>
                    </div>
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Views:</span>
                            <span class="badge badge-info">{{ number_format($berita->views ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Dibuat:</span>
                            <small>{{ $berita->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Diupdate:</span>
                            <small>{{ $berita->updated_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="card">
                    <div class="card-body py-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Update Berita
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
