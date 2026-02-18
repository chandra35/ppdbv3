@extends('adminlte::page')

@section('title', 'Dashboard Penguji')

@section('css')
<style>
    .sesi-card {
        transition: all 0.3s ease;
    }
    .sesi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .ruang-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
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
            <i class="fas fa-user-tie mr-2"></i>Dashboard Penguji
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard Penguji</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Welcome Card -->
    <div class="card bg-gradient-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="text-white mb-1">Selamat Datang, {{ Auth::user()->name }}!</h4>
                    <p class="text-white-50 mb-0">
                        Anda ditugaskan sebagai penguji seleksi. Silakan pilih ruangan untuk mulai melakukan penilaian.
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <i class="fas fa-clipboard-check fa-4x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    @if($sesiGroups->count() > 0)
        @foreach($sesiGroups as $group)
            <div class="card sesi-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>{{ $group['sesi']->nama }}
                    </h3>
                    <div class="card-tools">
                        @if($group['sesi']->status == 'locked')
                            <span class="badge badge-warning">Menunggu Dimulai</span>
                        @elseif($group['sesi']->status == 'in_progress')
                            <span class="badge badge-success">Sedang Berlangsung</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted">Tanggal Ujian</small>
                            <p class="mb-0"><strong>{{ $group['sesi']->tanggal?->format('d F Y') ?? '-' }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Waktu</small>
                            <p class="mb-0"><strong>{{ $group['sesi']->waktu_mulai }} - {{ $group['sesi']->waktu_selesai }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Jalur</small>
                            <p class="mb-0"><strong>{{ $group['sesi']->jalur->nama ?? '-' }}</strong></p>
                        </div>
                    </div>

                    <h6 class="mb-3"><i class="fas fa-door-open mr-2"></i>Ruangan yang Ditugaskan:</h6>
                    
                    <div class="row">
                        @foreach($group['ruangan'] as $ruang)
                            @php
                                $totalPeserta = $ruang->peserta->count();
                                $sudahDinilai = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $group['sesi']->id)
                                    ->where('ruang_ujian_id', $ruang->id)
                                    ->where('penguji_id', Auth::id())
                                    ->whereIn('status', ['submitted', 'verified'])
                                    ->count();
                                $percentage = $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0;
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="ruang-item">
                                    <div>
                                        <strong>{{ $ruang->nama_ruang }}</strong>
                                        <div class="progress progress-mini mt-2" style="width: 150px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $sudahDinilai }}/{{ $totalPeserta }} peserta</small>
                                    </div>
                                    <div>
                                        @if($group['sesi']->status == 'in_progress')
                                            <a href="{{ route('penguji.ruangan', $ruang->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-arrow-right mr-1"></i>Masuk
                                            </a>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-lock mr-1"></i>Menunggu
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>Belum Ada Penugasan</h5>
                <p class="text-muted mb-0">
                    Anda belum ditugaskan sebagai penguji di sesi ujian manapun.
                    Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
                </p>
            </div>
        </div>
    @endif

    <!-- Quick Guide -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Panduan Penilaian</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-list-ol mr-2 text-primary"></i>Langkah Penilaian:</h6>
                    <ol>
                        <li>Klik tombol <strong>"Masuk"</strong> pada ruangan yang ditugaskan</li>
                        <li>Pilih peserta yang akan dinilai</li>
                        <li>Input nilai untuk setiap komponen penilaian</li>
                        <li>Klik <strong>"Simpan Draft"</strong> untuk menyimpan sementara</li>
                        <li>Klik <strong>"Submit"</strong> jika nilai sudah final</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-star mr-2 text-warning"></i>Komponen Penilaian:</h6>
                    <ul>
                        <li><strong>Minat (TBQ)</strong> - Penilaian minat terhadap program</li>
                        <li><strong>Baca Al-Qur'an</strong> - Kemampuan membaca Al-Qur'an</li>
                        <li><strong>Tulis Al-Qur'an</strong> - Kemampuan menulis huruf Arab</li>
                        <li><strong>Hafalan</strong> - Penilaian hafalan Al-Qur'an</li>
                    </ul>
                    <p class="text-muted mb-0">
                        <small>Nilai menggunakan skala 0-100</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
