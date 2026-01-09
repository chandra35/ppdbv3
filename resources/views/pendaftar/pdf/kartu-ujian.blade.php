<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Ujian - {{ $calonSiswa->nomor_tes }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: 148mm 105mm landscape;
            margin: 0;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            width: 148mm;
            height: 105mm;
            padding: 0;
            margin: 0;
            background: #f5f5f5;
        }
        .card {
            width: 148mm;
            height: 105mm;
            background: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Header dengan gradient */
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4mm 6mm;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 20mm;
            vertical-align: middle;
        }
        .header-logo img {
            width: 18mm;
            height: 18mm;
            object-fit: contain;
            background: white;
            border-radius: 2mm;
            padding: 1mm;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 3mm;
        }
        .header-text h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1mm;
            letter-spacing: 0.5px;
        }
        .header-text h3 {
            font-size: 10pt;
            font-weight: normal;
            opacity: 0.95;
        }
        .tahun-badge {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30mm;
        }
        .tahun-badge span {
            background: rgba(255,255,255,0.25);
            padding: 2mm 3mm;
            border-radius: 2mm;
            font-size: 9pt;
            font-weight: bold;
            display: inline-block;
        }
        
        /* Body */
        .card-body {
            padding: 4mm 6mm;
            display: table;
            width: 100%;
        }
        
        /* Foto Section */
        .photo-section {
            display: table-cell;
            width: 32mm;
            vertical-align: top;
            padding-right: 4mm;
        }
        .photo-box {
            width: 28mm;
            height: 38mm;
            border: 2px solid #e0e0e0;
            border-radius: 2mm;
            overflow: hidden;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .no-photo {
            text-align: center;
            color: #bbb;
            font-size: 8pt;
        }
        
        /* Info Section */
        .info-section {
            display: table-cell;
            vertical-align: top;
        }
        
        /* Nomor Tes Badge */
        .nomor-tes-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2mm 0;
            text-align: center;
            border-radius: 2mm;
            margin-bottom: 3mm;
        }
        .nomor-tes-label {
            font-size: 7pt;
            opacity: 0.9;
            margin-bottom: 1mm;
        }
        .nomor-tes-value {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        /* Info Table */
        .info-table {
            width: 100%;
            margin-bottom: 3mm;
        }
        .info-table tr {
            border-bottom: 1px solid #f0f0f0;
        }
        .info-table td {
            padding: 1.5mm 0;
            font-size: 8pt;
            line-height: 1.3;
        }
        .info-table td:first-child {
            width: 25mm;
            color: #666;
            font-weight: 600;
        }
        .info-table td:nth-child(2) {
            width: 3mm;
            color: #666;
        }
        .info-table td:last-child {
            color: #000;
            font-weight: 600;
        }
        
        /* Password Box */
        .password-box {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            padding: 2.5mm;
            border-radius: 2mm;
            text-align: center;
            margin-bottom: 2mm;
        }
        .password-label {
            font-size: 7pt;
            color: #333;
            font-weight: 600;
            margin-bottom: 1mm;
        }
        .password-value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 3px;
        }
        
        /* Footer */
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            padding: 2mm 6mm;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #e0e0e0;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .footer-note {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="card">
        {{-- Header --}}
        <div class="card-header">
            <div class="header-logo">
                @if($sekolah && $sekolah->logo)
                    <img src="{{ $sekolah->logo }}" alt="Logo">
                @endif
            </div>
            <div class="header-text">
                <h2>{{ strtoupper($sekolah->nama_sekolah ?? 'SMK') }}</h2>
                <h3>Kartu Peserta Ujian PPDB</h3>
            </div>
            <div class="tahun-badge">
                <span>{{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</span>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">
            {{-- Photo --}}
            <div class="photo-section">
                <div class="photo-box">
                    @php
                        $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
                        $fotoPath = $fotoDokumen ? storage_path('app/public/' . $fotoDokumen->file_path) : null;
                    @endphp
                    @if($fotoPath && file_exists($fotoPath))
                        <img src="{{ $fotoPath }}" alt="Foto">
                    @else
                        <div class="no-photo">
                            <div style="font-size: 24pt; margin-bottom: 2mm;">👤</div>
                            <div>Pas Foto</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="info-section">
                {{-- Nomor Tes --}}
                <div class="nomor-tes-box">
                    <div class="nomor-tes-label">NOMOR TES</div>
                    <div class="nomor-tes-value">{{ $calonSiswa->nomor_tes }}</div>
                </div>

                {{-- Data Peserta --}}
                <table class="info-table">
                    <tr>
                        <td>NISN</td>
                        <td>:</td>
                        <td>{{ $calonSiswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>:</td>
                        <td>{{ strtoupper($calonSiswa->nama_lengkap) }}</td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>:</td>
                        <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($calonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>:</td>
                        <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                    </tr>
                    @if($calonSiswa->pilihan_program)
                    <tr>
                        <td>Program Keahlian</td>
                        <td>:</td>
                        <td>{{ $calonSiswa->pilihan_program }}</td>
                    </tr>
                    @endif
                </table>

                {{-- Password --}}
                <div class="password-box">
                    <div class="password-label">PASSWORD LOGIN SISTEM</div>
                    <div class="password-value">{{ $password ?? '********' }}</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <div class="footer-left">
                <strong>Penting:</strong> Bawa kartu ini saat ujian
            </div>
            <div class="footer-right">
                <span class="footer-note">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</body>
</html>
