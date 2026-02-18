<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PPDB {{ $selectedTahun?->nama ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .page-header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .page-header h1 {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .page-header h2 {
            font-size: 12px;
            font-weight: normal;
            color: #555;
            margin-top: 3px;
        }
        .page-header .sekolah-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #2c3e50;
            color: #fff;
            padding: 5px 10px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
        }
        table th {
            background: #ecf0f1;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        table td {
            font-size: 9px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .stat-grid {
            width: 100%;
            margin-bottom: 10px;
        }
        .stat-grid td {
            border: none;
            padding: 3px 5px;
            width: 25%;
        }
        .stat-box {
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-box .label {
            font-size: 8px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .stat-box.primary { border-left: 4px solid #3498db; }
        .stat-box.success { border-left: 4px solid #27ae60; }
        .stat-box.warning { border-left: 4px solid #f39c12; }
        .stat-box.purple { border-left: 4px solid #8e44ad; }
        .bar-container {
            background: #ecf0f1;
            height: 12px;
            border-radius: 3px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            border-radius: 3px;
        }
        .bar-blue { background: #3498db; }
        .bar-red { background: #e74c3c; }
        .bar-green { background: #27ae60; }
        .bar-orange { background: #f39c12; }
        .filter-info {
            background: #eaf2f8;
            border: 1px solid #aed6f1;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 9px;
            border-radius: 3px;
        }
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .two-col {
            width: 100%;
        }
        .two-col td {
            border: none;
            vertical-align: top;
            width: 50%;
            padding: 0 5px 0 0;
        }
        .two-col td:last-child {
            padding: 0 0 0 5px;
        }
        .bold { font-weight: bold; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="page-header">
        @if($sekolah)
            <div class="sekolah-name">{{ strtoupper($sekolah->nama_sekolah ?? 'SEKOLAH') }}</div>
        @endif
        <h1>Laporan Penerimaan Peserta Didik Baru (PPDB)</h1>
        <h2>Tahun Pelajaran {{ $selectedTahun?->nama ?? '-' }}</h2>
    </div>

    {{-- Filter Info --}}
    <div class="filter-info">
        <strong>Filter:</strong>
        Tahun Pelajaran: {{ $selectedTahun?->nama ?? 'Semua' }}
        @if($selectedJalur)
            &nbsp;|&nbsp; Jalur: {{ $selectedJalur->nama }}
        @endif
        @if($selectedGelombang)
            &nbsp;|&nbsp; Gelombang: {{ $selectedGelombang->nama }}
        @endif
        &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

    {{-- Statistik Utama --}}
    <div class="section">
        <div class="section-title">Ringkasan Statistik</div>
        <table class="stat-grid">
            <tr>
                <td>
                    <div class="stat-box primary">
                        <div class="number">{{ number_format($stats['total']) }}</div>
                        <div class="label">Total Pendaftar</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box success">
                        <div class="number">{{ number_format($stats['dapat_nomor_tes']) }}</div>
                        <div class="label">Mendapat Nomor Tes</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box warning">
                        <div class="number">{{ number_format($stats['finalisasi']) }}</div>
                        <div class="label">Sudah Finalisasi</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box purple">
                        <div class="number">{{ number_format($stats['ikut_tes']) }}</div>
                        <div class="label">Mengikuti Tes</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Jenis Kelamin & Peserta Tes --}}
    <table class="two-col">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Jenis Kelamin</div>
                    <table>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Persentase</th>
                        </tr>
                        <tr>
                            <td>Laki-laki</td>
                            <td class="text-center bold">{{ $stats['jenis_kelamin']['laki_laki'] }}</td>
                            <td class="text-center">{{ $stats['total'] > 0 ? round($stats['jenis_kelamin']['laki_laki'] / $stats['total'] * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td>Perempuan</td>
                            <td class="text-center bold">{{ $stats['jenis_kelamin']['perempuan'] }}</td>
                            <td class="text-center">{{ $stats['total'] > 0 ? round($stats['jenis_kelamin']['perempuan'] / $stats['total'] * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr style="background: #ecf0f1;">
                            <td class="bold">Total</td>
                            <td class="text-center bold">{{ $stats['total'] }}</td>
                            <td class="text-center bold">100%</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title">Peserta Tes</div>
                    <table>
                        <tr>
                            <th>Jenis Tes</th>
                            <th class="text-center">Jumlah</th>
                        </tr>
                        <tr>
                            <td>Tes Baca Tulis Qur'an (TBQ)</td>
                            <td class="text-center bold">{{ $stats['ikut_tbq'] }}</td>
                        </tr>
                        <tr>
                            <td>Computer Based Test (CBT)</td>
                            <td class="text-center bold">{{ $stats['ikut_cbt'] }}</td>
                        </tr>
                        <tr style="background: #ecf0f1;">
                            <td class="bold">Total Peserta Tes (Unik)</td>
                            <td class="text-center bold">{{ $stats['ikut_tes'] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Per Jalur --}}
    <div class="section">
        <div class="section-title">Statistik Per Jalur Pendaftaran</div>
        <table>
            <tr>
                <th>Jalur</th>
                <th class="text-center">Total</th>
                <th class="text-center">Laki-laki</th>
                <th class="text-center">Perempuan</th>
                <th class="text-center">Finalisasi</th>
                <th class="text-center">Nomor Tes</th>
            </tr>
            @foreach($stats['per_jalur'] as $nama => $data)
            <tr>
                <td class="bold">{{ $nama }}</td>
                <td class="text-center">{{ $data['total'] }}</td>
                <td class="text-center">{{ $data['laki_laki'] }}</td>
                <td class="text-center">{{ $data['perempuan'] }}</td>
                <td class="text-center">{{ $data['finalisasi'] }}</td>
                <td class="text-center">{{ $data['nomor_tes'] }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Per Gelombang & Pilihan Program --}}
    <table class="two-col">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Per Gelombang</div>
                    <table>
                        <tr>
                            <th>Gelombang</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                        </tr>
                        @foreach($stats['per_gelombang'] as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td class="text-center bold">{{ $data['total'] }}</td>
                            <td class="text-center">{{ $data['laki_laki'] }}</td>
                            <td class="text-center">{{ $data['perempuan'] }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title">Pilihan Program</div>
                    <table>
                        <tr>
                            <th>Program</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">L</th>
                            <th class="text-center">P</th>
                        </tr>
                        @foreach($stats['pilihan_program'] as $nama => $data)
                        <tr>
                            <td>{{ $nama }}</td>
                            <td class="text-center bold">{{ $data['total'] }}</td>
                            <td class="text-center">{{ $data['laki_laki'] }}</td>
                            <td class="text-center">{{ $data['perempuan'] }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Status --}}
    <table class="two-col">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Status Verifikasi</div>
                    <table>
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Jumlah</th>
                        </tr>
                        <tr><td>Pending</td><td class="text-center bold">{{ $stats['status_verifikasi']['pending'] }}</td></tr>
                        <tr><td>Terverifikasi</td><td class="text-center bold">{{ $stats['status_verifikasi']['verified'] }}</td></tr>
                        <tr><td>Ditolak</td><td class="text-center bold">{{ $stats['status_verifikasi']['rejected'] }}</td></tr>
                        <tr><td>Perlu Revisi</td><td class="text-center bold">{{ $stats['status_verifikasi']['revisi'] }}</td></tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title">Status Admisi</div>
                    <table>
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Jumlah</th>
                        </tr>
                        <tr><td>Diterima</td><td class="text-center bold">{{ $stats['status_admisi']['diterima'] }}</td></tr>
                        <tr><td>Cadangan</td><td class="text-center bold">{{ $stats['status_admisi']['cadangan'] }}</td></tr>
                        <tr><td>Ditolak</td><td class="text-center bold">{{ $stats['status_admisi']['ditolak'] }}</td></tr>
                        <tr><td>Pending</td><td class="text-center bold">{{ $stats['status_admisi']['pending'] }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- Sebaran Wilayah Kabupaten --}}
    <div class="section">
        <div class="section-title">Sebaran Wilayah (Kabupaten/Kota)</div>
        <table>
            <tr>
                <th width="5%">No</th>
                <th>Kabupaten / Kota</th>
                <th class="text-center" width="15%">Jumlah</th>
                <th class="text-center" width="15%">Persentase</th>
            </tr>
            @foreach($stats['sebaran_kabupaten'] as $nama => $jumlah)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $nama }}</td>
                <td class="text-center bold">{{ $jumlah }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            @if($stats['sebaran_kabupaten']->isEmpty())
            <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
            @endif
        </table>
    </div>

    {{-- Sebaran Kecamatan Top 20 --}}
    <div class="section">
        <div class="section-title">Sebaran Wilayah (Kecamatan) - Top 20</div>
        <table>
            <tr>
                <th width="5%">No</th>
                <th>Kecamatan</th>
                <th class="text-center" width="15%">Jumlah</th>
                <th class="text-center" width="15%">Persentase</th>
            </tr>
            @foreach($stats['sebaran_kecamatan'] as $nama => $jumlah)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $nama }}</td>
                <td class="text-center bold">{{ $jumlah }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            @if($stats['sebaran_kecamatan']->isEmpty())
            <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
            @endif
        </table>
    </div>

    {{-- Sebaran Asal Sekolah Top 20 --}}
    <div class="section">
        <div class="section-title">Sebaran Asal Sekolah - Top 20</div>
        <table>
            <tr>
                <th width="5%">No</th>
                <th>Nama Sekolah Asal</th>
                <th class="text-center" width="15%">Jumlah</th>
                <th class="text-center" width="15%">Persentase</th>
            </tr>
            @foreach($stats['sebaran_sekolah'] as $nama => $jumlah)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $nama }}</td>
                <td class="text-center bold">{{ $jumlah }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            @if($stats['sebaran_sekolah']->isEmpty())
            <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Dokumen Resmi &mdash; Sistem PPDB {{ $sekolah?->nama_sekolah ?? '' }} | Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

</body>
</html>
