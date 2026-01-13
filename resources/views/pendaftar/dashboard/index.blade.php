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
                            {{ ucfirst($calonSiswa->status_verifikasi) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Info Cards -->
    <div class="col-lg-6 col-6">
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
    
    <div class="col-lg-6 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ ucfirst($calonSiswa->status_admisi) }}</h3>
                <p>Status Admisi</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-graduate"></i>
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
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="#" class="text-decoration-none" data-toggle="modal" data-target="#kartuUjianModal">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-danger">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Kartu Ujian</h6>
                                <small class="text-muted">Preview & Print</small>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($calonSiswa->nama_lengkap) }}&size=150&background=667eea&color=fff" 
                     class="rounded-circle mb-3" style="width: 100px; height: 100px;">
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
