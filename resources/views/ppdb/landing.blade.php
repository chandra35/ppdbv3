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

        body.countdown-lock-active {
            overflow: hidden;
        }

        .page-content {
            transition: filter 0.35s ease, transform 0.35s ease, opacity 0.35s ease;
        }

        body.countdown-lock-active .page-content {
            filter: blur(10px) brightness(0.48);
            transform: scale(1.01);
            pointer-events: none;
            user-select: none;
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
        
        /* Hero Slider */
        .hero-slider {
            position: relative;
            width: 100%;
            height: 60vh;
            min-height: 400px;
            max-height: 600px;
            overflow: hidden;
            margin-top: 70px;
            background: #000;
        }
        
        .hero-slider .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        
        .hero-slider .slide.active {
            opacity: 1;
        }
        
        .hero-slider .slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center center;
        }
        
        /* Background blur for portrait images */
        .hero-slider .slide-bg {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 120%;
            height: 120%;
            object-fit: cover;
            filter: blur(30px) brightness(0.4);
            z-index: 0;
        }
        
        .hero-slider .slide-img-container {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-slider .slide-img-container img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        
        .hero-slider .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.3) 100%);
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 80px;
            z-index: 2;
        }
        
        .hero-slider .slide-content {
            text-align: center;
            color: #fff;
            padding: 20px;
            max-width: 800px;
        }
        
        .hero-slider .slide-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        }
        
        .hero-slider .slide-content p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
        }
        
        .hero-slider .slide-content .btn {
            background: var(--primary-color);
            color: #fff;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
        }
        
        .hero-slider .slide-content .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .slider-controls {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .slider-dot.active {
            background: #fff;
            transform: scale(1.2);
        }
        
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .slider-btn:hover {
            background: rgba(255,255,255,0.4);
        }
        
        .slider-btn.prev { left: 20px; }
        .slider-btn.next { right: 20px; }
        
        @media (max-width: 768px) {
            .hero-slider {
                height: 50vh;
                min-height: 300px;
                max-height: 450px;
            }
            .hero-slider .slide-content h2 {
                font-size: 1.4rem;
            }
            .hero-slider .slide-content p {
                font-size: 0.95rem;
            }
            .slider-btn {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            .hero-slider .slide-overlay {
                padding-bottom: 70px;
            }
        }
        
        /* Countdown Styles */
        .countdown-wrapper {
            position: relative;
            max-width: 880px;
            margin: 0 auto;
            padding: 2rem 1.75rem;
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.18);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.40) 0%, rgba(49, 46, 129, 0.24) 100%);
            backdrop-filter: blur(18px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
            overflow: hidden;
        }

        .countdown-wrapper::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.16), transparent 38%),
                linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.02));
            pointer-events: none;
        }

        .countdown-wrapper > * {
            position: relative;
            z-index: 1;
        }

        .countdown-wrapper p {
            margin-bottom: 1rem;
            font-weight: 500;
            letter-spacing: 0.2px;
        }
        
        .countdown-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1rem;
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
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
            background: rgba(255,255,255,0.14);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 1rem 0.75rem;
            border: 1px solid rgba(255,255,255,0.18);
            transition: all 0.3s ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
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
            opacity: 0.55;
            align-self: center;
            animation: blink 1s infinite;
        }

        @media (max-width: 768px) {
            .countdown-wrapper {
                padding: 1.35rem 1rem;
                border-radius: 22px;
            }
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

        .countdown-lock-overlay {
            position: fixed;
            inset: 0;
            z-index: 5000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.12), transparent 32%),
                radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 24%),
                rgba(8, 15, 32, 0.18);
        }

        .countdown-lock-panel {
            width: min(820px, 100%);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 34px 90px rgba(0, 0, 0, 0.42);
            border: 1px solid rgba(255,255,255,0.12);
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.09), transparent 30%),
                linear-gradient(135deg, rgba(10, 30, 66, 0.96), rgba(23, 78, 166, 0.95));
        }

        .countdown-lock-top {
            color: #fff;
            padding: 1.6rem 1.6rem 1.15rem;
        }

        .countdown-lock-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            margin-bottom: 1rem;
        }

        .countdown-lock-title {
            font-size: clamp(1.55rem, 2.5vw, 2.3rem);
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 0.4rem;
        }

        .countdown-lock-copy {
            opacity: 0.82;
            font-size: 1rem;
            max-width: 620px;
            margin-bottom: 1.4rem;
        }

        .countdown-lock-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.85rem;
            text-align: center;
        }

        .countdown-lock-unit {
            padding: 1rem 0.85rem;
            border-radius: 22px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .countdown-lock-label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 1.6px;
            opacity: 0.82;
            margin-bottom: 0.65rem;
            text-transform: uppercase;
        }

        .countdown-lock-value {
            display: block;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: clamp(2.35rem, 5vw, 3.2rem);
            font-weight: 700;
            line-height: 1;
            text-shadow: 0 2px 12px rgba(0,0,0,0.16);
        }

        .countdown-lock-bottom {
            background: rgba(255,255,255,0.95);
            color: #1f2937;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1.05rem 1.35rem;
            letter-spacing: 0.4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .countdown-lock-bottom .accent {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .countdown-lock-top {
                padding: 1.2rem 1rem 1rem;
            }

            .countdown-lock-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .countdown-lock-value {
                font-size: 2.4rem;
            }

            .countdown-lock-bottom {
                padding: 0.9rem 1rem;
                font-size: 0.78rem;
                justify-content: center;
                text-align: center;
            }
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
        .berita-card {
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .berita-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .berita-card .card-img-wrapper {
            display: block;
            position: relative;
            width: 100%;
            padding-top: 60%; /* Aspect ratio 5:3 */
            overflow: hidden;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            cursor: pointer;
        }
        
        .berita-card .card-img-wrapper img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            transition: transform 0.4s ease;
        }
        
        .berita-card:hover .card-img-wrapper img {
            transform: scale(1.08);
        }
        
        .berita-card .card-img-wrapper .img-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, black) 100%);
        }
        
        .berita-card .card-img-top {
            height: 180px;
            object-fit: cover;
            object-position: center center;
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
<body class="{{ $statusPendaftaran === 'upcoming' ? 'countdown-lock-active' : '' }}">
    <div class="page-content">
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
                                        <li><a class="dropdown-item" href="{{ route('pendaftar.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Saya</a></li>
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

    {{-- Conditional: Slider atau Hero Section --}}
    @if($siteSettings->enable_slider && $sliders->count() > 0)
        {{-- Hero Slider --}}
        <div class="hero-slider">
            @foreach($sliders as $index => $slider)
                <div class="slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                    @if($slider->gambar && file_exists(public_path('storage/' . $slider->gambar)))
                        {{-- Background blur untuk portrait images --}}
                        <img src="{{ asset('storage/' . $slider->gambar) }}" alt="" class="slide-bg" aria-hidden="true">
                        {{-- Main image container --}}
                        <div class="slide-img-container">
                            <img src="{{ asset('storage/' . $slider->gambar) }}" alt="{{ $slider->judul }}">
                        </div>
                    @else
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-color), color-mix(in srgb, var(--primary-color) 60%, black));"></div>
                    @endif
                    <div class="slide-overlay">
                        <div class="slide-content">
                            <h2>{{ $slider->judul ?: $sekolahSettings->nama_sekolah }}</h2>
                            @if($slider->deskripsi)
                                <p>{{ $slider->deskripsi }}</p>
                            @else
                                <p>Pendaftaran Peserta Didik Baru (PPDB) Online</p>
                            @endif
                            @if($slider->link)
                                <a href="{{ $slider->link }}" class="btn">Selengkapnya</a>
                            @elseif(isset($statusPendaftaran) && $statusPendaftaran == 'open')
                                <a href="{{ route('pendaftar.landing') }}" class="btn">Daftar Sekarang</a>
                            @else
                                <a href="{{ route('login') }}" class="btn">Login</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($sliders->count() > 1)
                <button class="slider-btn prev" onclick="moveSlide(-1)">‹</button>
                <button class="slider-btn next" onclick="moveSlide(1)">›</button>
                
                <div class="slider-controls">
                    @foreach($sliders as $index => $slider)
                        <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})"></div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        {{-- Hero Section (tanpa slider) --}}
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
                        @if($statusPendaftaran != 'open')
                        <div class="countdown-wrapper text-white text-center mb-4">
                            <div class="countdown-status {{ $statusPendaftaran }}">
                                @if($statusPendaftaran == 'upcoming')
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>Pendaftaran Segera Dibuka</span>
                                @else
                                    <i class="fas fa-door-closed"></i>
                                    <span>Pendaftaran Telah Ditutup</span>
                                @endif
                            </div>
                            
                            @if($gelombangAktif && $countdownTarget && $statusPendaftaran == 'upcoming')
                            <p class="mb-3 opacity-90">
                                <i class="fas fa-clock me-1"></i> Pendaftaran akan dibuka dalam:
                            </p>
                            <div id="countdown" class="countdown-timer" data-target="{{ $countdownTarget->format('Y-m-d H:i:s') }}">
                                <div class="countdown-box">
                                    <div class="countdown-value" data-countdown-part="days">00</div>
                                    <div class="countdown-label">Hari</div>
                                </div>
                                <span class="countdown-separator d-none d-sm-block">:</span>
                                <div class="countdown-box">
                                    <div class="countdown-value" data-countdown-part="hours">00</div>
                                    <div class="countdown-label">Jam</div>
                                </div>
                                <span class="countdown-separator d-none d-sm-block">:</span>
                                <div class="countdown-box">
                                    <div class="countdown-value" data-countdown-part="minutes">00</div>
                                    <div class="countdown-label">Menit</div>
                                </div>
                                <span class="countdown-separator d-none d-sm-block">:</span>
                                <div class="countdown-box">
                                    <div class="countdown-value" data-countdown-part="seconds">00</div>
                                    <div class="countdown-label">Detik</div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        {{-- Main CTA Cards --}}
                        @if($statusPendaftaran == 'open')
                            @auth
                                <div class="row justify-content-center g-3 mb-4">
                                    <div class="col-sm-6 col-md-5 col-lg-4">
                                        <div class="card bg-white text-dark h-100 border-0 shadow">
                                            <div class="card-body p-4 text-center">
                                                <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                                                    <i class="fas fa-tachometer-alt fa-2x text-success"></i>
                                                </div>
                                                <h5 class="fw-bold mb-2">Halo, {{ Auth::user()->name }}!</h5>
                                                <p class="text-muted small mb-3">Klik tombol di bawah untuk masuk ke dashboard.</p>
                                                @if(Auth::user()->isAdmin())
                                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-success w-100">
                                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin
                                                    </a>
                                                @elseif(Auth::user()->hasAnyRole(['operator', 'verifikator']))
                                                    <a href="{{ route('operator.dashboard') }}" class="btn btn-success w-100">
                                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard Operator
                                                    </a>
                                                @else
                                                    <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-success w-100">
                                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard Saya
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row justify-content-center g-3 mb-4">
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
                            @endauth
                        @else
                            {{-- Pendaftaran belum/sudah tutup --}}
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                @auth
                                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pendaftar.dashboard') }}" class="btn btn-light btn-lg px-4">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login
                                    </a>
                                @endauth
                            </div>
                        @endif
                        
                        {{-- Info Badge --}}
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
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
                        </div>
                        
                        {{-- Info Jalur Aktif Badge --}}
                        @if($jalurAktif->count() > 0)
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                            @foreach($jalurAktif as $jalur)
                                @php
                                    $gelombangJalur = $jalur->gelombang->first();
                                    $isOpen = $gelombangJalur && $gelombangJalur->status == 'open';
                                    $isUpcoming = $gelombangJalur && $gelombangJalur->status == 'upcoming';
                                @endphp
                                <div class="badge px-3 py-2 d-flex align-items-center gap-2" 
                                     style="background: {{ $isOpen ? 'rgba(25, 135, 84, 0.9)' : ($isUpcoming ? 'rgba(255, 193, 7, 0.9)' : 'rgba(108, 117, 125, 0.7)') }}; color: {{ $isUpcoming ? '#000' : '#fff' }};">
                                    <i class="{{ $jalur->icon ?? 'fas fa-graduation-cap' }}"></i>
                                    <span class="fw-semibold">{{ $jalur->nama }}</span>
                                    @if($gelombangJalur)
                                        <span class="opacity-75">|</span>
                                        <small>
                                            @if($isOpen)
                                                {{ $gelombangJalur->tanggal_buka->format('d M') }} - {{ $gelombangJalur->tanggal_tutup->format('d M Y') }}
                                                <span class="badge bg-white text-success ms-1">{{ $gelombangJalur->sisa_hari }} hari lagi</span>
                                            @elseif($isUpcoming)
                                                Dibuka {{ $gelombangJalur->tanggal_buka->format('d M Y') }}
                                            @else
                                                Ditutup
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Status Pendaftaran Section (ditampilkan saat slider aktif) --}}
    @if($siteSettings->enable_slider && $sliders->count() > 0)
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-10 mx-auto text-center text-white">
                    @php
                        $jalurDenganGelombang = $jalurAktif->filter(fn($j) => $j->gelombang->isNotEmpty());
                    @endphp
                    
                    {{-- Status Pendaftaran dengan Countdown - Hidden saat open --}}
                    @if($statusPendaftaran != 'open')
                    <div class="countdown-wrapper text-white text-center mb-4">
                        <div class="countdown-status {{ $statusPendaftaran }}">
                            @if($statusPendaftaran == 'upcoming')
                                <i class="fas fa-hourglass-half"></i>
                                <span>Pendaftaran Segera Dibuka</span>
                            @else
                                <i class="fas fa-door-closed"></i>
                                <span>Pendaftaran Telah Ditutup</span>
                            @endif
                        </div>
                        
                        @if($gelombangAktif && $countdownTarget && $statusPendaftaran == 'upcoming')
                        <p class="mb-3 opacity-90">
                            <i class="fas fa-clock me-1"></i> Pendaftaran akan dibuka dalam:
                        </p>
                        <div id="countdown" class="countdown-timer" data-target="{{ $countdownTarget->format('Y-m-d H:i:s') }}">
                            <div class="countdown-box">
                                <div class="countdown-value" data-countdown-part="days">00</div>
                                <div class="countdown-label">Hari</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" data-countdown-part="hours">00</div>
                                <div class="countdown-label">Jam</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" data-countdown-part="minutes">00</div>
                                <div class="countdown-label">Menit</div>
                            </div>
                            <span class="countdown-separator d-none d-sm-block">:</span>
                            <div class="countdown-box">
                                <div class="countdown-value" data-countdown-part="seconds">00</div>
                                <div class="countdown-label">Detik</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    {{-- Main CTA Cards --}}
                    @if($statusPendaftaran == 'open')
                        @auth
                            <div class="row justify-content-center g-3">
                                <div class="col-sm-6 col-md-5 col-lg-4">
                                    <div class="card bg-white text-dark h-100 border-0 shadow">
                                        <div class="card-body p-4 text-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                                                <i class="fas fa-tachometer-alt fa-2x text-success"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Halo, {{ Auth::user()->name }}!</h5>
                                            <p class="text-muted small mb-3">Klik tombol di bawah untuk masuk ke dashboard.</p>
                                            @if(Auth::user()->isAdmin())
                                                <a href="{{ route('admin.dashboard') }}" class="btn btn-success w-100">
                                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin
                                                </a>
                                            @elseif(Auth::user()->hasAnyRole(['operator', 'verifikator']))
                                                <a href="{{ route('operator.dashboard') }}" class="btn btn-success w-100">
                                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard Operator
                                                </a>
                                            @else
                                                <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-success w-100">
                                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard Saya
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row justify-content-center g-3">
                                <div class="col-sm-6 col-md-5 col-lg-4">
                                    <div class="card bg-white text-dark h-100 border-0 shadow">
                                        <div class="card-body p-4 text-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                                                <i class="fas fa-user-plus fa-2x text-primary"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Pendaftaran Baru</h5>
                                            <p class="text-muted small mb-3">Daftar di sini untuk memulai pendaftaran PPDB</p>
                                            <a href="{{ route('pendaftar.landing') }}" class="btn btn-primary w-100">
                                                <i class="fas fa-arrow-right me-2"></i> Daftar Sekarang
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-5 col-lg-4">
                                    <div class="card bg-white bg-opacity-10 text-white h-100 border border-white border-opacity-25">
                                        <div class="card-body p-4 text-center">
                                            <div class="rounded-circle bg-white bg-opacity-25 p-3 d-inline-flex mb-3">
                                                <i class="fas fa-sign-in-alt fa-2x"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Sudah Terdaftar?</h5>
                                            <p class="opacity-75 small mb-3">Login untuk melanjutkan pendaftaran</p>
                                            <a href="{{ route('login') }}" class="btn btn-outline-light w-100">
                                                <i class="fas fa-sign-in-alt me-2"></i> Login
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    @else
                        {{-- Pendaftaran Ditutup --}}
                        <div class="d-flex justify-content-center gap-3 flex-wrap mt-3">
                            @auth
                                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pendaftar.dashboard') }}" class="btn btn-light btn-lg px-4">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>
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
                    <div class="card h-100 berita-card border-0 shadow-sm">
                        <a href="{{ route('ppdb.berita.show', $berita->slug) }}" class="card-img-wrapper">
                            @if($berita->gambar)
                            <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}">
                            @else
                            <div class="img-placeholder">
                                <i class="fas fa-newspaper fa-3x text-white opacity-50"></i>
                            </div>
                            @endif
                        </a>
                        <div class="card-body">
                            @if($berita->kategori)
                            <span class="badge bg-primary mb-2">{{ ucfirst($berita->kategori) }}</span>
                            @endif
                            @if($berita->is_featured)
                            <span class="badge bg-warning mb-2"><i class="fas fa-star"></i> Featured</span>
                            @endif
                            <a href="{{ route('ppdb.berita.show', $berita->slug) }}" class="text-decoration-none">
                                <h5 class="card-title text-dark">{{ Str::limit($berita->judul, 50) }}</h5>
                            </a>
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
    </div>

    @if($statusPendaftaran === 'upcoming' && $countdownTarget)
    <div class="countdown-lock-overlay" id="countdownLockOverlay" aria-live="polite">
        <div class="countdown-lock-panel">
            <div class="countdown-lock-top">
                <div class="countdown-lock-kicker">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Pembukaan Pendaftaran</span>
                </div>
                <div class="countdown-lock-title">PPDB Belum Dibuka</div>
                <div class="countdown-lock-copy">
                    Portal akan aktif otomatis saat jadwal pembukaan dimulai. Silakan tunggu hitung mundurnya selesai.
                </div>
                <div class="countdown-lock-grid">
                    <div class="countdown-lock-unit">
                        <span class="countdown-lock-label">Hari</span>
                        <span class="countdown-lock-value" data-countdown-part="days">00</span>
                    </div>
                    <div class="countdown-lock-unit">
                        <span class="countdown-lock-label">Jam</span>
                        <span class="countdown-lock-value" data-countdown-part="hours">00</span>
                    </div>
                    <div class="countdown-lock-unit">
                        <span class="countdown-lock-label">Menit</span>
                        <span class="countdown-lock-value" data-countdown-part="minutes">00</span>
                    </div>
                    <div class="countdown-lock-unit">
                        <span class="countdown-lock-label">Detik</span>
                        <span class="countdown-lock-value" data-countdown-part="seconds">00</span>
                    </div>
                </div>
            </div>
            <div class="countdown-lock-bottom">
                <span>{{ $gelombangAktif?->nama ?: 'PPDB MAN 1 Metro' }}</span>
                <span class="accent">Dibuka {{ $countdownTarget->format('d M Y H:i') }} WIB</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ====== SLIDER FUNCTIONS ======
        @if($siteSettings->enable_slider && $sliders->count() > 1)
        let currentIndex = 0;
        const slides = document.querySelectorAll('.hero-slider .slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            if (index >= totalSlides) currentIndex = 0;
            if (index < 0) currentIndex = totalSlides - 1;
            
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            if (slides[currentIndex]) slides[currentIndex].classList.add('active');
            if (dots[currentIndex]) dots[currentIndex].classList.add('active');
        }

        function moveSlide(direction) {
            currentIndex += direction;
            showSlide(currentIndex);
        }

        function goToSlide(index) {
            currentIndex = index;
            showSlide(currentIndex);
        }

        // Auto slide every 5 seconds
        setInterval(() => {
            currentIndex++;
            showSlide(currentIndex);
        }, 5000);
        @endif

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
            
            document.querySelectorAll('[data-countdown-part=\"days\"]').forEach(el => el.textContent = String(days).padStart(2, '0'));
            document.querySelectorAll('[data-countdown-part=\"hours\"]').forEach(el => el.textContent = String(hours).padStart(2, '0'));
            document.querySelectorAll('[data-countdown-part=\"minutes\"]').forEach(el => el.textContent = String(minutes).padStart(2, '0'));
            document.querySelectorAll('[data-countdown-part=\"seconds\"]').forEach(el => el.textContent = String(seconds).padStart(2, '0'));
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
