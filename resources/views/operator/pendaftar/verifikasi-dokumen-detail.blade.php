@extends('adminlte::page')

@section('title', 'Detail Verifikasi Dokumen')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-alt"></i> Verifikasi Dokumen - {{ $pendaftar->nama_lengkap }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('operator.verifikasi-dokumen.index') }}">Verifikasi Dokumen</a></li>
                <li class="breadcrumb-item active">Detail</li>
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

    <div class="row">
        <div class="col-md-4">
            <!-- Info Pendaftar -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Info Pendaftar</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Nama</th>
                            <td>{{ $pendaftar->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td>{{ $pendaftar->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $pendaftar->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td>{{ $pendaftar->no_hp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Daftar Dokumen -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Dokumen</h3>
                </div>
                <div class="card-body">
                    @if($pendaftar->dokumen && $pendaftar->dokumen->count() > 0)
                        @foreach($pendaftar->dokumen as $dokumen)
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>{{ $dokumen->jenis_dokumen }}</strong>
                                @if($dokumen->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($dokumen->status == 'verified')
                                    <span class="badge badge-success">Verified</span>
                                @elseif($dokumen->status == 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        @if($dokumen->file_path)
                                            @php
                                                $extension = pathinfo($dokumen->file_path, PATHINFO_EXTENSION);
                                            @endphp
                                            @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                <img src="{{ asset('storage/' . $dokumen->file_path) }}" class="img-fluid mb-2" alt="{{ $dokumen->jenis_dokumen }}">
                                            @endif
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-external-link-alt"></i> Buka File
                                            </a>
                                        @else
                                            <p class="text-muted">File tidak tersedia</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <form action="{{ route('operator.verifikasi-dokumen.update', $dokumen->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="pending" {{ $dokumen->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="verified" {{ $dokumen->status == 'verified' ? 'selected' : '' }}>Verified</option>
                                                    <option value="rejected" {{ $dokumen->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Catatan</label>
                                                <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan verifikasi...">{{ $dokumen->catatan_verifikasi }}</textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i> Simpan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">Tidak ada dokumen</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('operator.verifikasi-dokumen.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop
