@extends('adminlte::page')

@section('title', 'Pengaturan WhatsApp API')

@section('content_header')
    <h1><i class="fas fa-cogs"></i> Pengaturan WhatsApp API</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        @php
            $waAktif = old('is_active', $settings->is_active);
            $waConfigured = !empty(old('api_key', $settings->api_key)) && !empty(old('sender_number', $settings->sender_number));
        @endphp

        <div class="alert alert-{{ $waAktif && $waConfigured ? 'success' : ($waAktif ? 'warning' : 'secondary') }}">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                <div>
                    <strong><i class="fab fa-whatsapp mr-1"></i> Status WhatsApp:</strong>
                    @if($waAktif && $waConfigured)
                        Aktif dan siap digunakan.
                    @elseif($waAktif)
                        Diaktifkan, tetapi konfigurasi belum lengkap.
                    @else
                        Sedang dinonaktifkan. Sistem akan mengandalkan email jika tersedia.
                    @endif
                </div>
                <div>
                    <span class="badge badge-{{ $waAktif ? ($waConfigured ? 'success' : 'warning') : 'secondary' }}">
                        {{ $waAktif ? ($waConfigured ? 'AKTIF' : 'BELUM SIAP') : 'NONAKTIF' }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.whatsapp.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- Provider Settings --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fab fa-whatsapp"></i> Konfigurasi Provider</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="provider">Provider WhatsApp <span class="text-danger">*</span></label>
                                <select name="provider" id="provider" class="form-control @error('provider') is-invalid @enderror">
                                    <option value="fonnte" {{ old('provider', $settings->provider) == 'fonnte' ? 'selected' : '' }}>Fonnte</option>
                                    <option value="wablas" {{ old('provider', $settings->provider) == 'wablas' ? 'selected' : '' }}>Wablas</option>
                                    <option value="wabotapi" {{ old('provider', $settings->provider) == 'wabotapi' ? 'selected' : '' }}>Wabotapi</option>
                                    <option value="twilio" {{ old('provider', $settings->provider) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                    <option value="other" {{ old('provider', $settings->provider) == 'other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('provider')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                        {{ old('is_active', $settings->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        <span id="status-text">{{ old('is_active', $settings->is_active) ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    </label>
                                </div>
                                <small class="text-muted">Aktifkan untuk menggunakan notifikasi WhatsApp</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="api_key">API Key / Token</label>
                        <div class="input-group">
                            <input type="password" name="api_key" id="api_key" 
                                class="form-control @error('api_key') is-invalid @enderror" 
                                value="{{ old('api_key', $settings->api_key) }}"
                                placeholder="Masukkan API Key dari provider">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="toggle-api-key">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @error('api_key')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group" id="api-url-group">
                        <label for="api_url">API URL (Opsional)</label>
                        <input type="url" name="api_url" id="api_url" 
                            class="form-control @error('api_url') is-invalid @enderror" 
                            value="{{ old('api_url', $settings->api_url) }}"
                            placeholder="https://api.example.com/send">
                        @error('api_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Kosongkan untuk menggunakan URL default provider</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="sender_number">Nomor Pengirim</label>
                        <input type="text" name="sender_number" id="sender_number" 
                            class="form-control @error('sender_number') is-invalid @enderror" 
                            value="{{ old('sender_number', $settings->sender_number) }}"
                            placeholder="628xxxxxxxxxx">
                        @error('sender_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Nomor WhatsApp yang terdaftar di provider (format: 628xxx)</small>
                    </div>
                </div>
            </div>

            {{-- Message Templates --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Template Pesan</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.whatsapp.reset-templates') }}" 
                            class="btn btn-sm btn-warning" 
                            onclick="return confirm('Reset semua template ke default?')">
                            <i class="fas fa-undo"></i> Reset Default
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Variabel yang tersedia:
                        <code>{nama_siswa}</code>, <code>{nama_sekolah}</code>, <code>{tahun_pelajaran}</code>,
                        <code>{username}</code>, <code>{password}</code>, <code>{url_login}</code>,
                        <code>{nomor_registrasi}</code>, <code>{jalur_pendaftaran}</code>
                    </div>
                    
                    <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="registrasi-tab" data-toggle="tab" href="#registrasi">
                                <i class="fas fa-user-plus"></i> Registrasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="verifikasi-tab" data-toggle="tab" href="#verifikasi">
                                <i class="fas fa-check-circle"></i> Verifikasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="diterima-tab" data-toggle="tab" href="#diterima">
                                <i class="fas fa-thumbs-up"></i> Diterima
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="ditolak-tab" data-toggle="tab" href="#ditolak">
                                <i class="fas fa-thumbs-down"></i> Ditolak
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="lupa-password-tab" data-toggle="tab" href="#lupa-password">
                                <i class="fas fa-key"></i> Lupa Password
                            </a>
                        </li>
                    </ul>
                    
                    <div class="card border mb-3">
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                <span class="text-muted small mr-2"><i class="fas fa-magic"></i> Editor WhatsApp</span>
                                <button type="button" class="btn btn-sm btn-outline-success wa-editor-btn" data-action="bold">
                                    <i class="fas fa-bold"></i> Bold
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary wa-editor-btn" data-action="code">
                                    <i class="fas fa-code"></i> Kode
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary wa-editor-btn" data-action="newline">
                                    <i class="fas fa-level-down-alt"></i> Baris Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-dark wa-editor-btn" data-action="bullet">
                                    <i class="fas fa-list-ul"></i> Bullet
                                </button>
                                <span class="text-muted small ml-2">Emoji cepat:</span>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="🎓" title="Topi Wisuda">🎓</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="📋" title="Clipboard">📋</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="🔗" title="Link">🔗</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="⚠️" title="Peringatan">⚠️</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="✅" title="Centang">✅</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="🎉" title="Perayaan">🎉</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="🔐" title="Kunci">🔐</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="emoji" data-value="😊" title="Smile">😊</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="symbol" data-value="•" title="Bullet Titik">•</button>
                                <button type="button" class="btn btn-sm btn-light border wa-editor-btn" data-action="symbol" data-value="→" title="Panah">→</button>
                                <select id="wa-placeholder-select" class="form-control form-control-sm" style="width: auto;">
                                    <option value="">Pilih Placeholder</option>
                                    <option value="{nama_siswa}">{nama_siswa}</option>
                                    <option value="{nama_sekolah}">{nama_sekolah}</option>
                                    <option value="{tahun_pelajaran}">{tahun_pelajaran}</option>
                                    <option value="{username}">{username}</option>
                                    <option value="{password}">{password}</option>
                                    <option value="{url_login}">{url_login}</option>
                                    <option value="{nomor_registrasi}">{nomor_registrasi}</option>
                                    <option value="{jalur_pendaftaran}">{jalur_pendaftaran}</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-info" id="btn-insert-placeholder">
                                    <i class="fas fa-plus-circle"></i> Sisipkan
                                </button>
                            </div>
                            <div class="small text-muted mt-2">
                                Gunakan <code>*teks*</code> untuk bold dan <code>`teks`</code> untuk kode. Tombol di atas akan menulis format itu otomatis ke posisi kursor.
                            </div>
                        </div>
                    </div>

                    <div class="tab-content pt-3" id="templateTabsContent">
                        <div class="tab-pane fade show active" id="registrasi">
                            <div class="form-group">
                                <label>Template Registrasi Berhasil</label>
                                <textarea name="template_registrasi" class="form-control wa-template-input" rows="6" data-preview-type="registrasi"
                                    placeholder="Template pesan untuk registrasi berhasil">{{ old('template_registrasi', $settings->template_registrasi ?? $defaultTemplates['template_registrasi']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="verifikasi">
                            <div class="form-group">
                                <label>Template Verifikasi Dokumen</label>
                                <textarea name="template_verifikasi" class="form-control wa-template-input" rows="6" data-preview-type="verifikasi"
                                    placeholder="Template pesan untuk verifikasi dokumen">{{ old('template_verifikasi', $settings->template_verifikasi ?? $defaultTemplates['template_verifikasi']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="diterima">
                            <div class="form-group">
                                <label>Template Diterima</label>
                                <textarea name="template_diterima" class="form-control wa-template-input" rows="6" data-preview-type="diterima"
                                    placeholder="Template pesan untuk calon siswa diterima">{{ old('template_diterima', $settings->template_diterima ?? $defaultTemplates['template_diterima']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="ditolak">
                            <div class="form-group">
                                <label>Template Ditolak</label>
                                <textarea name="template_ditolak" class="form-control wa-template-input" rows="6" data-preview-type="ditolak"
                                    placeholder="Template pesan untuk calon siswa ditolak">{{ old('template_ditolak', $settings->template_ditolak ?? $defaultTemplates['template_ditolak']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="lupa-password">
                            <div class="form-group">
                                <label>Template Lupa Password</label>
                                <textarea name="template_lupa_password" class="form-control wa-template-input" rows="6" data-preview-type="lupa-password"
                                    placeholder="Template pesan untuk reset password">{{ old('template_lupa_password', $settings->template_lupa_password ?? $defaultTemplates['template_lupa_password']) }}</textarea>
                                <small class="text-muted">Variabel khusus: <code>{password}</code> = password baru yang digenerate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fab fa-whatsapp"></i> Preview Visual</h3>
            </div>
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Preview ini menampilkan bentuk pesan yang mendekati tampilan WhatsApp. Placeholder akan diganti dengan data contoh.
                </div>
                <div class="wa-preview-phone">
                    <div class="wa-preview-topbar">
                        <i class="fab fa-whatsapp mr-1"></i> Chat Preview
                    </div>
                    <div class="wa-preview-body">
                        <div class="wa-preview-meta" id="wa-preview-type">Template Registrasi</div>
                        <div class="wa-preview-bubble" id="wa-preview-bubble"></div>
                        <div class="wa-preview-time">10:25</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test Connection --}}
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plug"></i> Test Koneksi</h3>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-block btn-outline-success" id="btn-test-connection" {{ !($settings->is_active ?? false) ? 'disabled' : '' }}>
                    <i class="fas fa-sync-alt"></i> Test Koneksi API
                </button>
                <div id="connection-result" class="mt-3" style="display: none;"></div>
            </div>
        </div>

        {{-- Send Test Message --}}
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane"></i> Kirim Pesan Test</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nomor Tujuan</label>
                    <input type="text" id="test-phone" class="form-control" placeholder="628xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Pesan</label>
                    <textarea id="test-message" class="form-control" rows="3" placeholder="Pesan test dari PPDB">Test pesan dari sistem PPDB {{ config('app.name') }}</textarea>
                </div>
                <button type="button" class="btn btn-block btn-warning" id="btn-send-test" {{ !($settings->is_active ?? false) ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i> Kirim Test
                </button>
                <div id="send-result" class="mt-3" style="display: none;"></div>
            </div>
        </div>

        {{-- Provider Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Provider</h3>
            </div>
            <div class="card-body">
                <div id="provider-info">
                    <p><strong>Fonnte</strong></p>
                    <ul class="small">
                        <li>Website: <a href="https://fonnte.com" target="_blank">fonnte.com</a></li>
                        <li>Harga mulai dari Rp50.000/bulan</li>
                        <li>Support multi-device</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .custom-switch .custom-control-label::before {
        width: 2.5rem;
        height: 1.5rem;
        border-radius: 0.75rem;
    }
    .custom-switch .custom-control-label::after {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
    }
    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1rem);
    }
    .wa-preview-phone {
        border: 1px solid #dfe5ec;
        border-radius: 18px;
        overflow: hidden;
        background: #e5ddd5;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.03);
    }
    .wa-preview-topbar {
        background: #075e54;
        color: #fff;
        padding: 10px 14px;
        font-weight: 600;
    }
    .wa-preview-body {
        padding: 14px;
        background-image: linear-gradient(rgba(255,255,255,0.35), rgba(255,255,255,0.35));
    }
    .wa-preview-meta {
        font-size: 12px;
        color: #667085;
        margin-bottom: 8px;
    }
    .wa-preview-bubble {
        background: #dcf8c6;
        border-radius: 10px 10px 2px 10px;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.5;
        color: #111827;
        white-space: normal;
        word-break: break-word;
        box-shadow: 0 1px 1px rgba(0,0,0,0.08);
    }
    .wa-preview-bubble code {
        background: rgba(0,0,0,0.06);
        color: #111827;
        padding: 1px 4px;
        border-radius: 4px;
        font-size: 12px;
    }
    .wa-preview-time {
        text-align: right;
        font-size: 11px;
        color: #667085;
        margin-top: 4px;
    }
    .wa-template-input {
        font-family: "Consolas", "Courier New", monospace;
        line-height: 1.6;
        min-height: 180px;
    }
</style>
@stop

@section('js')
<script>
$(function() {
    const previewTitles = {
        'registrasi': 'Template Registrasi',
        'verifikasi': 'Template Verifikasi',
        'diterima': 'Template Diterima',
        'ditolak': 'Template Ditolak',
        'lupa-password': 'Template Lupa Password'
    };

    const previewSamples = {
        '{nama_siswa}': 'Ahmad Fauzan',
        '{nama_sekolah}': 'MAN 1 Metro',
        '{tahun_pelajaran}': '2026/2027',
        '{username}': '0114349813',
        '{password}': 'Abc12345',
        '{url_login}': 'https://ppdb.man1metro.sch.id/login',
        '{nomor_registrasi}': 'REG-REGULER-2026-0498',
        '{jalur_pendaftaran}': 'REGULER'
    };

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function renderWhatsAppPreview(rawText) {
        let text = rawText || '';

        Object.entries(previewSamples).forEach(([key, value]) => {
            text = text.split(key).join(value);
        });

        text = escapeHtml(text);
        text = text.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
        text = text.replace(/`(.*?)`/g, '<code>$1</code>');
        text = text.replace(/\n/g, '<br>');

        return text;
    }

    function updateTemplatePreview() {
        const $active = $('.tab-pane.active .wa-template-input');
        if (!$active.length) {
            return;
        }

        const type = $active.data('preview-type');
        $('#wa-preview-type').text(previewTitles[type] || 'Preview Pesan');
        $('#wa-preview-bubble').html(renderWhatsAppPreview($active.val()));
    }

    function getActiveTemplateInput() {
        return $('.tab-pane.active .wa-template-input').get(0);
    }

    function wrapSelection(textarea, before, after = '') {
        if (!textarea) {
            return;
        }

        const start = textarea.selectionStart ?? 0;
        const end = textarea.selectionEnd ?? 0;
        const value = textarea.value || '';
        const selected = value.substring(start, end);
        const fallback = before === '\n• ' ? 'item baru' : 'teks';
        const insert = before + (selected || fallback) + after;

        textarea.value = value.substring(0, start) + insert + value.substring(end);

        const cursorPos = selected ? start + insert.length : start + before.length + fallback.length + after.length;
        textarea.focus();
        textarea.setSelectionRange(cursorPos, cursorPos);
        $(textarea).trigger('input');
    }

    function insertTextAtCursor(textarea, text) {
        if (!textarea) {
            return;
        }

        const start = textarea.selectionStart ?? 0;
        const end = textarea.selectionEnd ?? 0;
        const value = textarea.value || '';
        textarea.value = value.substring(0, start) + text + value.substring(end);
        const cursorPos = start + text.length;
        textarea.focus();
        textarea.setSelectionRange(cursorPos, cursorPos);
        $(textarea).trigger('input');
    }

    // Toggle API key visibility
    $('#toggle-api-key').click(function() {
        var input = $('#api_key');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Update status text
    $('#is_active').change(function() {
        $('#status-text').text($(this).is(':checked') ? 'Aktif' : 'Tidak Aktif');
        $('#btn-test-connection, #btn-send-test').prop('disabled', !$(this).is(':checked'));
    });

    // Provider info update
    const providerInfo = {
        fonnte: {
            name: 'Fonnte',
            info: '<ul class="small"><li>Website: <a href="https://fonnte.com" target="_blank">fonnte.com</a></li><li>Harga mulai dari Rp50.000/bulan</li><li>Support multi-device</li></ul>'
        },
        wablas: {
            name: 'Wablas',
            info: '<ul class="small"><li>Website: <a href="https://wablas.com" target="_blank">wablas.com</a></li><li>Harga mulai dari Rp35.000/bulan</li><li>REST API sederhana</li></ul>'
        },
        wabotapi: {
            name: 'Wabotapi',
            info: '<ul class="small"><li>Website: <a href="https://wabotapi.com" target="_blank">wabotapi.com</a></li><li>Free tier tersedia</li><li>Mudah diintegrasikan</li></ul>'
        },
        twilio: {
            name: 'Twilio',
            info: '<ul class="small"><li>Website: <a href="https://twilio.com/whatsapp" target="_blank">twilio.com</a></li><li>Provider internasional</li><li>Pay as you go</li></ul>'
        },
        other: {
            name: 'Provider Lainnya',
            info: '<ul class="small"><li>Gunakan API URL custom</li><li>Pastikan format request sesuai</li></ul>'
        }
    };

    $('#provider').change(function() {
        var provider = $(this).val();
        var info = providerInfo[provider];
        $('#provider-info').html('<p><strong>' + info.name + '</strong></p>' + info.info);
        
        // Show/hide API URL based on provider
        if (provider === 'other') {
            $('#api-url-group').show();
        } else {
            $('#api-url-group').hide();
        }
    }).trigger('change');

    $('.wa-template-input').on('input', updateTemplatePreview);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
        updateTemplatePreview();
    });
    updateTemplatePreview();

    $('.wa-editor-btn').on('click', function() {
        const textarea = getActiveTemplateInput();
        const action = $(this).data('action');

        if (action === 'bold') {
            wrapSelection(textarea, '*', '*');
        } else if (action === 'code') {
            wrapSelection(textarea, '`', '`');
        } else if (action === 'newline') {
            insertTextAtCursor(textarea, '\n');
        } else if (action === 'bullet') {
            insertTextAtCursor(textarea, '\n• ');
        } else if (action === 'emoji' || action === 'symbol') {
            insertTextAtCursor(textarea, $(this).data('value'));
        }
    });

    $('#btn-insert-placeholder').on('click', function() {
        const textarea = getActiveTemplateInput();
        const placeholder = $('#wa-placeholder-select').val();
        if (!placeholder) {
            return;
        }
        insertTextAtCursor(textarea, placeholder);
    });

    // Test connection
    $('#btn-test-connection').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        $.ajax({
            url: '{{ route("admin.whatsapp.test-connection") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                var alertClass = response.success ? 'alert-success' : 'alert-danger';
                var icon = response.success ? 'check-circle' : 'times-circle';
                $('#connection-result')
                    .removeClass('alert-success alert-danger')
                    .addClass('alert ' + alertClass)
                    .html('<i class="fas fa-' + icon + '"></i> ' + response.message)
                    .show();
            },
            error: function(xhr) {
                $('#connection-result')
                    .removeClass('alert-success')
                    .addClass('alert alert-danger')
                    .html('<i class="fas fa-times-circle"></i> Gagal melakukan test koneksi')
                    .show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Test Koneksi API');
            }
        });
    });

    // Send test message
    $('#btn-send-test').click(function() {
        var btn = $(this);
        var phone = $('#test-phone').val();
        var message = $('#test-message').val();

        if (!phone) {
            $('#send-result')
                .removeClass('alert-success')
                .addClass('alert alert-warning')
                .html('<i class="fas fa-exclamation-triangle"></i> Masukkan nomor tujuan')
                .show();
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
        
        $.ajax({
            url: '{{ route("admin.whatsapp.send-test") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                phone: phone,
                message: message
            },
            success: function(response) {
                var alertClass = response.success ? 'alert-success' : 'alert-danger';
                var icon = response.success ? 'check-circle' : 'times-circle';
                $('#send-result')
                    .removeClass('alert-success alert-danger alert-warning')
                    .addClass('alert ' + alertClass)
                    .html('<i class="fas fa-' + icon + '"></i> ' + response.message)
                    .show();
            },
            error: function(xhr) {
                $('#send-result')
                    .removeClass('alert-success alert-warning')
                    .addClass('alert alert-danger')
                    .html('<i class="fas fa-times-circle"></i> Gagal mengirim pesan')
                    .show();
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Test');
            }
        });
    });
});
</script>
@stop
