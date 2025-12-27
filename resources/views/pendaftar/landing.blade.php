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
        
        /* Preview Box */
        .preview-box {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 2px solid #cbd5e0;
            border-radius: 10px;
            padding: 1rem;
        }
        
        .preview-grid {
            background: white;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .preview-item {
            padding: 0.5rem 0;
        }
        
        .preview-item:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
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
            <div class="card" id="nisnCheckCard">
                <h2 class="card-title">Mulai Pendaftaran</h2>
                <p class="card-subtitle">Masukkan NISN untuk memulai</p>

                @if($gelombangAktif)
                <div class="gelombang-info">
                    <h5><i class="fas fa-check-circle text-success"></i> Pendaftaran Dibuka</h5>
                    @if($gelombangAktif->tampil_nama_gelombang)
                    <div class="nama">{{ $gelombangAktif->nama }}</div>
                    @endif
                    <div class="tanggal">
                        {{ $gelombangAktif->tanggal_buka->format('d M Y') }} - 
                        {{ $gelombangAktif->tanggal_tutup->format('d M Y') }}
                    </div>
                    @if($gelombangAktif->tampil_kuota && $gelombangAktif->kuota)
                    <div class="kuota mt-2">
                        <small>Kuota: {{ $gelombangAktif->kuota_terisi ?? 0 }}/{{ $gelombangAktif->kuota }}</small>
                    </div>
                    @endif
                </div>
                @else
                <div class="info-box" style="background: #fff5f5; border-color: #feb2b2;">
                    <p><i class="fas fa-exclamation-triangle"></i> Saat ini pendaftaran belum dibuka.</p>
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

            <!-- Registration Form Card (Hidden initially) -->
            <div class="card" id="registrationFormCard" style="display: none;">
                <h2 class="card-title">Lengkapi Data Pendaftaran</h2>
                <p class="card-subtitle">Isi formulir di bawah untuk membuat akun PPDB Anda</p>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #fee; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #fee; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('pendaftar.register.post') }}" method="POST" id="registrationForm">
                    @csrf
                    
                    <!-- Hidden fields -->
                    <input type="hidden" id="reg_nisn_hidden" name="nisn">
                    <input type="hidden" id="reg_encrypted_token" name="encrypted_token">
                    <input type="hidden" id="reg_emis_data" name="emis_data">
                    
                    <!-- Preview Data EMIS -->
                    <div class="preview-box" id="previewBox" style="display: none; margin-bottom: 1.5rem;">
                        <h5 style="color: #667eea; margin-bottom: 0.75rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-database"></i> <span id="previewSourceLabel">Data dari EMIS</span>
                        </h5>
                        <div class="preview-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; font-size: 0.9rem;">
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">NISN</span>
                                <span class="preview-value" id="preview_nisn" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">NIK</span>
                                <span class="preview-value" id="preview_nik" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item" style="grid-column: 1 / -1;">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Nama Lengkap</span>
                                <span class="preview-value" id="preview_nama" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Tempat, Tanggal Lahir</span>
                                <span class="preview-value" id="preview_ttl" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Jenis Kelamin</span>
                                <span class="preview-value" id="preview_jk" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Agama</span>
                                <span class="preview-value" id="preview_agama" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Sekolah Asal</span>
                                <span class="preview-value" id="preview_sekolah" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;"><span id="preview_npsn_label">NPSN</span></span>
                                <span class="preview-value" id="preview_npsn" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                            <div class="preview-item" style="grid-column: 1 / -1;">
                                <span class="preview-label" style="color: #718096; display: block; font-size: 0.85rem;">Alamat</span>
                                <span class="preview-value" id="preview_alamat" style="font-weight: 500; color: #2d3748;">-</span>
                            </div>
                        </div>
                        <p style="margin-top: 0.75rem; margin-bottom: 0; font-size: 0.85rem; color: #718096; text-align: center;">
                            <i class="fas fa-info-circle"></i> Data dapat dilengkapi di dashboard setelah login
                        </p>
                    </div>
                    
                    <!-- NISN (readonly) -->
                    <div class="form-group">
                        <label class="form-label">NISN <span style="color: #e53e3e;">*</span></label>
                        <input type="text" id="reg_nisn_display" class="form-control" readonly>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span style="color: #e53e3e;">*</span></label>
                        <input type="text" id="reg_nama_lengkap" name="nama_lengkap" class="form-control" required>
                        <small id="emisNote" style="color: #666; display: none; margin-top: 0.5rem;">Sesuai data EMIS</small>
                        <small id="manualNote" style="color: #666; display: none; margin-top: 0.5rem;">Data tidak ditemukan di EMIS, silakan input manual</small>
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp <span style="color: #e53e3e;">*</span></label>
                        <input type="text" id="reg_nomor_hp" name="nomor_hp" class="form-control" 
                               placeholder="Contoh: 081234567890" required>
                        <small style="color: #666; display: block; margin-top: 0.5rem;">
                            <i class="fas fa-info-circle"></i> Username dan password akan dikirim ke nomor WhatsApp ini
                        </small>
                    </div>

                    <!-- Email (opsional) -->
                    <div class="form-group">
                        <label class="form-label">Email (Opsional)</label>
                        <input type="email" id="reg_email" name="email" class="form-control" placeholder="email@example.com">
                    </div>

                    <!-- Info Box -->
                    <div class="info-box">
                        <p><strong><i class="fas fa-info-circle"></i> Informasi:</strong></p>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; font-size: 0.875rem;">
                            <li>Akun akan dibuat otomatis dengan username: <strong>NISN Anda</strong></li>
                            <li>Password akan digenerate otomatis dan dikirim via WhatsApp</li>
                            <li>Anda dapat mengubah password setelah login</li>
                            <li>Setelah daftar, Anda akan langsung masuk ke dashboard</li>
                        </ul>
                    </div>

                    <!-- Buttons -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline-primary" id="btnCancelRegister">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnRegister">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>
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
            // Check if there are validation errors, show registration form
            @if ($errors->any() || session('error'))
                $('#nisnCheckCard').hide();
                $('#registrationFormCard').show();
                
                // Scroll to error
                $('html, body').animate({
                    scrollTop: $('#registrationFormCard').offset().top - 100
                }, 500);
            @endif
            
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
                    dataType: 'json',
                    data: {
                        nisn: nisn,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        btn.removeClass('loading').prop('disabled', false);
                        
                        if (response.already_registered) {
                            toastr.warning(response.message || 'NISN sudah terdaftar.');
                            setTimeout(function() {
                                window.location.href = '{{ route("pendaftar.login") }}';
                            }, 2000);
                            return;
                        }

                        if (response.success === true) {
                            $('#resultBox').addClass('show');
                            if (response.data && response.data !== null) {
                                // Data found from EMIS
                                $('#resultBox').addClass('success');
                                
                                // Show data source
                                let titleText = 'Data Ditemukan';
                                if (response.data_source) {
                                    titleText += ' di ' + response.data_source;
                                }
                                $('#resultTitle').html('<i class="fas fa-check-circle"></i> <span>' + titleText + '</span>');
                                
                                let html = '';
                                html += `<div class="data-item"><span class="data-label">Nama</span><span class="data-value">${response.data.nama || '-'}</span></div>`;
                                html += `<div class="data-item"><span class="data-label">Tempat, Tgl Lahir</span><span class="data-value">${response.data.tempat_lahir || '-'}, ${response.data.tanggal_lahir || '-'}</span></div>`;
                                html += `<div class="data-item"><span class="data-label">Jenis Kelamin</span><span class="data-value">${response.data.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</span></div>`;
                                html += `<div class="data-item"><span class="data-label">Asal Sekolah</span><span class="data-value">${response.data.sekolah_asal || '-'}</span></div>`;
                                
                                // Show eligibility warning if not eligible
                                if (response.is_eligible === false && response.warning) {
                                    html += `<div class="alert alert-danger mt-3" style="margin: 0; padding: 15px; border-left: 5px solid #dc3545; background: #fee; border-radius: 8px;">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <i class="fas fa-exclamation-circle" style="color: #dc3545; font-size: 24px; margin-top: 2px;"></i>
                                            <div>
                                                <h5 style="color: #dc3545; margin: 0 0 8px 0; font-weight: 600;">
                                                    <i class="fas fa-ban"></i> NISN Tidak Dapat Didaftarkan
                                                </h5>
                                                <p style="margin: 0; color: #721c24; line-height: 1.6;">
                                                    ${response.warning}
                                                </p>
                                                <hr style="margin: 12px 0; border-color: #f5c6cb;">
                                                <small style="color: #856404; display: block;">
                                                    <i class="fas fa-info-circle"></i> 
                                                    Silakan periksa kembali NISN Anda atau hubungi sekolah asal untuk informasi lebih lanjut.
                                                </small>
                                            </div>
                                        </div>
                                    </div>`;
                                }
                                
                                $('#resultData').html(html);
                                
                                // Store data in session for registration
                                sessionStorage.setItem('emisData', JSON.stringify(response.data));
                                sessionStorage.setItem('nisnValid', 'true');
                                sessionStorage.setItem('isEligible', response.is_eligible ? 'true' : 'false');
                                
                                // Enable/disable registration button based on eligibility
                                if (response.is_eligible === false) {
                                    $('#daftarBtn').hide();
                                } else {
                                    // Show register button, don't redirect - show form inline
                                    $('#daftarBtn').show().off('click').on('click', function(e) {
                                        e.preventDefault();
                                        showRegistrationForm(nisn, response.data, response.encrypted_nisn, response.data_source);
                                    });
                                }
                            } else {
                                // Manual input allowed
                                $('#resultBox').addClass('success');
                                $('#resultTitle').html('<i class="fas fa-info-circle"></i> <span>Input Manual</span>');
                                $('#resultData').html('<p style="color: #666;">NISN tidak ditemukan di database EMIS. Anda dapat melanjutkan pendaftaran dengan mengisi data secara manual.</p>');
                                sessionStorage.removeItem('emisData');
                                sessionStorage.setItem('nisnValid', 'false');
                                // Show register button for manual input
                                $('#daftarBtn').show().off('click').on('click', function(e) {
                                    e.preventDefault();
                                    showRegistrationForm(nisn, null, response.encrypted_nisn, null);
                                });
                            }
                        } else {
                            toastr.error(response.message || 'Terjadi kesalahan saat memeriksa NISN');
                        }
                    },
                    error: function(xhr, status, error) {
                        btn.removeClass('loading').prop('disabled', false);
                        
                        console.log('AJAX Error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error
                        });
                        
                        let message = 'Terjadi kesalahan saat memeriksa NISN';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        toastr.error(message);
                    }
                });
            });

            // Function to show registration form inline
            function showRegistrationForm(nisn, emisData, encryptedToken, dataSource) {
                // Hide NISN check form
                $('#nisnCheckCard').slideUp(300);
                
                // Show registration form
                setTimeout(function() {
                    $('#registrationFormCard').slideDown(300);
                    
                    // Fill form with data
                    $('#reg_nisn_hidden').val(nisn);
                    $('#reg_nisn_display').val(nisn);
                    $('#reg_nama_lengkap').val(emisData?.nama || '');
                    $('#reg_encrypted_token').val(encryptedToken);
                    $('#reg_emis_data').val(JSON.stringify(emisData || {}));
                    
                    // Set readonly for nama if from EMIS
                    if (emisData?.nama) {
                        $('#reg_nama_lengkap').attr('readonly', true);
                        $('#emisNote').show();
                        $('#manualNote').hide();
                        
                        // Update preview source label
                        if (dataSource) {
                            $('#previewSourceLabel').text('Data dari ' + dataSource);
                        }
                        
                        // Show and populate preview box (works for both Kemdikbud and Kemenag)
                        populatePreviewBox(emisData);
                        $('#previewBox').slideDown(200);
                    } else {
                        $('#reg_nama_lengkap').attr('readonly', false);
                        $('#emisNote').hide();
                        $('#manualNote').show();
                        $('#previewBox').hide();
                    }
                }, 300);
            }
            
            // Function to populate preview box with EMIS data
            function populatePreviewBox(data) {
                if (!data) return;
                
                // Basic info
                $('#preview_nisn').text(data.nisn || '-');
                $('#preview_nik').text(data.nik || '-');
                $('#preview_nama').text(data.nama || '-');
                
                // Tempat, Tanggal Lahir
                let ttl = [];
                if (data.tempat_lahir) ttl.push(data.tempat_lahir);
                if (data.tanggal_lahir) ttl.push(formatTanggal(data.tanggal_lahir));
                $('#preview_ttl').text(ttl.join(', ') || '-');
                
                // Jenis Kelamin
                let jk = '-';
                if (data.jenis_kelamin === 'L') jk = 'Laki-laki';
                else if (data.jenis_kelamin === 'P') jk = 'Perempuan';
                $('#preview_jk').text(jk);
                
                // Agama (bisa null jika dari Kemdikbud)
                $('#preview_agama').text(data.agama || '-');
                
                // Sekolah
                let sekolah = data.sekolah_asal || '-';
                $('#preview_sekolah').text(sekolah);
                
                // NSM (prioritas untuk Madrasah) atau NPSN
                if (data.nsm) {
                    // Jika ada NSM (dari Kemenag - Madrasah), prioritaskan NSM
                    $('#preview_npsn_label').text('NSM');
                    $('#preview_npsn').text(data.nsm);
                } else if (data.npsn) {
                    // Jika ada NPSN (dari Kemdikbud atau Kemenag)
                    $('#preview_npsn_label').text('NPSN');
                    $('#preview_npsn').text(data.npsn);
                } else {
                    // Tidak ada keduanya
                    $('#preview_npsn_label').text('NPSN');
                    $('#preview_npsn').text('-');
                }
                
                // Alamat lengkap (bisa kosong jika dari Kemdikbud)
                let alamat = [];
                if (data.alamat) alamat.push(data.alamat);
                if (data.rt && data.rw) alamat.push('RT ' + data.rt + '/RW ' + data.rw);
                if (data.kode_pos) alamat.push('Kode Pos: ' + data.kode_pos);
                $('#preview_alamat').text(alamat.join(', ') || '-');
            }
            
            // Helper function to format date
            function formatTanggal(dateStr) {
                if (!dateStr) return '';
                
                try {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                                   'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const date = new Date(dateStr);
                    const day = date.getDate();
                    const month = months[date.getMonth()];
                    const year = date.getFullYear();
                    return `${day} ${month} ${year}`;
                } catch (e) {
                    return dateStr;
                }
            }


            // Registration form submission
            $('#registrationForm').on('submit', function(e) {
                const nomorHp = $('#reg_nomor_hp').val();
                
                // Validate phone format
                if (!nomorHp.startsWith('62')) {
                    e.preventDefault();
                    toastr.error('Nomor WhatsApp harus diawali dengan 62 (contoh: 628123456789)');
                    $('#reg_nomor_hp').focus();
                    return false;
                }
                
                if (nomorHp.length < 10) {
                    e.preventDefault();
                    toastr.error('Nomor WhatsApp tidak valid. Minimal 10 digit.');
                    $('#reg_nomor_hp').focus();
                    return false;
                }
                
                // Disable submit button
                $('#btnRegister').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
            });

            // Format nomor HP
            $('#reg_nomor_hp').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.startsWith('08')) {
                    value = '62' + value.substring(1);
                }
                $(this).val(value);
            });

            // Cancel registration
            $('#btnCancelRegister').on('click', function(e) {
                e.preventDefault();
                $('#registrationFormCard').slideUp(300);
                setTimeout(function() {
                    $('#nisnCheckCard').slideDown(300);
                    $('#cekNisnForm')[0].reset();
                    $('#resultBox').removeClass('show success error');
                }, 300);
            });
        });
    </script>
</body>
</html>
