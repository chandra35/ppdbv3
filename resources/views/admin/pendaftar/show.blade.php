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
                <div class="col-md-6">
                    <div class="btn-group">
                        <a href="{{ route('admin.pendaftar.edit', $pendaftar->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Lengkap
                        </a>
                        <button type="button" class="btn btn-warning btn-sm" onclick="resetPassword()">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="showPassword()">
                            <i class="fas fa-eye"></i> Lihat Password
                        </button>
                        @if($pendaftar->is_finalisasi)
                        <button type="button" class="btn btn-danger btn-sm" onclick="batalFinalisasi()">
                            <i class="fas fa-unlock"></i> Batal Finalisasi
                        </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @php
        $dokumenCount = $pendaftar->dokumen->count();
        $validCount = $pendaftar->dokumen->where('status_verifikasi', 'valid')->count();
        $pendingCount = $pendaftar->dokumen->where('status_verifikasi', 'pending')->count();
        $invalidCount = $pendaftar->dokumen->where('status_verifikasi', 'invalid')->count();
        $revisionCount = $pendaftar->dokumen->where('status_verifikasi', 'revision')->count();
    @endphp

    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4">
            <!-- Profile Box -->
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <div class="text-center">
                        @php
                            // Prioritas foto: 1. Dokumen pas_foto yang valid, 2. Foto upload manual, 3. Avatar
                            $pasFoto = $pendaftar->dokumen->where('jenis_dokumen', 'pas_foto')->where('status_verifikasi', 'valid')->first();
                            
                            if($pasFoto && file_exists(public_path('storage/' . $pasFoto->file_path))) {
                                // Gunakan foto dari dokumen yang sudah disetujui
                                $avatarSrc = asset('storage/' . $pasFoto->file_path);
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
                            <td><i class="fas fa-calendar text-muted"></i> <strong>Terdaftar</strong></td>
                            <td class="text-right"><small>{{ $pendaftar->created_at->format('d/m/Y') }}</small></td>
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
                                        <td class="p-1">{{ optional($pendaftar->ortu)->pekerjaan_ayah ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pendidikan</td>
                                        <td class="p-1">{{ optional($pendaftar->ortu)->pendidikan_ayah ?? '-' }}</td>
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
                                        <td class="p-1">{{ optional($pendaftar->ortu)->pekerjaan_ibu ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-1 text-muted">Pendidikan</td>
                                        <td class="p-1">{{ optional($pendaftar->ortu)->pendidikan_ibu ?? '-' }}</td>
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

    <!-- Dokumen Pendaftaran (Dipindah ke atas untuk memudahkan verifikasi) -->
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
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" 
                                               class="dokumen-link"
                                               data-url="{{ asset('storage/' . $dokumen->file_path) }}"
                                               data-title="{{ $docLabel }}"
                                               data-dokumen-id="{{ $dokumen->id }}"
                                               data-dokumen-status="{{ $dokumen->status_verifikasi }}"
                                               data-jenis-dokumen="{{ $dokumen->jenis_dokumen }}"
                                               data-type="image">
                                                <img src="{{ asset('storage/' . $dokumen->file_path) }}" class="card-img-top" style="height: 85px; object-fit: cover;">
                                            </a>
                                        @else
                                            <a href="javascript:void(0);"
                                               class="dokumen-link"
                                               data-url="{{ asset('storage/' . $dokumen->file_path) }}"
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
                                            
                                            @if($dokumen->jenis_dokumen != 'pas_foto')
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
                                            @else
                                                <small class="text-muted" style="font-size: 8px;"><i class="fas fa-info-circle"></i> Auto</small>
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
                <div class="modal-body text-center">
                    <div id="dokumenContent"></div>
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
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
    let currentScale = 1;
    
    // Gallery data
    let dokumenGallery = [];
    let currentIndex = 0;
    
    // Build gallery from all documents (images and PDFs)
    function buildGallery() {
        dokumenGallery = [];
        $('.dokumen-link').each(function() {
            const type = $(this).data('type'); // 'image' or 'pdf'
            dokumenGallery.push({
                url: $(this).data('url'),
                title: $(this).data('title'),
                dokumenId: $(this).data('dokumen-id'),
                status: $(this).data('dokumen-status'),
                jenisDokumen: $(this).data('jenis-dokumen'),
                type: type
            });
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
        container.empty();
        
        // Skip untuk pas_foto
        if (currentJenisDokumen === 'pas_foto') {
            container.html('<p class="text-muted mb-0"><i class="fas fa-info-circle"></i> Pas foto tidak perlu approval</p>');
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
</script>
@stop
