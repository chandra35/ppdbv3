<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Registrasi - {{ $calonSiswa->nomor_registrasi }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header img {
            height: 60px;
            margin-bottom: 8px;
        }
        .header h2 {
            font-size: 14pt;
            margin: 4px 0;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        .header h3 {
            font-size: 12pt;
            margin: 3px 0;
            font-weight: 600;
            color: #34495e;
        }
        .header .tahun {
            font-size: 11pt;
            font-weight: bold;
            color: #e74c3c;
            margin: 3px 0;
        }
        .header p {
            font-size: 9pt;
            margin: 2px 0;
            color: #7f8c8d;
        }
        
        .title {
            text-align: center;
            margin: 12px 0;
            padding: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 4px;
        }
        
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .info-box {
            display: table-cell;
            width: 70%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #2c3e50;
        }
        .info-table td {
            padding: 4px 8px;
            font-size: 9.5pt;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            width: 38%;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
        }
        .info-table td:last-child {
            font-weight: bold;
            color: #e74c3c;
        }
        
        .foto-container {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: center;
        }
        .foto-box {
            border: 1px solid #2c3e50;
            padding: 5px;
            background: #f8f9fa;
            width: 90px;
            height: 120px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .foto-box img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .foto-placeholder {
            width: 100%;
            height: 100%;
            background: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            font-size: 8pt;
        }
        
        .logo-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -1;
            width: 300px;
            height: 300px;
        }
        .logo-watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .section {
            margin-bottom: 10px;
        }
        
        .section-title {
            background: linear-gradient(to right, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 10.5pt;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 9.5pt;
            line-height: 1.3;
        }
        table td:first-child {
            width: 35%;
            font-weight: 600;
            color: #2c3e50;
        }
        table td:last-child {
            color: #333;
        }
        
        .two-column {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .column:first-child {
            padding-right: 6px;
        }
        .column:last-child {
            padding-left: 6px;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #ecf0f1;
        }
        .footer-grid {
            display: table;
            width: 100%;
        }
        .footer-col {
            display: table-cell;
            width: 50%;
            font-size: 9pt;
            vertical-align: top;
        }
        .signature-box {
            text-align: center;
            margin-top: 15px;
        }
        .signature-line {
            border-top: 1px solid #34495e;
            width: 150px;
            margin: 40px auto 4px;
        }
        
        .watermark {
            position: fixed;
            bottom: 10mm;
            right: 10mm;
            font-size: 8pt;
            color: #95a5a6;
            font-style: italic;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 10px;
            background: #27ae60;
            color: white;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>
    {{-- Logo Watermark Background --}}
    @if($sekolah && $sekolah->logo)
    <div class="logo-watermark">
        <img src="{{ $sekolah->logo }}" alt="Watermark">
    </div>
    @endif

    {{-- Header --}}
    <div class="header">
        @if($sekolah && $sekolah->logo)
            <img src="{{ $sekolah->logo }}" alt="Logo">
        @endif
        <h2>{{ $sekolah->nama_sekolah ?? 'SMK' }}</h2>
        <h3>PENERIMAAN PESERTA DIDIK BARU</h3>
        <div class="tahun">TAHUN PELAJARAN {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</div>
        <p>{{ $sekolah->alamat ?? '' }} | Telp: {{ $sekolah->telepon ?? '-' }} | Email: {{ $sekolah->email ?? '-' }}</p>
    </div>

    {{-- Title --}}
    <div class="title">BUKTI REGISTRASI PENDAFTARAN</div>

    {{-- Info Registrasi + Foto --}}
    <div class="info-container">
        <div class="info-box">
            <table class="info-table">
                <tr>
                    <td>Nomor Registrasi</td>
                    <td>{{ $calonSiswa->nomor_registrasi }}</td>
                </tr>
                <tr>
                    <td>Nomor Tes</td>
                    <td>{{ $calonSiswa->nomor_tes }}</td>
                </tr>
                <tr>
                    <td>Jalur Pendaftaran</td>
                    <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Finalisasi</td>
                    <td>{{ $calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('d F Y, H:i') : '-' }} WIB</td>
                </tr>
                @if($calonSiswa->jalurPendaftaran && $calonSiswa->jalurPendaftaran->pilihan_program_aktif && $calonSiswa->pilihan_program)
                <tr>
                    <td>Pilihan Program</td>
                    <td>{{ $calonSiswa->pilihan_program }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <div class="foto-container">
            <div class="foto-box">
                @php
                    $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
                    $fotoPath = $fotoDokumen ? storage_path('app/public/' . $fotoDokumen->file_path) : null;
                @endphp
                @if($fotoPath && file_exists($fotoPath))
                    <img src="{{ $fotoPath }}" alt="Foto">
                @else
                    <div class="foto-placeholder">Foto 3x4</div>
                @endif
            </div>
            <div style="margin-top: 3px; font-size: 8pt; color: #7f8c8d;">Pas Foto</div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="two-column">
        {{-- Column 1: Data Pribadi --}}
        <div class="column">
            <div class="section">
                <div class="section-title">DATA PRIBADI</div>
                <table>
                    <tr>
                        <td>NISN</td>
                        <td>{{ $calonSiswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td><strong>{{ strtoupper($calonSiswa->nama_lengkap) }}</strong></td>
                    </tr>
                    <tr>
                        <td>NIK</td>
                        <td>{{ $calonSiswa->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($calonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td>Agama</td>
                        <td>{{ $calonSiswa->agama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah</td>
                        <td>{{ $calonSiswa->asal_sekolah ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>{{ $calonSiswa->alamat_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No. HP/WA</td>
                        <td>{{ $calonSiswa->no_hp ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Column 2: Data Orang Tua --}}
        <div class="column">
            @if($calonSiswa->ortu)
            <div class="section">
                <div class="section-title">DATA ORANG TUA / WALI</div>
                <table>
                    <tr>
                        <td>Nama Ayah</td>
                        <td>{{ $calonSiswa->ortu->nama_ayah ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pekerjaan Ayah</td>
                        <td>{{ $calonSiswa->ortu->pekerjaan_ayah ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No. HP Ayah</td>
                        <td>{{ $calonSiswa->ortu->no_hp_ayah ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nama Ibu</td>
                        <td>{{ $calonSiswa->ortu->nama_ibu ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pekerjaan Ibu</td>
                        <td>{{ $calonSiswa->ortu->pekerjaan_ibu ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>No. HP Ibu</td>
                        <td>{{ $calonSiswa->ortu->no_hp_ibu ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <p style="color: #7f8c8d; font-style: italic;">
                    <strong>Catatan:</strong> Bukti ini merupakan dokumen resmi pendaftaran PPDB. 
                    Harap dibawa saat mengikuti tahapan selanjutnya.
                </p>
            </div>
            <div class="footer-col" style="text-align: right;">
                <p>{{ ($sekolah->kota ?? '') }}, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                <div class="signature-box">
                    <p style="font-weight: 600;">Calon Peserta Didik</p>
                    <div class="signature-line"></div>
                    <p><strong>{{ $calonSiswa->nama_lengkap }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Watermark --}}
    <div class="watermark">
        Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} | <span class="badge">VALID</span>
    </div>
</body>
</html>
