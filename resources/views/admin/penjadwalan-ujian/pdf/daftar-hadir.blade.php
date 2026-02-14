<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Ujian - {{ $jadwal->tanggal_ujian->format('Y-m-d') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #333;
            padding: 5px 8px;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Kop Surat */
        .kop-wrapper {
            margin-bottom: 5px;
        }
        
        /* Title Banner */
        .title-banner {
            text-align: center;
            padding: 3px 0;
            margin-bottom: 5px;
            border-bottom: 2px solid #333;
        }
        .title-banner h2 {
            font-size: 13px;
            margin-bottom: 1px;
            letter-spacing: 1px;
            color: #333;
            font-weight: bold;
        }
        .title-banner p {
            font-size: 10px;
            color: #555;
        }
        
        /* Room Info Box */
        .room-info {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .room-info-cell {
            display: table-cell;
            padding: 2px 4px;
            vertical-align: middle;
            border-right: 1px solid #dee2e6;
        }
        .room-info-cell:last-child {
            border-right: none;
        }
        .room-info-label {
            font-size: 6px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .room-info-value {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        .room-info-value.cbt { color: #28a745; }
        .room-info-value.wawancara { color: #d39e00; }
        
        /* Table */
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 11px;
        }
        table.attendance th {
            background: #f0f0f0;
            color: #333;
            padding: 3px 2px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #333;
        }
        table.attendance td {
            padding: 2px 2px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        table.attendance tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.attendance .col-no { 
            width: 18px; 
            text-align: center;
            font-weight: bold;
        }
        table.attendance .col-nomor-tes { 
            width: 90px; 
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }
        table.attendance .col-nama { 
            width: 150px;
            max-width: 150px;
            text-align: left; 
            padding-left: 4px;
            font-size: 11px;
        }
        table.attendance .col-jk { 
            width: 18px; 
            text-align: center; 
        }
        table.attendance .col-asal { 
            width: 130px;
            max-width: 130px;
            font-size: 10px;
            padding: 2px;
        }
        /* TTD Zigzag: rowspan bergantian */
        table.attendance .col-ttd-left { 
            width: 30px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #333;
            padding: 0;
        }
        table.attendance .col-ttd-right { 
            width: 30px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #333;
            padding: 0;
        }
        /* Zigzag merged cell with rowspan */
        table.attendance .ttd-merged {
            background: #fff;
        }
        table.attendance .ttd-empty {
            background: #fff;
            border-left: none !important;
            border-right: none !important;
        }
        table.attendance .ttd-border-left {
            border-left: 1px solid #333 !important;
        }
        table.attendance .ttd-border-right {
            border-right: 1px solid #333 !important;
        }
        
        /* Summary */
        .summary-box {
            display: table;
            width: 100%;
            font-size: 8px;
            margin-bottom: 3px;
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
            padding: 2px 5px;
            margin: 1px;
            background: #e9ecef;
            border-radius: 2px;
        }
        
        /* Signature */
        .signature-box {
            display: table;
            width: 100%;
            margin-top: 5px;
            page-break-inside: avoid;
        }
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 3px;
        }
        .signature-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 28px;
        }
        .signature-note {
            font-size: 9px;
            color: #333;
            font-weight: bold;
        }
        .signature-underline {
            border-bottom: 1px solid #333;
            width: 140px;
            margin: 0 auto;
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
    @foreach($ruangList as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        {{-- Kop Surat --}}
        @if(isset($kopSurat))
        <div class="kop-wrapper">
            {!! $kopSurat !!}
        </div>
        @endif

        {{-- Title Banner --}}
        <div class="title-banner">
            <h2>DAFTAR HADIR {{ strtoupper($room['jenis']) }}</h2>
            <p>Tahun Pelajaran {{ $jadwal->tahunPelajaran?->nama ?? date('Y') }}</p>
        </div>

        {{-- Room Info --}}
        <div class="room-info">
            <div class="room-info-cell" style="width: 20%;">
                <div class="room-info-label">Nama Ruang</div>
                <div class="room-info-value">{{ $room['nama'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 15%;">
                <div class="room-info-label">Jenis Ujian</div>
                <div class="room-info-value {{ $room['jenis'] }}">{{ strtoupper($room['jenis']) }}</div>
            </div>
            <div class="room-info-cell" style="width: 10%;">
                <div class="room-info-label">Sesi</div>
                <div class="room-info-value">{{ $room['sesi'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 25%;">
                <div class="room-info-label">Tanggal</div>
                <div class="room-info-value">{{ $jadwal->tanggal_ujian->translatedFormat('d F Y') }}</div>
            </div>
            <div class="room-info-cell" style="width: 15%;">
                <div class="room-info-label">Waktu</div>
                <div class="room-info-value">{{ $room['waktu_mulai'] }} - {{ $room['waktu_selesai'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 15%;">
                <div class="room-info-label">Peserta</div>
                <div class="room-info-value">{{ count($room['peserta']) }} Orang</div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <table class="attendance">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">No. Tes</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-jk">JK</th>
                    <th class="col-asal">Asal Sekolah</th>
                    <th class="col-ttd-left" colspan="2" style="text-align: center;">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @php $total = count($room['peserta']); @endphp
                @foreach($room['peserta'] as $index => $pesertaRuang)
                @php 
                    $nomor = $index + 1;
                    $isGanjil = $nomor % 2 == 1;
                    $isFirst = $nomor == 1;
                    $isLast = $nomor == $total;
                @endphp
                <tr>
                    <td class="col-no">{{ $nomor }}</td>
                    <td class="col-nomor-tes">{{ $pesertaRuang->calonSiswa->nomor_tes ?? '-' }}</td>
                    <td class="col-nama">{{ $pesertaRuang->calonSiswa->nama_lengkap ?? '-' }}</td>
                    <td class="col-jk">{{ ($pesertaRuang->calonSiswa->jenis_kelamin ?? '') == 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="col-asal">{{ $pesertaRuang->calonSiswa->nama_sekolah_asal ?? '-' }}</td>
                    {{-- TTD Zigzag dengan rowspan=2: ganjil di kiri, genap di kanan --}}
                    @if($isGanjil)
                        {{-- Baris ganjil: TTD di KIRI dengan rowspan --}}
                        <td class="col-ttd-left ttd-merged" @if(!$isLast) rowspan="2" @endif style="vertical-align: top; text-align: left; padding: 1px 0 0 1px;"><span style="font-size: 10px; color: #bbb;">{{ $nomor }}</span></td>
                        @if($isFirst)
                            {{-- Baris pertama: TTD kanan kosong --}}
                            <td class="col-ttd-right"></td>
                        @endif
                    @else
                        {{-- Baris genap: TTD di KANAN dengan rowspan --}}
                        <td class="col-ttd-right ttd-merged" @if(!$isLast) rowspan="2" @endif style="vertical-align: top; text-align: right; padding: 1px 1px 0 0;"><span style="font-size: 10px; color: #bbb;">{{ $nomor }}</span></td>
                        @if($isLast && $total % 2 == 0)
                            {{-- Baris terakhir genap: TTD kiri kosong di bawahnya --}}
                        @endif
                    @endif
                </tr>
                @endforeach
                {{-- Jika total genap, tambah baris kosong untuk melengkapi rowspan terakhir --}}
                @if($total % 2 == 0)
                <tr>
                    <td colspan="5" style="border: none; height: 0;"></td>
                    {{-- TTD kanan sudah di-merge dari baris sebelumnya --}}
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Signature --}}
        @php
            $petugasList = $room['penguji'] ?? collect();
            if ($room['jenis'] === 'cbt') {
                $pengawas = $petugasList->where('peran', 'pengawas')->first();
                $proktor = $petugasList->where('peran', 'proktor')->first();
            } else {
                $pengujiWaw = $petugasList->where('peran', 'penguji')->first();
                // Fallback: if no peran set, use first entry
                if (!$pengujiWaw) $pengujiWaw = $petugasList->first();
            }
        @endphp
        <div class="signature-box">
            @if($room['jenis'] === 'cbt')
            <div class="signature-cell">
                <div class="signature-title">Pengawas</div>
                <div class="signature-note">{{ $pengawas->user->name ?? '........................' }}</div>
                <div class="signature-underline"></div>
            </div>
            <div class="signature-cell">
                <div class="signature-title">Proktor</div>
                <div class="signature-note">{{ $proktor->user->name ?? '........................' }}</div>
                <div class="signature-underline"></div>
            </div>
            @else
            <div class="signature-cell">
                <div class="signature-title">Penguji</div>
                <div class="signature-note">{{ $pengujiWaw->user->name ?? '........................' }}</div>
                <div class="signature-underline"></div>
            </div>
            <div class="signature-cell">
                {{-- Empty cell for alignment --}}
            </div>
            @endif
            <div class="signature-cell">
                <div class="signature-title">Mengetahui,<br>Ketua Panitia</div>
                <div class="signature-note">{{ (isset($ketuaPanitia) && $ketuaPanitia) ? $ketuaPanitia->name : '........................' }}</div>
                <div class="signature-underline"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer-note">
            <div>Dokumen Resmi dicetak dari Sistem PPDB {{ $sekolah->nama ?? '' }}</div>
            <div>{{ $room['nama'] }} | Sesi {{ $room['sesi'] }} {{ strtoupper($room['jenis']) }} | Dicetak: {{ now()->format('d/m/Y H:i') }} | Halaman {{ $roomIndex + 1 }} dari {{ count($ruangList) }}</div>
        </div>
    </div>
    @endforeach
</body>
</html>
