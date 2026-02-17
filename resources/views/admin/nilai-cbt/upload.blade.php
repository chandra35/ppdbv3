@extends('adminlte::page')

@section('title', 'Upload Nilai CBT')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-file-upload mr-2"></i>Upload Nilai CBT
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-cbt.index') }}">Nilai CBT</a></li>
            <li class="breadcrumb-item active">Upload</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Upload File Excel</h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle mr-1"></i> Informasi</h5>
                        <ul class="mb-0">
                            <li>File harus berformat <strong>.xlsx</strong> atau <strong>.xls</strong></li>
                            <li>Identifikasi peserta menggunakan <strong>NISN</strong></li>
                            <li>Data akan di-preview terlebih dahulu sebelum diimport</li>
                            <li>Data yang sudah ada akan di-<strong>update</strong> (bukan duplikat)</li>
                            <li>Nilai di luar rentang 0-100 akan otomatis di-cap</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.nilai-cbt.upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <div class="form-group">
                            <label>Tahun Pelajaran Aktif</label>
                            <input type="text" class="form-control" value="{{ $tahunAktif->nama ?? 'Tidak ada' }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="file">File Excel <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                            @error('file')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="btnUpload">
                            <i class="fas fa-search mr-1"></i> Preview & Periksa Data
                        </button>
                        <a href="{{ route('admin.nilai-cbt.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-excel mr-2"></i>Format Excel</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Kolom</th>
                                <th>Isi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>A</strong></td><td>No. Urut</td></tr>
                            <tr><td><strong>B</strong></td><td>NISN</td></tr>
                            <tr><td><strong>C</strong></td><td>Nama Lengkap</td></tr>
                            <tr><td><strong>D</strong></td><td>Matematika</td></tr>
                            <tr><td><strong>E</strong></td><td>IPA Terpadu</td></tr>
                            <tr><td><strong>F</strong></td><td>IPS Terpadu</td></tr>
                            <tr><td><strong>G</strong></td><td>Bahasa Inggris</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Catatan</h3>
                </div>
                <div class="card-body">
                    <small>
                        <ul class="pl-3 mb-0">
                            <li>Header/judul tabel di Excel akan otomatis dilewati</li>
                            <li>Baris data dimulai dari baris pertama yang kolom A berisi angka</li>
                            <li>NISN harus sesuai dengan data pendaftar di sistem</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Loading state on submit
    $('#uploadForm').on('submit', function() {
        $('#btnUpload').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses file...');
    });
});
</script>
@stop
