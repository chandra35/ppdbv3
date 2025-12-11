<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi - PPDB {{ config('app.name') }}</title>
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
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
            padding: 2rem;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        
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
        
        .card-header h1 {
            font-size: 1.75rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .card-header p {
            color: #666;
        }
        
        .nisn-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-label .required {
            color: #e53e3e;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control:read-only {
            background: #f7fafc;
            color: #666;
        }
        
        .form-control.is-invalid {
            border-color: #e53e3e;
        }
        
        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        .select2-container--default .select2-selection--single {
            height: 46px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
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
        
        .btn-secondary {
            background: #e2e8f0;
            color: #666;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .form-actions .btn {
            flex: 1;
        }
        
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
        
        .emis-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        
        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Formulir Registrasi</h1>
                <p>Lengkapi data untuk membuat akun PPDB</p>
                <div class="nisn-badge">NISN: {{ $nisn }}</div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pendaftar.register.post') }}" method="POST" id="registerForm">
                @csrf
                <input type="hidden" name="nisn" value="{{ $nisn }}">
                <input type="hidden" name="nisn_valid" id="nisn_valid" value="0">

                <h3 class="section-title"><i class="fas fa-user"></i> Data Pribadi</h3>
                
                <div class="form-group">
                    <label class="form-label">
                        Nama Lengkap <span class="required">*</span>
                        <span class="emis-badge" id="emisBadge" style="display: none;">
                            <i class="fas fa-check"></i> Data EMIS
                        </span>
                    </label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                           value="{{ old('nama_lengkap') }}" required>
                    @error('nama_lengkap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control" 
                               value="{{ old('tempat_lahir') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" 
                               value="{{ old('tanggal_lahir') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asal Sekolah</label>
                        <input type="text" name="nama_sekolah_asal" id="nama_sekolah_asal" class="form-control" 
                               value="{{ old('nama_sekolah_asal') }}">
                    </div>
                </div>

                <h3 class="section-title"><i class="fas fa-phone"></i> Kontak</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" placeholder="email@contoh.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP (WhatsApp) <span class="required">*</span></label>
                        <input type="text" name="nomor_hp" class="form-control @error('nomor_hp') is-invalid @enderror" 
                               value="{{ old('nomor_hp') }}" placeholder="08xxxxxxxxxx" required>
                        @error('nomor_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP Orang Tua <span class="required">*</span></label>
                    <input type="text" name="nomor_hp_ortu" class="form-control @error('nomor_hp_ortu') is-invalid @enderror" 
                           value="{{ old('nomor_hp_ortu') }}" placeholder="08xxxxxxxxxx" required>
                    <small style="color: #666;">Untuk menerima informasi pendaftaran</small>
                    @error('nomor_hp_ortu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <h3 class="section-title"><i class="fas fa-clipboard-list"></i> Pilihan Pendaftaran</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jalur Pendaftaran <span class="required">*</span></label>
                        <select name="jalur_pendaftaran_id" class="form-control @error('jalur_pendaftaran_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Jalur --</option>
                            @foreach($jalurPendaftaran as $jalur)
                                <option value="{{ $jalur->id }}" {{ old('jalur_pendaftaran_id') == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jalur_pendaftaran_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gelombang <span class="required">*</span></label>
                        <select name="gelombang_pendaftaran_id" class="form-control @error('gelombang_pendaftaran_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Gelombang --</option>
                            @foreach($gelombangAktif as $gelombang)
                                <option value="{{ $gelombang->id }}" {{ old('gelombang_pendaftaran_id') == $gelombang->id ? 'selected' : '' }}>
                                    {{ $gelombang->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('gelombang_pendaftaran_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Setelah registrasi, Anda akan mendapatkan akun untuk login dan melengkapi data pendaftaran.
                </div>

                <div class="form-actions">
                    <a href="{{ route('pendaftar.landing') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </div>
            </form>
        </div>

        <a href="{{ route('pendaftar.landing') }}" class="back-link">
            <i class="fas fa-home"></i> Kembali ke Halaman Utama
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Check for EMIS data in session storage
            const emisData = sessionStorage.getItem('emisData');
            const nisnValid = sessionStorage.getItem('nisnValid');
            
            if (emisData) {
                try {
                    const data = JSON.parse(emisData);
                    
                    if (data.nama) {
                        $('#nama_lengkap').val(data.nama);
                        $('#emisBadge').show();
                    }
                    if (data.tempat_lahir) {
                        $('#tempat_lahir').val(data.tempat_lahir);
                    }
                    if (data.tanggal_lahir) {
                        $('#tanggal_lahir').val(data.tanggal_lahir);
                    }
                    if (data.jenis_kelamin) {
                        $('#jenis_kelamin').val(data.jenis_kelamin);
                    }
                    if (data.sekolah_asal) {
                        $('#nama_sekolah_asal').val(data.sekolah_asal);
                    }
                    
                    // Clear session storage after use
                    sessionStorage.removeItem('emisData');
                } catch (e) {
                    console.error('Error parsing EMIS data', e);
                }
            }
            
            if (nisnValid === 'true') {
                $('#nisn_valid').val('1');
            }
            sessionStorage.removeItem('nisnValid');

            // Phone number validation
            $('input[name="nomor_hp"], input[name="nomor_hp_ortu"]').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
</body>
</html>
