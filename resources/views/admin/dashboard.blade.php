@extends('adminlte::page')

@section('title', 'Dashboard Admin PPDB')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
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
                            <span class="badge badge-primary">{{ $role->display_name }}</span>
                        @endforeach
                    @else
                        <span class="badge badge-info">Administrator</span>
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
                    <span class="info-box-text">Terverifikasi</span>
                    <span class="info-box-number">{{ number_format($stats['terverifikasi']) }}</span>
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
    </div>

    @if($isAdmin)
    <!-- Second row of info boxes - ADMIN ONLY -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['ditolak'] }}</h3>
                    <p>Ditolak</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['total_berita'] }}</h3>
                    <p>Total Berita</p>
                </div>
                <div class="icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <a href="{{ route('admin.settings.berita.index') }}" class="small-box-footer">
                    Kelola Berita <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $stats['total_verifikator'] }}</h3>
                    <p>Total Verifikator</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="{{ route('admin.verifikator.index') }}" class="small-box-footer">
                    Kelola Verifikator <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $stats['total_user'] }}</h3>
                    <p>Total User</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    Kelola User <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

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
                    <canvas id="registrationChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Pendaftar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-1"></i>
                        Pendaftar Terbaru
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @forelse($recentPendaftar as $pendaftar)
                        <li class="item">
                            <div class="product-img">
                                <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="User" class="img-size-50 img-circle">
                            </div>
                            <div class="product-info">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="product-title">
                                    {{ $pendaftar->nama_lengkap ?? 'N/A' }}
                                    @if($pendaftar->status == 'pending')
                                        <span class="badge badge-warning float-right">Pending</span>
                                    @elseif($pendaftar->status == 'verified')
                                        <span class="badge badge-info float-right">Verified</span>
                                    @elseif($pendaftar->status == 'approved')
                                        <span class="badge badge-success float-right">Diterima</span>
                                    @elseif($pendaftar->status == 'rejected')
                                        <span class="badge badge-danger float-right">Ditolak</span>
                                    @endif
                                </a>
                                <span class="product-description">
                                    {{ $pendaftar->nisn ?? '-' }} - {{ $pendaftar->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                        @empty
                        <li class="item">
                            <div class="product-info text-center text-muted py-3">
                                Belum ada pendaftar
                            </div>
                        </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.pendaftar.index') }}" class="uppercase">Lihat Semua Pendaftar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-users"></i><br>
                                <small>Lihat Pendaftar</small>
                            </a>
                        </div>
                        @if($isAdmin)
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.settings.berita.create') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-plus-circle"></i><br>
                                <small>Tambah Berita</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-images"></i><br>
                                <small>Kelola Slider</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-user-cog"></i><br>
                                <small>Kelola User</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-cog"></i><br>
                                <small>Pengaturan</small>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-dark btn-block">
                                <i class="fas fa-history"></i><br>
                                <small>Activity Log</small>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity - ADMIN ONLY -->
    @if($isAdmin && count($recentLogs) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Aktivitas</th>
                                <th>Deskripsi</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td><span class="badge badge-primary">{{ $log->action }}</span></td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.logs.index') }}" class="uppercase">Lihat Semua Log</a>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-compact.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('registrationChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Pendaftar',
                    data: {!! json_encode($chartData['data'] ?? []) !!},
                    backgroundColor: 'rgba(60, 141, 188, 0.2)',
                    borderColor: 'rgba(60, 141, 188, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@stop
