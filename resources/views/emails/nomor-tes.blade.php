<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomor Tes PPDB</title>
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
        .nomor-tes-box {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .nomor-tes-box .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .nomor-tes-box .nomor {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
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
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-box p {
            margin: 0;
            color: #856404;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            opacity: 0.9;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🎉</div>
            <h1>Dokumen Terverifikasi!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">PPDB {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">
                Assalamu'alaikum Wr. Wb.<br>
                <strong>{{ $calonSiswa->nama_lengkap }}</strong>,
            </p>
            
            <p>
                Selamat! Dokumen pendaftaran PPDB Anda telah diverifikasi lengkap oleh panitia. 
                Anda telah mendapatkan Nomor Tes untuk mengikuti seleksi.
            </p>
            
            <div class="nomor-tes-box">
                <div class="label">NOMOR TES ANDA</div>
                <div class="nomor">{{ $nomorTes }}</div>
            </div>
            
            <div class="detail-box">
                <table>
                    <tr>
                        <td>No. Registrasi</td>
                        <td>{{ $calonSiswa->nomor_registrasi }}</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>{{ $calonSiswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                    </tr>
                    @if($calonSiswa->gelombangPendaftaran)
                    <tr>
                        <td>Gelombang</td>
                        <td>{{ $calonSiswa->gelombangPendaftaran->nama }}</td>
                    </tr>
                    @endif
                    @if($calonSiswa->pilihan_program)
                    <tr>
                        <td>Program</td>
                        <td>{{ $calonSiswa->pilihan_program }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <div class="info-box">
                <p>
                    <strong>📋 Catatan Penting:</strong><br>
                    Simpan nomor tes ini dengan baik. Silakan login ke akun pendaftar Anda untuk mencetak Kartu Ujian.
                </p>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="button">
                    Login ke Akun Pendaftar
                </a>
            </div>
            
            <p style="margin-top: 30px;">
                Terima kasih atas kepercayaan Anda memilih {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}.
            </p>
            
            <p>
                Wassalamu'alaikum Wr. Wb.<br>
                <strong>Panitia PPDB {{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong>
            </p>
        </div>
        
        <div class="footer">
            <p><strong>{{ $settings->nama_sekolah ?? 'MAN 1 Metro' }}</strong></p>
            <p>{{ $settings->alamat_sekolah ?? '' }}</p>
            <p>Email: {{ config('mail.from.address') }}</p>
            <hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">
            <p style="font-size: 11px; color: #999;">
                Email ini dikirim secara otomatis oleh sistem PPDB. Mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>
