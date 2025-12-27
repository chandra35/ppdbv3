@extends('adminlte::page')

@section('title', 'Tambah GTK Manual - PPDB Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-plus mr-2"></i>Tambah GTK Manual</h1>
        <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah GTK</h3>
            </div>
            <form action="{{ route('admin.gtk.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> GTK yang ditambahkan secara manual akan ditandai dengan source "Manual"
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" 
                               class="form-control @error('nama_lengkap') is-invalid @enderror" 
                               value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" name="nip" id="nip" 
                                       class="form-control @error('nip') is-invalid @enderror" 
                                       value="{{ old('nip') }}" maxlength="18"
                                       placeholder="18 digit">
                                @error('nip')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nomor_hp">Nomor HP</label>
                                <input type="text" name="nomor_hp" id="nomor_hp" 
                                       class="form-control @error('nomor_hp') is-invalid @enderror" 
                                       value="{{ old('nomor_hp') }}"
                                       placeholder="08xxxxx">
                                @error('nomor_hp')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" 
                                        class="form-control @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori_ptk">Kategori PTK</label>
                                <select name="kategori_ptk" id="kategori_ptk" 
                                        class="form-control @error('kategori_ptk') is-invalid @enderror">
                                    <option value="">Pilih Kategori</option>
                                    <option value="Pendidik" {{ old('kategori_ptk') == 'Pendidik' ? 'selected' : '' }}>Pendidik</option>
                                    <option value="Tenaga Kependidikan" {{ old('kategori_ptk') == 'Tenaga Kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                                </select>
                                @error('kategori_ptk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_ptk">Jenis PTK</label>
                                <input type="text" name="jenis_ptk" id="jenis_ptk" 
                                       class="form-control @error('jenis_ptk') is-invalid @enderror" 
                                       value="{{ old('jenis_ptk') }}"
                                       placeholder="Guru Mapel, Kepala Sekolah, dll">
                                @error('jenis_ptk')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" 
                               class="form-control @error('jabatan') is-invalid @enderror" 
                               value="{{ old('jabatan') }}"
                               placeholder="Guru, Kepala Sekolah, Wakasek, dll">
                        @error('jabatan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <p><strong>GTK Manual vs Sync SIMANSA:</strong></p>
                <ul>
                    <li><strong>Manual:</strong> Data GTK yang ditambahkan langsung melalui form ini</li>
                    <li><strong>SIMANSA:</strong> Data GTK yang di-sync dari database SIMANSA</li>
                </ul>
                <hr>
                <p><strong>Field Wajib:</strong></p>
                <ul>
                    <li>Nama Lengkap</li>
                    <li>Email</li>
                </ul>
                <hr>
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    GTK yang ditambahkan dapat didaftarkan sebagai user PPDB setelah disimpan.
                </p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Validation hints
    $('#email').on('blur', function() {
        const email = $(this).val();
        if (email) {
            // Simple email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    $('#nip').on('input', function() {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
@stop
