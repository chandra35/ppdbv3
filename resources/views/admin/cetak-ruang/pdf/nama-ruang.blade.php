<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nama Ruang Ujian</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
        .page-break {
            page-break-after: always;
        }
        .room-label {
            width: 100%;
            height: 190mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border: 4px solid #2c3e50;
            border-radius: 15px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
        }
        .room-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(90deg, #3498db, #2c3e50, #3498db);
        }
        .room-label::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(90deg, #3498db, #2c3e50, #3498db);
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
            text-shadow: 3px 3px 6px rgba(0,0,0,0.1);
        }
        .room-info {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 20px;
        }
        .info-box {
            background: #3498db;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            text-align: center;
        }
        .info-box .value {
            font-size: 28px;
            font-weight: bold;
        }
        .info-box .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .nomor-range {
            font-size: 24px;
            color: #2c3e50;
            margin-top: 30px;
            padding: 15px 40px;
            border: 2px solid #3498db;
            border-radius: 50px;
            background: white;
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
            transform: rotate(15deg);
        }
        .corner-decoration {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 3px solid #3498db;
            opacity: 0.3;
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
    </style>
</head>
<body>
    @foreach($rooms as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="room-label">
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
            <table style="margin: 0 auto;">
                <tr>
                    <td style="padding: 0 20px;">
                        <div class="info-box">
                            <div class="value">{{ $room['jumlah'] }}</div>
                            <div class="label">PESERTA</div>
                        </div>
                    </td>
                    <td style="padding: 0 20px;">
                        <div class="info-box" style="background: #27ae60;">
                            <div class="value">{{ $room['nomor'] }}</div>
                            <div class="label">RUANG KE</div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Nomor Tes Range --}}
            <div class="nomor-range">
                Nomor Tes: <span>{{ $room['peserta'][0]->nomor_tes ?? '-' }}</span> 
                s/d 
                <span>{{ $room['peserta'][count($room['peserta'])-1]->nomor_tes ?? '-' }}</span>
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
