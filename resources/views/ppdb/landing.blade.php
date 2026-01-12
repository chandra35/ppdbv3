<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta --}}
    <title>{{ $siteSettings->meta_title ?: ($sekolahSettings->nama_sekolah . ' - PPDB Online') }}</title>
    <meta name="description" content="{{ $siteSettings->meta_description ?: 'Pendaftaran Peserta Didik Baru (PPDB) Online ' . $sekolahSettings->nama_sekolah }}">
    <meta name="keywords" content="{{ $siteSettings->meta_keywords ?: 'ppdb, pendaftaran, sekolah, ' . $sekolahSettings->nama_sekolah }}">
    
    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $siteSettings->meta_title ?: ($sekolahSettings->nama_sekolah . ' - PPDB Online') }}">
    <meta property="og:description" content="{{ $siteSettings->meta_description }}">
    <meta property="og:image" content="{{ $siteSettings->hero_image_url ?: asset('images/default-og.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    
    @if($siteSettings->favicon)
    <link rel="icon" href="{{ $siteSettings->favicon_url }}" type="image/x-icon">
    @elseif($sekolahSettings->logo)
    <link rel="icon" href="{{ Storage::url($sekolahSettings->logo) }}" type="image/x-icon">
    @endif
    
    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        :root {
            --primary-color: {{ $siteSettings->primary_color ?: '#007bff' }};
            --secondary-color: {{ $siteSettings->secondary_color ?: '#6c757d' }};
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--primary-color) 85%, black);
            border-color: color-mix(in srgb, var(--primary-color) 85%, black);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: auto;
            padding: 60px 0 50px;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('{{ $siteSettings->hero_image_url }}') center/cover no-repeat;
            opacity: 0.1;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-section .card {
            transition: transform 0.2s ease;
        }
        
        .hero-section .card:hover {
            transform: translateY(-3px);
        }
        
        /* Countdown Styles */
        .countdown-wrapper {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .countdown-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .countdown-status.upcoming {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .countdown-status.open {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .countdown-status.closed {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            50% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
        }
        
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .countdown-box {
            min-width: 85px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1rem 0.75rem;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .countdown-box:hover {
            transform: translateY(-3px);
            background: rgba(255,255,255,0.2);
        }
        
        .countdown-value {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .countdown-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.85;
            margin-top: 0.5rem;
        }
        
        .countdown-separator {
            font-size: 2rem;
            font-weight: 700;
            opacity: 0.5;
            align-self: center;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.2; }
        }
        
        .countdown-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .countdown-info-item {
            text-align: center;
        }
        
        .countdown-info-item i {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }
        
        .countdown-info-item small {
            display: block;
            opacity: 0.8;
            font-size: 0.75rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .card-jalur:hover {
            transform: translateY(-3px);
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #e9ecef 100%);
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            background: white;
            transition: all 0.3s ease;
        }
        
        .timeline-item.active::before {
            background: var(--primary-color);
            animation: pulse 2s infinite;
        }
        
        .timeline-item:hover::before {
            transform: scale(1.3);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }
        
        /* Alur Pendaftaran */
        .alur-step {
            display: flex;
            align-items: flex-start;
            position: relative;
        }
        
        .alur-number {
            width: 45px;
            height: 45px;
            min-width: 45px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            position: relative;
            z-index: 2;
        }
        
        .alur-content {
            flex: 1;
            padding-bottom: 30px;
        }
        
        .alur-content h5 {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .alur-content p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        .alur-line {
            position: absolute;
            left: 22px;
            top: 45px;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .alur-step:last-child .alur-line {
            display: none;
        }
        
        .alur-step:last-child .alur-content {
            padding-bottom: 0;
        }
        
        /* Footer */
        footer {
            background: #212529;
            color: #fff;
        }
        
        footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
        }
        
        footer a:hover {
            color: #fff;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: #fff;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }
        
        /* Section spacing */
        section {
            padding: 60px 0;
        }
        
        .section-title {
            margin-bottom: 10px;
            color: #2d3748;
        }
        
        .section-subtitle {
            color: #718096;
            margin-bottom: 40px;
        }
        
        /* Smooth section backgrounds */
        .bg-soft {
            background: #f8fafc;
        }
        
        /* Berita Card */
        .berita-card .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        
        /* WhatsApp Float Button */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .whatsapp-float a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            color: white;
            font-size: 28px;
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
        }
        
        .whatsapp-float a:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.5);
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('ppdb.landing') }}">
                @if($sekolahSettings->logo)
                    <img src="{{ Storage::url($sekolahSettings->logo) }}" alt="{{ $sekolahSettings->nama_sekolah }}" class="me-2">
                @elseif($siteSettings->logo)
                    <img src="{{ $siteSettings->logo_url }}" alt="{{ $sekolahSettings->nama_sekolah }}" class="me-2">
                @else
                    <i class="fas fa-graduation-cap fa-2x text-primary me-2"></i>
                @endif
                <div>
                    <strong class="d-block">{{ $sekolahSettings->nama_sekolah ?: 'PPDB Online' }}</strong>
                    <small class="text-muted" style="font-size: 11px;">
                        {{ \App\Models\SekolahSettings::JENJANG_LIST[$sekolahSettings->jenjang] ?? $sekolahSettings->jenjang }}
                    </small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#alur">Info PPDB</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#berita">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        @auth
                            {{-- User sudah login --}}
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle px-4" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if(Auth::user()->isAdmin())
                                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</a></li>
                                    @elseif(Auth::user()->hasAnyRole(['operator', 'verifikator']))
                                        <li><a class="dropdown-item" href="{{ route('operator.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Operator</a></li>
                                    @else
                                        <li><a class="dropdown-item" href="{{ route('ppdb.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Saya</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('ppdb.logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            {{-- User belum login --}}
                            <a class="btn btn-primary px-4" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section id="beranda" class="hero-section d-flex align-items-center text-white" style="padding-top: 80px;">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-10 mx-auto text-center">
                    {{-- School Name --}}
                    <h1 class="display-5 fw-bold mb-2">{{ $sekolahSettings->nama_sekolah }}</h1>
                    <p class="lead mb-4 opacity-90">Pendaftaran Peserta Didik Baru (PPDB) Online</p>
                    
                    @php
                        $jalurDenganGelombang = $jalurAktif->filter(fn($j) => $j->gelombang->isNotEmpty());
                    @endphp
                    
                    {{-- Status Pendaftaran dengan Countdown - Hidden saat open --}}
                    @if($gelombangAktif && $statusPendaftaran != 'open')
                    <div class="countdown-wrapper text-white text-center mb-4">
                        {{-- Status Badge --}}
                        <div class="countdown-status {{ $statusPendaftaran }}">
                            @if($statusPendaftaran == 'upcoming')
                                <i class="fas fa-hourglass-half"></i>
                                <span>Pendaftaran Segera Dibuka</span>
                            @else
                                <i class="fas fa-door-closed"></i>
                                <span>Pendaftaran Telah Ditutup</span>
                            @endif
                        </div>
                        
                        {{-- Countdown Title - Hanya tampil saat belum dibuka --}}
                        @if($countdownTarget && $statusPendaftaran == 'upcoming')
                        <p class="mb-3 opacity-90">
                            <i class="fas fa-clock me-1"></i> Pendaftaran akan dibuka dalam:
                        </p>
                        
                        {{-- Countdown Timer --}}
                        <div id="countdown" class="countdown-timer" data-target="{{ $countdownTarget->format('Y-m-d H:i:s') }}">
                            <div class="countdown-box">
                                <div class="countdown-value" id="days">00</div>
                                <div class="countdown-label">Hari</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" id="hours">00</div>
                                <div class="countdown-label">Jam</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" id="minutes">00</div>
                                <div class="countdown-label">Menit</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" id="seconds">00</div>
                                <div class="countdown-label">Detik</div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Info Pendaftaran - Hanya tampil sesuai pengaturan --}}
                        @if(($gelombangAktif->jalur && $gelombangAktif->jalur->tampil_di_publik) || $gelombangAktif->tampil_nama_gelombang || $gelombangAktif->tampil_kuota)
                        <div class="countdown-info">
                            @if($gelombangAktif->jalur && $gelombangAktif->jalur->tampil_di_publik)
                            <div class="countdown-info-item">
                                <i class="fas fa-route"></i>
                                <small>Jalur: <strong>{{ $gelombangAktif->jalur->nama }}</strong></small>
                            </div>
                            @endif
                            @if($gelombangAktif->tampil_nama_gelombang)
                            <div class="countdown-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <small>Periode: <strong>{{ $gelombangAktif->tanggal_buka->format('d M') }} - {{ $gelombangAktif->tanggal_tutup->format('d M Y') }}</strong></small>
                            </div>
                            @endif
                            @if($gelombangAktif->tampil_kuota && $gelombangAktif->kuota)
                            <div class="countdown-info-item">
                                <i class="fas fa-users"></i>
                                <small>Kuota: <strong>{{ $gelombangAktif->kuota - $gelombangAktif->kuota_terisi }} tersisa</strong></small>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    {{-- Main CTA Cards - Hanya tampil jika pendaftaran dibuka --}}
                    @if($statusPendaftaran == 'open')
                    <div class="row justify-content-center g-3 mb-4">
                        {{-- Card Daftar Baru --}}
                        <div class="col-sm-6 col-md-5 col-lg-4">
                            <div class="card bg-white text-dark h-100 border-0 shadow">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                                        <i class="fas fa-user-plus fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Pendaftaran Baru</h5>
                                    <p class="text-muted small mb-3">Belum punya akun? Daftar di sini untuk memulai pendaftaran PPDB</p>
                                    <a href="{{ route('pendaftar.landing') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-arrow-right me-2"></i> Daftar Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Card Login --}}
                        <div class="col-sm-6 col-md-5 col-lg-4">
                            <div class="card bg-white bg-opacity-10 text-white h-100 border border-white border-opacity-25">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-white bg-opacity-25 p-3 d-inline-flex mb-3">
                                        <i class="fas fa-sign-in-alt fa-2x"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2">Sudah Terdaftar?</h5>
                                    <p class="opacity-75 small mb-3">Login untuk melanjutkan pendaftaran atau cek status</p>
                                    <a href="{{ route('login') }}" class="btn btn-outline-light w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Info Badge --}}
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <span class="badge bg-white text-dark px-3 py-2">
                            <i class="fas fa-graduation-cap me-1"></i>
                            {{ \App\Models\SekolahSettings::JENJANG_LIST[$sekolahSettings->jenjang] ?? $sekolahSettings->jenjang }}
                        </span>
                        @if($sekolahSettings->npsn)
                        <span class="badge bg-white bg-opacity-25 px-3 py-2">NPSN: {{ $sekolahSettings->npsn }}</span>
                        @endif
                        @if($sekolahSettings->akreditasi)
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-award me-1"></i> Akreditasi {{ $sekolahSettings->akreditasi }}
                        </span>
                        @endif
                        @if($jalurDenganGelombang->isNotEmpty())
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i> Pendaftaran Dibuka
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Jalur Pendaftaran Section --}}
    @if($jalurAktif->count() > 0)
    <section id="jalur-pendaftaran" class="bg-soft">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Jalur Pendaftaran</h2>
            <p class="section-subtitle text-center">Pilih jalur pendaftaran yang sesuai dengan kriteria Anda</p>
            
            <div class="row g-4 justify-content-center">
                @foreach($jalurAktif as $jalur)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-left: 5px solid {{ $jalur->warna ?? '#007bff' }} !important;">
                        {{-- Card Header --}}
                        <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, {{ $jalur->warna ?? '#007bff' }}15 0%, {{ $jalur->warna ?? '#007bff' }}05 100%);">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle p-3 me-3 shadow-sm" style="background: white;">
                                    <i class="{{ $jalur->icon ?? 'fas fa-graduation-cap' }} fa-lg" style="color: {{ $jalur->warna ?? '#007bff' }};"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 fw-bold">{{ $jalur->nama }}</h5>
                                    @if($jalur->tahunPelajaran)
                                    <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> TA {{ $jalur->tahunPelajaran->nama }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            @if($jalur->deskripsi)
                            <p class="text-muted small mb-3">{{ Str::limit($jalur->deskripsi, 120) }}</p>
                            @endif
                            
                            {{-- Kuota Progress - Conditional --}}
                            @if($jalur->tampil_kuota ?? true)
                            <div class="mb-3 p-3 rounded" style="background: #f8f9fa;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small fw-semibold"><i class="fas fa-users me-1"></i> Kuota</span>
                                    <span class="small">
                                        <span class="fw-bold" style="color: {{ $jalur->warna ?? '#007bff' }}">{{ $jalur->kuota_tersisa }}</span> 
                                        <span class="text-muted">/ {{ $jalur->kuota }} tersedia</span>
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                    @php
                                        $persentase = $jalur->kuota > 0 ? (($jalur->kuota - $jalur->kuota_tersisa) / $jalur->kuota) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $persentase }}%; background: linear-gradient(90deg, {{ $jalur->warna ?? '#007bff' }} 0%, color-mix(in srgb, {{ $jalur->warna ?? '#007bff' }} 70%, white) 100%); border-radius: 4px;"
                                         aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">{{ number_format($persentase, 0) }}% terisi</small>
                            </div>
                            @endif
                            
                            {{-- Status Pendaftaran (Gelombang hidden, hanya tampilkan status) --}}
                            @if($jalur->gelombang->isNotEmpty())
                                @php
                                    $gelombangAktif = $jalur->gelombang->first();
                                @endphp
                                <div class="mb-3">
                                    <div class="border border-success rounded p-3" style="background: rgba(25, 135, 84, 0.05);">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-door-open text-success me-2"></i>
                                            <span class="fw-semibold text-success">Pendaftaran Dibuka</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i> 
                                                {{ $gelombangAktif->tanggal_buka->format('d M') }} - {{ $gelombangAktif->tanggal_tutup->format('d M Y') }}
                                            </small>
                                            <span class="badge bg-success rounded-pill">
                                                <i class="fas fa-clock me-1"></i>{{ $gelombangAktif->sisa_hari }} hari lagi
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @else
                            <div class="mb-3">
                                <div class="alert alert-secondary py-2 px-3 mb-0">
                                    <small><i class="fas fa-clock me-1"></i> Pendaftaran belum dibuka</small>
                                </div>
                            </div>
                            @endif
                            
                            @if($jalur->persyaratan)
                            <details class="small mb-2">
                                <summary class="fw-semibold text-primary cursor-pointer">
                                    <i class="fas fa-list-check me-1"></i> Lihat Persyaratan
                                </summary>
                                <div class="mt-2 p-2 bg-light rounded text-muted">
                                    {!! nl2br(e(Str::limit($jalur->persyaratan, 200))) !!}
                                </div>
                            </details>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                            @if($statusPendaftaran == 'open' && $jalur->gelombang->isNotEmpty())
                            <a href="{{ route('pendaftar.landing') }}" class="btn w-100 py-2" style="background: {{ $jalur->warna ?? '#007bff' }}; color: white;">
                                <i class="fas fa-arrow-right me-2"></i> Daftar Sekarang
                            </a>
                            @elseif($statusPendaftaran == 'upcoming')
                            <button class="btn btn-outline-warning w-100 py-2" disabled>
                                <i class="fas fa-clock me-1"></i> Segera Dibuka
                            </button>
                            @else
                            <button class="btn btn-outline-secondary w-100 py-2" disabled>
                                <i class="fas fa-times-circle me-1"></i> Pendaftaran Ditutup
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Alur & Jadwal Section (Combined) --}}
    <section id="alur">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Informasi Pendaftaran</h2>
            <p class="section-subtitle text-center">Alur pendaftaran dan jadwal kegiatan PPDB</p>
            
            <div class="row g-4 g-lg-5">
                {{-- Kolom Alur Pendaftaran --}}
                <div class="col-lg-6">
                    <h5 class="fw-bold mb-4 text-primary">
                        <i class="fas fa-list-ol me-2"></i>Alur Pendaftaran
                    </h5>
                    
                    @forelse($alurPendaftaran as $index => $alur)
                    <div class="alur-step">
                        <div class="alur-number" @if($alur->warna) style="background-color: {{ $alur->warna }}" @endif>
                            {{ $index + 1 }}
                        </div>
                        <div class="alur-content">
                            <h6 class="fw-semibold mb-1">{{ $alur->judul }}</h6>
                            <p class="mb-0">{{ $alur->deskripsi }}</p>
                        </div>
                        @if(!$loop->last)
                        <div class="alur-line"></div>
                        @endif
                    </div>
                    @empty
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">Alur pendaftaran belum dikonfigurasi</p>
                    </div>
                    @endforelse
                </div>
                
                {{-- Kolom Jadwal PPDB --}}
                <div class="col-lg-6" id="jadwal">
                    <h5 class="fw-bold mb-4 text-primary">
                        <i class="fas fa-calendar-alt me-2"></i>Jadwal PPDB
                    </h5>
                    
                    @if($jadwals->count() > 0)
                    <div class="timeline">
                        @foreach($jadwals as $jadwal)
                        <div class="timeline-item {{ $jadwal->is_ongoing ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">{{ $jadwal->nama_kegiatan }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> {{ $jadwal->tanggal_range }}
                                    </small>
                                </div>
                                <span class="badge {{ $jadwal->is_ongoing ? 'bg-success' : ($jadwal->is_upcoming ? 'bg-info' : 'bg-secondary') }}">
                                    {{ $jadwal->is_ongoing ? 'Berlangsung' : ($jadwal->is_upcoming ? 'Akan Datang' : 'Selesai') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                        <p>Jadwal belum tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Tentang Section --}}
    @if($siteSettings->about_content)
    <section id="tentang" class="bg-soft">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Tentang Sekolah</h2>
            <p class="section-subtitle text-center">Mengenal lebih dekat {{ $sekolahSettings->nama_sekolah }}</p>
            <div class="row align-items-center justify-content-center">
                @if($siteSettings->about_image)
                <div class="col-md-5 mb-4 mb-md-0">
                    <img src="{{ $siteSettings->about_image_url }}" class="img-fluid rounded-3" alt="Tentang Kami">
                </div>
                <div class="col-md-7">
                @else
                <div class="col-lg-8">
                @endif
                    <div class="ps-md-4">
                        {!! nl2br(e($siteSettings->about_content)) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Berita Section --}}
    @if($beritas->count() > 0)
    <section id="berita">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Berita & Pengumuman</h2>
            <p class="section-subtitle text-center">Informasi terbaru seputar PPDB</p>
            <div class="row g-4">
                @foreach($beritas as $berita)
                <div class="col-md-4">
                    <div class="card h-100 berita-card">
                        @if($berita->gambar)
                        <img src="{{ asset('storage/' . $berita->gambar) }}" class="card-img-top" alt="{{ $berita->judul }}">
                        @else
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-newspaper fa-3x text-white"></i>
                        </div>
                        @endif
                        <div class="card-body">
                            @if($berita->kategori)
                            <span class="badge bg-primary mb-2">{{ ucfirst($berita->kategori) }}</span>
                            @endif
                            @if($berita->is_featured)
                            <span class="badge bg-warning mb-2"><i class="fas fa-star"></i> Featured</span>
                            @endif
                            <h5 class="card-title">{{ Str::limit($berita->judul, 50) }}</h5>
                            <p class="card-text text-muted small">{{ $berita->excerpt }}</p>
                        </div>
                        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $berita->tanggal_publikasi->format('d M Y') }}
                            </small>
                            <a href="{{ route('ppdb.berita.show', $berita->slug) }}" class="btn btn-sm btn-outline-primary">
                                Baca <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Kontak Section --}}
    <section id="kontak" class="bg-soft">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Hubungi Kami</h2>
            <p class="section-subtitle text-center">Butuh bantuan? Jangan ragu untuk menghubungi kami</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <h5 class="fw-bold mb-4"><i class="fas fa-school text-primary me-2"></i> {{ $sekolahSettings->nama_sekolah }}</h5>
                        
                        @if($sekolahSettings->npsn || $sekolahSettings->nsm)
                        <p class="mb-2">
                            @if($sekolahSettings->npsn)
                            <span class="badge bg-primary me-2">NPSN: {{ $sekolahSettings->npsn }}</span>
                            @endif
                            @if($sekolahSettings->nsm)
                            <span class="badge bg-secondary">NSM: {{ $sekolahSettings->nsm }}</span>
                            @endif
                        </p>
                        @endif
                        
                        <p class="mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            {{ $sekolahSettings->alamat_lengkap ?: ($siteSettings->alamat ?: 'Alamat belum diatur') }}
                        </p>
                        
                        @if($sekolahSettings->telepon || $siteSettings->telepon)
                        <p class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <a href="tel:{{ $sekolahSettings->telepon ?: $siteSettings->telepon }}">{{ $sekolahSettings->telepon ?: $siteSettings->telepon }}</a>
                        </p>
                        @endif
                        
                        @if($sekolahSettings->email || $siteSettings->email)
                        <p class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:{{ $sekolahSettings->email ?: $siteSettings->email }}">{{ $sekolahSettings->email ?: $siteSettings->email }}</a>
                        </p>
                        @endif
                        
                        @if($sekolahSettings->website || $siteSettings->website)
                        <p class="mb-4">
                            <i class="fas fa-globe text-primary me-2"></i>
                            <a href="{{ $sekolahSettings->website ?: $siteSettings->website }}" target="_blank">{{ $sekolahSettings->website ?: $siteSettings->website }}</a>
                        </p>
                        @endif
                        
                        @if($sekolahSettings->nama_kepala_sekolah)
                        <hr>
                        <p class="mb-1"><strong>Kepala Sekolah:</strong></p>
                        <p class="mb-0">{{ $sekolahSettings->nama_kepala_sekolah }}</p>
                        @if($sekolahSettings->nip_kepala_sekolah)
                        <small class="text-muted">NIP: {{ $sekolahSettings->nip_kepala_sekolah }}</small>
                        @endif
                        @endif
                        
                        {{-- Social Links --}}
                        <div class="social-links mt-4">
                            @if($siteSettings->facebook_url)
                            <a href="{{ $siteSettings->facebook_url }}" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif
                            @if($siteSettings->instagram_url)
                            <a href="{{ $siteSettings->instagram_url }}" target="_blank" title="Instagram" style="background: #E4405F;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif
                            @if($siteSettings->youtube_url)
                            <a href="{{ $siteSettings->youtube_url }}" target="_blank" title="YouTube" style="background: #FF0000;">
                                <i class="fab fa-youtube"></i>
                            </a>
                            @endif
                            @if($siteSettings->twitter_url)
                            <a href="{{ $siteSettings->twitter_url }}" target="_blank" title="Twitter" style="background: #1DA1F2;">
                                <i class="fab fa-twitter"></i>
                            </a>
                            @endif
                            @if($siteSettings->tiktok_url)
                            <a href="{{ $siteSettings->tiktok_url }}" target="_blank" title="TikTok" style="background: #000;">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @if($sekolahSettings->latitude && $sekolahSettings->longitude)
                    <div class="card h-100 p-0 overflow-hidden">
                        <div id="mapSekolah" style="height: 100%; min-height: 350px;"></div>
                    </div>
                    @elseif($siteSettings->google_maps_embed)
                    <div class="card h-100 p-0 overflow-hidden">
                        <div class="ratio ratio-4x3">
                            {!! $siteSettings->google_maps_embed !!}
                        </div>
                    </div>
                    @else
                    <div class="card h-100 d-flex align-items-center justify-content-center bg-light">
                        <div class="text-center text-muted">
                            <i class="fas fa-map-marked-alt fa-4x mb-3"></i>
                            <p>Peta belum diatur</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-3">
                        @if($sekolahSettings->logo)
                        <img src="{{ Storage::url($sekolahSettings->logo) }}" alt="{{ $sekolahSettings->nama_sekolah }}" style="height: 50px; filter: brightness(0) invert(1);" class="me-3">
                        @elseif($siteSettings->logo)
                        <img src="{{ $siteSettings->logo_url }}" alt="{{ $sekolahSettings->nama_sekolah }}" style="height: 50px; filter: brightness(0) invert(1);" class="me-3">
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $sekolahSettings->nama_sekolah ?: 'PPDB Online' }}</h5>
                            <small class="text-muted">{{ \App\Models\SekolahSettings::JENJANG_LIST[$sekolahSettings->jenjang] ?? $sekolahSettings->jenjang }}</small>
                        </div>
                    </div>
                    @if($siteSettings->footer_text)
                    <p class="text-muted small">{{ $siteSettings->footer_text }}</p>
                    @endif
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Link Cepat</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#beranda"><i class="fas fa-chevron-right me-2"></i>Beranda</a></li>
                        <li class="mb-2"><a href="#jadwal"><i class="fas fa-chevron-right me-2"></i>Jadwal</a></li>
                        <li class="mb-2"><a href="#berita"><i class="fas fa-chevron-right me-2"></i>Berita</a></li>
                        <li class="mb-2"><a href="{{ route('login') }}"><i class="fas fa-chevron-right me-2"></i>Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Kontak</h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        {{ $sekolahSettings->alamat_lengkap ?: ($siteSettings->alamat ?: '-') }}
                    </p>
                    @if($sekolahSettings->telepon || $siteSettings->telepon)
                    <p class="text-muted small mb-2"><i class="fas fa-phone me-2"></i>{{ $sekolahSettings->telepon ?: $siteSettings->telepon }}</p>
                    @endif
                    @if($sekolahSettings->email || $siteSettings->email)
                    <p class="text-muted small mb-2"><i class="fas fa-envelope me-2"></i>{{ $sekolahSettings->email ?: $siteSettings->email }}</p>
                    @endif
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="text-center">
                <small class="text-muted">
                    {{ $siteSettings->copyright_text ?: '© ' . date('Y') . ' ' . $sekolahSettings->nama_sekolah . '. All rights reserved.' }}
                </small>
            </div>
        </div>
    </footer>

    {{-- WhatsApp Float Button --}}
    @if($siteSettings->whatsapp_number)
    <div class="whatsapp-float">
        <a href="{{ $siteSettings->whatsapp_link }}" target="_blank" title="Chat via WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    @endif

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-sm');
            } else {
                navbar.classList.remove('shadow-sm');
            }
        });

        // Initialize Leaflet Map jika ada koordinat
        @if($sekolahSettings->latitude && $sekolahSettings->longitude)
        document.addEventListener('DOMContentLoaded', function() {
            var lat = {{ $sekolahSettings->latitude }};
            var lng = {{ $sekolahSettings->longitude }};
            
            var map = L.map('mapSekolah').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            var marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`
                <strong>{{ $sekolahSettings->nama_sekolah }}</strong><br>
                {{ $sekolahSettings->alamat_jalan }}<br>
                @if($sekolahSettings->telepon)
                <i class="fas fa-phone"></i> {{ $sekolahSettings->telepon }}
                @endif
            `).openPopup();
        });
        @endif
        
        // Countdown Timer
        @if($countdownTarget)
        function updateCountdown() {
            const target = new Date("{{ $countdownTarget->format('Y-m-d H:i:s') }}").getTime();
            const now = new Date().getTime();
            const distance = target - now;
            
            if (distance < 0) {
                // Countdown selesai, reload halaman
                location.reload();
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        // Update countdown setiap detik
        updateCountdown();
        setInterval(updateCountdown, 1000);
        @endif
    </script>
    
    {{-- GPS Permission Component --}}
    @include('components.gps-permission')
</body>
</html>
