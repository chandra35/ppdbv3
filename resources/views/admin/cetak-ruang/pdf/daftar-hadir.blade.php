<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Ruang Ujian</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 8mm;
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
            margin-bottom: 5px;
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
        
        /* Title Banner */
        .title-banner {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            text-align: center;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .title-banner h2 {
            font-size: 13px;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }
        .title-banner p {
            font-size: 10px;
            opacity: 0.9;
        }
        
        /* Room Info Box */
        .room-info {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .room-info-cell {
            display: table-cell;
            padding: 6px 8px;
            vertical-align: middle;
            border-right: 1px solid #dee2e6;
        }
        .room-info-cell:last-child {
            border-right: none;
        }
        .room-info-label {
            font-size: 7px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .room-info-value {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Table */
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
            margin-left: auto;
            margin-right: auto;
        }
        table.attendance th {
            background: #f0f0f0;
            color: #333;
            padding: 6px 3px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #333;
        }
        table.attendance td {
            padding: 4px 3px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        table.attendance tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.attendance .col-no { 
            width: 22px; 
            text-align: center;
            font-weight: bold;
        }
        table.attendance .col-nomor-tes { 
            width: 70px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 8px;
            font-weight: bold;
        }
        table.attendance .col-nisn { 
            width: 65px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 7px;
        }
        table.attendance .col-nama { 
            text-align: left; 
            padding-left: 5px;
            font-size: 8px;
        }
        table.attendance .col-jk { 
            width: 20px; 
            text-align: center; 
        }
        table.attendance .col-asal { 
            width: 100px;
            font-size: 7px;
            padding: 3px 2px;
        }
        table.attendance .col-ttd { 
            width: 55px; 
        }
        table.attendance .col-ket { 
            width: 35px; 
            text-align: center;
        }
        
        /* Summary */
        .summary-box {
            display: table;
            width: 100%;
            font-size: 8px;
            margin-bottom: 8px;
        }
        .summary-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .summary-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .summary-item {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            background: #e9ecef;
            border-radius: 3px;
        }
        
        /* Signature */
        .signature-box {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 5px;
        }
        .signature-title {
            font-size: 8px;
            margin-bottom: 35px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 100px;
            margin: 0 auto 3px;
        }
        .signature-note {
            font-size: 7px;
            color: #6c757d;
        }
        
        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 7px;
            color: #6c757d;
            margin-top: 5px;
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

        {{-- Title Banner --}}
        <div class="title-banner">
            <h2>DAFTAR HADIR PESERTA UJIAN</h2>
            <p>Tahun Pelajaran {{ $tahunAktif?->nama ?? date('Y') }}</p>
        </div>

        {{-- Room Info --}}
        <div class="room-info">
            <div class="room-info-cell" style="width: 25%;">
                <div class="room-info-label">Nama Ruang</div>
                <div class="room-info-value">{{ $room['nama'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 20%;">
                <div class="room-info-label">Jumlah Peserta</div>
                <div class="room-info-value">{{ $room['jumlah'] }} Orang</div>
            </div>
            <div class="room-info-cell" style="width: 27%;">
                <div class="room-info-label">Tanggal Ujian</div>
                <div class="room-info-value">........................................</div>
            </div>
            <div class="room-info-cell" style="width: 28%;">
                <div class="room-info-label">Waktu</div>
                <div class="room-info-value">............. s/d .............</div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <table class="attendance">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">No. Tes</th>
                    <th class="col-nisn">NISN</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-jk">JK</th>
                    <th class="col-asal">Asal Sekolah</th>
                    <th class="col-ttd">TTD</th>
                    <th class="col-ket">Ket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $peserta)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nomor-tes">{{ $peserta->nomor_tes }}</td>
                    <td class="col-nisn">{{ $peserta->nisn ?? '-' }}</td>
                    <td class="col-nama">{{ Str::limit($peserta->nama_lengkap, 28) }}</td>
                    <td class="col-jk">{{ $peserta->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="col-asal">{{ Str::limit($peserta->nama_sekolah_asal ?? '-', 20) }}</td>
                    <td class="col-ttd"></td>
                    <td class="col-ket"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <div class="summary-box">
            <div class="summary-left">
                <span class="summary-item"><strong>Keterangan:</strong> H=Hadir, S=Sakit, I=Izin, A=Alpa</span>
            </div>
            <div class="summary-right">
                <span class="summary-item">Hadir: .......</span>
                <span class="summary-item">Tidak Hadir: .......</span>
            </div>
        </div>

        {{-- Signature --}}
        <div class="signature-box">
            <div class="signature-cell">
                <div class="signature-title">Pengawas 1</div>
                <div class="signature-line"></div>
                <div class="signature-note">NIP: ........................</div>
            </div>
            <div class="signature-cell">
                <div class="signature-title">Pengawas 2</div>
                <div class="signature-line"></div>
                <div class="signature-note">NIP: ........................</div>
            </div>
            <div class="signature-cell">
                <div class="signature-title">Mengetahui,<br>Ketua Panitia</div>
                <div class="signature-line"></div>
                <div class="signature-note">NIP: ........................</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer-note">
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} | {{ $room['nama'] }} - Halaman {{ $roomIndex + 1 }} dari {{ count($rooms) }}
        </div>
    </div>
    @endforeach
</body>
</html>
