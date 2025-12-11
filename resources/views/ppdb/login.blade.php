@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $login_url = route('ppdb.login') )

@section('adminlte_css')
    <style>
        .login-card-body {
            padding: 30px;
        }
        .login-logo a {
            color: #007bff;
        }
        .login-info {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .login-info h6 {
            margin-bottom: 10px;
            color: #0056b3;
        }
        .login-info ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .login-info li {
            color: #555;
            font-size: 13px;
        }
        .error-box {
            background: #ffe6e6;
            border-left: 4px solid #dc3545;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .btn-login {
            font-size: 16px;
            padding: 12px;
            font-weight: 600;
        }
        .demo-credential {
            background: #fff3cd;
            border: 1px dashed #ffc107;
            padding: 10px;
            margin-top: 15px;
            border-radius: 4px;
            font-size: 12px;
        }
        .demo-credential strong {
            color: #856404;
        }
    </style>
@stop

@section('auth_header')
    <h4 class="mb-0"><i class="fas fa-graduation-cap text-primary"></i> Login PPDB</h4>
    <small class="text-muted">{{ $sekolahSettings->nama_sekolah ?? 'Sistem Penerimaan Peserta Didik Baru' }}</small>
@stop

@section('auth_body')
    {{-- Success Message --}}
    @if(session('success'))
        <div class="success-box">
            <i class="fas fa-check-circle text-success"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="error-box">
            <i class="fas fa-exclamation-circle text-danger"></i>
            @foreach($errors->all() as $error)
                <strong>{{ $error }}</strong>
            @endforeach
        </div>
    @endif

    {{-- Warning Message --}}
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    {{-- Login Info --}}
    <div class="login-info">
        <h6><i class="fas fa-info-circle"></i> Informasi Login</h6>
        <ul>
            <li>Gunakan email yang terdaftar</li>
            <li>Password bersifat case-sensitive</li>
            <li>Hubungi admin jika lupa password</li>
        </ul>
    </div>

    <form action="{{ $login_url }}" method="post" id="loginForm">
        @csrf

        {{-- Email field --}}
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <div class="input-group">
                <input type="email" 
                       name="email" 
                       id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" 
                       placeholder="Masukkan email Anda" 
                       autofocus 
                       required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-at"></span>
                    </div>
                </div>
            </div>
            @error('email')
                <small class="text-danger"><i class="fas fa-times-circle"></i> {{ $message }}</small>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Masukkan password Anda" 
                       required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @error('password')
                <small class="text-danger"><i class="fas fa-times-circle"></i> {{ $message }}</small>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                <label class="custom-control-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
        </div>

        {{-- Login button --}}
        <button type="submit" class="btn btn-primary btn-block btn-login" id="btnLogin">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </button>
    </form>

    {{-- Demo Credential (remove in production) --}}
    <div class="demo-credential">
        <strong><i class="fas fa-key"></i> Demo Admin:</strong><br>
        Email: <code>admin@ppdb.local</code><br>
        Password: <code>admin123</code>
    </div>
@stop

@section('auth_footer')
    <div class="text-center">
        <p class="mb-2">
            <a href="{{ route('ppdb.landing') }}" class="text-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </p>
        @if(Route::has('ppdb.register.step1'))
        <p class="mb-0">
            <a href="{{ route('ppdb.register.step1') }}" class="text-primary">
                <i class="fas fa-user-plus"></i> Belum punya akun? Daftar PPDB
            </a>
        </p>
        @endif
    </div>
@stop

@section('adminlte_js')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        var passwordInput = document.getElementById('password');
        var icon = this.querySelector('i');
        
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

    // Disable button on submit to prevent double click
    document.getElementById('loginForm').addEventListener('submit', function() {
        var btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    });
</script>
@stop
