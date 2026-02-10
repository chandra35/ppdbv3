@extends('adminlte::page')

@section('title', 'Ruangan ' . $ruangUjian->nama_ruang)

@section('css')
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
    .peserta-card.sedang-diuji {
        border-left: 4px solid #007bff;
        background: #e8f4fd;
        animation: pulse-border 2s ease-in-out infinite;
    }
    @keyframes pulse-border {
        0%, 100% { box-shadow: 0 0 0 0 rgba(0,123,255,0.4); }
        50% { box-shadow: 0 0 0 8px rgba(0,123,255,0); }
    }
    .status-icon {
        font-size: 1.5rem;
    }
    .progress-ring {
        width: 120px;
        height: 120px;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-door-open mr-2"></i>{{ $ruangUjian->nama_ruang }}
        </h1>
        <small class="text-muted">{{ $sesiUjian->nama }}</small>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('penguji.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $ruangUjian->nama_ruang }}</li>
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
                                    <td>: {{ $sesiUjian->tanggal?->format('d F Y') }}</td>
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
                                    <td>: {{ $sesiUjian->jalur->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gelombang</strong></td>
                                    <td>: {{ $sesiUjian->gelombang->nama ?? 'Semua' }}</td>
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
                        $nilaiStatus = $item['status']; // belum, draft, submitted, verified
                        $pesertaStatus = $item['peserta']->status ?? 'waiting'; // waiting, in_progress, completed
                        
                        if ($nilaiStatus == 'submitted' || $nilaiStatus == 'verified') {
                            $statusClass = 'dinilai';
                        } elseif ($pesertaStatus == 'in_progress') {
                            $statusClass = 'sedang-diuji';
                        } elseif ($nilaiStatus == 'draft') {
                            $statusClass = 'draft';
                        } else {
                            $statusClass = 'belum';
                        }
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card peserta-card {{ $statusClass }}" id="peserta-card-{{ $item['peserta']->id }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge badge-secondary mb-2">No. {{ $item['peserta']->nomor_urut }}</span>
                                        @if($pesertaStatus == 'in_progress')
                                            <span class="badge badge-primary mb-2"><i class="fas fa-volume-up mr-1"></i>Sedang Diuji</span>
                                        @elseif($pesertaStatus == 'completed')
                                            <span class="badge badge-success mb-2"><i class="fas fa-check mr-1"></i>Selesai</span>
                                        @endif
                                        <h6 class="mb-1">{{ $item['calon_siswa']->nama_lengkap ?? '-' }}</h6>
                                        <small class="text-muted">{{ $item['calon_siswa']->no_pendaftaran ?? '-' }}</small>
                                    </div>
                                    <div class="text-right">
                                        @if($nilaiStatus == 'submitted' || $nilaiStatus == 'verified')
                                            <i class="fas fa-check-circle text-success status-icon"></i>
                                            @if($item['nilai'])
                                                <div class="mt-2">
                                                    <span class="badge badge-primary">{{ number_format($item['nilai']->total_nilai, 2) }}</span>
                                                </div>
                                            @endif
                                        @elseif($nilaiStatus == 'draft')
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
                                <div class="mt-2 d-flex gap-1">
                                    @if($pesertaStatus == 'waiting' && $nilaiStatus == 'belum')
                                        <button type="button" class="btn btn-sm btn-info btn-panggil mr-1"
                                                data-peserta-id="{{ $item['peserta']->id }}"
                                                data-peserta-nama="{{ $item['calon_siswa']->nama_lengkap ?? '-' }}">
                                            <i class="fas fa-bullhorn mr-1"></i>Panggil
                                        </button>
                                    @endif
                                    <a href="{{ route('penguji.input-nilai', [$ruangUjian->id, $item['peserta']->id]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-pen mr-1"></i>Input Nilai
                                    </a>
                                </div>
                            </div>
                        </div>
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
@stop

@section('js')
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

    // Panggil Peserta button
    $(document).on('click', '.btn-panggil', function(e) {
        e.preventDefault();
        var btn = $(this);
        var pesertaId = btn.data('peserta-id');
        var pesertaNama = btn.data('peserta-nama');

        if (!confirm('Panggil peserta ' + pesertaNama + '?')) return;

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memanggil...');

        $.ajax({
            url: '{{ url("penguji/ruangan") }}/' + '{{ $ruangUjian->id }}' + '/peserta/' + pesertaId + '/panggil',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                // Reload page to reflect new status
                location.reload();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.error || 'Gagal memanggil peserta.';
                alert(msg);
                btn.prop('disabled', false).html('<i class="fas fa-bullhorn mr-1"></i>Panggil');
            }
        });
    });
});
</script>
@stop
