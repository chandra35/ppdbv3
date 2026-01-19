@extends('layouts.pendaftar')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white mb-1">Selamat Datang, {{ $calonSiswa->nama_lengkap }}!</h4>
                        <p class="text-white-50 mb-0">
                            No. Registrasi: <strong class="text-white">{{ $calonSiswa->nomor_registrasi }}</strong>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="status-badge status-{{ $calonSiswa->status_verifikasi }}">
                            @if($calonSiswa->status_verifikasi === 'verified')
                                <i class="fas fa-check-circle"></i> Terverifikasi
                            @elseif($calonSiswa->status_verifikasi === 'pending')
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            @elseif($calonSiswa->status_verifikasi === 'revision')
                                <i class="fas fa-exclamation-circle"></i> Perlu Revisi
                            @else
                                {{ ucfirst($calonSiswa->status_verifikasi) }}
                            @endif
                        </span>
                    </div>
                </div>
                
                {{-- Keterangan Status Verifikasi --}}
                @if($calonSiswa->is_finalisasi)
                    @if($calonSiswa->status_verifikasi === 'verified' && $calonSiswa->nomor_tes)
                        <div class="alert alert-success mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-check-circle text-white mr-2"></i>
                            <span class="text-white"><strong>Sudah Diverifikasi!</strong> Silahkan cetak Kartu Tes untuk mengikuti ujian. <a href="#" data-toggle="modal" data-target="#kartuUjianModal" class="text-white" style="text-decoration: underline; font-weight: bold;">Klik Disini</a></span>
                        </div>
                    @elseif($calonSiswa->status_verifikasi === 'pending')
                        <div class="alert alert-warning mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-hourglass-half text-white mr-2"></i>
                            <span class="text-white"><strong>Menunggu Verifikasi Berkas Oleh Panitia.</strong> Mohon tunggu 1-3 hari kerja.</span>
                        </div>
                    @elseif($calonSiswa->status_verifikasi === 'revision')
                        <div class="alert alert-danger mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-exclamation-triangle text-white mr-2"></i>
                            <span class="text-white"><strong>Perlu Revisi!</strong> Silahkan periksa dan perbaiki dokumen yang diminta.</span>
                        </div>
                    @endif
                @endif

                {{-- Detail Dokumen Bermasalah --}}
                @if(isset($dokumenBermasalah) && $dokumenBermasalah->count() > 0)
                    <div class="mt-3 p-3" style="background: rgba(220, 53, 69, 0.3); border: 1px solid rgba(255,255,255,0.4); border-radius: 8px;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-file-exclamation text-white mr-2" style="font-size: 1.2rem;"></i>
                            <strong class="text-white">{{ $dokumenBermasalah->count() }} Dokumen Perlu Diperbaiki:</strong>
                        </div>
                        @foreach($dokumenBermasalah as $dok)
                            <div class="mb-2 ml-4 p-2" style="background: rgba(255,255,255,0.1); border-radius: 5px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-white">
                                        <i class="fas fa-file mr-1"></i>
                                        {{ ucwords(str_replace('_', ' ', $dok->jenis_dokumen)) }}
                                    </span>
                                    @if($dok->status_verifikasi === 'revision')
                                        <span class="badge" style="background: #ffc107; color: #000;">Perlu Revisi</span>
                                    @else
                                        <span class="badge" style="background: #dc3545; color: #fff;">Tidak Valid</span>
                                    @endif
                                </div>
                                @if($dok->catatan_verifikasi)
                                    <small class="text-white-50 d-block mt-1">
                                        <i class="fas fa-comment-alt mr-1"></i>{{ $dok->catatan_verifikasi }}
                                    </small>
                                @endif
                            </div>
                        @endforeach
                        <a href="{{ route('pendaftar.dokumen') }}" class="btn btn-light btn-sm mt-2">
                            <i class="fas fa-upload mr-1"></i> Upload Ulang Dokumen
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Info Cards -->
    <div class="col-12">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $progress['overall'] }}%</h3>
                <p>Progress Pendaftaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Progress Card -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Progress Pendaftaran
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Data Pribadi</span>
                        <span class="font-weight-bold">{{ $progress['data_diri'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['data_diri'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Data Orang Tua</span>
                        <span class="font-weight-bold">{{ $progress['data_ortu'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['data_ortu'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Upload Dokumen</span>
                        <span class="font-weight-bold">{{ $progress['dokumen'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['dokumen'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Nilai Rapor</span>
                        <span class="font-weight-bold">{{ $progress['nilai_rapor'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['nilai_rapor'] }}%"></div>
                    </div>
                </div>
                
                @if(isset($progress['pilihan_program']))
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Pilihan Program</span>
                        <span class="font-weight-bold">{{ $progress['pilihan_program'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['pilihan_program'] }}%"></div>
                    </div>
                </div>
                @endif
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Verifikasi</span>
                        <span class="font-weight-bold">{{ $progress['verifikasi'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['verifikasi'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.data-pribadi') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Data Pribadi</h6>
                                @if($calonSiswa->data_diri_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.data-ortu') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Data Orang Tua</h6>
                                @if($calonSiswa->data_ortu_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.nilai-rapor') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-warning">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Nilai Rapor</h6>
                                @if($calonSiswa->nilai_rapor_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.dokumen') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-info">
                                    <i class="fas fa-file-upload"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Upload Dokumen</h6>
                                @if($calonSiswa->data_dokumen_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.status') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-warning">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Status</h6>
                                <small class="text-muted">Lihat Status</small>
                            </div>
                        </a>
                    </div>
                    
                    {{-- Lokasi Card --}}
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card quick-action-card h-100 text-center p-3" id="locationCard" style="cursor: pointer;" onclick="requestLocation()">
                            @if($calonSiswa->registration_location_source)
                                <div class="icon text-success">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Lokasi</h6>
                                <small class="text-success"><i class="fas fa-check"></i> Terdeteksi</small>
                            @else
                                <div class="icon text-danger" id="locationIcon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h6 class="mt-2 mb-0" id="locationTitle">Lokasi</h6>
                                <small class="text-danger" id="locationStatus">
                                    <i class="fas fa-times"></i> Belum Aktif
                                    @if($wajibLokasi)<span class="badge badge-danger ml-1" style="font-size: 0.6rem;">WAJIB</span>@endif
                                </small>
                            @endif
                        </div>
                    </div>
                    
                    @if($calonSiswa->is_finalisasi)
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.cetak-bukti-registrasi.preview') }}" target="_blank" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-primary">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Bukti Registrasi</h6>
                                <small class="text-muted">Preview & Download</small>
                            </div>
                        </a>
                    </div>
                    
                    @if($calonSiswa->nomor_tes)
                    <div class="col-md-4 col-6 mb-3">
                        <a href="#" class="text-decoration-none" data-toggle="modal" data-target="#kartuUjianModal">
                            <div class="card quick-action-card h-100 text-center p-3 border-success">
                                <div class="icon text-success">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Kartu Ujian</h6>
                                <small class="text-success"><i class="fas fa-print"></i> Siap Cetak</small>
                            </div>
                        </a>
                    </div>
                    @else
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card quick-action-card h-100 text-center p-3" style="opacity: 0.6;">
                            <div class="icon text-secondary">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Kartu Ujian</h6>
                            <small class="text-warning"><i class="fas fa-clock"></i> Menunggu Verifikasi</small>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Profile Card -->
        @php
            $fotoProfileDashboard = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
            $fotoProfileDashboardUrl = $fotoProfileDashboard ? asset('storage/' . $fotoProfileDashboard->file_path) : null;
        @endphp
        <div class="card">
            <div class="card-body text-center">
                @if($fotoProfileDashboardUrl)
                    <img src="{{ $fotoProfileDashboardUrl }}" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #667eea;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($calonSiswa->nama_lengkap) }}&size=150&background=667eea&color=fff'">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($calonSiswa->nama_lengkap) }}&size=150&background=667eea&color=fff" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px;">
                @endif
                <h5 class="mb-1">{{ $calonSiswa->nama_lengkap }}</h5>
                <p class="text-muted mb-2">NISN: {{ $calonSiswa->nisn }}</p>
                @if($calonSiswa->nisn_valid)
                    <span class="badge badge-success"><i class="fas fa-check"></i> NISN Terverifikasi</span>
                @endif
            </div>
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-6 border-right">
                        <small class="text-muted d-block">Terdaftar</small>
                        <strong>{{ $calonSiswa->created_at->format('d M Y') }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tahun Ajaran</small>
                        <strong>{{ $calonSiswa->tahunPelajaran->nama ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Status Timeline
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="timeline timeline-inverse p-3">
                    <div class="time-label">
                        <span class="bg-success">Pendaftaran</span>
                    </div>
                    <div>
                        <i class="fas fa-user-plus bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->created_at->format('d M Y H:i') }}</span>
                            <h3 class="timeline-header">Akun Dibuat</h3>
                            <div class="timeline-body">
                                Pendaftaran berhasil dilakukan
                            </div>
                        </div>
                    </div>

                    @if($calonSiswa->status_verifikasi === 'verified')
                    <div class="time-label">
                        <span class="bg-info">Verifikasi</span>
                    </div>
                    <div>
                        <i class="fas fa-check bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->verified_at?->format('d M Y H:i') ?? '-' }}</span>
                            <h3 class="timeline-header">Data Terverifikasi</h3>
                            <div class="timeline-body">
                                {{ $calonSiswa->catatan_verifikasi ?? 'Data pendaftaran telah diverifikasi' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($calonSiswa->status_admisi !== 'pending')
                    <div class="time-label">
                        <span class="bg-{{ $calonSiswa->status_admisi === 'diterima' ? 'success' : ($calonSiswa->status_admisi === 'ditolak' ? 'danger' : 'warning') }}">
                            Hasil Seleksi
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-{{ $calonSiswa->status_admisi === 'diterima' ? 'check-circle' : 'times-circle' }} bg-{{ $calonSiswa->status_admisi === 'diterima' ? 'success' : 'danger' }}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->approved_at?->format('d M Y H:i') ?? '-' }}</span>
                            <h3 class="timeline-header">{{ ucfirst($calonSiswa->status_admisi) }}</h3>
                            <div class="timeline-body">
                                {{ $calonSiswa->catatan_admisi ?? '-' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$calonSiswa->registration_location_source)
@push('scripts')
<script>
function requestLocation() {
    const card = document.getElementById('locationCard');
    const icon = document.getElementById('locationIcon');
    const title = document.getElementById('locationTitle');
    const status = document.getElementById('locationStatus');
    
    // Show loading state
    icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    icon.className = 'icon text-primary';
    status.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Mendeteksi...';
    status.className = 'text-primary';
    
    if (!navigator.geolocation) {
        handleFallbackIP('Browser tidak mendukung GPS');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            // GPS success
            saveLocation({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy,
                altitude: position.coords.altitude,
                location_source: 'gps'
            });
        },
        function(error) {
            let errorMsg = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin ditolak';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Tidak tersedia';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Waktu habis';
                    break;
            }
            handleFallbackIP(errorMsg);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

function handleFallbackIP(errorMsg) {
    const status = document.getElementById('locationStatus');
    status.innerHTML = '<i class="fas fa-globe"></i> Via IP...';
    status.className = 'text-info';
    
    // Use IP fallback
    saveLocation({
        location_source: 'ip'
    });
}

function saveLocation(data) {
    fetch('{{ route("pendaftar.update-location") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        const icon = document.getElementById('locationIcon');
        const status = document.getElementById('locationStatus');
        const card = document.getElementById('locationCard');
        
        if (result.success) {
            // Success state
            icon.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
            icon.className = 'icon text-success';
            
            if (data.location_source === 'gps') {
                status.innerHTML = '<i class="fas fa-check"></i> GPS';
            } else {
                status.innerHTML = '<i class="fas fa-check"></i> IP';
            }
            status.className = 'text-success';
            
            // Add location info as tooltip
            const locationParts = [result.data.city, result.data.region].filter(Boolean);
            if (locationParts.length) {
                card.title = locationParts.join(', ');
            }
            
            // Remove click handler
            card.onclick = null;
            card.style.cursor = 'default';
        } else {
            resetLocationCard(result.message || 'Gagal menyimpan');
        }
    })
    .catch(error => {
        resetLocationCard('Error');
    });
}

function resetLocationCard(message) {
    const icon = document.getElementById('locationIcon');
    const status = document.getElementById('locationStatus');
    
    icon.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
    icon.className = 'icon text-danger';
    status.innerHTML = '<i class="fas fa-times"></i> ' + message;
    status.className = 'text-danger';
}
</script>
@endpush
@endif
@endsection
