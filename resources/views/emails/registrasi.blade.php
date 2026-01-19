<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran PPDB Berhasil</title>
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
        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .icon {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .credential-box {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-box .label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 3px;
        }
        .credential-box .value {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            font-family: monospace;
            letter-spacing: 1px;
        }
        .credential-box .value:last-child {
            margin-bottom: 0;
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
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box strong {
            color: #856404;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Pendaftaran Berhasil!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">
                Assalamu'alaikum <strong>{{ $calonSiswa->nama_lengkap }}</strong>,
            </p>
            
            <p>
                Selamat! Pendaftaran PPDB Anda telah <strong>berhasil</strong>. 
                Berikut adalah data akun untuk login ke sistem:
            </p>
            
            <div class="credential-box">
                <div class="label">Username (NISN)</div>
                <div class="value">{{ $username }}</div>
                <div class="label">Password</div>
                <div class="value">{{ $password }}</div>
            </div>
            
            <div class="detail-box">
                <table>
                    <tr>
                        <td>No. Registrasi</td>
                        <td>{{ $calonSiswa->nomor_registrasi }}</td>
                    </tr>
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
                        <td>Jalur</td>
                        <td>{{ $calonSiswa->jalurPendaftaran->nama }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Penting:</strong><br>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Simpan username dan password dengan baik</li>
                    <li>Jangan bagikan password kepada siapapun</li>
                    <li>Segera lengkapi data dan dokumen yang diperlukan</li>
                    <li>Ubah password setelah login pertama kali</li>
                </ul>
            </div>
            
            <p><strong>Langkah selanjutnya:</strong></p>
            <ol>
                <li>Login ke sistem PPDB menggunakan akun di atas</li>
                <li>Lengkapi data diri dan dokumen yang diminta</li>
                <li>Lakukan finalisasi setelah semua data lengkap</li>
                <li>Tunggu proses verifikasi dari panitia</li>
            </ol>
            
            <center>
                <a href="{{ $loginUrl }}" class="btn">Login Sekarang</a>
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
