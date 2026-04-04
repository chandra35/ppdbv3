@extends('adminlte::page')

@section('title', 'Preview Upload Nilai CBT - {{ $mapelLabel }}')

@section('css')
<style>
    .cell-valid { background-color: #d4edda !important; }
    .cell-warning { background-color: #fff3cd !important; }
    .cell-invalid { background-color: #f8d7da !important; }
    .cell-extracted { background-color: #cce5ff !important; }
    .cell-empty { background-color: #f0f0f0 !important; color: #999; }
    .preview-table { font-size: 0.85rem; }
    .preview-table td, .preview-table th { padding: 0.4rem 0.5rem; white-space: nowrap; }
    .filter-btn.active { font-weight: bold; box-shadow: 0 0 0 2px rgba(0,123,255,0.5); }
    .progress-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .progress-overlay.show { display: flex; }
    .progress-box {
        background: #fff;
        border-radius: 12px;
        padding: 2rem 3rem;
        min-width: 400px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .progress-box .progress {
        height: 24px;
        border-radius: 12px;
    }
    .progress-box .progress-bar {
        transition: width 0.3s ease;
        font-size: 0.85rem;
        font-weight: bold;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-search mr-2"></i>Preview Upload <span class="text-primary">{{ $mapelLabel }}</span>
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-cbt.index') }}">Nilai CBT</a></li>
            <li class="breadcrumb-item active">Preview {{ $mapelLabel }}</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Mapel Indicator --}}
    <div class="callout callout-primary">
        <h5 class="mb-0">
            <i class="fas fa-book mr-1"></i> Mata Pelajaran: <strong>{{ $mapelLabel }}</strong>
            &nbsp;|&nbsp; File: <code>{{ $originalName }}</code>
        </h5>
    </div>

    {{-- Summary --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $preview['summary']['total'] }}</h3>
                    <p>Total Baris</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $preview['summary']['valid'] }}</h3>
                    <p>Valid</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $preview['summary']['warning'] }}</h3>
                    <p>Warning</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $preview['summary']['error'] + $preview['summary']['skip'] }}</h3>
                    <p>Error / Skip</p>
                </div>
                <div class="icon"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if(!empty($preview['errors']))
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-circle mr-1"></i> Error</h5>
            <ul class="mb-0">
                @foreach($preview['errors'] as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Legend --}}
    <div class="card card-outline card-info mb-3">
        <div class="card-body py-2">
            <small>
                <strong>Legenda:</strong>
                <span class="badge" style="background:#d4edda;color:#155724;">Valid</span>
                <span class="badge" style="background:#cce5ff;color:#004085;">Ekstrak Angka</span>
                <span class="badge" style="background:#fff3cd;color:#856404;">Warning</span>
                <span class="badge" style="background:#f8d7da;color:#721c24;">Error</span>
                <span class="badge" style="background:#f0f0f0;color:#999;">Kosong/Skip</span>
            </small>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary filter-btn active" data-filter="all">
                Semua ({{ $preview['summary']['total'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="valid">
                Valid ({{ $preview['summary']['valid'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="warning">
                Warning ({{ $preview['summary']['warning'] }})
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="error">
                Error ({{ $preview['summary']['error'] + $preview['summary']['skip'] }})
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Preview Data {{ $mapelLabel }} ({{ $preview['summary']['total'] }} baris)</h3>
        </div>
        <div class="card-body p-0" style="overflow-x: auto;">
            <table class="table table-bordered table-sm preview-table mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="text-center" width="60">Baris</th>
                        <th>Nama (Excel)</th>
                        <th>NISN</th>
                        <th>No. Tes</th>
                        <th>Nama (Database)</th>
                        <th class="text-center" width="120">Nilai {{ $mapelLabel }}</th>
                        <th class="text-center" width="80">Aksi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview['rows'] as $row)
                        @php
                            $cellClass = match($row['cell_type'] ?? 'valid') {
                                'valid' => 'cell-valid',
                                'warning' => 'cell-warning',
                                'extracted' => 'cell-extracted',
                                'invalid' => 'cell-invalid',
                                'empty' => 'cell-empty',
                                default => '',
                            };
                            $filterStatus = in_array($row['status'], ['error', 'skip']) ? 'error' : $row['status'];
                        @endphp
                        <tr class="preview-row" data-status="{{ $filterStatus }}">
                            <td class="text-center">{{ $row['baris'] }}</td>
                            <td>{{ $row['nama_excel'] ?? '-' }}</td>
                            <td><code>{{ $row['nisn'] ?: '-' }}</code></td>
                            <td>{{ $row['nomor_tes'] ?? '-' }}</td>
                            <td>{{ $row['nama_lengkap'] ?: '-' }}</td>
                            <td class="text-center font-weight-bold {{ $cellClass }}">
                                @if(($row['cell_type'] ?? '') === 'empty')
                                    <span class="text-muted">-</span>
                                @elseif(($row['cell_type'] ?? '') === 'extracted')
                                    <i class="fas fa-magic text-primary" title="Diekstrak dari: {{ $row['nilai_raw'] }}"></i>
                                    {{ $row['nilai_parsed'] }}
                                @elseif(($row['cell_type'] ?? '') === 'warning')
                                    <i class="fas fa-exclamation-triangle text-warning" title="Nilai asli: {{ $row['nilai_raw'] }}"></i>
                                    {{ $row['nilai_parsed'] }}
                                @elseif(($row['cell_type'] ?? '') === 'invalid')
                                    <i class="fas fa-times text-danger"></i>
                                    <small>{{ $row['nilai_raw'] }}</small>
                                @else
                                    {{ $row['nilai_parsed'] ?? '-' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['status'] === 'error' || $row['status'] === 'skip')
                                    <span class="badge badge-danger">Skip</span>
                                @elseif($row['action'] === 'baru')
                                    <span class="badge badge-success">Baru</span>
                                @else
                                    <span class="badge badge-warning">Update</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($row['issues']))
                                    @foreach($row['issues'] as $issue)
                                        <small class="d-block text-{{ in_array($row['status'], ['error', 'skip']) ? 'danger' : 'warning' }}">
                                            <i class="fas fa-info-circle"></i> {{ $issue }}
                                        </small>
                                    @endforeach
                                @else
                                    <small class="text-success"><i class="fas fa-check"></i> OK</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @php $importable = $preview['summary']['valid'] + $preview['summary']['warning']; @endphp
                    <form action="{{ route('admin.nilai-cbt.upload.confirm') }}" method="POST" id="confirmForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="extension" value="{{ $extension }}">
                        <input type="hidden" name="mapel" value="{{ $mapel }}">
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnContext['tahun_pelajaran_id'] }}">
                        <input type="hidden" name="jalur_id" value="{{ $returnContext['jalur_id'] }}">
                        <input type="hidden" name="gelombang_id" value="{{ $returnContext['gelombang_id'] }}">
                        <button type="submit" class="btn btn-success btn-lg" id="btnConfirm"
                            {{ $importable == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle mr-1"></i>
                            Konfirmasi & Import {{ $mapelLabel }} ({{ $importable }} data)
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <form action="{{ route('admin.nilai-cbt.upload.cancel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="extension" value="{{ $extension }}">
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnContext['tahun_pelajaran_id'] }}">
                        <input type="hidden" name="jalur_id" value="{{ $returnContext['jalur_id'] }}">
                        <input type="hidden" name="gelombang_id" value="{{ $returnContext['gelombang_id'] }}">
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-times-circle mr-1"></i> Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Progress Overlay --}}
<div class="progress-overlay" id="progressOverlay">
    <div class="progress-box">
        <h4 class="mb-3"><i class="fas fa-cog fa-spin mr-2"></i>Mengimport Data...</h4>
        <p class="text-muted mb-3">Mengimport nilai <strong>{{ $mapelLabel }}</strong> — {{ $importable }} data</p>
        <div class="progress mb-2">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressBar"
                 role="progressbar" style="width: 0%">0%</div>
        </div>
        <small class="text-muted" id="progressText">Mempersiapkan import...</small>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Filter buttons
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        var filter = $(this).data('filter');
        if (filter === 'all') {
            $('.preview-row').show();
        } else {
            $('.preview-row').hide();
            $('.preview-row[data-status="' + filter + '"]').show();
        }
    });

    // Loading + progress on confirm
    $('#confirmForm').on('submit', function() {
        $('#btnConfirm').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengimport...');
        $('#progressOverlay').addClass('show');

        // Simulate progress (since import is synchronous server-side)
        var total = {{ $importable }};
        var progress = 0;
        var steps = [
            { pct: 10, text: 'Membaca file Excel...' },
            { pct: 25, text: 'Memvalidasi NISN...' },
            { pct: 45, text: 'Memproses data (' + Math.round(total * 0.4) + '/' + total + ')...' },
            { pct: 65, text: 'Memproses data (' + Math.round(total * 0.65) + '/' + total + ')...' },
            { pct: 80, text: 'Menyimpan ke database...' },
            { pct: 92, text: 'Menghitung total & rata-rata...' },
            { pct: 98, text: 'Finalisasi...' },
        ];

        var i = 0;
        var interval = setInterval(function() {
            if (i < steps.length) {
                $('#progressBar').css('width', steps[i].pct + '%').text(steps[i].pct + '%');
                $('#progressText').text(steps[i].text);
                i++;
            } else {
                clearInterval(interval);
            }
        }, 800);
    });
});
</script>
@stop
