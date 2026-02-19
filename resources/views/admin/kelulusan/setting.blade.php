@extends('adminlte::page')

@section('title', 'Pengaturan Kelulusan')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
@endsection

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-cog mr-2"></i>Pengaturan Info Kelulusan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.kelulusan.index') }}">Kelulusan</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_lulus'] }}</h3>
                    <p>Siswa Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_tidak_lulus'] }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_cadangan'] }}</h3>
                    <p>Cadangan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <a href="{{ route('admin.kelulusan.envelope-logs') }}" class="text-decoration-none">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\EnvelopeOpenLog::where('tahun_pelajaran_id', $tahunAktif->id)->count() }}</h3>
                        <p>Sudah Buka Amplop</p>
                    </div>
                    <div class="icon"><i class="fas fa-envelope-open"></i></div>
                    <span class="small-box-footer">Lihat Log <i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.kelulusan.setting.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Pengumuman -->
            <div class="col-lg-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>Pengumuman Kelulusan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Judul Pengumuman</label>
                            <input type="text" name="judul_pengumuman" class="form-control" value="{{ old('judul_pengumuman', $setting->judul_pengumuman) }}" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="tampilkan_pengumuman" class="custom-control-input" id="tampilkanPengumuman" {{ $setting->tampilkan_pengumuman ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tampilkanPengumuman">
                                    <strong>Tampilkan Pengumuman ke Pendaftar</strong>
                                </label>
                            </div>
                            <small class="text-muted">Jika aktif, pendaftar akan melihat menu "Info Kelulusan" di dashboard mereka</small>
                        </div>
                        <div class="form-group" id="tanggalPengumumanGroup">
                            <label><i class="fas fa-calendar-alt mr-1"></i>Tanggal & Jam Pengumuman</label>
                            <input type="datetime-local" name="tanggal_pengumuman" class="form-control" 
                                   value="{{ old('tanggal_pengumuman', $setting->tanggal_pengumuman ? $setting->tanggal_pengumuman->format('Y-m-d\TH:i') : '') }}">
                            <small class="text-muted">
                                Pengumuman akan tampil ke pendaftar setelah tanggal & jam ini.<br>
                                Kosongkan jika ingin langsung aktif saat toggle dihidupkan.
                            </small>
                            @if($setting->tanggal_pengumuman)
                                @if(now()->lt($setting->tanggal_pengumuman))
                                    <div class="mt-2">
                                        <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Terjadwal: {{ $setting->tanggal_pengumuman->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</span>
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Sudah Aktif sejak {{ $setting->tanggal_pengumuman->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Pesan untuk yang <span class="text-success font-weight-bold">LULUS</span></label>
                            <textarea name="pesan_lulus" class="form-control" rows="3">{{ old('pesan_lulus', $setting->pesan_lulus) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Pesan untuk yang <span class="text-danger font-weight-bold">TIDAK LULUS</span></label>
                            <textarea name="pesan_tidak_lulus" class="form-control" rows="3">{{ old('pesan_tidak_lulus', $setting->pesan_tidak_lulus) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Group -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fab fa-whatsapp mr-2"></i>Grup WhatsApp</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="tampilkan_link_wa" class="custom-control-input" id="tampilkanWa" {{ $setting->tampilkan_link_wa ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tampilkanWa">
                                    <strong>Tampilkan Link Grup WA ke Pendaftar Lulus</strong>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Grup WA</label>
                            <input type="text" name="nama_grup_wa" class="form-control" value="{{ old('nama_grup_wa', $setting->nama_grup_wa) }}" placeholder="Grup PPDB 2025/2026">
                        </div>
                        <div class="form-group">
                            <label>Link Grup WA</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                </div>
                                <input type="url" name="link_grup_wa" class="form-control" value="{{ old('link_grup_wa', $setting->link_grup_wa) }}" placeholder="https://chat.whatsapp.com/xxxx">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Ulang & Dokumen -->
            <div class="col-lg-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-circle mr-2"></i>Info Penting (Ditampilkan ke Pendaftar Lulus)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Konten Info Penting</label>
                            <textarea name="catatan_daftar_ulang" id="infoPentingEditor" class="form-control">{{ old('catatan_daftar_ulang', $setting->catatan_daftar_ulang) }}</textarea>
                            <small class="text-muted mt-1">Gunakan editor untuk menulis informasi penting seperti jadwal daftar ulang, lokasi, persyaratan, dll.</small>
                        </div>
                    </div>
                </div>

                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Dokumen Persyaratan Daftar Ulang</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="tampilkan_dokumen" class="custom-control-input" id="tampilkanDokumen" {{ $setting->tampilkan_dokumen ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tampilkanDokumen">
                                    <strong>Tampilkan Daftar Dokumen ke Pendaftar Lulus</strong>
                                </label>
                            </div>
                        </div>
                        <div id="dokumen-list">
                            @if($setting->dokumen_persyaratan && count($setting->dokumen_persyaratan) > 0)
                                @foreach($setting->dokumen_persyaratan as $i => $dok)
                                <div class="input-group mb-2 dokumen-item">
                                    <input type="text" name="dokumen_persyaratan[]" class="form-control form-control-sm" value="{{ $dok }}" placeholder="Nama dokumen...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-dok"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 dokumen-item">
                                    <input type="text" name="dokumen_persyaratan[]" class="form-control form-control-sm" placeholder="Nama dokumen...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-dok"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm" id="addDokumen">
                            <i class="fas fa-plus mr-1"></i>Tambah Dokumen
                        </button>
                    </div>
                </div>

                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i>Template Surat Pernyataan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea name="template_surat_pernyataan" class="form-control" rows="6" placeholder="Template surat pernyataan (HTML). Gunakan variabel: {nama}, {nisn}, {jalur}, {tahun_pelajaran}">{{ old('template_surat_pernyataan', $setting->template_surat_pernyataan) }}</textarea>
                            <small class="text-muted">Variabel yang tersedia: <code>{nama}</code>, <code>{nisn}</code>, <code>{jalur}</code>, <code>{gelombang}</code>, <code>{tahun_pelajaran}</code>, <code>{nomor_registrasi}</code></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg btn-block mb-3">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    // Summernote editor for Info Penting
    $('#infoPentingEditor').summernote({
        height: 250,
        placeholder: 'Tulis informasi penting untuk pendaftar yang lulus...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function() {
                // Set default font
                $(this).summernote('fontName', 'Arial');
            }
        }
    });
    // Add dokumen
    $('#addDokumen').click(function() {
        var html = `<div class="input-group mb-2 dokumen-item">
            <input type="text" name="dokumen_persyaratan[]" class="form-control form-control-sm" placeholder="Nama dokumen...">
            <div class="input-group-append">
                <button type="button" class="btn btn-danger btn-sm btn-remove-dok"><i class="fas fa-times"></i></button>
            </div>
        </div>`;
        $('#dokumen-list').append(html);
    });

    // Remove dokumen
    $(document).on('click', '.btn-remove-dok', function() {
        if ($('.dokumen-item').length > 1) {
            $(this).closest('.dokumen-item').remove();
        } else {
            $(this).closest('.dokumen-item').find('input').val('');
        }
    });

    // Toggle tanggal pengumuman visibility
    function toggleTanggalPengumuman() {
        if ($('#tampilkanPengumuman').is(':checked')) {
            $('#tanggalPengumumanGroup').slideDown(200);
        } else {
            $('#tanggalPengumumanGroup').slideUp(200);
        }
    }
    toggleTanggalPengumuman();
    $('#tampilkanPengumuman').on('change', toggleTanggalPengumuman);
});
</script>
@stop
