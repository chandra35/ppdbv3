@extends('adminlte::page')

@section('title', 'Dashboard Operator')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard Operator</h1>
        <ol class="breadcrumb m-0 bg-transparent p-0">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    {{-- Welcome Card --}}
    <div class="card card-primary card-outline mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center">
                <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" 
                     alt="Logo" class="mr-3" style="width: 45px;">
                <div class="flex-grow-1">
                    <h5 class="mb-0">Selamat Datang, {{ auth()->user()->name }}!</h5>
                    <small class="text-muted">
                        <i class="fas fa-envelope"></i> {{ auth()->user()->email }} |
                        <i class="fas fa-clock"></i> {{ now()->format('d M Y, H:i') }}
                    </small>
                </div>
                <div>
                    @if(auth()->user()->roles->count() > 0)
                        @foreach(auth()->user()->roles as $role)
                            <span class="badge badge-success">{{ $role->display_name }}</span>
                        @endforeach
                    @else
                        <span class="badge badge-info">Operator</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Pendaftar</span>
                    <span class="info-box-number">{{ number_format($stats['total_pendaftar']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Menunggu Verifikasi</span>
                    <span class="info-box-number">{{ number_format($stats['pendaftar_baru']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Diterima</span>
                    <span class="info-box-number">{{ number_format($stats['diterima']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ditolak</span>
                    <span class="info-box-number">{{ number_format($stats['ditolak']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Statistik Pendaftaran (7 Hari Terakhir)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="pendaftarChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('operator.pendaftar.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users text-primary mr-2"></i> Daftar Pendaftar
                        </a>
                        <a href="{{ route('operator.verifikasi-dokumen.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt text-warning mr-2"></i> Verifikasi Dokumen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Pendaftar -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-1"></i>
                        Pendaftar Terbaru
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPendaftar as $pendaftar)
                            <tr>
                                <td>
                                    <a href="{{ route('operator.pendaftar.show', $pendaftar->id) }}">
                                        {{ Str::limit($pendaftar->nama_lengkap, 20) }}
                                    </a>
                                </td>
                                <td>{{ $pendaftar->nisn }}</td>
                                <td>
                                    @if($pendaftar->status_verifikasi == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($pendaftar->status_verifikasi == 'verified')
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $pendaftar->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada pendaftar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('operator.pendaftar.index') }}" class="btn btn-sm btn-primary">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Aktivitas</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>{{ Str::limit($log->description, 30) }}</td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada aktivitas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('pendaftarChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Pendaftar',
                data: @json($chartData['data']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@stop
