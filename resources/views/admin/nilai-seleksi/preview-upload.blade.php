@extends('adminlte::page')

@section('title', 'Preview Import Nilai')

@section('css')
<style>
    .preview-summary .stat-card {
        padding: 12px 15px;
        border-radius: 8px;
        text-align: center;
    }
    .preview-summary .stat-card .number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .preview-summary .stat-card .label {
        font-size: 0.85rem;
    }
    .table-preview th {
        white-space: nowrap;
        font-size: 0.8rem;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 10;
    }
    .table-preview td {
        font-size: 0.85rem;
        vertical-align: middle !important;
    }
    .cell-valid {
        background-color: #d4edda !important;
    }
    .cell-invalid {
        background-color: #f8d7da !important;
        color: #721c24;
        font-weight: bold;
    }
    .cell-warning {
        background-color: #fff3cd !important;
        color: #856404;
    }
    .cell-empty {
        color: #ccc;
    }
    .cell-extracted {
        background-color: #dbeafe !important;
        color: #1e40af;
    }
    .cell-smart {
        background-color: #dcfce7 !important;
        color: #166534;
        font-weight: bold;
    }
    .cell-skip {
        color: #999;
        font-style: italic;
    }
    .row-error {
        background-color: #fef2f2 !important;
    }
    .row-warning {
        background-color: #fffbeb !important;
    }
    .row-skip {
        background-color: #f9fafb !important;
        opacity: 0.7;
    }
    .issues-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .issues-list li {
        font-size: 0.78rem;
        padding: 1px 0;
    }
    .issues-list li i {
        width: 14px;
    }
    .badge-action {
        font-size: 0.7rem;
    }
    .preview-scroll {
        max-height: 65vh;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .sheet-separator {
        background: #e2e8f0 !important;
        font-weight: bold;
    }
    .sheet-separator td {
        padding: 6px 12px !important;
        font-size: 0.85rem;
    }
    .filter-tabs .btn {
        font-size: 0.82rem;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-search mr-2"></i>Preview Import Nilai
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.upload') }}">Upload Nilai TBQ</a></li>
            <li class="breadcrumb-item active">Preview</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Info Jadwal & File --}}
    <div class="callout callout-info py-2 px-3 mb-3">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">Jadwal Ujian</small><br>
                <strong>{{ $jadwal->tanggal_ujian->isoFormat('D MMMM Y') }}</strong>
                — {{ $jadwal->jalurPendaftaran->nama ?? 'Semua Jalur' }}
                @if($jadwal->gelombangPendaftaran)
                    ({{ $jadwal->gelombangPendaftaran->nama }})
                @endif
            </div>
            <div class="col-md-6">
                <small class="text-muted">File Excel</small><br>
                <strong><i class="fas fa-file-excel text-success mr-1"></i>{{ $originalFileName }}</strong>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row preview-summary mb-3">
        <div class="col-lg-3 col-6">
            <div class="stat-card bg-white border shadow-sm">
                <div class="number text-primary">{{ $preview['summary']['total'] }}</div>
                <div class="label text-muted">Total Baris Data</div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stat-card bg-white border shadow-sm">
                <div class="number text-success">{{ $preview['summary']['valid'] }}</div>
                <div class="label text-muted">
                    <i class="fas fa-check-circle text-success"></i> Siap Import
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stat-card bg-white border shadow-sm">
                <div class="number text-warning">{{ $preview['summary']['warning'] }}</div>
                <div class="label text-muted">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Ada Masalah (tetap import)
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stat-card bg-white border shadow-sm">
                <div class="number text-danger">{{ $preview['summary']['error'] + $preview['summary']['skip'] }}</div>
                <div class="label text-muted">
                    <i class="fas fa-times-circle text-danger"></i> Tidak Bisa Import
                </div>
            </div>
        </div>
    </div>

    {{-- Global Errors --}}
    @if(!empty($preview['errors']))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-exclamation-circle mr-2"></i><strong>Error pada proses parsing:</strong>
        <ul class="mb-0 mt-1">
            @foreach($preview['errors'] as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="mb-2 filter-tabs">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary active" data-filter="all">
                Semua <span class="badge badge-primary">{{ $preview['summary']['total'] }}</span>
            </button>
            <button type="button" class="btn btn-outline-success" data-filter="valid">
                <i class="fas fa-check"></i> Valid <span class="badge badge-success">{{ $preview['summary']['valid'] }}</span>
            </button>
            <button type="button" class="btn btn-outline-warning" data-filter="warning">
                <i class="fas fa-exclamation-triangle"></i> Warning <span class="badge badge-warning">{{ $preview['summary']['warning'] }}</span>
            </button>
            <button type="button" class="btn btn-outline-danger" data-filter="error">
                <i class="fas fa-times"></i> Error <span class="badge badge-danger">{{ $preview['summary']['error'] + $preview['summary']['skip'] }}</span>
            </button>
        </div>
    </div>

    {{-- Preview Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="preview-scroll">
                <table class="table table-bordered table-hover table-preview mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="text-center" style="width: 30px;">#</th>
                            <th style="width: 140px;">Sheet/Ruang</th>
                            <th style="width: 80px;">No. Tes</th>
                            <th style="width: 180px;">Nama Peserta</th>
                            @foreach($preview['komponen_labels'] as $kl)
                                @if($kl['type'] !== 'skip')
                                <th class="text-center" style="min-width: 65px;">{{ $kl['label'] }}</th>
                                @endif
                            @endforeach
                            <th class="text-center" style="width: 70px;">Aksi</th>
                            <th style="width: 60px;">Status</th>
                            <th style="min-width: 200px;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentSheet = ''; $rowNum = 0; @endphp
                        @foreach($preview['rows'] as $row)
                            @if($row['sheet'] !== $currentSheet)
                                @php $currentSheet = $row['sheet']; @endphp
                                <tr class="sheet-separator" data-filter-status="all">
                                    <td colspan="{{ 6 + collect($preview['komponen_labels'])->where('type', '!=', 'skip')->count() }}">
                                        <i class="fas fa-table mr-1"></i>
                                        Sheet: <strong>{{ $currentSheet }}</strong>
                                        — Ruang: {{ $row['ruang'] }} (Sesi {{ $row['sesi'] }})
                                    </td>
                                </tr>
                            @endif
                            @php $rowNum++; @endphp
                            <tr class="preview-row {{ $row['status'] === 'error' ? 'row-error' : ($row['status'] === 'warning' ? 'row-warning' : ($row['status'] === 'skip' ? 'row-skip' : '')) }}"
                                data-filter-status="{{ $row['status'] === 'skip' ? 'error' : $row['status'] }}">
                                <td class="text-center text-muted">{{ $rowNum }}</td>
                                <td>
                                    <small class="text-muted">{{ $row['ruang'] }} S{{ $row['sesi'] }}</small>
                                </td>
                                <td><code>{{ $row['nomor_tes'] ?: '-' }}</code></td>
                                <td>{{ $row['nama_lengkap'] }}</td>

                                {{-- Nilai Columns --}}
                                @foreach($row['nilai_raw'] as $nv)
                                    @if($nv['type'] === 'skip')
                                        @continue
                                    @endif
                                    <td class="text-center {{ $nv['type'] === 'valid' ? 'cell-valid' : ($nv['type'] === 'invalid' ? 'cell-invalid' : ($nv['type'] === 'warning' ? 'cell-warning' : ($nv['type'] === 'extracted' ? 'cell-extracted' : ($nv['type'] === 'smart' ? 'cell-smart' : ($nv['type'] === 'empty' ? 'cell-empty' : ''))))) }}">
                                        @if($nv['type'] === 'valid')
                                            {{ $nv['parsed'] }}
                                        @elseif($nv['type'] === 'smart')
                                            <div title="{{ $nv['raw'] }} → {{ $nv['smart_info']['jumlah_juz'] }} juz ({{ $nv['smart_info']['detail'] }}) → skor {{ $nv['parsed'] }}">
                                                <small class="d-block text-muted" style="font-size: 0.7rem; line-height: 1.2;">
                                                    <i class="fas fa-quran text-success" style="font-size: 0.6rem;"></i>
                                                    "{{ $nv['raw'] }}"
                                                </small>
                                                <span class="font-weight-bold">→ {{ $nv['parsed'] }}</span>
                                            </div>
                                        @elseif($nv['type'] === 'extracted')
                                            <span title="Asli: '{{ $nv['raw'] }}' → diambil {{ $nv['parsed'] }}">
                                                {{ $nv['parsed'] }}
                                                <i class="fas fa-magic text-primary" style="font-size: 0.7rem;"></i>
                                            </span>
                                        @elseif($nv['type'] === 'invalid')
                                            <span title="Tidak ada angka: {{ $nv['raw'] }}">
                                                {{ $nv['raw'] }}
                                                <i class="fas fa-exclamation-circle text-danger" style="font-size: 0.7rem;"></i>
                                            </span>
                                        @elseif($nv['type'] === 'warning')
                                            <span title="Nilai di luar rentang: {{ $nv['parsed'] }}">
                                                {{ $nv['parsed'] }}
                                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 0.7rem;"></i>
                                            </span>
                                        @elseif($nv['type'] === 'empty')
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Pad empty columns if row has error (no nilai_raw) --}}
                                @for($pad = count($row['nilai_raw']); $pad < collect($preview['komponen_labels'])->where('type', '!=', 'skip')->count(); $pad++)
                                    <td class="text-center cell-empty"><span class="text-muted">-</span></td>
                                @endfor

                                {{-- Action --}}
                                <td class="text-center">
                                    @if($row['action'] === 'baru')
                                        <span class="badge badge-info badge-action">BARU</span>
                                    @elseif($row['action'] === 'update')
                                        <span class="badge badge-warning badge-action">UPDATE</span>
                                    @elseif($row['action'] === 'skip')
                                        <span class="badge badge-secondary badge-action">SKIP</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    @if($row['status'] === 'valid')
                                        <i class="fas fa-check-circle text-success" title="Valid"></i>
                                    @elseif($row['status'] === 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning" title="Warning"></i>
                                    @elseif($row['status'] === 'error')
                                        <i class="fas fa-times-circle text-danger" title="Error"></i>
                                    @elseif($row['status'] === 'skip')
                                        <i class="fas fa-minus-circle text-secondary" title="Dilewati"></i>
                                    @endif
                                </td>

                                {{-- Issues --}}
                                <td>
                                    @if(!empty($row['issues']))
                                        <ul class="issues-list">
                                            @foreach($row['issues'] as $issue)
                                                <li>
                                                    @if($row['status'] === 'error')
                                                        <i class="fas fa-times text-danger"></i>
                                                    @elseif($row['status'] === 'warning')
                                                        <i class="fas fa-exclamation text-warning"></i>
                                                    @else
                                                        <i class="fas fa-info text-info"></i>
                                                    @endif
                                                    {{ $issue }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <small class="text-success"><i class="fas fa-check mr-1"></i>Tidak ada masalah</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if(empty($preview['rows']))
                            <tr>
                                <td colspan="{{ 6 + collect($preview['komponen_labels'])->where('type', '!=', 'skip')->count() }}" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted d-block mb-2"></i>
                                    Tidak ada data yang ditemukan dalam file Excel.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary py-2 px-3">
                <small>
                    <strong>Keterangan Warna Nilai:</strong>&nbsp;
                    <span class="px-2 py-1 cell-valid">Angka valid</span>&nbsp;
                    <span class="px-2 py-1 cell-smart">Smart Hafalan Juz <i class="fas fa-quran" style="font-size: 0.7rem;"></i></span>&nbsp;
                    <span class="px-2 py-1 cell-extracted">Angka diekstrak dari teks <i class="fas fa-magic" style="font-size: 0.7rem;"></i></span>&nbsp;
                    <span class="px-2 py-1 cell-invalid">Tidak ada angka (diabaikan)</span>&nbsp;
                    <span class="px-2 py-1 cell-warning">Di luar rentang 0-100</span>&nbsp;
                    <span class="px-2 py-1 cell-empty text-muted border">Kosong</span>&nbsp;
                    &mdash;
                    Baris <span class="text-danger"><i class="fas fa-times-circle"></i> Error</span> dan
                    <span class="text-secondary"><i class="fas fa-minus-circle"></i> Skip</span> tidak akan diimport.
                    Baris <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Warning</span> tetap diimport (nilai non-angka jadi kosong).
                </small>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <form method="POST" action="{{ route('admin.nilai-seleksi.upload.cancel') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="temp_file" value="{{ $tempFile }}">
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnContext['tahun_pelajaran_id'] }}">
                        <input type="hidden" name="jalur_id" value="{{ $returnContext['jalur_id'] }}">
                        <input type="hidden" name="gelombang_id" value="{{ $returnContext['gelombang_id'] }}">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Batal & Kembali
                        </button>
                    </form>
                </div>
                <div>
                    @if($preview['summary']['valid'] + $preview['summary']['warning'] > 0)
                        <form method="POST" action="{{ route('admin.nilai-seleksi.upload.confirm') }}" id="formConfirm">
                            @csrf
                            <input type="hidden" name="jadwal_id" value="{{ $jadwalId }}">
                            <input type="hidden" name="temp_file" value="{{ $tempFile }}">
                            <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnContext['tahun_pelajaran_id'] }}">
                            <input type="hidden" name="jalur_id" value="{{ $returnContext['jalur_id'] }}">
                            <input type="hidden" name="gelombang_id" value="{{ $returnContext['gelombang_id'] }}">
                            <button type="submit" class="btn btn-success btn-lg" id="btnConfirm">
                                <i class="fas fa-check-circle mr-1"></i>Konfirmasi Import
                                <span class="badge badge-light ml-1">{{ $preview['summary']['valid'] + $preview['summary']['warning'] }} data</span>
                            </button>
                        </form>
                    @else
                        <button class="btn btn-success btn-lg" disabled>
                            <i class="fas fa-check-circle mr-1"></i>Tidak ada data yang bisa diimport
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Filter tabs
    $('.filter-tabs .btn').on('click', function() {
        var filter = $(this).data('filter');
        $('.filter-tabs .btn').removeClass('active');
        $(this).addClass('active');

        if (filter === 'all') {
            $('.preview-row, .sheet-separator').show();
        } else {
            $('.preview-row').each(function() {
                var rowStatus = $(this).data('filter-status');
                $(this).toggle(rowStatus === filter);
            });
            // Show sheet separators only if they have visible rows after them
            $('.sheet-separator').each(function() {
                var $next = $(this).nextUntil('.sheet-separator').filter('.preview-row:visible');
                $(this).toggle($next.length > 0);
            });
        }
    });

    // Confirm button loading state
    $('#formConfirm').on('submit', function() {
        var btn = $('#btnConfirm');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengimport nilai...');
    });
});
</script>
@stop
