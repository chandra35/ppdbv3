<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lembar Penilaian - {{ $jadwal->tanggal_ujian->format('Y-m-d') }}</title>
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
            padding: 2px 6px;
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

        /* Table */
        table.penilaian {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 9px;
        }
        table.penilaian th {
            background: #f0f0f0;
            color: #333;
            padding: 3px 2px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #333;
            text-align: center;
            vertical-align: middle;
        }
        table.penilaian td {
            padding: 2px 3px;
            border: 1px solid #333;
            vertical-align: middle;
            text-align: center;
            font-size: 9px;
        }
        table.penilaian tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.penilaian .col-urut {
            width: 28px;
        }
        table.penilaian .col-psrta {
            width: 70px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        table.penilaian td.col-psrta {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: bold;
        }
        table.penilaian .col-nama {
            width: 150px;
            max-width: 150px;
            text-align: left;
            padding-left: 5px;
        }
        table.penilaian td.col-nama {
            text-align: left;
            padding-left: 5px;
            font-size: 9px;
        }
        table.penilaian .col-nilai {
            width: 42px;
        }
        table.penilaian .col-asal {
            width: 90px;
            font-size: 8px;
            text-align: left;
            padding-left: 4px;
        }
        table.penilaian td.col-asal {
            text-align: left;
            padding-left: 4px;
            font-size: 8px;
        }

        /* Keterangan */
        .keterangan {
            font-size: 8px;
            line-height: 1.4;
        }
        .keterangan .ket-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 1px;
        }
        .keterangan .section-title {
            font-weight: bold;
        }
        .keterangan table {
            border: none;
            width: 100%;
        }
        .keterangan table td {
            border: none;
            padding: 0 4px;
            font-size: 8px;
            vertical-align: top;
        }

        /* Signature */
        .signature-cell {
            text-align: center;
            vertical-align: top;
            padding-top: 4px;
        }
        .signature-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 40px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 150px;
            margin: 0 auto 2px auto;
        }
        .signature-nip {
            font-size: 9px;
        }

        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 7px;
            color: #6c757d;
            margin-top: 4px;
            padding-top: 3px;
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
            <h2>LEMBAR PENILAIAN</h2>
            <p>Tahun Pelajaran {{ $jadwal->tahunPelajaran?->nama ?? date('Y') }}</p>
        </div>

        {{-- Room Info Box --}}
        <div class="room-info">
            <div class="room-info-cell" style="width: 18%;">
                <div class="room-info-label">Nama Ruang</div>
                <div class="room-info-value">{{ $room['nama'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 8%;">
                <div class="room-info-label">Sesi</div>
                <div class="room-info-value">{{ $room['sesi'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 18%;">
                <div class="room-info-label">Tanggal</div>
                <div class="room-info-value">{{ $jadwal->tanggal_ujian->translatedFormat('d F Y') }}</div>
            </div>
            <div class="room-info-cell" style="width: 16%;">
                <div class="room-info-label">Waktu</div>
                <div class="room-info-value">{{ $room['waktu'] }}</div>
            </div>
            <div class="room-info-cell" style="width: 15%;">
                <div class="room-info-label">Jumlah Peserta</div>
                <div class="room-info-value">{{ count($room['peserta']) }} Orang</div>
            </div>
            <div class="room-info-cell" style="width: 25%;">
                <div class="room-info-label">Penguji</div>
                <div class="room-info-value" style="font-size: 8px;">
                    @if(!empty($room['penguji']) && count($room['penguji']) > 0)
                        {{ $room['penguji']->pluck('user.name')->filter()->join(', ') }}
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Penilaian Table --}}
        <table class="penilaian">
            <thead>
                {{-- Row 1: Main headers --}}
                <tr>
                    <th colspan="2" style="border-bottom: 0;">NOMOR</th>
                    <th rowspan="3" class="col-nama">N A M A</th>
                    <th colspan="{{ $nilaiColSpan }}">NILAI</th>
                    <th rowspan="3" class="col-asal">SEKOLAH<br>ASAL</th>
                </tr>
                {{-- Row 2: Sub-group headers --}}
                <tr>
                    <th rowspan="2" class="col-urut" style="border-top: 0;">URUT</th>
                    <th rowspan="2" class="col-psrta" style="border-top: 0;">PSRTA</th>
                    @foreach($bobotList as $bobot)
                        @if($bobot->komponen === 'baca_quran')
                            <th colspan="4" class="col-nilai">MEMBACA AL QUR'AN</th>
                        @elseif($bobot->komponen === 'hafalan')
                            <th rowspan="2" class="col-nilai">Hfln<br>Qur'an</th>
                        @elseif($bobot->komponen === 'tulis_quran')
                            <th rowspan="2" class="col-nilai">Tulis<br>Arab</th>
                        @elseif($bobot->komponen === 'wawancara')
                            <th rowspan="2" class="col-nilai">Wwncr</th>
                        @endif
                    @endforeach
                </tr>
                {{-- Row 3: Sub-komponen for baca_quran --}}
                <tr>
                    @foreach($bobotList as $bobot)
                        @if($bobot->komponen === 'baca_quran')
                            <th class="col-nilai">Tjwd</th>
                            <th class="col-nilai">Mhrj</th>
                            <th class="col-nilai">Lncr</th>
                            <th class="col-nilai">Rata2</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($room['peserta'] as $index => $pr)
                @php $cs = $pr->calonSiswa; @endphp
                @if($cs)
                <tr>
                    <td class="col-urut">{{ $index + 1 }}</td>
                    <td class="col-psrta">{{ $cs->nomor_tes ?? '' }}</td>
                    <td class="col-nama">{{ $cs->nama_lengkap ?? '' }}</td>
                    @php $nilai = $room['nilaiMap'][$cs->id] ?? null; @endphp
                    @foreach($bobotList as $bobot)
                        @if($bobot->komponen === 'baca_quran')
                            <td class="col-nilai">{{ $nilai?->nilai_tajwid ?: '' }}</td>
                            <td class="col-nilai">{{ $nilai?->nilai_makhroj ?: '' }}</td>
                            <td class="col-nilai">{{ $nilai?->nilai_kelancaran ?: '' }}</td>
                            <td class="col-nilai">{{ $nilai?->nilai_baca_quran ?: '' }}</td>
                        @elseif($bobot->komponen === 'hafalan')
                            <td class="col-nilai">{{ $nilai?->nilai_hafalan ?: '' }}</td>
                        @elseif($bobot->komponen === 'tulis_quran')
                            <td class="col-nilai">{{ $nilai?->nilai_tulis_quran ?: '' }}</td>
                        @elseif($bobot->komponen === 'wawancara')
                            <td class="col-nilai">{{ $nilai?->nilai_wawancara ?: '' }}</td>
                        @endif
                    @endforeach
                    <td class="col-asal">{{ Str::limit($cs->nama_sekolah_asal ?? '', 20) }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        {{-- Footer: Keterangan + Signature side by side --}}
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; vertical-align: top; border: none; padding: 0;">
                    <div class="keterangan">
                        <div class="ket-title">Keterangan : Penilaian dibubuhkan dalam bentuk angka</div>
                        <div>&nbsp;&nbsp;&nbsp;&nbsp;Kriteria Penilaian :</div>
                        <div class="section-title">&nbsp;&nbsp;1. Penilaian Piagam:</div>
                        <table>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;a. Tk. Sekolah : 5</td><td>e. Tk. Provinsi : 20</td></tr>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;b. Tk. Desa/Kelurahan : 7.5</td><td>f. Tk. Nasional : 25</td></tr>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;c. Tk. Kecamatan : 10</td><td>g. Tk. Internasional : 30</td></tr>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;d. Tk. Kabupaten/Kota : 15</td><td></td></tr>
                        </table>
                        <div class="section-title">&nbsp;&nbsp;2. Rentang Penilaian : Baca Al Qur'an, tulis arab :</div>
                        <table>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;a. Sangat Kurang (SK) : 00 - 45</td><td>c. Cukup (C) : 56 - 75</td></tr>
                            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;b. Kurang (K) : 46 - 55</td><td>d. Baik (B) : 76 - 85</td></tr>
                        </table>
                        <div class="section-title">&nbsp;&nbsp;3. Hafalan Al-Qur'an :</div>
                        <div>&nbsp;&nbsp;&nbsp;&nbsp;a. Cukup : 75-85 &nbsp;&nbsp;&nbsp; b. Baik : 85-100</div>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top; border: none; padding: 0;">
                    @if(!empty($room['penguji']) && count($room['penguji']) > 0)
                        @foreach($room['penguji'] as $pgj)
                        <div class="signature-cell" style="margin-bottom: 5px;">
                            @if($loop->first)
                            <div style="font-size: 9px;">Metro, {{ $jadwal->tanggal_ujian?->translatedFormat('d F Y') ?? now()->format('d F Y') }}</div>
                            @endif
                            <div class="signature-title">{{ $pgj->is_ketua ? 'KETUA PENGUJI' : 'PENGUJI' }}</div>
                            <div class="signature-line">&nbsp;</div>
                            <div style="font-size: 9px; font-weight: bold;">{{ $pgj->user->name ?? '-' }}</div>
                            @php
                                $nip = '';
                                if ($pgj->user && is_numeric($pgj->user->username)) {
                                    $nip = $pgj->user->username;
                                }
                            @endphp
                            <div class="signature-nip">NIP. {{ $nip ?: '................................' }}</div>
                        </div>
                        @endforeach
                    @else
                        <div class="signature-cell">
                            <div style="font-size: 9px;">Metro, {{ $jadwal->tanggal_ujian?->translatedFormat('d F Y') ?? now()->format('d F Y') }}</div>
                            <div class="signature-title">PENGUJI</div>
                            <div class="signature-line">&nbsp;</div>
                            <div class="signature-nip">NIP.</div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="footer-note">
            <div>Dokumen Resmi dicetak dari Sistem PPDB {{ $sekolah->nama_sekolah ?? '' }}</div>
            <div>{{ $room['nama'] }} | Sesi {{ $room['sesi'] }} | Dicetak: {{ now()->format('d/m/Y H:i') }} | Halaman {{ $roomIndex + 1 }} dari {{ count($ruangList) }}</div>
        </div>
    </div>
    @endforeach
</body>
</html>
