@extends('adminlte::page')

@section('title', 'Detail Pendaftar')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* Box Style */
.box {
    border-radius: 3px;
    border-top: 3px solid #d2d6de;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    background-color: #ffffff;
}
.box-primary {
    border-top-color: #3c8dbc;
}
.box-solid {
    border: 1px solid #d2d6de;
}
.box-solid > .box-header {
    padding: 10px;
    background: #f4f4f4;
}
.box-header {
    padding: 10px;
    position: relative;
}
.box-header.with-border {
    border-bottom: 1px solid #f4f4f4;
}
.box-title {
    display: inline-block;
    font-size: 18px;
    margin: 0;
    line-height: 1;
    font-weight: 600;
}
.box-body {
    padding: 10px;
}
.box-body.no-padding {
    padding: 0;
}
.box-body.box-profile {
    padding: 20px;
}

/* Description Block */
.description-block {
    display: block;
    margin: 10px 0;
    text-align: center;
}
.description-header {
    margin: 0;
    padding: 0;
    font-weight: 600;
    font-size: 20px;
}
.description-text {
    text-transform: uppercase;
    display: block;
    font-size: 12px;
    color: #999;
}

/* Label */
.label {
    display: inline;
    padding: .3em .6em;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: .25em;
}
.label-warning { background-color: #f39c12; }
.label-info { background-color: #00c0ef; }
.label-success { background-color: #00a65a; }
.label-danger { background-color: #dd4b39; }

/* Table */
.table-hover > tbody > tr:hover {
    background-color: #f5f5f5;
}
.table-condensed > tbody > tr > td,
.table-condensed > tbody > tr > th {
    padding: 8px;
}

/* DL Horizontal */
.dl-horizontal dt {
    width: 100px;
    text-align: left;
    font-weight: 600;
    color: #666;
}
.dl-horizontal dd {
    margin-left: 120px;
}

/* Background Colors */
.bg-aqua { background-color: #00c0ef !important; color: #fff !important; }
.bg-red { background-color: #dd4b39 !important; color: #fff !important; }

/* Dokumen Card */
.dokumen-card {
    cursor: pointer;
    transition: all 0.3s ease;
}
.dokumen-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.dokumen-card img {
    height: 150px;
    object-fit: cover;
}

/* Toast notification */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
.toast-notification {
    animation: slideInRight 0.3s ease;
}

/* Modal Approve Animation */
@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

#approveDokumenModal {
    z-index: 10060 !important;
}

#approveDokumenModal .modal-backdrop {
    z-index: 10050 !important;
}

#approveDokumenModal .modal-dialog {
    animation: bounceIn 0.5s ease;
}

#approveDokumenModal .modal-content {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    border-radius: 15px;
    overflow: hidden;
}

#approveDokumenModal .modal-header {
    border: none;
    padding: 1.5rem;
}

#approveDokumenModal .modal-body {
    padding: 2rem;
}

#approveDokumenModal .modal-footer {
    border: none;
    padding: 1.5rem;
}

#approveDokumenModal .btn {
    border-radius: 25px;
    padding: 0.6rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

#approveDokumenModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #dee2e6;
    left: 31px;
    margin: 0;
}

.timeline > div {
    position: relative;
    margin-bottom: 15px;
}

