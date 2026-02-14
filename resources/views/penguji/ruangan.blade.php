@extends('adminlte::page')

@section('title', 'Ruangan ' . $ruangUjian->nama_ruang)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
    .peserta-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #dee2e6;
        flex-shrink: 0;
    }
    .peserta-card.dinilai .peserta-avatar { border-color: #28a745; }
    .peserta-card.draft .peserta-avatar { border-color: #ffc107; }
    .peserta-card.sedang-diuji .peserta-avatar { border-color: #007bff; }
    .peserta-card.belum .peserta-avatar { border-color: #dc3545; }
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

    <!-- Peserta Susulan -->
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Tambah Peserta Susulan</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">Cari pendaftar berdasarkan nama, NISN, no. pendaftaran, atau nomor tes.</p>
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" id="searchSusulan" class="form-control" placeholder="Ketik minimal 2 karakter...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" id="btnSearchSusulan">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="hasilCariSusulan" class="mt-3" style="display:none;">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Nomor Tes</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody id="listSusulan"></tbody>
                </table>
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
                        <a href="{{ route('penguji.input-nilai', [$ruangUjian->id, $item['peserta']->id]) }}" class="text-decoration-none">
                            <div class="card peserta-card {{ $statusClass }}" id="peserta-card-{{ $item['peserta']->id }}">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        {{-- Avatar --}}
                                        @php
                                            $pasFoto = $item['calon_siswa']?->dokumen?->where('jenis_dokumen', 'foto')->first();
                                            $fotoSrc = null;
                                            if($pasFoto && $pasFoto->file_path && file_exists(public_path('storage/' . $pasFoto->file_path))) {
                                                $fotoSrc = asset('storage/' . $pasFoto->file_path);
                                            }
                                            if(!$fotoSrc) {
                                                $initials = collect(explode(' ', $item['calon_siswa']->nama_lengkap ?? '-'))->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
                                                $bgColor = ($item['calon_siswa']->jenis_kelamin ?? 'L') == 'L' ? '3498db' : 'e74c3c';
                                                $fotoSrc = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=50&background=' . $bgColor . '&color=ffffff&bold=true';
                                            }
                                        @endphp
                                        <img src="{{ $fotoSrc }}" class="peserta-avatar mr-3" alt="Foto">
                                        
                                        {{-- Info --}}
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="mb-1">
                                                <span class="badge badge-secondary">No. {{ $item['peserta']->nomor_urut }}</span>
                                                @if($item['calon_siswa']->nomor_tes)
                                                    <span class="badge badge-warning">{{ $item['calon_siswa']->nomor_tes }}</span>
                                                @endif
                                                @if($pesertaStatus == 'in_progress')
                                                    <span class="badge badge-primary"><i class="fas fa-volume-up mr-1"></i>Diuji</span>
                                                @elseif($pesertaStatus == 'completed')
                                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Selesai</span>
                                                @endif
                                            </div>
                                            <h6 class="mb-0 text-truncate" style="font-weight:600;">{{ $item['calon_siswa']->nama_lengkap ?? '-' }}</h6>
                                            <small class="text-muted">{{ $item['calon_siswa']->no_pendaftaran ?? '-' }}</small>
                                        </div>
                                        
                                        {{-- Status Icon --}}
                                        <div class="text-right ml-2">
                                            @if($nilaiStatus == 'submitted' || $nilaiStatus == 'verified')
                                                <i class="fas fa-check-circle text-success status-icon"></i>
                                                @if($item['nilai'])
                                                    <div class="mt-1">
                                                        <span class="badge badge-primary">{{ number_format($item['nilai']->total_nilai, 2) }}</span>
                                                    </div>
                                                @endif
                                            @elseif($nilaiStatus == 'draft')
                                                <i class="fas fa-edit text-warning status-icon"></i>
                                                <div class="mt-1">
                                                    <span class="badge badge-warning">Draft</span>
                                                </div>
                                            @else
                                                <i class="fas fa-minus-circle text-danger status-icon"></i>
                                                <div class="mt-1">
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
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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

    // ========== Peserta Susulan ==========
    var searchTimer;
    
    function cariPesertaSusulan() {
        var q = $('#searchSusulan').val().trim();
        if (q.length < 2) {
            $('#hasilCariSusulan').hide();
            return;
        }

        $.get('{{ route("penguji.cari-peserta", $ruangUjian->id) }}', { q: q }, function(data) {
            var tbody = $('#listSusulan');
            tbody.empty();

            if (data.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center text-muted">Tidak ditemukan</td></tr>');
            } else {
                data.forEach(function(item) {
                    tbody.append(
                        '<tr>' +
                            '<td>' + (item.nomor_tes || '-') + '</td>' +
                            '<td>' + item.nama_lengkap + '</td>' +
                            '<td>' + (item.nisn || '-') + '</td>' +
                            '<td class="text-center">' +
                                '<form method="POST" action="{{ route("penguji.tambah-peserta", $ruangUjian->id) }}" class="d-inline">' +
                                    '@csrf' +
                                    '<input type="hidden" name="calon_siswa_id" value="' + item.id + '">' +
                                    '<button type="submit" class="btn btn-xs btn-success" onclick="return confirm(\'Tambahkan ' + item.nama_lengkap + '?\')">' +
                                        '<i class="fas fa-plus"></i> Tambah' +
                                    '</button>' +
                                '</form>' +
                            '</td>' +
                        '</tr>'
                    );
                });
            }

            $('#hasilCariSusulan').show();
        });
    }

    $('#searchSusulan').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(cariPesertaSusulan, 400);
    });

    $('#btnSearchSusulan').on('click', cariPesertaSusulan);
});
</script>
@stop
