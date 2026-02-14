<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Ujian - {{ $jadwal->tanggal_ujian->format('d-m-Y') }}</title>
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
            font-size: 9pt;
            line-height: 1.3;
        }
        
        .page {
            page-break-after: always;
        }
        
        .page:last-child {
            page-break-after: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 2px solid #333;
            padding-bottom: 3mm;
        }
        
        .header h1 {
            font-size: 14pt;
            margin-bottom: 2mm;
        }
        
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #555;
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            margin-top: 2mm;
            font-size: 10pt;
        }
        
        .room-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f0f0f0;
            padding: 3mm;
            margin-bottom: 3mm;
            border-radius: 3px;
        }
        
        .room-name {
            font-size: 12pt;
            font-weight: bold;
        }
        
        .room-type {
            display: inline-block;
            padding: 1mm 4mm;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .room-type.cbt {
            background: #28a745;
            color: #fff;
        }
        
        .room-type.wawancara {
            background: #ffc107;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 2mm;
            text-align: left;
        }
        
        th {
            background: #333;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .col-no { width: 8mm; text-align: center; }
        .col-nomor-tes { width: 25mm; text-align: center; }
        .col-nama { width: auto; }
        .col-ttd { width: 35mm; }
        .col-keterangan { width: 35mm; }
        
        .ttd-box {
            height: 15mm;
        }
        
        .footer {
            margin-top: 5mm;
            display: flex;
            justify-content: space-between;
        }
        
        .footer-note {
            font-size: 8pt;
            color: #777;
        }
        
        .ttd-section {
            text-align: center;
            width: 60mm;
        }
        
        .ttd-line {
            margin-top: 20mm;
            border-top: 1px solid #333;
            padding-top: 2mm;
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
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-print:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Cetak Daftar Hadir</button>

    @foreach($ruangList as $ruang)
    <div class="page">
        <div class="header">
            <h1>DAFTAR HADIR PESERTA UJIAN</h1>
            <h2>{{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</h2>
            <div class="header-info">
                <span>Tahun Pelajaran: {{ $jadwal->tahunPelajaran->nama ?? '-' }}</span>
            </div>
        </div>
        
        <div class="room-info">
            <div>
                <span class="room-name">{{ $ruang['nama'] }}</span>
                <span class="room-type {{ $ruang['jenis'] }}">{{ strtoupper($ruang['jenis']) }}</span>
            </div>
            <div>
                <strong>Sesi {{ $ruang['sesi'] }}</strong> | 
                {{ $ruang['waktu_mulai'] }} - {{ $ruang['waktu_selesai'] }} |
                Peserta: {{ count($ruang['peserta']) }}/{{ $ruang['kapasitas'] }}
                @if(count($ruang['peserta']) > $ruang['kapasitas'])
                    <span style="color: #dc3545; font-weight: bold;"> (overflow +{{ count($ruang['peserta']) - $ruang['kapasitas'] }})</span>
                @endif
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">No. Tes</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-ttd">Tanda Tangan</th>
                    <th class="col-keterangan">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ruang['peserta'] as $idx => $peserta)
                <tr>
                    <td class="col-no">{{ $idx + 1 }}</td>
                    <td class="col-nomor-tes"><strong>{{ $peserta['nomor_tes'] }}</strong></td>
                    <td class="col-nama">{{ $peserta['nama'] }}</td>
                    <td class="ttd-box"></td>
                    <td></td>
                </tr>
                @endforeach
                
                @for($i = count($ruang['peserta']); $i < $ruang['kapasitas']; $i++)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nomor-tes">-</td>
                    <td class="col-nama">-</td>
                    <td class="ttd-box"></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>
        
        <div class="footer">
            <div class="footer-note">
                Hadir: ___ orang | Tidak Hadir: ___ orang<br>
                <small>Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}</small>
            </div>
            @php
                $petugasList = $room['penguji'] ?? collect();
                if ($room['jenis'] === 'cbt') {
                    $pengawas = is_object($petugasList) ? $petugasList->where('peran', 'pengawas')->first() : null;
                    $proktor = is_object($petugasList) ? $petugasList->where('peran', 'proktor')->first() : null;
                } else {
                    $pengujiWaw = is_object($petugasList) ? $petugasList->where('peran', 'penguji')->first() : null;
                    if (!$pengujiWaw && is_object($petugasList)) $pengujiWaw = $petugasList->first();
                }
            @endphp
            @if($room['jenis'] === 'cbt')
            <div style="display: flex; justify-content: flex-end; gap: 30mm;">
                <div class="ttd-section">
                    <div class="ttd-line">
                        Pengawas<br>
                        <strong>{{ $pengawas->user->name ?? '........................' }}</strong>
                    </div>
                </div>
                <div class="ttd-section">
                    <div class="ttd-line">
                        Proktor<br>
                        <strong>{{ $proktor->user->name ?? '........................' }}</strong>
                    </div>
                </div>
                <div class="ttd-section">
                    <div style="margin-bottom: 2mm; font-size: 8pt;">Mengetahui,</div>
                    <div class="ttd-line">
                        Ketua Panitia<br>
                        <strong>{{ (isset($ketuaPanitia) && $ketuaPanitia) ? $ketuaPanitia->name : '........................' }}</strong>
                    </div>
                </div>
            </div>
            @else
            <div style="display: flex; justify-content: flex-end; gap: 40mm;">
                <div class="ttd-section">
                    <div class="ttd-line">
                        Penguji<br>
                        <strong>{{ $pengujiWaw->user->name ?? '........................' }}</strong>
                    </div>
                </div>
                <div class="ttd-section">
                    <div style="margin-bottom: 2mm; font-size: 8pt;">Mengetahui,</div>
                    <div class="ttd-line">
                        Ketua Panitia<br>
                        <strong>{{ (isset($ketuaPanitia) && $ketuaPanitia) ? $ketuaPanitia->name : '........................' }}</strong>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</body>
</html>
