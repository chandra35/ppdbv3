<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PPDB {{ $selectedTahun?->nama ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .page-header { text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 15px; }
        .page-header h1 { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
        .page-header h2 { font-size: 12px; font-weight: normal; color: #555; margin-top: 3px; }
        .page-header .sekolah-name { font-size: 14px; font-weight: bold; color: #2c3e50; }
        .section { margin-bottom: 12px; }
        .section-title { font-size: 11px; font-weight: bold; background: #2c3e50; color: #fff; padding: 4px 8px; margin-bottom: 6px; }
        .sub-section-title { font-size: 10px; font-weight: bold; background: #34495e; color: #fff; padding: 3px 8px; margin-bottom: 4px; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table th, table td { border: 1px solid #ddd; padding: 3px 5px; text-align: left; font-size: 9px; }
        table th { background: #ecf0f1; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .stat-grid { width: 100%; margin-bottom: 8px; }
        .stat-grid td { border: none; padding: 2px 4px; width: 16.6%; }
        .stat-box { border: 1px solid #bdc3c7; border-radius: 3px; padding: 6px; text-align: center; }
        .stat-box .number { font-size: 16px; font-weight: bold; color: #2c3e50; }
        .stat-box .label { font-size: 7px; color: #7f8c8d; text-transform: uppercase; }
        .stat-box.primary { border-left: 3px solid #3498db; }
        .stat-box.success { border-left: 3px solid #27ae60; }
        .stat-box.warning { border-left: 3px solid #f39c12; }
        .stat-box.purple { border-left: 3px solid #8e44ad; }
        .stat-box.olive { border-left: 3px solid #6b8e23; }
        .stat-box.danger { border-left: 3px solid #e74c3c; }
        .filter-info { background: #eaf2f8; border: 1px solid #aed6f1; padding: 5px 8px; margin-bottom: 10px; font-size: 9px; border-radius: 3px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
        .three-col { width: 100%; }
        .three-col td { border: none; vertical-align: top; width: 33.3%; padding: 0 3px; }
        .two-col { width: 100%; }
        .two-col td { border: none; vertical-align: top; width: 50%; padding: 0 4px 0 0; }
        .two-col td:last-child { padding: 0 0 0 4px; }
        .page-break { page-break-before: always; }
        .row-total { background: #d5f5e3; font-weight: bold; }
        .row-warning { background: #fef9e7; }
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
        @if($selectedJalur) &nbsp;|&nbsp; Jalur: {{ $selectedJalur->nama }} @endif
        @if($selectedGelombang) &nbsp;|&nbsp; Gelombang: {{ $selectedGelombang->nama }} @endif
        &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

    {{-- Statistik Ringkasan --}}
    <div class="section">
        <div class="section-title">Ringkasan Statistik</div>
        <table class="stat-grid">
            <tr>
                <td><div class="stat-box primary"><div class="number">{{ number_format($stats['total']) }}</div><div class="label">Total Pendaftar</div></div></td>
                <td><div class="stat-box success"><div class="number">{{ number_format($stats['dapat_nomor_tes']) }}</div><div class="label">Dapat Nomor Tes</div></div></td>
                <td><div class="stat-box warning"><div class="number">{{ number_format($stats['tidak_dapat_nomor_tes']) }}</div><div class="label">Tanpa Nomor Tes</div></div></td>
                <td><div class="stat-box purple"><div class="number">{{ number_format($stats['ikut_tes']) }}</div><div class="label">Mengikuti Tes</div></div></td>
                <td><div class="stat-box olive"><div class="number">{{ number_format($stats['lulus_total']) }}</div><div class="label">Lulus</div></div></td>
                <td><div class="stat-box danger"><div class="number">{{ number_format($stats['tidak_lulus_total']) }}</div><div class="label">Tidak Lulus</div></div></td>
            </tr>
        </table>
    </div>

    {{-- ============================== --}}
    {{-- SECTION 1: TOTAL PENDAFTAR --}}
    {{-- ============================== --}}
    @include('admin.report._pdf_section', [
        'sectionTitle' => '1. TOTAL PENDAFTAR',
        'data' => $stats['total_pendaftar'],
    ])

    {{-- ============================== --}}
    {{-- SECTION 2: MENDAPAT NOMOR TES --}}
    {{-- ============================== --}}
    @include('admin.report._pdf_section', [
        'sectionTitle' => '2. YANG MENDAPAT NOMOR TES',
        'data' => $stats['dengan_nomor_tes'],
    ])

    <div class="page-break"></div>

    {{-- ============================== --}}
    {{-- SECTION 3: TIDAK MENDAPAT NOMOR TES --}}
    {{-- ============================== --}}
    @include('admin.report._pdf_section', [
        'sectionTitle' => '3. YANG TIDAK MENDAPAT NOMOR TES',
        'data' => $stats['tanpa_nomor_tes'],
    ])

    {{-- ============================== --}}
    {{-- SECTION 4: MENGIKUTI TES --}}
    {{-- ============================== --}}
    @include('admin.report._pdf_section', [
        'sectionTitle' => '4. YANG MENGIKUTI TES (CBT / TBQ)',
        'data' => $stats['peserta_tes'],
    ])

    <div class="page-break"></div>

    {{-- ============================== --}}
    {{-- SECTION 5: KELULUSAN --}}
    {{-- ============================== --}}
    <div class="section">
        <div class="section-title">5. KELULUSAN AKHIR</div>

        <div class="sub-section-title">5a. LULUS ({{ $stats['lulus_total'] }} orang)</div>
        @include('admin.report._pdf_section_tables', ['data' => $stats['kelulusan']])

        <div class="sub-section-title">5b. TIDAK LULUS ({{ $stats['tidak_lulus_total'] }} orang)</div>
        @include('admin.report._pdf_section_tables', ['data' => $stats['kelulusan_tidak_lulus']])

        <div class="sub-section-title">5c. CADANGAN ({{ $stats['cadangan_total'] }} orang)</div>
        @include('admin.report._pdf_section_tables', ['data' => $stats['kelulusan_cadangan']])
    </div>

    <div class="page-break"></div>

    {{-- ============================== --}}
    {{-- STATISTIK TAMBAHAN --}}
    {{-- ============================== --}}
    <div class="section">
        <div class="section-title">STATISTIK TAMBAHAN</div>

        {{-- Per Jalur --}}
        <table>
            <tr>
                <th>Jalur Pendaftaran</th>
                <th class="text-center">Total</th>
                <th class="text-center">Laki-laki</th>
                <th class="text-center">Perempuan</th>
                <th class="text-center">Finalisasi</th>
                <th class="text-center">Nomor Tes</th>
            </tr>
            @foreach($stats['per_jalur'] as $nama => $dj)
            <tr>
                <td class="bold">{{ $nama }}</td>
                <td class="text-center">{{ $dj['total'] }}</td>
                <td class="text-center">{{ $dj['laki_laki'] }}</td>
                <td class="text-center">{{ $dj['perempuan'] }}</td>
                <td class="text-center">{{ $dj['finalisasi'] }}</td>
                <td class="text-center">{{ $dj['nomor_tes'] }}</td>
            </tr>
            @endforeach
        </table>

        {{-- Per Gelombang & Status --}}
        <table class="two-col">
            <tr>
                <td>
                    <table>
                        <tr><th>Gelombang</th><th class="text-center">Total</th><th class="text-center">L</th><th class="text-center">P</th></tr>
                        @foreach($stats['per_gelombang'] as $nama => $dg)
                        <tr><td>{{ $nama }}</td><td class="text-center bold">{{ $dg['total'] }}</td><td class="text-center">{{ $dg['laki_laki'] }}</td><td class="text-center">{{ $dg['perempuan'] }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td>
                    <table>
                        <tr><th>Status Verifikasi</th><th class="text-center">Jumlah</th></tr>
                        <tr><td>Pending</td><td class="text-center bold">{{ $stats['status_verifikasi']['pending'] }}</td></tr>
                        <tr><td>Terverifikasi</td><td class="text-center bold">{{ $stats['status_verifikasi']['verified'] }}</td></tr>
                        <tr><td>Ditolak</td><td class="text-center bold">{{ $stats['status_verifikasi']['rejected'] }}</td></tr>
                        <tr><td>Revisi</td><td class="text-center bold">{{ $stats['status_verifikasi']['revisi'] }}</td></tr>
                    </table>
                    <table>
                        <tr><th>Status Admisi</th><th class="text-center">Jumlah</th></tr>
                        <tr><td>Diterima</td><td class="text-center bold">{{ $stats['status_admisi']['diterima'] }}</td></tr>
                        <tr><td>Cadangan</td><td class="text-center bold">{{ $stats['status_admisi']['cadangan'] }}</td></tr>
                        <tr><td>Ditolak</td><td class="text-center bold">{{ $stats['status_admisi']['ditolak'] }}</td></tr>
                        <tr><td>Pending</td><td class="text-center bold">{{ $stats['status_admisi']['pending'] }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Sebaran Wilayah --}}
    <div class="section">
        <div class="section-title">SEBARAN WILAYAH</div>
        <table class="two-col">
            <tr>
                <td>
                    <table>
                        <tr><th width="5%">No</th><th>Kabupaten / Kota</th><th class="text-center" width="15%">Jumlah</th><th class="text-center" width="15%">%</th></tr>
                        @foreach($stats['sebaran_kabupaten'] as $nama => $jumlah)
                        <tr><td class="text-center">{{ $loop->iteration }}</td><td>{{ $nama }}</td><td class="text-center bold">{{ $jumlah }}</td><td class="text-center">{{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%</td></tr>
                        @endforeach
                        @if($stats['sebaran_kabupaten']->isEmpty())
                        <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
                        @endif
                    </table>
                </td>
                <td>
                    <table>
                        <tr><th width="5%">No</th><th>Kecamatan (Top 20)</th><th class="text-center" width="15%">Jumlah</th><th class="text-center" width="15%">%</th></tr>
                        @foreach($stats['sebaran_kecamatan'] as $nama => $jumlah)
                        <tr><td class="text-center">{{ $loop->iteration }}</td><td>{{ $nama }}</td><td class="text-center bold">{{ $jumlah }}</td><td class="text-center">{{ $stats['total'] > 0 ? round($jumlah / $stats['total'] * 100, 1) : 0 }}%</td></tr>
                        @endforeach
                        @if($stats['sebaran_kecamatan']->isEmpty())
                        <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Sebaran Sekolah Asal --}}
    @if(count($stats['sebaran_sekolah']) > 0)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">SEBARAN SEKOLAH ASAL ({{ count($stats['sebaran_sekolah']) }} Sekolah)</div>
        <table>
            <tr>
                <th width="4%">No</th>
                <th>Nama Sekolah</th>
                <th class="text-center" width="8%">NPSN</th>
                <th class="text-center" width="8%">Bentuk</th>
                <th class="text-center" width="8%">Status</th>
                <th class="text-center" width="8%">Total</th>
                <th class="text-center" width="6%">L</th>
                <th class="text-center" width="6%">P</th>
                <th class="text-center" width="7%">%</th>
            </tr>
            @foreach($stats['sebaran_sekolah'] as $idx => $sk)
            <tr @if($idx < 3) style="background: #fef9e7;" @endif>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="bold">{{ $sk['nama'] }}</td>
                <td class="text-center">{{ $sk['npsn'] }}</td>
                <td class="text-center">{{ $sk['bentuk'] }}</td>
                <td class="text-center">{{ $sk['status'] }}</td>
                <td class="text-center bold">{{ $sk['total'] }}</td>
                <td class="text-center">{{ $sk['l'] }}</td>
                <td class="text-center">{{ $sk['p'] }}</td>
                <td class="text-center">{{ $stats['total'] > 0 ? round($sk['total'] / $stats['total'] * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Dokumen Resmi &mdash; Sistem PPDB {{ $sekolah?->nama_sekolah ?? '' }} | Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

</body>
</html>
