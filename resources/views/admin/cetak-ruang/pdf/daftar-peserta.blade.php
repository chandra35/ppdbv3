<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Peserta Ruang Ujian</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            padding: 10px 15px;
        }
        .page-content {
            width: 100%;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -1;
            width: 350px;
            height: 350px;
        }
        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Kop Surat */
        .kop-wrapper {
            margin-bottom: 20px;
        }
        .kop-wrapper div[style*="border-bottom: 3px double"] {
            border-bottom: 1px solid #000 !important;
        }
        .kop-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
        }
        .kop-header table {
            width: 100%;
        }
        .kop-logo {
            width: 60px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo img {
            height: 50px;
            width: auto;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-text .line1 {
            font-size: 10px;
            color: #666;
        }
        .kop-text .line2 {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .kop-text .line3 {
            font-size: 9px;
            color: #666;
        }
        .kop-text .line4 {
            font-size: 8px;
            color: #888;
        }
        
        /* Room Banner */
        .room-banner {
            text-align: center;
            padding: 10px 0;
            margin-bottom: 10px;
            border: 2px solid #333;
            border-radius: 4px;
            position: relative;
        }
        .room-banner h2 {
            font-size: 22px;
            margin-bottom: 3px;
            letter-spacing: 2px;
            color: #333;
            font-weight: bold;
        }
        .room-banner p {
            font-size: 11px;
            color: #555;
        }
        .room-badge {
            position: absolute;
            top: -8px;
            right: 10px;
            background: #fff;
            color: #333;
            padding: 2px 8px;
            border: 1px solid #333;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        /* Room Stats */
        .room-stats {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px 5px;
            background: #ecf0f1;
            border-radius: 4px;
        }
        .stat-box:nth-child(2) {
            margin: 0 5px;
            background: #e8f6f3;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 7px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        /* Instruction */
        .instruction {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 1px solid #ffc107;
            border-left: 4px solid #f39c12;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 10px;
            font-size: 8px;
        }
        .instruction strong {
            color: #856404;
        }
        
        /* Table */
        table.peserta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.peserta th {
            background: #f0f0f0;
            color: #333;
            padding: 6px 4px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #333;
        }
        table.peserta td {
            padding: 5px 4px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        table.peserta tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.peserta tbody tr:hover {
            background: #e8f8f5;
        }
        table.peserta .col-no { 
            width: 25px; 
            text-align: center;
            font-weight: bold;
            background: #f0f0f0;
        }
        table.peserta .col-nomor-tes { 
            width: 95px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: bold;
            background: #e8f8f5;
        }
        table.peserta .col-nisn { 
            width: 75px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 8px;
        }
        table.peserta .col-nama { 
            width: 180px;
            text-align: left; 
            padding-left: 6px;
            font-size: 9px;
        }
        table.peserta .col-jk { 
            width: 25px; 
            text-align: center;
            font-weight: bold;
        }
        table.peserta .col-asal { 
            width: 130px;
            font-size: 8px;
        }
        
        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 7px;
            color: #6c757d;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #dee2e6;
        }
    </style>
</head>
<body>
    @foreach($rooms as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        {{-- Watermark --}}
        @if($sekolah && $sekolah->logo)
        <div class="watermark">
            <img src="{{ public_path('storage/' . $sekolah->logo) }}" alt="Watermark">
        </div>
        @endif

        {{-- Kop Surat --}}
        @if(isset($kopHtml))
        <div class="kop-wrapper">
            {!! $kopHtml !!}
        </div>
        @else
        <div class="kop-header">
            <table>
                <tr>
                    @if($sekolah && $sekolah->logo)
                    <td class="kop-logo">
                        <img src="{{ public_path('storage/' . $sekolah->logo) }}" alt="Logo">
                    </td>
                    @endif
                    <td class="kop-text">
                        <div class="line1">PENERIMAAN PESERTA DIDIK BARU (PPDB)</div>
                        <div class="line2">{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</div>
                        <div class="line3">{{ $sekolah->alamat ?? 'Alamat Sekolah' }}</div>
                        @if($sekolah && ($sekolah->telepon || $sekolah->email))
                        <div class="line4">
                            {{ $sekolah->telepon ? 'Telp: '.$sekolah->telepon : '' }}
                            {{ $sekolah->telepon && $sekolah->email ? ' | ' : '' }}
                            {{ $sekolah->email ? 'Email: '.$sekolah->email : '' }}
                        </div>
                        @endif
                    </td>
                    @if($sekolah && $sekolah->logo)
                    <td class="kop-logo">
                        <img src="{{ public_path('storage/' . $sekolah->logo) }}" alt="Logo">
                    </td>
                    @endif
                </tr>
            </table>
        </div>
        @endif

        {{-- Room Banner --}}
        <div class="room-banner">
            <div class="room-badge">TEMPEL DI RUANG</div>
            <h2>{{ strtoupper($room['nama']) }}</h2>
            <p>Daftar Peserta Calon Siswa Tahun Pelajaran {{ $tahunAktif?->nama ?? date('Y') }}</p>
        </div>

        {{-- Room Stats --}}
        <table style="width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 5px 0;">
            <tr>
                <td class="stat-box">
                    <div class="stat-value">{{ $room['jumlah'] }}</div>
                    <div class="stat-label">Total Peserta</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value" style="font-size: 12px;">{{ $room['peserta'][0]->nomor_tes ?? '-' }}</div>
                    <div class="stat-label">Nomor Tes Awal</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value" style="font-size: 12px;">{{ $room['peserta'][count($room['peserta'])-1]->nomor_tes ?? '-' }}</div>
                    <div class="stat-label">Nomor Tes Akhir</div>
                </td>
            </tr>
        </table>

        {{-- Instruction --}}
        <div class="instruction">
            <strong>📋 PETUNJUK:</strong> Peserta diharapkan mencari nama dan nomor tes masing-masing pada daftar di bawah ini, 
            kemudian menempati kursi sesuai dengan nomor urut yang tertera.
        </div>

        {{-- Peserta Table --}}
        <table class="peserta">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">No. Tes</th>
                    <th class="col-nisn">NISN</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-jk">JK</th>
                    <th class="col-asal">Asal Sekolah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $peserta)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nomor-tes">{{ $peserta->nomor_tes }}</td>
                    <td class="col-nisn">{{ $peserta->nisn ?? '-' }}</td>
                    <td class="col-nama">{{ Str::limit($peserta->nama_lengkap, 30) }}</td>
                    <td class="col-jk">{{ $peserta->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="col-asal">{{ Str::limit($peserta->nama_sekolah_asal ?? '-', 22) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer-note">
            Dokumen Resmi Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $room['nama'] }} - Halaman {{ $roomIndex + 1 }}
        </div>
    </div>
    @endforeach
</body>
</html>
