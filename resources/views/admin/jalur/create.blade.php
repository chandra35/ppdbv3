@extends('adminlte::page')

@section('title', 'Tambah Jalur Pendaftaran')

@section('content_header')
    <h1><i class="fas fa-plus-circle mr-2"></i>Tambah Jalur Pendaftaran</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.jalur.store') }}" method="POST">
            @csrf
            
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi Jalur</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Jalur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                       id="nama" name="nama" value="{{ old('nama') }}" 
                                       placeholder="Contoh: Jalur Prestasi" required>
                                @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode">Kode Jalur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode') is-invalid @enderror" 
                                       id="kode" name="kode" value="{{ old('kode') }}" 
                                       placeholder="PRESTASI" style="text-transform: uppercase;" required>
                                @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Kode unik tanpa spasi</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                                <select class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" 
                                        id="tahun_pelajaran_id" name="tahun_pelajaran_id" required>
                                    <option value="">-- Pilih Tahun Pelajaran --</option>
                                    @foreach($tahunPelajaranList as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $tahunPelajaranId) == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} @if($tp->is_active) (Aktif) @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('tahun_pelajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kuota">Kuota Pendaftar <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('kuota') is-invalid @enderror" 
                                       id="kuota" name="kuota" value="{{ old('kuota', 100) }}" min="1" required>
                                @error('kuota')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Periode Pendaftaran --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_buka">Tanggal Dibuka</label>
                                <input type="date" class="form-control @error('tanggal_buka') is-invalid @enderror" 
                                       id="tanggal_buka" name="tanggal_buka" value="{{ old('tanggal_buka') }}">
                                @error('tanggal_buka')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tanggal mulai menerima pendaftar</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_tutup">Tanggal Ditutup</label>
                                <input type="date" class="form-control @error('tanggal_tutup') is-invalid @enderror" 
                                       id="tanggal_tutup" name="tanggal_tutup" value="{{ old('tanggal_tutup') }}">
                                @error('tanggal_tutup')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tanggal terakhir menerima pendaftar</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" name="deskripsi" rows="2" 
                                  placeholder="Deskripsi singkat tentang jalur ini">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="persyaratan">Persyaratan Khusus</label>
                        <textarea class="form-control @error('persyaratan') is-invalid @enderror" 
                                  id="persyaratan" name="persyaratan" rows="4" 
                                  placeholder="Tuliskan persyaratan khusus untuk jalur ini...">{{ old('persyaratan') }}</textarea>
                        @error('persyaratan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Tampilan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="warna">Warna <span class="text-danger">*</span></label>
                                <select class="form-control @error('warna') is-invalid @enderror" id="warna" name="warna" required>
                                    @foreach($warnaOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('warna', 'primary') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('warna')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon <span class="text-danger">*</span></label>
                                <select class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" required>
                                    @foreach($iconOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('icon', 'fas fa-graduation-cap') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tampil_di_publik" name="tampil_di_publik" value="1" {{ old('tampil_di_publik', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="tampil_di_publik">Tampilkan di Halaman Publik</label>
                                </div>
                                <small class="text-muted">Info jalur muncul di landing page</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tampil_kuota" name="tampil_kuota" value="1" {{ old('tampil_kuota', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="tampil_kuota">Tampilkan Kuota ke Publik</label>
                                </div>
                                <small class="text-muted">Jika tidak dicentang, kuota disembunyikan</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="callout callout-info py-2 px-3 mb-0">
                                <small><i class="fas fa-info-circle mr-1"></i> Status pendaftaran dikelola via <strong>"Buka Pendaftaran"</strong> setelah jalur dibuat.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.jalur.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Jalur
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        {{-- Preview --}}
        <div class="card card-outline" id="preview-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview</h3>
            </div>
            <div class="card-body text-center">
                <i id="preview-icon" class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                <h5 id="preview-nama">Nama Jalur</h5>
                <span id="preview-badge" class="badge badge-primary">KODE</span>
            </div>
        </div>

        {{-- Info --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi</h3>
            </div>
            <div class="card-body">
                <p>Jalur Pendaftaran adalah kategori pendaftaran yang akan dilihat oleh calon siswa.</p>
                
                <h6 class="mt-3"><i class="fas fa-lightbulb text-warning mr-1"></i> Contoh Jalur:</h6>
                <ul class="pl-3">
                    <li><strong>Jalur Prestasi</strong> - Siswa berprestasi akademik/non-akademik</li>
                    <li><strong>Jalur Reguler</strong> - Pendaftaran umum</li>
                    <li><strong>Jalur Afirmasi</strong> - Siswa kurang mampu</li>
                    <li><strong>Jalur Zonasi</strong> - Berdasarkan zona tempat tinggal</li>
                    <li><strong>Jalur Pindahan</strong> - Siswa pindahan</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Auto uppercase kode
    $('#kode').on('input', function() {
        $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''));
    });
    
    // Preview
    function updatePreview() {
        var nama = $('#nama').val() || 'Nama Jalur';
        var kode = $('#kode').val() || 'KODE';
        var warna = $('#warna').val();
        var icon = $('#icon').val();
        
        $('#preview-nama').text(nama);
        $('#preview-badge').text(kode).attr('class', 'badge badge-' + warna);
        $('#preview-icon').attr('class', icon + ' fa-3x text-' + warna + ' mb-3');
        $('#preview-card').attr('class', 'card card-outline card-' + warna);
    }
    
    $('#nama, #kode, #warna, #icon').on('input change', updatePreview);
    updatePreview();
});
</script>
@stop
