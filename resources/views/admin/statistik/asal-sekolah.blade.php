@extends('adminlte::page')

@section('title', 'Statistik Asal Sekolah')

@section('css')
<style>
    .chart-container { position: relative; height: 350px; }
    .table-stat th, .table-stat td { padding: 8px 12px; font-size: 13px; }
    .school-badge { font-size: 11px; padding: 3px 8px; }
    .search-form { max-width: 300px; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-school"></i> Statistik Asal Sekolah</h1>
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
{{-- Ringkasan --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-school"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sekolah Asal</span>
                <span class="info-box-number">{{ $totalSekolah }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pendaftar</span>
                <span class="info-box-number">{{ $byAsalSekolah->sum('total') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Rata-rata per Sekolah</span>
                <span class="info-box-number">{{ $totalSekolah > 0 ? round($byAsalSekolah->sum('total') / $totalSekolah, 1) : 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sekolah Terbanyak</span>
                <span class="info-box-number">{{ $topSekolah->first()->total ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Top 10 Chart --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy"></i> Top 10 Sekolah Terbanyak</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="top10Chart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Top 10 Table --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-medal"></i> Peringkat Sekolah</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Sekolah</th>
                            <th>NPSN</th>
                            <th class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSekolah as $i => $sekolah)
                        <tr>
                            <td>
                                @if($i == 0)
                                <span class="badge badge-warning"><i class="fas fa-trophy"></i></span>
                                @elseif($i == 1)
                                <span class="badge badge-secondary"><i class="fas fa-medal"></i></span>
                                @elseif($i == 2)
                                <span class="badge badge-danger"><i class="fas fa-award"></i></span>
                                @else
                                {{ $i + 1 }}
                                @endif
                            </td>
                            <td>
                                {{ $sekolah->asal_sekolah ?? 'Tidak Diketahui' }}
                            </td>
                            <td>
                                @if($sekolah->npsn)
                                <code class="school-badge">{{ $sekolah->npsn }}</code>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $sekolah->total }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- All Schools --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar Semua Sekolah Asal</h3>
        <div class="card-tools">
            <form class="search-form form-inline" method="GET">
                <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari sekolah/NPSN..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Sekolah</th>
                    <th>NPSN</th>
                    <th class="text-center">Jumlah Pendaftar</th>
                    <th class="text-center">% Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPendaftar = $byAsalSekolah->sum('total'); @endphp
                @forelse($byAsalSekolah as $i => $sekolah)
                <tr>
                    <td>{{ ($byAsalSekolah->currentPage() - 1) * $byAsalSekolah->perPage() + $i + 1 }}</td>
                    <td>{{ $sekolah->asal_sekolah ?? 'Tidak Diketahui' }}</td>
                    <td>
                        @if($sekolah->npsn)
                        <code class="school-badge">{{ $sekolah->npsn }}</code>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center"><strong>{{ $sekolah->total }}</strong></td>
                    <td class="text-center">
                        {{ $totalPendaftar > 0 ? round(($sekolah->total / $totalPendaftar) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($byAsalSekolah->hasPages())
    <div class="card-footer clearfix">
        {{ $byAsalSekolah->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    var top10Data = @json($topSekolah);
    
    new Chart(document.getElementById('top10Chart'), {
        type: 'bar',
        data: {
            labels: top10Data.map(s => (s.asal_sekolah || 'Tidak Diketahui').substring(0, 25)),
            datasets: [{
                label: 'Jumlah Pendaftar',
                data: top10Data.map(s => s.total),
                backgroundColor: [
                    '#ffc107', '#6c757d', '#dc3545', '#007bff', '#28a745',
                    '#17a2b8', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c'
                ],
                borderWidth: 0,
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
</script>
@stop
