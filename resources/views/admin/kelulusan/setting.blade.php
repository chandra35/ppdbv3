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
    <div class="alert alert-info">
        <i class="fas fa-layer-group mr-1"></i>
        <strong>Konteks aktif:</strong>
        Tahun {{ $contextInfo['tahun'] }},
        Jalur {{ $contextInfo['jalur'] }},
        Gelombang {{ $contextInfo['gelombang'] }}.
    </div>

    <div class="card card-outline card-info">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kelulusan.setting') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajaranList as $tahun)
                                <option value="{{ $tahun->id }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                    <div class="form-group mb-md-0">
                        <label>Jalur</label>
                        <select name="jalur_id" id="filter_jalur_id" class="form-control">
                            <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ (string) $selectedJalurIdInput === (string) $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                    <div class="form-group mb-md-0">
                        <label>Gelombang</label>
                        <select name="gelombang_id" id="filter_gelombang_id" class="form-control">
                            <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                            @foreach($allGelombangs as $gel)
                                <option
                                    value="{{ $gel->id }}"
                                    data-jalur-id="{{ $gel->jalur_id }}"
                                    {{ (string) $selectedGelombangIdInput === (string) $gel->id ? 'selected' : '' }}
                                >
                                    {{ $gel->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end mt-3 mt-md-0">
                    <div class="mb-md-0">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter mr-1"></i>Terapkan
                        </button>
                        <a href="{{ route('admin.kelulusan.setting') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-undo mr-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
            <a href="{{ route('admin.kelulusan.envelope-logs', ['tahun_pelajaran_id' => $selectedTahunIdInput]) }}" class="text-decoration-none">
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

    <form action="{{ route('admin.kelulusan.setting.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">
        <input type="hidden" name="jalur_id" value="{{ $selectedJalurIdInput }}">
        <input type="hidden" name="gelombang_id" value="{{ $selectedGelombangIdInput }}">

        <div class="row">
            <!-- Pengumuman -->
            <div class="col-lg-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>Pengumuman Kelulusan</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <div class="text-muted text-uppercase small font-weight-bold">Scope Pengaturan</div>
                                    <div class="font-weight-bold">{{ $setting->scope_label }}</div>
                                    <small class="text-muted">{{ $setting->scope_description }}</small>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    @if($setting->isPengumumanAktif())
                                        <span class="badge badge-success px-3 py-2"><i class="fas fa-broadcast-tower mr-1"></i>Sudah Dipublish</span>
                                    @elseif($setting->tampilkan_pengumuman && $setting->tanggal_pengumuman && now()->lt($setting->tanggal_pengumuman))
                                        <span class="badge badge-warning px-3 py-2"><i class="fas fa-clock mr-1"></i>Terjadwal</span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2"><i class="fas fa-eye-slash mr-1"></i>Masih Disembunyikan</span>
                                    @endif
                                </div>
                            </div>
                        </div>
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
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-success btn-sm mr-1" id="btnPublishNow">
                                    <i class="fas fa-bolt mr-1"></i>Publish Sekarang
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnHideAnnouncement">
                                    <i class="fas fa-eye-slash mr-1"></i>Sembunyikan Lagi
                                </button>
                            </div>
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

                {{-- File Lampiran Konsider (AJAX Upload) --}}
                <div class="card card-purple card-outline" id="konsiderCard">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-download mr-2"></i>Lampiran File Konsider</h3>
                    </div>
                    <div class="card-body">
                        {{-- Drop Zone --}}
                        <div id="konsiderDropZone" class="border border-dashed rounded p-4 text-center mb-3" style="border-color: #6f42c1 !important; cursor: pointer; transition: all 0.3s; background: #faf8ff;">
                            <div id="konsiderDropContent">
                                <i class="fas fa-cloud-upload-alt fa-3x text-purple mb-2"></i>
                                <p class="mb-1 font-weight-bold">Drag & drop file di sini</p>
                                <p class="text-muted mb-2"><small>atau klik untuk memilih file</small></p>
                                <span class="badge badge-light"><i class="fas fa-info-circle mr-1"></i>PDF, DOC, DOCX — Maks. 10MB</span>
                            </div>
                            <input type="file" id="fileKonsider" accept=".pdf,.doc,.docx" class="d-none">
                        </div>

                        {{-- Progress Bar --}}
                        <div id="konsiderProgress" class="mb-3" style="display: none;">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="font-weight-bold" id="konsiderProgressLabel">Mengupload...</small>
                                <small id="konsiderProgressPercent">0%</small>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div id="konsiderProgressBar" class="progress-bar bg-purple progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; transition: width 0.2s;"></div>
                            </div>
                            <small class="text-muted" id="konsiderProgressInfo"></small>
                        </div>

                        {{-- Upload Result / Current File --}}
                        <div id="konsiderFileInfo" style="{{ $setting->file_konsider ? '' : 'display: none;' }}">
                            <div class="alert mb-0 py-2 px-3" style="background: linear-gradient(135deg, #f0e6ff 0%, #e8f4fd 100%); border: 1px solid #d4b5ff; border-radius: 8px;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3" style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #6f42c1, #764ba2); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                        <div>
                                            <strong id="konsiderFileName">{{ $setting->file_konsider ? basename($setting->file_konsider) : '' }}</strong>
                                            <br><small class="text-muted" id="konsiderFileSize">File tersimpan</small>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ $setting->file_konsider ? asset('storage/' . $setting->file_konsider) : '#' }}" target="_blank" class="btn btn-sm btn-info mr-1" id="konsiderViewBtn">
                                            <i class="fas fa-eye mr-1"></i>Lihat
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" id="konsiderDeleteBtn">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Feedback Alert --}}
                        <div id="konsiderAlert" class="mt-2" style="display: none;"></div>
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
    const $jalur = $('#filter_jalur_id');
    const $gelombang = $('#filter_gelombang_id');
    const selectedGelombangId = @json((string) ($selectedGelombangIdInput ?? 'all'));
    const allGelombangOptions = $gelombang.find('option').clone();

    function refreshGelombangOptions(preferredValue = null) {
        const jalurId = String($jalur.val() || 'all');
        const currentValue = preferredValue ?? String($gelombang.val() || 'all');

        $gelombang.empty();

        allGelombangOptions.each(function() {
            const $option = $(this).clone();
            const optionValue = String($option.val() || '');
            const optionJalurId = String($option.data('jalur-id') || '');

            if (optionValue === 'all' || jalurId === 'all' || optionJalurId === jalurId) {
                $gelombang.append($option);
            }
        });

        const optionExists = $gelombang.find(`option[value="${currentValue}"]`).length > 0;
        $gelombang.val(optionExists ? currentValue : 'all');
    }

    $jalur.on('change', function() {
        refreshGelombangOptions('all');
    });

    refreshGelombangOptions(selectedGelombangId);

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

    $('#btnPublishNow').on('click', function() {
        $('#tampilkanPengumuman').prop('checked', true);
        $('input[name="tanggal_pengumuman"]').val('');
        toggleTanggalPengumuman();
    });

    $('#btnHideAnnouncement').on('click', function() {
        $('#tampilkanPengumuman').prop('checked', false);
        toggleTanggalPengumuman();
    });

    // ============================================
    // AJAX Upload File Konsider with Progress
    // ============================================
    var dropZone = document.getElementById('konsiderDropZone');
    var fileInput = document.getElementById('fileKonsider');

    // Click to select file
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag & Drop events
    ['dragenter', 'dragover'].forEach(function(evt) {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.background = '#ede0ff';
            dropZone.style.borderColor = '#6f42c1';
        });
    });
    ['dragleave', 'drop'].forEach(function(evt) {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.background = '#faf8ff';
        });
    });
    dropZone.addEventListener('drop', function(e) {
        var files = e.dataTransfer.files;
        if (files.length > 0) uploadKonsider(files[0]);
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) uploadKonsider(this.files[0]);
    });

    function showKonsiderAlert(type, message) {
        var icons = { success: 'check-circle', danger: 'exclamation-triangle', warning: 'exclamation-circle', info: 'info-circle' };
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show py-2 mb-0" style="border-radius: 8px;">' +
            '<i class="fas fa-' + (icons[type] || 'info-circle') + ' mr-2"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>';
        $('#konsiderAlert').html(alertHtml).show();
        setTimeout(function() { $('#konsiderAlert .alert').alert('close'); }, 6000);
    }

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' bytes';
    }

    function uploadKonsider(file) {
        // Validate client-side
        var allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (allowed.indexOf(file.type) === -1) {
            showKonsiderAlert('danger', 'Format file tidak valid! Hanya PDF, DOC, DOCX yang diperbolehkan.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showKonsiderAlert('danger', 'Ukuran file terlalu besar! Maksimal 10MB. File Anda: ' + formatBytes(file.size));
            return;
        }

        var formData = new FormData();
        formData.append('file_konsider', file);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('tahun_pelajaran_id', '{{ $selectedTahunIdInput }}');
        formData.append('jalur_id', '{{ $selectedJalurIdInput }}');
        formData.append('gelombang_id', '{{ $selectedGelombangIdInput }}');

        // Show progress
        $('#konsiderProgress').show();
        $('#konsiderProgressBar').css('width', '0%').removeClass('bg-success bg-danger').addClass('bg-purple progress-bar-striped progress-bar-animated');
        $('#konsiderProgressPercent').text('0%');
        $('#konsiderProgressLabel').text('Mengupload: ' + file.name);
        $('#konsiderProgressInfo').text('0 / ' + formatBytes(file.size));
        $('#konsiderAlert').hide();
        dropZone.style.pointerEvents = 'none';
        dropZone.style.opacity = '0.5';

        $.ajax({
            url: '{{ route("admin.kelulusan.setting.upload-konsider") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var pct = Math.round((e.loaded / e.total) * 100);
                        $('#konsiderProgressBar').css('width', pct + '%');
                        $('#konsiderProgressPercent').text(pct + '%');
                        $('#konsiderProgressInfo').text(formatBytes(e.loaded) + ' / ' + formatBytes(e.total));
                    }
                });
                return xhr;
            },
            success: function(res) {
                // Complete progress
                $('#konsiderProgressBar').css('width', '100%').removeClass('progress-bar-striped progress-bar-animated bg-purple').addClass('bg-success');
                $('#konsiderProgressLabel').text('Upload selesai!');
                $('#konsiderProgressPercent').text('100%');

                setTimeout(function() {
                    $('#konsiderProgress').slideUp(300);
                }, 1500);

                // Update file info
                $('#konsiderFileName').text(res.filename);
                $('#konsiderFileSize').text(res.filesize);
                $('#konsiderViewBtn').attr('href', res.view_url);
                $('#konsiderFileInfo').slideDown(300);

                showKonsiderAlert('success', '<strong>Berhasil!</strong> File "' + res.filename + '" (' + res.filesize + ') berhasil diupload.');

                // Reset
                dropZone.style.pointerEvents = '';
                dropZone.style.opacity = '';
                fileInput.value = '';
            },
            error: function(xhr) {
                $('#konsiderProgressBar').css('width', '100%').removeClass('progress-bar-striped progress-bar-animated bg-purple').addClass('bg-danger');
                $('#konsiderProgressLabel').text('Upload gagal!');

                setTimeout(function() {
                    $('#konsiderProgress').slideUp(300);
                }, 2000);

                var msg = 'Terjadi kesalahan saat upload.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.file_konsider) {
                        msg = xhr.responseJSON.errors.file_konsider[0];
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                showKonsiderAlert('danger', '<strong>Gagal!</strong> ' + msg);

                dropZone.style.pointerEvents = '';
                dropZone.style.opacity = '';
                fileInput.value = '';
            }
        });
    }

    // Delete file konsider via AJAX
    $(document).on('click', '#konsiderDeleteBtn', function() {
        if (!confirm('Yakin ingin menghapus file konsider?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menghapus...');

        $.ajax({
            url: '{{ route("admin.kelulusan.setting.delete-konsider") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                tahun_pelajaran_id: '{{ $selectedTahunIdInput }}',
                jalur_id: '{{ $selectedJalurIdInput }}',
                gelombang_id: '{{ $selectedGelombangIdInput }}'
            },
            success: function(res) {
                $('#konsiderFileInfo').slideUp(300);
                showKonsiderAlert('success', '<strong>Berhasil!</strong> File konsider telah dihapus.');
                btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i>Hapus');
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus file.';
                showKonsiderAlert('danger', '<strong>Gagal!</strong> ' + msg);
                btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i>Hapus');
            }
        });
    });
});
</script>
@stop
