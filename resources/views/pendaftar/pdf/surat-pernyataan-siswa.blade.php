<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Peserta Didik Baru - {{ $calonSiswa->nama_lengkap }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            padding: 8mm 28mm 5mm 28mm;
        }

        .kop-wrapper { margin: 0 -18mm 0 -18mm; }

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

        .data-tbl { margin: 2px 0 3px 0; }
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

        .titik { letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <div class="kop-wrapper">{!! $kopHtml !!}</div>

    <div class="judul">SURAT PERNYATAAN PESERTA DIDIK BARU</div>
    <hr class="judul-garis">
    <div class="sub-judul">Tahun Pelajaran {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</div>

    <p>Yang bertanda tangan di bawah ini:</p>

    <table class="data-tbl">
        <tr>
            <td width="4%">1.</td>
            <td width="33%">Nama</td>
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
            <td>Nomor Pendaftaran</td>
            <td>:</td>
            <td>{{ $calonSiswa->nomor_registrasi }}</td>
        </tr>
        <tr>
            <td>6.</td>
            <td>No. Peserta</td>
            <td>:</td>
            <td>{{ $calonSiswa->nomor_tes ?? '-' }}</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>Minat Reguler / Asrama</td>
            <td>:</td>
            <td>{{ $calonSiswa->pilihan_program ?? '-' }}</td>
        </tr>
        <tr>
            <td>8.</td>
            <td>Nama Orang Tua / Wali</td>
            <td>:</td>
            <td>{{ $namaOrtu }}</td>
        </tr>
        <tr>
            <td>9.</td>
            <td>Pekerjaan Orang Tua</td>
            <td>:</td>
            <td>{{ $pekerjaanOrtu }}</td>
        </tr>
        <tr>
            <td>10.</td>
            <td>Nama Wali</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td>11.</td>
            <td>Pekerjaan Wali</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td>12.</td>
            <td>Agama Wali</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td>13.</td>
            <td>Hubungan Keluarga dgn Wali</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td>14.</td>
            <td>Telp / HP Wali</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td>15.</td>
            <td>Alamat Orang Tua / Wali</td>
            <td>:</td>
            <td>RT ......... / RW ......... / Dusun .....................</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-left: 12px;">Kelurahan</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-left: 12px;">Kecamatan</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-left: 12px;">Kabupaten / Kota</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-left: 12px;">Propinsi</td>
            <td>:</td>
            <td class="titik">........................................</td>
        </tr>
    </table>

    <div style="text-align: center; font-weight: bold; font-size: 11pt; letter-spacing: 2px; margin: 6px 0 4px 0;">M E N Y A T A K A N</div>

    <p style="margin-bottom: 2px;">Dengan ini menyatakan dengan sesungguhnya, bahwa selama di Madrasah ini:</p>

    <table class="pernyataan-tbl">
        <tr>
            <td width="4%">1.</td>
            <td width="96%">Akan belajar dengan tekun, sungguh-sungguh dan penuh semangat.</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Akan menjaga nama baik diri sendiri, keluarga, masyarakat dan madrasah.</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Sanggup mentaati seluruh tata tertib dan peraturan yang berlaku, mematuhi peraturan di lingkungan pendidikan termasuk berpakaian seragam madrasah, OSIS dan lain-lain.</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Siap menerima sanksi sesuai ketentuan tata tertib madrasah.</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Bagi siswa yang diterima tidak boleh pindah ke SMA/MA lainnya.</td>
        </tr>
    </table>

    <p>Demikian surat pernyataan ini dibuat dengan sebenarnya dan penuh rasa tanggung jawab.</p>

    <table class="ttd-tbl">
        <tr>
            <td width="50%">Mengetahui,<br>Orang Tua / Wali</td>
            <td width="50%">{{ $kota }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>Yang membuat pernyataan,</td>
        </tr>
        <tr>
            <td style="height: 75px;"></td>
            <td style="height: 75px; position: relative;">
                <div style="position: absolute; top: 12px; left: 50%; transform: translateX(-50%); font-size: 9pt; font-style: italic; color: #666;">Materai Rp 10.000</div>
            </td>
        </tr>
        <tr>
            <td>
                <strong><u>{{ $namaOrtu }}</u></strong>
            </td>
            <td>
                <strong><u>{{ $calonSiswa->nama_lengkap }}</u></strong>
            </td>
        </tr>
    </table>

    <div class="footer-strip">
        <table>
            <tr>
                <td>*) Surat ini wajib dibawa saat daftar ulang / rapat wali calon peserta didik baru</td>
                <td style="text-align:right;">{{ $calonSiswa->nomor_tes ?? $calonSiswa->nomor_registrasi }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
