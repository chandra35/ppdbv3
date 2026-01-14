<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Ruang Ujian</title>
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
            font-size: 11px;
            line-height: 1.4;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 3px;
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
        .room-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .room-info table {
            width: 100%;
        }
        .room-info td {
            padding: 3px 5px;
        }
        .room-info .label {
            font-weight: bold;
            width: 150px;
        }
        .room-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.attendance th, 
        table.attendance td {
            border: 1px solid #333;
            padding: 6px 4px;
            text-align: center;
        }
        table.attendance th {
            background: #e9e9e9;
            font-weight: bold;
            font-size: 10px;
        }
        table.attendance td {
            font-size: 10px;
        }
        table.attendance .col-no { width: 30px; }
        table.attendance .col-nomor-tes { width: 100px; }
        table.attendance .col-nama { width: auto; text-align: left; padding-left: 8px; }
        table.attendance .col-ttd { width: 80px; }
        table.attendance .col-ket { width: 70px; }
        .footer {
            margin-top: 20px;
        }
        .footer table {
            width: 100%;
        }
        .footer td {
            vertical-align: top;
            padding: 5px;
        }
        .ttd-box {
            text-align: center;
        }
        .ttd-space {
            height: 60px;
        }
        .ttd-line {
            border-bottom: 1px solid #333;
            width: 150px;
            margin: 0 auto;
        }
        .summary {
            margin-top: 10px;
            font-size: 10px;
        }
        .summary td {
            padding: 2px 5px;
        }
    </style>
</head>
<body>
    @foreach($rooms as $roomIndex => $room)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        {{-- Header --}}
        <div class="header">
            @if($sekolah)
            <h1>{{ strtoupper($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') }}</h1>
            <h2>Penerimaan Peserta Didik Baru (PPDB) {{ $tahunAktif?->nama ?? date('Y') }}</h2>
            <p>{{ $sekolah->alamat ?? '' }} {{ $sekolah->telepon ? '| Telp: '.$sekolah->telepon : '' }}</p>
            @else
            <h1>DAFTAR HADIR PESERTA UJIAN</h1>
            <h2>PPDB Tahun {{ $tahunAktif?->nama ?? date('Y') }}</h2>
            @endif
        </div>

        {{-- Room Title --}}
        <div class="room-title">
            DAFTAR HADIR - {{ strtoupper($room['nama']) }}
        </div>

        {{-- Room Info --}}
        <div class="room-info">
            <table>
                <tr>
                    <td class="label">Nama Ruang</td>
                    <td>: {{ $room['nama'] }}</td>
                    <td class="label">Jumlah Peserta</td>
                    <td>: {{ $room['jumlah'] }} orang</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Ujian</td>
                    <td>: ...................................</td>
                    <td class="label">Waktu</td>
                    <td>: ................... - ..................</td>
                </tr>
            </table>
        </div>

        {{-- Attendance Table --}}
        <table class="attendance">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nomor-tes">Nomor Tes</th>
                    <th class="col-nama">Nama Peserta</th>
                    <th class="col-ttd">Tanda Tangan</th>
                    <th class="col-ket">Ket.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $peserta)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $peserta->nomor_tes }}</td>
                    <td class="col-nama">{{ $peserta->nama_lengkap }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <table class="summary">
            <tr>
                <td width="50%">Keterangan: H = Hadir, S = Sakit, I = Izin, A = Alpa</td>
                <td width="50%" style="text-align: right;">
                    Hadir: ........ | Tidak Hadir: ........
                </td>
            </tr>
        </table>

        {{-- Footer with Signatures --}}
        <div class="footer">
            <table>
                <tr>
                    <td width="33%">
                        <div class="ttd-box">
                            <p>Pengawas 1</p>
                            <div class="ttd-space"></div>
                            <div class="ttd-line"></div>
                            <p style="font-size: 9px;">NIP: ...........................</p>
                        </div>
                    </td>
                    <td width="34%">
                        <div class="ttd-box">
                            <p>Pengawas 2</p>
                            <div class="ttd-space"></div>
                            <div class="ttd-line"></div>
                            <p style="font-size: 9px;">NIP: ...........................</p>
                        </div>
                    </td>
                    <td width="33%">
                        <div class="ttd-box">
                            <p>Mengetahui,<br>Ketua Panitia</p>
                            <div class="ttd-space"></div>
                            <div class="ttd-line"></div>
                            <p style="font-size: 9px;">NIP: ...........................</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>
