<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Orang Tua/Wali - {{ $calonSiswa->nama_lengkap }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 18mm 12mm 22mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
        }

        .kop-wrapper { margin-bottom: 2px; }

        .judul {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
            margin: 8px 0 2px 0;
        }
        .sub-judul {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12px;
        }

        p { text-align: justify; margin-bottom: 6px; }

        .data-tbl {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        .data-tbl td {
            padding: 1.5px 4px;
            font-size: 10.5pt;
            vertical-align: top;
            border: none;
        }
        .col-no   { width: 4%; }
        .col-lbl  { width: 33%; }
        .col-sep  { width: 2%; text-align: center; }
        .col-val  { width: 61%; }

        .menyatakan {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 10px 0 6px 0;
        }

        .pernyataan { margin: 0 0 8px 0; }
        .pernyataan ol {
            margin-left: 18px;
            padding-left: 0;
        }
        .pernyataan li {
            margin-bottom: 3px;
            text-align: justify;
            font-size: 10.5pt;
        }

        .penutup {
            text-align: justify;
            font-size: 10.5pt;
            margin-bottom: 2px;
        }

        .ttd-tbl {
            width: 100%;
            margin-top: 14px;
        }
        .ttd-tbl td {
            vertical-align: top;
            text-align: center;
            font-size: 10.5pt;
            padding: 2px;
        }
        .ttd-space { height: 55px; }
        .ttd-name { font-weight: bold; text-decoration: underline; }

        .footer-strip {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #aaa;
            font-size: 8pt;
            color: #555;
        }
        .footer-strip td {
            border: none;
            padding: 1px 3px;
            font-size: 8pt;
            color: #555;
        }
    </style>
</head>
<body>

    {{-- Kop Surat --}}
    <div class="kop-wrapper">
        {!! $kopHtml !!}
    </div>

    {{-- Judul --}}
    <div class="judul">SURAT PERNYATAAN ORANG TUA / WALI</div>
    <div class="sub-judul">Peserta Didik Baru Tahun Pelajaran {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</div>

    {{-- Pembuka --}}
    <p>Yang bertanda tangan di bawah ini:</p>

    {{-- Data Orang Tua --}}
    <table class="data-tbl">
        <tr>
            <td class="col-no">1.</td>
            <td class="col-lbl">Nama Orang Tua / Wali</td>
            <td class="col-sep">:</td>
            <td class="col-val"><strong>{{ $namaOrtu }}</strong></td>
        </tr>
        <tr>
            <td class="col-no">2.</td>
            <td class="col-lbl">Pekerjaan</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $pekerjaanOrtu }}</td>
        </tr>
        <tr>
            <td class="col-no">3.</td>
            <td class="col-lbl">Alamat</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $alamatOrtu }}</td>
        </tr>
        <tr>
            <td class="col-no">4.</td>
            <td class="col-lbl">No. Telepon / HP</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $hpOrtu }}</td>
        </tr>
        <tr>
            <td class="col-no">5.</td>
            <td class="col-lbl">Hubungan dengan Peserta Didik</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $hubunganOrtu }}</td>
        </tr>
    </table>

    <p style="margin-top: 6px;">Adalah orang tua / wali dari peserta didik:</p>

    {{-- Data Peserta Didik --}}
    <table class="data-tbl">
        <tr>
            <td class="col-no">1.</td>
            <td class="col-lbl">Nama Peserta Didik</td>
            <td class="col-sep">:</td>
            <td class="col-val"><strong>{{ $calonSiswa->nama_lengkap }}</strong></td>
        </tr>
        <tr>
            <td class="col-no">2.</td>
            <td class="col-lbl">NISN</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->nisn }}</td>
        </tr>
        <tr>
            <td class="col-no">3.</td>
            <td class="col-lbl">Tempat, Tanggal Lahir</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">4.</td>
            <td class="col-lbl">Jenis Kelamin</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td class="col-no">5.</td>
            <td class="col-lbl">Asal Sekolah</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->nama_sekolah_asal ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-no">6.</td>
            <td class="col-lbl">No. Registrasi PPDB</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->nomor_registrasi }}</td>
        </tr>
        <tr>
            <td class="col-no">7.</td>
            <td class="col-lbl">Jalur Pendaftaran</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}@if($calonSiswa->pilihan_program) &mdash; {{ $calonSiswa->pilihan_program }}@endif</td>
        </tr>
    </table>

    {{-- Menyatakan --}}
    <div class="menyatakan">MENYATAKAN</div>

    <div class="pernyataan">
        <ol>
            <li>Bersedia membimbing dan mengawasi peserta didik tersebut untuk mentaati tata tertib selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</li>
            <li>Tidak keberatan apabila peserta didik menerima sanksi sesuai dengan ketentuan dan peraturan yang berlaku.</li>
            <li>Bersedia memenuhi segala persyaratan administrasi yang ditetapkan oleh pihak madrasah/sekolah.</li>
            <li>Bersedia menghadiri setiap undangan rapat/pertemuan yang diselenggarakan oleh pihak madrasah/sekolah.</li>
            <li>Turut bertanggung jawab atas segala tindakan peserta didik selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</li>
        </ol>
    </div>

    <p class="penutup">Demikian surat pernyataan ini dibuat dengan sebenarnya dan penuh tanggung jawab, tanpa ada paksaan dari pihak manapun.</p>

    {{-- Tanda Tangan --}}
    <table class="ttd-tbl">
        <tr>
            <td width="50%">
                Mengetahui,<br>Kepala Madrasah / Sekolah
            </td>
            <td width="50%">
                {{ $kota }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Yang membuat pernyataan,<br>Orang Tua / Wali
            </td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td>
                @if($kepalaSekolah)
                    <span class="ttd-name">{{ $kepalaSekolah }}</span>
                    @if($nipKepalaSekolah)<br><span style="font-size:9.5pt;">NIP. {{ $nipKepalaSekolah }}</span>@endif
                @else
                    <span class="ttd-name">.................................</span>
                @endif
            </td>
            <td>
                <span class="ttd-name">{{ $namaOrtu }}</span>
            </td>
        </tr>
    </table>

    {{-- Footer strip --}}
    <div class="footer-strip">
        <table style="width:100%;">
            <tr>
                <td>*) Surat ini dibawa saat daftar ulang / rapat wali calon peserta didik baru</td>
                <td style="text-align:right;">{{ $calonSiswa->nomor_registrasi }} | {{ $calonSiswa->nomor_hp ?? '-' }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
