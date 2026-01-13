@extends('adminlte::page')

@section('title', 'Statistik Dokumen & Prestasi')

@section('css')
<style>
    .chart-container { position: relative; height: 300px; }
    .stat-card { border-radius: 10px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 15px; top: 15px; }
    .stat-number { font-size: 2rem; font-weight: bold; }
    .stat-label { font-size: 0.9rem; color: #6c757d; }
    .prestasi-badge { font-size: 12px; padding: 5px 10px; margin: 2px; display: inline-block; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-certificate"></i> Statistik Dokumen & Prestasi</h1>
    </div>
    <div class="col-sm-6">
        <div class="d-flex justify-content-sm-end align-items-center" style="gap: 10px;">
            <a href="{{ route('admin.statistik.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <form class="form-inline">
                <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@stop

@section('content')
@php
    // Prepare stats from byJenisDokumen
    $dokumenStats = [];
    foreach($byJenisDokumen as $dok) {
        $dokumenStats[$dok->jenis_dokumen] = $dok->total;
    }
    $totalDokumen = $byJenisDokumen->sum('total');
@endphp

{{-- Statistik Dokumen Prestasi --}}
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-gradient-warning text-white">
            <div class="card-body">
                <i class="fas fa-trophy stat-icon"></i>
                <div class="stat-number">{{ $dokumenStats['sertifikat_prestasi'] ?? 0 }}</div>
                <div class="stat-label text-white">Sertifikat Prestasi</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-gradient-info text-white">
            <div class="card-body">
                <i class="fas fa-award stat-icon"></i>
                <div class="stat-number">{{ $dokumenStats['piagam_penghargaan'] ?? 0 }}</div>
                <div class="stat-label text-white">Piagam Penghargaan</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-gradient-success text-white">
            <div class="card-body">
                <i class="fas fa-quran stat-icon"></i>
                <div class="stat-number">{{ $dokumenStats['sertifikat_tahfidz'] ?? 0 }}</div>
                <div class="stat-label text-white">Sertifikat Tahfidz</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-gradient-danger text-white">
            <div class="card-body">
                <i class="fas fa-medal stat-icon"></i>
                <div class="stat-number">{{ $dokumenStats['sertifikat_olimpiade'] ?? 0 }}</div>
                <div class="stat-label text-white">Sertifikat Olimpiade</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card bg-gradient-primary text-white">
            <div class="card-body">
                <i class="fas fa-file-alt stat-icon"></i>
                <div class="stat-number">{{ $dokumenStats['sertifikat_lainnya'] ?? 0 }}</div>
                <div class="stat-label text-white">Sertifikat Lainnya</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card bg-gradient-secondary text-white">
            <div class="card-body">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-number">{{ $pendaftarDenganPrestasi }}</div>
                <div class="stat-label text-white">Pendaftar dgn Prestasi</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card bg-gradient-dark text-white">
            <div class="card-body">
                <i class="fas fa-folder-open stat-icon"></i>
                <div class="stat-number">{{ $totalDokumen }}</div>
                <div class="stat-label text-white">Total Dokumen</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Perbandingan Jenis Dokumen --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Perbandingan Jenis Dokumen</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dokumenPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Dokumen per Jenis Bar --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Jumlah per Jenis Dokumen</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dokumenBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pendaftar dengan Prestasi --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-star"></i> Pendaftar dengan Dokumen Prestasi</h3>
        <div class="card-tools">
            <span class="badge badge-primary">{{ $pendaftarDenganPrestasi }} dari {{ $totalPendaftar }} pendaftar</span>
            <span class="badge badge-success">{{ $totalPendaftar > 0 ? round(($pendaftarDenganPrestasi / $totalPendaftar) * 100, 1) : 0 }}%</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="progress-group">
                    <span class="progress-text">Pendaftar dengan Prestasi</span>
                    <span class="float-right"><b>{{ $pendaftarDenganPrestasi }}</b>/{{ $totalPendaftar }}</span>
                    <div class="progress">
                        @php $persen = $totalPendaftar > 0 ? ($pendaftarDenganPrestasi / $totalPendaftar) * 100 : 0; @endphp
                        <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="progress-group">
                    <span class="progress-text">Pendaftar Tanpa Prestasi</span>
                    <span class="float-right"><b>{{ $totalPendaftar - $pendaftarDenganPrestasi }}</b>/{{ $totalPendaftar }}</span>
                    <div class="progress">
                        @php $persen = $totalPendaftar > 0 ? (($totalPendaftar - $pendaftarDenganPrestasi) / $totalPendaftar) * 100 : 0; @endphp
                        <div class="progress-bar bg-secondary" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Pendaftar dengan Prestasi --}}
@if($detailPrestasi && $detailPrestasi->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Pendaftar dengan Dokumen Prestasi</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Pendaftar</th>
                    <th>Asal Sekolah</th>
                    <th class="text-center">Jumlah Dokumen</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detailPrestasi as $i => $item)
                <tr>
                    <td>{{ ($detailPrestasi->currentPage() - 1) * $detailPrestasi->perPage() + $i + 1 }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->asal_sekolah ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $item->dokumen->count() }} dokumen</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.pendaftar.show', $item->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($detailPrestasi->hasPages())
    <div class="card-footer clearfix">
        {{ $detailPrestasi->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    var byJenisDokumen = @json($byJenisDokumen);
    var labels = byJenisDokumen.map(d => d.label);
    var data = byJenisDokumen.map(d => d.total);
    var colors = ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#007bff'];
    var data = [
        dokumenStats.sertifikat_prestasi,
    // Pie Chart
    new Chart(document.getElementById('dokumenPieChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
    
    // Bar Chart
    new Chart(document.getElementById('dokumenBarChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah',
                data: data,
                backgroundColor: colors,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@stop
