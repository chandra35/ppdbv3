<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Orang Tua/Wali - {{ $calonSiswa->nama_lengkap }}</title>
    <style>
        @page {
            size: A4;
            margin: 18mm 25mm 15mm 25mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }

        /* ── Kop ── */
        .kop-wrapper { margin-bottom: 0; }

        /* ── Judul ── */
        .judul {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 4px 0 1px 0;
        }
        .judul-garis {
            width: 220px;
            border: none;
            border-top: 1.5px solid #000;
            margin: 2px auto 4px auto;
        }
        .sub-judul {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 10px;
        }

        /* ── Paragraf ── */
        p {
            text-align: justify;
            margin-bottom: 5px;
            font-size: 11pt;
        }

        /* ── Tabel Data ── */
        .data-tbl {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 4px 8px;
            table-layout: fixed;
        }
        .data-tbl td {
            padding: 1.5px 3px;
            font-size: 10.5pt;
            vertical-align: top;
            border: none;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-no  { width: 20px; text-align: right; padding-right: 5px !important; }
        .col-lbl { width: 170px; }
        .col-sep { width: 10px; text-align: center; }
        .col-val { }

        /* ── Menyatakan ── */
        .menyatakan {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            letter-spacing: 2px;
            margin: 8px 0 4px 0;
        }

        /* ── Pernyataan ── */
        .pernyataan-tbl {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 6px 0;
            table-layout: fixed;
        }
        .pernyataan-tbl td {
            padding: 1.5px 3px;
            font-size: 10.5pt;
            vertical-align: top;
            border: none;
            line-height: 1.4;
            text-align: justify;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .pernyataan-tbl td:first-child {
            width: 20px;
            text-align: right;
            padding-right: 5px;
        }

        /* ── Penutup ── */
        .penutup {
            text-align: justify;
            font-size: 11pt;
            margin-bottom: 0;
        }

        /* ── Tanda Tangan ── */
        .ttd-tbl {
            width: 100%;
            margin-top: 12px;
        }
        .ttd-tbl td {
            vertical-align: top;
            text-align: center;
            font-size: 11pt;
            padding: 0 5px;
            line-height: 1.45;
        }
        .ttd-space { height: 50px; }
        .ttd-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
        }
        .ttd-nip {
            font-size: 9.5pt;
            margin-top: 1px;
        }

        /* ── Footer ── */
        .footer-strip {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 0.5px solid #999;
            font-size: 8pt;
            color: #666;
            font-style: italic;
        }
        .footer-strip td {
            border: none;
            padding: 0 2px;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>

    {{-- Kop Surat --}}
    <div class="kop-wrapper">{!! $kopHtml !!}</div>

    {{-- Judul --}}
    <div class="judul">SURAT PERNYATAAN ORANG TUA / WALI</div>
    <hr class="judul-garis">
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
            <td class="col-lbl">Hubungan dengan Calon Siswa</td>
            <td class="col-sep">:</td>
            <td class="col-val">{{ $hubunganOrtu }}</td>
        </tr>
    </table>

    <p style="margin-top: 6px;">Adalah orang tua / wali dari calon peserta didik:</p>

    {{-- Data Peserta Didik --}}
    <table class="data-tbl">
        <tr>
            <td class="col-no">1.</td>
            <td class="col-lbl">Nama Lengkap</td>
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
            <td class="col-val">{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->locale('id')->isoFormat('D MMMM Y') : '-' }}</td>
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
    <div class="menyatakan">M E N Y A T A K A N</div>

    <table class="pernyataan-tbl">
        <tr>
            <td>1.</td>
            <td>Bersedia membimbing dan mengawasi peserta didik tersebut untuk mentaati tata tertib selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Tidak keberatan apabila peserta didik menerima sanksi sesuai dengan ketentuan dan peraturan yang berlaku di madrasah/sekolah.</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Bersedia memenuhi segala persyaratan administrasi yang ditetapkan oleh pihak madrasah/sekolah.</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Bersedia menghadiri setiap undangan rapat/pertemuan yang diselenggarakan oleh pihak madrasah/sekolah.</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Turut bertanggung jawab atas segala tindakan peserta didik selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</td>
        </tr>
    </table>

    <p class="penutup">Demikian surat pernyataan ini dibuat dengan sebenarnya dan penuh tanggung jawab, tanpa ada paksaan dari pihak manapun.</p>

    {{-- Tanda Tangan --}}
    <table class="ttd-tbl">
        <tr>
            <td width="50%">
                Mengetahui,<br>Kepala Madrasah / Sekolah
            </td>
            <td width="50%">
                {{ $kota }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
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
                    @if($nipKepalaSekolah)<br><span class="ttd-nip">NIP. {{ $nipKepalaSekolah }}</span>@endif
                @else
                    <span class="ttd-name">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span>
                @endif
            </td>
            <td>
                <span class="ttd-name">{{ $namaOrtu }}</span>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer-strip">
        <table style="width:100%;">
            <tr>
                <td>*) Surat ini wajib dibawa saat daftar ulang / rapat wali calon peserta didik baru</td>
                <td style="text-align:right;">{{ $calonSiswa->nomor_registrasi }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
