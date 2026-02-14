<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Peserta Ruang Ujian - {{ $jadwal->tanggal_ujian->format('Y-m-d') }}</title>
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
            margin-bottom: 8px;
        }
        
        /* Room Banner */
        .room-banner {
            text-align: center;
            padding: 6px 0;
            margin-bottom: 6px;
            border: 2px solid #333;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            position: relative;
        }

        .room-banner h2 {
            font-size: 26px;
            margin: 2px 0 2px;
            letter-spacing: 3px;
        }
        .room-banner .sesi-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1px;
            opacity: 0.9;
        }
        .room-banner .exam-type {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .room-banner .exam-type.cbt { color: #90EE90; }
        .room-banner .exam-type.wawancara { color: #FFD700; }
        .room-banner p {
            font-size: 9px;
            opacity: 0.9;
        }
        
        /* Stats Box */
        .stat-box {
            text-align: center;
            padding: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 7px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        /* Instruction */
        .instruction {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 7px;
            margin-bottom: 6px;
            text-align: center;
        }
        
        /* Table */
        table.peserta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        table.peserta th {
            background: #2c3e50;
            color: #fff;
            padding: 6px 4px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #333;
        }
        table.peserta td {
            padding: 4px 3px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        table.peserta tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.peserta .col-no { 
            width: 25px; 
            text-align: center;
            font-weight: bold;
        }
        table.peserta td.col-no {
            background: #f0f0f0;
        }
        table.peserta .col-nomor-tes { 
            width: 90px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: bold;
        }
        table.peserta td.col-nomor-tes {
            background: #e8f8f5;
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
    @foreach($ruangList as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        {{-- Kop Surat --}}
        @if(isset($kopSurat))
        <div class="kop-wrapper">
            {!! $kopSurat !!}
        </div>
        @endif

        {{-- Room Banner --}}
        <div class="room-banner">
            <div class="sesi-label">RUANG SESI {{ $room['sesi'] }}</div>
            <h2>{{ strtoupper($room['nama']) }}</h2>
            <div class="exam-type {{ $room['jenis'] }}">{{ strtoupper($room['jenis']) }}</div>
            <p>{{ $jadwal->tanggal_ujian->translatedFormat('l, d F Y') }} | {{ $room['waktu'] }}</p>
        </div>

        {{-- Room Stats --}}
        <table style="width: 100%; margin-bottom: 6px; border-collapse: separate; border-spacing: 5px 0;">
            <tr>
                <td class="stat-box">
                    <div class="stat-value">{{ $room['jumlah_peserta'] }}</div>
                    <div class="stat-label">Total Peserta</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value" style="font-size: 12px;">{{ $room['nomor_tes_awal'] }}</div>
                    <div class="stat-label">Nomor Tes Awal</div>
                </td>
                <td class="stat-box">
                    <div class="stat-value" style="font-size: 12px;">{{ $room['nomor_tes_akhir'] }}</div>
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
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-jk">JK</th>
                    <th class="col-asal">Asal Sekolah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $pesertaRuang)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nomor-tes">{{ $pesertaRuang->calonSiswa->nomor_tes ?? '-' }}</td>
                    <td class="col-nama">{{ Str::limit($pesertaRuang->calonSiswa->nama_lengkap ?? '-', 32) }}</td>
                    <td class="col-jk">{{ ($pesertaRuang->calonSiswa->jenis_kelamin ?? '') == 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="col-asal">{{ Str::limit($pesertaRuang->calonSiswa->nama_sekolah_asal ?? '-', 24) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer-note">
            {{ $room['nama'] }} | {{ strtoupper($room['jenis']) }} Sesi {{ $room['sesi'] }} | Dicetak: {{ now()->format('d/m/Y H:i') }} | Halaman {{ $roomIndex + 1 }} dari {{ count($ruangList) }}
        </div>
    </div>
    @endforeach
</body>
</html>
