<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - PPDB {{ config('app.name') }}</title>
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .container { width: 100%; max-width: 420px; }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .header { text-align: center; margin-bottom: 2rem; }
        
        .header .icon {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .header .icon i { font-size: 2.5rem; color: white; }
        .header h1 { font-size: 1.5rem; color: #333; margin-bottom: 0.5rem; }
        .header p { color: #666; font-size: 0.9rem; }
        
        .form-group { margin-bottom: 1.25rem; }
        
        .form-label {
            display: block; font-weight: 500; color: #333;
            margin-bottom: 0.5rem; font-size: 0.9rem;
        }
        
        .input-icon-wrapper { position: relative; }
        
        .input-icon-wrapper .icon {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%); color: #9ca3af;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
            margin-top: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .alert i { font-size: 1.25rem; flex-shrink: 0; margin-top: 2px; }
        .alert-content { flex: 1; font-size: 0.9rem; line-height: 1.5; }
        
        .error-text {
            color: #dc2626;
            margin-top: 0.25rem;
            display: block;
            font-size: 0.8rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            border-radius: 2px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .password-strength .bar {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
            width: 0;
        }

        .password-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
        }

        .toggle-password:hover { color: #374151; }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .back-link a:hover { color: #764ba2; }

        .spinner {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="icon">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h1>Buat Password Baru</h1>
                <p>Masukkan password baru untuk akun Anda</p>
            </div>

            @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div class="alert-content">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('pendaftar.reset-password.post') }}" method="POST" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" class="form-control" value="{{ $email }}" disabled style="background: #f3f4f6; color: #6b7280;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Masukkan password baru"
                               required minlength="6" autofocus>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="bar" id="strengthBar"></div>
                    </div>
                    <div class="password-hint" id="strengthText">Minimal 6 karakter</div>
                    @error('password')
                    <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                               placeholder="Ulangi password baru"
                               required minlength="6">
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-hint" id="matchText"></div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Password Baru
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('pendaftar.login') }}">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(id, btn) {
            var input = document.getElementById(id);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            var val = this.value;
            var bar = document.getElementById('strengthBar');
            var text = document.getElementById('strengthText');
            var score = 0;

            if (val.length >= 6) score++;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            var pct = (score / 5) * 100;
            bar.style.width = pct + '%';

            if (score <= 1) { bar.style.background = '#ef4444'; text.textContent = 'Lemah'; text.style.color = '#ef4444'; }
            else if (score <= 2) { bar.style.background = '#f59e0b'; text.textContent = 'Sedang'; text.style.color = '#f59e0b'; }
            else if (score <= 3) { bar.style.background = '#3b82f6'; text.textContent = 'Cukup Kuat'; text.style.color = '#3b82f6'; }
            else { bar.style.background = '#10b981'; text.textContent = 'Kuat'; text.style.color = '#10b981'; }

            if (val.length === 0) { bar.style.width = '0'; text.textContent = 'Minimal 6 karakter'; text.style.color = '#9ca3af'; }

            checkMatch();
        });

        document.getElementById('password_confirmation').addEventListener('input', checkMatch);

        function checkMatch() {
            var pw = document.getElementById('password').value;
            var cf = document.getElementById('password_confirmation').value;
            var text = document.getElementById('matchText');
            
            if (cf.length === 0) { text.textContent = ''; return; }
            if (pw === cf) { text.textContent = '✓ Password cocok'; text.style.color = '#10b981'; }
            else { text.textContent = '✗ Password tidak cocok'; text.style.color = '#ef4444'; }
        }

        document.getElementById('resetForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Menyimpan...';
        });
    </script>
</body>
</html>
