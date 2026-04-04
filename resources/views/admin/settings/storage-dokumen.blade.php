@extends('adminlte::page')

@section('title', 'Storage Dokumen')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fab fa-google-drive"></i> Storage Dokumen</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Pengaturan PPDB</a></li>
                <li class="breadcrumb-item active">Storage Dokumen</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.settings.storage.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_dokumen_storage_mode_selected" id="_dokumen_storage_mode_selected" value="{{ old('dokumen_storage_mode', $settings->dokumen_storage_mode) }}">
        <input type="hidden" name="_google_drive_auth_mode_selected" id="_google_drive_auth_mode_selected" value="{{ old('google_drive_auth_mode', $settings->google_drive_auth_mode) }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-hdd"></i> Konfigurasi Penyimpanan Dokumen</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $statusClass = match($googleDriveStatus) {
                                'ready' => 'success',
                                'warning' => 'warning',
                                'incomplete' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <div class="alert alert-{{ $statusClass }}">
                            <strong>Status:</strong> {{ strtoupper($googleDriveStatus === 'local' ? 'LOKAL' : $googleDriveStatus) }}<br>
                            <small>{{ $googleDriveStatusMessage }}</small>
                        </div>

                        <div class="form-group">
                            <label for="dokumen_storage_mode">Mode Penyimpanan Dokumen</label>
                            <select class="form-control @error('dokumen_storage_mode') is-invalid @enderror" id="dokumen_storage_mode" name="dokumen_storage_mode">
                                <option value="local" {{ old('dokumen_storage_mode', $settings->dokumen_storage_mode) === 'local' ? 'selected' : '' }}>Lokal Saja</option>
                                <option value="gdrive_primary_local_fallback" {{ old('dokumen_storage_mode', $settings->dokumen_storage_mode) === 'gdrive_primary_local_fallback' ? 'selected' : '' }}>Google Drive Utama, Lokal sebagai Fallback</option>
                            </select>
                            @error('dokumen_storage_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Mode hybrid akan mencoba simpan ke Google Drive dulu. Jika gagal, file otomatis jatuh ke storage lokal.</small>
                        </div>

                        <div id="googleDriveConfigBox">
                            <div class="form-group">
                                <label for="google_drive_auth_mode">Mode Autentikasi Google Drive</label>
                                <select class="form-control @error('google_drive_auth_mode') is-invalid @enderror"
                                        id="google_drive_auth_mode" name="google_drive_auth_mode">
                                    <option value="service_account" {{ old('google_drive_auth_mode', $settings->google_drive_auth_mode) === 'service_account' ? 'selected' : '' }}>Service Account</option>
                                    <option value="oauth" {{ old('google_drive_auth_mode', $settings->google_drive_auth_mode) === 'oauth' ? 'selected' : '' }}>OAuth Akun Google Pribadi</option>
                                </select>
                                @error('google_drive_auth_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="google_drive_root_folder_id">Google Drive Root Folder ID</label>
                                <input type="text" class="form-control @error('google_drive_root_folder_id') is-invalid @enderror"
                                       id="google_drive_root_folder_id" name="google_drive_root_folder_id"
                                       value="{{ old('google_drive_root_folder_id', $settings->google_drive_root_folder_id) }}"
                                       placeholder="Contoh: 1-J_OmrI9QkGGFnGCoCy5QNOt9jHcEmg0">
                                @error('google_drive_root_folder_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="googleDriveServiceAccountBox">
                                <div class="form-group">
                                    <label for="google_drive_credentials_file">Upload Credential JSON Google Drive</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('google_drive_credentials_file') is-invalid @enderror"
                                               id="google_drive_credentials_file" name="google_drive_credentials_file" accept=".json,application/json,text/plain">
                                        <label class="custom-file-label" for="google_drive_credentials_file">Pilih file JSON service account</label>
                                    </div>
                                    @error('google_drive_credentials_file')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @if($settings->google_drive_credentials_path)
                                        <small class="text-success d-block mt-1">
                                            Credential tersimpan: <code>{{ basename($settings->google_drive_credentials_path) }}</code>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div id="googleDriveOauthBox">
                                <div class="form-group">
                                    <label for="google_drive_oauth_client_id">OAuth Client ID</label>
                                    <input type="text" class="form-control @error('google_drive_oauth_client_id') is-invalid @enderror"
                                           id="google_drive_oauth_client_id" name="google_drive_oauth_client_id"
                                           value="{{ old('google_drive_oauth_client_id', $settings->google_drive_oauth_client_id) }}"
                                           placeholder="Masukkan Google OAuth Client ID">
                                    @error('google_drive_oauth_client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="google_drive_oauth_client_secret">OAuth Client Secret</label>
                                    <input type="password" class="form-control @error('google_drive_oauth_client_secret') is-invalid @enderror"
                                           id="google_drive_oauth_client_secret" name="google_drive_oauth_client_secret"
                                           value="{{ old('google_drive_oauth_client_secret', $settings->google_drive_oauth_client_secret) }}"
                                           placeholder="Masukkan Google OAuth Client Secret">
                                    @error('google_drive_oauth_client_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="alert alert-light border">
                                    <strong>Redirect URI OAuth:</strong>
                                    <code>{{ route('admin.settings.storage.google-drive.oauth.callback') }}</code>
                                    <br>
                                    <small class="text-muted">Tambahkan URI ini ke OAuth Client di Google Cloud Console.</small>
                                </div>

                                <div class="mb-2">
                                    @if($settings->google_drive_oauth_refresh_token)
                                        <span class="badge badge-success">OAuth Terhubung</span>
                                        @if($settings->google_drive_oauth_email)
                                            <small class="text-muted ml-2">{{ $settings->google_drive_oauth_email }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">OAuth Belum Terhubung</span>
                                    @endif
                                </div>

                                <div class="btn-group mb-3">
                                    <a href="{{ route('admin.settings.storage.google-drive.oauth.redirect') }}" class="btn btn-primary btn-sm">
                                        <i class="fab fa-google-drive mr-1"></i> Connect Google Drive
                                    </a>
                                    @if($settings->google_drive_oauth_refresh_token)
                                        <button type="submit"
                                                formaction="{{ route('admin.settings.storage.google-drive.oauth.disconnect') }}"
                                                formmethod="POST"
                                                class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-unlink mr-1"></i> Putuskan
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                           id="google_drive_make_public" name="google_drive_make_public" value="1"
                                           {{ old('google_drive_make_public', $settings->google_drive_make_public) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="google_drive_make_public">
                                        <strong>Izinkan Preview Link Google Drive</strong>
                                        <small class="text-muted d-block">File Google Drive akan diberi akses baca agar preview dokumen tetap berjalan di aplikasi.</small>
                                    </label>
                                </div>
                            </div>

                            @if($googleDriveClientEmail)
                                <div class="alert alert-light border">
                                    <strong>{{ old('google_drive_auth_mode', $settings->google_drive_auth_mode) === 'oauth' ? 'Akun OAuth' : 'Service Account' }}:</strong>
                                    <code>{{ $googleDriveClientEmail }}</code>
                                    <br>
                                    <small class="text-muted">
                                        @if(old('google_drive_auth_mode', $settings->google_drive_auth_mode) === 'oauth')
                                            Akun ini akan dipakai untuk menyimpan file ke Google Drive pribadi Anda.
                                        @else
                                            Pastikan folder Google Drive utama sudah di-share ke email ini sebagai Editor.
                                        @endif
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Catatan</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Mode yang paling cocok untuk Google Drive pribadi biasanya adalah <strong>OAuth</strong>, bukan service account.</p>
                        <ul class="pl-3 text-muted mb-0">
                            <li>Service account cocok untuk Shared Drive atau Workspace.</li>
                            <li>OAuth cocok untuk Google One / Drive pribadi.</li>
                            <li>Fallback lokal tetap aktif jika upload utama gagal.</li>
                        </ul>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-link"></i> Navigasi</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-sliders-h"></i> Kembali ke PPDB Settings
                        </a>
                        <a href="{{ route('admin.settings.nomor-rules.index') }}" class="btn btn-outline-success btn-block">
                            <i class="fas fa-hashtag"></i> Pengaturan Penomoran
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Simpan Storage Dokumen
            </button>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif

    function updateStorageBox() {
        const mode = $('#dokumen_storage_mode').val();
        $('#_dokumen_storage_mode_selected').val(mode);
        if (mode === 'gdrive_primary_local_fallback') {
            $('#googleDriveConfigBox').show();
        } else {
            $('#googleDriveConfigBox').hide();
        }
    }

    function updateGoogleDriveAuthBox() {
        const authMode = $('#google_drive_auth_mode').val();
        $('#_google_drive_auth_mode_selected').val(authMode);
        if (authMode === 'oauth') {
            $('#googleDriveOauthBox').show();
            $('#googleDriveServiceAccountBox').hide();
        } else {
            $('#googleDriveOauthBox').hide();
            $('#googleDriveServiceAccountBox').show();
        }
    }

    $('#dokumen_storage_mode').on('change', updateStorageBox);
    $('#google_drive_auth_mode').on('change', updateGoogleDriveAuthBox);
    $('.custom-file-input').on('change', function() {
        const fileName = this.files && this.files.length ? this.files[0].name : 'Pilih file JSON service account';
        $(this).next('.custom-file-label').text(fileName);
    });

    $('form').on('submit', function() {
        $('#_dokumen_storage_mode_selected').val($('#dokumen_storage_mode').val());
        $('#_google_drive_auth_mode_selected').val($('#google_drive_auth_mode').val());
    });

    updateStorageBox();
    updateGoogleDriveAuthBox();
});
</script>
@stop
