@extends('adminlte::page')

@section('title', 'Laporan PPDB')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    .chart-bar {
        height: 24px;
        border-radius: 4px;
        margin-bottom: 6px;
        position: relative;
        min-width: 2px;
    }
    .chart-bar-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2px;
        font-size: 0.85rem;
    }
    .sebaran-table td,
    .sebaran-table th {
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
    }
    .filter-card .form-control-sm {
        font-size: 0.85rem;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-chart-bar mr-2"></i>Laporan PPDB
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan PPDB</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Filter --}}
    <div class="card filter-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.report.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="mb-1 small">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua --</option>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id', $tahunAktif?->id) == $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1 small">Jalur Pendaftaran</label>
                        <select name="jalur_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua Jalur --</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1 small">Gelombang</label>
                        <select name="gelombang_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($gelombangs as $gel)
                                <option value="{{ $gel->id }}" {{ request('gelombang_id') == $gel->id ? 'selected' : '' }}>
                                    {{ $gel->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search mr-1"></i> Tampilkan
                        </button>
                        <a href="{{ route('admin.report.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-sync mr-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.report.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Header Info --}}
    @if($selectedTahun)
    <div class="callout callout-info">
        <h5 class="mb-0">
            <i class="fas fa-calendar mr-1"></i>
            Laporan PPDB Tahun Pelajaran: <strong>{{ $selectedTahun->nama }}</strong>
            @if(request('jalur_id'))
                &mdash; Jalur: <strong>{{ $jalurs->firstWhere('id', request('jalur_id'))?->nama }}</strong>
            @endif
            @if(request('gelombang_id'))
                &mdash; Gelombang: <strong>{{ $gelombangs->firstWhere('id', request('gelombang_id'))?->nama }}</strong>
            @endif
        </h5>
    </div>
    @endif

    {{-- Statistik Utama --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Pendaftar</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['dapat_nomor_tes']) }}</h3>
                    <p>Mendapat Nomor Tes</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['finalisasi']) }}</h3>
                    <p>Sudah Finalisasi</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-purple stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['ikut_tes']) }}</h3>
                    <p>Mengikuti Tes</p>
                </div>
                <div class="icon"><i class="fas fa-pen-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- Detail Tes & Gender --}}
    <div class="row">
        {{-- Jenis Kelamin --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-venus-mars mr-2"></i>Jenis Kelamin</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="info-box mb-0 bg-primary">
                                <div class="info-box-content">
                                    <span class="info-box-text">Laki-laki</span>
                                    <span class="info-box-number">{{ $stats['jenis_kelamin']['laki_laki'] }}</span>
                                    <small>{{ $stats['total'] > 0 ? round($stats['jenis_kelamin']['laki_laki'] / $stats['total'] * 100, 1) : 0 }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box mb-0 bg-danger">
                                <div class="info-box-content">
                                    <span class="info-box-text">Perempuan</span>
                                    <span class="info-box-number">{{ $stats['jenis_kelamin']['perempuan'] }}</span>
                                    <small>{{ $stats['total'] > 0 ? round($stats['jenis_kelamin']['perempuan'] / $stats['total'] * 100, 1) : 0 }}%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($stats['total'] > 0)
                    <div class="progress mt-3" style="height: 20px;">
                        <div class="progress-bar bg-primary" style="width: {{ round($stats['jenis_kelamin']['laki_laki'] / $stats['total'] * 100, 1) }}%">
                            {{ $stats['jenis_kelamin']['laki_laki'] }}
                        </div>
                        <div class="progress-bar bg-danger" style="width: {{ round($stats['jenis_kelamin']['perempuan'] / $stats['total'] * 100, 1) }}%">
                            {{ $stats['jenis_kelamin']['perempuan'] }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ikut Tes Detail --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-pen-alt mr-2"></i>Peserta Tes</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><i class="fas fa-quran text-success mr-1"></i> Ikut TBQ</td>
                            <td class="text-right"><strong>{{ number_format($stats['ikut_tbq']) }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-laptop text-info mr-1"></i> Ikut CBT</td>
                            <td class="text-right"><strong>{{ number_format($stats['ikut_cbt']) }}</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td><i class="fas fa-users text-purple mr-1"></i> Total Ikut Tes</td>
                            <td class="text-right"><strong class="text-purple">{{ number_format($stats['ikut_tes']) }}</strong></td>
                        </tr>
                        @if($stats['total'] > 0)
                        <tr>
                            <td colspan="2">
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar bg-purple" style="width: {{ round($stats['ikut_tes'] / $stats['total'] * 100, 1) }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($stats['ikut_tes'] / $stats['total'] * 100, 1) }}% dari total pendaftar</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Status Admisi --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-double mr-2"></i>Status Admisi</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><span class="badge badge-success">Diterima</span></td>
                            <td class="text-right"><strong>{{ number_format($stats['status_admisi']['diterima']) }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">Cadangan</span></td>
                            <td class="text-right"><strong>{{ number_format($stats['status_admisi']['cadangan']) }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-danger">Ditolak</span></td>
                            <td class="text-right"><strong>{{ number_format($stats['status_admisi']['ditolak']) }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-secondary">Pending</span></td>
                            <td class="text-right"><strong>{{ number_format($stats['status_admisi']['pending']) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Per Jalur --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-road mr-2"></i>Statistik Per Jalur Pendaftaran</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Jalur</th>
                        <th class="text-center">Total</th>
                        <th class="text-center"><i class="fas fa-mars text-primary"></i> L</th>
                        <th class="text-center"><i class="fas fa-venus text-danger"></i> P</th>
                        <th class="text-center">Finalisasi</th>
                        <th class="text-center">Nomor Tes</th>
                        <th width="30%">Distribusi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['per_jalur'] as $namaJalur => $data)
                    <tr>
                        <td><strong>{{ $namaJalur }}</strong></td>
                        <td class="text-center">{{ $data['total'] }}</td>
                        <td class="text-center">{{ $data['laki_laki'] }}</td>
                        <td class="text-center">{{ $data['perempuan'] }}</td>
                        <td class="text-center">{{ $data['finalisasi'] }}</td>
                        <td class="text-center">{{ $data['nomor_tes'] }}</td>
                        <td>
                            @if($stats['total'] > 0)
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar bg-primary" style="width: {{ round($data['laki_laki'] / $stats['total'] * 100, 1) }}%" title="Laki-laki: {{ $data['laki_laki'] }}">
                                    {{ $data['laki_laki'] }}
                                </div>
                                <div class="progress-bar bg-danger" style="width: {{ round($data['perempuan'] / $stats['total'] * 100, 1) }}%" title="Perempuan: {{ $data['perempuan'] }}">
                                    {{ $data['perempuan'] }}
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per Gelombang & Pilihan Program --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Per Gelombang</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Gelombang</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">L</th>
                                <th class="text-center">P</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['per_gelombang'] as $nama => $data)
                            <tr>
                                <td>{{ $nama }}</td>
                                <td class="text-center"><strong>{{ $data['total'] }}</strong></td>
                                <td class="text-center">{{ $data['laki_laki'] }}</td>
                                <td class="text-center">{{ $data['perempuan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-heart mr-2"></i>Pilihan Program</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">L</th>
                                <th class="text-center">P</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['pilihan_program'] as $nama => $data)
                            <tr>
                                <td>{{ $nama }}</td>
                                <td class="text-center"><strong>{{ $data['total'] }}</strong></td>
                                <td class="text-center">{{ $data['laki_laki'] }}</td>
                                <td class="text-center">{{ $data['perempuan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sebaran Wilayah --}}
    <div class="row">
        {{-- Kabupaten --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt mr-2"></i>Sebaran Wilayah (Kabupaten/Kota)</h3>
                </div>
                <div class="card-body">
                    @foreach($stats['sebaran_kabupaten'] as $nama => $jumlah)
                    <div class="chart-bar-label">
                        <span>{{ $nama }}</span>
                        <strong>{{ $jumlah }}</strong>
                    </div>
                    <div class="chart-bar bg-info" style="width: {{ $stats['total'] > 0 ? max(round($jumlah / $stats['total'] * 100, 1), 1) : 0 }}%; opacity: {{ max(0.4, $jumlah / max($stats['sebaran_kabupaten']->first(), 1)) }}">
                    </div>
                    @endforeach
                    @if($stats['sebaran_kabupaten']->isEmpty())
                        <p class="text-muted text-center mb-0">Belum ada data wilayah</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kecamatan Top 20 --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin mr-2"></i>Sebaran Wilayah (Kecamatan) - Top 20</h3>
                </div>
                <div class="card-body">
                    @foreach($stats['sebaran_kecamatan'] as $nama => $jumlah)
                    <div class="chart-bar-label">
                        <span>{{ $nama }}</span>
                        <strong>{{ $jumlah }}</strong>
                    </div>
                    <div class="chart-bar bg-success" style="width: {{ $stats['total'] > 0 ? max(round($jumlah / $stats['total'] * 100, 1), 1) : 0 }}%; opacity: {{ max(0.4, $jumlah / max($stats['sebaran_kecamatan']->first(), 1)) }}">
                    </div>
                    @endforeach
                    @if($stats['sebaran_kecamatan']->isEmpty())
                        <p class="text-muted text-center mb-0">Belum ada data wilayah</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sebaran Asal Sekolah --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-school mr-2"></i>Sebaran Asal Sekolah - Top 20</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover sebaran-table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Sekolah Asal</th>
                        <th class="text-center" width="15%">Jumlah</th>
                        <th width="40%">Proporsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['sebaran_sekolah'] as $nama => $jumlah)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $nama }}</td>
                        <td class="text-center"><strong>{{ $jumlah }}</strong></td>
                        <td>
                            <div class="progress" style="height: 16px;">
                                <div class="progress-bar bg-warning" style="width: {{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%">
                                    {{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($stats['sebaran_sekolah']->isEmpty())
                <p class="text-muted text-center py-3 mb-0">Belum ada data asal sekolah</p>
            @endif
        </div>
    </div>

    {{-- Status Verifikasi --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Status Verifikasi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number">{{ $stats['status_verifikasi']['pending'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Terverifikasi</span>
                            <span class="info-box-number">{{ $stats['status_verifikasi']['verified'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ditolak</span>
                            <span class="info-box-number">{{ $stats['status_verifikasi']['rejected'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info"><i class="fas fa-redo"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Perlu Revisi</span>
                            <span class="info-box-number">{{ $stats['status_verifikasi']['revisi'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});
</script>
@stop
