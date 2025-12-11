<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPDB Online - {{ config('app.name') }}</title>
    
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
        
        .nav-link.btn-outline {
            border: 2px solid white;
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
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        @media (max-width: 900px) {
            .container {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
        
        /* Hero Section */
        .hero-content h1 {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .hero-content p {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }
        
        .feature-item i {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 900px) {
            .hero-features {
                align-items: center;
            }
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .card-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
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
        
        .input-group {
            display: flex;
            gap: 0.5rem;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
            color: white;
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .btn-outline-primary:hover {
            background: #667eea;
            color: white;
        }
        
        /* Result Box */
        .result-box {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            border-radius: 10px;
            background: #f8fafc;
        }
        
        .result-box.show {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        .result-box.success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
        }
        
        .result-box.error {
            background: #fff5f5;
            border: 1px solid #feb2b2;
        }
        
        .result-box h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .result-box.success h4 {
            color: #276749;
        }
        
        .result-box.error h4 {
            color: #c53030;
        }
        
        .data-list {
            display: grid;
            gap: 0.5rem;
        }
        
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .data-item:last-child {
            border-bottom: none;
        }
        
        .data-label {
            color: #666;
        }
        
        .data-value {
            font-weight: 500;
            color: #333;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Info Box */
        .info-box {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-box p {
            color: #2c5282;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .info-box i {
            color: #3182ce;
        }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 1rem;
            color: #999;
            font-size: 0.9rem;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 1rem;
            color: rgba(255,255,255,0.7);
        }
        
        /* Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .btn.loading .spinner {
            display: inline-block;
        }
        
        .btn.loading .btn-text {
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Timeline untuk gelombang */
        .gelombang-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .gelombang-info h5 {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .gelombang-info .nama {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .gelombang-info .tanggal {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ url('/') }}" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i>
            PPDB {{ config('app.name') }}
        </a>
        <div class="navbar-nav">
            <a href="{{ route('pendaftar.login') }}" class="nav-link btn-outline">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Hero Content -->
            <div class="hero-content">
                <h1>Pendaftaran Peserta Didik Baru</h1>
                <p>Tahun Pelajaran {{ $tahunAktif->nama ?? date('Y') . '/' . (date('Y') + 1) }}</p>
                
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Pendaftaran Online 24 Jam</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Validasi NISN Otomatis</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Pantau Status Real-time</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Cetak Bukti Pendaftaran</span>
                    </div>
                </div>
            </div>

            <!-- Registration Card -->
            <div class="card">
                <h2 class="card-title">Mulai Pendaftaran</h2>
                <p class="card-subtitle">Masukkan NISN untuk memulai</p>

                @if($gelombangAktif)
                <div class="gelombang-info">
                    <h5>Gelombang Pendaftaran Aktif</h5>
                    <div class="nama">{{ $gelombangAktif->nama }}</div>
                    <div class="tanggal">
                        {{ \Carbon\Carbon::parse($gelombangAktif->tanggal_mulai)->format('d M Y') }} - 
                        {{ \Carbon\Carbon::parse($gelombangAktif->tanggal_selesai)->format('d M Y') }}
                    </div>
                </div>
                @else
                <div class="info-box" style="background: #fff5f5; border-color: #feb2b2;">
                    <p><i class="fas fa-exclamation-triangle"></i> Saat ini tidak ada gelombang pendaftaran yang aktif.</p>
                </div>
                @endif

                <form id="cekNisnForm">
                    <div class="form-group">
                        <label class="form-label">NISN (Nomor Induk Siswa Nasional)</label>
                        <div class="input-group">
                            <input type="text" id="nisn" name="nisn" class="form-control" 
                                   placeholder="Contoh: 0012345678" maxlength="10" 
                                   pattern="[0-9]{10}" required {{ !$gelombangAktif ? 'disabled' : '' }}>
                            <button type="submit" class="btn btn-primary" id="cekNisnBtn" {{ !$gelombangAktif ? 'disabled' : '' }}>
                                <span class="spinner"></span>
                                <span class="btn-text">Cek NISN</span>
                            </button>
                        </div>
                        <small style="color: #666; display: block; margin-top: 0.5rem;">
                            NISN terdiri dari 10 digit angka
                        </small>
                    </div>
                </form>

                <!-- Result Box -->
                <div id="resultBox" class="result-box">
                    <h4 id="resultTitle">
                        <i class="fas fa-check-circle"></i>
                        <span>Data Ditemukan</span>
                    </h4>
                    <div class="data-list" id="resultData">
                        <!-- Data will be inserted here -->
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <a href="#" id="daftarBtn" class="btn btn-success btn-block">
                            <i class="fas fa-arrow-right"></i> Lanjut Daftar
                        </a>
                    </div>
                </div>

                <div class="divider">
                    <span>atau</span>
                </div>

                <p style="text-align: center; color: #666; margin-bottom: 1rem;">
                    Sudah punya akun?
                </p>
                <a href="{{ route('pendaftar.login') }}" class="btn btn-outline-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; {{ date('Y') }} PPDB {{ config('app.name') }}. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // NISN only numbers
            $('#nisn').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Cek NISN Form
            $('#cekNisnForm').on('submit', function(e) {
                e.preventDefault();
                
                const nisn = $('#nisn').val();
                if (nisn.length !== 10) {
                    toastr.error('NISN harus 10 digit');
                    return;
                }

                const btn = $('#cekNisnBtn');
                btn.addClass('loading').prop('disabled', true);
                $('#resultBox').removeClass('show success error');

                $.ajax({
                    url: '{{ route("pendaftar.cek-nisn") }}',
                    method: 'POST',
                    data: {
                        nisn: nisn,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        btn.removeClass('loading').prop('disabled', false);
                        
                        if (response.already_registered) {
                            toastr.warning(response.message);
                            setTimeout(function() {
                                window.location.href = '{{ route("pendaftar.login") }}';
                            }, 2000);
                            return;
                        }

                        $('#resultBox').addClass('show');
                        
                        if (response.data) {
                            // Data found from EMIS
                            $('#resultBox').addClass('success');
                            $('#resultTitle').html('<i class="fas fa-check-circle"></i> <span>Data Ditemukan</span>');
                            
                            let html = '';
                            html += `<div class="data-item"><span class="data-label">Nama</span><span class="data-value">${response.data.nama || '-'}</span></div>`;
                            html += `<div class="data-item"><span class="data-label">Tempat, Tgl Lahir</span><span class="data-value">${response.data.tempat_lahir || '-'}, ${response.data.tanggal_lahir || '-'}</span></div>`;
                            html += `<div class="data-item"><span class="data-label">Jenis Kelamin</span><span class="data-value">${response.data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</span></div>`;
                            html += `<div class="data-item"><span class="data-label">Asal Sekolah</span><span class="data-value">${response.data.sekolah_asal || '-'}</span></div>`;
                            $('#resultData').html(html);
                            
                            // Store data in session for registration
                            sessionStorage.setItem('emisData', JSON.stringify(response.data));
                            sessionStorage.setItem('nisnValid', 'true');
                        } else {
                            // Manual input allowed
                            $('#resultBox').addClass('success');
                            $('#resultTitle').html('<i class="fas fa-info-circle"></i> <span>Input Manual</span>');
                            $('#resultData').html('<p style="color: #666;">NISN tidak ditemukan di database EMIS. Anda dapat melanjutkan pendaftaran dengan mengisi data secara manual.</p>');
                            
                            sessionStorage.removeItem('emisData');
                            sessionStorage.setItem('nisnValid', 'false');
                        }
                        
                        // Set registration link
                        $('#daftarBtn').attr('href', '{{ route("pendaftar.register.form") }}?nisn=' + nisn);
                    },
                    error: function(xhr) {
                        btn.removeClass('loading').prop('disabled', false);
                        
                        let message = 'Terjadi kesalahan saat memeriksa NISN';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        toastr.error(message);
                    }
                });
            });
        });
    </script>
</body>
</html>
