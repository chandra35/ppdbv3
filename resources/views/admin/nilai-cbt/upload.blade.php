@extends('adminlte::page')

@section('title', 'Upload Nilai CBT')

@section('css')
<style>
    .mapel-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .mapel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .mapel-card.selected {
        border-color: #007bff;
        background: #e8f4fd;
    }
    .mapel-card .mapel-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .upload-section {
        display: none;
    }
    .upload-section.show {
        display: block;
    }
</style>
@stop

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
    {{-- Step 1: Pilih Mapel --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book mr-2"></i>Langkah 1: Pilih Mata Pelajaran</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Pilih mata pelajaran CBT yang akan diupload nilainya:</p>
            <div class="row">
                @php
                    $icons = [
                        'nilai_mtk' => 'fas fa-calculator text-primary',
                        'nilai_ipa' => 'fas fa-flask text-success',
                        'nilai_ips' => 'fas fa-globe-asia text-warning',
                        'nilai_bahasa_inggris' => 'fas fa-language text-info',
                    ];
                @endphp
                @foreach($komponenList as $field => $label)
                    <div class="col-md-3 col-6 mb-3">
                        <div class="card mapel-card text-center p-3 {{ $selectedMapel == $field ? 'selected' : '' }}"
                             data-mapel="{{ $field }}" data-label="{{ $label }}">
                            <div class="mapel-icon">
                                <i class="{{ $icons[$field] ?? 'fas fa-book' }}"></i>
                            </div>
                            <h6 class="mb-0 font-weight-bold">{{ $label }}</h6>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Step 2: Upload File --}}
    <div class="row upload-section {{ $selectedMapel ? 'show' : '' }}" id="uploadSection">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload mr-2"></i>Langkah 2: Upload File <strong id="mapelTitle">{{ $komponenList[$selectedMapel ?? ''] ?? '' }}</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle mr-1"></i> Informasi</h5>
                        <ul class="mb-0">
                            <li>File harus berformat <strong>.xlsx</strong> atau <strong>.xls</strong></li>
                            <li>Identifikasi peserta menggunakan <strong>NISN</strong></li>
                            <li>Data akan di-<strong>preview</strong> terlebih dahulu sebelum diimport</li>
                            <li>Data yang sudah ada akan di-<strong>update</strong> (bukan duplikat)</li>
                            <li>Nilai di luar rentang 0-100 akan otomatis di-cap</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.nilai-cbt.upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <input type="hidden" name="mapel" id="mapelInput" value="{{ $selectedMapel }}">

                        <div class="form-group">
                            <label>Tahun Pelajaran Aktif</label>
                            <input type="text" class="form-control" value="{{ $tahunAktif->nama ?? 'Tidak ada' }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Mata Pelajaran</label>
                            <input type="text" class="form-control" id="mapelDisplay" value="{{ $komponenList[$selectedMapel ?? ''] ?? '' }}" disabled>
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
                            <tr><td><strong>A</strong></td><td>Nama Lengkap</td></tr>
                            <tr><td><strong>B</strong></td><td>NISN</td></tr>
                            <tr><td><strong>C</strong></td><td>Nilai <span id="formatMapel">{{ $komponenList[$selectedMapel ?? ''] ?? 'mapel' }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table mr-2"></i>Contoh Data</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Ahmad Fauzi</td><td>0012345678</td><td>85</td></tr>
                            <tr><td>Siti Aisyah</td><td>0012345679</td><td>90</td></tr>
                            <tr><td>Budi Santoso</td><td>0012345680</td><td>78</td></tr>
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
                            <li>Upload <strong>satu mapel per file</strong></li>
                            <li>Header/judul di Excel akan otomatis dilewati</li>
                            <li>NISN harus sesuai dengan data pendaftar</li>
                            <li>Jika data sudah ada, nilai akan di-update</li>
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
    // Mapel card selection
    $('.mapel-card').on('click', function() {
        $('.mapel-card').removeClass('selected');
        $(this).addClass('selected');

        var mapel = $(this).data('mapel');
        var label = $(this).data('label');

        $('#mapelInput').val(mapel);
        $('#mapelDisplay').val(label);
        $('#mapelTitle').text(label);
        $('#formatMapel').text(label);
        $('#uploadSection').addClass('show');

        // Scroll to upload section
        $('html, body').animate({
            scrollTop: $('#uploadSection').offset().top - 60
        }, 400);
    });

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Loading + progress on submit
    $('#uploadForm').on('submit', function(e) {
        if (!$('#mapelInput').val()) {
            e.preventDefault();
            alert('Silakan pilih mata pelajaran terlebih dahulu.');
            return false;
        }

        $('#btnUpload').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses file...');
    });
});
</script>
@stop
