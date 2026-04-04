<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Registrasi Batch</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .page-break {
            page-break-after: always;
        }
        .page-break:last-child {
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .school-name {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
        }
        .school-address {
            font-size: 9pt;
            color: #666;
        }
        .title {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 4px;
        }
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-box {
            display: table-cell;
            width: 70%;
            vertical-align: top;
        }
        .foto-box {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }
        .foto-box img {
            width: 90px;
            height: 120px;
            object-fit: cover;
            border: 1px solid #333;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 8px;
            font-size: 10pt;
            border-bottom: 1px solid #ddd;
        }
        .info-table td:first-child {
            width: 35%;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            padding: 8px;
            background: #ecf0f1;
            margin: 10px 0 5px 0;
            border-left: 4px solid #3498db;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .qr-code {
            text-align: center;
            margin-top: 15px;
        }
        .nomor-box {
            background: #27ae60;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
@foreach($pendaftarList as $index => $calonSiswa)
<div class="{{ !$loop->last ? 'page-break' : '' }}">
    {{-- Header --}}
    <div class="header">
        <div class="school-name">{{ $settings['nama_sekolah'] ?? config('app.name') }}</div>
        <div class="school-address">{{ $settings['alamat_sekolah'] ?? '' }}</div>
    </div>

    {{-- Title --}}
    <div class="title">BUKTI PENDAFTARAN PESERTA DIDIK BARU</div>

    {{-- Nomor Registrasi --}}
    <div class="nomor-box">
        No. Registrasi: {{ $calonSiswa->nomor_registrasi ?? '-' }}
    </div>

    {{-- Info Container --}}
    <div class="info-container">
        <div class="info-box">
            <table class="info-table">
                <tr>
                    <td>Nama Lengkap</td>
                    <td><strong>{{ $calonSiswa->nama_lengkap }}</strong></td>
                </tr>
                <tr>
                    <td>NISN</td>
                    <td>{{ $calonSiswa->nisn ?? '-' }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>{{ $calonSiswa->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td>Asal Sekolah</td>
                    <td>{{ $calonSiswa->nama_sekolah_asal ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="foto-box">
            @if($calonSiswa->foto_profile && Storage::exists($calonSiswa->foto_profile))
                <img src="{{ Storage::url($calonSiswa->foto_profile) }}" alt="Foto">
            @else
                <div style="width: 90px; height: 120px; border: 1px solid #333; display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                    <span style="font-size: 8pt; color: #999;">Foto 3x4</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Data Pendaftaran --}}
    <div class="section-title">Data Pendaftaran</div>
    <table class="info-table">
        <tr>
            <td>Jalur Pendaftaran</td>
            <td>{{ $calonSiswa->jalurPendaftaran?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Gelombang</td>
            <td>{{ $calonSiswa->gelombangPendaftaran?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tahun Pelajaran</td>
            <td>{{ $calonSiswa->tahunPelajaran?->nama ?? '-' }}</td>
        </tr>
        @if($calonSiswa->jalurPendaftaran?->pilihan_program_aktif && $calonSiswa->pilihan_program)
        <tr>
            <td>Pilihan Program</td>
            <td>{{ $calonSiswa->pilihan_program }}</td>
        </tr>
        @endif
        <tr>
            <td>Nomor Tes</td>
            <td><strong>{{ $calonSiswa->nomor_tes ?? 'Belum digenerate' }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal Finalisasi</td>
            <td>{{ $calonSiswa->tanggal_finalisasi ? $calonSiswa->tanggal_finalisasi->format('d F Y, H:i') : '-' }}</td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Dicetak pada: {{ now()->format('d F Y, H:i') }} | Dokumen ini sah tanpa tanda tangan dan cap
    </div>
</div>
@endforeach
</body>
</html>
