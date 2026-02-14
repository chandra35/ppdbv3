<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Sesi - {{ $jadwal->tanggal_ujian->format('d-m-Y') }}</title>
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
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 8mm;
            border-bottom: 3px double #333;
            padding-bottom: 5mm;
        }
        
        .header h1 {
            font-size: 18pt;
            margin-bottom: 2mm;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #555;
        }
        
        .header-info {
            margin-top: 3mm;
            font-size: 11pt;
        }
        
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 8mm;
            padding: 4mm;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-value {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
        }
        
        .summary-label {
            font-size: 9pt;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 3mm;
            text-align: center;
        }
        
        th {
            background: #333;
            color: #fff;
            font-weight: bold;
        }
        
        .th-sesi { width: 15mm; }
        .th-waktu { width: 35mm; }
        .th-cbt { background: #28a745 !important; }
        .th-wawancara { background: #ffc107 !important; color: #333 !important; }
        
        .cell-cbt {
            background: rgba(40, 167, 69, 0.1);
        }
        
        .cell-wawancara {
            background: rgba(255, 193, 7, 0.1);
        }
        
        .peserta-count {
            font-size: 14pt;
            font-weight: bold;
        }
        
        .range-info {
            font-size: 8pt;
            color: #666;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 20mm;
            margin-top: 8mm;
            padding-top: 5mm;
            border-top: 1px dashed #ccc;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 3mm;
        }
        
        .legend-color {
            width: 15mm;
            height: 8mm;
            border-radius: 3px;
        }
        
        .legend-color.cbt { background: #28a745; }
        .legend-color.wawancara { background: #ffc107; }
        
        .grup-info {
            margin-top: 8mm;
            padding: 4mm;
            background: #f0f8ff;
            border-left: 4px solid #007bff;
            border-radius: 3px;
        }
        
        .grup-info h4 {
            margin-bottom: 2mm;
            color: #007bff;
        }
        
        .footer {
            margin-top: 8mm;
            text-align: center;
            font-size: 8pt;
            color: #999;
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
            background: #17a2b8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #138496;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Jadwal Sesi</button>

    <div class="header">
        <h1>JADWAL SESI UJIAN CBT & WAWANCARA</h1>
        <h2>{{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</h2>
        <div class="header-info">
            Tahun Pelajaran: {{ $jadwal->tahunPelajaran->nama ?? '-' }} | 
            Mulai Pukul: {{ $jadwal->jam_mulai }} |
            Jeda Antar Sesi: {{ $jadwal->jeda_sesi }} menit
        </div>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $jadwal->total_peserta }}</div>
            <div class="summary-label">Total Peserta</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $jadwal->total_sesi }}</div>
            <div class="summary-label">Jumlah Sesi</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color: #28a745;">{{ $jadwal->jumlah_ruang_cbt }}</div>
            <div class="summary-label">Ruang CBT</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color: #ffc107;">{{ $jadwal->jumlah_ruang_wawancara }}</div>
            <div class="summary-label">Ruang Wawancara</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $jadwal->jumlah_ruang_cbt * $jadwal->kapasitas_cbt }}</div>
            <div class="summary-label">Kapasitas CBT/Sesi</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $jadwal->jumlah_ruang_wawancara * $jadwal->kapasitas_wawancara }}</div>
            <div class="summary-label">Kapasitas Wawancara/Sesi</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="th-sesi">Sesi</th>
                <th class="th-waktu">Waktu</th>
                <th class="th-cbt">CBT ({{ $jadwal->durasi_cbt }} menit)</th>
                <th class="th-wawancara">Wawancara ({{ $jadwal->durasi_wawancara }} menit)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sesiGrouped = $jadwal->sesiUjian->groupBy('nomor_sesi');
            @endphp
            @foreach($sesiGrouped as $nomorSesi => $sesiList)
            @php
                $sesiCbt = $sesiList->where('jenis_ujian', 'cbt')->first();
                $sesiWawancara = $sesiList->where('jenis_ujian', 'wawancara')->first();
                
                $pesertaCbt = $jadwal->jadwalPeserta->where('sesi_cbt_id', $sesiCbt?->id ?? null);
                $pesertaWawancara = $jadwal->jadwalPeserta->where('sesi_wawancara_id', $sesiWawancara?->id ?? null);
                
                $waktuMulai = optional($sesiCbt ?? $sesiWawancara)->waktu_mulai?->format('H:i') ?? '-';
                $waktuSelesai = optional($sesiCbt ?? $sesiWawancara)->waktu_selesai?->format('H:i') ?? '-';
            @endphp
            <tr>
                <td><strong>{{ $nomorSesi }}</strong></td>
                <td>{{ $waktuMulai }} - {{ $waktuSelesai }}</td>
                <td class="cell-cbt">
                    @if($sesiCbt)
                    <div class="peserta-count">{{ $pesertaCbt->count() }} peserta</div>
                    <div class="range-info">
                        {{ $jadwal->jumlah_ruang_cbt }} ruang × {{ $jadwal->kapasitas_cbt }} kapasitas
                    </div>
                    @else
                    -
                    @endif
                </td>
                <td class="cell-wawancara">
                    @if($sesiWawancara)
                    <div class="peserta-count">{{ $pesertaWawancara->count() }} peserta</div>
                    <div class="range-info">
                        {{ $jadwal->jumlah_ruang_wawancara }} ruang × {{ $jadwal->kapasitas_wawancara }} kapasitas
                    </div>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="grup-info">
        <h4>Keterangan Grup</h4>
        <p>
            <strong>Grup A:</strong> Peserta mengikuti CBT terlebih dahulu, kemudian Wawancara (CBT → Wawancara)<br>
            <strong>Grup B:</strong> Peserta mengikuti Wawancara terlebih dahulu, kemudian CBT (Wawancara → CBT)
        </p>
        <p style="margin-top: 2mm; font-size: 9pt; color: #666;">
            Pembagian grup dilakukan secara otomatis untuk memaksimalkan penggunaan ruang secara paralel.
        </p>
    </div>

    <div class="legend">
        <div class="legend-item">
            <div class="legend-color cbt"></div>
            <span>Tes CBT ({{ $jadwal->durasi_cbt }} menit)</span>
        </div>
        <div class="legend-item">
            <div class="legend-color wawancara"></div>
            <span>Wawancara ({{ $jadwal->durasi_wawancara }} menit)</span>
        </div>
    </div>

    {{-- Ketua Panitia Signature --}}
    <div style="margin-top: 10mm; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 70mm;">
            <div style="font-size: 9pt; margin-bottom: 2mm;">Mengetahui,</div>
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 20mm;">Ketua Panitia</div>
            <div style="border-top: 1px solid #333; padding-top: 2mm;">
                <strong>{{ (isset($ketuaPanitia) && $ketuaPanitia) ? $ketuaPanitia->name : '........................' }}</strong>
            </div>
        </div>
    </div>

    <div class="footer">
        Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} | 
        PPDB {{ $jadwal->tahunPelajaran->nama ?? '-' }}
    </div>
</body>
</html>
