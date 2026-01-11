<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login PPDB - {{ $sekolahSettings->nama_sekolah ?? 'PPDB Online' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --secondary: #06b6d4;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-attachment: fixed;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .bg-shapes .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: float 20s infinite ease-in-out;
        }
        
        .bg-shapes .shape:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .bg-shapes .shape:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: -50px;
            right: -50px;
            animation-delay: -5s;
        }
        
        .bg-shapes .shape:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 30%;
            animation-delay: -10s;
        }
        
        .bg-shapes .shape:nth-child(4) {
            width: 150px;
            height: 150px;
            bottom: 30%;
            right: 20%;
            animation-delay: -15s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.1; }
            50% { transform: translateY(-30px) rotate(180deg); opacity: 0.15; }
        }
        
        /* Main Container */
        .login-container {
            min-height: 100vh;
            display: flex;
            position: relative;
            z-index: 1;
        }
        
        /* Left Panel - Branding */
        .brand-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .brand-content {
            max-width: 500px;
        }
        
        .school-logo {
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: pulse-glow 3s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
            50% { box-shadow: 0 20px 60px rgba(255,255,255,0.2); }
        }
        
        .school-name {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .school-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        .feature-list {
            text-align: left;
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(10px);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .feature-text h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .feature-text p {
            font-size: 0.85rem;
            opacity: 0.8;
            margin: 0;
        }
        
        /* Right Panel - Login Form */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
        }
        
        .login-card {
            width: 100%;
            max-width: 440px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .mobile-logo {
            display: none;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 20px;
            margin: 0 auto 20px;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
        }
        
        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        /* Alert Messages */
        .alert-box {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .alert-box.error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: var(--danger);
        }
        
        .alert-box.success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            color: var(--success);
        }
        
        .alert-box.warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
            color: var(--warning);
        }
        
        .alert-box i {
            font-size: 1.2rem;
            margin-top: 2px;
        }
        
        .alert-box span {
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        /* Login Info Box */
        .login-info-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .login-info-box h5 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-types {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .login-type-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: white;
            border-radius: 10px;
            font-size: 0.85rem;
            color: var(--dark);
            transition: all 0.3s ease;
        }
        
        .login-type-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .login-type-item i {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        
        .login-type-item.siswa i { background: #dbeafe; color: #2563eb; }
        .login-type-item.gtk i { background: #dcfce7; color: #16a34a; }
        .login-type-item.admin i { background: #fef3c7; color: #d97706; }
        .login-type-item.email i { background: #f3e8ff; color: #9333ea; }
        
        .login-type-item strong {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .login-type-item small {
            font-size: 0.7rem;
            color: #64748b;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-group label i {
            margin-right: 6px;
            color: var(--primary);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 50px 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .input-wrapper input.is-invalid {
            border-color: var(--danger);
        }
        
        .input-wrapper .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }
        
        .input-wrapper .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s ease;
        }
        
        .input-wrapper .toggle-password:hover {
            color: var(--primary);
        }
        
        .error-text {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            color: var(--danger);
            font-size: 0.8rem;
        }
        
        /* Remember & Forgot */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        
        .remember-me span {
            font-size: 0.9rem;
            color: #64748b;
        }
        
        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(79, 70, 229, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Footer Links */
        .login-footer {
            margin-top: 30px;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .footer-link {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .footer-link:hover {
            background: #f1f5f9;
            color: var(--primary);
        }
        
        .footer-link.register {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: var(--success);
            font-weight: 600;
        }
        
        .footer-link.register:hover {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
            }
            
            .brand-panel {
                padding: 40px 20px;
                min-height: auto;
            }
            
            .feature-list {
                display: none;
            }
            
            .school-logo {
                width: 80px;
                height: 80px;
                font-size: 35px;
                border-radius: 20px;
                margin-bottom: 20px;
            }
            
            .school-name {
                font-size: 1.5rem;
            }
            
            .school-subtitle {
                font-size: 0.95rem;
                margin-bottom: 0;
            }
            
            .form-panel {
                border-radius: 30px 30px 0 0;
                margin-top: -20px;
                padding: 30px 20px 40px;
            }
            
            .login-header .mobile-logo {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .brand-panel {
                padding: 30px 15px;
            }
            
            .school-logo {
                width: 70px;
                height: 70px;
                font-size: 30px;
            }
            
            .school-name {
                font-size: 1.3rem;
            }
            
            .form-panel {
                padding: 25px 15px 35px;
            }
            
            .login-types {
                grid-template-columns: 1fr;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .footer-link {
                justify-content: center;
            }
        }
        
        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .slide-up {
            animation: slideUp 0.6s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="brand-panel fade-in">
            <div class="brand-content">
                <div class="school-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="school-name">{{ $sekolahSettings->nama_sekolah ?? 'PPDB Online' }}</h1>
                <p class="school-subtitle">Sistem Penerimaan Peserta Didik Baru</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Aman & Terpercaya</h4>
                            <p>Data Anda terlindungi dengan enkripsi</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Akses 24 Jam</h4>
                            <p>Daftar kapan saja, di mana saja</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-screen"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Mobile Friendly</h4>
                            <p>Akses mudah dari perangkat apapun</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel - Login Form -->
        <div class="form-panel">
            <div class="login-card slide-up">
                <div class="login-header">
                    <div class="mobile-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Selamat Datang!</h2>
                    <p>Silakan masuk untuk melanjutkan</p>
                </div>
                
                {{-- Success Messages --}}
                @if(session('success'))
                    <div class="alert-box success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                
                {{-- Error Messages --}}
                @if($errors->any())
                    <div class="alert-box error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </span>
                    </div>
                @endif
                
                {{-- Warning Messages --}}
                @if(session('warning'))
                    <div class="alert-box warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif
                
                <form action="{{ route('ppdb.login') }}" method="post" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="login">
                            <i class="fas fa-user"></i> Username / Email / NISN / NIP
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="login" 
                                   id="login"
                                   class="@error('login') is-invalid @enderror"
                                   value="{{ old('login') }}" 
                                   placeholder="Masukkan identitas Anda" 
                                   autofocus 
                                   required>
                            <span class="input-icon">
                                <i class="fas fa-id-card"></i>
                            </span>
                        </div>
                        @error('login')
                            <div class="error-text">
                                <i class="fas fa-times-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="@error('password') is-invalid @enderror"
                                   placeholder="Masukkan password" 
                                   required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-text">
                                <i class="fas fa-times-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Ingat saya</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login" id="btnLogin">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </button>
                </form>
                
                <div class="login-footer">
                    <div class="footer-links">
                        <a href="{{ route('ppdb.landing') }}" class="footer-link">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke Beranda</span>
                        </a>
                        @if(Route::has('ppdb.register.step1'))
                        <a href="{{ route('ppdb.register.step1') }}" class="footer-link register">
                            <i class="fas fa-user-plus"></i>
                            <span>Daftar PPDB</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Disable button on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Memproses...</span>';
        });
    </script>
    
    {{-- GPS Permission Component --}}
    @include('components.gps-permission')
</body>
</html>
