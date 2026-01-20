@extends('adminlte::page')

@section('title', 'Dashboard Admin PPDB')

@section('css')
<style>
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }
    .stat-updated {
        animation: highlight 0.5s ease-out;
    }
    @keyframes highlight {
        0% { background-color: #ffc107; color: #000; }
        100% { background-color: transparent; }
    }
    #btn-refresh-now.loading i {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div class="d-flex align-items-center">
            <div class="live-datetime mr-3 text-right d-none d-md-block">
                <div class="font-weight-bold text-primary" id="live-date" style="font-size: 14px;"></div>
                <div class="text-muted" id="live-time" style="font-size: 20px; font-weight: 600;"></div>
            </div>
            <ol class="breadcrumb m-0 bg-transparent p-0">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
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

    <!-- Auto-refresh indicator -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <span class="badge badge-success" id="live-indicator">
                <i class="fas fa-circle fa-xs mr-1" style="animation: pulse 1s infinite;"></i> LIVE
            </span>
            <small class="text-muted ml-2">Auto-refresh setiap <span id="refresh-interval">10</span> detik</small>
        </div>
        <div>
            <small class="text-muted">Update terakhir: <span id="last-update">{{ now()->format('H:i:s') }}</span></small>
            <button class="btn btn-sm btn-outline-primary ml-2" id="btn-refresh-now" title="Refresh Sekarang">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Main Stats - Small boxes (Big Cards) -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 data-stat="total_pendaftar">{{ number_format($stats['total_pendaftar']) }}</h3>
                    <p>Total Pendaftar</p>
                    <small style="font-size: 12px; opacity: 0.9;">
                        Reguler = <span data-stat="pendaftar_reguler">{{ number_format($stats['pendaftar_reguler']) }}</span> | 
                        Asrama = <span data-stat="pendaftar_asrama">{{ number_format($stats['pendaftar_asrama']) }}</span>
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 data-stat="pendaftar_baru">{{ number_format($stats['pendaftar_baru']) }}</h3>
                    <p>Menunggu Verifikasi</p>
                    <small style="font-size: 12px; opacity: 0.9;">
                        Reguler = <span data-stat="pendaftar_baru_reguler">{{ number_format($stats['pendaftar_baru_reguler']) }}</span> | 
                        Asrama = <span data-stat="pendaftar_baru_asrama">{{ number_format($stats['pendaftar_baru_asrama']) }}</span>
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}?status=pending" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 data-stat="terverifikasi">{{ number_format($stats['terverifikasi']) }}</h3>
                    <p>Terverifikasi</p>
                    <small style="font-size: 12px; opacity: 0.9;">
                        Reguler = <span data-stat="terverifikasi_reguler">{{ number_format($stats['terverifikasi_reguler']) }}</span> | 
                        Asrama = <span data-stat="terverifikasi_asrama">{{ number_format($stats['terverifikasi_asrama']) }}</span>
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}?status=verified" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 data-stat="finalisasi">{{ number_format($stats['finalisasi']) }}</h3>
                    <p>Finalisasi</p>
                    <small style="font-size: 12px; opacity: 0.9;">
                        Reguler = <span data-stat="finalisasi_reguler">{{ number_format($stats['finalisasi_reguler']) }}</span> | 
                        Asrama = <span data-stat="finalisasi_asrama">{{ number_format($stats['finalisasi_asrama']) }}</span>
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <a href="{{ route('admin.pendaftar.index') }}?finalisasi=1" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    @if($isAdmin)
    <!-- Second row - Admin Only Stats (Smaller info boxes) -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-user-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ditolak</span>
                    <span class="info-box-number" data-stat="ditolak">{{ $stats['ditolak'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-newspaper"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Berita</span>
                    <span class="info-box-number" data-stat="total_berita">{{ $stats['total_berita'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-teal">
                <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Verifikator</span>
                    <span class="info-box-number" data-stat="total_verifikator">{{ $stats['total_verifikator'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fas fa-user-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total User</span>
                    <span class="info-box-number" data-stat="total_user">{{ $stats['total_user'] }}</span>
                </div>
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
                                @php
                                    // Prioritas foto: 1. Dokumen foto, 2. Foto manual, 3. UI Avatars
                                    $foto = $pendaftar->dokumen->where('jenis_dokumen', 'foto')->first();
                                    if($foto && $foto->file_path && file_exists(public_path('storage/' . $foto->file_path))) {
                                        $avatarUrl = asset('storage/' . $foto->file_path);
                                    } elseif($pendaftar->foto && file_exists(public_path('storage/' . $pendaftar->foto))) {
                                        $avatarUrl = asset('storage/' . $pendaftar->foto);
                                    } else {
                                        $initials = collect(explode(' ', $pendaftar->nama_lengkap))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->join('');
                                        $bgColor = $pendaftar->jenis_kelamin == 'L' ? '3498db' : ($pendaftar->jenis_kelamin == 'P' ? 'e74c3c' : '95a5a6');
                                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=50&background=' . $bgColor . '&color=ffffff&bold=true';
                                    }
                                @endphp
                                <img src="{{ $avatarUrl }}" alt="User" class="img-size-50 img-circle">
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

        // Live DateTime
        function updateDateTime() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            document.getElementById('live-date').innerHTML = '<i class="far fa-calendar-alt mr-1"></i>' + dayName + ', ' + date + ' ' + month + ' ' + year;
            document.getElementById('live-time').innerHTML = '<i class="far fa-clock mr-1"></i>' + hours + ':' + minutes + ':' + seconds + ' WIB';
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // ========================================
        // AUTO-REFRESH STATS
        // ========================================
        const REFRESH_INTERVAL = 5000; // 5 seconds
        let statsChart = myChart; // Reference to the chart
        
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
        
        function refreshStats() {
            const btn = document.getElementById('btn-refresh-now');
            btn.classList.add('loading');
            
            fetch('{{ route("admin.dashboard.stats") }}')
                .then(response => response.json())
                .then(data => {
                    // Update stats with animation
                    Object.keys(data.stats).forEach(key => {
                        const el = document.querySelector(`[data-stat="${key}"]`);
                        if (el) {
                            const newValue = formatNumber(data.stats[key]);
                            if (el.textContent !== newValue) {
                                el.textContent = newValue;
                                el.classList.add('stat-updated');
                                setTimeout(() => el.classList.remove('stat-updated'), 500);
                            }
                        }
                    });
                    
                    // Update chart data
                    if (data.chartData && statsChart) {
                        statsChart.data.labels = data.chartData.labels;
                        statsChart.data.datasets[0].data = data.chartData.data;
                        statsChart.update('none'); // Update without animation for smoother experience
                    }
                    
                    // Update timestamp
                    document.getElementById('last-update').textContent = data.timestamp;
                })
                .catch(error => {
                    console.error('Failed to refresh stats:', error);
                })
                .finally(() => {
                    btn.classList.remove('loading');
                });
        }
        
        // Auto-refresh every REFRESH_INTERVAL
        setInterval(refreshStats, REFRESH_INTERVAL);
        
        // Manual refresh button
        document.getElementById('btn-refresh-now').addEventListener('click', function() {
            refreshStats();
        });
    </script>
@stop
