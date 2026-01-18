<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Belum Finalisasi - {{ config('app.name', 'PPDB') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); 
            min-height: 100vh; 
            padding: 50px 0; 
        }
        .verify-card { 
            max-width: 700px; 
            margin: 0 auto; 
            border-radius: 15px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
            overflow: hidden;
        }
        .verify-header { 
            background: linear-gradient(135deg, #f5af19 0%, #f12711 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .verify-body { 
            background: white; 
            padding: 30px; 
        }
        .status-badge { 
            display: inline-block; 
            padding: 12px 25px; 
            border-radius: 25px; 
            font-weight: bold; 
            margin: 15px 0; 
        }
        .status-pending { 
            background: #f39c12; 
            color: white; 
        }
        .info-card {
            background: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 15px 20px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
        }
        .step-list {
            list-style: none;
            padding: 0;
            counter-reset: step-counter;
        }
        .step-list li {
            position: relative;
            padding: 15px 15px 15px 60px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            counter-increment: step-counter;
        }
        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .data-preview {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .data-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }
        .data-row:last-child {
            border-bottom: none;
        }
        .data-label {
            width: 40%;
            font-weight: 600;
            color: #495057;
        }
        .data-value {
            width: 60%;
            color: #212529;
        }
        .contact-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .contact-box a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="verify-header">
                <i class="fas fa-clock fa-3x mb-3"></i>
                <h2 class="mb-2">Dokumen Belum Valid</h2>
                <p class="mb-0">Pendaftar Belum Melakukan Finalisasi</p>
            </div>
            
            <div class="verify-body">
                <div class="text-center mb-4">
                    <span class="status-badge status-pending">
                        <i class="fas fa-hourglass-half me-2"></i> MENUNGGU FINALISASI
                    </span>
                </div>

                <div class="info-card">
                    <h6 class="mb-2"><i class="fas fa-info-circle text-warning"></i> Apa Artinya?</h6>
                    <p class="mb-0">
                        Dokumen ini <strong>belum dapat diverifikasi</strong> karena pendaftar belum menyelesaikan proses finalisasi pendaftaran. 
                        Kartu registrasi hanya valid setelah pendaftar melakukan finalisasi di dashboard PPDB.
                    </p>
                </div>

                {{-- Data Preview --}}
                <div class="data-preview">
                    <h6 class="mb-3"><i class="fas fa-user text-primary"></i> Data Pendaftar (Preview)</h6>
                    <div class="data-row">
                        <div class="data-label">Nama Lengkap:</div>
                        <div class="data-value">{{ strtoupper($calonSiswa->nama_lengkap) }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">NISN:</div>
                        <div class="data-value">{{ $calonSiswa->nisn }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Nomor Registrasi:</div>
                        <div class="data-value">{{ $calonSiswa->nomor_registrasi ?? '-' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Jalur Pendaftaran:</div>
                        <div class="data-value">{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Tanggal Daftar:</div>
                        <div class="data-value">{{ $calonSiswa->tanggal_registrasi ? $calonSiswa->tanggal_registrasi->format('d F Y') : '-' }}</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Status Verifikasi:</div>
                        <div class="data-value">
                            @if($calonSiswa->status_verifikasi == 'verified')
                                <span class="badge bg-success">Terverifikasi</span>
                            @elseif($calonSiswa->status_verifikasi == 'rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                            @endif
                        </div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Status Finalisasi:</div>
                        <div class="data-value">
                            <span class="badge bg-secondary">Belum Finalisasi</span>
                        </div>
                    </div>
                </div>

                {{-- Langkah yang harus dilakukan --}}
                <h6 class="mb-3"><i class="fas fa-tasks text-success"></i> Langkah yang Harus Dilakukan Pendaftar:</h6>
                <ul class="step-list">
                    <li>
                        <strong>Lengkapi Data Diri</strong><br>
                        <small class="text-muted">Pastikan semua data pribadi sudah terisi dengan benar</small>
                    </li>
                    <li>
                        <strong>Lengkapi Data Orang Tua/Wali</strong><br>
                        <small class="text-muted">Isi informasi ayah, ibu, atau wali yang bertanggung jawab</small>
                    </li>
                    <li>
                        <strong>Upload Dokumen Wajib</strong><br>
                        <small class="text-muted">Upload Kartu Keluarga, Foto, dan dokumen lain yang diminta</small>
                    </li>
                    <li>
                        <strong>Klik Tombol "Finalisasi"</strong><br>
                        <small class="text-muted">Setelah semua lengkap, tekan tombol finalisasi di dashboard</small>
                    </li>
                </ul>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Jika Anda adalah panitia/admin dan menerima kartu ini dari pendaftar, 
                    mohon informasikan kepada pendaftar untuk segera menyelesaikan proses finalisasi melalui dashboard PPDB.
                </div>

                {{-- Contact Info --}}
                <div class="contact-box">
                    <h6 class="mb-2"><i class="fas fa-headset"></i> Butuh Bantuan?</h6>
                    <p class="mb-0 small">
                        Hubungi panitia PPDB untuk informasi lebih lanjut atau kunjungi 
                        <a href="{{ route('ppdb.landing') }}">halaman PPDB</a>
                    </p>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('ppdb.landing') }}" class="btn btn-primary">
                        <i class="fas fa-home"></i> Kembali ke Halaman PPDB
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-4">
            <small class="text-white">
                <i class="fas fa-shield-alt"></i> Sistem Verifikasi PPDB Online
            </small>
        </div>
    </div>
</body>
</html>
