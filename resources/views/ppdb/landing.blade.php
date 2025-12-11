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
            min-height: 70vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, black) 100%);
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
            opacity: 0.2;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        /* Slider */
        .carousel-item img {
            height: 500px;
            object-fit: cover;
        }
        
        .carousel-caption {
            background: rgba(0,0,0,0.6);
            border-radius: 10px;
            padding: 20px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
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
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
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
        }
        
        .timeline-item.active::before {
            background: var(--primary-color);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
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
            padding: 80px 0;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 50px;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
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
                        <a class="nav-link" href="#jadwal">Jadwal</a>
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
    <section id="beranda" class="hero-section d-flex align-items-center text-white" style="padding-top: 100px;">
        <div class="container hero-content text-center">
            <h1 class="display-4 fw-bold mb-3">{{ $siteSettings->hero_title ?: 'PPDB Online ' . $sekolahSettings->nama_sekolah }}</h1>
            <p class="lead mb-2">{{ $siteSettings->hero_subtitle ?: 'Pendaftaran Peserta Didik Baru secara online, cepat dan mudah' }}</p>
            <p class="mb-4">
                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                    <i class="fas fa-graduation-cap me-1"></i>
                    {{ \App\Models\SekolahSettings::JENJANG_LIST[$sekolahSettings->jenjang] ?? $sekolahSettings->jenjang }}
                </span>
                @if($sekolahSettings->npsn)
                <span class="badge bg-light text-dark fs-6 px-3 py-2 ms-2">NPSN: {{ $sekolahSettings->npsn }}</span>
                @endif
            </p>
            
            @php
                $jalurDenganGelombang = $jalurAktif->filter(fn($j) => $j->gelombangs->isNotEmpty());
            @endphp
            
            @if($jalurDenganGelombang->isNotEmpty())
                <div class="alert alert-success d-inline-block mb-3">
                    <i class="fas fa-bullhorn me-2"></i>
                    <strong>Pendaftaran Dibuka!</strong> - 
                    {{ $jalurDenganGelombang->count() }} jalur pendaftaran tersedia
                </div>
                <br>
                <a href="{{ route('ppdb.register.step1') }}" class="btn btn-light btn-lg px-5 me-3">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5">
                    <i class="fas fa-sign-in-alt me-2"></i> Cek Status
                </a>
            @elseif($ppdbSettings && $ppdbSettings->status_pendaftaran)
                <a href="{{ route('ppdb.register.step1') }}" class="btn btn-light btn-lg px-5 me-3">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5">
                    <i class="fas fa-sign-in-alt me-2"></i> Cek Status
                </a>
            @else
                <div class="alert alert-warning d-inline-block">
                    <i class="fas fa-info-circle me-2"></i>
                    Pendaftaran saat ini belum dibuka
                </div>
            @endif
            
            @if($jalurDenganGelombang->isNotEmpty())
            <div class="mt-5">
                <div class="row justify-content-center g-3">
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">{{ $jalurDenganGelombang->count() }}</h3>
                            <small>Jalur Dibuka</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">{{ $jalurAktif->sum('kuota') }}</h3>
                            <small>Total Kuota</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">{{ $jalurAktif->sum(fn($j) => $j->pendaftars_count ?? 0) }}</h3>
                            <small>Sudah Daftar</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">{{ $jalurAktif->sum('kuota_tersisa') }}</h3>
                            <small>Sisa Kuota</small>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($ppdbSettings)
            <div class="mt-5">
                <div class="row justify-content-center g-3">
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">{{ $ppdbSettings->kuota_penerimaan }}</h3>
                            <small>Kuota Siswa</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h3 class="fw-bold mb-0">
                                @if($ppdbSettings->status_pendaftaran)
                                    <span class="text-success"><i class="fas fa-check-circle"></i></span>
                                @else
                                    <span class="text-danger"><i class="fas fa-times-circle"></i></span>
                                @endif
                            </h3>
                            <small>{{ $ppdbSettings->status_pendaftaran ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h5 class="fw-bold mb-0">{{ $ppdbSettings->tanggal_dibuka ? $ppdbSettings->tanggal_dibuka->format('d M Y') : '-' }}</h5>
                            <small>Tanggal Dibuka</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <h5 class="fw-bold mb-0">{{ $ppdbSettings->tanggal_ditutup ? $ppdbSettings->tanggal_ditutup->format('d M Y') : '-' }}</h5>
                            <small>Tanggal Ditutup</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Slider Section --}}
    @if($sliders->count() > 0)
    <section class="py-0">
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($sliders as $index => $slider)
                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($sliders as $index => $slider)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    @if($slider->link)
                    <a href="{{ $slider->link }}" target="_blank">
                    @endif
                        <img src="{{ asset('storage/' . $slider->gambar) }}" class="d-block w-100" alt="{{ $slider->judul }}">
                        @if($slider->judul || $slider->deskripsi)
                        <div class="carousel-caption d-none d-md-block">
                            @if($slider->judul)<h5>{{ $slider->judul }}</h5>@endif
                            @if($slider->deskripsi)<p>{{ $slider->deskripsi }}</p>@endif
                        </div>
                        @endif
                    @if($slider->link)
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>
    @endif

    {{-- Jalur Pendaftaran Section --}}
    @if($jalurAktif->count() > 0)
    <section id="jalur-pendaftaran" class="py-5">
        <div class="container">
            <h2 class="section-title text-center fw-bold mb-2">Jalur Pendaftaran</h2>
            <p class="text-center text-muted mb-5">Pilih jalur pendaftaran yang sesuai dengan kriteria Anda</p>
            
            <div class="row g-4">
                @foreach($jalurAktif as $jalur)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid {{ $jalur->warna ?? '#007bff' }} !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle p-3 me-3" style="background: {{ $jalur->warna ?? '#007bff' }}15;">
                                    <i class="{{ $jalur->icon ?? 'fas fa-graduation-cap' }} fa-lg" style="color: {{ $jalur->warna ?? '#007bff' }};"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">{{ $jalur->nama }}</h5>
                                    @if($jalur->tahunPelajaran)
                                    <small class="text-muted">TA {{ $jalur->tahunPelajaran->nama }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            @if($jalur->deskripsi)
                            <p class="text-muted small mb-3">{{ Str::limit($jalur->deskripsi, 100) }}</p>
                            @endif
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Kuota Tersedia</span>
                                    <span class="small fw-bold">{{ $jalur->kuota_tersisa }} / {{ $jalur->kuota }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php
                                        $persentase = $jalur->kuota > 0 ? (($jalur->kuota - $jalur->kuota_tersisa) / $jalur->kuota) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $persentase }}%; background-color: {{ $jalur->warna ?? '#007bff' }};"
                                         aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            {{-- Gelombang yang dibuka --}}
                            @if($jalur->gelombangs->isNotEmpty())
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-door-open me-1"></i> Periode Dibuka:
                                </small>
                                @foreach($jalur->gelombangs as $gelombang)
                                <div class="bg-success bg-opacity-10 rounded p-2 mb-1 small">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-success">
                                            @if($gelombang->tampilkan_nama)
                                                {{ $gelombang->nama }}
                                            @else
                                                Pendaftaran Dibuka
                                            @endif
                                        </span>
                                        <span class="badge bg-success">{{ $gelombang->sisa_hari }} hari</span>
                                    </div>
                                    <small class="text-muted">
                                        s/d {{ $gelombang->tanggal_ditutup->format('d M Y') }} | Kuota: {{ $gelombang->kuota_tersisa }}
                                    </small>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="mb-3">
                                <span class="badge bg-secondary">Belum ada periode dibuka</span>
                            </div>
                            @endif
                            
                            @if($jalur->persyaratan)
                            <div class="small text-muted mb-3">
                                <strong class="d-block mb-1"><i class="fas fa-list-check me-1"></i> Persyaratan:</strong>
                                {!! nl2br(e(Str::limit($jalur->persyaratan, 150))) !!}
                            </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-transparent border-0 pt-0">
                            @if($jalur->gelombangs->isNotEmpty())
                            <a href="{{ route('ppdb.register.step1') }}" class="btn btn-sm w-100" style="background: {{ $jalur->warna ?? '#007bff' }}; color: white;">
                                <i class="fas fa-arrow-right me-1"></i> Daftar Jalur Ini
                            </a>
                            @else
                            <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                                <i class="fas fa-clock me-1"></i> Belum Dibuka
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

    {{-- Jadwal Section --}}
    @if($jadwals->count() > 0)
    <section id="jadwal" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Jadwal PPDB</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card p-4">
                        <div class="timeline">
                            @foreach($jadwals as $jadwal)
                            <div class="timeline-item {{ $jadwal->is_ongoing ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1" style="color: {{ $jadwal->warna ?: 'var(--primary-color)' }}">
                                            {{ $jadwal->nama_kegiatan }}
                                        </h5>
                                        @if($jadwal->keterangan)
                                        <p class="text-muted mb-1 small">{{ $jadwal->keterangan }}</p>
                                        @endif
                                    </div>
                                    <span class="badge {{ $jadwal->is_ongoing ? 'bg-success' : ($jadwal->is_upcoming ? 'bg-info' : 'bg-secondary') }}">
                                        {{ $jadwal->is_ongoing ? 'Berlangsung' : ($jadwal->is_upcoming ? 'Akan Datang' : 'Selesai') }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i> {{ $jadwal->tanggal_range }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Tentang Section --}}
    @if($siteSettings->about_content)
    <section id="tentang">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Tentang Kami</h2>
            <div class="row align-items-center">
                @if($siteSettings->about_image)
                <div class="col-md-5 mb-4 mb-md-0">
                    <img src="{{ $siteSettings->about_image_url }}" class="img-fluid rounded-3 shadow" alt="Tentang Kami">
                </div>
                <div class="col-md-7">
                @else
                <div class="col-md-10 mx-auto">
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
    <section id="berita" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Berita & Pengumuman</h2>
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
    <section id="kontak">
        <div class="container">
            <h2 class="section-title text-center fw-bold">Hubungi Kami</h2>
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
    </script>
</body>
</html>
