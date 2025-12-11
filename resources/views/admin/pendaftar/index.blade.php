@extends('adminlte::page')

@section('title', 'Daftar Pendaftar')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-users"></i> Daftar Pendaftar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pendaftar</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pendaftar.index') }}" method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Cari</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama, NISN, Email, No Registrasi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="">Semua Jalur</option>
                            @foreach($jalurList as $jalur)
                            <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                {{ $jalur->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Gelombang</label>
                        <select name="gelombang_id" class="form-control">
                            <option value="">Semua Gelombang</option>
                            @foreach($gelombangList as $gelombang)
                            <option value="{{ $gelombang->id }}" {{ request('gelombang_id') == $gelombang->id ? 'selected' : '' }}>
                                {{ $gelombang->jalur->nama ?? '' }} - {{ $gelombang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pendaftar</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Registrasi</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>Jalur / Gelombang</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftars as $key => $pendaftar)
                    <tr>
                        <td>{{ $pendaftars->firstItem() + $key }}</td>
                        <td><code>{{ $pendaftar->nomor_registrasi ?? '-' }}</code></td>
                        <td>
                            <strong>{{ $pendaftar->nama_lengkap }}</strong>
                            @if($pendaftar->jenis_kelamin)
                                <br><small class="text-muted">{{ $pendaftar->jenis_kelamin == 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</small>
                            @endif
                        </td>
                        <td>{{ $pendaftar->nisn ?? '-' }}</td>
                        <td>
                            @if($pendaftar->jalurPendaftaran)
                                <span class="badge" style="background: {{ $pendaftar->jalurPendaftaran->warna ?? '#007bff' }}; color: white;">
                                    {{ $pendaftar->jalurPendaftaran->nama }}
                                </span>
                                @if($pendaftar->gelombangPendaftaran)
                                    <br><small class="text-muted">{{ $pendaftar->gelombangPendaftaran->nama }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pendaftar->status_verifikasi == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($pendaftar->status_verifikasi == 'verified')
                                <span class="badge badge-info">Verified</span>
                            @elseif($pendaftar->status_verifikasi == 'approved')
                                <span class="badge badge-success">Diterima</span>
                            @elseif($pendaftar->status_verifikasi == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-secondary">{{ $pendaftar->status_verifikasi }}</span>
                            @endif
                        </td>
                        <td>{{ $pendaftar->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-action-view" data-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> <span class="btn-text">Detail</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Tidak ada pendaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $pendaftars->appends(request()->query())->links() }}
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
