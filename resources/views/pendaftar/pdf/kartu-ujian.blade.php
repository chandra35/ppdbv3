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
            font-family: Arial, sans-serif;
            width: 148mm;
            height: 105mm;
            padding: 0;
            margin: 0;
        }
        .card {
            width: 148mm;
            height: 105mm;
            border: 2px solid #333;
            padding: 8mm;
            position: relative;
            background: white;
        }
        .card-header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 4mm;
            margin-bottom: 4mm;
        }
        .card-header img {
            height: 15mm;
            margin-bottom: 2mm;
        }
        .card-header h2 {
            font-size: 14pt;
            margin: 1mm 0;
            color: #007bff;
        }
        .card-header h3 {
            font-size: 11pt;
            margin: 1mm 0;
            font-weight: normal;
        }
        .card-header .tahun {
            font-size: 9pt;
            font-weight: bold;
            background: #007bff;
            color: white;
            padding: 1mm 4mm;
            display: inline-block;
            margin-top: 1mm;
        }
        
        .card-body {
            display: table;
            width: 100%;
        }
        .photo-section {
            display: table-cell;
            width: 30mm;
            vertical-align: top;
            padding-right: 4mm;
        }
        .photo-box {
            width: 30mm;
            height: 40mm;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            overflow: hidden;
        }
        .photo-box img {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .photo-box .no-photo {
            font-size: 8pt;
            color: #999;
            text-align: center;
        }
        
        .info-section {
            display: table-cell;
            vertical-align: top;
        }
        .info-row {
            margin-bottom: 2mm;
            font-size: 9pt;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 35mm;
        }
        .info-value {
            color: #000;
            font-weight: bold;
        }
        .nomor-tes {
            background: #ffc107;
            color: #000;
            padding: 2mm 3mm;
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 2mm 0;
            border-radius: 2mm;
            letter-spacing: 1px;
        }
        .password-box {
            background: #dc3545;
            color: white;
            padding: 2mm 3mm;
            font-size: 10pt;
            text-align: center;
            margin: 2mm 0;
            border-radius: 2mm;
        }
        .password-label {
            font-size: 7pt;
            font-weight: normal;
        }
        .password-value {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .card-footer {
            position: absolute;
            bottom: 8mm;
            left: 8mm;
            right: 8mm;
            border-top: 1px solid #dee2e6;
            padding-top: 2mm;
            font-size: 7pt;
            color: #666;
        }
        .signature-area {
            float: right;
            text-align: center;
            width: 35mm;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 8mm;
            padding-top: 1mm;
        }
        
        .instruction {
            background: #e7f3ff;
            border: 1px solid #007bff;
            padding: 2mm;
            margin-top: 2mm;
            font-size: 7pt;
            color: #004085;
        }
        .instruction strong {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="card">
        {{-- Header --}}
        <div class="card-header">
            @if($sekolah && $sekolah->logo)
                <img src="{{ $sekolah->logo }}" alt="Logo">
            @endif
            <h2>{{ $sekolah->nama_sekolah ?? 'SMK' }}</h2>
            <h3>KARTU PESERTA UJIAN</h3>
            <div class="tahun">
                PPDB {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}
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
                            <i class="fas fa-user" style="font-size: 20pt; color: #ccc;"></i>
                            <p>Foto 3x4</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="info-section">
                {{-- Nomor Tes --}}
                <div class="nomor-tes">
                    {{ $calonSiswa->nomor_tes }}
                </div>

                {{-- Data Peserta --}}
                <div class="info-row">
                    <span class="info-label">NISN</span>
                    <span class="info-value">: {{ $calonSiswa->nisn }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">: {{ strtoupper($calonSiswa->nama_lengkap) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TTL</span>
                    <span class="info-value">: {{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($calonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jalur</span>
                    <span class="info-value">: {{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</span>
                </div>
                @if($calonSiswa->pilihan_program)
                <div class="info-row">
                    <span class="info-label">Program</span>
                    <span class="info-value">: {{ $calonSiswa->pilihan_program }}</span>
                </div>
                @endif

                {{-- Password --}}
                <div class="password-box">
                    <div class="password-label">PASSWORD LOGIN</div>
                    <div class="password-value">{{ $password ?? '********' }}</div>
                </div>

                {{-- Instruction --}}
                <div class="instruction">
                    <strong>PENTING:</strong> Bawa kartu ini saat ujian. Password untuk login CBT/Sistem Ujian.
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer">
            <div style="float: left;">
                Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            </div>
            <div class="signature-area">
                <div>Panitia PPDB</div>
                <div class="signature-line">
                    ( ................... )
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</body>
</html>
