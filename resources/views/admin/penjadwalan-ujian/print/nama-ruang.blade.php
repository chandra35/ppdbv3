<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nama Ruang - {{ $jadwal->tanggal_ujian->format('d-m-Y') }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
        }
        
        .room-label {
            width: 190mm;
            height: 130mm;
            border: 3px solid #333;
            border-radius: 10px;
            padding: 10mm;
            margin-bottom: 10mm;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .room-label:nth-child(2n) {
            page-break-after: always;
        }
        
        .room-label.cbt {
            border-color: #28a745;
            background: linear-gradient(135deg, rgba(40,167,69,0.1) 0%, rgba(255,255,255,1) 100%);
        }
        
        .room-label.wawancara {
            border-color: #ffc107;
            background: linear-gradient(135deg, rgba(255,193,7,0.1) 0%, rgba(255,255,255,1) 100%);
        }
        
        .room-type {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            padding: 3mm 8mm;
            border-radius: 5px;
            margin-bottom: 5mm;
        }
        
        .room-type.cbt {
            background: #28a745;
            color: #fff;
        }
        
        .room-type.wawancara {
            background: #ffc107;
            color: #333;
        }
        
        .room-name {
            font-size: 48pt;
            font-weight: bold;
            margin: 10mm 0;
            letter-spacing: 2px;
        }
        
        .room-info {
            font-size: 14pt;
            color: #555;
            margin-top: 5mm;
        }
        
        .room-capacity {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 5mm;
        }
        
        .room-sessions {
            margin-top: 8mm;
            padding-top: 5mm;
            border-top: 1px dashed #ccc;
            font-size: 12pt;
        }
        
        .exam-date {
            font-size: 14pt;
            color: #333;
            margin-top: 5mm;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
        
        .btn-print {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #ffc107;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .btn-print:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Label Ruang</button>

    {{-- CBT Rooms --}}
    @foreach($cbtRooms as $room)
    <div class="room-label cbt">
        <div class="room-type cbt">TES CBT</div>
        <div class="room-name">{{ $room['nama_ruang'] }}</div>
        <div class="room-capacity">
            Peserta: {{ $room['jumlah_peserta'] }} / {{ $room['kapasitas'] }}
            @if($room['overflow'])
                <span style="color: #dc3545; font-weight: bold;"> (+{{ $room['jumlah_peserta'] - $room['kapasitas'] }} overflow)</span>
            @endif
        </div>
        <div class="room-info">Durasi: {{ $jadwal->durasi_cbt }} menit</div>
        <div class="exam-date">{{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    @endforeach

    {{-- TBQ Rooms --}}
    @foreach($wawancaraRooms as $room)
    <div class="room-label wawancara">
        <div class="room-type wawancara">TBQ</div>
        <div class="room-name">{{ $room['nama_ruang'] }}</div>
        <div class="room-capacity">
            Peserta: {{ $room['jumlah_peserta'] }} / {{ $room['kapasitas'] }}
            @if($room['overflow'])
                <span style="color: #dc3545; font-weight: bold;"> (+{{ $room['jumlah_peserta'] - $room['kapasitas'] }} overflow)</span>
            @endif
        </div>
        <div class="room-info">Durasi: {{ $jadwal->durasi_wawancara }} menit</div>
        <div class="exam-date">{{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    @endforeach
</body>
</html>
