<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Hasil Seleksi PPDB</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header-diterima {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header-ditolak {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .result-box-diterima {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        .result-box-ditolak {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        .result-box .result-text {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .result-box .result-desc {
            font-size: 14px;
            opacity: 0.9;
        }
        .detail-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .detail-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail-box td {
            padding: 8px 0;
            vertical-align: top;
        }
        .detail-box td:first-child {
            color: #666;
            width: 40%;
        }
        .detail-box td:last-child {
            font-weight: 500;
        }
        .keterangan-box {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .keterangan-box .label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }
        .success-steps {
            background-color: #d4edda;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-steps h4 {
            margin: 0 0 15px 0;
            color: #155724;
        }
        .success-steps ol {
            margin: 0;
            padding-left: 20px;
            color: #155724;
        }
        .success-steps li {
            margin-bottom: 8px;
        }
        .info-box {
            background-color: #cce5ff;
            border: 1px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h4 {
            margin: 0 0 15px 0;
            color: #004085;
        }
        .info-box p {
            margin: 0;
            color: #004085;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .congrats-animation {
            text-align: center;
            font-size: 30px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header header-{{ $hasil }}">
            <div class="icon">
                @if($hasil === 'diterima')
                    🎉
                @else
                    📋
                @endif
            </div>
            <h1>
                @if($hasil === 'diterima')
                    SELAMAT!
                @else
                    Pengumuman Hasil Seleksi
                @endif
            </h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">
                Assalamu'alaikum <strong>{{ $calonSiswa->nama_lengkap }}</strong>,
            </p>
            
            @if($hasil === 'diterima')
                <div class="congrats-animation">
                    🎊 🎉 🎊
                </div>
                
                <p>
                    Dengan penuh rasa syukur, kami sampaikan bahwa Anda telah <strong>DITERIMA</strong> 
                    sebagai peserta didik baru di <strong>{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong>.
                </p>
                
                <div class="result-box-diterima">
                    <div class="result-text">✅ DITERIMA</div>
                    <div class="result-desc">Selamat bergabung di keluarga besar {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}!</div>
                </div>
            @else
                <p>
                    Dengan hormat, kami sampaikan hasil seleksi PPDB 
                    <strong>{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong>.
                </p>
                
                <div class="result-box-ditolak">
                    <div class="result-text">Tidak Lolos Seleksi</div>
                    <div class="result-desc">Mohon maaf, Anda belum dapat diterima pada periode ini</div>
                </div>
            @endif
            
            <div class="detail-box">
                <table>
                    <tr>
                        <td>No. Registrasi</td>
                        <td>{{ $calonSiswa->nomor_registrasi }}</td>
                    </tr>
                    @if($calonSiswa->nomor_tes)
                    <tr>
                        <td>No. Tes</td>
                        <td>{{ $calonSiswa->nomor_tes }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>{{ $calonSiswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>{{ $calonSiswa->nisn }}</td>
                    </tr>
                    @if($calonSiswa->jalurPendaftaran)
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>{{ $calonSiswa->jalurPendaftaran->nama }}</td>
                    </tr>
                    @endif
                    @if($calonSiswa->gelombangPendaftaran)
                    <tr>
                        <td>Gelombang</td>
                        <td>{{ $calonSiswa->gelombangPendaftaran->nama }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            @if($keterangan)
            <div class="keterangan-box">
                <div class="label">📝 Keterangan:</div>
                <p>{{ $keterangan }}</p>
            </div>
            @endif
            
            @if($hasil === 'diterima')
                <div class="success-steps">
                    <h4>📋 Langkah Selanjutnya:</h4>
                    <ol>
                        <li>Login ke sistem PPDB untuk melihat informasi daftar ulang</li>
                        <li>Siapkan dokumen asli untuk verifikasi</li>
                        <li>Lakukan daftar ulang sesuai jadwal yang ditentukan</li>
                        <li>Simpan bukti pendaftaran dengan baik</li>
                    </ol>
                </div>
            @else
                <div class="info-box">
                    <h4>ℹ️ Informasi:</h4>
                    <p>
                        Jangan berkecil hati. Masih banyak kesempatan di luar sana. 
                        Tetap semangat dan terus berusaha!
                    </p>
                </div>
            @endif
            
            <center>
                <a href="{{ $loginUrl }}" class="btn">
                    @if($hasil === 'diterima')
                        Lihat Info Daftar Ulang
                    @else
                        Login ke Akun
                    @endif
                </a>
            </center>
        </div>
        
        <div class="footer">
            <p>
                Email ini dikirim otomatis oleh sistem PPDB<br>
                <strong>{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong>
            </p>
            <p>
                Jika ada pertanyaan, silakan hubungi panitia PPDB<br>
                @if($settings->telepon ?? null)
                    📞 {{ $settings->telepon }}
                @endif
            </p>
            <p style="margin-top: 15px; color: #999;">
                &copy; {{ date('Y') }} PPDB {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}
            </p>
        </div>
    </div>
</body>
</html>
