@extends('adminlte::page')

@section('title', 'Statistik Pengunjung')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-chart-line mr-2"></i>Statistik Pengunjung</h1>
            <small class="text-muted">Analisis antusiasme calon pendaftar</small>
        </div>
        <div>
            <a href="{{ route('admin.visitor-logs.online') }}" class="btn btn-success btn-sm">
                <i class="fas fa-circle text-white mr-1"></i> Online Sekarang
            </a>
            <a href="{{ route('admin.visitor-logs.list') }}" class="btn btn-info btn-sm">
                <i class="fas fa-list mr-1"></i> Detail Log
            </a>
            <a href="{{ route('admin.visitor-logs.map') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-map-marker-alt mr-1"></i> Peta
            </a>
            <a href="{{ route('admin.visitor-logs.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-download mr-1"></i> Export
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Date Range Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.visitor-logs.index') }}" method="GET" class="row align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-4 text-right">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Menampilkan data {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </small>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_visits']) }}</h3>
                    <p>Total Kunjungan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
                <span class="small-box-footer">
                    Periode yang dipilih
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3>{{ number_format($stats['unique_visitors']) }}</h3>
                    <p>Pengunjung Unik</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="small-box-footer">
                    Berdasarkan IP Address
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ number_format($stats['unique_converted']) }}</h3>
                    <p>Melakukan Pendaftaran</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="small-box-footer">
                    Pengunjung yang mendaftar
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3>{{ $stats['conversion_rate'] }}%</h3>
                    <p>Conversion Rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <span class="small-box-footer">
                    Rasio pengunjung → pendaftar
                </span>
            </div>
        </div>
    </div>
    
    {{-- Online Now & Today Stats --}}
    <div class="row">
        <div class="col-lg-4">
            <a href="{{ route('admin.visitor-logs.online') }}" class="text-decoration-none">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-circle pulse-animation"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Online Sekarang</span>
                        <span class="info-box-number">{{ $stats['online_now'] ?? 0 }}</span>
                        <small>Pengunjung aktif dalam 5 menit terakhir</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <div class="info-box bg-gradient-light">
                <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Kunjungan Hari Ini</span>
                    <span class="info-box-number">{{ number_format($stats['today_visits']) }}</span>
                    <small>{{ $stats['today_unique'] }} pengunjung unik</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="info-box bg-gradient-light">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Rata-rata Kunjungan/Hari</span>
                    @php
                        $daysDiff = max(1, \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1);
                        $avgVisits = round($stats['total_visits'] / $daysDiff, 1);
                    @endphp
                    <span class="info-box-number">{{ $avgVisits }}</span>
                    <small>Dalam {{ $daysDiff }} hari</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        {{-- Visits Chart --}}
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Grafik Kunjungan</h3>
                </div>
                <div class="card-body">
                    <canvas id="visitsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Device Distribution --}}
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-mobile-alt mr-1"></i> Perangkat</h3>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" height="200"></canvas>
                    <div class="mt-3">
                        @foreach($deviceStats as $device)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>
                                    @if($device->device_type == 'mobile')
                                        <i class="fas fa-mobile-alt text-success"></i>
                                    @elseif($device->device_type == 'tablet')
                                        <i class="fas fa-tablet-alt text-info"></i>
                                    @else
                                        <i class="fas fa-desktop text-primary"></i>
                                    @endif
                                    {{ ucfirst($device->device_type) }}
                                </span>
                                <span class="badge badge-secondary">{{ number_format($device->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row --}}
    <div class="row">
        {{-- Hourly Distribution --}}
        <div class="col-lg-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Distribusi Per Jam</h3>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" height="150"></canvas>
                </div>
            </div>
        </div>

        {{-- Browser & Platform --}}
        <div class="col-lg-3">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-globe mr-1"></i> Browser</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($browserStats as $browser)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span>
                                    @php
                                        $browserLower = strtolower($browser->browser);
                                        $icon = match(true) {
                                            str_contains($browserLower, 'chrome') => 'fab fa-chrome',
                                            str_contains($browserLower, 'firefox') => 'fab fa-firefox',
                                            str_contains($browserLower, 'safari') => 'fab fa-safari',
                                            str_contains($browserLower, 'edge') => 'fab fa-edge',
                                            default => 'fas fa-globe'
                                        };
                                    @endphp
                                    <i class="{{ $icon }} mr-1"></i>
                                    {{ $browser->browser }}
                                </span>
                                <span class="badge badge-info">{{ number_format($browser->count) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">Tidak ada data</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-laptop mr-1"></i> Platform</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($platformStats as $platform)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span>
                                    @php
                                        $platformLower = strtolower($platform->platform);
                                        $icon = match(true) {
                                            str_contains($platformLower, 'windows') => 'fab fa-windows',
                                            str_contains($platformLower, 'android') => 'fab fa-android',
                                            str_contains($platformLower, 'ios') || str_contains($platformLower, 'mac') => 'fab fa-apple',
                                            str_contains($platformLower, 'linux') => 'fab fa-linux',
                                            default => 'fas fa-laptop'
                                        };
                                    @endphp
                                    <i class="{{ $icon }} mr-1"></i>
                                    {{ $platform->platform }}
                                </span>
                                <span class="badge badge-secondary">{{ number_format($platform->count) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">Tidak ada data</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Third Row --}}
    <div class="row">
        {{-- Top Locations --}}
        <div class="col-lg-6">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Lokasi Pengunjung</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Kota</th>
                                <th>Negara</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locationStats as $location)
                                <tr>
                                    <td><i class="fas fa-city mr-1 text-muted"></i>{{ $location->city }}</td>
                                    <td>{{ $location->country }}</td>
                                    <td class="text-right"><span class="badge badge-danger">{{ number_format($location->count) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada data lokasi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Popular Pages --}}
        <div class="col-lg-6">
            <div class="card card-outline card-purple">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Halaman Populer</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Halaman</th>
                                <th class="text-right">Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pageStats as $page)
                                <tr>
                                    <td>
                                        <i class="fas fa-link mr-1 text-muted"></i>
                                        <span title="{{ $page->page_url }}">{{ Str::limit($page->page_title, 40) }}</span>
                                    </td>
                                    <td class="text-right"><span class="badge badge-purple">{{ number_format($page->count) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .badge-purple { background-color: #6f42c1; color: white; }
    .card-outline.card-purple { border-top: 3px solid #6f42c1; }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Visits Chart
    const visitsCtx = document.getElementById('visitsChart').getContext('2d');
    new Chart(visitsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($visitsPerDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
            datasets: [
                {
                    label: 'Total Kunjungan',
                    data: {!! json_encode($visitsPerDay->pluck('total')) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Pengunjung Unik',
                    data: {!! json_encode($visitsPerDay->pluck('unique_visitors')) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Device Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($deviceStats->pluck('device_type')->map(fn($d) => ucfirst($d))) !!},
            datasets: [{
                data: {!! json_encode($deviceStats->pluck('count')) !!},
                backgroundColor: ['#28a745', '#17a2b8', '#007bff'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Hourly Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hourlyData->pluck('hour')->map(fn($h) => sprintf('%02d:00', $h))) !!},
            datasets: [{
                label: 'Kunjungan',
                data: {!! json_encode($hourlyData->pluck('count')) !!},
                backgroundColor: 'rgba(23, 162, 184, 0.7)',
                borderColor: '#17a2b8',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@stop
