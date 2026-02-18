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
            line-height: 1.3;
            color: #000;
        }

        .kop-wrapper { margin-bottom: 0; }

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
            margin: 2px auto 3px auto;
        }
        .sub-judul {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 8px;
        }

        p {
            text-align: justify;
            margin-bottom: 4px;
            font-size: 11pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            border: none;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.3;
        }

        .data-tbl { margin: 2px 0 3px 5px; }
        .data-tbl td { padding: 1px 2px; }

        .pernyataan-tbl { margin: 0 0 5px 0; }
        .pernyataan-tbl td {
            padding: 1px 2px;
            text-align: justify;
        }

        .ttd-tbl { margin-top: 10px; }
        .ttd-tbl td {
            text-align: center;
            padding: 0 5px;
            line-height: 1.35;
        }

        .footer-strip {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 0.5px solid #999;
            font-size: 8pt;
            color: #666;
            font-style: italic;
        }
        .footer-strip td {
            font-size: 8pt;
            color: #666;
            padding: 0;
        }
    </style>
</head>
<body>

    <div class="kop-wrapper">{!! $kopHtml !!}</div>

    <div class="judul">SURAT PERNYATAAN ORANG TUA / WALI</div>
    <hr class="judul-garis">
    <div class="sub-judul">Peserta Didik Baru Tahun Pelajaran {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</div>

    <p>Yang bertanda tangan di bawah ini:</p>

    <table class="data-tbl">
        <tr>
            <td width="4%">1.</td>
            <td width="33%">Nama Orang Tua / Wali</td>
            <td width="2%">:</td>
            <td width="61%"><strong>{{ $namaOrtu }}</strong></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Pekerjaan</td>
            <td>:</td>
            <td>{{ $pekerjaanOrtu }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $alamatOrtu }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>No. Telepon / HP</td>
            <td>:</td>
            <td>{{ $hpOrtu }}</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Hubungan dengan Calon Siswa</td>
            <td>:</td>
            <td>{{ $hubunganOrtu }}</td>
        </tr>
    </table>

    <p style="margin-top: 4px;">Adalah orang tua / wali dari calon peserta didik:</p>

    <table class="data-tbl">
        <tr>
            <td width="4%">1.</td>
            <td width="33%">Nama Lengkap</td>
            <td width="2%">:</td>
            <td width="61%"><strong>{{ $calonSiswa->nama_lengkap }}</strong></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>NISN</td>
            <td>:</td>
            <td>{{ $calonSiswa->nisn }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->locale('id')->isoFormat('D MMMM Y') : '-' }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td>{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Asal Sekolah</td>
            <td>:</td>
            <td>{{ $calonSiswa->nama_sekolah_asal ?? '-' }}</td>
        </tr>
        <tr>
            <td>6.</td>
            <td>No. Registrasi PPDB</td>
            <td>:</td>
            <td>{{ $calonSiswa->nomor_registrasi }}</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>Jalur Pendaftaran</td>
            <td>:</td>
            <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}@if($calonSiswa->pilihan_program) &mdash; {{ $calonSiswa->pilihan_program }}@endif</td>
        </tr>
    </table>

    <div style="text-align: center; font-weight: bold; font-size: 11pt; letter-spacing: 2px; margin: 6px 0 4px 0;">M E N Y A T A K A N</div>

    <table class="pernyataan-tbl">
        <tr>
            <td width="4%">1.</td>
            <td width="96%">Bersedia membimbing dan mengawasi peserta didik tersebut untuk mentaati tata tertib selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</td>
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

    <p>Demikian surat pernyataan ini dibuat dengan sebenarnya dan penuh tanggung jawab, tanpa ada paksaan dari pihak manapun.</p>

    <table class="ttd-tbl">
        <tr>
            <td width="50%">Mengetahui,<br>Kepala Madrasah / Sekolah</td>
            <td width="50%">{{ $kota }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>Yang membuat pernyataan,<br>Orang Tua / Wali</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
        </tr>
        <tr>
            <td>
                @if($kepalaSekolah)
                    <strong><u>{{ $kepalaSekolah }}</u></strong>
                    @if($nipKepalaSekolah)<br><span style="font-size:9.5pt;">NIP. {{ $nipKepalaSekolah }}</span>@endif
                @else
                    <strong><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</u></strong>
                @endif
            </td>
            <td>
                <strong><u>{{ $namaOrtu }}</u></strong>
            </td>
        </tr>
    </table>

    <div class="footer-strip">
        <table>
            <tr>
                <td>*) Surat ini wajib dibawa saat daftar ulang / rapat wali calon peserta didik baru</td>
                <td style="text-align:right;">{{ $calonSiswa->nomor_registrasi }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
