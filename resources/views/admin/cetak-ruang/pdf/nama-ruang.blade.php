<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nama Ruang Ujian</title>
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
            font-size: 120px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 20px;
        }
        .info-box {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            text-align: center;
            margin: 0 15px;
        }
        .info-box.green {
            background: #27ae60;
        }
        .info-box .value {
            font-size: 28px;
            font-weight: bold;
        }
        .info-box .label {
            font-size: 12px;
        }
        .nomor-range {
            font-size: 24px;
            color: #2c3e50;
            margin-top: 30px;
            padding: 15px 40px;
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
    @foreach($rooms as $roomIndex => $room)
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
                Penerimaan Peserta Didik Baru {{ $tahunAktif?->nama ?? date('Y') }}
            </div>

            {{-- Room Name --}}
            <div class="room-name">
                {{ $room['nama'] }}
            </div>

            {{-- Room Info --}}
            <div>
                <div class="info-box">
                    <div class="value">{{ $room['jumlah'] }}</div>
                    <div class="label">PESERTA</div>
                </div>
                <div class="info-box green">
                    <div class="value">{{ $room['nomor'] }}</div>
                    <div class="label">RUANG KE</div>
                </div>
            </div>

            {{-- Nomor Tes Range --}}
            <div class="nomor-range">
                Nomor Tes: <span>{{ $room['peserta'][0]->nomor_tes ?? '-' }}</span> 
                s/d 
                <span>{{ $room['peserta'][count($room['peserta'])-1]->nomor_tes ?? '-' }}</span>
            </div>

            {{-- Footer --}}
            <div class="footer-label">
                Dokumen Resmi Dicetak pada: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
