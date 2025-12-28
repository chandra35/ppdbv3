@extends('adminlte::page')

@section('title', 'Dashboard PPDB')

@section('content_header')
    <h1>Dashboard PPDB</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Selamat Datang!</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Informasi</h5>
                        <p>Selamat datang di Portal PPDB. Anda belum melakukan pendaftaran.</p>
                        <p>Silakan klik tombol di bawah untuk memulai pendaftaran.</p>
                    </div>

                    <a href="{{ route('ppdb.register.step1') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Mulai Pendaftaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($calonSiswa)
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $calonSiswa->nomor_registrasi }}</h3>
                    <p>Nomor Registrasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-id-card"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ ucfirst($calonSiswa->status_verifikasi) }}</h3>
                    <p>Status Verifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $calonSiswa->dokumen->count() }}</h3>
                    <p>Dokumen Terupload</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $calonSiswa->created_at->format('d M Y') }}</h3>
                    <p>Tanggal Daftar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Data Pribadi</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">NISN</th>
                            <td>{{ $calonSiswa->nisn }}</td>
                        </tr>
                        <tr>
                            <th>Nama Lengkap</th>
                            <td>{{ $calonSiswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $calonSiswa->jenis_kelamin }}</td>
                        </tr>
                        <tr>
                            <th>Tempat, Tanggal Lahir</th>
                            <td>{{ $calonSiswa->tempat_lahir }}, {{ $calonSiswa->tanggal_lahir }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Dokumen</h3>
                </div>
                <div class="card-body">
                    <p>Status dokumen Anda:</p>
                    <ul>
                        @forelse($calonSiswa->dokumen as $dok)
                            <li>{{ $dok->jenis_dokumen }}: <span class="badge badge-{{ $dok->status == 'verified' ? 'success' : 'warning' }}">{{ $dok->status }}</span></li>
                        @empty
                            <li class="text-muted">Belum ada dokumen</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="{{ route('ppdb.bukti-registrasi') }}" class="btn btn-success" target="_blank">
                        <i class="fas fa-print"></i> Cetak Bukti Registrasi
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop