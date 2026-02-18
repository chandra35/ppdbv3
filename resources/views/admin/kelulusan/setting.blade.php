@extends('adminlte::page')

@section('title', 'Pengaturan Kelulusan')

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
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_lulus'] }}</h3>
                    <p>Siswa Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_tidak_lulus'] }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_cadangan'] }}</h3>
                    <p>Cadangan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
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
                        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Jadwal Daftar Ulang</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="tanggal_daftar_ulang_mulai" class="form-control"
                                           value="{{ old('tanggal_daftar_ulang_mulai', $setting->tanggal_daftar_ulang_mulai?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label>
                                    <input type="date" name="tanggal_daftar_ulang_selesai" class="form-control"
                                           value="{{ old('tanggal_daftar_ulang_selesai', $setting->tanggal_daftar_ulang_selesai?->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Catatan Daftar Ulang</label>
                            <textarea name="catatan_daftar_ulang" class="form-control" rows="3" placeholder="Informasi jadwal, lokasi, dll">{{ old('catatan_daftar_ulang', $setting->catatan_daftar_ulang) }}</textarea>
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
<script>
$(document).ready(function() {
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
});
</script>
@stop
