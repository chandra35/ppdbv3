@extends('adminlte::page')

@section('title', 'Rekap Nilai Seleksi')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
    .nilai-cell {
        font-weight: bold;
    }
    .rank-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .rank-1 { background: gold; color: #000; }
    .rank-2 { background: silver; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-trophy mr-2"></i>Rekap Nilai Seleksi
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai Seleksi</a></li>
            <li class="breadcrumb-item active">Rekap</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-seleksi.rekap') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control select2">
                                <option value="">-- Semua --</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id') == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Jalur Pendaftaran</label>
                            <select name="jalur_id" class="form-control select2">
                                <option value="">-- Semua --</option>
                                @foreach($jalurs as $jalur)
                                    <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                        {{ $jalur->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status Nilai</label>
                            <select name="status" class="form-control">
                                <option value="">-- Semua --</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('admin.nilai-seleksi.rekap') }}" class="btn btn-secondary">
                                    <i class="fas fa-sync mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $rekapData->count() }}</h3>
                    <p>Total Peserta</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->avg('total_nilai') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Nilai</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->max('total_nilai') ?? 0, 2) : '0.00' }}</h3>
                    <p>Nilai Tertinggi</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->min('total_nilai') ?? 0, 2) : '0.00' }}</h3>
                    <p>Nilai Terendah</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data Rekap Nilai</h3>
        </div>
        <div class="card-body">
            <table id="rekapTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center" width="60">Rank</th>
                        <th>No. Pendaftaran</th>
                        <th>Nama Peserta</th>
                        <th>Jalur</th>
                        <th class="text-center">Wawancara</th>
                        <th class="text-center">Baca</th>
                        <th class="text-center">Tulis</th>
                        <th class="text-center">Hafalan</th>
                        <th class="text-center">Juz</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapData as $index => $nilai)
                        <tr>
                            <td class="text-center">
                                @if($index < 3)
                                    <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td>{{ $nilai->calonSiswa->no_pendaftaran ?? '-' }}</td>
                            <td>
                                <strong>{{ $nilai->calonSiswa->nama_lengkap ?? '-' }}</strong>
                                @if($nilai->calonSiswa->jenis_kelamin == 'L')
                                    <i class="fas fa-mars text-primary"></i>
                                @else
                                    <i class="fas fa-venus text-danger"></i>
                                @endif
                            </td>
                            <td>{{ $nilai->sesiUjian->jalurPendaftaran->nama ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_wawancara ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_baca_quran ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_tulis_quran ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_hafalan ?? '-' }}</td>
                            <td class="text-center">{{ $nilai->jumlah_juz_hafalan ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-primary" style="font-size: 1rem;">
                                    {{ number_format($nilai->total_nilai, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($nilai->status == 'verified')
                                    <span class="badge badge-success">Verified</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($nilai->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Initialize DataTable
    $('#rekapTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                title: 'Rekap Nilai Seleksi PPDB',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                className: 'btn btn-info btn-sm',
                title: 'Rekap Nilai Seleksi PPDB'
            }
        ],
        order: [[9, 'desc']], // Sort by total nilai descending
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        }
    });
});
</script>
@stop
