@extends('adminlte::page')

@section('title', 'Nilai CBT')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-laptop mr-2"></i>Nilai CBT
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Nilai CBT</li>
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
            <div class="card-tools">
                <a href="{{ route('admin.nilai-cbt.upload') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-upload mr-1"></i> Upload Nilai CBT
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-cbt.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control" onchange="this.form.submit()">
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
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
                    <h3>{{ $data->count() }}</h3>
                    <p>Total Peserta CBT</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->avg('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Keseluruhan</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->max('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Tertinggi</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->min('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Terendah</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data Nilai CBT</h3>
        </div>
        <div class="card-body">
            @if($data->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada data nilai CBT. <a href="{{ route('admin.nilai-cbt.upload') }}">Upload sekarang</a></p>
                </div>
            @else
                <table id="cbtTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>NISN</th>
                            <th>No. Tes</th>
                            <th>Nama Peserta</th>
                            <th class="text-center">MTK</th>
                            <th class="text-center">IPA</th>
                            <th class="text-center">IPS</th>
                            <th class="text-center">B. Inggris</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Rata-rata</th>
                            <th class="text-center" width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $cbt)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><code>{{ $cbt->calonSiswa->nisn ?? '-' }}</code></td>
                                <td>{{ $cbt->calonSiswa->nomor_tes ?? '-' }}</td>
                                <td>
                                    <strong>{{ $cbt->calonSiswa->nama_lengkap ?? '-' }}</strong>
                                    @if($cbt->calonSiswa->jenis_kelamin == 'L')
                                        <i class="fas fa-mars text-primary"></i>
                                    @else
                                        <i class="fas fa-venus text-danger"></i>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_mtk ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_ipa ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_ips ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_bahasa_inggris ?? '-' }}</td>
                                <td class="text-center">{{ number_format($cbt->total_nilai ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary" style="font-size: 1rem;">
                                        {{ number_format($cbt->rata_rata ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.nilai-cbt.destroy', $cbt) }}" method="POST"
                                          onsubmit="return confirm('Hapus data CBT ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
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
    @if($data->isNotEmpty())
    $('#cbtTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                title: 'Nilai CBT PPDB',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                className: 'btn btn-info btn-sm',
                title: 'Nilai CBT PPDB',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            }
        ],
        order: [[9, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        }
    });
    @endif
});
</script>
@stop
