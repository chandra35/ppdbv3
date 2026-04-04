@extends('adminlte::page')

@section('title', 'Nilai TBQ')

@section('css')
<style>
    .session-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .session-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .progress-mini {
        height: 6px;
        border-radius: 3px;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-chart-bar mr-2"></i>Nilai TBQ
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Nilai TBQ</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="fas fa-layer-group mr-1"></i>
        <strong>Konteks aktif:</strong>
        Tahun {{ $contextInfo['tahun'] }},
        Jalur {{ $contextInfo['jalur'] }},
        Gelombang {{ $contextInfo['gelombang'] }}.
    </div>

    <div class="card card-outline card-info">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-seleksi.index') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajarans as $tahun)
                                <option value="{{ $tahun->id }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ $selectedJalurIdInput == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Gelombang</label>
                        <select name="gelombang_id" class="form-control">
                            <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                            @foreach($gelombangs as $gelombang)
                                <option value="{{ $gelombang->id }}" {{ $selectedGelombangIdInput == $gelombang->id ? 'selected' : '' }}>
                                    {{ $gelombang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter mr-1"></i>Terapkan Filter
                    </button>
                    <a href="{{ route('admin.nilai-seleksi.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_sesi'] }}</h3>
                    <p>Total Sesi Ujian</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_peserta'] }}</h3>
                    <p>Total Peserta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['sudah_dinilai'] }}</h3>
                    <p>Sudah Dinilai</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['sudah_verifikasi'] }}</h3>
                    <p>Sudah Verifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Cards -->
    <div class="row">
        <div class="col-md-3">
            <a href="{{ route('admin.nilai-seleksi.bobot') }}" class="text-decoration-none">
                <div class="card bg-gradient-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-balance-scale fa-3x text-primary mb-3"></i>
                        <h5>Pengaturan Bobot Nilai</h5>
                        <p class="text-muted mb-0">Atur persentase bobot tiap komponen penilaian</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.nilai-seleksi.upload') }}" class="text-decoration-none">
                <div class="card bg-gradient-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-file-upload fa-3x text-warning mb-3"></i>
                        <h5>Upload Nilai TBQ</h5>
                        <p class="text-muted mb-0">Upload nilai dari Excel Lembar Penilaian</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.nilai-seleksi.rekap') }}" class="text-decoration-none">
                <div class="card bg-gradient-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                        <h5>Rekap Nilai</h5>
                        <p class="text-muted mb-0">Lihat dan export rekapitulasi nilai seluruh peserta</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.sesi-ujian.index') }}" class="text-decoration-none">
                <div class="card bg-gradient-light">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                        <h5>Kelola Sesi Ujian</h5>
                        <p class="text-muted mb-0">Kelola sesi dan assign penguji</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Sesi Ujian List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Pilih Sesi Ujian untuk Verifikasi Nilai</h3>
        </div>
        <div class="card-body">
            @if($sesiUjians->count() > 0)
                <div class="row">
                    @foreach($sesiUjians as $sesi)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.nilai-seleksi.show', $sesi->id) }}" class="text-decoration-none">
                                <div class="card session-card border-left-primary">
                                    <div class="card-body">
                                        <h5 class="mb-2">
                                            <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                                            {{ $sesi->nama_sesi }}
                                        </h5>
                                        
                                        <p class="text-muted mb-2">
                                            <small>
                                                <i class="fas fa-road mr-1"></i>{{ $sesi->jalur->nama ?? '-' }}
                                                @if($sesi->gelombang)
                                                    - {{ $sesi->gelombang->nama }}
                                                @endif
                                            </small>
                                        </p>

                                        <!-- Progress -->
                                        @php
                                            $totalPeserta = $sesi->ruangan ? $sesi->ruangan->sum(fn($r) => $r->peserta->count()) : 0;
                                            $submitted = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $sesi->id)
                                                ->where('status', 'submitted')->count();
                                            $verified = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $sesi->id)
                                                ->where('status', 'verified')->count();
                                        @endphp
                                        
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <small>Verifikasi</small>
                                                <small>{{ $verified }}/{{ $totalPeserta }}</small>
                                            </div>
                                            <div class="progress progress-mini">
                                                <div class="progress-bar bg-success" style="width: {{ $totalPeserta > 0 ? ($verified/$totalPeserta*100) : 0 }}%"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between text-sm">
                                            <span class="text-warning">
                                                <i class="fas fa-clock mr-1"></i>{{ $submitted }} menunggu
                                            </span>
                                            <span class="text-success">
                                                <i class="fas fa-check mr-1"></i>{{ $verified }} verified
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5>Belum Ada Sesi Ujian</h5>
                    <p class="text-muted">Buat sesi ujian melalui menu Cetak Ruang Ujian</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
