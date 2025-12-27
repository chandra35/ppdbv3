@extends('adminlte::page')

@section('title', 'Update Token EMIS')

@section('content_header')
    <h1 class="m-0 text-dark">Update Token EMIS</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Token Bearer API EMIS Kemenag</h3>
            </div>

            <form id="formUpdateToken">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle"></i>
                        Token EMIS digunakan untuk fitur <strong>Cek NISN Siswa</strong> pada pendaftaran PPDB. Token ini bersifat JWT dan memiliki masa berlaku sekitar 4-5 jam.
                    </div>

                    @if($tokenData && $tokenData->expires_at)
                    <div class="alert {{ strtotime($tokenData->expires_at) > time() ? 'alert-success' : 'alert-danger' }}">
                        <i class="icon fas {{ strtotime($tokenData->expires_at) > time() ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        <strong>Status Token:</strong>
                        @if(strtotime($tokenData->expires_at) > time())
                            Aktif (Kadaluarsa: {{ \Carbon\Carbon::parse($tokenData->expires_at)->format('d F Y H:i:s') }})
                        @else
                            Kadaluarsa ({{ \Carbon\Carbon::parse($tokenData->expires_at)->format('d F Y H:i:s') }})
                        @endif
                    </div>
                    @elseif($tokenData && empty($tokenData->token))
                    <div class="alert alert-warning">
                        <i class="icon fas fa-exclamation-triangle"></i>
                        <strong>Status Token:</strong> Belum dikonfigurasi
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="current_token">Token Saat Ini</label>
                        <textarea class="form-control" rows="3" id="current_token" readonly>{{ $tokenData && $tokenData->token ? substr($tokenData->token, 0, 50) . '...' . substr($tokenData->token, -20) : 'Belum ada token' }}</textarea>
                        <small class="form-text text-muted">Token ditampilkan sebagian untuk keamanan</small>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="new_token">Token Baru <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('token') is-invalid @enderror" 
                                  rows="5" 
                                  id="new_token" 
                                  name="token" 
                                  placeholder="Paste token JWT baru di sini (format: eyJ0eXAi...)"
                                  required></textarea>
                        <small class="form-text text-muted">
                            Paste token JWT lengkap yang didapat dari API EMIS Kemenag
                        </small>
                        @error('token')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div id="tokenInfo" class="alert alert-secondary d-none">
                        <strong>Info Token:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Format: <span id="formatStatus"></span></li>
                            <li>Expires: <span id="expiryTime"></span></li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-save"></i> Update Token
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-info collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Cara Mendapatkan Token Baru</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ol>
                    <li>Login ke sistem EMIS Kemenag</li>
                    <li>Buka Developer Tools browser (F12)</li>
                    <li>Pergi ke tab <strong>Network</strong></li>
                    <li>Lakukan pencarian NISN atau akses API</li>
                    <li>Cari request API, klik request tersebut</li>
                    <li>Pergi ke tab <strong>Headers</strong></li>
                    <li>Cari <strong>Authorization: Bearer eyJ0eXAi...</strong></li>
                    <li>Copy token setelah kata "Bearer " (tanpa kata Bearer)</li>
                    <li>Paste di form di atas</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Token JWT memiliki masa berlaku terbatas (±4-5 jam). 
                    Jika fitur Cek NISN error, kemungkinan token sudah kadaluarsa.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Fungsi Token</dt>
                    <dd>Mengakses API EMIS Kemenag untuk validasi NISN calon siswa pada pendaftaran PPDB</dd>

                    <dt>Fitur Terkait</dt>
                    <dd>Pendaftaran PPDB Step 1 (Cek NISN)</dd>

                    <dt>Format Token</dt>
                    <dd>JWT (JSON Web Token)</dd>

                    <dt>Masa Berlaku</dt>
                    <dd>±4-5 jam dari waktu generate</dd>

                    <dt>Terakhir Update</dt>
                    <dd>{{ $tokenData && $tokenData->updated_at ? \Carbon\Carbon::parse($tokenData->updated_at)->format('d F Y H:i:s') : '-' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Testing Token</h3>
            </div>
            <div class="card-body">
                <p>Setelah update token, test dengan mencoba fitur pendaftaran PPDB:</p>
                <a href="{{ route('ppdb.register.step1') }}" class="btn btn-block btn-outline-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Test di Halaman Pendaftaran
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // Auto-validate token format on input
    $('#new_token').on('input', function() {
        const token = $(this).val().trim();
        
        if (token.length > 100) {
            validateTokenFormat(token);
        } else {
            $('#tokenInfo').addClass('d-none');
        }
    });

    // Handle form submission
    $('#formUpdateToken').on('submit', function(e) {
        e.preventDefault();
        
        const token = $('#new_token').val().trim();
        
        if (token.length < 100) {
            toastr.error('Token terlalu pendek. Pastikan Anda copy token JWT lengkap.', 'Token Tidak Valid');
            return;
        }

        // Disable submit button
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: '{{ route("admin.update-emis-token.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                token: token
            },
            dataType: 'text', // Get as text first to handle BOM
            success: function(responseText) {
                // Remove BOM if present
                responseText = responseText.replace(/^\uFEFF/, '');
                
                try {
                    const response = JSON.parse(responseText);
                    
                    if (response.success) {
                        let msg = response.message;
                        if (response.expires_at) {
                            msg += '<br><small>Kadaluarsa: ' + response.expires_at + '</small>';
                        }
                        toastr.success(msg, 'Berhasil!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message, 'Gagal');
                        $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
                    }
                } catch (e) {
                    console.log('Parse error:', e, responseText);
                    toastr.error('Error parsing response', 'Error');
                    $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                
                // Try to parse response text manually
                let responseText = xhr.responseText;
                if (responseText) {
                    responseText = responseText.replace(/^\uFEFF/, '');
                    try {
                        const data = JSON.parse(responseText);
                        if (data.success) {
                            let msg = data.message;
                            if (data.expires_at) {
                                msg += '<br><small>Kadaluarsa: ' + data.expires_at + '</small>';
                            }
                            toastr.success(msg, 'Berhasil!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                            return;
                        }
                    } catch (e) {
                        console.log('Parse error in error handler:', e);
                    }
                }
                
                toastr.error('Terjadi kesalahan saat update token', 'Error');
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
            }
        });
    });

    function validateTokenFormat(token) {
        const parts = token.split('.');
        
        if (parts.length === 3) {
            $('#formatStatus').html('<span class="badge badge-success">Valid JWT</span>');
            
            try {
                const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
                
                if (payload.exp) {
                    const expiryDate = new Date(payload.exp * 1000);
                    const now = new Date();
                    
                    if (expiryDate > now) {
                        $('#expiryTime').html('<span class="badge badge-success">' + expiryDate.toLocaleString('id-ID') + '</span>');
                    } else {
                        $('#expiryTime').html('<span class="badge badge-danger">Sudah Kadaluarsa (' + expiryDate.toLocaleString('id-ID') + ')</span>');
                    }
                } else {
                    $('#expiryTime').html('<span class="badge badge-warning">Tidak ada info expiry</span>');
                }
                
                $('#tokenInfo').removeClass('d-none');
            } catch (e) {
                $('#expiryTime').text('Error decode payload');
                $('#tokenInfo').removeClass('d-none');
            }
        } else {
            $('#formatStatus').html('<span class="badge badge-danger">Format tidak valid</span>');
            $('#expiryTime').text('-');
            $('#tokenInfo').removeClass('d-none');
        }
    }
});
</script>
@stop
