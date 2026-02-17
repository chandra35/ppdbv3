@extends('adminlte::page')

@section('title', 'Nilai Seleksi - ' . $sesiUjian->nama)

@section('css')
<style>
    .nilai-badge {
        font-size: 1.1rem;
        font-weight: bold;
    }
    .table-nilai th {
        background: #f4f6f9;
    }
    .nilai-cell {
        font-weight: bold;
        font-size: 1rem;
    }
    .btn-verify {
        padding: 0.25rem 0.5rem;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-chart-bar mr-2"></i>Nilai Seleksi
        </h1>
        <small class="text-muted">{{ $sesiUjian->nama }}</small>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai Seleksi</a></li>
            <li class="breadcrumb-item active">{{ $sesiUjian->nama }}</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Sesi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Jalur:</strong> {{ $sesiUjian->jalur->nama ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Gelombang:</strong> {{ $sesiUjian->gelombang->nama ?? 'Semua' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Tanggal:</strong> {{ $sesiUjian->tanggal?->format('d F Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Verify -->
    @if($nilaiList->where('status', 'submitted')->count() > 0)
    <div class="card bg-warning">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Nilai Menunggu Verifikasi</h5>
                    <p class="mb-0">Ada <strong>{{ $nilaiList->where('status', 'submitted')->count() }}</strong> nilai yang menunggu verifikasi</p>
                </div>
                <div class="col-md-4 text-right">
                    <form action="{{ route('admin.nilai-seleksi.bulk-verify', $sesiUjian->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Verifikasi semua nilai yang menunggu?')">
                            <i class="fas fa-check-double mr-1"></i> Verifikasi Semua
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter by Ruangan -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ !request('ruang') ? 'active' : '' }}" href="{{ route('admin.nilai-seleksi.show', $sesiUjian->id) }}">
                        Semua Ruangan
                    </a>
                </li>
                @foreach($sesiUjian->ruangan as $ruang)
                    <li class="nav-item">
                        <a class="nav-link {{ request('ruang') == $ruang->id ? 'active' : '' }}" 
                           href="{{ route('admin.nilai-seleksi.show', [$sesiUjian->id, 'ruang' => $ruang->id]) }}">
                            {{ $ruang->nama_ruang }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-nilai">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>Peserta</th>
                            <th>Ruangan</th>
                            <th class="text-center">Wawancara</th>
                            <th class="text-center">Baca Qur'an</th>
                            <th class="text-center">Tulis Qur'an</th>
                            <th class="text-center">Hafalan</th>
                            <th class="text-center">Juz</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($nilaiList as $nilai)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    <strong>{{ $nilai->calonSiswa->nama_lengkap ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $nilai->calonSiswa->nomor_tes ?? '-' }}</small>
                                </td>
                                <td>{{ $nilai->ruangUjian->nama_ruang ?? '-' }}</td>
                                <td class="text-center nilai-cell">{{ $nilai->nilai_wawancara ?? '-' }}</td>
                                <td class="text-center nilai-cell">{{ $nilai->nilai_baca_quran ?? '-' }}</td>
                                <td class="text-center nilai-cell">{{ $nilai->nilai_tulis_quran ?? '-' }}</td>
                                <td class="text-center nilai-cell">{{ $nilai->nilai_hafalan ?? '-' }}</td>
                                <td class="text-center">{{ $nilai->jumlah_juz_hafalan ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary nilai-badge">{{ number_format($nilai->total_nilai, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($nilai->status == 'draft')
                                        <span class="badge badge-secondary">Draft</span>
                                    @elseif($nilai->status == 'submitted')
                                        <span class="badge badge-warning">Menunggu</span>
                                    @else
                                        <span class="badge badge-success">Verified</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($nilai->status == 'submitted')
                                        <form action="{{ route('admin.nilai-seleksi.verify', [$sesiUjian->id, $nilai->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success btn-verify" title="Verifikasi">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @elseif($nilai->status == 'verified')
                                        <i class="fas fa-check-double text-success"></i>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data nilai</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bobot Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-balance-scale mr-2"></i>Bobot Penilaian</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($bobotList as $bobot)
                    <div class="col-md-3">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-percent"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $bobot->nama_komponen }}</span>
                                <span class="info-box-number">{{ $bobot->bobot }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.nilai-seleksi.bobot') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-cog mr-1"></i>Ubah Bobot
            </a>
        </div>
    </div>
</div>
@stop
