<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Bukti Registrasi - {{ $sekolahSettings->nama_sekolah ?? 'PPDB' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .verify-card { max-width: 800px; margin: 0 auto; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .verify-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px 15px 0 0; text-align: center; }
        .verify-body { background: white; padding: 30px; border-radius: 0 0 15px 15px; }
        .status-badge { display: inline-block; padding: 10px 20px; border-radius: 25px; font-weight: bold; margin: 20px 0; }
        .status-verified { background: #27ae60; color: white; }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #ecf0f1; }
        .info-label { width: 40%; font-weight: 600; color: #2c3e50; }
        .info-value { width: 60%; color: #34495e; }
        .foto-box { width: 120px; height: 160px; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa; }
        .foto-box img { max-width: 100%; max-height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="verify-header">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h2>Dokumen Terverifikasi</h2>
                <p class="mb-0">Bukti Registrasi Pendaftaran PPDB</p>
            </div>
            <div class="verify-body">
                <div class="text-center mb-4">
                    <span class="status-badge status-verified">
                        <i class="fas fa-shield-check"></i> DOKUMEN ASLI & VALID
                    </span>
                    <p class="text-muted mb-0">Terverifikasi pada: {{ now()->format('d F Y, H:i') }} WIB</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-9">
                        <h5 class="mb-3"><i class="fas fa-user text-primary"></i> Informasi Pendaftar</h5>
                        <div class="info-row">
                            <div class="info-label">Nomor Registrasi:</div>
                            <div class="info-value"><strong class="text-danger">{{ $calonSiswa->nomor_registrasi }}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nomor Tes:</div>
                            <div class="info-value"><strong>{{ $calonSiswa->nomor_tes }}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nama Lengkap:</div>
                            <div class="info-value"><strong>{{ strtoupper($calonSiswa->nama_lengkap) }}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">NISN:</div>
                            <div class="info-value">{{ $calonSiswa->nisn }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jalur Pendaftaran:</div>
                            <div class="info-value">{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tanggal Finalisasi:</div>
                            <div class="info-value">{{ $calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('d F Y, H:i') : '-' }} WIB</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <span class="badge bg-success">Terverifikasi & Finalisasi</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        @php
                            $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
                            $fotoPath = $fotoDokumen ? storage_path('app/public/' . $fotoDokumen->file_path) : null;
                        @endphp
                        <div class="foto-box mx-auto">
                            @if($fotoPath && file_exists($fotoPath))
                                <img src="{{ asset('storage/' . $fotoDokumen->file_path) }}" alt="Foto">
                            @else
                                <div class="text-muted">Foto 3x4</div>
                            @endif
                        </div>
                        <small class="text-muted">Pas Foto</small>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informasi:</strong> Dokumen ini merupakan bukti registrasi resmi pendaftaran PPDB
                    <strong>{{ $sekolahSettings->nama_sekolah ?? 'Sekolah' }}</strong>
                    Tahun Pelajaran {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}.
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('ppdb.landing') }}" class="btn btn-primary">
                        <i class="fas fa-home"></i> Kembali ke Halaman PPDB
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
