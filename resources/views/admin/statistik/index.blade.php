@extends('adminlte::page')

@section('title', 'Statistik Pendaftar')

@section('css')
<style>
    .info-box {
        min-height: 80px;
    }
    .info-box-icon {
        height: 80px;
        line-height: 80px;
        width: 70px;
    }
    .info-box-content {
        padding: 8px 10px;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 5px;
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        margin-right: 5px;
    }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-chart-bar"></i> Statistik Pendaftar</h1>
    </div>
    <div class="col-sm-6">
        <form class="form-inline justify-content-sm-end">
            <label class="mr-2">Tahun Pelajaran:</label>
            <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($tahunPelajaranList as $tp)
                <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
@stop

@section('content')
<div class="row">
    {{-- Total Pendaftar --}}
    <div class="col-lg-3 col-6">
        <div class="info-box bg-info stat-card">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pendaftar</span>
                <span class="info-box-number">{{ number_format($totalPendaftar) }}</span>
            </div>
        </div>
    </div>
    
    {{-- Pending --}}
    <div class="col-lg-3 col-6">
        <div class="info-box bg-warning stat-card">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending</span>
                <span class="info-box-number">{{ number_format($byStatus['pending'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    
    {{-- Diterima --}}
    <div class="col-lg-3 col-6">
        <div class="info-box bg-success stat-card">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Diterima</span>
                <span class="info-box-number">{{ number_format($byStatus['approved'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    
    {{-- Ditolak --}}
    <div class="col-lg-3 col-6">
        <div class="info-box bg-danger stat-card">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ditolak</span>
                <span class="info-box-number">{{ number_format($byStatus['rejected'] ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Chart By Jalur --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-route"></i> Pendaftar per Jalur</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartJalur"></canvas>
                </div>
                <div class="mt-3">
                    @foreach($byJalur as $jalur)
                    <div class="legend-item">
                        <span class="legend-color" style="background: {{ $jalur->warna ?? '#007bff' }}"></span>
                        <span>{{ $jalur->nama }}: <strong>{{ $jalur->total }}</strong></span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart By Jenis Kelamin --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-venus-mars"></i> Pendaftar per Jenis Kelamin</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartJenisKelamin"></canvas>
                </div>
                <div class="mt-3 text-center">
                    <div class="legend-item">
                        <span class="legend-color" style="background: #007bff"></span>
                        <span>Laki-laki: <strong>{{ $byJenisKelamin['laki-laki'] ?? 0 }}</strong></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background: #e83e8c"></span>
                        <span>Perempuan: <strong>{{ $byJenisKelamin['perempuan'] ?? 0 }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Chart By Status --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Pendaftar per Status</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart By Gelombang --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group"></i> Pendaftar per Gelombang</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartGelombang"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart By Pilihan Program --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Pendaftar per Program</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartProgram"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Trend Pendaftaran --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Trend Pendaftaran 30 Hari Terakhir</h3>
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-link"></i> Statistik Detail</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-2">
                        <a href="{{ route('admin.statistik.geografis') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-map-marked-alt"></i> Sebaran Geografis
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <a href="{{ route('admin.statistik.asal-sekolah') }}" class="btn btn-outline-success btn-block">
                            <i class="fas fa-school"></i> Asal Sekolah
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <a href="{{ route('admin.statistik.ekonomi') }}" class="btn btn-outline-info btn-block">
                            <i class="fas fa-wallet"></i> Status Ekonomi
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <a href="{{ route('admin.statistik.dokumen-prestasi') }}" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-trophy"></i> Dokumen Prestasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Daftar Pendaftar --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Daftar Pendaftar Berdasarkan Kriteria</h3>
        <div class="card-tools">
            <form class="form-inline" method="GET">
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">
                <select name="filter_type" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                    <option value="">-- Pilih Filter --</option>
                    <option value="status" {{ $filterType == 'status' ? 'selected' : '' }}>Status</option>
                    <option value="jenis_kelamin" {{ $filterType == 'jenis_kelamin' ? 'selected' : '' }}>Jenis Kelamin</option>
                    <option value="jalur" {{ $filterType == 'jalur' ? 'selected' : '' }}>Jalur</option>
                    <option value="gelombang" {{ $filterType == 'gelombang' ? 'selected' : '' }}>Gelombang</option>
                    <option value="pilihan_program" {{ $filterType == 'pilihan_program' ? 'selected' : '' }}>Pilihan Program</option>
                </select>
                @if($filterType == 'status')
                <select name="filter_value" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Status --</option>
                    <option value="pending" {{ $filterValue == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ $filterValue == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="final" {{ $filterValue == 'final' ? 'selected' : '' }}>Final</option>
                    <option value="rejected" {{ $filterValue == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
                @elseif($filterType == 'jenis_kelamin')
                <select name="filter_value" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="laki-laki" {{ $filterValue == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="perempuan" {{ $filterValue == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @elseif($filterType == 'jalur')
                <select name="filter_value" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Jalur --</option>
                    @foreach($jalurList as $jalur)
                    <option value="{{ $jalur->id }}" {{ $filterValue == $jalur->id ? 'selected' : '' }}>{{ $jalur->nama }}</option>
                    @endforeach
                </select>
                @elseif($filterType == 'gelombang')
                <select name="filter_value" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Gelombang --</option>
                    @foreach($gelombangList as $gel)
                    <option value="{{ $gel->id }}" {{ $filterValue == $gel->id ? 'selected' : '' }}>{{ $gel->nama }}</option>
                    @endforeach
                </select>
                @elseif($filterType == 'pilihan_program')
                <select name="filter_value" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Pilih Program --</option>
                    @foreach(array_keys($byPilihanProgram) as $prog)
                    <option value="{{ $prog }}" {{ $filterValue == $prog ? 'selected' : '' }}>{{ $prog }}</option>
                    @endforeach
                </select>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>No. Pendaftaran</th>
                    <th>Nama Lengkap</th>
                    <th>Jalur</th>
                    <th>Gelombang</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftarList as $i => $p)
                <tr>
                    <td>{{ ($pendaftarList->currentPage() - 1) * $pendaftarList->perPage() + $i + 1 }}</td>
                    <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td>
                        @if($p->jalurPendaftaran)
                        <span class="badge" style="background: {{ $p->jalurPendaftaran->warna }}; color: white;">
                            {{ $p->jalurPendaftaran->nama }}
                        </span>
                        @endif
                    </td>
                    <td>{{ $p->gelombangPendaftaran?->nama ?? '-' }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'secondary',
                                'verified' => 'info',
                                'final' => 'success',
                                'rejected' => 'danger'
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$p->status_verifikasi] ?? 'secondary' }}">
                            {{ ucfirst($p->status_verifikasi) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">
                    @if($filterType)
                    Tidak ada pendaftar dengan kriteria tersebut
                    @else
                    Pilih filter untuk melihat daftar pendaftar
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pendaftarList->hasPages())
    <div class="card-footer clearfix">
        {{ $pendaftarList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart Jalur
    new Chart(document.getElementById('chartJalur'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($byJalur->pluck('nama')) !!},
            datasets: [{
                data: {!! json_encode($byJalur->pluck('total')) !!},
                backgroundColor: {!! json_encode($byJalur->pluck('warna')->map(fn($c) => $c ?? '#007bff')) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
    
    // Chart Jenis Kelamin
    new Chart(document.getElementById('chartJenisKelamin'), {
        type: 'pie',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [{{ $byJenisKelamin['laki-laki'] ?? 0 }}, {{ $byJenisKelamin['perempuan'] ?? 0 }}],
                backgroundColor: ['#007bff', '#e83e8c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
    
    // Chart Status
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Verified', 'Diterima', 'Ditolak'],
            datasets: [{
                data: [
                    {{ $byStatus['pending'] ?? 0 }},
                    {{ $byStatus['verified'] ?? 0 }},
                    {{ $byStatus['approved'] ?? 0 }},
                    {{ $byStatus['rejected'] ?? 0 }}
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
        }
    });
    
    // Chart Gelombang
    new Chart(document.getElementById('chartGelombang'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($byGelombang->pluck('nama')) !!},
            datasets: [{
                label: 'Pendaftar',
                data: {!! json_encode($byGelombang->pluck('total')) !!},
                backgroundColor: '#17a2b8'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    
    // Chart Program
    new Chart(document.getElementById('chartProgram'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($byPilihanProgram)) !!},
            datasets: [{
                data: {!! json_encode(array_values($byPilihanProgram)) !!},
                backgroundColor: ['#28a745', '#6f42c1', '#fd7e14', '#20c997', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
        }
    });
    
    // Chart Trend
    new Chart(document.getElementById('chartTrend'), {
        type: 'line',
        data: {
            labels: {!! json_encode($trendPendaftaran->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
            datasets: [{
                label: 'Pendaftar',
                data: {!! json_encode($trendPendaftaran->pluck('total')) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });
</script>
@stop
