<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - PPDB {{ config('app.name') }}</title>
    
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
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .container {
            width: 100%;
            max-width: 420px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .header .icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .header h1 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .input-icon-wrapper {
            position: relative;
        }
        
        .input-icon-wrapper .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        .alert i {
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .alert-content {
            flex: 1;
        }
        
        .info-box {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-box h4 {
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-box p {
            font-size: 0.8rem;
            color: #6b7280;
            line-height: 1.5;
        }
        
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
        
        .back-link a:hover {
            color: #764ba2;
        }
        
        .whatsapp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #25d366;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .wa-not-active {
            text-align: center;
            padding: 2rem;
        }
        
        .wa-not-active .icon-large {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .wa-not-active .icon-large i {
            font-size: 2rem;
            color: #9ca3af;
        }
        
        .wa-not-active h3 {
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .wa-not-active p {
            color: #6b7280;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Lupa Password</h1>
                <p>Reset password via WhatsApp</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
            @endif

            @if($waActive)
                <div class="info-box">
                    <h4>
                        <i class="fab fa-whatsapp" style="color: #25d366;"></i>
                        Cara Reset Password
                    </h4>
                    <p>
                        Masukkan NISN Anda. Password baru akan dikirim ke nomor WhatsApp 
                        yang terdaftar pada saat pendaftaran PPDB.
                    </p>
                </div>

                <form action="{{ route('pendaftar.forgot-password.send') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">NISN</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-id-card icon"></i>
                            <input type="text" name="nisn" class="form-control" 
                                   placeholder="Masukkan NISN Anda"
                                   value="{{ old('nisn') }}"
                                   required
                                   maxlength="20"
                                   autofocus>
                        </div>
                        @error('nisn')
                        <small style="color: #dc2626; margin-top: 0.25rem; display: block;">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-whatsapp"></i> Kirim Password Baru
                    </button>
                </form>
            @else
                <div class="wa-not-active">
                    <div class="icon-large">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Layanan Tidak Tersedia</h3>
                    <p>
                        Fitur reset password via WhatsApp sedang tidak aktif. 
                        Silakan hubungi admin atau panitia PPDB untuk bantuan reset password.
                    </p>
                </div>
            @endif

            <div class="back-link">
                <a href="{{ route('pendaftar.login') }}">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
