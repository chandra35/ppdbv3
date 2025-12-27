<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Berhasil - PPDB Online {{ config('app.name') }}</title>
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
        }
        
        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }
        
        .navbar-brand i {
            margin-right: 0.5rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        /* Success Icon */
        .success-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #48bb78;
            margin-bottom: 1rem;
        }
        
        .success-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .success-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Credentials Box */
        .credentials-box {
            background: #f7fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .credentials-box h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .credential-value {
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .copy-btn {
            background: #edf2f7;
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: #e2e8f0;
        }
        
        .copy-btn.copied {
            background: #48bb78;
            color: white;
        }
        
        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }
        
        .alert-warning {
            background: #fffaf0;
            border: 1px solid #fbd38d;
            color: #7c2d12;
        }
        
        .alert-info {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            color: #2c5282;
        }
        
        .alert h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }
        
        .alert ul li {
            margin-bottom: 0.25rem;
        }
        
        /* Button */
        .btn {
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mb-0 { margin-bottom: 0; }
        
        /* Responsive */
        @media (max-width: 600px) {
            .navbar {
                padding: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .credential-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
        
        @media print {
            .navbar, .btn, .alert {
                display: none !important;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ route('pendaftar.landing') }}" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i>
            PPDB {{ config('app.name') }}
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="card">
                <div class="success-header">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2>Pendaftaran Berhasil!</h2>
                    <p>Akun Anda telah dibuat. Silakan catat kredensial login Anda.</p>
                </div>

                <!-- Credentials -->
                <div class="credentials-box">
                    <h3>
                        <i class="fas fa-key" style="color: #667eea;"></i>
                        Kredensial Login Anda
                    </h3>
                    
                    <div class="credential-item">
                        <span class="credential-label">Nomor Registrasi</span>
                        <span class="credential-value">
                            <span class="badge">{{ $credentials['nomor_registrasi'] }}</span>
                        </span>
                    </div>
                    
                    <div class="credential-item">
                        <span class="credential-label">Username</span>
                        <span class="credential-value">{{ $credentials['username'] }}</span>
                    </div>
                    
                    <div class="credential-item">
                        <span class="credential-label">Password</span>
                        <span class="credential-value">
                            <span id="passwordText">{{ $credentials['password'] }}</span>
                            <button type="button" class="copy-btn" id="copyPassword" title="Salin Password">
                                <i class="fas fa-copy"></i>
                            </button>
                        </span>
                    </div>
                    
                    @if(!empty($credentials['email']) && !str_contains($credentials['email'], '@ppdb.temp'))
                    <div class="credential-item">
                        <span class="credential-label">Email</span>
                        <span class="credential-value">{{ $credentials['email'] }}</span>
                    </div>
                    @endif
                </div>

                <!-- WhatsApp Status -->
                @if($waSent)
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> WhatsApp Terkirim</h5>
                        <p class="mb-0">
                            Kredensial login Anda telah dikirim ke nomor WhatsApp yang Anda daftarkan. 
                            Silakan cek pesan WhatsApp Anda.
                        </p>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> WhatsApp Tidak Terkirim</h5>
                        <p class="mb-0">
                            Kredensial login tidak dapat dikirim via WhatsApp. 
                            <strong>Harap catat kredensial di atas dengan baik!</strong><br>
                            <small>Anda dapat screenshot halaman ini atau tulis di tempat yang aman.</small>
                        </p>
                    </div>
                @endif

                <!-- Important Notes -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Catatan Penting</h5>
                    <ul class="mb-0">
                        <li>Simpan username dan password Anda dengan baik</li>
                        <li>Anda dapat mengubah password setelah login</li>
                        <li>Username untuk login adalah <strong>NISN Anda</strong></li>
                        <li>Segera lengkapi data pendaftaran di dashboard</li>
                    </ul>
                </div>

                <!-- Action Button -->
                <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Lanjut ke Dashboard
                </a>

                <p class="text-center mt-3 mb-0" style="color: #666; font-size: 0.875rem;">
                    Anda akan otomatis login dan diarahkan ke dashboard untuk melengkapi data pendaftaran
                </p>
            </div>
        </div>
    </div>
                <p class="text-center mt-3 mb-0" style="color: #666; font-size: 0.875rem;">
                    Anda akan otomatis login dan diarahkan ke dashboard untuk melengkapi data pendaftaran
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script>
    $(document).ready(function() {
        // Copy password to clipboard
        $('#copyPassword').on('click', function() {
            const password = $('#passwordText').text();
            const btn = $(this);
            
            // Modern clipboard API
            if (navigator.clipboard) {
                navigator.clipboard.writeText(password).then(function() {
                    // Success
                    const originalHtml = btn.html();
                    btn.html('<i class="fas fa-check"></i>').addClass('copied');
                    
                    setTimeout(function() {
                        btn.html(originalHtml).removeClass('copied');
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(password).select();
                document.execCommand('copy');
                $temp.remove();
                
                alert('Password berhasil disalin!');
            }
        });

        // Print option (Ctrl+P)
        $(document).on('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    });
    </script>
</body>
</html>
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        h1 {
            color: #333;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .credentials-box {
            background: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .credentials-box h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .credential-value {
            font-weight: 600;
            color: #333;
            font-family: 'Courier New', monospace;
            background: #edf2f7;
            padding: 0.25rem 0.75rem;
            border-radius: 5px;
        }
        
        .copy-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: 0.5rem;
        }
        
        .copy-btn:hover {
            color: #5a67d8;
        }
        
        .wa-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .wa-status.sent {
            background: #d1fae5;
            color: #065f46;
        }
        
        .wa-status.not-sent {
            background: #fef3c7;
            color: #92400e;
        }
        
        .important-note {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .important-note h4 {
            color: #c53030;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .important-note p {
            color: #742a2a;
            font-size: 0.85rem;
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .registration-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .registration-number small {
            opacity: 0.9;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .registration-number strong {
            font-size: 1.25rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1>Registrasi Berhasil!</h1>
            <p class="subtitle">Akun PPDB Anda telah berhasil dibuat</p>

            <div class="registration-number">
                <small>Nomor Registrasi</small>
                <strong>{{ $credentials['nomor_registrasi'] }}</strong>
            </div>

            <div class="credentials-box">
                <h3><i class="fas fa-key"></i> Kredensial Login</h3>
                
                <div class="credential-item">
                    <span class="credential-label">Username (NISN)</span>
                    <div>
                        <span class="credential-value" id="username">{{ $credentials['username'] }}</span>
                        <button class="copy-btn" onclick="copyToClipboard('{{ $credentials['username'] }}')" title="Salin">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Email</span>
                    <div>
                        <span class="credential-value" id="email">{{ $credentials['email'] }}</span>
                        <button class="copy-btn" onclick="copyToClipboard('{{ $credentials['email'] }}')" title="Salin">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">Password</span>
                    <div>
                        <span class="credential-value" id="password">{{ $credentials['password'] }}</span>
                        <button class="copy-btn" onclick="copyToClipboard('{{ $credentials['password'] }}')" title="Salin">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            @if($waSent)
                <div class="wa-status sent">
                    <i class="fab fa-whatsapp"></i>
                    <span>Kredensial telah dikirim ke WhatsApp Anda</span>
                </div>
            @else
                <div class="wa-status not-sent">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Simpan kredensial di atas, tidak dikirim via WhatsApp</span>
                </div>
            @endif

            <div class="important-note">
                <h4><i class="fas fa-exclamation-circle"></i> Penting!</h4>
                <p>Simpan atau screenshot halaman ini. Password tidak dapat dilihat lagi setelah meninggalkan halaman ini.</p>
            </div>

            <a href="{{ route('pendaftar.login') }}" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login Sekarang
            </a>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Berhasil disalin: ' + text);
            }).catch(function(err) {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Berhasil disalin: ' + text);
            });
        }
    </script>
</body>
</html>
