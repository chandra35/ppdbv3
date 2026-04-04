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
        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $dir, 'page' => 1, 'tab' => 'sudah']);
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

    // Sort helpers for belum buka tab
    $sortUrlBelum = function($column) {
        $dir = (request('sort_belum') === $column && request('dir_belum', 'asc') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort_belum' => $column, 'dir_belum' => $dir, 'page_belum' => 1, 'tab' => 'belum']);
    };

    $sortIconBelum = function($column) {
        if (request('sort_belum') !== $column && !($column === 'nama_lengkap' && !request('sort_belum'))) {
            return '<i class="fas fa-sort text-muted ml-1"></i>';
        }
        $dir = request('dir_belum', 'asc');
        return $dir === 'asc'
            ? '<i class="fas fa-sort-up text-primary ml-1"></i>'
            : '<i class="fas fa-sort-down text-primary ml-1"></i>';
    };
@endphp

@section('content')
<div class="container-fluid">
    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
        <div>
            Log amplop sedang memakai konteks tahun:
            <strong>{{ $tahunAktif->nama ?? '-' }}</strong>
        </div>
        <form method="GET" class="form-inline">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <label class="mr-2 mb-0">Tahun</label>
            <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($tahunPelajaranList as $tp)
                <option value="{{ $tp->id }}" {{ (string) $selectedTahunIdInput === (string) $tp->id ? 'selected' : '' }}>
                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

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
            <a href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'sudah']) }}" class="small-box-link-wrapper">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalOpened }}</h3>
                        <p>Sudah Buka Amplop</p>
                    </div>
                    <div class="icon"><i class="fas fa-envelope-open"></i></div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-6">
            <a href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'belum']) }}" class="small-box-link-wrapper">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalBelumBuka }}</h3>
                        <p>Belum Buka Amplop</p>
                    </div>
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" id="envelopeTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'sudah' ? 'active' : '' }}"
               href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'sudah']) }}">
                <i class="fas fa-envelope-open text-success mr-1"></i>
                Sudah Buka Amplop
                <span class="badge badge-success ml-1">{{ $totalOpened }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'belum' ? 'active' : '' }}"
               href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'belum']) }}">
                <i class="fas fa-envelope text-warning mr-1"></i>
                Belum Buka Amplop
                <span class="badge badge-warning ml-1">{{ $totalBelumBuka }}</span>
            </a>
        </li>
    </ul>

    {{-- Tab: Sudah Buka Amplop --}}
    @if($activeTab === 'sudah')
    <div class="card card-outline card-success" style="border-top-left-radius: 0;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Riwayat Buka Amplop — {{ $tahunAktif->nama ?? '' }}</h3>
            <div class="card-tools">
                <form method="GET" class="input-group input-group-sm" style="width: 250px;">
                    <input type="hidden" name="tab" value="sudah">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/NISN/no.tes..."
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if(request('search'))
                        <a href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'sudah']) }}" class="btn btn-default" title="Reset">
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
                                <a href="{{ $sortUrl('nomor_tes') }}" class="text-dark text-decoration-none">
                                    No. Tes {!! $sortIcon('nomor_tes') !!}
                                </a>
                            </th>
                            <th>Jalur</th>
                            <th>
                                <a href="{{ $sortUrl('pilihan_program') }}" class="text-dark text-decoration-none">
                                    Minat {!! $sortIcon('pilihan_program') !!}
                                </a>
                            </th>
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
                                @if($log->calonSiswa)
                                <a href="{{ route('admin.pendaftar.show', $log->calonSiswa->id) }}" class="text-primary font-weight-bold" title="Lihat detail pendaftar">
                                    {{ $log->calonSiswa->nama_lengkap }}
                                </a>
                                @else
                                    <strong>-</strong>
                                @endif
                            </td>
                            <td>{{ $log->calonSiswa->nisn ?? '-' }}</td>
                            <td><code>{{ $log->calonSiswa->nomor_tes ?? '-' }}</code></td>
                            <td>
                                @if($log->calonSiswa && $log->calonSiswa->jalurPendaftaran)
                                    <span class="badge badge-info">{{ $log->calonSiswa->jalurPendaftaran->nama }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($log->calonSiswa && $log->calonSiswa->pilihan_program)
                                    <span class="badge badge-{{ $log->calonSiswa->pilihan_program === 'asrama' ? 'dark' : 'light border' }}">
                                        {{ ucfirst($log->calonSiswa->pilihan_program) }}
                                    </span>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                @if($log->calonSiswa && $log->calonSiswa->kelulusan)
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
                            <td colspan="10" class="text-center py-4 text-muted">
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
    @endif

    {{-- Tab: Belum Buka Amplop --}}
    @if($activeTab === 'belum')
    <div class="card card-outline card-warning" style="border-top-left-radius: 0;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Belum Buka Amplop — {{ $tahunAktif->nama ?? '' }}</h3>
            <div class="card-tools">
                <form method="GET" class="input-group input-group-sm" style="width: 250px;">
                    <input type="hidden" name="tab" value="belum">
                    <input type="text" name="search_belum" class="form-control" placeholder="Cari nama/NISN/no.tes..."
                           value="{{ request('search_belum') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if(request('search_belum'))
                        <a href="{{ route('admin.kelulusan.envelope-logs', ['tab' => 'belum']) }}" class="btn btn-default" title="Reset">
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
                                <a href="{{ $sortUrlBelum('nama_lengkap') }}" class="text-dark text-decoration-none">
                                    Nama Pendaftar {!! $sortIconBelum('nama_lengkap') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrlBelum('nisn') }}" class="text-dark text-decoration-none">
                                    NISN {!! $sortIconBelum('nisn') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrlBelum('nomor_tes') }}" class="text-dark text-decoration-none">
                                    No. Tes {!! $sortIconBelum('nomor_tes') !!}
                                </a>
                            </th>
                            <th>Jalur</th>
                            <th>Gelombang</th>
                            <th>
                                <a href="{{ $sortUrlBelum('pilihan_program') }}" class="text-dark text-decoration-none">
                                    Minat {!! $sortIconBelum('pilihan_program') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrlBelum('status') }}" class="text-dark text-decoration-none">
                                    Status Kelulusan {!! $sortIconBelum('status') !!}
                                </a>
                            </th>
                            <th>No. HP / Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($belumBuka as $i => $kelulusan)
                        <tr>
                            <td>{{ $belumBuka->firstItem() + $i }}</td>
                            <td>
                                @if($kelulusan->calonSiswa)
                                <a href="{{ route('admin.pendaftar.show', $kelulusan->calonSiswa->id) }}" class="text-primary font-weight-bold" title="Lihat detail pendaftar">
                                    {{ $kelulusan->calonSiswa->nama_lengkap }}
                                </a>
                                @else
                                    <strong>-</strong>
                                @endif
                            </td>
                            <td>{{ $kelulusan->calonSiswa->nisn ?? '-' }}</td>
                            <td><code>{{ $kelulusan->calonSiswa->nomor_tes ?? '-' }}</code></td>
                            <td>
                                @if($kelulusan->calonSiswa && $kelulusan->calonSiswa->jalurPendaftaran)
                                    <span class="badge badge-info">{{ $kelulusan->calonSiswa->jalurPendaftaran->nama }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($kelulusan->calonSiswa && $kelulusan->calonSiswa->gelombangPendaftaran)
                                    <span class="badge badge-secondary">{{ $kelulusan->calonSiswa->gelombangPendaftaran->nama }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($kelulusan->calonSiswa && $kelulusan->calonSiswa->pilihan_program)
                                    <span class="badge badge-{{ $kelulusan->calonSiswa->pilihan_program === 'asrama' ? 'dark' : 'light border' }}">
                                        {{ ucfirst($kelulusan->calonSiswa->pilihan_program) }}
                                    </span>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                @php $st = $kelulusan->status; @endphp
                                <span class="badge badge-{{ $st === 'lulus' ? 'success' : ($st === 'cadangan' ? 'warning' : 'danger') }}">
                                    {{ strtoupper(str_replace('_', ' ', $st)) }}
                                </span>
                            </td>
                            <td>
                                @if($kelulusan->calonSiswa)
                                    @if($kelulusan->calonSiswa->nomor_hp)
                                        <small><i class="fas fa-phone mr-1"></i>{{ $kelulusan->calonSiswa->nomor_hp }}</small><br>
                                    @endif
                                    @if($kelulusan->calonSiswa->email)
                                        <small><i class="fas fa-envelope mr-1"></i>{{ $kelulusan->calonSiswa->email }}</small>
                                    @elseif($kelulusan->calonSiswa->user && $kelulusan->calonSiswa->user->email)
                                        <small><i class="fas fa-envelope mr-1"></i>{{ $kelulusan->calonSiswa->user->email }}</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                Semua peserta sudah membuka amplop!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($belumBuka->hasPages())
        <div class="card-footer clearfix">
            {{ $belumBuka->links() }}
        </div>
        @endif
    </div>
    @endif

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
    .nav-tabs .nav-link {
        color: #6c757d;
    }
    .nav-tabs .nav-link.active {
        font-weight: 600;
    }
    .small-box-link-wrapper {
        text-decoration: none !important;
        color: inherit !important;
    }
    .small-box-link-wrapper:hover .small-box {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
    }
    .small-box {
        transition: all 0.2s ease;
    }
</style>
@endpush
