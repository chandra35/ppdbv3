<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - PPDB Online {{ config('app.name') }}</title>
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .navbar-nav {
            display: flex;
            gap: 1rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
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
            max-width: 500px;
            width: 100%;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .card-subtitle {
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .required {
            color: #e53e3e;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control:read-only {
            background: #f7fafc;
            color: #4a5568;
        }
        
        .form-control.is-invalid {
            border-color: #e53e3e;
        }
        
        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        .form-text {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
            display: block;
        }
        
        .input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        
        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            color: #c53030;
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
        
        .close {
            float: right;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
        }
        
        .close:hover {
            opacity: 1;
        }
        
        /* Buttons */
        .btn {
            padding: 0.875rem 1.5rem;
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-default {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }
        
        .btn-default:hover {
            background: #edf2f7;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }
        
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
            
            .btn-group {
                grid-template-columns: 1fr;
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
        <div class="navbar-nav">
            <a href="{{ route('pendaftar.login') }}" class="nav-link">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Lengkapi Data Pendaftaran</h2>
                    <p class="card-subtitle">Isi formulir di bawah untuk membuat akun PPDB Anda</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('pendaftar.register.post') }}" method="POST" id="registerForm">
                    @csrf

                    <!-- NISN (readonly) -->
                    <div class="form-group">
                        <label class="form-label">
                            NISN <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nisn') is-invalid @enderror" 
                               id="nisn" 
                               name="nisn" 
                               value="{{ old('nisn', $nisn) }}" 
                               readonly
                               required>
                        @error('nisn')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label class="form-label">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nama_lengkap') is-invalid @enderror" 
                               id="nama_lengkap" 
                               name="nama_lengkap" 
                               value="{{ old('nama_lengkap', $nama_lengkap) }}" 
                               {{ $nama_lengkap ? 'readonly' : '' }}
                               required>
                        @error('nama_lengkap')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($nama_lengkap)
                            <small class="form-text">Sesuai data EMIS</small>
                        @else
                            <small class="form-text">Data tidak ditemukan di EMIS, silakan input manual</small>
                        @endif
                    </div>

                    <!-- Hidden: EMIS data -->
                    <input type="hidden" name="emis_data" value="{{ json_encode($emis_data) }}">

                    <!-- Nomor WhatsApp -->
                    <div class="form-group">
                        <label class="form-label">
                            Nomor WhatsApp <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nomor_hp') is-invalid @enderror" 
                               id="nomor_hp" 
                               name="nomor_hp" 
                               value="{{ old('nomor_hp') }}" 
                               placeholder="Contoh: 081234567890"
                               required>
                        @error('nomor_hp')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text">
                            <i class="fas fa-info-circle"></i> Username dan password akan dikirim ke nomor WhatsApp ini
                        </small>
                    </div>

                    <!-- Email (opsional) -->
                    <div class="form-group">
                        <label class="form-label">Email (Opsional)</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="email@example.com">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Informasi</h5>
                        <ul>
                            <li>Akun akan dibuat otomatis dengan username: <strong>NISN Anda</strong></li>
                            <li>Password akan digenerate otomatis dan dikirim via WhatsApp</li>
                            <li>Anda dapat mengubah password setelah login</li>
                            <li>Setelah daftar, Anda akan langsung masuk ke dashboard untuk melengkapi data</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="btn-group">
                        <a href="{{ route('pendaftar.landing') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Format nomor HP
        $('#nomor_hp').on('input', function() {
            let value = $(this).val().replace(/\D/g, ''); // Only numbers
            
            // Convert 08xxx to 628xxx
            if (value.startsWith('08')) {
                value = '62' + value.substring(1);
            }
            
            $(this).val(value);
        });

        // Form validation
        $('#registerForm').on('submit', function(e) {
            const nomorHp = $('#nomor_hp').val();
            
            // Validate phone format
            if (!nomorHp.startsWith('62')) {
                e.preventDefault();
                toastr.error('Nomor WhatsApp harus diawali dengan 62 (contoh: 628123456789)');
                $('#nomor_hp').focus();
                return false;
            }
            
            if (nomorHp.length < 10) {
                e.preventDefault();
                toastr.error('Nomor WhatsApp tidak valid. Minimal 10 digit.');
                $('#nomor_hp').focus();
                return false;
            }
            
            // Disable submit button
            $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        });
    });
    </script>
</body>
</html>