.timeline > div > .timeline-item {
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #495057;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}

.timeline > div > .fas,
.timeline > div > .far,
.timeline > div > .fab {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #fff;
    background: #6c757d;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}

.timeline > div > .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}

.timeline > div > .timeline-item > .timeline-body {
    padding: 15px;
}

.timeline > div > .timeline-item > .timeline-footer {
    padding: 10px;
    background-color: #f4f4f4;
    border-top: 1px solid #dee2e6;
}

/* Card Custom Styling */
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.card-header {
    border-radius: 10px 10px 0 0;
    background: linear-gradient(to right, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
}

/* Widget User Custom */
.widget-user .widget-user-header {
    border-radius: 10px 10px 0 0;
    height: 150px;
    position: relative;
}
.widget-user .widget-user-username {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}
.widget-user .widget-user-desc {
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}
.widget-user .widget-user-image {
    border: 5px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.widget-user .description-block {
    padding: 5px 0;
}
.widget-user .description-block .description-header {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
}

/* Progress Bar Custom */
.progress {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 20px;
    overflow: hidden;
}
.progress-bar {
    border-radius: 20px;
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    box-shadow: 0 2px 4px rgba(40,167,69,0.4);
    transition: width 1s ease-in-out;
}

/* Description List Styling */
dl.row dt {
    font-weight: 600;
    color: #495057;
    padding: 8px 0;
}rd-info.card-outline {
    border-top: 3px solid #17a2b8;
}
.card-success.card-outline {
    border-top: 3px solid #28a745;
}
.card-warning.card-outline {
    border-top: 3px solid #ffc107;
}

/* Data Orang Tua Custom Card */
.bg-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}
.bg-light:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Badge Custom */
.badge {
    padding: 6px 12px;
    font-weight: 600;
    border-radius: 20px;
    font-size: 0.85rem;
}

/* Button Custom */
.btn-lg {
    padding: 12px 20px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .widget-user .description-block {
        text-align: center;
    }
}

/* Button Purple */
.btn-purple {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
.btn-purple:hover {
    color: #fff;
    background-color: #5a32a3;
    border-color: #533099;
}
.btn-purple:focus, .btn-purple.focus {
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.5);
}
.btn-purple:disabled, .btn-purple.disabled {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
    opacity: 0.65;
}
.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
}
.btn-outline-purple:hover {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
.bg-purple {
    background-color: #6f42c1 !important;
}

/* Upload Dokumen Modal Camera */
#uploadDokumenModal .modal-content {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    border-radius: 15px;
}
#uploadDokumenModal .modal-header {
    border-radius: 15px 15px 0 0;
}
#cameraVideo {
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
#capturedImage, #filePreview {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
}
.custom-file-label::after {
    content: "Browse";
}
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Detail Pendaftar</h1>
        </div>
        <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.pendaftar.index') }}">Pendaftar</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
            @if(session('new_password'))
                <hr>
                <strong><i class="fas fa-key"></i> Password Baru:</strong> 
                <code id="newPassword" style="font-size: 16px; background: #fff; padding: 5px 10px; border-radius: 3px;">{{ session('new_password') }}</code>
                <button type="button" class="btn btn-xs btn-default ml-2" onclick="copyPassword('{{ session('new_password') }}')">
                    <i class="fas fa-copy"></i> Salin
                </button>
            @endif
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif
    
    <div class="card card-outline card-primary mb-3">
        <div class="card-body p-2">
            <div class="row">
                <div class="col-md-8">
                    <div class="btn-group mr-2">
                        <a href="{{ route('admin.pendaftar.edit', $pendaftar->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Lengkap
                        </a>
                        <button type="button" class="btn btn-warning btn-sm" onclick="resetPassword()">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="showPassword()">
                            <i class="fas fa-eye"></i> Lihat Password
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#sendEmailModal">
                            <i class="fas fa-envelope"></i> Kirim Email
                        </button>
                        @if($pendaftar->is_finalisasi)
                            @php
                                $gelombangClosed = $pendaftar->gelombangPendaftaran 
                                    && in_array($pendaftar->gelombangPendaftaran->computed_status, ['closed', 'finished']);
                            @endphp
                            @if($gelombangClosed)
                                <button type="button" class="btn btn-secondary btn-sm" disabled title="Gelombang {{ $pendaftar->gelombangPendaftaran->nama }} sudah ditutup">
                                    <i class="fas fa-lock"></i> Batal Finalisasi <small>(Gelombang Ditutup)</small>
                                </button>
                            @else
                                <button type="button" class="btn btn-danger btn-sm" onclick="batalFinalisasi()">
                                    <i class="fas fa-unlock"></i> Batal Finalisasi
                                </button>
                            @endif
                        @endif
                    </div>
                    
                    {{-- Tombol Cetak & Upload --}}
                    <div class="btn-group mr-2">
                        @if($pendaftar->is_finalisasi && auth()->user()->hasPermission('pendaftar.cetak-registrasi'))
                        <a href="{{ route('admin.pendaftar.cetak-registrasi', $pendaftar->id) }}" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-print"></i> Cetak Registrasi
                        </a>
                        @endif
                        @if($pendaftar->is_finalisasi && $pendaftar->nomor_tes && auth()->user()->hasPermission('pendaftar.cetak-ujian'))
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#kartuUjianModal">
                            <i class="fas fa-id-card"></i> Cetak Kartu Ujian
                        </button>
                        @endif
                        @if(auth()->user()->hasPermission('pendaftar.upload-dokumen'))
                        <button type="button" class="btn btn-purple btn-sm" data-toggle="modal" data-target="#uploadDokumenModal">
                            <i class="fas fa-camera"></i> Upload Dokumen
                        </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($pendaftar->nomor_tes && auth()->user()->hasPermission('verifikasi.verify'))
    <div class="card card-outline {{ $nomorTesUndoInfo['can_undo'] ? 'card-warning' : 'card-secondary' }} mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-history"></i> Generate Nomor Tes Terakhir
            </h3>
        </div>
        <div class="card-body p-2">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Nomor tes pendaftar</small>
                    <strong>{{ $pendaftar->nomor_tes }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Nomor terakhir sequence</small>
                    <strong>{{ $nomorTesUndoInfo['sequence']?->last_generated_value ?? '-' }}</strong>
                    <small class="text-muted d-block">
                        {{ $nomorTesUndoInfo['sequence']?->last_generated_at ? $nomorTesUndoInfo['sequence']->last_generated_at->format('d/m/Y H:i') : '-' }}
                    </small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Pemilik nomor terakhir</small>
                    <strong>{{ $nomorTesUndoInfo['last_owner']?->nama_lengkap ?? '-' }}</strong>
                    <small class="text-muted d-block">{{ $nomorTesUndoInfo['last_owner']?->nisn ?? '-' }}</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Nomor sebelumnya</small>
                    <strong>{{ $nomorTesUndoInfo['previous_owner']?->nomor_tes ?? '-' }}</strong>
                    <small class="text-muted d-block">{{ $nomorTesUndoInfo['previous_owner']?->nama_lengkap ?? '-' }}</small>
                </div>
            </div>
            <div class="row mt-2 align-items-center">
                <div class="col-md-9">
                    <small class="text-muted d-block">Log cetak kartu ujian terakhir</small>
                    @if($nomorTesUndoInfo['latest_print_log'])
                        <span>
                            {{ $nomorTesUndoInfo['latest_print_log']->description }}
                            <small class="text-muted">({{ $nomorTesUndoInfo['latest_print_log']->created_at->format('d/m/Y H:i') }})</small>
                        </span>
                    @else
                        <span class="text-muted">Belum ada log cetak kartu ujian untuk pendaftar ini.</span>
                    @endif
                    @if(!$nomorTesUndoInfo['can_undo'])
                        <div class="text-muted mt-1">
                            <i class="fas fa-info-circle"></i> {{ $nomorTesUndoInfo['reason'] }}
                        </div>
                    @endif
                </div>
                <div class="col-md-3 text-right">
                    <button type="button"
                            class="btn btn-warning btn-sm"
                            onclick="batalGenerateNomorTes()"
                            {{ $nomorTesUndoInfo['can_undo'] ? '' : 'disabled' }}>
                        <i class="fas fa-undo"></i> Batal Nomor Tes Terakhir
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @php
        // Exclude dokumen tambahan dari statistik verifikasi (hanya hitung dokumen yang diperlukan/required)
        $dokumenTambahanKeys = array_keys(\App\Models\CalonDokumen::DOKUMEN_TAMBAHAN);
        $dokumenRequired = $pendaftar->dokumen->whereNotIn('jenis_dokumen', $dokumenTambahanKeys);
        $dokumenCount = $dokumenRequired->count();
        $validCount = $dokumenRequired->where('status_verifikasi', 'valid')->count();
        $pendingCount = $dokumenRequired->where('status_verifikasi', 'pending')->count();
        $invalidCount = $dokumenRequired->where('status_verifikasi', 'invalid')->count();
        $revisionCount = $dokumenRequired->where('status_verifikasi', 'revision')->count();
    @endphp

    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4">
            <!-- Profile Box -->
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <div class="text-center">
                        @php
                            // Prioritas foto: 1. Dokumen foto yang sudah diupload, 2. Foto upload manual, 3. Avatar
                            $pasFoto = $pendaftar->dokumen->where('jenis_dokumen', 'foto')->first();
                            
                            if($pasFoto && $pasFoto->preview_url) {
                                // Gunakan foto dari dokumen yang sudah diupload
                                $avatarSrc = $pasFoto->preview_url;
                                $useInitials = false;
                            } elseif($pendaftar->foto && file_exists(public_path('storage/' . $pendaftar->foto))) {
                                // Gunakan foto upload manual (jika ada)
                                $avatarSrc = asset('storage/' . $pendaftar->foto);
                                $useInitials = false;
                            } else {
                                // Gunakan UI Avatars dengan inisial nama
                                $nama = $pendaftar->nama_lengkap;
                                $initials = '';
                                $words = explode(' ', $nama);
                                foreach($words as $index => $word) {
                                    if($index < 2 && !empty($word)) {
                                        $initials .= strtoupper(substr($word, 0, 1));
                                    }
                                }
                                
                                // Warna berdasarkan jenis kelamin
                                if($pendaftar->jenis_kelamin == 'L') {
                                    $bgColor = '3498db'; // Biru untuk laki-laki
                                } elseif($pendaftar->jenis_kelamin == 'P') {
                                    $bgColor = 'e74c3c'; // Merah untuk perempuan
                                } else {
                                    $bgColor = '95a5a6'; // Abu-abu default
                                }
                                
                                $avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=160&background=' . $bgColor . '&color=ffffff&bold=true';
                                $useInitials = true;
                            }
                        @endphp
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $avatarSrc }}"
                             alt="User profile picture"
                             @if($useInitials) onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22160%22 height=%22160%22%3E%3Crect width=%22160%22 height=%22160%22 fill=%22%23{{ $bgColor }}%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23fff%22 font-size=%2260%22 font-weight=%22bold%22 font-family=%22Arial%22%3E{{ $initials }}%3C/text%3E%3C/svg%3E'" @endif>
                    </div>
                    <h3 class="profile-username text-center">{{ $pendaftar->nama_lengkap }}</h3>
                    <p class="text-center mb-3">
                        @if($pendaftar->status_verifikasi == 'pending')
                            <span class="label label-warning">Pending Verifikasi</span>
                        @elseif($pendaftar->status_verifikasi == 'verified')
                            <span class="label label-info">Terverifikasi</span>
                        @elseif($pendaftar->status_verifikasi == 'approved')
                            <span class="label label-success">Diterima</span>
                        @elseif($pendaftar->status_verifikasi == 'rejected')
                            <span class="label label-danger">Ditolak</span>
                        @endif
                    </p>

                    <table class="table table-condensed">
                        <tr>
                            <td><i class="fas fa-id-card text-muted"></i> <strong>NISN</strong></td>
                            <td class="text-right">{{ $pendaftar->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-envelope text-muted"></i> <strong>Email</strong></td>
                            <td class="text-right"><small>{{ $pendaftar->email ?? '-' }}</small></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-phone text-muted"></i> <strong>No HP</strong></td>
                            <td class="text-right">{{ $pendaftar->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-road text-muted"></i> <strong>Jalur</strong></td>
                            <td class="text-right">
                                @if($pendaftar->jalurPendaftaran)
                                    <span class="label" style="background: {{ $pendaftar->jalurPendaftaran->warna ?? '#007bff' }}">
                                        {{ $pendaftar->jalurPendaftaran->nama }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-graduation-cap text-muted"></i> <strong>Pilihan Program</strong></td>
                            <td class="text-right">
                                @if($pendaftar->pilihan_program == 'Reguler')
                                    <span class="label label-info">Reguler</span>
                                @elseif($pendaftar->pilihan_program == 'Asrama')
                                    <span class="label label-success">Asrama</span>
                                @else
                                    <span class="label label-secondary">Belum Memilih</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar text-muted"></i> <strong>Terdaftar</strong></td>
                            <td class="text-right"><small>{{ $pendaftar->created_at->format('d/m/Y') }}</small></td>
                        </tr>
                        @if($pendaftar->riwayatGelombang && $pendaftar->riwayatGelombang->count() > 0)
                        <tr>
                            <td><i class="fas fa-exchange-alt text-warning"></i> <strong>Pindah Gelombang</strong></td>
                            <td class="text-right">
                                <span class="label label-warning">{{ $pendaftar->riwayatGelombang->count() }}x pindah</span>
                            </td>
                        </tr>
                        @endif
                        </tr>
                        @if($pendaftar->hasRegistrationCoordinates())
                        <tr>
                            <td><i class="fas fa-map-marker-alt text-danger"></i> <strong>Lokasi Daftar</strong></td>
                            <td class="text-right">
                                <a href="{{ $pendaftar->registration_maps_url }}" target="_blank" class="btn btn-xs btn-success" title="Lihat di Maps">
                                    <i class="fas fa-map"></i> Maps
                                </a>
                            </td>
                        </tr>
                        @endif
                    </table>
                    
                    <div class="row mt-3">
                        <div class="col-xs-4 text-center">
                            <div class="description-block">
                                <h5 class="description-header text-success">{{ $validCount }}</h5>
                                <span class="description-text">Valid</span>
                            </div>
                        </div>
                        <div class="col-xs-4 text-center">
                            <div class="description-block">
                                <h5 class="description-header text-warning">{{ $pendingCount }}</h5>
                                <span class="description-text">Pending</span>
                            </div>
                        </div>
                        <div class="col-xs-4 text-center">
                            <div class="description-block">
                                <h5 class="description-header text-danger">{{ $invalidCount }}</h5>
                                <span class="description-text">Ditolak</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="col-md-8">
            <!-- Data Pribadi & Sekolah (Compact) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-user"></i> Data Pribadi & Sekolah</h3>
                </div>
                <div class="box-body">
                    <table class="table table-sm table-borderless" style="font-size: 13px;">
                        <tbody>
                            <tr>
                                <td width="30%" class="p-1 text-muted"><i class="fas fa-user"></i> Nama</td>
                                <td class="p-1"><strong>{{ $pendaftar->nama_lengkap ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-id-card-alt"></i> NIK</td>
                                <td class="p-1">{{ $pendaftar->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-venus-mars"></i> Jenis Kelamin</td>
                                <td class="p-1">
                                    @if($pendaftar->jenis_kelamin == 'L')
                                        <i class="fas fa-mars text-info"></i> Laki-laki
                                    @elseif($pendaftar->jenis_kelamin == 'P')
                                        <i class="fas fa-venus text-danger"></i> Perempuan
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-birthday-cake"></i> TTL</td>
                                <td class="p-1">{{ $pendaftar->tempat_lahir ?? '-' }}, {{ $pendaftar->tanggal_lahir ? \Carbon\Carbon::parse($pendaftar->tanggal_lahir)->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-pray"></i> Agama</td>
                                <td class="p-1">{{ ucfirst($pendaftar->agama ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-phone"></i> No. HP</td>
                                <td class="p-1">
                                    @if($pendaftar->nomor_hp)
                                        <i class="fas fa-phone text-success"></i> {{ $pendaftar->nomor_hp }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted"><i class="fas fa-envelope"></i> Email</td>
                                <td class="p-1">{{ $pendaftar->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted align-top"><i class="fas fa-home"></i> Alamat</td>
                                <td class="p-1">
                                    @if($pendaftar->alamat_siswa)
                                        {{ $pendaftar->alamat_siswa }}
                                        @if($pendaftar->rt_siswa || $pendaftar->rw_siswa)
                                            , RT {{ $pendaftar->rt_siswa ?? '-' }}/RW {{ $pendaftar->rw_siswa ?? '-' }}
                                        @endif
                                        @if($pendaftar->kelurahanSiswa)
                                            <br><small class="text-muted">{{ $pendaftar->kelurahanSiswa->name ?? '' }}, {{ $pendaftar->kecamatanSiswa->name ?? '' }}, {{ $pendaftar->kabupatenSiswa->name ?? '' }}, {{ $pendaftar->provinsiSiswa->name ?? '' }}@if($pendaftar->kodepos_siswa) {{ $pendaftar->kodepos_siswa }}@endif</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="p-1 text-muted align-top"><i class="fas fa-school"></i> Asal Sekolah</td>
                                <td class="p-1">
                                    @if($pendaftar->nama_sekolah_asal)
                                        <strong>{{ $pendaftar->nama_sekolah_asal }}</strong>
                                        @if($pendaftar->npsn_asal_sekolah)
                                            <br><small class="text-muted">NPSN: {{ $pendaftar->npsn_asal_sekolah }}</small>
                                        @endif
                                        @if($pendaftar->nsm_asal_sekolah)
                                            <small class="text-muted"> | NSM: {{ $pendaftar->nsm_asal_sekolah }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Data Keluarga & Orang Tua (Compact) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-users"></i> Data Keluarga</h3>
                </div>
                <div class="box-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card text-primary"></i> No. Kartu Keluarga</strong>
                            <p class="text-muted mb-0">{{ optional($pendaftar->ortu)->no_kk ?? '-' }}</p>
                        </div>
                        @if(optional($pendaftar->ortu)->alamat_ortu)
                        <div class="col-md-6">
                            <strong><i class="fas fa-home text-primary"></i> Alamat Orang Tua</strong>
                            <p class="text-muted mb-0 small">
                                {{ $pendaftar->ortu->alamat_ortu }}
                                @if($pendaftar->ortu->rt_ortu || $pendaftar->ortu->rw_ortu)
                                    , RT {{ $pendaftar->ortu->rt_ortu ?? '-' }}/RW {{ $pendaftar->ortu->rw_ortu ?? '-' }}
                                @endif
                                @if($pendaftar->ortu->kelurahanOrtu)
                                    <br>{{ $pendaftar->ortu->kelurahanOrtu->name ?? '' }}, {{ $pendaftar->ortu->kecamatanOrtu->name ?? '' }}
                                    <br>{{ $pendaftar->ortu->kabupatenOrtu->name ?? '' }}, {{ $pendaftar->ortu->provinsiOrtu->name ?? '' }}
                                    @if($pendaftar->ortu->kodepos) {{ $pendaftar->ortu->kodepos }}@endif
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <!-- Data Ayah Compact -->
                        <div class="col-md-6">
                            <div style="border-left: 3px solid #17a2b8; padding-left: 10px; margin-bottom: 15px;">
                                <strong style="color: #17a2b8;"><i class="fas fa-male"></i> Ayah</strong>
                                <table class="table table-sm table-borderless mt-2" style="font-size: 13px;">
                                    <tr>
                                        <td width="35%" class="p-1 text-muted">Nama</td>
                                        <td class="p-1"><strong>{{ optional($pendaftar->ortu)->nama_ayah ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">NIK</td>
                                        <td class="p-1">{{ optional($pendaftar->ortu)->nik_ayah ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pekerjaan</td>
                                        <td class="p-1">{{ \App\Models\CalonOrtu::PEKERJAAN[optional($pendaftar->ortu)->pekerjaan_ayah] ?? optional($pendaftar->ortu)->pekerjaan_ayah ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pendidikan</td>
                                        <td class="p-1">{{ \App\Models\CalonOrtu::PENDIDIKAN[optional($pendaftar->ortu)->pendidikan_ayah] ?? optional($pendaftar->ortu)->pendidikan_ayah ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">No. HP</td>
                                        <td class="p-1">
                                            @if(optional($pendaftar->ortu)->hp_ayah)
                                                <i class="fas fa-phone text-success"></i> {{ $pendaftar->ortu->hp_ayah }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Data Ibu Compact -->
                        <div class="col-md-6">
                            <div style="border-left: 3px solid #dc3545; padding-left: 10px; margin-bottom: 15px;">
                                <strong style="color: #dc3545;"><i class="fas fa-female"></i> Ibu</strong>
                                <table class="table table-sm table-borderless mt-2" style="font-size: 13px;">
                                    <tr>
                                        <td width="35%" class="p-1 text-muted">Nama</td>
                                        <td class="p-1"><strong>{{ optional($pendaftar->ortu)->nama_ibu ?? '-' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">NIK</td>
                                        <td class="p-1">{{ optional($pendaftar->ortu)->nik_ibu ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pekerjaan</td>
                                        <td class="p-1">{{ \App\Models\CalonOrtu::PEKERJAAN[optional($pendaftar->ortu)->pekerjaan_ibu] ?? optional($pendaftar->ortu)->pekerjaan_ibu ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pendidikan</td>
                                        <td class="p-1">{{ \App\Models\CalonOrtu::PENDIDIKAN[optional($pendaftar->ortu)->pendidikan_ibu] ?? optional($pendaftar->ortu)->pendidikan_ibu ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">No. HP</td>
                                        <td class="p-1">
                                            @if(optional($pendaftar->ortu)->hp_ibu)
                                                <i class="fas fa-phone text-success"></i> {{ $pendaftar->ortu->hp_ibu }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nilai Rapor -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-line"></i> Nilai Rapor</h3>
                    <div class="box-tools pull-right">
                        @if($pendaftar->nilaiRapor && $pendaftar->nilaiRapor->count() > 0)
                            <span class="label label-success">{{ $pendaftar->nilaiRapor->count() }} Semester</span>
                        @else
                            <span class="label label-secondary">Belum Diisi</span>
                        @endif
                    </div>
                </div>
                <div class="box-body">
                    @if($pendaftar->nilaiRapor && $pendaftar->nilaiRapor->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" style="font-size: 12px;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-center" style="width: 100px;">Semester</th>
                                        <th class="text-center">Matematika</th>
                                        <th class="text-center">IPA</th>
                                        <th class="text-center">IPS</th>
                                        <th class="text-center">Rata-Rata</th>
                                        <th class="text-center" style="width: 120px;">Dokumen</th>
                                        <th class="text-center" style="width: 180px;">Validasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalRataRata = 0;
                                        $countSemester = 0;
                                    @endphp
                                    @foreach($pendaftar->nilaiRapor->sortBy('semester') as $nilai)
                                        @php
                                            $totalRataRata += $nilai->rata_rata;
                                            $countSemester++;
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-info">Semester {{ $nilai->semester }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong class="{{ $nilai->matematika >= 75 ? 'text-success' : ($nilai->matematika >= 60 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $nilai->matematika }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <strong class="{{ $nilai->ipa >= 75 ? 'text-success' : ($nilai->ipa >= 60 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $nilai->ipa }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <strong class="{{ $nilai->ips >= 75 ? 'text-success' : ($nilai->ips >= 60 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $nilai->ips }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $nilai->rata_rata >= 75 ? 'badge-success' : ($nilai->rata_rata >= 60 ? 'badge-warning' : 'badge-danger') }}" style="font-size: 11px;">
                                                    {{ number_format($nilai->rata_rata, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($nilai->hasDokumen())
                                                    <a href="javascript:void(0);"
                                                       class="btn btn-xs btn-info dokumen-link"
                                                       data-url="{{ $nilai->preview_url }}"
                                                       data-title="Rapor Semester {{ $nilai->semester }}"
                                                       data-dokumen-id="rapor-{{ $nilai->id }}"
                                                       data-dokumen-status="{{ $nilai->status_validasi }}"
                                                       data-jenis-dokumen="rapor_sem_{{ $nilai->semester }}"
                                                       data-type="{{ $nilai->dokumen_type }}"
                                                       data-nilai-matematika="{{ $nilai->matematika }}"
                                                       data-nilai-ipa="{{ $nilai->ipa }}"
                                                       data-nilai-ips="{{ $nilai->ips }}"
                                                       data-nilai-rata="{{ number_format($nilai->rata_rata, 2) }}"
                                                       title="Lihat Dokumen">
                                                        <i class="fas fa-{{ $nilai->dokumen_type === 'image' ? 'image' : 'file-pdf' }}"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="text-muted"><i class="fas fa-minus-circle"></i> Belum ada</span>
                                                @endif
                                            </td>
                                            <td class="text-center" id="rapor-validasi-{{ $nilai->id }}">
                                                @if($nilai->hasDokumen())
                                                    @if($nilai->status_validasi === 'pending')
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-success btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="valid" title="Validasi">
                                                                <i class="fas fa-check"></i> Valid
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="invalid" title="Tolak">
                                                                <i class="fas fa-times"></i> Tolak
                                                            </button>
                                                        </div>
                                                    @elseif($nilai->status_validasi === 'valid')
                                                        <span class="badge badge-success mb-1"><i class="fas fa-check-circle"></i> Valid</span>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-warning btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="pending" title="Batal Verifikasi">
                                                                <i class="fas fa-undo"></i> Batal
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="invalid" title="Tolak">
                                                                <i class="fas fa-times"></i> Tolak
                                                            </button>
                                                        </div>
                                                        <br><small class="text-muted">{{ $nilai->validated_at ? $nilai->validated_at->format('d/m H:i') : '' }}</small>
                                                    @else
                                                        <span class="badge badge-danger mb-1"><i class="fas fa-times-circle"></i> Ditolak</span>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-success btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="valid" title="Validasi">
                                                                <i class="fas fa-check"></i> Valid
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-xs btn-validasi-rapor" 
                                                                    data-id="{{ $nilai->id }}" data-status="pending" title="Batal Tolak">
                                                                <i class="fas fa-undo"></i> Batal
                                                            </button>
                                                        </div>
                                                        @if($nilai->catatan_validasi)
                                                            <br><small class="text-danger" title="{{ $nilai->catatan_validasi }}"><i class="fas fa-comment"></i> {{ Str::limit($nilai->catatan_validasi, 20) }}</small>
                                                        @endif
                                                        <br><small class="text-muted">{{ $nilai->validated_at ? $nilai->validated_at->format('d/m H:i') : '' }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Rata-Rata Keseluruhan:</strong></td>
                                        <td class="text-center">
                                            @php
                                                $rataRataTotal = $countSemester > 0 ? $totalRataRata / $countSemester : 0;
                                            @endphp
                                            <span class="badge {{ $rataRataTotal >= 75 ? 'badge-success' : ($rataRataTotal >= 60 ? 'badge-warning' : 'badge-danger') }}" style="font-size: 12px; padding: 5px 10px;">
                                                {{ number_format($rataRataTotal, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Progress Bar Visualisasi -->
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Matematika (Avg)</span>
                                        <span class="info-box-number">{{ number_format($pendaftar->nilaiRapor->avg('matematika'), 1) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon"><i class="fas fa-flask"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">IPA (Avg)</span>
                                        <span class="info-box-number">{{ number_format($pendaftar->nilaiRapor->avg('ipa'), 1) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon"><i class="fas fa-globe"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">IPS (Avg)</span>
                                        <span class="info-box-number">{{ number_format($pendaftar->nilaiRapor->avg('ips'), 1) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Pendaftar belum mengisi data nilai rapor</p>
                            <small class="text-muted">Data nilai rapor akan ditampilkan setelah pendaftar mengisinya</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dokumen Pendaftaran -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-file-alt"></i> Dokumen Pendaftaran</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-success">{{ $validCount }}</span>
                        <span class="label label-warning">{{ $pendingCount }}</span>
                        <span class="label label-danger">{{ $invalidCount }}</span>
                    </div>
                </div>
                <div class="box-body" style="padding: 8px;">
                    @if(count($requiredDocs) > 0)
                        <div class="row" style="margin: 0 -3px;">
                            @foreach($requiredDocs as $docType)
                            @php
                                $dokumen = $pendaftar->dokumen->firstWhere('jenis_dokumen', $docType);
                                $docLabel = $dokumenLabels[$docType] ?? ucfirst(str_replace('_', ' ', $docType));
                            @endphp
                            <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6 mb-2" style="padding: 0 3px;">
                                <div class="box box-widget dokumen-card" style="margin-bottom: 0; border: 1px solid #ddd;">
                                    @if($dokumen)
                                        @php
                                            $extension = strtolower(pathinfo($dokumen->file_path, PATHINFO_EXTENSION));
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                            $isPdf = $extension === 'pdf';
                                        @endphp
                                        @if($isImage)
                                            <a href="{{ $dokumen->preview_url }}" 
                                               class="dokumen-link"
                                               data-url="{{ $dokumen->preview_url }}"
                                               data-title="{{ $docLabel }}"
                                               data-dokumen-id="{{ $dokumen->id }}"
                                               data-dokumen-status="{{ $dokumen->status_verifikasi }}"
                                               data-jenis-dokumen="{{ $dokumen->jenis_dokumen }}"
                                               data-type="image">
                                                <img src="{{ $dokumen->preview_url }}" class="card-img-top" style="height: 85px; object-fit: cover;">
                                            </a>
                                        @else
                                            <a href="javascript:void(0);"
                                               class="dokumen-link"
                                               data-url="{{ $dokumen->preview_url }}"
                                               data-title="{{ $docLabel }}"
                                               data-dokumen-id="{{ $dokumen->id }}"
                                               data-dokumen-status="{{ $dokumen->status_verifikasi }}"
                                               data-jenis-dokumen="{{ $dokumen->jenis_dokumen }}"
                                               data-type="pdf">
                                                <div class="card-img-top bg-danger d-flex align-items-center justify-content-center" style="height: 85px;">
                                                    <div class="text-center text-white">
                                                        <i class="fas fa-file-pdf fa-2x"></i>
                                                        <div style="font-size: 9px;">PDF</div>
                                                    </div>
                                                </div>
                                            </a>
                                        @endif
                                        <div class="card-body" style="padding: 5px;">
                                            <div style="font-size: 10px; font-weight: 600; margin-bottom: 3px; line-height: 1.2;">{{ $docLabel }}</div>
                                            <div style="margin-bottom: 4px;">
                                                @if($dokumen->status_verifikasi == 'pending')
                                                    <span class="badge badge-warning" style="font-size: 8px; padding: 2px 4px;">Pending</span>
                                                @elseif($dokumen->status_verifikasi == 'valid')
                                                    <span class="badge badge-success" style="font-size: 8px; padding: 2px 4px;">Valid</span>
                                                @elseif($dokumen->status_verifikasi == 'revision')
                                                    <span class="badge badge-info" style="font-size: 8px; padding: 2px 4px;">Revisi</span>
                                                @endif
                                            </div>
                                            
                                                @if($dokumen->status_verifikasi == 'pending')
                                                <div class="btn-group d-flex" role="group">
                                                    <button type="button" class="btn btn-success flex-fill approve-card-btn" data-dokumen-id="{{ $dokumen->id }}" title="Setujui" style="font-size: 9px; padding: 3px;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning flex-fill" data-toggle="modal" data-target="#revisiDokumenModal{{ $dokumen->id }}" title="Minta Revisi" style="font-size: 9px; padding: 3px;">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </div>
                                                @elseif($dokumen->status_verifikasi == 'valid')
                                                <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#revisiDokumenModal{{ $dokumen->id }}" style="font-size: 9px; padding: 3px; margin-bottom: 2px;">
                                                    <i class="fas fa-redo"></i> Revisi
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-block" data-toggle="modal" data-target="#cancelDokumenModal{{ $dokumen->id }}" style="font-size: 9px; padding: 3px;">
                                                    <i class="fas fa-ban"></i> Batal
                                                </button>
                                                @elseif($dokumen->status_verifikasi == 'revision')
                                                <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#cancelRevisiModal{{ $dokumen->id }}" style="font-size: 9px; padding: 3px;">
                                                    <i class="fas fa-undo"></i> Batal Revisi
                                                </button>
                                                @endif
                                        </div>
                                    @else
                                        {{-- Placeholder for missing document --}}
                                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 85px; opacity: 0.5;">
                                            <div class="text-center text-white">
                                                <i class="fas fa-file fa-2x"></i>
                                                <div style="font-size: 9px;">Belum Upload</div>
                                            </div>
                                        </div>
                                        <div class="card-body" style="padding: 5px;">
                                            <div style="font-size: 10px; font-weight: 600; margin-bottom: 3px; line-height: 1.2;">{{ $docLabel }}</div>
                                            <div style="margin-bottom: 4px;">
                                                <span class="badge badge-secondary" style="font-size: 8px; padding: 2px 4px;">Belum Upload</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Modal Revisi Dokumen -->
                        @foreach($pendaftar->dokumen as $dokumen)
                        <div class="modal fade" id="revisiDokumenModal{{ $dokumen->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title">Minta Revisi: {{ ucfirst(str_replace('_', ' ', $dokumen->jenis_dokumen)) }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form class="revisi-dokumen-form" data-dokumen-id="{{ $dokumen->id }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Info:</strong> Dokumen akan dikembalikan ke pendaftar untuk diperbaiki dan diupload ulang.
                                            </div>
                                            <div class="form-group">
                                                <label>Catatan Revisi <span class="text-danger">*</span></label>
                                                <textarea name="catatan" class="form-control" rows="4" required placeholder="Contoh: Foto terlalu gelap, mohon upload dengan pencahayaan lebih baik...&#10;Contoh: Format file salah, mohon upload dalam format PDF...&#10;Contoh: Dokumen tidak lengkap, mohon upload halaman lengkap..."></textarea>
                                                <small class="text-muted">Berikan catatan yang jelas agar pendaftar tahu apa yang perlu diperbaiki.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-redo"></i> Minta Revisi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Modal Cancel Verifikasi Dokumen -->
                        @foreach($pendaftar->dokumen as $dokumen)
                        <div class="modal fade" id="cancelDokumenModal{{ $dokumen->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-secondary">
                                        <h5 class="modal-title text-white">
                                            <i class="fas fa-ban"></i> Batal Verifikasi: {{ ucfirst(str_replace('_', ' ', $dokumen->jenis_dokumen)) }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form class="cancel-dokumen-form" data-dokumen-id="{{ $dokumen->id }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Peringatan!</strong> Dokumen akan dikembalikan ke status <strong>Pending</strong>
                                            </div>
                                            <div class="form-group">
                                                <label>Alasan Pembatalan <span class="text-danger">*</span></label>
                                                <textarea name="alasan" class="form-control" rows="4" required placeholder="Masukkan alasan pembatalan verifikasi..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-dark">
                                                <i class="fas fa-ban"></i> Batalkan Verifikasi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Modal Batal Revisi -->
                        @foreach($pendaftar->dokumen as $dokumen)
                        <div class="modal fade" id="cancelRevisiModal{{ $dokumen->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-info">
                                        <h5 class="modal-title text-white">
                                            <i class="fas fa-undo"></i> Batal Revisi: {{ ucfirst(str_replace('_', ' ', $dokumen->jenis_dokumen)) }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form class="cancel-revisi-form" data-dokumen-id="{{ $dokumen->id }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Info:</strong> Permintaan revisi akan dibatalkan dan dokumen dikembalikan ke status <strong>Pending</strong>
                                            </div>
                                            <div class="form-group">
                                                <label>Alasan Pembatalan Revisi <span class="text-danger">*</span></label>
                                                <textarea name="alasan" class="form-control" rows="4" required placeholder="Revisi tidak diperlukan, salah klik, dll..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-undo"></i> Batalkan Revisi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Modal Approve Dokumen -->
                        <div class="modal fade" id="approveDokumenModal" style="z-index: 10060;">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-success">
                                        <h5 class="modal-title text-white">
                                            <i class="fas fa-check-circle"></i> Konfirmasi Persetujuan
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                        </div>
                                        <h5 class="mb-3">Setujui Dokumen Ini?</h5>
                                        <p class="text-muted mb-0" id="approveDokumenName">Dokumen akan disetujui dan pendaftar akan menerima notifikasi.</p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                        <button type="button" class="btn btn-success" id="confirmApproveBtn">
                                            <i class="fas fa-check"></i> Ya, Setujui
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom PDF Lightbox (Lightbox-style) -->
                        <div id="pdfLightbox" class="pdf-lightbox" style="display: none;">
                            <div class="pdf-overlay"></div>
                            <div class="pdf-container">
                                <button class="pdf-close" onclick="closePdfLightbox()">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="pdf-nav pdf-prev" onclick="navigatePdf(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="pdf-nav pdf-next" onclick="navigatePdf(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="pdf-content">
                                    <iframe id="pdfFrame" src="" frameborder="0"></iframe>
                                </div>
                                <div class="pdf-data">
                                    <div class="pdf-details">
                                        <div class="pdf-caption" id="pdfCaption"></div>
                                        <div class="pdf-number" id="pdfNumber"></div>
                                    </div>
                                </div>
                                <div class="pdf-approval-buttons" id="pdfApprovalBtns"></div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center">Tidak ada dokumen</p>
                    @endif
                </div>
            </div>
            
            {{-- Dokumen Tambahan (Opsional) --}}
            @if(isset($dokumenTambahan) && $dokumenTambahan->count() > 0)
            <div class="box box-solid box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-plus-circle"></i> Dokumen Tambahan (Opsional)</h3>
                    <div class="box-tools pull-right">
                        <span class="badge badge-success">{{ $dokumenTambahan->count() }} dokumen</span>
                    </div>
                </div>
                <div class="box-body" style="padding: 8px;">
                    <div class="row" style="margin: 0 -3px;">
                        @foreach($dokumenTambahan as $dokTambahan)
                        @php
                            $extension = strtolower(pathinfo($dokTambahan->file_path, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                            $isPdf = $extension === 'pdf';
                            $docLabel = $dokumenTambahanOptions[$dokTambahan->jenis_dokumen] ?? ucfirst(str_replace('_', ' ', $dokTambahan->jenis_dokumen));
                            $docKategori = \App\Models\CalonDokumen::getDokumenTambahanCategory($dokTambahan->jenis_dokumen);
                        @endphp
                        <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6 mb-2" style="padding: 0 3px;">
                            <div class="box box-widget dokumen-card" style="margin-bottom: 0; border: 1px solid #28a745;">
                                @if($isImage)
                                    <a href="{{ $dokTambahan->preview_url }}" 
                                       class="dokumen-link"
                                       data-url="{{ $dokTambahan->preview_url }}"
                                       data-title="{{ $docLabel }} (Opsional)"
                                       data-dokumen-id=""
                                       data-dokumen-status="valid"
                                       data-jenis-dokumen="{{ $dokTambahan->jenis_dokumen }}"
                                       data-type="image">
                                        <img src="{{ $dokTambahan->preview_url }}" class="card-img-top" style="height: 85px; object-fit: cover;">
                                    </a>
                                @else
                                    <a href="javascript:void(0);"
                                       class="dokumen-link"
                                       data-url="{{ $dokTambahan->preview_url }}"
                                       data-title="{{ $docLabel }} (Opsional)"
                                       data-dokumen-id=""
                                       data-dokumen-status="valid"
                                       data-jenis-dokumen="{{ $dokTambahan->jenis_dokumen }}"
                                       data-type="pdf">
                                        <div class="card-img-top bg-danger d-flex align-items-center justify-content-center" style="height: 85px;">
                                            <div class="text-center text-white">
                                                <i class="fas fa-file-pdf fa-2x"></i>
                                                <div style="font-size: 9px;">PDF</div>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                                <div class="card-body" style="padding: 5px;">
                                    <div style="font-size: 10px; font-weight: 600; margin-bottom: 3px; line-height: 1.2;">{{ $docLabel }}</div>
                                    @if($docKategori)
                                    <div style="margin-bottom: 3px;">
                                        <span class="badge badge-light border" style="font-size: 8px; color: #666;">{{ $docKategori }}</span>
                                    </div>
                                    @endif
                                    @if($dokTambahan->nama_dokumen && $dokTambahan->nama_dokumen != $docLabel)
                                    <div style="font-size: 8px; color: #666; margin-bottom: 2px;" title="{{ $dokTambahan->nama_dokumen }}">{{ Str::limit($dokTambahan->nama_dokumen, 25) }}</div>
                                    @endif
                                    <div style="margin-bottom: 4px;">
                                        <span class="badge badge-success" style="font-size: 8px; padding: 2px 4px;">
                                            <i class="fas fa-check"></i> Opsional
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Riwayat Gelombang -->
            @if($pendaftar->riwayatGelombang && $pendaftar->riwayatGelombang->count() > 0)
            <div class="box box-solid box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-exchange-alt"></i> Riwayat Perpindahan Gelombang</h3>
                    <div class="box-tools pull-right">
                        <span class="badge badge-warning">{{ $pendaftar->riwayatGelombang->count() }}x pindah</span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="timeline">
                        @foreach($pendaftar->riwayatGelombang as $riwayat)
                        <div class="time-label">
                            <span class="bg-warning">{{ $riwayat->created_at->format('d M Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-exchange-alt bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $riwayat->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">
                                    <strong>{{ $riwayat->dariGelombang->nama ?? '?' }}</strong>
                                    <i class="fas fa-arrow-right mx-2 text-info"></i>
                                    <strong>{{ $riwayat->keGelombang->nama ?? '?' }}</strong>
                                </h3>
                                <div class="timeline-body">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                                        <tr>
                                            <td width="45%" class="text-muted p-1">No. Reg Lama</td>
                                            <td class="p-1"><code>{{ $riwayat->nomor_registrasi_lama ?? '-' }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted p-1">No. Reg Baru</td>
                                            <td class="p-1"><code class="text-success">{{ $riwayat->nomor_registrasi_baru ?? '-' }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted p-1">Status Kelulusan Sebelumnya</td>
                                            <td class="p-1">
                                                @php $stLama = $riwayat->status_kelulusan_sebelumnya; @endphp
                                                @if($stLama === 'tidak_mengikuti_seleksi')
                                                    <span class="badge badge-secondary">TIDAK MENGIKUTI SELEKSI</span>
                                                @else
                                                    <span class="badge badge-{{ $stLama === 'tidak_lulus' ? 'danger' : ($stLama === 'cadangan' ? 'warning' : 'secondary') }}">
                                                        {{ strtoupper(str_replace('_', ' ', $stLama ?? '-')) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted p-1">Dipindahkan Oleh</td>
                                            <td class="p-1">
                                                <span class="badge badge-{{ $riwayat->dipindahkan_oleh === 'admin' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($riwayat->dipindahkan_oleh) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($riwayat->catatan)
                                        <tr>
                                            <td class="text-muted p-1">Catatan</td>
                                            <td class="p-1">{{ $riwayat->catatan }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fas fa-check-circle bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header text-muted" style="font-size: 12px;">
                                    Gelombang saat ini: <strong>{{ $pendaftar->gelombangPendaftaran->nama ?? '-' }}</strong>
                                    &middot; No. Reg: <code>{{ $pendaftar->nomor_registrasi }}</code>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info Registrasi & Lokasi -->
            <div class="box box-solid box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-map-marker-alt"></i> Info Registrasi & Lokasi</h3>
                    <div class="box-tools pull-right">
                        @if($pendaftar->registration_location_source)
                            {!! $pendaftar->registration_location_source_badge !!}
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-question-circle"></i> Tidak Tersedia</span>
                        @endif
                    </div>
                </div>
                <div class="box-body">
                    {{-- Alert jika data lokasi kosong --}}
                    @if(!$pendaftar->registration_ip && !$pendaftar->hasRegistrationCoordinates())
                        <div class="alert alert-warning" style="margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Data lokasi tidak tersedia.</strong>
                            @if(!$wajibLokasiRegistrasi)
                                <br><small>
                                    Fitur <b>"Wajibkan Lokasi Saat Registrasi"</b> belum diaktifkan. 
                                    <a href="{{ route('admin.settings.index') }}#lokasi-registrasi" class="alert-link">
                                        <i class="fas fa-cog"></i> Aktifkan di Pengaturan
                                    </a>
                                </small>
                            @else
                                <br><small>Pendaftar ini mungkin mendaftar sebelum fitur lokasi diaktifkan.</small>
                            @endif
                        </div>
                    @endif
                    
                    <div class="row">
                        <!-- Lokasi -->
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless" style="font-size: 13px;">
                                @if($pendaftar->hasRegistrationCoordinates())
                                <tr>
                                    <td width="35%" class="p-1 text-muted"><i class="fas fa-crosshairs"></i> Koordinat</td>
                                    <td class="p-1">
                                        <code style="font-size: 11px;">{{ $pendaftar->registration_coordinates }}</code>
                                        <a href="{{ $pendaftar->registration_maps_url }}" target="_blank" class="btn btn-xs btn-success ml-1" title="Lihat di Google Maps">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                @if($pendaftar->registration_accuracy)
                                <tr>
                                    <td class="p-1 text-muted"><i class="fas fa-bullseye"></i> Akurasi</td>
                                    <td class="p-1">± {{ number_format($pendaftar->registration_accuracy, 0) }} meter</td>
                                </tr>
                                @endif
                                {{-- Lokasi Tempat - dengan fallback reverse geocode jika data kosong --}}
                                <tr>
                                    <td class="p-1 text-muted"><i class="fas fa-map-pin"></i> Lokasi Tempat</td>
                                    <td class="p-1">
                                        @if($pendaftar->registration_city || $pendaftar->registration_region || $pendaftar->registration_address)
                                            <span class="text-success">
                                                @if($pendaftar->registration_address)
                                                    <small>{{ $pendaftar->registration_address }}</small>
                                                @else
                                                    {{ $pendaftar->registration_full_location }}
                                                @endif
                                            </span>
                                        @elseif($pendaftar->hasRegistrationCoordinates())
                                            <span id="location-address-{{ $pendaftar->id }}" class="text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Memuat lokasi...
                                            </span>
                                            <script>
                                                (function() {
                                                    var lat = {{ $pendaftar->registration_latitude }};
                                                    var lng = {{ $pendaftar->registration_longitude }};
                                                    var el = document.getElementById('location-address-{{ $pendaftar->id }}');
                                                    
                                                    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1', {
                                                        headers: { 'Accept-Language': 'id' }
                                                    })
                                                    .then(function(r) { return r.json(); })
                                                    .then(function(data) {
                                                        if (data && data.address) {
                                                            var addr = data.address;
                                                            var parts = [
                                                                addr.village || addr.suburb || addr.neighbourhood,
                                                                addr.city || addr.town || addr.county,
                                                                addr.state
                                                            ].filter(Boolean);
                                                            el.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + parts.join(', ') + '</span>';
                                                        } else {
                                                            el.innerHTML = '<span class="text-warning">-</span>';
                                                        }
                                                    })
                                                    .catch(function() {
                                                        el.innerHTML = '<span class="text-warning">-</span>';
                                                    });
                                                })();
                                            </script>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Device Info -->
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless" style="font-size: 13px;">
                                <tr>
                                    <td width="35%" class="p-1 text-muted"><i class="{{ $pendaftar->registration_device_icon }}"></i> Perangkat</td>
                                    <td class="p-1">{{ ucfirst($pendaftar->registration_device ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 text-muted"><i class="fab fa-chrome"></i> Browser</td>
                                    <td class="p-1">{{ $pendaftar->registration_browser ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 text-muted"><i class="fas fa-globe"></i> IP Address</td>
                                    <td class="p-1"><code style="font-size: 11px;">{{ $pendaftar->registration_ip ?? '-' }}</code></td>
                                </tr>
                                @if($pendaftar->registration_isp)
                                <tr>
                                    <td class="p-1 text-muted"><i class="fas fa-wifi"></i> ISP</td>
                                    <td class="p-1"><small>{{ $pendaftar->registration_isp }}</small></td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="p-1 text-muted"><i class="fas fa-clock"></i> Waktu Daftar</td>
                                    <td class="p-1">{{ $pendaftar->tanggal_registrasi ? $pendaftar->tanggal_registrasi->format('d M Y H:i:s') : $pendaftar->created_at->format('d M Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Histori Verifikasi Dokumen -->
            @if($pendaftar->dokumen && $pendaftar->dokumen->count() > 0)
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-history"></i> Histori Verifikasi Dokumen</h3>
                </div>
                <div class="box-body">
                    @php
                        $allHistories = collect();
                        foreach($pendaftar->dokumen as $dokumen) {
                            if($dokumen->histories && $dokumen->histories->count() > 0) {
                                foreach($dokumen->histories as $history) {
                                    $allHistories->push([
                                        'dokumen' => $dokumen,
                                        'history' => $history
                                    ]);
                                }
                            }
                        }
                        $allHistories = $allHistories->sortByDesc(function($item) {
                            return $item['history']->created_at;
                        });
                    @endphp

                    @if($allHistories->count() > 0)
                        <div class="timeline">
                            @foreach($allHistories as $item)
                                @php
                                    $dokumen = $item['dokumen'];
                                    $history = $item['history'];
                                    $iconClass = match($history->action) {
                                        'approve' => 'fa-check bg-success',
                                        'reject' => 'fa-times bg-danger',
                                        'revisi' => 'fa-redo bg-warning',
                                        'cancel' => 'fa-ban bg-secondary',
                                        default => 'fa-circle bg-info'
                                    };
                                @endphp
                                <div>
                                    <i class="fas {{ $iconClass }}"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $history->created_at->diffForHumans() }}
                                            <br>
                                            <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i:s') }}</small>
                                        </span>
                                        <h3 class="timeline-header">
                                            {!! $history->action_badge !!}
                                            <strong>{{ $dokumen->nama_dokumen_lengkap }}</strong>
                                        </h3>
                                        <div class="timeline-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">Oleh:</small><br>
                                                    <strong>
                                                        <i class="fas fa-user"></i> {{ $history->user->name ?? 'System' }}
                                                    </strong>
                                                    @if($history->user)
                                                        <br><small class="text-muted">{{ $history->user->email }}</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted">Perubahan Status:</small><br>
                                                    <span class="badge badge-secondary">{{ $history->status_from ?? 'N/A' }}</span>
                                                    <i class="fas fa-arrow-right"></i>
                                                    <span class="badge badge-primary">{{ $history->status_to }}</span>
                                                </div>
                                            </div>
                                            @if($history->keterangan)
                                                <div class="mt-2">
                                                    <small class="text-muted">Keterangan:</small><br>
                                                    <div class="alert alert-light mb-0 mt-1">
                                                        <i class="fas fa-comment"></i> {{ $history->keterangan }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div>
                                <i class="fas fa-flag bg-gray"></i>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center"><i class="fas fa-info-circle"></i> Belum ada histori verifikasi</p>
                    @endif
                </div>
            </div>
            @endif

            @if($pendaftar->status_verifikasi == 'rejected' && $pendaftar->rejection_reason)
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-times-circle"></i> Alasan Penolakan</h3>
                </div>
                <div class="card-body">
                    {{ $pendaftar->rejection_reason }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Dokumen Modal -->
    <div class="modal fade" id="dokumenModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="dokumenModalLabel">Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="d-flex">
                        <!-- Left side: Nilai Info (for rapor documents) -->
                        <div id="dokumenNilaiInfo" class="bg-light border-right d-none" style="width: 200px; min-height: 400px;">
                            <!-- Nilai data will be injected here -->
                        </div>
                        <!-- Right side: Document Content -->
                        <div class="flex-grow-1 text-center p-3">
                            <div id="dokumenContent"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-secondary" id="prevDokumen" onclick="navigateDokumen(-1)">
                            <i class="fas fa-chevron-left"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-secondary" id="nextDokumen" onclick="navigateDokumen(1)">
                            Selanjutnya <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div>
                        <span id="dokumenCounter" class="text-muted mr-3"></span>
                        <span id="dokumenApprovalButtons"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Tolak Pendaftar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.pendaftar.reject', $pendaftar->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="alasan">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="4" required 
                                      placeholder="Masukkan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Pendaftar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Preview Kartu Ujian --}}
    @if($pendaftar->is_finalisasi && auth()->user()->hasPermission('pendaftar.cetak-ujian'))
    <div class="modal fade" id="kartuUjianModal" tabindex="-1" role="dialog" aria-labelledby="kartuUjianModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="kartuUjianModalLabel">
                        <i class="fas fa-id-card mr-2"></i>Preview Kartu Ujian
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="background: #f5f5f5; padding: 20px;">
                    <div id="kartuUjianContent">
                        @php
                            $sekolahSettings = \App\Models\SekolahSettings::first();
                            $fotoDokumen = $pendaftar->dokumen()->where('jenis_dokumen', 'foto')->first();
                            $fotoUrl = $fotoDokumen?->preview_url;
                            $password = $pendaftar->user->readable_password ?? '********';
                        @endphp
                        @include('partials.kartu-ujian-preview-card', [
                            'previewCalonSiswa' => $pendaftar,
                            'sekolahSettings' => $sekolahSettings,
                            'fotoUrl' => $fotoUrl,
                            'password' => $password,
                        ])
                    </div>
                    <p class="text-muted mt-3 mb-0" style="font-size: 12px;">✂️ Gunting mengikuti tepi kartu setelah dicetak</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-info" onclick="printKartuUjian()">
                        <i class="fas fa-print mr-1"></i>Print
                    </button>
                    <a href="{{ route('admin.pendaftar.cetak-ujian', $pendaftar->id) }}" class="btn btn-success">
                        <i class="fas fa-download mr-1"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Kirim Email --}}
    <div class="modal fade" id="sendEmailModal" tabindex="-1" role="dialog" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h5 class="modal-title text-white" id="sendEmailModalLabel">
                        <i class="fas fa-envelope mr-2"></i>Kirim Email Notifikasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>Penerima:</strong></label>
                        <p class="mb-0">
                            <i class="fas fa-user mr-1"></i> {{ $pendaftar->nama_lengkap }}<br>
                            <i class="fas fa-envelope mr-1"></i> 
                            @php
                                $emailPendaftar = $pendaftar->user?->email ?? $pendaftar->email ?? null;
                                $emailValid = $emailPendaftar && !str_contains($emailPendaftar, '@ppdb.temp');
                            @endphp
                            @if($emailValid)
                                <span class="text-success">{{ $emailPendaftar }}</span>
                            @else
                                <span class="text-danger">Tidak ada email valid</span>
                            @endif
                        </p>
                    </div>
                    
                    @if($emailValid)
                    <div class="form-group">
                        <label for="emailType"><strong>Jenis Notifikasi:</strong> <span class="text-danger">*</span></label>
                        <select class="form-control" id="emailType" name="type" required>
                            <option value="">-- Pilih Jenis Email --</option>
                            <option value="registrasi">📧 Notifikasi Registrasi (Kredensial Login)</option>
                            <option value="revisi">⚠️ Notifikasi Revisi Dokumen</option>
                            @if($pendaftar->nomor_tes)
                            <option value="nomor_tes">🎫 Notifikasi Nomor Tes</option>
                            @endif
                            <option value="diterima">🎉 Notifikasi Diterima</option>
                            <option value="ditolak">📋 Notifikasi Ditolak</option>
                            <option value="lainnya">✉️ Notifikasi Lainnya (Custom)</option>
                        </select>
                    </div>
                    
                    {{-- Field Subject (untuk lainnya) --}}
                    <div class="form-group" id="subjectField" style="display: none;">
                        <label for="emailSubject"><strong>Subjek Email:</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="emailSubject" name="subject" placeholder="Masukkan subjek email...">
                    </div>
                    
                    {{-- Field Dokumen (untuk revisi) --}}
                    <div class="form-group" id="dokumenField" style="display: none;">
                        <label for="dokumenSelect"><strong>Pilih Dokumen:</strong> <span class="text-danger">*</span></label>
                        <select class="form-control" id="dokumenSelect" name="dokumen_id">
                            <option value="">-- Pilih Dokumen --</option>
                            @foreach($pendaftar->dokumen as $dok)
                            <option value="{{ $dok->id }}">{{ ucwords(str_replace('_', ' ', $dok->jenis_dokumen)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Field Catatan --}}
                    <div class="form-group" id="catatanField" style="display: none;">
                        <label for="emailCatatan"><strong><span id="catatanLabel">Catatan/Keterangan</span>:</strong> <span class="text-danger" id="catatanRequired" style="display: none;">*</span></label>
                        <textarea class="form-control" id="emailCatatan" name="catatan" rows="4" placeholder="Masukkan catatan..."></textarea>
                    </div>
                    
                    <div class="alert alert-info mb-0" id="emailInfo" style="display: none;">
                        <small id="emailInfoText"></small>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    @if($emailValid)
                    <button type="button" class="btn btn-primary" id="btnSendEmail" onclick="sendEmailNotification()">
                        <i class="fas fa-paper-plane"></i> Kirim Email
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Upload Dokumen dengan Kamera --}}
    @if(auth()->user()->hasPermission('pendaftar.upload-dokumen'))
    <div class="modal fade" id="uploadDokumenModal" tabindex="-1" role="dialog" aria-labelledby="uploadDokumenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-purple">
                    <h5 class="modal-title text-white" id="uploadDokumenModalLabel">
                        <i class="fas fa-upload mr-2"></i>Upload Dokumen untuk {{ $pendaftar->nama_lengkap }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadDokumenForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jenis_dokumen"><i class="fas fa-file-alt mr-1"></i> Jenis Dokumen <span class="text-danger">*</span></label>
                                    <select class="form-control" id="jenis_dokumen" name="jenis_dokumen" required>
                                        <option value="">-- Pilih Jenis Dokumen --</option>
                                        @foreach($adminUploadDokumenGroups as $groupLabel => $groupOptions)
                                            @if(isset($groupOptions[array_key_first($groupOptions)]) && is_array($groupOptions[array_key_first($groupOptions)]))
                                                @foreach($groupOptions as $subGroupLabel => $subGroupOptions)
                                                <optgroup label="{{ $groupLabel }} - {{ $subGroupLabel }}">
                                                    @foreach($subGroupOptions as $docKey => $docLabel)
                                                    <option value="{{ $docKey }}">{{ $docLabel }}</option>
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            @else
                                                <optgroup label="{{ $groupLabel }}">
                                                    @foreach($groupOptions as $docKey => $docLabel)
                                                    <option value="{{ $docKey }}">{{ $docLabel }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-cog mr-1"></i> Metode Upload</label>
                                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active flex-fill">
                                            <input type="radio" name="upload_method" value="file" checked> 
                                            <i class="fas fa-file-upload"></i> File
                                        </label>
                                        <label class="btn btn-outline-success flex-fill">
                                            <input type="radio" name="upload_method" value="camera"> 
                                            <i class="fas fa-camera"></i> Kamera
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- File Upload Section --}}
                        <div id="fileUploadSection">
                            <div class="form-group">
                                <label for="file_upload"><i class="fas fa-cloud-upload-alt mr-1"></i> Pilih File</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file_upload" name="file" accept="image/*,.pdf">
                                    <label class="custom-file-label" for="file_upload">Pilih file...</label>
                                </div>
                                <small class="form-text text-muted">Format: JPG, JPEG, PNG, PDF. Maks: 5MB</small>
                            </div>
                            <div id="filePreviewContainer" class="text-center mb-3" style="display: none;">
                                <img id="filePreview" src="" alt="Preview" class="img-fluid img-thumbnail" style="max-height: 300px;">
                                <p id="filePreviewName" class="mt-2 mb-0"></p>
                            </div>
                        </div>

                        {{-- Camera Section --}}
                        <div id="cameraSection" style="display: none;">
                            <div class="text-center mb-3">
                                <div class="position-relative d-inline-block">
                                    <video id="cameraVideo" width="100%" autoplay playsinline style="max-width: 500px; border-radius: 8px; background: #000;"></video>
                                    <div id="cameraOverlay" class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
                                        <i class="fas fa-camera fa-3x text-white"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <select id="cameraSelect" class="form-control form-control-sm">
                                        <option value="">Pilih Kamera...</option>
                                    </select>
                                </div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-info btn-sm" id="btnStartCamera">
                                        <i class="fas fa-play"></i> Mulai Kamera
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="btnStopCamera" style="display: none;">
                                        <i class="fas fa-stop"></i> Stop
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-success btn-lg" id="btnCapture" disabled>
                                    <i class="fas fa-camera"></i> Ambil Foto
                                </button>
                            </div>
                            
                            <canvas id="cameraCanvas" style="display: none;"></canvas>
                            
                            <div id="capturedImageContainer" class="text-center mb-3" style="display: none;">
                                <p class="text-success mb-2"><i class="fas fa-check-circle"></i> Foto berhasil diambil</p>
                                <img id="capturedImage" src="" alt="Captured" class="img-fluid img-thumbnail" style="max-height: 300px;">
                                <br>
                                <button type="button" class="btn btn-warning btn-sm mt-2" id="btnRetake">
                                    <i class="fas fa-redo"></i> Ulangi
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="catatan"><i class="fas fa-sticky-note mr-1"></i> Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <input type="hidden" id="captured_image_data" name="captured_image_data" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-purple" id="btnUploadDokumen" disabled>
                        <i class="fas fa-upload"></i> Upload Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Email modal type change handler
$('#emailType').on('change', function() {
    var type = $(this).val();
    var dokumenField = $('#dokumenField');
    var catatanField = $('#catatanField');
    var subjectField = $('#subjectField');
    var emailInfo = $('#emailInfo');
    var emailInfoText = $('#emailInfoText');
    var catatanLabel = $('#catatanLabel');
    var catatanRequired = $('#catatanRequired');
    
    // Reset fields
    dokumenField.hide();
    catatanField.hide();
    subjectField.hide();
    emailInfo.hide();
    catatanRequired.hide();
    catatanLabel.text('Catatan/Keterangan');
    $('#emailCatatan').attr('placeholder', 'Masukkan catatan...');
    
    switch(type) {
        case 'registrasi':
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email berisi <strong>username</strong> dan <strong>password</strong> untuk login.');
            break;
        case 'revisi':
            dokumenField.show();
            catatanField.show();
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email permintaan revisi akan dikirim dengan catatan yang Anda tulis.');
            break;
        case 'nomor_tes':
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email berisi <strong>Nomor Tes: {{ $pendaftar->nomor_tes }}</strong>');
            break;
        case 'diterima':
            catatanField.show();
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email pemberitahuan bahwa pendaftar <strong>DITERIMA</strong>.');
            break;
        case 'ditolak':
            catatanField.show();
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email pemberitahuan hasil seleksi (tidak diterima).');
            break;
        case 'lainnya':
            subjectField.show();
            catatanField.show();
            catatanRequired.show();
            catatanLabel.text('Isi Pesan');
            $('#emailCatatan').attr('placeholder', 'Masukkan isi pesan email...');
            emailInfo.show();
            emailInfoText.html('<i class="fas fa-info-circle"></i> Email custom dengan subjek dan isi pesan yang Anda tentukan sendiri.');
            break;
    }
});

// Send email function
function sendEmailNotification() {
    var type = $('#emailType').val();
    if (!type) {
        Swal.fire('Peringatan', 'Pilih jenis email terlebih dahulu', 'warning');
        return;
    }
    
    if (type === 'revisi' && !$('#dokumenSelect').val()) {
        Swal.fire('Peringatan', 'Pilih dokumen untuk revisi', 'warning');
        return;
    }
    
    if (type === 'lainnya') {
        if (!$('#emailSubject').val()) {
            Swal.fire('Peringatan', 'Subjek email harus diisi', 'warning');
            return;
        }
        if (!$('#emailCatatan').val()) {
            Swal.fire('Peringatan', 'Isi pesan email harus diisi', 'warning');
            return;
        }
    }
    
    var btn = $('#btnSendEmail');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
    
    $.ajax({
        url: '{{ route("admin.pendaftar.send-email", $pendaftar->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            type: type,
            dokumen_id: $('#dokumenSelect').val(),
            catatan: $('#emailCatatan').val(),
            subject: $('#emailSubject').val()
        },
        success: function(response) {
            $('#sendEmailModal').modal('hide');
            if (response.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                });
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        },
        error: function(xhr) {
            var message = 'Terjadi kesalahan saat mengirim email.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire('Error!', message, 'error');
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Email');
            // Reset form
            $('#emailType').val('');
            $('#dokumenSelect').val('');
            $('#emailCatatan').val('');
            $('#emailSubject').val('');
            $('#dokumenField, #catatanField, #subjectField, #emailInfo').hide();
            $('#catatanRequired').hide();
            $('#catatanLabel').text('Catatan/Keterangan');
        }
    });
}

// Global functions for onclick handlers
function resetPassword() {
    Swal.fire({
        title: 'Reset Password?',
        text: "Password akan direset secara otomatis dengan string random.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-key"></i> Ya, Reset!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.pendaftar.reset-password", $pendaftar->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: '<p>' + response.message + '</p>' +
                                  '<hr>' +
                                  '<div class="alert alert-info">' +
                                  '<strong><i class="fas fa-key"></i> Password Baru:</strong><br>' +
                                  '<code id="newPasswordCode" style="font-size: 18px; background: #fff; padding: 8px 15px; border-radius: 5px; display: inline-block; margin-top: 10px;">' + response.password + '</code><br>' +
                                  '<button type="button" class="btn btn-sm btn-primary mt-2" onclick="copyPasswordFromSwal(\'' + response.password + '\')">' +
                                  '<i class="fas fa-copy"></i> Salin Password</button>' +
                                  '</div>',
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat reset password.', 'error');
                }
            });
        }
    });
}

function showPassword() {
    $.ajax({
        url: '{{ route("admin.pendaftar.show-password", $pendaftar->id) }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Password Pendaftar',
                    html: '<div class="text-left">' +
                          '<p><strong><i class="fas fa-envelope"></i> Email:</strong><br>' +
                          '<code>' + response.email + '</code></p>' +
                          '<p><strong><i class="fas fa-key"></i> Password:</strong><br>' +
                          '<code id="currentPasswordCode" style="font-size: 18px; background: #f8f9fa; padding: 8px 15px; border-radius: 5px; display: inline-block;">' + response.password + '</code><br>' +
                          '<button type="button" class="btn btn-sm btn-primary mt-2" onclick="copyPasswordFromSwal(\'' + response.password + '\')">' +
                          '<i class="fas fa-copy"></i> Salin</button></p>' +
                          '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup'
                });
            } else {
                Swal.fire('Info', response.message, 'info');
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Terjadi kesalahan saat mengambil password.', 'error');
        }
    });
}

function copyPassword(password) {
    navigator.clipboard.writeText(password).then(function() {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Password disalin!',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    });
}

function copyPasswordFromSwal(password) {
    navigator.clipboard.writeText(password).then(function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Password disalin!'
        });
    });
}

function deletePendaftar() {
    Swal.fire({
        title: 'Hapus Pendaftar?',
        html: '<div class="text-left">' +
              '<p>Data akan dipindah ke <strong>Data Terhapus</strong> dan masih bisa di-restore.</p>' +
              '<div class="form-group mt-3">' +
              '<label>Alasan (opsional):</label>' +
              '<textarea id="deleteReason" class="form-control" rows="3" placeholder="Alasan penghapusan..."></textarea>' +
              '</div>' +
              '</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true,
        preConfirm: () => {
            return document.getElementById('deleteReason').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.pendaftar.destroy", $pendaftar->id) }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            if (result.value) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = result.value;
                form.appendChild(reasonInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

$(document).ready(function() {
    let currentDokumenId = null;
    let currentDokumenStatus = null;
    let currentJenisDokumen = null;
    let currentNilaiData = null;
    let currentScale = 1;
    
    // Gallery data
    let dokumenGallery = [];
    let currentIndex = 0;
    
    // Build gallery from all documents (images and PDFs)
    function buildGallery() {
        dokumenGallery = [];
        $('.dokumen-link').each(function() {
            const type = $(this).data('type'); // 'image' or 'pdf'
            const item = {
                url: $(this).data('url'),
                title: $(this).data('title'),
                dokumenId: $(this).data('dokumen-id'),
                status: $(this).data('dokumen-status'),
                jenisDokumen: $(this).data('jenis-dokumen'),
                type: type
            };
            
            // Add nilai data for rapor documents
            if ($(this).data('nilai-matematika') !== undefined) {
                item.nilaiData = {
                    matematika: $(this).data('nilai-matematika'),
                    ipa: $(this).data('nilai-ipa'),
                    ips: $(this).data('nilai-ips'),
                    rata: $(this).data('nilai-rata')
                };
            }
            
            dokumenGallery.push(item);
        });
        console.log('Gallery built:', dokumenGallery.length, 'items');
    }
    
    buildGallery();
    
    // Open modal when dokumen clicked
    $(document).on('click', '.dokumen-link', function(e) {
        e.preventDefault();
        const clickedUrl = $(this).data('url');
        currentIndex = dokumenGallery.findIndex(item => item.url === clickedUrl);
        
        if (currentIndex !== -1) {
            openModal(currentIndex);
        }
    });
    
    function openModal(index) {
        const item = dokumenGallery[index];
        currentDokumenId = item.dokumenId;
        currentDokumenStatus = item.status;
        currentJenisDokumen = item.jenisDokumen;
        currentNilaiData = item.nilaiData || null;
        currentScale = 1;
        
        // Set title
        $('#dokumenModalLabel').text(item.title);
        
        // Set content based on type - using DOM manipulation instead of string concatenation
        $('#dokumenContent').empty();
        if (item.type === 'pdf') {
            const iframe = $('<iframe>')
                .attr('src', item.url)
                .css({
                    'width': '100%',
                    'height': '500px',
                    'border': 'none'
                });
            $('#dokumenContent').append(iframe);
        } else {
            const img = $('<img>')
                .addClass('img-fluid dokumen-image')
                .attr('src', item.url)
                .css({
                    'max-height': '500px',
                    'width': 'auto',
                    'margin': '0 auto',
                    'display': 'block',
                    'cursor': 'zoom-in'
                });
            $('#dokumenContent').append(img);
        }
        
        // Update navigation
        $('#dokumenCounter').text('Dokumen ' + (index + 1) + ' dari ' + dokumenGallery.length);
        $('#prevDokumen').prop('disabled', index === 0);
        $('#nextDokumen').prop('disabled', index === dokumenGallery.length - 1);
        
        // Update approval buttons
        updateApprovalButtons();
        
        // Show modal
        $('#dokumenModal').modal('show');
        
        // Enable zoom for images after modal shown
        if (item.type === 'image') {
            setTimeout(enableZoom, 300);
        }
    }
    
    function closeModal() {
        $('#dokumenModal').modal('hide');
        $('#dokumenContent').html('');
    }
    
    window.navigateDokumen = function(direction) {
        const newIndex = currentIndex + direction;
        if (newIndex >= 0 && newIndex < dokumenGallery.length) {
            currentIndex = newIndex;
            openModal(currentIndex);
        }
    };
    
    function enableZoom() {
        const $img = $('.dokumen-image');
        if ($img.length === 0) return;
        
        // Double-click zoom
        $img.off('dblclick').on('dblclick', function(e) {
            e.preventDefault();
            if (currentScale === 1) {
                currentScale = 2.5;
                $(this).css({
                    'transform': 'scale(2.5)',
                    'cursor': 'zoom-out',
                    'transition': 'transform 0.3s ease'
                });
            } else {
                currentScale = 1;
                $(this).css({
                    'transform': 'scale(1)',
                    'cursor': 'zoom-in',
                    'transition': 'transform 0.3s ease'
                });
            }
        });
        
        // Mouse wheel zoom
        $img.off('wheel').on('wheel', function(e) {
            e.preventDefault();
            const delta = e.originalEvent.deltaY;
            
            if (delta < 0) {
                currentScale = Math.min(currentScale + 0.3, 4);
            } else {
                currentScale = Math.max(currentScale - 0.3, 1);
            }
            
            $(this).css({
                'transform': 'scale(' + currentScale + ')',
                'cursor': currentScale > 1 ? 'zoom-out' : 'zoom-in',
                'transition': 'transform 0.2s ease'
            });
        });
    }
    
    function updateApprovalButtons() {
        const container = $('#dokumenApprovalButtons');
        const nilaiContainer = $('#dokumenNilaiInfo');
        container.empty();
        nilaiContainer.empty().addClass('d-none');
        
        // Check if this is a rapor document
        if (currentDokumenId && currentDokumenId.toString().startsWith('rapor-')) {
            const raporId = currentDokumenId.replace('rapor-', '');
            
            // Display nilai data on left panel if available
            if (currentNilaiData) {
                nilaiContainer.removeClass('d-none').html(`
                    <div class="p-3 d-flex flex-column justify-content-center h-100">
                        <div class="text-center mb-3">
                            <i class="fas fa-calculator fa-2x text-primary mb-2"></i>
                            <h6 class="mb-0"><strong>Data Nilai Rapor</strong></h6>
                        </div>
                        <div class="text-center">
                            <div class="mb-3 p-2 bg-white rounded shadow-sm">
                                <div class="h4 mb-0 text-primary font-weight-bold">${currentNilaiData.matematika}</div>
                                <small class="text-muted">Matematika</small>
                            </div>
                            <div class="mb-3 p-2 bg-white rounded shadow-sm">
                                <div class="h4 mb-0 text-success font-weight-bold">${currentNilaiData.ipa}</div>
                                <small class="text-muted">IPA</small>
                            </div>
                            <div class="mb-3 p-2 bg-white rounded shadow-sm">
                                <div class="h4 mb-0 text-info font-weight-bold">${currentNilaiData.ips}</div>
                                <small class="text-muted">IPS</small>
                            </div>
                            <div class="p-2 bg-warning rounded shadow-sm">
                                <div class="h4 mb-0 text-dark font-weight-bold">${currentNilaiData.rata}</div>
                                <small class="text-dark">Rata-Rata</small>
                            </div>
                        </div>
                    </div>
                `);
            }
            
            // Buttons based on status
            if (currentDokumenStatus === 'pending') {
                const validBtn = $('<button>')
                    .addClass('btn btn-success btn-sm')
                    .html('<i class="fas fa-check"></i> Valid')
                    .on('click', function() { validasiRaporFromModal(raporId, 'valid'); });
                
                const invalidBtn = $('<button>')
                    .addClass('btn btn-danger btn-sm ml-2')
                    .html('<i class="fas fa-times"></i> Tolak')
                    .on('click', function() { validasiRaporFromModal(raporId, 'invalid'); });
                
                container.append(validBtn).append(' ').append(invalidBtn);
            } else if (currentDokumenStatus === 'valid') {
                const statusBadge = $('<span>')
                    .addClass('badge badge-success mr-2')
                    .html('<i class="fas fa-check-circle"></i> Valid');
                
                const batalBtn = $('<button>')
                    .addClass('btn btn-warning btn-sm')
                    .html('<i class="fas fa-undo"></i> Batal')
                    .on('click', function() { validasiRaporFromModal(raporId, 'pending'); });
                
                const tolakBtn = $('<button>')
                    .addClass('btn btn-danger btn-sm ml-2')
                    .html('<i class="fas fa-times"></i> Tolak')
                    .on('click', function() { validasiRaporFromModal(raporId, 'invalid'); });
                
                container.append(statusBadge).append(' ').append(batalBtn).append(' ').append(tolakBtn);
            } else if (currentDokumenStatus === 'invalid') {
                const statusBadge = $('<span>')
                    .addClass('badge badge-danger mr-2')
                    .html('<i class="fas fa-times-circle"></i> Ditolak');
                
                const validBtn = $('<button>')
                    .addClass('btn btn-success btn-sm')
                    .html('<i class="fas fa-check"></i> Valid')
                    .on('click', function() { validasiRaporFromModal(raporId, 'valid'); });
                
                const batalBtn = $('<button>')
                    .addClass('btn btn-warning btn-sm ml-2')
                    .html('<i class="fas fa-undo"></i> Batal')
                    .on('click', function() { validasiRaporFromModal(raporId, 'pending'); });
                
                container.append(statusBadge).append(' ').append(validBtn).append(' ').append(batalBtn);
            }
            
            return;
        }
        
        // Skip untuk dokumen tambahan (opsional) - tidak punya ID
        if (!currentDokumenId) {
            container.html('<p class="text-muted mb-0"><i class="fas fa-info-circle"></i> Dokumen opsional tidak perlu verifikasi</p>');
            return;
        }
        
        if (currentDokumenStatus === 'pending') {
            const approveBtn = $('<button>')
                .addClass('btn btn-success btn-sm')
                .html('<i class="fas fa-check"></i> Setujui')
                .on('click', function() { approveDokumen(currentDokumenId); });
            
            const revisiBtn = $('<button>')
                .addClass('btn btn-warning btn-sm ml-2')
                .html('<i class="fas fa-redo"></i> Minta Revisi')
                .on('click', function() { revisiDokumen(currentDokumenId); });
            
            container.append(approveBtn).append(' ').append(revisiBtn);
        } else if (currentDokumenStatus === 'valid') {
            const statusBadge = $('<span>')
                .addClass('badge badge-success mr-2')
                .html('<i class="fas fa-check-circle"></i> Dokumen Disetujui');
            
            const revisiBtn = $('<button>')
                .addClass('btn btn-warning btn-sm')
                .html('<i class="fas fa-redo"></i> Minta Revisi')
                .on('click', function() { revisiDokumen(currentDokumenId); });
            
            container.append(statusBadge).append(' ').append(revisiBtn);
        } else if (currentDokumenStatus === 'revision') {
            container.html('<span class="badge badge-info"><i class="fas fa-clock"></i> Menunggu Revisi dari Pendaftar</span>');
        }
    }
    
    // Validasi Rapor from Modal
    window.validasiRaporFromModal = function(raporId, status) {
        let title, text, icon, confirmBtnColor, confirmBtnText;
        
        if (status === 'invalid') {
            // Tolak dokumen
            Swal.fire({
                title: 'Tolak Dokumen Rapor?',
                html: `<div class="form-group text-left">
                    <label>Catatan penolakan (opsional):</label>
                    <textarea id="catatanValidasiModal" class="form-control" rows="3" placeholder="Masukkan catatan penolakan..."></textarea>
                   </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Ya, Tolak',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                reverseButtons: true,
                preConfirm: () => document.getElementById('catatanValidasiModal')?.value
            }).then((result) => {
                if (result.isConfirmed) {
                    doValidasiRapor(raporId, status, result.value);
                }
            });
        } else if (status === 'pending') {
            // Batal verifikasi
            Swal.fire({
                title: 'Batalkan Verifikasi?',
                text: 'Status dokumen akan dikembalikan ke Pending (belum diverifikasi)',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo"></i> Ya, Batalkan',
                cancelButtonText: '<i class="fas fa-times"></i> Tidak',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    doValidasiRapor(raporId, status, null);
                }
            });
        } else {
            // Valid dokumen
            Swal.fire({
                title: 'Validasi Dokumen Rapor?',
                text: 'Apakah Anda yakin ingin memvalidasi dokumen rapor ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Valid',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    doValidasiRapor(raporId, status, null);
                }
            });
        }
    };
    
    function doValidasiRapor(raporId, status, catatan) {
        $.ajax({
            url: `/admin/pendaftar/rapor/${raporId}/validasi`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status,
                catatan: catatan
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    currentDokumenStatus = status;
                    updateApprovalButtons();
                    
                    // Update cell content in table using returned HTML or reload
                    const validasiCell = $(`#rapor-validasi-${raporId}`);
                    if (validasiCell.length && response.html) {
                        validasiCell.html(response.html);
                    } else {
                        // Fallback: reload page if we can't update cell
                        setTimeout(() => location.reload(), 1000);
                    }
                    
                    // Update dokumen-link data attribute for gallery
                    $(`.dokumen-link[data-dokumen-id="rapor-${raporId}"]`).data('dokumen-status', status);
                    
                    // Rebuild gallery to update status
                    buildGallery();
                } else {
                    showToast('error', response.message || 'Gagal memvalidasi dokumen');
                }
            },
            error: function(xhr) {
                showToast('error', 'Terjadi kesalahan saat memvalidasi dokumen');
            }
        });
    }
    
    // Approval functions
    let currentApproveDokumenId = null;
    
    window.approveDokumen = function(dokumenId) {
        console.log('approveDokumen called with ID:', dokumenId);
        currentApproveDokumenId = dokumenId;
        $('#approveDokumenModal').modal('show');
    };
    
    // Handle confirm approve button - must be inside document.ready
    $('#confirmApproveBtn').on('click', function() {
        console.log('Confirm button clicked, dokumenId:', currentApproveDokumenId);
        if (currentApproveDokumenId) {
            $.ajax({
                url: '/admin/pendaftar/dokumen/' + currentApproveDokumenId + '/approve',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $('#approveDokumenModal').modal('hide');
                    showToast('success', 'Dokumen berhasil disetujui!');
                    updateDokumenStatus(currentApproveDokumenId, 'valid');
                    closeModal();
                    currentApproveDokumenId = null;
                },
                error: function(xhr) {
                    console.error('Error approving dokumen:', xhr);
                    $('#approveDokumenModal').modal('hide');
                    showToast('error', 'Gagal menyetujui dokumen');
                    currentApproveDokumenId = null;
                }
            });
        }
    });
    
    window.revisiDokumen = function(dokumenId) {
        closeModal();
        setTimeout(function() {
            $('#revisiDokumenModal' + dokumenId).modal('show');
        }, 400);
    };
    
    // Handle approve from card
    $(document).on('click', '.approve-card-btn', function() {
        const dokumenId = $(this).data('dokumen-id');
        console.log('Approve card button clicked, dokumenId:', dokumenId);
        currentApproveDokumenId = dokumenId;
        $('#approveDokumenModal').modal('show');
    });
    
    // Handle revisi form
    $(document).on('submit', '.revisi-dokumen-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const dokumenId = $form.data('dokumen-id');
        
        $.ajax({
            url: '/admin/pendaftar/dokumen/' + dokumenId + '/revisi',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $('#revisiDokumenModal' + dokumenId).modal('hide');
                showToast('warning', 'Permintaan revisi berhasil dikirim');
                updateDokumenStatus(dokumenId, 'revision');
                $form[0].reset();
            },
            error: function() {
                showToast('error', 'Gagal meminta revisi');
            }
        });
    });

    // Handle cancel verifikasi form
    $(document).on('submit', '.cancel-dokumen-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const dokumenId = $form.data('dokumen-id');
        
        $.ajax({
            url: '/admin/pendaftar/dokumen/' + dokumenId + '/cancel',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $('#cancelDokumenModal' + dokumenId).modal('hide');
                showToast('success', 'Verifikasi dokumen berhasil dibatalkan');
                updateDokumenStatus(dokumenId, 'pending');
                $form[0].reset();
                // Reload page untuk refresh tombol
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                $('#cancelDokumenModal' + dokumenId).modal('hide');
                const message = xhr.responseJSON?.message || 'Gagal membatalkan verifikasi';
                showToast('error', message);
            }
        });
    });
    
    // Handle cancel revisi form
    $(document).on('submit', '.cancel-revisi-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const dokumenId = $form.data('dokumen-id');
        
        $.ajax({
            url: '/admin/pendaftar/dokumen/' + dokumenId + '/cancel-revisi',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $('#cancelRevisiModal' + dokumenId).modal('hide');
                showToast('success', 'Permintaan revisi berhasil dibatalkan');
                updateDokumenStatus(dokumenId, 'pending');
                $form[0].reset();
                // Reload page untuk refresh tombol
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                $('#cancelRevisiModal' + dokumenId).modal('hide');
                const message = xhr.responseJSON?.message || 'Gagal membatalkan revisi';
                showToast('error', message);
            }
        });
    });
    
    function showToast(type, message) {
        const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-warning');
        const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-exclamation-circle');
        
        const toast = $('<div>').addClass('toast-notification ' + bgClass)
            .html('<i class="fas ' + iconClass + '"></i> ' + message)
            .css({
                'position': 'fixed',
                'top': '20px',
                'right': '20px',
                'padding': '15px 20px',
                'border-radius': '5px',
                'color': 'white',
                'font-weight': 'bold',
                'z-index': 99999,
                'box-shadow': '0 4px 6px rgba(0,0,0,0.2)'
            });
        
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
    
    function updateDokumenStatus(dokumenId, newStatus) {
        const $card = $('[data-dokumen-id="' + dokumenId + '"]').closest('.dokumen-card');
        const $badge = $card.find('.badge');
        $badge.removeClass('badge-warning badge-success badge-danger badge-info');
        
        if (newStatus === 'valid') {
            $badge.addClass('badge-success').text('Valid');
        } else if (newStatus === 'invalid') {
            $badge.addClass('badge-danger').text('Invalid');
        } else if (newStatus === 'revision') {
            $badge.addClass('badge-info').text('Revisi');
        }
        
        // Update buttons in card
        const $btnGroup = $card.find('.btn-group');
        if (newStatus === 'valid') {
            const revisiBtn = '<button type="button" class="btn btn-warning btn-sm btn-block" data-toggle="modal" data-target="#revisiDokumenModal' + dokumenId + '"><i class="fas fa-redo"></i> Revisi</button>';
            $btnGroup.replaceWith(revisiBtn);
        } else if (newStatus === 'invalid' || newStatus === 'revision') {
            $btnGroup.remove();
        }
        
        // Update data attributes
        $card.find('.dokumen-link').attr('data-dokumen-status', newStatus);
        
        // Rebuild gallery
        buildGallery();
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if ($('#dokumenModal').is(':visible')) {
            if (e.key === 'Escape') {
                closeModal();
            } else if (e.key === 'ArrowLeft') {
                navigateDokumen(-1);
            } else if (e.key === 'ArrowRight') {
                navigateDokumen(1);
            }
        }
    });
});

// Global functions - outside document.ready untuk onclick access
function resetPassword() {
    Swal.fire({
        title: 'Reset Password?',
        text: "Password akan direset secara otomatis dengan string random.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-key"></i> Ya, Reset!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.pendaftar.reset-password", $pendaftar->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: '<p>' + response.message + '</p>' +
                                  '<hr>' +
                                  '<div class="alert alert-info">' +
                                  '<strong><i class="fas fa-key"></i> Password Baru:</strong><br>' +
                                  '<code id="newPasswordCode" style="font-size: 18px; background: #fff; padding: 8px 15px; border-radius: 5px; display: inline-block; margin-top: 10px;">' + response.password + '</code><br>' +
                                  '<button type="button" class="btn btn-sm btn-primary mt-2" onclick="copyPasswordFromSwal(\'' + response.password + '\')"><i class="fas fa-copy"></i> Salin Password</button>' +
                                  '</div>',
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat reset password.', 'error');
                }
            });
        }
    });
}

function showPassword() {
    $.ajax({
        url: '{{ route("admin.pendaftar.show-password", $pendaftar->id) }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Password Pendaftar',
                    html: '<div class="text-left">' +
                          '<p><strong><i class="fas fa-envelope"></i> Email:</strong><br>' +
                          '<code>' + response.email + '</code></p>' +
                          '<p><strong><i class="fas fa-key"></i> Password:</strong><br>' +
                          '<code id="currentPasswordCode" style="font-size: 18px; background: #f8f9fa; padding: 8px 15px; border-radius: 5px; display: inline-block;">' + response.password + '</code><br>' +
                          '<button type="button" class="btn btn-sm btn-primary mt-2" onclick="copyPasswordFromSwal(\'' + response.password + '\')"><i class="fas fa-copy"></i> Salin</button></p>' +
                          '</div>',
                    icon: 'info',
                    confirmButtonText: 'Tutup'
                });
            } else {
                Swal.fire('Info', response.message, 'info');
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Terjadi kesalahan saat mengambil password.', 'error');
        }
    });
}

function copyPassword(password) {
    navigator.clipboard.writeText(password).then(function() {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Password disalin!',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    });
}

function copyPasswordFromSwal(password) {
    navigator.clipboard.writeText(password).then(function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Password disalin!'
        });
    });
}

function batalFinalisasi() {
    Swal.fire({
        title: 'Batalkan Finalisasi?',
        html: '<div class="text-left">' +
              '<p>Dengan membatalkan finalisasi:</p>' +
              '<ul>' +
              '<li>Pendaftar dapat mengedit data kembali</li>' +
              '<li>Nomor tes akan tetap tersimpan</li>' +
              '<li>Status finalisasi akan di-reset</li>' +
              '</ul>' +
              '<p class="text-danger"><strong>Perhatian:</strong> Lakukan dengan hati-hati!</p>' +
              '</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-unlock"></i> Ya, Batalkan',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.pendaftar.batal-finalisasi", $pendaftar->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan saat membatalkan finalisasi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMsg, 'error');
                }
            });
        }
    });
}

function batalGenerateNomorTes() {
    Swal.fire({
        title: 'Batalkan Nomor Tes Terakhir?',
        html: '<div class="text-left">' +
              '<p>Fitur ini hanya untuk membatalkan nomor tes yang benar-benar terakhir digenerate.</p>' +
              '<ul>' +
              '<li>Nomor tes pendaftar akan dikosongkan</li>' +
              '<li>Status finalisasi akan dibatalkan</li>' +
              '<li>Status verifikasi kembali ke pending</li>' +
              '<li>Counter nomor tes dikembalikan ke nomor sebelumnya</li>' +
              '</ul>' +
              '<p class="text-danger"><strong>Perhatian:</strong> Sistem akan menolak jika nomor sudah dipakai di jadwal, ruang, nilai, atau kelulusan.</p>' +
              '</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-undo"></i> Ya, Batalkan Nomor',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.pendaftar.batal-generate-nomor-tes", $pendaftar->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMsg = 'Terjadi kesalahan saat membatalkan nomor tes.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Tidak Bisa Dibatalkan', errorMsg, 'error');
                }
            });
        }
    });
}

// ===============================================
// Upload Dokumen dengan Kamera - Functions
// ===============================================
@if(auth()->user()->hasPermission('pendaftar.upload-dokumen'))
let cameraStream = null;
let capturedImageData = null;

// Toggle upload method (file or camera)
$('input[name="upload_method"]').on('change', function() {
    const method = $(this).val();
    if (method === 'file') {
        $('#fileUploadSection').show();
        $('#cameraSection').hide();
        stopCamera();
    } else {
        $('#fileUploadSection').hide();
        $('#cameraSection').show();
        populateCameraList();
    }
    updateUploadButtonState();
});

// File upload preview
$('#file_upload').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Update label
        $(this).next('.custom-file-label').text(file.name);
        
        // Show preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#filePreview').attr('src', e.target.result);
                $('#filePreviewName').text(file.name + ' (' + formatFileSize(file.size) + ')');
                $('#filePreviewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#filePreview').attr('src', '');
            $('#filePreviewName').text(file.name + ' (' + formatFileSize(file.size) + ')');
            $('#filePreviewContainer').show();
        }
        
        // Clear camera data
        capturedImageData = null;
        $('#captured_image_data').val('');
        
        updateUploadButtonState();
    }
});

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Populate camera list
async function populateCameraList() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');
        
        const $select = $('#cameraSelect');
        $select.empty().append('<option value="">Pilih Kamera...</option>');
        
        videoDevices.forEach((device, index) => {
            const label = device.label || `Kamera ${index + 1}`;
            $select.append(`<option value="${device.deviceId}">${label}</option>`);
        });
        
        // Auto-select first camera
        if (videoDevices.length > 0) {
            $select.val(videoDevices[0].deviceId);
        }
    } catch (error) {
        console.error('Error getting camera list:', error);
    }
}

// Start camera
$('#btnStartCamera').on('click', async function() {
    const deviceId = $('#cameraSelect').val();
    if (!deviceId) {
        Swal.fire('Perhatian', 'Pilih kamera terlebih dahulu', 'warning');
        return;
    }
    
    try {
        const constraints = {
            video: {
                deviceId: { exact: deviceId },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('cameraVideo');
        video.srcObject = cameraStream;
        
        $('#btnStartCamera').hide();
        $('#btnStopCamera').show();
        $('#btnCapture').prop('disabled', false);
        $('#cameraOverlay').hide();
        
    } catch (error) {
        console.error('Error starting camera:', error);
        Swal.fire('Error', 'Gagal mengakses kamera: ' + error.message, 'error');
    }
});

// Stop camera
$('#btnStopCamera').on('click', function() {
    stopCamera();
});

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    const video = document.getElementById('cameraVideo');
    if (video) {
        video.srcObject = null;
    }
    
    $('#btnStartCamera').show();
    $('#btnStopCamera').hide();
    $('#btnCapture').prop('disabled', true);
}

// Capture photo
$('#btnCapture').on('click', function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size to video size
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get base64 data
    capturedImageData = canvas.toDataURL('image/jpeg', 0.9);
    $('#captured_image_data').val(capturedImageData);
    
    // Show captured image
    $('#capturedImage').attr('src', capturedImageData);
    $('#capturedImageContainer').show();
    
    // Stop camera after capture
    stopCamera();
    
    // Clear file input
    $('#file_upload').val('');
    $('.custom-file-label').text('Pilih file...');
    $('#filePreviewContainer').hide();
    
    updateUploadButtonState();
});

// Retake photo
$('#btnRetake').on('click', function() {
    capturedImageData = null;
    $('#captured_image_data').val('');
    $('#capturedImageContainer').hide();
    
    // Restart camera
    $('#btnStartCamera').click();
    
    updateUploadButtonState();
});

// Update upload button state
function updateUploadButtonState() {
    const jenisDokumen = $('#jenis_dokumen').val();
    const hasFile = $('#file_upload')[0].files.length > 0;
    const hasCapture = capturedImageData !== null;
    
    const canUpload = jenisDokumen && (hasFile || hasCapture);
    $('#btnUploadDokumen').prop('disabled', !canUpload);
}

// Watch jenis_dokumen change
$('#jenis_dokumen').on('change', function() {
    updateUploadButtonState();
});

// Upload dokumen
$('#btnUploadDokumen').on('click', function() {
    const jenisDokumen = $('#jenis_dokumen').val();
    const catatan = $('#catatan').val();
    const hasFile = $('#file_upload')[0].files.length > 0;
    const hasCapture = capturedImageData !== null;
    
    if (!jenisDokumen) {
        Swal.fire('Perhatian', 'Pilih jenis dokumen terlebih dahulu', 'warning');
        return;
    }
    
    if (!hasFile && !hasCapture) {
        Swal.fire('Perhatian', 'Pilih file atau ambil foto terlebih dahulu', 'warning');
        return;
    }
    
    // Create FormData
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('jenis_dokumen', jenisDokumen);
    formData.append('catatan', catatan);
    
    if (hasCapture) {
        formData.append('captured_image_data', capturedImageData);
    } else if (hasFile) {
        formData.append('file', $('#file_upload')[0].files[0]);
    }
    
    // Show loading
    Swal.fire({
        title: 'Mengupload...',
        html: 'Mohon tunggu, sedang mengupload dokumen',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Upload via AJAX
    $.ajax({
        url: '{{ route("admin.pendaftar.upload-dokumen", $pendaftar->id) }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Close modal and reload
                    $('#uploadDokumenModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Terjadi kesalahan saat mengupload dokumen.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('<br>');
                }
            }
            Swal.fire('Error!', errorMsg, 'error');
        }
    });
});

// Reset modal on close
$('#uploadDokumenModal').on('hidden.bs.modal', function() {
    stopCamera();
    capturedImageData = null;
    $('#captured_image_data').val('');
    $('#uploadDokumenForm')[0].reset();
    $('.custom-file-label').text('Pilih file...');
    $('#filePreviewContainer').hide();
    $('#capturedImageContainer').hide();
    $('#fileUploadSection').show();
    $('#cameraSection').hide();
    $('input[name="upload_method"][value="file"]').prop('checked', true).parent().addClass('active');
    $('input[name="upload_method"][value="camera"]').prop('checked', false).parent().removeClass('active');
    updateUploadButtonState();
});
@endif

// Print Kartu Ujian from modal
function printKartuUjian() {
    var printContent = document.getElementById('kartuUjianContent').innerHTML;
    var printWindow = window.open('', '_blank', 'width=500,height=400');
    printWindow.document.write('<html><head><title>Kartu Tes - {{ $pendaftar->nomor_tes }}</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }');
    printWindow.document.write('.card { width: 400px; height: 250px; margin: 0 auto; background: #fff; border: 1px solid #999; border-radius: 8px; overflow: hidden; position: relative; }');
    printWindow.document.write('.watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100px; height: 100px; opacity: 0.12; }');
    printWindow.document.write('.watermark img { width: 100%; height: 100%; object-fit: contain; }');
    printWindow.document.write('.card-header { border-bottom: 1px solid #ccc; padding: 8px 12px; background: #fff; }');
    printWindow.document.write('.card-header table { width: 100%; }');
    printWindow.document.write('.school-name { color: #333; font-size: 11px; font-weight: bold; text-transform: uppercase; }');
    printWindow.document.write('.card-type { color: #666; font-size: 9px; border: 1px solid #999; padding: 2px 6px; border-radius: 3px; }');
    printWindow.document.write('.card-body { padding: 10px 12px; }');
    printWindow.document.write('.card-body table { width: 100%; }');
    printWindow.document.write('.photo-cell { width: 80px; vertical-align: top; padding-right: 10px; }');
    printWindow.document.write('.photo-box { width: 75px; height: 100px; border: 1px solid #999; border-radius: 4px; overflow: hidden; background: #fff; }');
    printWindow.document.write('.photo-box img { width: 75px; height: 100px; object-fit: cover; }');
    printWindow.document.write('.no-photo { color: #999; font-size: 10px; text-align: center; padding-top: 35px; }');
    printWindow.document.write('.info-cell { vertical-align: top; }');
    printWindow.document.write('.nomor-tes-box { border: 1px solid #999; border-radius: 4px; padding: 5px; text-align: center; margin-bottom: 8px; }');
    printWindow.document.write('.nomor-tes-label { color: #666; font-size: 8px; text-transform: uppercase; letter-spacing: 1px; }');
    printWindow.document.write('.nomor-tes-value { color: #333; font-size: 16px; font-weight: bold; letter-spacing: 1px; }');
    printWindow.document.write('.data-table { width: 100%; margin-bottom: 8px; }');
    printWindow.document.write('.data-table td { padding: 2px 0; font-size: 10px; color: #333; vertical-align: top; }');
    printWindow.document.write('.data-label { width: 45px; color: #666; }');
    printWindow.document.write('.data-separator { width: 10px; color: #666; }');
    printWindow.document.write('.data-value { font-weight: bold; color: #333; }');
    printWindow.document.write('.nama-value { font-size: 11px; text-transform: uppercase; }');
    printWindow.document.write('.password-box { border: 1px dashed #999; border-radius: 4px; padding: 5px 8px; display: inline-block; }');
    printWindow.document.write('.password-box table { width: auto; }');
    printWindow.document.write('.password-label { color: #666; font-size: 9px; padding-right: 8px; }');
    printWindow.document.write('.password-value { color: #c0392b; font-size: 12px; font-weight: bold; letter-spacing: 2px; font-family: Consolas, monospace; }');
    printWindow.document.write('.card-footer { position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #ccc; padding: 6px 12px; background: #fff; }');
    printWindow.document.write('.card-footer table { width: 100%; }');
    printWindow.document.write('.card-footer td { color: #666; font-size: 9px; }');
    printWindow.document.write('.year-badge { border: 1px solid #999; color: #333; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }');
    printWindow.document.write('.footer-center { text-align: center; color: #666; }');
    printWindow.document.write('.footer-right { text-align: right; color: #999; font-size: 8px; }');
    printWindow.document.write('@media print { @page { size: A4; margin: 15mm; } }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() { printWindow.print(); }, 250);
}

// Validasi Dokumen Rapor
$(document).on('click', '.btn-validasi-rapor', function() {
    const btn = $(this);
    const nilaiId = btn.data('id');
    const status = btn.data('status');
    const statusText = status === 'valid' ? 'memvalidasi' : 'menolak';
    const statusLabel = status === 'valid' ? 'Valid' : 'Invalid';
    
    Swal.fire({
        title: `${statusLabel} Dokumen Rapor?`,
        html: status === 'invalid' 
            ? `<div class="form-group text-left">
                <label>Catatan penolakan (opsional):</label>
                <textarea id="catatanValidasi" class="form-control" rows="3" placeholder="Masukkan catatan penolakan..."></textarea>
               </div>`
            : `<p>Apakah Anda yakin ingin ${statusText} dokumen rapor ini?</p>`,
        icon: status === 'valid' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'valid' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="fas fa-${status === 'valid' ? 'check' : 'times'}"></i> Ya, ${statusLabel}`,
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true,
        preConfirm: () => {
            return status === 'invalid' ? document.getElementById('catatanValidasi')?.value : null;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/pendaftar/rapor/${nilaiId}/validasi`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    catatan: result.value || null
                },
                beforeSend: function() {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        
                        // Update button styles
                        const row = btn.closest('tr');
                        const btnGroup = row.find('.btn-validasi-rapor');
                        
                        btnGroup.each(function() {
                            const thisBtn = $(this);
                            const btnStatus = thisBtn.data('status');
                            
                            thisBtn.removeClass('btn-success btn-danger btn-outline-success btn-outline-danger');
                            
                            if (btnStatus === status) {
                                thisBtn.addClass(status === 'valid' ? 'btn-success' : 'btn-danger');
                            } else {
                                thisBtn.addClass(btnStatus === 'valid' ? 'btn-outline-success' : 'btn-outline-danger');
                            }
                            
                            thisBtn.prop('disabled', false).html(`<i class="fas fa-${btnStatus === 'valid' ? 'check' : 'times'}"></i>`);
                        });
                        
                        // Add timestamp
                        const now = new Date();
                        const timeStr = now.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit'}) + ' ' + 
                                       now.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                        
                        // Update or add timestamp
                        let timeEl = row.find('td:last small.text-muted');
                        if (timeEl.length === 0) {
                            row.find('td:last').append('<br><small class="text-muted">' + timeStr + '</small>');
                        } else {
                            timeEl.text(timeStr);
                        }
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                        btn.prop('disabled', false).html(`<i class="fas fa-${status === 'valid' ? 'check' : 'times'}"></i>`);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat memvalidasi dokumen.', 'error');
                    btn.prop('disabled', false).html(`<i class="fas fa-${status === 'valid' ? 'check' : 'times'}"></i>`);
                }
            });
        }
    });
});
</script>
@stop

