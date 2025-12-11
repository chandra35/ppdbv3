@extends('adminlte::page')

@section('title', 'Detail Pendaftar')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user"></i> Detail Pendaftar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.pendaftar.index') }}">Pendaftar</a></li>
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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                             alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{ $pendaftar->nama_lengkap }}</h3>
                    <p class="text-muted text-center">
                        @if($pendaftar->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($pendaftar->status == 'verified')
                            <span class="badge badge-info">Verified</span>
                        @elseif($pendaftar->status == 'approved')
                            <span class="badge badge-success">Diterima</span>
                        @elseif($pendaftar->status == 'rejected')
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>NISN</b> <span class="float-right">{{ $pendaftar->nisn ?? '-' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <span class="float-right">{{ $pendaftar->email ?? '-' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>No HP</b> <span class="float-right">{{ $pendaftar->no_hp ?? '-' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Terdaftar</b> <span class="float-right">{{ $pendaftar->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>

                    <!-- Action Buttons -->
                    @if($pendaftar->status == 'pending')
                        <form action="{{ route('admin.pendaftar.verify', $pendaftar->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-check"></i> Verifikasi
                            </button>
                        </form>
                    @endif

                    @if($pendaftar->status == 'verified')
                        <form action="{{ route('admin.pendaftar.approve', $pendaftar->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check-double"></i> Terima
                            </button>
                        </form>
                    @endif

                    @if(in_array($pendaftar->status, ['pending', 'verified']))
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Data Pribadi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Data Pribadi</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px">Nama Lengkap</th>
                            <td>{{ $pendaftar->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $pendaftar->jenis_kelamin == 'L' ? 'Laki-laki' : ($pendaftar->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                        </tr>
                        <tr>
                            <th>Tempat, Tanggal Lahir</th>
                            <td>{{ $pendaftar->tempat_lahir ?? '-' }}, {{ $pendaftar->tanggal_lahir ? \Carbon\Carbon::parse($pendaftar->tanggal_lahir)->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Agama</th>
                            <td>{{ $pendaftar->agama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $pendaftar->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Asal Sekolah</th>
                            <td>{{ $pendaftar->asal_sekolah ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Data Orang Tua/Wali</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Ayah</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th>Nama</th>
                                    <td>{{ $pendaftar->nama_ayah ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Pekerjaan</th>
                                    <td>{{ $pendaftar->pekerjaan_ayah ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No HP</th>
                                    <td>{{ $pendaftar->no_hp_ayah ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Ibu</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th>Nama</th>
                                    <td>{{ $pendaftar->nama_ibu ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Pekerjaan</th>
                                    <td>{{ $pendaftar->pekerjaan_ibu ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No HP</th>
                                    <td>{{ $pendaftar->no_hp_ibu ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Dokumen</h3>
                </div>
                <div class="card-body">
                    @if($pendaftar->dokumen && $pendaftar->dokumen->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Jenis Dokumen</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendaftar->dokumen as $dokumen)
                                <tr>
                                    <td>{{ $dokumen->jenis_dokumen }}</td>
                                    <td>
                                        @if($dokumen->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($dokumen->status == 'verified')
                                            <span class="badge badge-success">Verified</span>
                                        @elseif($dokumen->status == 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dokumen->file_path)
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-info btn-xs">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">Tidak ada dokumen</p>
                    @endif
                </div>
            </div>

            @if($pendaftar->status == 'rejected' && $pendaftar->rejection_reason)
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-times-circle"></i> Alasan Penolakan</h3>
                </div>
                <div class="card-body">
                    {{ $pendaftar->rejection_reason }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Tolak Pendaftar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.pendaftar.reject', $pendaftar->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="alasan">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="4" required 
                                      placeholder="Masukkan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Pendaftar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
