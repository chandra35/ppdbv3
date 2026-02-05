<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta Ujian - {{ $jadwal->tanggal_ujian->format('d-m-Y') }}</title>
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
            font-size: 10pt;
            line-height: 1.4;
        }
        
        .kartu-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5mm;
        }
        
        .kartu {
            width: 90mm;
            height: 128mm;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 4mm;
            page-break-inside: avoid;
            position: relative;
            background: #fff;
        }
        
        .kartu-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }
        
        .kartu-header h1 {
            font-size: 12pt;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        
        .kartu-header h2 {
            font-size: 10pt;
            font-weight: normal;
            color: #555;
        }
        
        .kartu-body {
            font-size: 9pt;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 2mm;
        }
        
        .info-label {
            width: 25mm;
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .nomor-tes {
            text-align: center;
            background: #333;
            color: #fff;
            padding: 3mm;
            border-radius: 4px;
            margin: 3mm 0;
        }
        
        .nomor-tes-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nomor-tes-value {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .jadwal-section {
            margin-top: 3mm;
        }
        
        .jadwal-title {
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1px solid #ccc;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }
        
        .jadwal-item {
            background: #f5f5f5;
            padding: 2mm;
            border-radius: 3px;
            margin-bottom: 2mm;
        }
        
        .jadwal-item.cbt {
            border-left: 3px solid #28a745;
        }
        
        .jadwal-item.wawancara {
            border-left: 3px solid #ffc107;
        }
        
        .jadwal-item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .jadwal-item-body {
            font-size: 8pt;
            color: #555;
        }
        
        .grup-badge {
            display: inline-block;
            padding: 1mm 3mm;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .grup-badge.a {
            background: #007bff;
            color: #fff;
        }
        
        .grup-badge.b {
            background: #6c757d;
            color: #fff;
        }
        
        .kartu-footer {
            position: absolute;
            bottom: 4mm;
            left: 4mm;
            right: 4mm;
            text-align: center;
            font-size: 7pt;
            color: #999;
            border-top: 1px dashed #ccc;
            padding-top: 2mm;
        }
        
        .page-break {
            page-break-after: always;
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
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Kartu</button>

    <div class="kartu-container">
        @foreach($pesertaList as $index => $jp)
        <div class="kartu">
            <div class="kartu-header">
                <h1>Kartu Peserta Ujian</h1>
                <h2>{{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</h2>
            </div>
            
            <div class="kartu-body">
                <div class="nomor-tes">
                    <div class="nomor-tes-label">Nomor Tes</div>
                    <div class="nomor-tes-value">{{ $jp->calonSiswa->nomor_tes ?? '-' }}</div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">: <strong>{{ $jp->calonSiswa->nama_lengkap ?? '-' }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Jalur</span>
                    <span class="info-value">: {{ $jp->calonSiswa->jalur->nama ?? '-' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Asal Sekolah</span>
                    <span class="info-value">: {{ $jp->calonSiswa->nama_sekolah_asal ?? '-' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Grup</span>
                    <span class="info-value">: <span class="grup-badge {{ strtolower($jp->grup) }}">Grup {{ $jp->grup }}</span>
                        @if($jp->grup === 'A')
                        <small>(CBT → Wawancara)</small>
                        @else
                        <small>(Wawancara → CBT)</small>
                        @endif
                    </span>
                </div>
                
                <div class="jadwal-section">
                    <div class="jadwal-title">Jadwal Ujian</div>
                    
                    @if($jp->grup === 'A')
                    {{-- Grup A: CBT dulu --}}
                    <div class="jadwal-item cbt">
                        <div class="jadwal-item-header">
                            <span>1. Tes CBT</span>
                            <span>Sesi {{ $jp->sesiCbt->nomor_sesi ?? '-' }}</span>
                        </div>
                        <div class="jadwal-item-body">
                            Pukul: {{ optional($jp->sesiCbt)->waktu_mulai?->format('H:i') ?? '-' }} - {{ optional($jp->sesiCbt)->waktu_selesai?->format('H:i') ?? '-' }}<br>
                            Ruang: <strong>{{ $jadwal->prefix_ruang_cbt }} {{ $jp->ruang_cbt_nomor ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="jadwal-item wawancara">
                        <div class="jadwal-item-header">
                            <span>2. Wawancara</span>
                            <span>Sesi {{ $jp->sesiWawancara->nomor_sesi ?? '-' }}</span>
                        </div>
                        <div class="jadwal-item-body">
                            Pukul: {{ optional($jp->sesiWawancara)->waktu_mulai?->format('H:i') ?? '-' }} - {{ optional($jp->sesiWawancara)->waktu_selesai?->format('H:i') ?? '-' }}<br>
                            Ruang: <strong>{{ $jadwal->prefix_ruang_wawancara }} {{ $jp->ruang_wawancara_nomor ?? '-' }}</strong>
                        </div>
                    </div>
                    @else
                    {{-- Grup B: Wawancara dulu --}}
                    <div class="jadwal-item wawancara">
                        <div class="jadwal-item-header">
                            <span>1. Wawancara</span>
                            <span>Sesi {{ $jp->sesiWawancara->nomor_sesi ?? '-' }}</span>
                        </div>
                        <div class="jadwal-item-body">
                            Pukul: {{ optional($jp->sesiWawancara)->waktu_mulai?->format('H:i') ?? '-' }} - {{ optional($jp->sesiWawancara)->waktu_selesai?->format('H:i') ?? '-' }}<br>
                            Ruang: <strong>{{ $jadwal->prefix_ruang_wawancara }} {{ $jp->ruang_wawancara_nomor ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="jadwal-item cbt">
                        <div class="jadwal-item-header">
                            <span>2. Tes CBT</span>
                            <span>Sesi {{ $jp->sesiCbt->nomor_sesi ?? '-' }}</span>
                        </div>
                        <div class="jadwal-item-body">
                            Pukul: {{ optional($jp->sesiCbt)->waktu_mulai?->format('H:i') ?? '-' }} - {{ optional($jp->sesiCbt)->waktu_selesai?->format('H:i') ?? '-' }}<br>
                            Ruang: <strong>{{ $jadwal->prefix_ruang_cbt }} {{ $jp->ruang_cbt_nomor ?? '-' }}</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="kartu-footer">
                Hadir 30 menit sebelum waktu ujian • Bawa kartu ini saat ujian
            </div>
        </div>
        
        @if(($index + 1) % 4 == 0 && $index + 1 < count($pesertaList))
        <div class="page-break"></div>
        @endif
        @endforeach
    </div>
</body>
</html>
