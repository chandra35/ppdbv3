@extends('adminlte::page')

@section('title', 'Upload & Pengolahan Nilai TBQ')

@section('css')
<style>
    .upload-zone {
        border: 2px dashed #007bff;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background: #f8f9ff;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.dragover {
        background: #e8f0fe;
        border-color: #0056b3;
    }
    .upload-zone .icon {
        font-size: 3rem;
        color: #007bff;
    }
    .stat-mini {
        text-align: center;
        padding: 10px;
    }
    .stat-mini .number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .stat-mini .label {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .nilai-cell {
        font-weight: 600;
        font-size: 0.95rem;
    }
    .table-nilai th {
        white-space: nowrap;
        font-size: 0.85rem;
    }
    .filter-bar {
        background: #f4f6f9;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-file-upload mr-2"></i>Upload & Pengolahan Nilai TBQ TBQ
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai TBQ</a></li>
            <li class="breadcrumb-item active">Upload Nilai TBQ</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-check-circle mr-2"></i>{!! session('success') !!}
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-exclamation-triangle mr-2"></i>{!! session('warning') !!}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-times-circle mr-2"></i>{!! session('error') !!}
    </div>
    @endif
    @if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        <i class="fas fa-exclamation-triangle mr-2"></i><strong>Detail Error Import:</strong>
        <ul class="mb-0 mt-1">
            @foreach(array_slice(session('import_errors'), 0, 15) as $err)
                <li>{{ $err }}</li>
            @endforeach
            @if(count(session('import_errors')) > 15)
                <li class="text-muted">...dan {{ count(session('import_errors')) - 15 }} error lainnya.</li>
            @endif
        </ul>
    </div>
    @endif

    {{-- Quick Stats --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Nilai</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['draft'] }}</h3>
                    <p>Draft</p>
                </div>
                <div class="icon"><i class="fas fa-pencil-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['submitted'] }}</h3>
                    <p>Submitted</p>
                </div>
                <div class="icon"><i class="fas fa-paper-plane"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['verified'] }}</h3>
                    <p>Verified</p>
                </div>
                <div class="icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Upload Form --}}
        <div class="col-lg-5">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-upload mr-2"></i>Upload Nilai dari Excel Penilaian</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.nilai-seleksi.upload.process') }}" enctype="multipart/form-data" id="formUploadNilai">
                        @csrf

                        <div class="form-group">
                            <label for="jadwal_id"><strong>Pilih Jadwal Ujian</strong> <span class="text-danger">*</span></label>
                            <select class="form-control" id="jadwal_id" name="jadwal_id" required>
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($jadwalList as $jd)
                                    <option value="{{ $jd->id }}">
                                        {{ $jd->tanggal_ujian->isoFormat('D MMM Y') }}
                                        — {{ $jd->jalurPendaftaran->nama ?? 'Semua Jalur' }}
                                        @if($jd->gelombangPendaftaran) ({{ $jd->gelombangPendaftaran->nama }}) @endif
                                        [{{ $jd->sesi_ujian_count }} sesi]
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Jadwal dari <a href="{{ route('admin.penjadwalan-ujian.list') }}">Penjadwalan Ujian</a></small>
                        </div>

                        <div class="form-group">
                            <label><strong>File Excel Lembar Penilaian</strong> <span class="text-danger">*</span></label>
                            <div class="upload-zone" id="uploadZone">
                                <div class="icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <p class="mt-2 mb-1"><strong>Klik atau drag file ke sini</strong></p>
                                <p class="text-muted mb-0"><small>Format: .xlsx / .xls &bull; Maks: 10MB</small></p>
                            </div>
                            <input type="file" class="d-none" id="file_nilai" name="file_nilai" accept=".xlsx,.xls" required>
                            <div id="fileInfo" class="mt-2 d-none">
                                <div class="alert alert-light border py-2 px-3 mb-0">
                                    <i class="fas fa-file-excel text-success mr-2"></i>
                                    <span id="fileName"></span>
                                    <button type="button" class="close" id="removeFile"><span>&times;</span></button>
                                </div>
                            </div>
                        </div>

                        <div class="callout callout-info py-2 px-3">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                Upload file <strong>Lembar Penilaian Excel</strong> yang sudah diisi nilai penguji.
                                Sistem akan menampilkan <strong>preview data</strong> sebelum import.
                                Data dicocokkan berdasarkan <strong>Nomor Tes</strong> peserta dan nama sheet/ruang.
                                Nilai non-angka (huruf/teks) akan ditandai agar bisa diperiksa.
                            </small>
                        </div>

                        <div class="callout callout-warning py-2 px-3">
                            <small>
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Nilai yang sudah ada (draft) akan di-<em>update</em>. Nilai yang sudah <em>submitted</em> tidak akan berubah.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-3" id="btnUpload" disabled>
                            <i class="fas fa-search mr-1"></i>Preview & Periksa Data
                        </button>
                    </form>
                </div>
            </div>

            {{-- Panduan --}}
            <div class="card card-outline card-secondary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Panduan Upload</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li class="mb-2">Download <strong>Lembar Penilaian (Excel)</strong> dari menu <a href="{{ route('admin.penjadwalan-ujian.index') }}">Penjadwalan Ujian</a> → Detail Jadwal → Export</li>
                        <li class="mb-2">Isi nilai pada kolom yang tersedia di setiap sheet ruangan</li>
                        <li class="mb-2">Pilih <strong>Jadwal Ujian</strong> yang sesuai pada form di atas</li>
                        <li class="mb-2">Upload file Excel yang sudah diisi</li>
                        <li>Sistem akan otomatis mencocokkan berdasarkan <strong>Nomor Tes</strong> dan <strong>Sheet (Ruang)</strong></li>
                    </ol>
                    <hr>
                    <p class="mb-1"><strong>Komponen Nilai Aktif:</strong></p>
                    <ul class="pl-3 mb-0">
                        @foreach($bobotList as $bobot)
                            <li>{{ ucwords(str_replace('_', ' ', $bobot->komponen)) }} — Bobot: <strong>{{ $bobot->bobot }}%</strong></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Daftar Nilai --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-ol mr-2"></i>Daftar Nilai TBQ</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $nilaiList->total() }} data</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Filter Bar --}}
                    <div class="filter-bar">
                        <form method="GET" action="{{ route('admin.nilai-seleksi.upload') }}" class="form-inline">
                            <div class="form-group mr-2 mb-1">
                                <select name="jadwal_id" class="form-control form-control-sm">
                                    <option value="">Semua Jadwal</option>
                                    @foreach($jadwalList as $jd)
                                        <option value="{{ $jd->id }}" {{ request('jadwal_id') == $jd->id ? 'selected' : '' }}>
                                            {{ $jd->tanggal_ujian->isoFormat('D MMM Y') }}
                                            — {{ $jd->jalurPendaftaran->nama ?? 'Semua' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-1">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-1">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/no. tes..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary mb-1">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            @if(request()->hasAny(['jadwal_id', 'status', 'search']))
                                <a href="{{ route('admin.nilai-seleksi.upload') }}" class="btn btn-sm btn-secondary mb-1 ml-1">
                                    <i class="fas fa-times mr-1"></i>Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-nilai mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No. Tes</th>
                                    <th>Nama Peserta</th>
                                    @foreach($bobotList as $bobot)
                                        <th class="text-center">{{ ucwords(str_replace('_', ' ', $bobot->komponen)) }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Status</th>
                                    <th>Penguji</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nilaiList as $i => $nilai)
                                    <tr>
                                        <td>{{ $nilaiList->firstItem() + $i }}</td>
                                        <td><code>{{ $nilai->calonSiswa->nomor_tes ?? '-' }}</code></td>
                                        <td>{{ $nilai->calonSiswa->nama_lengkap ?? '-' }}</td>
                                        @foreach($bobotList as $bobot)
                                            @php
                                                $field = 'nilai_' . $bobot->komponen;
                                                if ($bobot->komponen === 'baca_quran') $field = 'nilai_baca_quran';
                                                $val = $nilai->$field;
                                            @endphp
                                            <td class="text-center nilai-cell">
                                                @if($val !== null)
                                                    {{ number_format($val, 1) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <strong class="nilai-cell {{ $nilai->total_nilai >= 70 ? 'text-success' : 'text-danger' }}">
                                                {{ $nilai->total_nilai ? number_format($nilai->total_nilai, 2) : '-' }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            @if($nilai->status === 'verified')
                                                <span class="badge badge-success">Verified</span>
                                            @elseif($nilai->status === 'submitted')
                                                <span class="badge badge-warning">Submitted</span>
                                            @elseif($nilai->status === 'revision')
                                                <span class="badge badge-danger">Revisi</span>
                                            @else
                                                <span class="badge badge-secondary">Draft</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $nilai->penguji->name ?? '-' }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 5 + $bobotList->count() }}" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                            Belum ada data nilai
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($nilaiList->hasPages())
                        <div class="card-footer clearfix">
                            {{ $nilaiList->links() }}
                        </div>
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
    var uploadZone = $('#uploadZone');
    var fileInput = $('#file_nilai');
    var fileInfo = $('#fileInfo');
    var fileName = $('#fileName');
    var btnUpload = $('#btnUpload');
    var jadwalSelect = $('#jadwal_id');

    function checkReady() {
        btnUpload.prop('disabled', !(fileInput[0].files.length > 0 && jadwalSelect.val()));
    }

    // Click to upload
    uploadZone.on('click', function() {
        fileInput.click();
    });

    // Drag & drop
    uploadZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    }).on('dragleave drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    }).on('drop', function(e) {
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            fileInput.trigger('change');
        }
    });

    // File selected
    fileInput.on('change', function() {
        if (this.files.length > 0) {
            var name = this.files[0].name;
            var sizeMB = (this.files[0].size / 1024 / 1024).toFixed(2);
            fileName.text(name + ' (' + sizeMB + ' MB)');
            fileInfo.removeClass('d-none');
            uploadZone.addClass('d-none');
        }
        checkReady();
    });

    // Remove file
    $('#removeFile').on('click', function() {
        fileInput.val('');
        fileInfo.addClass('d-none');
        uploadZone.removeClass('d-none');
        checkReady();
    });

    // Jadwal change
    jadwalSelect.on('change', checkReady);

    // Form submit - loading state
    $('#formUploadNilai').on('submit', function() {
        btnUpload.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses file...');
    });
});
</script>
@stop
