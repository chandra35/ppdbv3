@extends('adminlte::page')

@section('title', 'Pengaturan Email Notifikasi')

@section('content_header')
<h1 class="m-0 text-dark">Pengaturan Email Notifikasi</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.email.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Global Settings --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog mr-2"></i>Pengaturan Global
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                    {{ old('is_active', $settings->is_active ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    <strong>Aktifkan Email Notifikasi</strong>
                                </label>
                            </div>
                            <small class="text-muted">Jika dinonaktifkan, semua email tidak akan dikirim</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_name">Nama Pengirim</label>
                            <input type="text" class="form-control" id="from_name" name="from_name" 
                                value="{{ old('from_name', $settings->from_name) }}" placeholder="PPDB MAN 1 Metro">
                            <small class="text-muted">Nama yang ditampilkan sebagai pengirim email</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_email">Email Pengirim</label>
                            <input type="email" class="form-control" id="from_email" name="from_email" 
                                value="{{ old('from_email', $settings->from_email) }}" placeholder="ppdb@man1metro.sch.id">
                            <small class="text-muted">Alamat email pengirim</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reply_to">Reply To</label>
                            <input type="email" class="form-control" id="reply_to" name="reply_to" 
                                value="{{ old('reply_to', $settings->reply_to) }}" placeholder="info@man1metro.sch.id">
                            <small class="text-muted">Email untuk balasan</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="footer_text">Footer Email</label>
                            <textarea class="form-control" id="footer_text" name="footer_text" rows="2" 
                                placeholder="© 2026 MAN 1 Metro. All rights reserved.">{{ old('footer_text', $settings->footer_text) }}</textarea>
                            <small class="text-muted">Teks footer yang akan ditambahkan di akhir email</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enable/Disable Per Type --}}
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-toggle-on mr-2"></i>Aktifkan/Nonaktifkan Notifikasi
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="enable_registrasi" name="enable_registrasi" value="1"
                                {{ old('enable_registrasi', $settings->enable_registrasi ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enable_registrasi">
                                <i class="fas fa-user-plus text-success mr-1"></i> Notifikasi Registrasi
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="enable_revisi" name="enable_revisi" value="1"
                                {{ old('enable_revisi', $settings->enable_revisi ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enable_revisi">
                                <i class="fas fa-edit text-warning mr-1"></i> Notifikasi Revisi Dokumen
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="enable_nomor_tes" name="enable_nomor_tes" value="1"
                                {{ old('enable_nomor_tes', $settings->enable_nomor_tes ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enable_nomor_tes">
                                <i class="fas fa-id-card text-info mr-1"></i> Notifikasi Nomor Tes
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="enable_diterima" name="enable_diterima" value="1"
                                {{ old('enable_diterima', $settings->enable_diterima ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enable_diterima">
                                <i class="fas fa-check-circle text-success mr-1"></i> Notifikasi Diterima
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="enable_ditolak" name="enable_ditolak" value="1"
                                {{ old('enable_ditolak', $settings->enable_ditolak ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enable_ditolak">
                                <i class="fas fa-times-circle text-danger mr-1"></i> Notifikasi Ditolak
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Template Editor Tabs --}}
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope-open-text mr-2"></i>Template Email
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-warning btn-sm" onclick="confirmResetTemplates()">
                        <i class="fas fa-undo mr-1"></i> Reset Template ke Default
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="registrasi-tab" data-toggle="tab" href="#registrasi" role="tab">
                            <i class="fas fa-user-plus text-success mr-1"></i> Registrasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="revisi-tab" data-toggle="tab" href="#revisi" role="tab">
                            <i class="fas fa-edit text-warning mr-1"></i> Revisi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="nomor_tes-tab" data-toggle="tab" href="#nomor_tes" role="tab">
                            <i class="fas fa-id-card text-info mr-1"></i> Nomor Tes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="diterima-tab" data-toggle="tab" href="#diterima" role="tab">
                            <i class="fas fa-check-circle text-success mr-1"></i> Diterima
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ditolak-tab" data-toggle="tab" href="#ditolak" role="tab">
                            <i class="fas fa-times-circle text-danger mr-1"></i> Ditolak
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="templateTabsContent">
                    {{-- Registrasi --}}
                    <div class="tab-pane fade show active" id="registrasi" role="tabpanel">
                        @include('admin.pengaturan.email-template-form', [
                            'type' => 'registrasi',
                            'label' => 'Registrasi',
                            'settings' => $settings,
                            'defaultTemplates' => $defaultTemplates,
                            'placeholders' => $placeholders['registrasi']
                        ])
                    </div>

                    {{-- Revisi --}}
                    <div class="tab-pane fade" id="revisi" role="tabpanel">
                        @include('admin.pengaturan.email-template-form', [
                            'type' => 'revisi',
                            'label' => 'Revisi Dokumen',
                            'settings' => $settings,
                            'defaultTemplates' => $defaultTemplates,
                            'placeholders' => $placeholders['revisi']
                        ])
                    </div>

                    {{-- Nomor Tes --}}
                    <div class="tab-pane fade" id="nomor_tes" role="tabpanel">
                        @include('admin.pengaturan.email-template-form', [
                            'type' => 'nomor_tes',
                            'label' => 'Nomor Tes',
                            'settings' => $settings,
                            'defaultTemplates' => $defaultTemplates,
                            'placeholders' => $placeholders['nomor_tes']
                        ])
                    </div>

                    {{-- Diterima --}}
                    <div class="tab-pane fade" id="diterima" role="tabpanel">
                        @include('admin.pengaturan.email-template-form', [
                            'type' => 'diterima',
                            'label' => 'Diterima',
                            'settings' => $settings,
                            'defaultTemplates' => $defaultTemplates,
                            'placeholders' => $placeholders['diterima']
                        ])
                    </div>

                    {{-- Ditolak --}}
                    <div class="tab-pane fade" id="ditolak" role="tabpanel">
                        @include('admin.pengaturan.email-template-form', [
                            'type' => 'ditolak',
                            'label' => 'Ditolak',
                            'settings' => $settings,
                            'defaultTemplates' => $defaultTemplates,
                            'placeholders' => $placeholders['ditolak']
                        ])
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="row mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>

    {{-- Reset Template Form --}}
    <form id="resetTemplatesForm" action="{{ route('admin.email.reset-templates') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>

{{-- Test Email Modal --}}
<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Test Email
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="test_email">Email Tujuan</label>
                    <input type="email" class="form-control" id="test_email" placeholder="test@example.com" required>
                </div>
                <input type="hidden" id="test_type">
                <div id="testResult" class="mt-3" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info" onclick="sendTestEmail()">
                    <i class="fas fa-paper-plane mr-1"></i> Kirim
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
<style>
.nav-tabs .nav-link {
    color: #555;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    font-weight: 600;
}
.placeholder-badge {
    cursor: pointer;
    margin: 2px;
    font-size: 12px;
}
.placeholder-badge:hover {
    opacity: 0.8;
    transform: scale(1.05);
}
.note-editor.note-frame {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.note-editor .note-toolbar {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.note-editor .note-editing-area .note-editable {
    background-color: #fff;
    min-height: 250px;
}
.note-editor .note-statusbar {
    background-color: #f8f9fa;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
// Summernote configuration
const summernoteConfig = {
    height: 300,
    minHeight: 200,
    maxHeight: 500,
    focus: false,
    placeholder: 'Ketik konten email di sini...',
    toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture', 'hr']],
        ['view', ['fullscreen', 'codeview', 'help']]
    ],
    fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36'],
    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    callbacks: {
        onInit: function() {
            // Editor initialized
        }
    }
};

function openTestModal(type) {
    $('#test_type').val(type);
    $('#test_email').val('');
    $('#testResult').hide();
    $('#testEmailModal').modal('show');
}

function sendTestEmail() {
    const email = $('#test_email').val();
    const type = $('#test_type').val();
    
    if (!email) {
        alert('Masukkan email tujuan!');
        return;
    }

    $('#testResult').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...</div>').show();

    $.ajax({
        url: '{{ route("admin.email.send-test") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            email: email,
            type: type
        },
        success: function(response) {
            if (response.success) {
                $('#testResult').html('<div class="alert alert-success"><i class="fas fa-check mr-2"></i>' + response.message + '</div>');
            } else {
                $('#testResult').html('<div class="alert alert-danger"><i class="fas fa-times mr-2"></i>' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
            $('#testResult').html('<div class="alert alert-danger"><i class="fas fa-times mr-2"></i>' + msg + '</div>');
        }
    });
}

function confirmResetTemplates() {
    if (confirm('Apakah Anda yakin ingin mereset semua template ke default?\n\nPerhatian: Perubahan yang sudah dibuat akan hilang!')) {
        $('#resetTemplatesForm').submit();
    }
}

function insertPlaceholder(type, placeholder) {
    // Insert placeholder at cursor position in Summernote
    const editor = $('#template_' + type);
    
    // Check if Summernote is initialized
    if (editor.hasClass('note-editor') || editor.next('.note-editor').length > 0) {
        editor.summernote('editor.insertText', placeholder);
    } else {
        // Fallback for regular textarea
        const textarea = document.getElementById('template_' + type);
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        
        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    }
}

$(document).ready(function() {
    // Initialize Summernote on all template textareas
    $('.summernote-editor').each(function() {
        $(this).summernote(summernoteConfig);
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle tab switching - reinitialize Summernote if needed
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Refresh Summernote layout when tab becomes visible
        $($(e.target).attr('href')).find('.summernote-editor').each(function() {
            if (!$(this).next('.note-editor').length) {
                $(this).summernote(summernoteConfig);
            }
        });
    });
});
</script>
@stop
