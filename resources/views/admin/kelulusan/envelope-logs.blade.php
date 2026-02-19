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
                            <th>Nama Pendaftar</th>
                            <th>NISN</th>
                            <th>No. Registrasi</th>
                            <th>Jalur</th>
                            <th>Status Kelulusan</th>
                            <th>Waktu Buka</th>
                            <th>IP Address</th>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
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
