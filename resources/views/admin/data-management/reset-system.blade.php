@extends('layouts.admin')

@section('title', 'Reset Data Sistem')

@section('page-title', 'Reset Data Sistem')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reset Sistem</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Warning Alert -->
        <div class="alert alert-danger alert-dismissible">
            <h5><i class="icon fas fa-exclamation-triangle"></i> PERINGATAN!</h5>
            <p class="mb-0">
                Halaman ini digunakan untuk <strong>menghapus permanen</strong> data pendaftar. 
                Data yang dihapus <strong>TIDAK DAPAT</strong> dikembalikan. 
                Gunakan fitur ini hanya untuk persiapan deployment ke production atau reset sistem.
            </p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pendaftar</span>
                <span class="info-box-number">{{ number_format($stats['pendaftar']) }}</span>
                <small>Aktif: {{ $stats['pendaftar_aktif'] }} | Terhapus: {{ $stats['pendaftar_terhapus'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-user-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Akun Pendaftar</span>
                <span class="info-box-number">{{ number_format($stats['users']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Dokumen Upload</span>
                <span class="info-box-number">{{ number_format($stats['dokumen']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-secondary">
            <span class="info-box-icon"><i class="fas fa-hdd"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Storage Terpakai</span>
                <span class="info-box-number">{{ $stats['storage_size'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Verify Password Section -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i>Verifikasi Password</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Masukkan password admin Anda untuk mengaktifkan fitur reset.</p>
                <div class="form-group">
                    <label>Password Admin</label>
                    <div class="input-group">
                        <input type="password" id="adminPassword" class="form-control" placeholder="Masukkan password">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="verifyBtn">
                                <i class="fas fa-check"></i> Verifikasi
                            </button>
                        </div>
                    </div>
                </div>
                <div id="tokenInfo" class="alert alert-success d-none">
                    <i class="fas fa-check-circle"></i> 
                    Password terverifikasi. Token berlaku <span id="tokenExpiry">5 menit</span>.
                </div>
            </div>
        </div>
    </div>

    <!-- Reset by Gelombang -->
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Reset per Gelombang</h3>
            </div>
            <form id="resetGelombangForm" action="{{ route('admin.reset-system.by-gelombang') }}" method="POST">
                @csrf
                <input type="hidden" name="token" id="gelombangToken">
                <div class="card-body">
                    <p class="text-muted">Hapus semua data pendaftar dari gelombang tertentu.</p>
                    <div class="form-group">
                        <label>Pilih Gelombang</label>
                        <select name="gelombang_id" class="form-control" required>
                            <option value="">-- Pilih Gelombang --</option>
                            @foreach(\App\Models\GelombangPendaftaran::withCount('calonSiswas')->get() as $gel)
                            <option value="{{ $gel->id }}">{{ $gel->nama }} ({{ $gel->calon_siswas_count }} pendaftar)</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning reset-btn" disabled>
                        <i class="fas fa-trash-alt"></i> Reset Gelombang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <!-- Reset ALL Data -->
    <div class="col-md-8">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bomb mr-2"></i>Reset SEMUA Data Pendaftar</h3>
            </div>
            <form id="resetAllForm" action="{{ route('admin.reset-system.all') }}" method="POST">
                @csrf
                <input type="hidden" name="token" id="allToken">
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-skull-crossbones"></i> ZONA BERBAHAYA!</h5>
                        <p>Tindakan ini akan menghapus <strong>SEMUA</strong> data berikut secara permanen:</p>
                        <ul>
                            <li><strong>{{ number_format($stats['pendaftar']) }}</strong> data calon siswa (termasuk yang sudah dihapus)</li>
                            <li><strong>{{ number_format($stats['users']) }}</strong> akun pendaftar</li>
                            <li><strong>{{ number_format($stats['ortu']) }}</strong> data orang tua</li>
                            <li><strong>{{ number_format($stats['dokumen']) }}</strong> dokumen upload</li>
                            <li><strong>{{ number_format($stats['nilai_rapor']) }}</strong> data nilai rapor</li>
                            <li>Counter nomor registrasi akan di-reset ke 0</li>
                            <li>Counter nomor tes (per jalur) akan di-reset ke 0</li>
                        </ul>
                        <p class="mb-0"><strong>Data yang TIDAK dihapus:</strong> Pengaturan sekolah, jalur pendaftaran, gelombang, tahun pelajaran, admin users.</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Ketik <code>RESET SEMUA DATA</code> untuk konfirmasi:</label>
                        <input type="text" name="confirm_text" class="form-control" 
                               placeholder="RESET SEMUA DATA" autocomplete="off" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger btn-lg reset-btn" disabled>
                        <i class="fas fa-radiation"></i> RESET SEMUA DATA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Clean Orphaned Files -->
    <div class="col-md-4">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-broom mr-2"></i>Bersihkan File Orphan</h3>
            </div>
            <form id="cleanFilesForm" action="{{ route('admin.reset-system.clean-files') }}" method="POST">
                @csrf
                <input type="hidden" name="token" id="cleanToken">
                <div class="card-body">
                    <p class="text-muted">
                        Hapus file yang tidak memiliki referensi di database 
                        (file yang tersisa dari penghapusan yang tidak sempurna).
                    </p>
                    <p><strong>Storage terpakai:</strong> {{ $stats['storage_size'] }}</p>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-secondary reset-btn" disabled>
                        <i class="fas fa-broom"></i> Bersihkan File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let resetToken = null;

    // Verify password
    $('#verifyBtn').click(function() {
        const password = $('#adminPassword').val();
        if (!password) {
            toastr.error('Masukkan password!');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifikasi...');

        $.ajax({
            url: '{{ route("admin.reset-system.verify") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                password: password
            },
            success: function(response) {
                if (response.success) {
                    resetToken = response.token;
                    
                    // Set token to all forms
                    $('#gelombangToken, #allToken, #cleanToken').val(resetToken);
                    
                    // Enable reset buttons
                    $('.reset-btn').prop('disabled', false);
                    
                    // Show token info
                    $('#tokenInfo').removeClass('d-none');
                    
                    // Start countdown
                    let remaining = 300; // 5 minutes
                    const countdown = setInterval(function() {
                        remaining--;
                        const mins = Math.floor(remaining / 60);
                        const secs = remaining % 60;
                        $('#tokenExpiry').text(mins + ':' + (secs < 10 ? '0' : '') + secs);
                        
                        if (remaining <= 0) {
                            clearInterval(countdown);
                            resetToken = null;
                            $('.reset-btn').prop('disabled', true);
                            $('#tokenInfo').addClass('d-none');
                            toastr.warning('Token sudah kadaluarsa. Verifikasi ulang.');
                        }
                    }, 1000);
                    
                    toastr.success('Password terverifikasi!');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Password salah!');
            },
            complete: function() {
                $('#verifyBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Verifikasi');
            }
        });
    });

    // Confirm reset all
    $('#resetAllForm').submit(function(e) {
        const confirmText = $('input[name="confirm_text"]').val();
        if (confirmText !== 'RESET SEMUA DATA') {
            e.preventDefault();
            toastr.error('Ketik "RESET SEMUA DATA" dengan benar!');
            return false;
        }

        if (!confirm('TERAKHIR KALI!\n\nAnda yakin ingin menghapus SEMUA data pendaftar?\n\nTindakan ini TIDAK DAPAT dibatalkan!')) {
            e.preventDefault();
            return false;
        }
    });

    // Confirm reset gelombang
    $('#resetGelombangForm').submit(function(e) {
        if (!confirm('Anda yakin ingin menghapus semua data dari gelombang ini?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
