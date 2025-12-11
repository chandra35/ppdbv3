@extends('adminlte::page')

@section('title', 'Pengaturan WhatsApp API')

@section('content_header')
    <h1><i class="fas fa-cogs"></i> Pengaturan WhatsApp API</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.pengaturan.whatsapp.update') }}" method="POST">
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
                        <a href="{{ route('admin.pengaturan.whatsapp.reset-templates') }}" 
                            class="btn btn-sm btn-warning" 
                            onclick="return confirm('Reset semua template ke default?')">
                            <i class="fas fa-undo"></i> Reset Default
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Variabel yang tersedia:
                        <code>{nama}</code>, <code>{nisn}</code>, <code>{password}</code>, <code>{no_pendaftaran}</code>, 
                        <code>{tanggal}</code>, <code>{madrasah}</code>, <code>{url}</code>
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
                    </ul>
                    
                    <div class="tab-content pt-3" id="templateTabsContent">
                        <div class="tab-pane fade show active" id="registrasi">
                            <div class="form-group">
                                <label>Template Registrasi Berhasil</label>
                                <textarea name="template_registrasi" class="form-control" rows="6"
                                    placeholder="Template pesan untuk registrasi berhasil">{{ old('template_registrasi', $settings->template_registrasi ?? $defaultTemplates['template_registrasi']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="verifikasi">
                            <div class="form-group">
                                <label>Template Verifikasi Dokumen</label>
                                <textarea name="template_verifikasi" class="form-control" rows="6"
                                    placeholder="Template pesan untuk verifikasi dokumen">{{ old('template_verifikasi', $settings->template_verifikasi ?? $defaultTemplates['template_verifikasi']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="diterima">
                            <div class="form-group">
                                <label>Template Diterima</label>
                                <textarea name="template_diterima" class="form-control" rows="6"
                                    placeholder="Template pesan untuk calon siswa diterima">{{ old('template_diterima', $settings->template_diterima ?? $defaultTemplates['template_diterima']) }}</textarea>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="ditolak">
                            <div class="form-group">
                                <label>Template Ditolak</label>
                                <textarea name="template_ditolak" class="form-control" rows="6"
                                    placeholder="Template pesan untuk calon siswa ditolak">{{ old('template_ditolak', $settings->template_ditolak ?? $defaultTemplates['template_ditolak']) }}</textarea>
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
        {{-- Test Connection --}}
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plug"></i> Test Koneksi</h3>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-block btn-outline-success" id="btn-test-connection" {{ !$settings->is_active ? 'disabled' : '' }}>
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
                <button type="button" class="btn btn-block btn-warning" id="btn-send-test" {{ !$settings->is_active ? 'disabled' : '' }}>
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
</style>
@stop

@section('js')
<script>
$(function() {
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

    // Test connection
    $('#btn-test-connection').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        
        $.ajax({
            url: '{{ route("admin.pengaturan.whatsapp.test-connection") }}',
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
            url: '{{ route("admin.pengaturan.whatsapp.send-test") }}',
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
