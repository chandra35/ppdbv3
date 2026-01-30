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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $siteSettings->primary_color ?: '#007bff' }};
            --text-primary: #1a1a2e;
            --text-secondary: #6c757d;
            --bg-light: #fafbfc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.7;
        }
        
        /* Navbar - Instagram Style */
        .navbar-minimal {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 12px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar-minimal.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .navbar-minimal .brand-text {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .navbar-minimal .nav-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            font-size: 1.1rem;
        }
        
        .navbar-minimal .nav-icon:hover {
            background: rgba(0,0,0,0.05);
        }
        
        /* Hero Image - Instagram Style */
        .hero-image-wrapper {
            margin-top: 60px;
            background: #000;
            position: relative;
            overflow: hidden;
        }
        
        .hero-image-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }
        
        .hero-image {
            width: 100%;
            max-height: 70vh;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        
        .hero-image-placeholder {
            width: 100%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);
        }
        
        .hero-image-placeholder i {
            font-size: 5rem;
            color: rgba(255,255,255,0.3);
        }
        
        /* Image Actions Bar */
        .image-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 60px 20px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .action-btn {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.1);
        }
        
        .action-btn.liked {
            color: #ed4956;
        }
        
        /* Content Area */
        .content-wrapper {
            max-width: 680px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Article Header */
        .article-header {
            padding: 40px 0 30px;
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .author-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .author-info {
            flex: 1;
        }
        
        .author-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
            margin: 0;
        }
        
        .post-date {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin: 0;
        }
        
        .category-badge {
            background: var(--primary-color);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .featured-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Title */
        .article-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(1.75rem, 5vw, 2.5rem);
            font-weight: 700;
            line-height: 1.25;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        /* Stats Row */
        .stats-row {
            display: flex;
            gap: 20px;
            padding: 16px 0;
            border-top: 1px solid rgba(0,0,0,0.08);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .stat-item i {
            font-size: 1rem;
        }
        
        /* Article Body */
        .article-body {
            padding-bottom: 40px;
        }
        
        .lead-text {
            font-size: 1.2rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 30px;
            padding-left: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .article-content {
            font-size: 1.05rem;
            line-height: 1.9;
            color: var(--text-primary);
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
        }
        
        /* Share Section - Instagram Style */
        .share-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin: 30px 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .share-section h6 {
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .share-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .share-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
        }
        
        .share-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: white;
        }
        
        .share-btn.facebook { background: linear-gradient(135deg, #1877F2 0%, #0d5ecf 100%); }
        .share-btn.twitter { background: linear-gradient(135deg, #1DA1F2 0%, #0c85d0 100%); }
        .share-btn.whatsapp { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
        .share-btn.telegram { background: linear-gradient(135deg, #0088cc 0%, #0066aa 100%); }
        .share-btn.copy { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
        
        /* Related Section */
        .related-section {
            padding: 50px 0;
            background: white;
            border-top: 1px solid rgba(0,0,0,0.06);
        }
        
        .related-section .section-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 30px;
            color: var(--text-primary);
        }
        
        /* Related Cards - Instagram Grid Style */
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .related-card {
            background: var(--bg-light);
            border-radius: 16px;
            overflow: hidden;
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
        }
        
        .related-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }
        
        .related-card-image {
            position: relative;
            padding-top: 66.67%; /* 3:2 aspect ratio */
            background: linear-gradient(135deg, #e0e0e0 0%, #f0f0f0 100%);
            overflow: hidden;
        }
        
        .related-card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        
        .related-card:hover .related-card-image img {
            transform: scale(1.08);
        }
        
        .related-card-image .placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);
        }
        
        .related-card-image .placeholder i {
            font-size: 2.5rem;
            color: rgba(255,255,255,0.4);
        }
        
        .related-card-body {
            padding: 20px;
        }
        
        .related-card-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .related-card-date {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        /* CTA Box */
        .cta-box {
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 60%, black) 100%);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: white;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .cta-box h5 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 10px;
            position: relative;
        }
        
        .cta-box p {
            opacity: 0.9;
            margin-bottom: 20px;
            position: relative;
        }
        
        .cta-box .btn {
            background: white;
            color: var(--primary-color);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .cta-box .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        /* Footer */
        .footer-minimal {
            background: var(--text-primary);
            color: rgba(255,255,255,0.7);
            padding: 30px 0;
            text-align: center;
        }
        
        /* Toast Notification */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--text-primary);
            color: white;
            padding: 14px 28px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.9rem;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 9999;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .toast-notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-image {
                max-height: 50vh;
            }
            
            .content-wrapper {
                padding: 0 16px;
            }
            
            .article-header {
                padding: 30px 0 20px;
            }
            
            .article-title {
                font-size: 1.5rem;
            }
            
            .lead-text {
                font-size: 1.05rem;
            }
            
            .share-btn {
                width: 44px;
                height: 44px;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-box {
                padding: 30px 20px;
            }
            
            .stats-row {
                flex-wrap: wrap;
                gap: 12px;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    {{-- Minimal Navbar --}}
    <nav class="navbar-minimal">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('ppdb.landing') }}" class="nav-icon" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
                
                <a href="{{ route('ppdb.landing') }}" class="text-decoration-none">
                    <span class="brand-text">{{ $siteSettings->nama_sekolah ?: 'PPDB Online' }}</span>
                </a>
                
                <button class="nav-icon" onclick="shareNative()" title="Share">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>
        </div>
    </nav>

    {{-- Hero Image --}}
    <div class="hero-image-wrapper">
        <div class="hero-image-container">
            @if($berita->gambar)
            <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}" class="hero-image">
            <div class="image-actions">
                <div class="d-flex gap-2">
                    <button class="action-btn" onclick="toggleLike(this)" title="Suka">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="action-btn" onclick="shareNative()" title="Share">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <button class="action-btn" onclick="toggleBookmark(this)" title="Simpan">
                    <i class="far fa-bookmark"></i>
                </button>
            </div>
            @else
            <div class="hero-image-placeholder">
                <i class="fas fa-newspaper"></i>
            </div>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <main>
        <div class="content-wrapper">
            {{-- Article Header --}}
            <header class="article-header">
                <div class="article-meta animate-in">
                    <div class="author-avatar">
                        {{ substr($berita->penulis ?: 'Admin', 0, 1) }}
                    </div>
                    <div class="author-info">
                        <p class="author-name">{{ $berita->penulis ?: 'Admin PPDB' }}</p>
                        <p class="post-date">{{ $berita->tanggal_publikasi->diffForHumans() }}</p>
                    </div>
                    @if($berita->kategori)
                    <span class="category-badge">{{ $berita->kategori }}</span>
                    @endif
                    @if($berita->is_featured)
                    <span class="featured-badge"><i class="fas fa-star me-1"></i> Featured</span>
                    @endif
                </div>
                
                <h1 class="article-title animate-in delay-1">{{ $berita->judul }}</h1>
                
                <div class="stats-row animate-in delay-2">
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span>{{ number_format($berita->views) }} views</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span>{{ $berita->reading_time }} min read</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $berita->tanggal_publikasi->format('d M Y') }}</span>
                    </div>
                </div>
            </header>
            
            {{-- Article Body --}}
            <article class="article-body animate-in delay-3">
                @if($berita->deskripsi)
                <p class="lead-text">{{ $berita->deskripsi }}</p>
                @endif
                
                @if($berita->konten)
                <div class="article-content">
                    {!! nl2br(e($berita->konten)) !!}
                </div>
                @endif
            </article>
            
            {{-- Share Section --}}
            <div class="share-section">
                <h6><i class="fas fa-share-alt me-2"></i>Bagikan Artikel Ini</h6>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                       target="_blank" class="share-btn facebook" title="Share ke Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($berita->judul) }}&url={{ urlencode(url()->current()) }}" 
                       target="_blank" class="share-btn twitter" title="Share ke Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($berita->judul . ' - ' . url()->current()) }}" 
                       target="_blank" class="share-btn whatsapp" title="Share via WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($berita->judul) }}" 
                       target="_blank" class="share-btn telegram" title="Share via Telegram">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                    <button onclick="copyLink()" class="share-btn copy" title="Copy Link">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
            
            {{-- CTA Box --}}
            <div class="cta-box">
                <h5><i class="fas fa-graduation-cap me-2"></i>Tertarik Bergabung?</h5>
                <p>Daftarkan diri Anda sekarang melalui sistem PPDB Online</p>
                <a href="{{ route('pendaftar.landing') }}" class="btn">
                    <i class="fas fa-arrow-right me-2"></i>Daftar Sekarang
                </a>
            </div>
        </div>
        
        {{-- Related Articles --}}
        @if($relatedBeritas->count() > 0)
        <section class="related-section">
            <div class="container">
                <h4 class="section-title">
                    <i class="fas fa-newspaper text-primary me-2"></i>Artikel Terkait
                </h4>
                <div class="related-grid">
                    @foreach($relatedBeritas as $related)
                    <a href="{{ route('ppdb.berita.show', $related->slug) }}" class="related-card">
                        <div class="related-card-image">
                            @if($related->gambar)
                            <img src="{{ asset('storage/' . $related->gambar) }}" alt="{{ $related->judul }}">
                            @else
                            <div class="placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            @endif
                        </div>
                        <div class="related-card-body">
                            <h5 class="related-card-title">{{ $related->judul }}</h5>
                            <p class="related-card-date">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $related->tanggal_publikasi->format('d M Y') }}
                            </p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="footer-minimal">
        <div class="container">
            <small>
                {{ $siteSettings->copyright_text ?: '© ' . date('Y') . ' ' . ($siteSettings->nama_sekolah ?: 'PPDB Online') . '. All rights reserved.' }}
            </small>
        </div>
    </footer>
    
    {{-- Toast Notification --}}
    <div class="toast-notification" id="toast">Link berhasil disalin!</div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-minimal');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Copy link
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                showToast('Link berhasil disalin!');
            }).catch(function(err) {
                showToast('Gagal menyalin link');
            });
        }
        
        // Native share
        function shareNative() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $berita->judul }}',
                    text: '{{ $berita->excerpt }}',
                    url: window.location.href
                }).catch(console.error);
            } else {
                copyLink();
            }
        }
        
        // Toggle like (visual only)
        function toggleLike(btn) {
            const icon = btn.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('liked');
                showToast('Disukai!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('liked');
            }
        }
        
        // Toggle bookmark (visual only)
        function toggleBookmark(btn) {
            const icon = btn.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showToast('Disimpan ke bookmark');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showToast('Dihapus dari bookmark');
            }
        }
        
        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
