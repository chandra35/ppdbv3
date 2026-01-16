@extends('layouts.admin')

@section('title', 'Ruangan ' . $ruangUjian->nama_ruang)

@push('css')
<style>
    .peserta-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .peserta-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
    }
    .peserta-card.dinilai {
        border-left: 4px solid #28a745;
    }
    .peserta-card.belum {
        border-left: 4px solid #dc3545;
    }
    .peserta-card.draft {
        border-left: 4px solid #ffc107;
    }
    .status-icon {
        font-size: 1.5rem;
    }
    .progress-ring {
        width: 120px;
        height: 120px;
    }
</style>
@endpush

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-door-open mr-2"></i>{{ $ruangUjian->nama_ruang }}
        </h1>
        <small class="text-muted">{{ $sesiUjian->nama_sesi }}</small>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('penguji.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $ruangUjian->nama_ruang }}</li>
        </ol>
    </div>
</div>
@endsection

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

    <!-- Progress Card -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="mb-3">Progress Penilaian</h5>
                    <div class="position-relative d-inline-block mb-3">
                        <canvas id="progressChart" width="120" height="120"></canvas>
                        <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <h3 class="mb-0">{{ round(($sudahSubmit / max($totalPeserta, 1)) * 100) }}%</h3>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-success mb-0">{{ $sudahSubmit }}</h4>
                            <small class="text-muted">Submit</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning mb-0">{{ $sudahDinilai - $sudahSubmit }}</h4>
                            <small class="text-muted">Draft</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger mb-0">{{ $totalPeserta - $sudahDinilai }}</h4>
                            <small class="text-muted">Belum</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Sesi</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal</strong></td>
                                    <td>: {{ $sesiUjian->tanggal_ujian?->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>: {{ $sesiUjian->waktu_mulai }} - {{ $sesiUjian->waktu_selesai }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Jalur</strong></td>
                                    <td>: {{ $sesiUjian->jalurPendaftaran->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gelombang</strong></td>
                                    <td>: {{ $sesiUjian->gelombangPendaftaran->nama ?? 'Semua' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Peserta -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users mr-2"></i>Daftar Peserta ({{ $totalPeserta }})
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($pesertaList as $item)
                    @php
                        $statusClass = $item['status'] == 'belum' ? 'belum' : ($item['status'] == 'draft' ? 'draft' : 'dinilai');
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('penguji.input-nilai', [$ruangUjian->id, $item['peserta']->id]) }}" class="text-decoration-none">
                            <div class="card peserta-card {{ $statusClass }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge badge-secondary mb-2">No. {{ $item['peserta']->nomor_urut }}</span>
                                            <h6 class="mb-1">{{ $item['calon_siswa']->nama_lengkap ?? '-' }}</h6>
                                            <small class="text-muted">{{ $item['calon_siswa']->no_pendaftaran ?? '-' }}</small>
                                        </div>
                                        <div class="text-right">
                                            @if($item['status'] == 'submitted' || $item['status'] == 'verified')
                                                <i class="fas fa-check-circle text-success status-icon"></i>
                                                @if($item['nilai'])
                                                    <div class="mt-2">
                                                        <span class="badge badge-primary">{{ number_format($item['nilai']->total_nilai, 2) }}</span>
                                                    </div>
                                                @endif
                                            @elseif($item['status'] == 'draft')
                                                <i class="fas fa-edit text-warning status-icon"></i>
                                                <div class="mt-2">
                                                    <span class="badge badge-warning">Draft</span>
                                                </div>
                                            @else
                                                <i class="fas fa-minus-circle text-danger status-icon"></i>
                                                <div class="mt-2">
                                                    <span class="badge badge-danger">Belum</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada peserta di ruangan ini</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('penguji.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Progress Chart
    var ctx = document.getElementById('progressChart').getContext('2d');
    var submitted = {{ $sudahSubmit }};
    var draft = {{ $sudahDinilai - $sudahSubmit }};
    var belum = {{ $totalPeserta - $sudahDinilai }};
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [submitted, draft, belum],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
