<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - {{ $calonSiswa->nomor_registrasi }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 10px;
        }
        
        .header h3 {
            font-size: 14px;
            color: #666;
        }
        
        .title {
            text-align: center;
            margin: 20px 0;
        }
        
        .title h2 {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 2px solid #333;
            display: inline-block;
            padding: 8px 20px;
        }
        
        .registration-number {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        
        .registration-number label {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .registration-number strong {
            font-size: 24px;
            letter-spacing: 3px;
            color: #333;
        }
        
        .info-section {
            margin: 20px 0;
        }
        
        .info-section h4 {
            font-size: 12px;
            background: #667eea;
            color: white;
            padding: 8px 15px;
            margin-bottom: 10px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 8px 15px;
            border: 1px solid #ddd;
        }
        
        .info-table td:first-child {
            width: 35%;
            background: #f8f9fa;
            font-weight: 500;
        }
        
        .status-box {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #48bb78;
            background: #f0fff4;
            text-align: center;
        }
        
        .status-box.pending {
            border-color: #ed8936;
            background: #fffaf0;
        }
        
        .status-box label {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .status-box strong {
            font-size: 16px;
            text-transform: uppercase;
        }
        
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .footer-left {
            font-size: 10px;
            color: #666;
        }
        
        .footer-right {
            text-align: center;
        }
        
        .footer-right p {
            margin-bottom: 60px;
        }
        
        .qr-placeholder {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #999;
        }
        
        .notes {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            font-size: 11px;
        }
        
        .notes h5 {
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .notes ul {
            padding-left: 20px;
            margin: 0;
        }
        
        .notes li {
            margin-bottom: 5px;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }
        
        .print-btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'MADRASAH') }}</h1>
            <h2>PENERIMAAN PESERTA DIDIK BARU (PPDB)</h2>
            <h3>Tahun Pelajaran {{ $calonSiswa->tahunPelajaran->nama ?? date('Y') . '/' . (date('Y')+1) }}</h3>
        </div>

        <div class="title">
            <h2>Bukti Pendaftaran</h2>
        </div>

        <div class="registration-number">
            <label>NOMOR REGISTRASI</label>
            <strong>{{ $calonSiswa->nomor_registrasi }}</strong>
        </div>

        <div class="info-section">
            <h4><i class="fas fa-user"></i> DATA CALON PESERTA DIDIK</h4>
            <table class="info-table">
                <tr>
                    <td>NISN</td>
                    <td>{{ $calonSiswa->nisn }}</td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td><strong>{{ $calonSiswa->nama_lengkap }}</strong></td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>{{ $calonSiswa->tempat_lahir }}, {{ $calonSiswa->tanggal_lahir?->format('d F Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>{{ $calonSiswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td>Asal Sekolah</td>
                    <td>{{ $calonSiswa->nama_sekolah_asal ?? '-' }}</td>
                </tr>
                <tr>
                    <td>No. HP</td>
                    <td>{{ $calonSiswa->nomor_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $calonSiswa->email ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <h4><i class="fas fa-users"></i> DATA ORANG TUA</h4>
            <table class="info-table">
                <tr>
                    <td>Nama Ayah</td>
                    <td>{{ $calonSiswa->ortu->nama_ayah ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nama Ibu</td>
                    <td>{{ $calonSiswa->ortu->nama_ibu ?? '-' }}</td>
                </tr>
                <tr>
                    <td>No. HP Orang Tua</td>
                    <td>{{ $calonSiswa->ortu->nomor_hp_ortu ?? $calonSiswa->ortu->nomor_hp_ayah ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <h4><i class="fas fa-clipboard-list"></i> DATA PENDAFTARAN</h4>
            <table class="info-table">
                <tr>
                    <td>Jalur Pendaftaran</td>
                    <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Gelombang</td>
                    <td>{{ $calonSiswa->gelombangPendaftaran->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Pendaftaran</td>
                    <td>{{ $calonSiswa->created_at->format('d F Y, H:i') }} WIB</td>
                </tr>
            </table>
        </div>

        <div class="status-box {{ $calonSiswa->status_verifikasi === 'verified' ? '' : 'pending' }}">
            <label>STATUS PENDAFTARAN</label>
            <strong>{{ strtoupper($calonSiswa->status_verifikasi) }}</strong>
        </div>

        <div class="notes">
            <h5><i class="fas fa-exclamation-triangle"></i> CATATAN PENTING:</h5>
            <ul>
                <li>Simpan bukti pendaftaran ini dengan baik.</li>
                <li>Bukti pendaftaran ini wajib dibawa saat daftar ulang.</li>
                <li>Pastikan semua data yang tercantum sudah benar.</li>
                <li>Pantau status pendaftaran melalui website atau dashboard PPDB.</li>
                <li>Untuk informasi lebih lanjut, hubungi panitia PPDB.</li>
            </ul>
        </div>

        <div class="footer">
            <div class="footer-left">
                <p>Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
                <p>Dokumen ini digenerate secara otomatis oleh sistem PPDB.</p>
            </div>
            <div class="footer-right">
                <p>{{ now()->format('d F Y') }}</p>
                <p><strong>Panitia PPDB</strong></p>
            </div>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak / Download PDF
    </button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
