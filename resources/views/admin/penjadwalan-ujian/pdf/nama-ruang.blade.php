<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nama Ruang Ujian - {{ $jadwal->tanggal_ujian->format('Y-m-d') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 20mm 15mm 20mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .room-label {
            width: 100%;
            height: 170mm;
            text-align: center;
            border: 4px solid #2c3e50;
            border-radius: 15px;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
            padding-top: 30px;
        }
        .room-label-top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: #2c3e50;
        }
        .room-label-bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: #2c3e50;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .ppdb-text {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .room-name {
            font-size: 100px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 15px;
        }
        .exam-type {
            font-size: 28px;
            font-weight: bold;
            padding: 10px 30px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .exam-type.cbt {
            background: #28a745;
            color: white;
        }
        .exam-type.wawancara {
            background: #ffc107;
            color: #333;
        }
        .info-box {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-align: center;
            margin: 0 10px;
        }
        .info-box.green {
            background: #27ae60;
        }
        .info-box.orange {
            background: #e67e22;
        }
        .info-box .value {
            font-size: 24px;
            font-weight: bold;
        }
        .info-box .label {
            font-size: 11px;
        }
        .nomor-range {
            font-size: 20px;
            color: #2c3e50;
            margin-top: 25px;
            padding: 12px 35px;
            border: 2px solid #3498db;
            border-radius: 50px;
            background: white;
            display: inline-block;
        }
        .nomor-range span {
            font-weight: bold;
            color: #3498db;
        }
        .exam-badge {
            position: absolute;
            top: 30px;
            right: 30px;
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .corner-decoration {
            position: absolute;
            width: 80px;
            height: 80px;
            border: 3px solid #3498db;
        }
        .corner-tl {
            top: 25px;
            left: 25px;
            border-right: none;
            border-bottom: none;
        }
        .corner-tr {
            top: 25px;
            right: 25px;
            border-left: none;
            border-bottom: none;
        }
        .corner-bl {
            bottom: 25px;
            left: 25px;
            border-right: none;
            border-top: none;
        }
        .corner-br {
            bottom: 25px;
            right: 25px;
            border-left: none;
            border-top: none;
        }
        .footer-label {
            position: absolute;
            bottom: 22px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    @foreach($ruangList as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="room-label">
            {{-- Top/Bottom Bars --}}
            <div class="room-label-top-bar"></div>
            <div class="room-label-bottom-bar"></div>
            
            {{-- Corner Decorations --}}
            <div class="corner-decoration corner-tl"></div>
            <div class="corner-decoration corner-tr"></div>
            <div class="corner-decoration corner-bl"></div>
            <div class="corner-decoration corner-br"></div>

            {{-- Exam Badge --}}
            <div class="exam-badge">UJIAN PPDB</div>

            {{-- School Name --}}
            <div class="school-name">
                {{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}
            </div>
            <div class="ppdb-text">
                Penerimaan Peserta Didik Baru {{ $jadwal->tahunPelajaran?->nama ?? date('Y') }}
            </div>

            {{-- Room Name --}}
            <div class="room-name">
                {{ $room['nama'] }}
            </div>

            {{-- Exam Type Badge --}}
            <div class="exam-type {{ $room['jenis'] }}">
                {{ strtoupper($room['jenis']) }} - SESI {{ $room['sesi'] }}
            </div>

            {{-- Room Info --}}
            <div style="margin-bottom: 10px;">
                <div class="info-box">
                    <div class="value">{{ $room['jumlah_peserta'] }}</div>
                    <div class="label">PESERTA</div>
                </div>
                <div class="info-box green">
                    <div class="value">{{ $room['sesi'] }}</div>
                    <div class="label">SESI</div>
                </div>
                <div class="info-box orange">
                    <div class="value">{{ $room['waktu'] }}</div>
                    <div class="label">WAKTU</div>
                </div>
            </div>

            {{-- Nomor Tes Range --}}
            <div class="nomor-range">
                Nomor Tes: <span>{{ $room['nomor_tes_awal'] }}</span> 
                s/d 
                <span>{{ $room['nomor_tes_akhir'] }}</span>
            </div>

            {{-- Footer --}}
            <div class="footer-label">
                {{ $jadwal->tanggal_ujian->translatedFormat('d F Y') }} | Dicetak: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
