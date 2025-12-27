@extends('adminlte::page')

@section('title', 'Edit GTK - PPDB Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-edit mr-2"></i>Edit GTK: {{ $gtk->nama_lengkap }}</h1>
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
                <h3 class="card-title">Form Edit GTK</h3>
            </div>
            <form action="{{ route('admin.gtk.update', $gtk->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    
                    @if($gtk->source === 'simansa')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            GTK ini berasal dari SIMANSA. Perubahan akan di-overwrite saat sync berikutnya.
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Source</label>
                        <div>
                            @if($gtk->source === 'manual')
                                <span class="badge badge-warning badge-lg">
                                    <i class="fas fa-edit"></i> Manual
                                </span>
                            @else
                                <span class="badge badge-primary badge-lg">
                                    <i class="fas fa-database"></i> SIMANSA
                                </span>
                                @if($gtk->synced_at)
                                    <small class="text-muted ml-2">
                                        Terakhir sync: {{ $gtk->synced_at->diffForHumans() }}
                                    </small>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" 
                               class="form-control @error('nama_lengkap') is-invalid @enderror" 
                               value="{{ old('nama_lengkap', $gtk->nama_lengkap) }}" required>
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
                                       value="{{ old('nip', $gtk->nip) }}" maxlength="18"
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
                                       value="{{ old('email', $gtk->email) }}" required>
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
                                       value="{{ old('nomor_hp', $gtk->nomor_hp) }}"
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
                                    <option value="L" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
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
                                    <option value="Pendidik" {{ old('kategori_ptk', $gtk->kategori_ptk) == 'Pendidik' ? 'selected' : '' }}>Pendidik</option>
                                    <option value="Tenaga Kependidikan" {{ old('kategori_ptk', $gtk->kategori_ptk) == 'Tenaga Kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
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
                                       value="{{ old('jenis_ptk', $gtk->jenis_ptk) }}"
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
                               value="{{ old('jabatan', $gtk->jabatan) }}"
                               placeholder="Guru, Kepala Sekolah, Wakasek, dll">
                        @error('jabatan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
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
                <h3 class="card-title">Informasi GTK</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>ID:</th>
                        <td>{{ $gtk->id }}</td>
                    </tr>
                    <tr>
                        <th>Source:</th>
                        <td>
                            @if($gtk->source === 'manual')
                                <span class="badge badge-warning">Manual</span>
                            @else
                                <span class="badge badge-primary">SIMANSA</span>
                            @endif
                        </td>
                    </tr>
                    @if($gtk->simansa_id)
                    <tr>
                        <th>SIMANSA ID:</th>
                        <td>{{ $gtk->simansa_id }}</td>
                    </tr>
                    @endif
                    @if($gtk->synced_at)
                    <tr>
                        <th>Terakhir Sync:</th>
                        <td>{{ $gtk->synced_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Dibuat:</th>
                        <td>{{ $gtk->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate:</th>
                        <td>{{ $gtk->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>

                @php
                    $user = \App\Models\User::where('email', $gtk->email)->first();
                @endphp

                @if($user)
                    <hr>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> 
                        Sudah terdaftar sebagai user PPDB
                    </div>
                @else
                    <hr>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle"></i> 
                        Belum terdaftar sebagai user PPDB
                    </div>
                @endif
            </div>
        </div>

        @if($gtk->source === 'simansa')
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Peringatan</h3>
            </div>
            <div class="card-body">
                <p><i class="fas fa-exclamation-triangle"></i> <strong>GTK dari SIMANSA</strong></p>
                <p class="text-sm">
                    Data GTK ini berasal dari sinkronisasi dengan database SIMANSA. 
                    Perubahan yang Anda lakukan akan di-overwrite saat sinkronisasi berikutnya dijalankan.
                </p>
                <p class="text-sm mb-0">
                    Untuk perubahan permanen, edit data di sistem SIMANSA kemudian lakukan sinkronisasi ulang.
                </p>
            </div>
        </div>
        @endif
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
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        }
    });

    $('#nip').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
@stop
