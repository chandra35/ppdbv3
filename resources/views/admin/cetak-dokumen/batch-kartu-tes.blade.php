<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Tes Batch</title>
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
        .kartu-container {
            border: 2px solid #2c3e50;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .kartu-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
        }
        .kartu-header h2 {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .kartu-header p {
            font-size: 10pt;
            opacity: 0.9;
        }
        .kartu-body {
            padding: 15px;
            display: table;
            width: 100%;
        }
        .kartu-info {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }
        .kartu-foto {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            text-align: center;
        }
        .kartu-foto img {
            width: 100px;
            height: 130px;
            object-fit: cover;
            border: 2px solid #2c3e50;
            border-radius: 5px;
        }
        .foto-placeholder {
            width: 100px;
            height: 130px;
            border: 2px solid #2c3e50;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            font-size: 8pt;
            color: #999;
        }
        .nomor-tes {
            background: #e74c3c;
            color: white;
            padding: 12px 20px;
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 0;
            font-size: 10pt;
            border-bottom: 1px dashed #ddd;
        }
        .info-table td:first-child {
            width: 40%;
            color: #666;
        }
        .info-table td:last-child {
            font-weight: 600;
        }
        .kartu-footer {
            background: #f8f9fa;
            padding: 10px 15px;
            border-top: 1px solid #ddd;
        }
        .catatan {
            font-size: 8pt;
            color: #666;
        }
        .catatan ul {
            margin-left: 15px;
            margin-top: 5px;
        }
        .qr-container {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
@foreach($pendaftarList as $index => $calonSiswa)
<div class="{{ !$loop->last ? 'page-break' : '' }}">
    <div class="kartu-container">
        {{-- Header --}}
        <div class="kartu-header">
            <h2>{{ $settings['nama_sekolah'] ?? config('app.name') }}</h2>
            <p>KARTU PESERTA TES PPDB {{ $calonSiswa->tahunPelajaran?->nama ?? date('Y') }}</p>
        </div>

        {{-- Body --}}
        <div class="kartu-body">
            <div class="kartu-info">
                {{-- Nomor Tes --}}
                <div class="nomor-tes">
                    {{ $calonSiswa->nomor_tes }}
                </div>

                {{-- Info Table --}}
                <table class="info-table">
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>{{ $calonSiswa->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td>No. Registrasi</td>
                        <td>{{ $calonSiswa->nomor_registrasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>{{ $calonSiswa->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>{{ $calonSiswa->jalurPendaftaran?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Gelombang</td>
                        <td>{{ $calonSiswa->gelombangPendaftaran?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pilihan Program</td>
                        <td>{{ $calonSiswa->pilihan_program ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah</td>
                        <td>{{ $calonSiswa->nama_sekolah_asal ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="kartu-foto">
                @if($calonSiswa->foto_profile && Storage::exists($calonSiswa->foto_profile))
                    <img src="{{ Storage::url($calonSiswa->foto_profile) }}" alt="Foto">
                @else
                    <div class="foto-placeholder">
                        Foto 3x4
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="kartu-footer">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="catatan" style="flex: 1;">
                    <strong>Catatan Penting:</strong>
                    <ul>
                        <li>Kartu ini wajib dibawa saat mengikuti tes</li>
                        <li>Kartu ini tidak boleh diwakilkan</li>
                        <li>Peserta wajib hadir 30 menit sebelum tes dimulai</li>
                        <li>Membawa alat tulis (pensil 2B, penghapus, pulpen)</li>
                    </ul>
                </div>
                <div class="qr-container" style="text-align: center; margin-left: 15px;">
                    @php
                        $hash = $calonSiswa->getOrGenerateHash();
                        $verifyUrl = route('verify.bukti', $hash);
                        $qrSvg = QrCode::format('svg')->size(120)->margin(0)->generate($verifyUrl);
                        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
                    @endphp
                    <img src="{{ $qrBase64 }}" style="width: 60px; height: 60px;" alt="QR">
                    <div style="font-size: 7pt; color: #999; margin-top: 3px;">Scan untuk verifikasi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Cetak --}}
    <div style="text-align: center; font-size: 8pt; color: #999; margin-top: 10px;">
        Dicetak pada: {{ now()->format('d F Y, H:i') }}
    </div>
</div>
@endforeach
</body>
</html>
