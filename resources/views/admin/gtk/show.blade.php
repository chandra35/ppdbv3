@extends('adminlte::page')

@section('title', 'Detail GTK - PPDB Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user mr-2"></i>Detail GTK</h1>
        <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; font-size: 40px;">
                        {{ strtoupper(substr($gtk->nama_lengkap, 0, 1)) }}
                    </div>
                </div>

                <h3 class="profile-username text-center">{{ $gtk->nama_lengkap }}</h3>
                <p class="text-muted text-center">
                    {{ $gtk->jabatan ?? 'GTK' }}
                    @if($gtk->kategori_ptk)
                        <br><span class="badge badge-secondary">{{ $gtk->kategori_ptk }}</span>
                    @endif
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>NIP</b> <a class="float-right">{{ $gtk->nip ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>NUPTK</b> <a class="float-right">{{ $gtk->nuptk ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>NIK</b> <a class="float-right">{{ $gtk->nik ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $gtk->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>No. HP</b> <a class="float-right">{{ $gtk->nomor_hp ?? '-' }}</a>
                    </li>
                </ul>

                @if($ppdbUser)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Sudah terdaftar sebagai User PPDB</strong>
                    </div>
                @else
                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#registerModal">
                        <i class="fas fa-user-plus"></i> Daftarkan sebagai User PPDB
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Data Pribadi -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Jenis Kelamin</dt>
                            <dd class="col-sm-7">{{ $gtk->jenis_kelamin_label ?? '-' }}</dd>
                            
                            <dt class="col-sm-5">Tempat Lahir</dt>
                            <dd class="col-sm-7">{{ $gtk->tempat_lahir ?? '-' }}</dd>
                            
                            <dt class="col-sm-5">Tanggal Lahir</dt>
                            <dd class="col-sm-7">
                                {{ $gtk->tanggal_lahir ? \Carbon\Carbon::parse($gtk->tanggal_lahir)->format('d F Y') : '-' }}
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Alamat</dt>
                            <dd class="col-sm-7">{{ $gtk->alamat ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Kepegawaian -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i>Data Kepegawaian</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Kategori PTK</dt>
                            <dd class="col-sm-7">{{ $gtk->kategori_ptk ?? '-' }}</dd>
                            
                            <dt class="col-sm-5">Jenis PTK</dt>
                            <dd class="col-sm-7">{{ $gtk->jenis_ptk ?? '-' }}</dd>
                            
                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">{{ $gtk->status_kepegawaian ?? '-' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Jabatan</dt>
                            <dd class="col-sm-7">{{ $gtk->jabatan ?? '-' }}</dd>
                            
                            <dt class="col-sm-5">TMT Kerja</dt>
                            <dd class="col-sm-7">
                                {{ $gtk->tmt_kerja ? \Carbon\Carbon::parse($gtk->tmt_kerja)->format('d F Y') : '-' }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status PPDB -->
        @if($ppdbUser)
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Status di PPDB</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">User ID</dt>
                            <dd class="col-sm-7"><code>{{ $ppdbUser->id }}</code></dd>
                            
                            <dt class="col-sm-5">Terdaftar</dt>
                            <dd class="col-sm-7">{{ $ppdbUser->created_at->format('d F Y H:i') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Role</dt>
                            <dd class="col-sm-7">
                                @forelse($ppdbUser->roles as $role)
                                    <span class="badge badge-info">{{ $role->display_name }}</span>
                                @empty
                                    <span class="badge badge-secondary">Tidak ada role</span>
                                @endforelse
                            </dd>
                        </dl>
                    </div>
                </div>
                
                <hr>
                
                <form action="{{ route('admin.gtk.update-roles', $gtk->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Ubah Role</label>
                        <select name="roles[]" class="form-control select2" multiple required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                    {{ $ppdbUser->roles->contains('id', $role->id) ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <form action="{{ route('admin.gtk.remove', $gtk->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus user PPDB ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-user-times"></i> Hapus dari PPDB
                            </button>
                        </form>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Register Modal -->
@if(!$ppdbUser)
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.gtk.register', $gtk->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Daftarkan sebagai User PPDB</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>{{ $gtk->nama_lengkap }}</strong><br>
                        Email: {{ $gtk->email }}
                    </div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="roles[]" class="form-control select2" multiple required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih satu atau lebih role</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" placeholder="Default: ppdb123">
                        <small class="text-muted">Kosongkan untuk menggunakan password default</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Daftarkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@stop

@section('css')
<style>
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@stop
