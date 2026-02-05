@extends('adminlte::page')

@section('title', 'Detail Penguji - ' . $penguji->name)

@section('css')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 0.5rem;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0 auto 1rem;
    }
    .stat-box {
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-box h3 {
        margin: 0;
        color: #667eea;
    }
    .stat-box p {
        margin: 0.5rem 0 0;
        color: #6c757d;
        font-size: 0.85rem;
    }
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1rem;
        border-left: 2px solid #dee2e6;
    }
    .timeline-item:last-child {
        border-left-color: transparent;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #667eea;
    }
    .active-sesi-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-user-tie mr-2"></i>Detail Penguji
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.penguji.index') }}">Penguji</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Profile Column -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($penguji->name, 0, 2)) }}
                    </div>
                    <h4 class="mb-1">{{ $penguji->name }}</h4>
                    <p class="mb-2 opacity-75">{{ $penguji->email }}</p>
                    <div>
                        @foreach($penguji->roles as $role)
                            <span class="badge badge-light mr-1">{{ $role->display_name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40"><i class="fas fa-phone fa-fw text-muted"></i></td>
                            <td>{{ $penguji->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-toggle-on fa-fw text-muted"></i></td>
                            <td>
                                @if($penguji->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar fa-fw text-muted"></i></td>
                            <td>Bergabung: {{ $penguji->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-clock fa-fw text-muted"></i></td>
                            <td>
                                Login terakhir: 
                                {{ $penguji->last_login_at ? $penguji->last_login_at->diffForHumans() : 'Belum pernah' }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm w-100">
                        <button type="button" class="btn btn-secondary btn-reset-password" 
                                data-id="{{ $penguji->id }}" data-name="{{ $penguji->name }}">
                            <i class="fas fa-key mr-1"></i>Reset Password
                        </button>
                        <form action="{{ route('admin.penguji.toggle-status', $penguji) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ $penguji->is_active ? 'warning' : 'success' }} btn-block"
                                    onclick="return confirm('{{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }} penguji ini?')">
                                <i class="fas {{ $penguji->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-1"></i>
                                {{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- GTK Info Card -->
            @if($gtkData)
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-2"></i>Data GTK
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.gtk.show', $gtkData->id) }}" class="btn btn-tool" title="Lihat di GTK">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th width="40%">NIP</th>
                            <td>{{ $gtkData->nip ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NUPTK</th>
                            <td>{{ $gtkData->nuptk ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jabatan</th>
                            <td>{{ $gtkData->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kategori PTK</th>
                            <td>{{ $gtkData->kategori_ptk ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $gtkData->nomor_hp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Active Sessions -->
            @if($activeSesi->count() > 0)
                <div class="card border-primary">
                    <div class="card-header bg-primary">
                        <h3 class="card-title text-white">
                            <i class="fas fa-clipboard-check mr-2 active-sesi-badge"></i>Sesi Aktif
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($activeSesi as $active)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $active->sesiUjian->nama ?? 'Sesi' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $active->ruangUjian->nama_ruang ?? 'Ruangan' }}
                                                @if($active->is_ketua)
                                                    <span class="badge badge-warning ml-1">Ketua</span>
                                                @endif
                                            </small>
                                        </div>
                                        <span class="badge badge-{{ $active->sesiUjian->status == 'in_progress' ? 'primary' : 'secondary' }}">
                                            {{ $active->sesiUjian->status == 'in_progress' ? 'Berlangsung' : 'Terkunci' }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Stats & History Column -->
        <div class="col-lg-8">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-box">
                        <h3>{{ $nilaiStats['total_dinilai'] }}</h3>
                        <p>Total Dinilai</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h3 class="text-warning">{{ $nilaiStats['draft'] }}</h3>
                        <p>Draft</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h3 class="text-info">{{ $nilaiStats['submitted'] }}</h3>
                        <p>Submitted</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h3 class="text-success">{{ $nilaiStats['verified'] }}</h3>
                        <p>Terverifikasi</p>
                    </div>
                </div>
            </div>

            <!-- Assignment History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>Riwayat Penugasan
                    </h3>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sesi Ujian</th>
                                        <th>Ruangan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Peran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        <tr>
                                            <td>
                                                @if($assignment->sesiUjian)
                                                    <a href="{{ route('admin.sesi-ujian.show', $assignment->sesiUjian->id) }}">
                                                        {{ $assignment->sesiUjian->nama }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->ruangUjian->nama_ruang ?? '-' }}</td>
                                            <td>{{ $assignment->sesiUjian->tanggal?->format('d M Y') ?? '-' }}</td>
                                            <td>
                                                @if($assignment->sesiUjian)
                                                    @switch($assignment->sesiUjian->status)
                                                        @case('draft')
                                                            <span class="badge badge-secondary">Draft</span>
                                                            @break
                                                        @case('locked')
                                                            <span class="badge badge-warning">Terkunci</span>
                                                            @break
                                                        @case('in_progress')
                                                            <span class="badge badge-primary">Berlangsung</span>
                                                            @break
                                                        @case('completed')
                                                            <span class="badge badge-success">Selesai</span>
                                                            @break
                                                        @default
                                                            <span class="badge badge-secondary">-</span>
                                                    @endswitch
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($assignment->is_ketua)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-star mr-1"></i>Ketua
                                                    </span>
                                                @else
                                                    <span class="badge badge-light">Anggota</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat penugasan</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-warning btn-block" data-toggle="modal" data-target="#resetPasswordModal">
                                <i class="fas fa-key mr-1"></i>Reset Password
                            </button>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.penguji.toggle-status', $penguji) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-{{ $penguji->is_active ? 'danger' : 'success' }} btn-block"
                                        onclick="return confirm('{{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }} penguji ini?')">
                                    <i class="fas {{ $penguji->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-1"></i>
                                    {{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.sesi-ujian.index') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-clipboard-check mr-1"></i>Assign ke Sesi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-key mr-2"></i>Reset Password
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.penguji.reset-password', $penguji) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Reset password untuk: <strong>{{ $penguji->name }}</strong></p>
                    
                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="newPassword" required minlength="8">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('newPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirmation" id="confirmPassword" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    var icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Reset Password Modal
    $('.btn-reset-password').on('click', function() {
        $('#resetPasswordModal').modal('show');
    });
});
</script>
@stop
