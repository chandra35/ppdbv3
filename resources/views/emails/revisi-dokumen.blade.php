<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Revisi Dokumen</title>
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
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #333;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
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
        .alert-box {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert-box .dokumen-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .catatan-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .catatan-box .label {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }
        .catatan-box .catatan-text {
            font-size: 16px;
            color: #333;
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
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
        .steps-box {
            background-color: #e7f5ff;
            border: 1px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .steps-box h4 {
            margin: 0 0 15px 0;
            color: #0056b3;
        }
        .steps-box ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps-box li {
            margin-bottom: 8px;
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
            <div class="icon">⚠️</div>
            <h1>Permintaan Revisi Dokumen</h1>
            <p style="margin: 10px 0 0 0;">{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">
                Assalamu'alaikum <strong>{{ $calonSiswa->nama_lengkap }}</strong>,
            </p>
            
            <p>
                Ada permintaan <strong>revisi dokumen</strong> dari panitia PPDB. 
                Silakan perbaiki dokumen berikut:
            </p>
            
            <div class="alert-box">
                <div class="dokumen-name">📄 {{ ucwords(str_replace('_', ' ', $dokumen->jenis_dokumen)) }}</div>
                <small>Dokumen ini perlu diperbaiki/diupload ulang</small>
            </div>
            
            <div class="catatan-box">
                <div class="label">📝 Catatan dari Panitia:</div>
                <div class="catatan-text">{{ $catatan }}</div>
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
                    <tr>
                        <td>Waktu Permintaan</td>
                        <td>{{ now()->format('d F Y, H:i') }} WIB</td>
                    </tr>
                </table>
            </div>
            
            <div class="steps-box">
                <h4>📋 Langkah-langkah:</h4>
                <ol>
                    <li>Login ke sistem PPDB menggunakan akun Anda</li>
                    <li>Buka menu <strong>Dokumen</strong></li>
                    <li>Cari dokumen yang diminta untuk direvisi</li>
                    <li>Upload dokumen yang sudah diperbaiki</li>
                    <li>Pastikan file jelas dan sesuai ketentuan</li>
                </ol>
            </div>
            
            <center>
                <a href="{{ $loginUrl }}" class="btn">Login & Perbaiki Dokumen</a>
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
