@extends('layouts.pendaftar')

@section('title', 'Status Pendaftaran')
@section('page-title', 'Status Pendaftaran')

@section('breadcrumb')
<li class="breadcrumb-item active">Status</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Status Card -->
        <div class="card">
            <div class="card-body text-center py-5">
                @if($calonSiswa->status_admisi === 'diterima')
                    <div class="mb-4">
                        <span class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                              style="width: 100px; height: 100px;">
                            <i class="fas fa-check fa-3x text-white"></i>
                        </span>
                    </div>
                    <h2 class="text-success">SELAMAT!</h2>
                    <h4 class="mb-3">Anda Dinyatakan DITERIMA</h4>
                    <p class="text-muted">
                        Silakan lakukan daftar ulang sesuai jadwal yang ditentukan.
                    </p>
                @elseif($calonSiswa->status_admisi === 'ditolak')
                    <div class="mb-4">
                        <span class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center" 
                              style="width: 100px; height: 100px;">
                            <i class="fas fa-times fa-3x text-white"></i>
                        </span>
                    </div>
                    <h2 class="text-danger">Mohon Maaf</h2>
                    <h4 class="mb-3">Anda Belum Diterima</h4>
                    <p class="text-muted">
                        Tetap semangat dan jangan menyerah!
                    </p>
                @elseif($calonSiswa->status_admisi === 'cadangan')
                    <div class="mb-4">
                        <span class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center" 
                              style="width: 100px; height: 100px;">
                            <i class="fas fa-clock fa-3x text-white"></i>
                        </span>
                    </div>
                    <h2 class="text-warning">CADANGAN</h2>
                    <h4 class="mb-3">Anda Masuk Daftar Cadangan</h4>
                    <p class="text-muted">
                        Mohon menunggu informasi lebih lanjut dari panitia.
                    </p>
                @else
                    <div class="mb-4">
                        <span class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center" 
                              style="width: 100px; height: 100px;">
                            <i class="fas fa-hourglass-half fa-3x text-white"></i>
                        </span>
                    </div>
                    <h2 class="text-info">Dalam Proses</h2>
                    <h4 class="mb-3">Pendaftaran Sedang Diproses</h4>
                    <p class="text-muted">
                        Pastikan semua data dan dokumen sudah lengkap.<br>
                        Hasil seleksi akan diumumkan sesuai jadwal.
                    </p>
                @endif
            </div>
        </div>

        <!-- Detail Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Detail Pendaftaran
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <td width="40%">Nomor Registrasi</td>
                        <td><strong>{{ $calonSiswa->nomor_registrasi }}</strong></td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>{{ $calonSiswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>
                            {{ $calonSiswa->nisn }}
                            @if($calonSiswa->nisn_valid)
                                <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Valid</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Gelombang</td>
                        <td>{{ $calonSiswa->gelombangPendaftaran->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Daftar</td>
                        <td>{{ $calonSiswa->created_at->format('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Status Verifikasi</td>
                        <td>
                            <span class="status-badge status-{{ $calonSiswa->status_verifikasi }}">
                                {{ ucfirst($calonSiswa->status_verifikasi) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Status Admisi</td>
                        <td>
                            <span class="status-badge status-{{ $calonSiswa->status_admisi }}">
                                {{ ucfirst($calonSiswa->status_admisi) }}
                            </span>
                        </td>
                    </tr>
                    @if($calonSiswa->catatan_verifikasi)
                    <tr>
                        <td>Catatan Verifikasi</td>
                        <td>{{ $calonSiswa->catatan_verifikasi }}</td>
                    </tr>
                    @endif
                    @if($calonSiswa->catatan_admisi)
                    <tr>
                        <td>Catatan Admisi</td>
                        <td>{{ $calonSiswa->catatan_admisi }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-2"></i>
                    Tahapan Pendaftaran
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- Step 1: Registrasi -->
                    <div class="time-label">
                        <span class="bg-success">Selesai</span>
                    </div>
                    <div>
                        <i class="fas fa-user-plus bg-success"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Registrasi Akun</h3>
                            <div class="timeline-body text-muted">
                                Akun berhasil dibuat pada {{ $calonSiswa->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Kelengkapan Data -->
                    <div class="time-label">
                        @if($calonSiswa->is_complete)
                            <span class="bg-success">Selesai</span>
                        @else
                            <span class="bg-warning">Proses</span>
                        @endif
                    </div>
                    <div>
                        <i class="fas fa-file-alt {{ $calonSiswa->is_complete ? 'bg-success' : 'bg-warning' }}"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Kelengkapan Data & Dokumen</h3>
                            <div class="timeline-body text-muted">
                                @if($calonSiswa->is_complete)
                                    Semua data dan dokumen telah lengkap
                                @else
                                    Silakan lengkapi data pribadi, data orang tua, dan upload dokumen
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Verifikasi -->
                    <div class="time-label">
                        @if($calonSiswa->status_verifikasi === 'verified')
                            <span class="bg-success">Selesai</span>
                        @elseif($calonSiswa->status_verifikasi === 'revision')
                            <span class="bg-danger">Revisi</span>
                        @else
                            <span class="bg-secondary">Menunggu</span>
                        @endif
                    </div>
                    <div>
                        <i class="fas fa-clipboard-check {{ $calonSiswa->status_verifikasi === 'verified' ? 'bg-success' : 'bg-secondary' }}"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Verifikasi Berkas</h3>
                            <div class="timeline-body text-muted">
                                @if($calonSiswa->status_verifikasi === 'verified')
                                    Data dan dokumen telah diverifikasi
                                @elseif($calonSiswa->status_verifikasi === 'revision')
                                    Ada revisi yang perlu diperbaiki
                                @else
                                    Menunggu verifikasi dari panitia
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Pengumuman -->
                    <div class="time-label">
                        @if($calonSiswa->status_admisi !== 'pending')
                            <span class="bg-{{ $calonSiswa->status_admisi === 'diterima' ? 'success' : ($calonSiswa->status_admisi === 'ditolak' ? 'danger' : 'warning') }}">
                                {{ ucfirst($calonSiswa->status_admisi) }}
                            </span>
                        @else
                            <span class="bg-secondary">Menunggu</span>
                        @endif
                    </div>
                    <div>
                        <i class="fas fa-bullhorn {{ $calonSiswa->status_admisi !== 'pending' ? 'bg-' . ($calonSiswa->status_admisi === 'diterima' ? 'success' : 'secondary') : 'bg-secondary' }}"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Pengumuman Hasil Seleksi</h3>
                            <div class="timeline-body text-muted">
                                @if($calonSiswa->status_admisi !== 'pending')
                                    Hasil seleksi telah diumumkan
                                @else
                                    Menunggu pengumuman hasil seleksi
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <i class="fas fa-flag-checkered bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
