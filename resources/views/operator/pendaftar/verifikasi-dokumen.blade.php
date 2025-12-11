@extends('adminlte::page')

@section('title', 'Verifikasi Dokumen')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-alt"></i> Verifikasi Dokumen</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Verifikasi Dokumen</li>
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
            <form action="{{ route('operator.verifikasi-dokumen.index') }}" method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status Dokumen</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('operator.verifikasi-dokumen.index') }}" class="btn btn-secondary">
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
            <h3 class="card-title">Daftar Pendaftar dengan Dokumen</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>Jumlah Dokumen</th>
                        <th>Status Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftars as $key => $pendaftar)
                    <tr>
                        <td>{{ $pendaftars->firstItem() + $key }}</td>
                        <td><strong>{{ $pendaftar->nama_lengkap }}</strong></td>
                        <td>{{ $pendaftar->nisn ?? '-' }}</td>
                        <td>{{ $pendaftar->dokumen->count() }} dokumen</td>
                        <td>
                            @php
                                $pending = $pendaftar->dokumen->where('status', 'pending')->count();
                                $verified = $pendaftar->dokumen->where('status', 'verified')->count();
                                $rejected = $pendaftar->dokumen->where('status', 'rejected')->count();
                            @endphp
                            @if($pending > 0)
                                <span class="badge badge-warning">{{ $pending }} Pending</span>
                            @endif
                            @if($verified > 0)
                                <span class="badge badge-success">{{ $verified }} Verified</span>
                            @endif
                            @if($rejected > 0)
                                <span class="badge badge-danger">{{ $rejected }} Rejected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('operator.verifikasi-dokumen.show', $pendaftar->id) }}" class="btn btn-info btn-xs">
                                <i class="fas fa-eye"></i> Verifikasi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada pendaftar dengan dokumen</td>
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
