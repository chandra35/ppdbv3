@extends('adminlte::page')

@section('title', 'Preview Upload Nilai CBT')

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
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-search mr-2"></i>Preview Upload Nilai CBT
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-cbt.index') }}">Nilai CBT</a></li>
            <li class="breadcrumb-item active">Preview</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
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
                    <h3>{{ $preview['summary']['error'] }}</h3>
                    <p>Error</p>
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
                <span class="badge" style="background:#f8d7da;color:#721c24;">Error/Invalid</span>
                <span class="badge" style="background:#f0f0f0;color:#999;">Kosong</span>
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
                Error ({{ $preview['summary']['error'] }})
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Preview Data ({{ $preview['summary']['total'] }} baris)</h3>
        </div>
        <div class="card-body p-0" style="overflow-x: auto;">
            <table class="table table-bordered table-sm preview-table mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="text-center">Baris</th>
                        <th>NISN</th>
                        <th>No. Tes</th>
                        <th>Nama</th>
                        @foreach($preview['field_map'] as $fm)
                            <th class="text-center">{{ $fm['label'] }}</th>
                        @endforeach
                        <th class="text-center">Aksi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview['rows'] as $row)
                        <tr class="preview-row" data-status="{{ $row['status'] }}">
                            <td class="text-center">{{ $row['baris'] }}</td>
                            <td><code>{{ $row['nisn'] ?: '-' }}</code></td>
                            <td>{{ $row['nomor_tes'] ?? '-' }}</td>
                            <td>{{ $row['nama_lengkap'] ?: '-' }}</td>
                            @foreach($row['nilai_raw'] as $nr)
                                @php
                                    $cellClass = match($nr['type']) {
                                        'valid' => 'cell-valid',
                                        'warning' => 'cell-warning',
                                        'extracted' => 'cell-extracted',
                                        'invalid' => 'cell-invalid',
                                        'empty' => 'cell-empty',
                                        default => '',
                                    };
                                @endphp
                                <td class="text-center {{ $cellClass }}">
                                    @if($nr['type'] === 'empty')
                                        <span class="text-muted">-</span>
                                    @elseif($nr['type'] === 'extracted')
                                        <i class="fas fa-magic text-primary" title="Diekstrak dari: {{ $nr['raw'] }}"></i>
                                        {{ $nr['parsed'] }}
                                    @elseif($nr['type'] === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning" title="Nilai asli: {{ $nr['raw'] }}"></i>
                                        {{ $nr['parsed'] }}
                                    @elseif($nr['type'] === 'invalid')
                                        <i class="fas fa-times text-danger"></i>
                                        <small>{{ $nr['raw'] }}</small>
                                    @else
                                        {{ $nr['parsed'] }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                @if($row['status'] === 'error')
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
                                        <small class="d-block text-{{ $row['status'] === 'error' ? 'danger' : 'warning' }}">
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
                            <td colspan="10" class="text-center text-muted py-3">Tidak ada data</td>
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
                    <form action="{{ route('admin.nilai-cbt.upload.confirm') }}" method="POST" id="confirmForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="extension" value="{{ $extension }}">
                        <button type="submit" class="btn btn-success btn-lg" id="btnConfirm"
                            {{ $preview['summary']['total'] == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle mr-1"></i>
                            Konfirmasi & Import ({{ $preview['summary']['valid'] + $preview['summary']['warning'] }} data)
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <form action="{{ route('admin.nilai-cbt.upload.cancel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="extension" value="{{ $extension }}">
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-times-circle mr-1"></i> Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
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

    // Loading on confirm
    $('#confirmForm').on('submit', function() {
        $('#btnConfirm').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengimport data...');
    });
});
</script>
@stop
