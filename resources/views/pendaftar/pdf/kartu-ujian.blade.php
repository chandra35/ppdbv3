<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Tes - {{ $calonSiswa->nomor_tes }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
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
            width: 340px;
            height: 220px;
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
        }
        .password-box table {
            width: 100%;
        }
        .password-label {
            color: #666;
            font-size: 9px;
        }
        .password-value {
            color: #c0392b;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            font-family: Consolas, Monaco, monospace;
            text-align: right;
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
        
        /* Cut Guide */
        .cut-guide {
            margin-top: 15px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            width: 340px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    {{-- Action Buttons (tidak muncul saat print) --}}
    <div class="action-buttons no-print">
        @if(isset($isAdmin) && $isAdmin)
            <a href="{{ route('admin.pendaftar.show', $calonSiswa->id) }}" class="btn btn-back">← Kembali</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Print Kartu</button>
            <a href="{{ route('admin.pendaftar.cetak-ujian', $calonSiswa->id) }}" class="btn btn-download">⬇️ Download PDF</a>
        @else
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-back">← Kembali</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Print Kartu</button>
            <a href="{{ route('pendaftar.cetak-kartu-ujian') }}" class="btn btn-download">⬇️ Download PDF</a>
        @endif
    </div>
    
    <div class="card-wrapper">
        <div class="card">
            {{-- Watermark Logo --}}
            <div class="watermark">
                @if($sekolah && $sekolah->logo)
                    <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo">
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
                                    $fotoUrl = $fotoDokumen ? asset('storage/' . $fotoDokumen->file_path) : null;
                                @endphp
                                @if($fotoUrl)
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
                            </table>
                            
                            {{-- Password --}}
                            <div class="password-box">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="password-label">🔑 Password:</td>
                                        <td class="password-value">{{ $password ?? '********' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
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
        
        <div class="cut-guide no-print">✂️ Gunting mengikuti tepi kartu</div>
    </div>
</body>
</html>
