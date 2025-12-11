<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta --}}
    <title>{{ $berita->judul }} - {{ $siteSettings->nama_sekolah }}</title>
    <meta name="description" content="{{ $berita->excerpt }}">
    
    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $berita->judul }}">
    <meta property="og:description" content="{{ $berita->excerpt }}">
    <meta property="og:image" content="{{ $berita->gambar_url ?: asset('images/default-og.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
    
    @if($siteSettings->favicon)
    <link rel="icon" href="{{ $siteSettings->favicon_url }}" type="image/x-icon">
    @endif
    
    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $siteSettings->primary_color ?: '#007bff' }};
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .article-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, black) 100%);
            color: white;
            padding: 100px 0 60px;
        }
        
        .article-content {
            margin-top: -40px;
        }
        
        .article-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .article-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
        }
        
        .article-body {
            padding: 40px;
        }
        
        .article-body p {
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .share-buttons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }
        
        .share-buttons a:hover {
            transform: scale(1.1);
        }
        
        .related-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
        }
        
        .related-card img {
            height: 120px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('ppdb.landing') }}">
                @if($siteSettings->logo)
                    <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->nama_sekolah }}" class="me-2">
                @else
                    <i class="fas fa-graduation-cap fa-2x text-primary me-2"></i>
                @endif
                <strong>{{ $siteSettings->nama_sekolah ?: 'PPDB Online' }}</strong>
            </a>
            <div class="ms-auto">
                <a href="{{ route('ppdb.landing') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    {{-- Article Header --}}
    <header class="article-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('ppdb.landing') }}" class="text-white-50">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ppdb.landing') }}#berita" class="text-white-50">Berita</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Detail</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold mb-3">{{ $berita->judul }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-3">
                @if($berita->kategori)
                <span class="badge bg-light text-dark">
                    <i class="fas fa-tag me-1"></i> {{ ucfirst($berita->kategori) }}
                </span>
                @endif
                <span class="text-white-50">
                    <i class="fas fa-calendar me-1"></i> {{ $berita->tanggal_publikasi->format('d F Y') }}
                </span>
                @if($berita->penulis)
                <span class="text-white-50">
                    <i class="fas fa-user me-1"></i> {{ $berita->penulis }}
                </span>
                @endif
                <span class="text-white-50">
                    <i class="fas fa-eye me-1"></i> {{ number_format($berita->views) }} views
                </span>
                <span class="text-white-50">
                    <i class="fas fa-clock me-1"></i> {{ $berita->reading_time }} menit baca
                </span>
            </div>
        </div>
    </header>

    {{-- Article Content --}}
    <main class="article-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article class="article-card mb-4">
                        @if($berita->gambar)
                        <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}" class="article-image">
                        @endif
                        
                        <div class="article-body">
                            {{-- Lead/Deskripsi --}}
                            <p class="lead text-muted border-start border-4 border-primary ps-3">
                                {{ $berita->deskripsi }}
                            </p>
                            
                            {{-- Konten --}}
                            @if($berita->konten)
                            <div class="article-text">
                                {!! nl2br(e($berita->konten)) !!}
                            </div>
                            @endif
                            
                            <hr class="my-4">
                            
                            {{-- Share Buttons --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div>
                                    <span class="text-muted me-3">Bagikan:</span>
                                    <div class="share-buttons d-inline-flex">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                                           target="_blank" style="background: #1877F2;" title="Share ke Facebook">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($berita->judul) }}&url={{ urlencode(url()->current()) }}" 
                                           target="_blank" style="background: #1DA1F2;" title="Share ke Twitter">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                        <a href="https://wa.me/?text={{ urlencode($berita->judul . ' - ' . url()->current()) }}" 
                                           target="_blank" style="background: #25D366;" title="Share via WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($berita->judul) }}" 
                                           target="_blank" style="background: #0088cc;" title="Share via Telegram">
                                            <i class="fab fa-telegram-plane"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="copyToClipboard()" style="background: #6c757d;" title="Copy Link">
                                            <i class="fas fa-link"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-3 mt-md-0">
                                    <a href="{{ route('ppdb.landing') }}#berita" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Berita
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
                
                <div class="col-lg-4">
                    {{-- Related Articles --}}
                    @if($relatedBeritas->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0"><i class="fas fa-newspaper text-primary me-2"></i> Berita Terkait</h5>
                        </div>
                        <div class="card-body">
                            @foreach($relatedBeritas as $related)
                            <a href="{{ route('ppdb.berita.show', $related->slug) }}" class="text-decoration-none">
                                <div class="d-flex mb-3 {{ !$loop->last ? 'pb-3 border-bottom' : '' }}">
                                    @if($related->gambar)
                                    <img src="{{ asset('storage/' . $related->gambar) }}" 
                                         alt="{{ $related->judul }}" 
                                         class="rounded me-3" 
                                         style="width: 80px; height: 60px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <h6 class="mb-1 text-dark">{{ Str::limit($related->judul, 50) }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $related->tanggal_publikasi->format('d M Y') }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Info Box --}}
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-user-plus fa-3x mb-3"></i>
                            <h5>Tertarik Mendaftar?</h5>
                            <p class="small mb-3">Daftarkan diri Anda sekarang melalui sistem PPDB Online kami</p>
                            <a href="{{ route('ppdb.register.step1') }}" class="btn btn-light">
                                <i class="fas fa-arrow-right me-1"></i> Daftar Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <small>
                {{ $siteSettings->copyright_text ?: '© ' . date('Y') . ' ' . ($siteSettings->nama_sekolah ?: 'PPDB Online') . '. All rights reserved.' }}
            </small>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('Link berhasil disalin!');
            }, function(err) {
                console.error('Gagal menyalin: ', err);
            });
        }
    </script>
</body>
</html>
