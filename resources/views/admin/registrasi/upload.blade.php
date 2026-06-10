@extends('adminlte::page')

@section('title', 'Import Registrasi')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-import mr-2"></i>Import Data Registrasi</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.registrasi.index') }}">Registrasi</a></li>
            <li class="breadcrumb-item active">Import</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="fas fa-calendar-alt mr-1"></i>
        <strong>Tahun Pelajaran Aktif:</strong> {{ $tahunAktif->nama ?? 'Tidak ada' }}
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Upload File Excel</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle mr-1"></i> Ketentuan Format</h5>
                        <ul class="mb-0">
                            <li>Format <strong>.xlsx</strong> atau <strong>.xls</strong> (maks 10 MB).</li>
                            <li>Kolom: <strong>A=No</strong>, <strong>B=Notes</strong> (4 digit akhir nomor tes), <strong>C=Nama</strong>, <strong>D=Jurusan</strong>.</li>
                            <li>Sistem mencocokkan otomatis (<em>smart matching</em>) dengan pendaftar yang <strong>LULUS</strong> pada tahun aktif berdasarkan nomor tes, nama, dan jurusan.</li>
                            <li>Hasil akan ditampilkan sebagai <strong>preview</strong> untuk dianalisa sebelum disimpan.</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.registrasi.upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">

                        <div class="form-group">
                            <label for="file">File Excel <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                            @error('file')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="fas fa-search mr-1"></i> Preview & Cocokkan Data
                        </button>
                        <a href="{{ route('admin.registrasi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-lightbulb mr-2"></i>Contoh Format</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0 text-center" style="font-size:.8rem;">
                        <thead class="bg-light"><tr><th>No</th><th>Notes</th><th>Nama</th><th>Jurusan</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td>0001</td><td>Adzriel Razka</td><td>Asrama</td></tr>
                            <tr><td>2</td><td>0002</td><td>Berliana Fairus</td><td>Reguler</td></tr>
                            <tr><td>3</td><td>0003</td><td>Afifah Zahra</td><td>Reguler</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    $('#file').on('change', function () {
        var name = this.files[0]?.name || 'Pilih file...';
        $(this).next('.custom-file-label').text(name);
    });
    $('#uploadForm').on('submit', function () {
        $('#btnUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
    });
});
</script>
@stop
