<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Berhasil - PPDB {{ config('app.name') }}</title>
    
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
            max-width: 500px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
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
