<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Peserta Ruang Ujian</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px double #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .room-banner {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            text-align: center;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .room-banner h2 {
            font-size: 28px;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        .room-banner p {
            font-size: 14px;
            opacity: 0.9;
        }
        .room-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .room-info-item {
            text-align: center;
        }
        .room-info-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .room-info-item .label {
            font-size: 10px;
            color: #666;
        }
        table.peserta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.peserta th, 
        table.peserta td {
            border: 1px solid #333;
            padding: 8px 6px;
        }
        table.peserta th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.peserta td {
            font-size: 11px;
        }
        table.peserta .col-no { 
            width: 35px; 
            text-align: center;
            font-weight: bold;
        }
        table.peserta .col-nomor-tes { 
            width: 110px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background: #f8f9fa;
        }
        table.peserta .col-nama { 
            width: auto; 
            text-align: left; 
            padding-left: 10px; 
        }
        table.peserta .col-jk { 
            width: 30px; 
            text-align: center; 
        }
        table.peserta .col-asal { 
            width: 150px; 
            font-size: 10px;
        }
        table.peserta tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.peserta tbody tr:hover {
            background: #e8f4f8;
        }
        .footer-note {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 15px;
            padding: 10px;
            border-top: 1px dashed #ccc;
        }
        .instruction {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .instruction strong {
            color: #856404;
        }
    </style>
</head>
<body>
    @foreach($rooms as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        {{-- Header --}}
        <div class="header">
            @if($sekolah)
            <h1>{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h1>
            <h2>Penerimaan Peserta Didik Baru (PPDB) {{ $tahunAktif?->nama ?? date('Y') }}</h2>
            <p>{{ $sekolah->alamat ?? '' }}</p>
            @else
            <h1>DAFTAR PESERTA UJIAN</h1>
            <h2>PPDB Tahun {{ $tahunAktif?->nama ?? date('Y') }}</h2>
            @endif
        </div>

        {{-- Room Banner --}}
        <div class="room-banner">
            <h2>{{ strtoupper($room['nama']) }}</h2>
            <p>Daftar Peserta Ujian Seleksi PPDB</p>
        </div>

        {{-- Room Info --}}
        <table style="width: 100%; margin-bottom: 15px;">
            <tr>
                <td style="text-align: center; padding: 10px; background: #e8f4f8; border-radius: 5px; width: 33%;">
                    <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">{{ $room['jumlah'] }}</div>
                    <div style="font-size: 10px; color: #666;">Total Peserta</div>
                </td>
                <td style="text-align: center; padding: 10px; background: #e8f4f8; border-radius: 5px; width: 33%;">
                    <div style="font-size: 14px; font-weight: bold; color: #2c3e50;">
                        {{ $room['peserta'][0]->nomor_tes ?? '-' }}
                    </div>
                    <div style="font-size: 10px; color: #666;">Nomor Tes Awal</div>
                </td>
                <td style="text-align: center; padding: 10px; background: #e8f4f8; border-radius: 5px; width: 33%;">
                    <div style="font-size: 14px; font-weight: bold; color: #2c3e50;">
                        {{ $room['peserta'][count($room['peserta'])-1]->nomor_tes ?? '-' }}
                    </div>
                    <div style="font-size: 10px; color: #666;">Nomor Tes Akhir</div>
                </td>
            </tr>
        </table>

        {{-- Instruction --}}
        <div class="instruction">
            <strong>PETUNJUK:</strong> Peserta diharapkan mencari nama dan nomor tes masing-masing, 
            kemudian menempati kursi sesuai dengan nomor urut yang tertera.
        </div>

        {{-- Peserta Table --}}
        <table class="peserta">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">Nomor Tes</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-jk">L/P</th>
                    <th class="col-asal">Asal Sekolah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $peserta)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nomor-tes">{{ $peserta->nomor_tes }}</td>
                    <td class="col-nama">{{ $peserta->nama_lengkap }}</td>
                    <td class="col-jk">{{ $peserta->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="col-asal">{{ Str::limit($peserta->nama_sekolah_asal ?? '-', 25) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer Note --}}
        <div class="footer-note">
            <p>Dokumen ini ditempel di dalam ruang ujian untuk membantu peserta menemukan tempat duduknya.</p>
            <p style="margin-top: 5px;">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    @endforeach
</body>
</html>
