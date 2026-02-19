@extends('adminlte::page')

@section('title', 'Log Buka Amplop')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-envelope-open-text mr-2"></i>Log Buka Amplop Kelulusan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.kelulusan.index') }}">Kelulusan</a></li>
            <li class="breadcrumb-item active">Log Buka Amplop</li>
        </ol>
    </div>
</div>
@endsection

@php
    $currentSort = request('sort', 'opened_at');
    $currentDir  = request('dir', 'desc');

    $sortUrl = function($column) {
        $dir = (request('sort') === $column && request('dir', 'desc') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $dir, 'page' => 1]);
    };

    $sortIcon = function($column) {
        if (request('sort') !== $column && !($column === 'opened_at' && !request('sort'))) {
            return '<i class="fas fa-sort text-muted ml-1"></i>';
        }
        $dir = request('dir', 'desc');
        return $dir === 'asc'
            ? '<i class="fas fa-sort-up text-primary ml-1"></i>'
            : '<i class="fas fa-sort-down text-primary ml-1"></i>';
    };
@endphp

@section('content')
<div class="container-fluid">

    {{-- Stats --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalKelulusan }}</h3>
                    <p>Total Peserta Kelulusan</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalOpened }}</h3>
                    <p>Sudah Buka Amplop</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalBelumBuka < 0 ? 0 : $totalBelumBuka }}</h3>
                    <p>Belum Buka Amplop</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
    </div>

    {{-- Card --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Riwayat Buka Amplop — {{ $tahunAktif->nama ?? '' }}</h3>
            <div class="card-tools">
                <form method="GET" class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/NISN/no.reg..."
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if(request('search'))
                        <a href="{{ route('admin.kelulusan.envelope-logs') }}" class="btn btn-default" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th>
                                <a href="{{ $sortUrl('nama_lengkap') }}" class="text-dark text-decoration-none">
                                    Nama Pendaftar {!! $sortIcon('nama_lengkap') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('nisn') }}" class="text-dark text-decoration-none">
                                    NISN {!! $sortIcon('nisn') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('nomor_registrasi') }}" class="text-dark text-decoration-none">
                                    No. Registrasi {!! $sortIcon('nomor_registrasi') !!}
                                </a>
                            </th>
                            <th>Jalur</th>
                            <th>Status Kelulusan</th>
                            <th>
                                <a href="{{ $sortUrl('opened_at') }}" class="text-dark text-decoration-none">
                                    Waktu Buka {!! $sortIcon('opened_at') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('ip_address') }}" class="text-dark text-decoration-none">
                                    IP Address {!! $sortIcon('ip_address') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('location_name') }}" class="text-dark text-decoration-none">
                                    Lokasi {!! $sortIcon('location_name') !!}
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $log->calonSiswa->nama_lengkap ?? '-' }}</strong>
                            </td>
                            <td>{{ $log->calonSiswa->nisn ?? '-' }}</td>
                            <td><code>{{ $log->calonSiswa->nomor_registrasi ?? '-' }}</code></td>
                            <td>
                                @if($log->calonSiswa->jalurPendaftaran)
                                    <span class="badge badge-info">{{ $log->calonSiswa->jalurPendaftaran->nama }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($log->calonSiswa->kelulusan)
                                    @php $st = $log->calonSiswa->kelulusan->status; @endphp
                                    <span class="badge badge-{{ $st === 'lulus' ? 'success' : ($st === 'cadangan' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($st) }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $log->opened_at->format('d/m/Y H:i:s') }}">
                                    {{ $log->opened_at->locale('id')->diffForHumans() }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $log->opened_at->format('d M Y, H:i') }}</small>
                            </td>
                            <td>
                                <small class="text-muted" title="{{ $log->user_agent }}">{{ $log->ip_address }}</small>
                            </td>
                            <td>
                                @if($log->latitude && $log->longitude)
                                    <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" title="Buka di Google Maps" class="text-primary">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        @if($log->location_name)
                                            {{ $log->location_name }}
                                        @else
                                            {{ number_format($log->latitude, 4) }}, {{ number_format($log->longitude, 4) }}
                                        @endif
                                    </a>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada pendaftar yang membuka amplop
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
        <div class="card-footer clearfix">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.kelulusan.setting') }}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pengaturan
        </a>
    </div>
</div>
@endsection

@push('css')
<style>
    .table thead th a {
        white-space: nowrap;
    }
    .table thead th a:hover {
        text-decoration: none !important;
        color: #007bff !important;
    }
</style>
@endpush
