<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Petugas Ujian - {{ $jadwal->tanggal_ujian->format('Y-m-d') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            padding: 5px 10px;
        }
        .page-break {
            page-break-after: always;
        }

        /* Kop Surat */
        .kop-wrapper {
            margin-bottom: 5px;
        }

        /* Title */
        .title-banner {
            text-align: center;
            padding: 6px 0;
            margin-bottom: 8px;
            border-bottom: 2px solid #333;
        }
        .title-banner h2 {
            font-size: 14px;
            margin-bottom: 2px;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .title-banner p {
            font-size: 10px;
            color: #555;
        }

        /* Info Box */
        .info-box {
            margin-bottom: 10px;
            font-size: 10px;
        }
        .info-box table {
            border: none;
            border-collapse: collapse;
        }
        .info-box td {
            border: none;
            padding: 1px 5px;
            vertical-align: top;
        }
        .info-box .label {
            font-weight: bold;
            width: 120px;
        }

        /* Section Header */
        .section-header {
            background: #2c3e50;
            color: #fff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
        }

        /* Table */
        table.petugas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        table.petugas th {
            background: #f0f0f0;
            color: #333;
            padding: 5px 6px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #333;
            text-align: center;
            vertical-align: middle;
        }
        table.petugas td {
            padding: 4px 6px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        table.petugas tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .col-no { width: 30px; text-align: center; }
        .col-nama { font-weight: bold; }
        .col-ruang { text-align: center; }
        .col-peran { text-align: center; font-weight: bold; }
        .col-user { font-family: 'Courier New', monospace; font-size: 10px; }
        .col-pass { font-family: 'Courier New', monospace; font-size: 10px; font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
        }
        .badge-penguji { background: #e67e22; }
        .badge-pengawas { background: #27ae60; }
        .badge-proktor { background: #2980b9; }
        .badge-ketua { background: #c0392b; }

        /* Cut line */
        .cut-line {
            border-top: 1px dashed #999;
            margin: 8px 0;
            position: relative;
        }
        .cut-line::before {
            content: '✂';
            position: absolute;
            top: -8px;
            left: 0;
            font-size: 12px;
            color: #999;
        }

        /* Individual Card */
        .card-petugas {
            border: 1px solid #333;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .card-header-petugas {
            background: #f0f0f0;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 1px solid #333;
        }
        .card-body-petugas {
            padding: 6px 8px;
        }
        .card-body-petugas table {
            border: none;
            border-collapse: collapse;
            width: 100%;
        }
        .card-body-petugas td {
            border: none;
            padding: 2px 4px;
            font-size: 10px;
        }
        .card-body-petugas .field-label {
            width: 100px;
            color: #666;
        }
        .card-body-petugas .field-value {
            font-weight: bold;
        }
        .card-body-petugas .credential {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background: #fffde7;
            padding: 1px 4px;
            border: 1px solid #e0e0e0;
        }

        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 7px;
            color: #6c757d;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px dashed #dee2e6;
        }

        /* Confidential warning */
        .confidential {
            text-align: center;
            padding: 4px;
            margin-bottom: 6px;
            border: 2px solid #c0392b;
            background: #fdf2f2;
            color: #c0392b;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>
    {{-- PAGE 1: Daftar Lengkap Semua Petugas --}}
    <div>
        {{-- Kop Surat --}}
        @if(isset($kopSurat))
        <div class="kop-wrapper">{!! $kopSurat !!}</div>
        @endif

        <div class="title-banner">
            <h2>DATA PETUGAS UJIAN</h2>
            <p>Tahun Pelajaran {{ $jadwal->tahunPelajaran?->nama ?? date('Y') }}</p>
        </div>

        <div class="confidential">
            ⚠ RAHASIA — Dokumen ini berisi kredensial login. Simpan dengan baik dan bagikan secara pribadi.
        </div>

        <div class="info-box">
            <table>
                <tr>
                    <td class="label">Tanggal Ujian</td>
                    <td>: {{ $jadwal->tanggal_ujian->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Ketua Panitia</td>
                    <td>: {{ $jadwal->ketuaPanitia->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Total Petugas</td>
                    <td>: {{ count($petugasList) }} orang</td>
                </tr>
            </table>
        </div>

        {{-- Penguji Wawancara --}}
        @if($pengujiWawancara->isNotEmpty())
        <div class="section-header">
            <i>★</i> PENGUJI WAWANCARA ({{ $pengujiWawancara->count() }} orang)
        </div>
        <table class="petugas">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Nama Lengkap</th>
                    <th>Ruang</th>
                    <th>Peran</th>
                    <th>Username</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengujiWawancara as $i => $p)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nama">{{ $p['nama'] }}</td>
                    <td class="col-ruang">{{ $p['ruang'] }}</td>
                    <td class="col-peran">
                        @if($p['is_ketua'])
                            <span class="badge badge-ketua">Ketua</span>
                        @endif
                        <span class="badge badge-penguji">Penguji</span>
                    </td>
                    <td class="col-user">{{ $p['username'] }}</td>
                    <td class="col-pass">{{ $p['password'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Pengawas CBT --}}
        @if($pengawasList->isNotEmpty())
        <div class="section-header">
            <i>★</i> PENGAWAS CBT ({{ $pengawasList->count() }} orang)
        </div>
        <table class="petugas">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Nama Lengkap</th>
                    <th>Ruang</th>
                    <th>Peran</th>
                    <th>Username</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengawasList as $i => $p)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nama">{{ $p['nama'] }}</td>
                    <td class="col-ruang">{{ $p['ruang'] }}</td>
                    <td class="col-peran"><span class="badge badge-pengawas">Pengawas</span></td>
                    <td class="col-user">{{ $p['username'] }}</td>
                    <td class="col-pass">{{ $p['password'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Proktor CBT --}}
        @if($proktorList->isNotEmpty())
        <div class="section-header">
            <i>★</i> PROKTOR CBT ({{ $proktorList->count() }} orang)
        </div>
        <table class="petugas">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Nama Lengkap</th>
                    <th>Ruang</th>
                    <th>Peran</th>
                    <th>Username</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proktorList as $i => $p)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nama">{{ $p['nama'] }}</td>
                    <td class="col-ruang">{{ $p['ruang'] }}</td>
                    <td class="col-peran"><span class="badge badge-proktor">Proktor</span></td>
                    <td class="col-user">{{ $p['username'] }}</td>
                    <td class="col-pass">{{ $p['password'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="footer-note">
            Dokumen Resmi — Sistem PPDB {{ $sekolah->nama_sekolah ?? '' }} | Dicetak: {{ now()->translatedFormat('d F Y H:i') }} | Halaman 1
        </div>
    </div>

    {{-- PAGE 2+: Kartu Kredensial Individual (untuk dibagikan per orang) --}}
    <div class="page-break"></div>
    <div>
        @if(isset($kopSurat))
        <div class="kop-wrapper">{!! $kopSurat !!}</div>
        @endif

        <div class="title-banner">
            <h2>KARTU KREDENSIAL PETUGAS</h2>
            <p>Potong sesuai garis putus-putus, bagikan kepada masing-masing petugas</p>
        </div>

        <div class="confidential">
            ⚠ RAHASIA — Potong dan bagikan kartu ini secara pribadi kepada masing-masing petugas.
        </div>

        @foreach($petugasList as $idx => $p)
            <div class="card-petugas">
                <div class="card-header-petugas">
                    @if($p['peran'] === 'penguji')
                        @if($p['is_ketua']) <span class="badge badge-ketua">Ketua</span> @endif
                        <span class="badge badge-penguji">Penguji Wawancara</span>
                    @elseif($p['peran'] === 'pengawas')
                        <span class="badge badge-pengawas">Pengawas CBT</span>
                    @elseif($p['peran'] === 'proktor')
                        <span class="badge badge-proktor">Proktor CBT</span>
                    @endif
                    &nbsp; {{ $p['ruang'] }} — Sesi {{ $p['sesi'] }}
                </div>
                <div class="card-body-petugas">
                    <table>
                        <tr>
                            <td class="field-label">Nama Lengkap</td>
                            <td class="field-value">: {{ $p['nama'] }}</td>
                        </tr>
                        <tr>
                            <td class="field-label">Ruang</td>
                            <td class="field-value">: {{ $p['ruang'] }}</td>
                        </tr>
                        <tr>
                            <td class="field-label">Tanggal</td>
                            <td>: {{ $jadwal->tanggal_ujian->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="field-label">Waktu</td>
                            <td>: {{ $p['waktu'] }}</td>
                        </tr>
                        <tr>
                            <td class="field-label">Username</td>
                            <td><span class="credential">{{ $p['username'] }}</span></td>
                        </tr>
                        <tr>
                            <td class="field-label">Password</td>
                            <td><span class="credential">{{ $p['password'] }}</span></td>
                        </tr>
                        <tr>
                            <td class="field-label">URL Login</td>
                            <td style="font-size: 9px;">: {{ config('app.url') }}/login</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if(!$loop->last)
                <div class="cut-line"></div>
            @endif

            @if(($idx + 1) % 4 === 0 && !$loop->last)
                <div class="footer-note">
                    Dokumen Resmi — Sistem PPDB {{ $sekolah->nama_sekolah ?? '' }} | Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
                </div>
                <div class="page-break"></div>
                @if(isset($kopSurat))
                <div class="kop-wrapper">{!! $kopSurat !!}</div>
                @endif
                <div class="title-banner">
                    <h2>KARTU KREDENSIAL PETUGAS</h2>
                    <p>Potong sesuai garis putus-putus, bagikan kepada masing-masing petugas</p>
                </div>
            @endif
        @endforeach

        <div class="footer-note">
            Dokumen Resmi — Sistem PPDB {{ $sekolah->nama_sekolah ?? '' }} | Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>
</body>
</html>
