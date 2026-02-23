@extends('adminlte::page')

@section('title', 'Laporan PPDB')

@section('css')
<style>
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .filter-card .form-control-sm { font-size: 0.85rem; }
    .section-card .card-header { cursor: pointer; }
    .section-card .card-header:hover { background-color: #f4f6f9; }
    .rincian-table th, .rincian-table td { padding: 0.4rem 0.75rem; font-size: 0.85rem; vertical-align: middle; }
    .rincian-table .row-total { background-color: #e8f5e9; font-weight: bold; }
    .rincian-table .row-header { background-color: #ecf0f1; font-weight: bold; }
    .chart-bar { height: 24px; border-radius: 4px; margin-bottom: 6px; min-width: 2px; }
    .chart-bar-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; font-size: 0.85rem; }
    .badge-l { background-color: #007bff; color: #fff; }
    .badge-p { background-color: #dc3545; color: #fff; }
    .section-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; color: #fff; font-weight: bold; font-size: 0.85rem; margin-right: 8px; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-chart-bar mr-2"></i>Laporan PPDB</h1>
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
                    <div class="col-md-2">
                        <label class="mb-1 small">Jalur Pendaftaran</label>
                        <select name="jalur_id" id="jalur_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua Jalur --</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 small">Gelombang</label>
                        <select name="gelombang_id" id="gelombang_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($gelombangs as $gel)
                                <option value="{{ $gel->id }}" {{ request('gelombang_id') == $gel->id ? 'selected' : '' }}>
                                    {{ $gel->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search mr-1"></i> Tampilkan
                        </button>
                        <a href="{{ route('admin.report.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-sync mr-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.report.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.report.excel', request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Excel
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

    {{-- Statistik Ringkasan --}}
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Pendaftar</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['dapat_nomor_tes']) }}</h3>
                    <p>Dapat Nomor Tes</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['tidak_dapat_nomor_tes']) }}</h3>
                    <p>Tanpa Nomor Tes</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-purple stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['ikut_tes']) }}</h3>
                    <p>Mengikuti Tes</p>
                </div>
                <div class="icon"><i class="fas fa-pen-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-olive stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['lulus_total']) }}</h3>
                    <p>Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger stat-card">
                <div class="inner">
                    <h3>{{ number_format($stats['tidak_lulus_total']) }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 1: TOTAL PENDAFTAR --}}
    {{-- ============================================================ --}}
    @include('admin.report._section_detail', [
        'sectionNumber' => '1',
        'sectionColor' => '#3498db',
        'sectionIcon' => 'fas fa-users',
        'sectionTitle' => 'Total Pendaftar',
        'sectionId' => 'totalPendaftar',
        'data' => $stats['total_pendaftar'],
        'collapsed' => false,
    ])

    {{-- ============================================================ --}}
    {{-- SECTION 2: MENDAPAT NOMOR TES --}}
    {{-- ============================================================ --}}
    @include('admin.report._section_detail', [
        'sectionNumber' => '2',
        'sectionColor' => '#27ae60',
        'sectionIcon' => 'fas fa-id-card',
        'sectionTitle' => 'Yang Mendapat Nomor Tes',
        'sectionId' => 'denganNomorTes',
        'data' => $stats['dengan_nomor_tes'],
        'collapsed' => true,
    ])

    {{-- ============================================================ --}}
    {{-- SECTION 3: TIDAK MENDAPAT NOMOR TES --}}
    {{-- ============================================================ --}}
    @include('admin.report._section_detail', [
        'sectionNumber' => '3',
        'sectionColor' => '#e67e22',
        'sectionIcon' => 'fas fa-times-circle',
        'sectionTitle' => 'Yang Tidak Mendapat Nomor Tes',
        'sectionId' => 'tanpaNomorTes',
        'data' => $stats['tanpa_nomor_tes'],
        'collapsed' => true,
    ])

    {{-- ============================================================ --}}
    {{-- SECTION 4: MENGIKUTI TES --}}
    {{-- ============================================================ --}}
    @include('admin.report._section_detail', [
        'sectionNumber' => '4',
        'sectionColor' => '#8e44ad',
        'sectionIcon' => 'fas fa-pen-alt',
        'sectionTitle' => 'Yang Mengikuti Tes (CBT / TBQ)',
        'sectionId' => 'pesertaTes',
        'data' => $stats['peserta_tes'],
        'collapsed' => true,
    ])

    {{-- ============================================================ --}}
    {{-- SECTION 5: KELULUSAN --}}
    {{-- ============================================================ --}}
    <div class="card section-card">
        <div class="card-header" data-toggle="collapse" data-target="#collapseKelulusan" aria-expanded="false">
            <h3 class="card-title">
                <span class="section-number" style="background-color: #2c3e50;">5</span>
                <i class="fas fa-graduation-cap mr-2"></i>
                <strong>Kelulusan Akhir</strong>
                <span class="badge badge-success ml-2">Lulus: {{ $stats['lulus_total'] }}</span>
                <span class="badge badge-danger ml-1">Tidak Lulus: {{ $stats['tidak_lulus_total'] }}</span>
                <span class="badge badge-warning ml-1">Cadangan: {{ $stats['cadangan_total'] }}</span>
            </h3>
            <div class="card-tools">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div id="collapseKelulusan" class="collapse">
            <div class="card-body">
                {{-- LULUS --}}
                <h5 class="text-success mb-3"><i class="fas fa-check-circle mr-1"></i> Yang LULUS ({{ $stats['lulus_total'] }} orang)</h5>
                @include('admin.report._section_tables', ['data' => $stats['kelulusan']])

                <hr>

                {{-- TIDAK LULUS --}}
                <h5 class="text-danger mb-3"><i class="fas fa-times-circle mr-1"></i> Yang TIDAK LULUS ({{ $stats['tidak_lulus_total'] }} orang)</h5>
                @include('admin.report._section_tables', ['data' => $stats['kelulusan_tidak_lulus']])

                <hr>

                {{-- CADANGAN --}}
                <h5 class="text-warning mb-3"><i class="fas fa-hourglass-half mr-1"></i> CADANGAN ({{ $stats['cadangan_total'] }} orang)</h5>
                @include('admin.report._section_tables', ['data' => $stats['kelulusan_cadangan']])
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- STATISTIK TAMBAHAN --}}
    {{-- ============================================================ --}}
    <div class="card">
        <div class="card-header" data-toggle="collapse" data-target="#collapseTambahan" aria-expanded="false" style="cursor:pointer">
            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i><strong>Statistik Tambahan</strong></h3>
            <div class="card-tools"><i class="fas fa-chevron-down"></i></div>
        </div>
        <div id="collapseTambahan" class="collapse">
            <div class="card-body">

                {{-- Per Jalur --}}
                <h5 class="mb-3"><i class="fas fa-road mr-1"></i> Per Jalur Pendaftaran</h5>
                <table class="table table-striped table-hover table-sm rincian-table mb-4">
                    <thead class="thead-dark">
                        <tr>
                            <th>Jalur</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                            <th class="text-center">Finalisasi</th>
                            <th class="text-center">Nomor Tes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['per_jalur'] as $namaJalur => $dj)
                        <tr>
                            <td><strong>{{ $namaJalur }}</strong></td>
                            <td class="text-center">{{ $dj['total'] }}</td>
                            <td class="text-center">{{ $dj['laki_laki'] }}</td>
                            <td class="text-center">{{ $dj['perempuan'] }}</td>
                            <td class="text-center">{{ $dj['finalisasi'] }}</td>
                            <td class="text-center">{{ $dj['nomor_tes'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Per Gelombang --}}
                <h5 class="mb-3"><i class="fas fa-layer-group mr-1"></i> Per Gelombang</h5>
                <table class="table table-striped table-sm rincian-table mb-4">
                    <thead class="thead-dark">
                        <tr>
                            <th>Gelombang</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['per_gelombang'] as $namaGel => $dg)
                        <tr>
                            <td>{{ $namaGel }}</td>
                            <td class="text-center"><strong>{{ $dg['total'] }}</strong></td>
                            <td class="text-center">{{ $dg['laki_laki'] }}</td>
                            <td class="text-center">{{ $dg['perempuan'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Status --}}
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-clipboard-check mr-1"></i> Status Verifikasi</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Pending</span><span class="info-box-number">{{ $stats['status_verifikasi']['pending'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Terverifikasi</span><span class="info-box-number">{{ $stats['status_verifikasi']['verified'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span><div class="info-box-content"><span class="info-box-text">Ditolak</span><span class="info-box-number">{{ $stats['status_verifikasi']['rejected'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-info"><i class="fas fa-redo"></i></span><div class="info-box-content"><span class="info-box-text">Revisi</span><span class="info-box-number">{{ $stats['status_verifikasi']['revisi'] }}</span></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-check-double mr-1"></i> Status Admisi</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span><div class="info-box-content"><span class="info-box-text">Diterima</span><span class="info-box-number">{{ $stats['status_admisi']['diterima'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span><div class="info-box-content"><span class="info-box-text">Cadangan</span><span class="info-box-number">{{ $stats['status_admisi']['cadangan'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span><div class="info-box-content"><span class="info-box-text">Ditolak</span><span class="info-box-number">{{ $stats['status_admisi']['ditolak'] }}</span></div></div>
                            </div>
                            <div class="col-6">
                                <div class="info-box mb-2"><span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Pending</span><span class="info-box-number">{{ $stats['status_admisi']['pending'] }}</span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sebaran Wilayah --}}
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-map-marked-alt mr-1"></i> Sebaran Kabupaten/Kota</h5>
                        @foreach($stats['sebaran_kabupaten'] as $nama => $jumlah)
                        <div class="chart-bar-label"><span>{{ $nama }}</span><strong>{{ $jumlah }}</strong></div>
                        <div class="chart-bar bg-info" style="width: {{ $stats['total'] > 0 ? max(round($jumlah / $stats['total'] * 100, 1), 1) : 0 }}%; opacity: {{ max(0.4, $jumlah / max($stats['sebaran_kabupaten']->first(), 1)) }}"></div>
                        @endforeach
                        @if($stats['sebaran_kabupaten']->isEmpty())
                            <p class="text-muted text-center mb-0">Belum ada data</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-map-pin mr-1"></i> Sebaran Kecamatan (Top 20)</h5>
                        @foreach($stats['sebaran_kecamatan'] as $nama => $jumlah)
                        <div class="chart-bar-label"><span>{{ $nama }}</span><strong>{{ $jumlah }}</strong></div>
                        <div class="chart-bar bg-success" style="width: {{ $stats['total'] > 0 ? max(round($jumlah / $stats['total'] * 100, 1), 1) : 0 }}%; opacity: {{ max(0.4, $jumlah / max($stats['sebaran_kecamatan']->first(), 1)) }}"></div>
                        @endforeach
                        @if($stats['sebaran_kecamatan']->isEmpty())
                            <p class="text-muted text-center mb-0">Belum ada data</p>
                        @endif
                    </div>
                </div>

                {{-- Sebaran Sekolah Asal --}}
                <hr>
                <h5 class="mb-3"><i class="fas fa-school mr-1"></i> Sebaran Sekolah Asal &amp; Trending
                    <span class="badge badge-info ml-1">{{ count($stats['sebaran_sekolah']) }} sekolah</span>
                </h5>

                @if(count($stats['sebaran_sekolah']) > 0)
                @php
                    $sebaranAll = $stats['sebaran_sekolah'];
                    $sebaranTop20 = array_slice($sebaranAll, 0, 20);
                    $maxSekolah = $sebaranTop20[0]['total'] ?? 1;
                @endphp

                {{-- Top 20 Visual Bar --}}
                <div class="mb-3">
                    <h6 class="text-muted"><i class="fas fa-trophy mr-1 text-warning"></i> Top 20 Sekolah Asal</h6>
                    @foreach($sebaranTop20 as $idx => $sk)
                    <div class="chart-bar-label">
                        <span>
                            <strong class="text-primary">{{ $idx + 1 }}.</strong>
                            {{ $sk['nama'] }}
                            <small class="text-muted">({{ $sk['bentuk'] }}/{{ $sk['status'] }})</small>
                        </span>
                        <strong>{{ $sk['total'] }} <small class="text-muted">(L:{{ $sk['l'] }} P:{{ $sk['p'] }})</small></strong>
                    </div>
                    <div class="chart-bar bg-warning" style="width: {{ max(round($sk['total'] / $maxSekolah * 100, 1), 2) }}%; opacity: {{ max(0.4, $sk['total'] / $maxSekolah) }}"></div>
                    @endforeach
                </div>

                {{-- Full Table (collapsible) --}}
                <div class="mb-2">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#collapseSebaranSekolah">
                        <i class="fas fa-list mr-1"></i> Tampilkan Semua Sekolah ({{ count($sebaranAll) }})
                    </button>
                </div>
                <div class="collapse" id="collapseSebaranSekolah">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm rincian-table mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th>Nama Sekolah</th>
                                    <th class="text-center">NPSN</th>
                                    <th class="text-center">Bentuk</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">L</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sebaranAll as $idx => $sk)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $sk['nama'] }}</td>
                                    <td class="text-center"><small>{{ $sk['npsn'] }}</small></td>
                                    <td class="text-center">{{ $sk['bentuk'] }}</td>
                                    <td class="text-center">
                                        @if(strtoupper($sk['status']) === 'NEGERI')
                                            <span class="badge badge-primary">Negeri</span>
                                        @elseif(strtoupper($sk['status']) === 'SWASTA')
                                            <span class="badge badge-secondary">Swasta</span>
                                        @else
                                            <span class="badge badge-light">{{ $sk['status'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><strong>{{ $sk['total'] }}</strong></td>
                                    <td class="text-center">{{ $sk['l'] }}</td>
                                    <td class="text-center">{{ $sk['p'] }}</td>
                                    <td class="text-center text-muted">{{ $stats['total'] > 0 ? round($sk['total'] / $stats['total'] * 100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    <p class="text-muted text-center mb-0">Belum ada data sekolah asal</p>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    // Toggle chevron icons on collapse
    $('.section-card .card-header, [data-toggle="collapse"]').on('click', function() {
        var $icon = $(this).find('.card-tools .fas, .fa-chevron-down, .fa-chevron-up');
        if ($icon.length) {
            $icon.toggleClass('fa-chevron-down fa-chevron-up');
        }
    });
});
</script>
@stop
