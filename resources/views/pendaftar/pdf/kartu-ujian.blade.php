<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Tes - {{ $calonSiswa->nomor_tes }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm 5mm 5mm 5mm;
        }
        @media print {
            /* Hilangkan header/footer browser */
            @page {
                margin: 5mm 5mm 5mm 5mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .card-wrapper {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                text-align: left !important;
                max-width: none !important;
                width: auto !important;
                border-radius: 0 !important;
                background: transparent !important;
            }
            .card {
                margin: 0 !important;
                margin-left: 0 !important;
                margin-right: auto !important;
            }
            .cut-guide {
                margin-left: 0 !important;
                margin-right: auto !important;
                text-align: left !important;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        /* Action Buttons */
        .action-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 25px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-print {
            background: #28a745;
            color: #fff;
        }
        .btn-print:hover {
            background: #218838;
        }
        .btn-download {
            background: #007bff;
            color: #fff;
        }
        .btn-download:hover {
            background: #0056b3;
        }
        .btn-back {
            background: #6c757d;
            color: #fff;
        }
        .btn-back:hover {
            background: #545b62;
        }
        
        /* Card Container */
        .card-wrapper {
            width: 100%;
            text-align: center;
            padding-top: 20px;
            background: #fff;
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Card */
        .card {
            width: 400px;
            height: 250px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #999;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        /* Watermark Logo */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            opacity: 0.12;
            z-index: 0;
        }
        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Header */
        .card-header {
            border-bottom: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
            position: relative;
            z-index: 1;
            background: #fff;
        }
        .card-header table {
            width: 100%;
        }
        .school-name {
            color: #333;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .card-type {
            color: #666;
            font-size: 9px;
            border: 1px solid #999;
            padding: 2px 6px;
            border-radius: 3px;
            white-space: nowrap;
        }
        
        /* Body */
        .card-body {
            padding: 10px 12px;
            position: relative;
            z-index: 1;
        }
        .card-body table {
            width: 100%;
        }
        
        /* Photo */
        .photo-cell {
            width: 80px;
            vertical-align: top;
            padding-right: 10px;
        }
        .photo-box {
            width: 75px;
            height: 100px;
            border: 1px solid #999;
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
        }
        .photo-box img {
            width: 75px;
            height: 100px;
            object-fit: cover;
        }
        .no-photo {
            color: #999;
            font-size: 10px;
            text-align: center;
            padding-top: 35px;
        }
        
        /* Info */
        .info-cell {
            vertical-align: top;
        }
        
        /* Nomor Tes */
        .nomor-tes-box {
            border: 1px solid #999;
            border-radius: 4px;
            padding: 5px;
            text-align: center;
            margin-bottom: 8px;
            background: #fff;
        }
        .nomor-tes-label {
            color: #666;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nomor-tes-value {
            color: #333;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .data-table td {
            padding: 2px 0;
            font-size: 9px;
            color: #333;
            vertical-align: top;
            text-align: left;
        }
        .data-label {
            width: 40px;
            color: #666;
        }
        .data-separator {
            width: 8px;
            color: #666;
        }
        .data-value {
            font-weight: bold;
            color: #333;
        }
        .nama-value {
            font-size: 9px;
            text-transform: uppercase;
        }
        
        /* Password */
        .password-box {
            border: 1px dashed #999;
            border-radius: 4px;
            padding: 5px 8px;
            background: #fff;
            display: inline-block;
        }
        .password-box table {
            width: auto;
        }
        .password-label {
            color: #666;
            font-size: 9px;
            padding-right: 8px;
        }
        .password-value {
            color: #c0392b;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            font-family: Consolas, Monaco, monospace;
        }
        
        /* Footer */
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #ccc;
            padding: 6px 12px;
            z-index: 1;
            background: #fff;
        }
        .card-footer table {
            width: 100%;
        }
        .card-footer td {
            color: #666;
            font-size: 9px;
        }
        .year-badge {
            border: 1px solid #999;
            color: #333;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .footer-center {
            text-align: center;
            color: #666;
        }
        .footer-right {
            text-align: right;
            color: #999;
            font-size: 8px;
        }
        
        /* QR Code */
        .qr-code-box {
            position: absolute;
            bottom: 28px;
            left: 8px;
            width: 65px;
            height: 65px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 3px;
            z-index: 10;
        }
        .qr-code-box svg, .qr-code-box img {
            width: 100%;
            height: 100%;
        }
        
        /* Cut Guide */
        .cut-guide {
            margin-top: 15px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    {{-- Action Buttons (hanya muncul di preview, tidak di PDF) --}}
    <div class="action-buttons no-print">
        @if(isset($isAdmin) && $isAdmin)
            <a href="{{ route('admin.pendaftar.show', $calonSiswa->id) }}" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Print Kartu</button>
            <a href="{{ route('admin.pendaftar.cetak-ujian', $calonSiswa->id) }}" class="btn btn-download"><i class="fas fa-download"></i> Download PDF</a>
        @else
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Print Kartu</button>
            <a href="{{ route('pendaftar.cetak-kartu-ujian') }}" class="btn btn-download"><i class="fas fa-download"></i> Download PDF</a>
        @endif
    </div>
    @endif
    
    <div class="card-wrapper">
        <div class="card">
            {{-- Watermark Logo --}}
            <div class="watermark">
                @if($sekolah && $sekolah->logo)
                    @php
                        // Check if logo is already absolute path or relative path
                        $logoPath = $sekolah->logo;
                        if (!file_exists($logoPath)) {
                            // Try as relative path in storage
                            $logoPath = storage_path('app/public/' . $sekolah->logo);
                        }
                        
                        $logoBase64 = null;
                        if (file_exists($logoPath)) {
                            $logoData = file_get_contents($logoPath);
                            $logoMime = @mime_content_type($logoPath) ?: 'image/png';
                            $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
                        }
                    @endphp
                    @if(isset($isPdf) && $isPdf && $logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @elseif($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @else
                        <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo">
                    @endif
                @endif
            </div>
            
            {{-- Header --}}
            <div class="card-header">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="school-name">{{ Str::limit($sekolah->nama_sekolah ?? config('app.name'), 30) }}</td>
                        <td style="text-align: right;"><span class="card-type">KARTU TES PPDB</span></td>
                    </tr>
                </table>
            </div>

            {{-- Body --}}
            <div class="card-body">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        {{-- Photo --}}
                        <td class="photo-cell">
                            <div class="photo-box">
                                @php
                                    $fotoDokumen = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
                                    $fotoPath = ($fotoDokumen && $fotoDokumen->storage_disk === 'public' && $fotoDokumen->file_path) ? storage_path('app/public/' . $fotoDokumen->file_path) : null;
                                    $fotoUrl = $fotoDokumen?->file_url;
                                    
                                    // For PDF, use base64 encoded image
                                    $fotoBase64 = null;
                                    if ($fotoPath && file_exists($fotoPath)) {
                                        $fotoData = file_get_contents($fotoPath);
                                        $fotoMime = mime_content_type($fotoPath);
                                        $fotoBase64 = 'data:' . $fotoMime . ';base64,' . base64_encode($fotoData);
                                    }
                                @endphp
                                @if(isset($isPdf) && $isPdf && $fotoBase64)
                                    <img src="{{ $fotoBase64 }}" alt="Foto">
                                @elseif(isset($isPdf) && $isPdf && $fotoUrl)
                                    <img src="{{ $fotoUrl }}" alt="Foto">
                                @elseif($fotoUrl && (!isset($isPdf) || !$isPdf))
                                    <img src="{{ $fotoUrl }}" alt="Foto">
                                @else
                                    <div class="no-photo">Pas Foto</div>
                                @endif
                            </div>
                        </td>
                        
                        {{-- Info --}}
                        <td class="info-cell">
                            {{-- Nomor Tes --}}
                            <div class="nomor-tes-box">
                                <div class="nomor-tes-label">Nomor Tes</div>
                                <div class="nomor-tes-value">{{ $calonSiswa->nomor_tes }}</div>
                            </div>
                            
                            {{-- Data --}}
                            <table class="data-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="data-label">Nama</td>
                                    <td class="data-separator">:</td>
                                    <td class="data-value nama-value">{{ $calonSiswa->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <td class="data-label">NISN</td>
                                    <td class="data-separator">:</td>
                                    <td class="data-value">{{ $calonSiswa->nisn }}</td>
                                </tr>
                                <tr>
                                    <td class="data-label">TTL</td>
                                    <td class="data-separator">:</td>
                                    <td class="data-value">{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($calonSiswa->tanggal_lahir)->format('d/m/Y') : '-' }}</td>
                                </tr>
                                @if($calonSiswa->jalurPendaftaran?->pilihan_program_aktif && $calonSiswa->pilihan_program)
                                <tr>
                                    <td class="data-label">Program</td>
                                    <td class="data-separator">:</td>
                                    <td class="data-value">{{ $calonSiswa->pilihan_program }}</td>
                                </tr>
                                @endif
                            </table>
                            
                            {{-- Password --}}
                            <div class="password-box">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="password-label"><i class="fas fa-key"></i> Password:</td>
                                        <td class="password-value">{{ $password ?? '********' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            {{-- QR Code --}}
            <div class="qr-code-box">
                @php
                    $hash = $calonSiswa->getOrGenerateHash();
                    $verifyUrl = route('verify.bukti', $hash);
                    $qrSvg = QrCode::format('svg')->size(80)->margin(0)->generate($verifyUrl);
                    $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
                @endphp
                @if(isset($isPdf) && $isPdf)
                    <img src="{{ $qrBase64 }}" style="width: 100%; height: 100%;" alt="QR">
                @else
                    {!! $qrSvg !!}
                @endif
            </div>

            {{-- Footer --}}
            <div class="card-footer">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td><span class="year-badge">{{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ (($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1) }}</span></td>
                        <td class="footer-center">{{ $calonSiswa->jalurPendaftaran->nama ?? 'Jalur Umum' }}</td>
                        <td class="footer-right">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        @if(!isset($isPdf) || !$isPdf)
        <div class="cut-guide no-print"><i class="fas fa-cut"></i> Gunting mengikuti tepi kartu</div>
        @endif
    </div>
</body>
</html>
