<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Orang Tua/Wali - {{ $calonSiswa->nama_lengkap }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 20mm 15mm 25mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .page-wrapper {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .title {
            text-align: center;
            margin: 15px 0 5px 0;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11pt;
            font-style: italic;
            color: #555;
        }

        .isi-surat {
            text-align: justify;
            margin-bottom: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .data-table td {
            padding: 4px 5px;
            font-size: 11pt;
            vertical-align: top;
            border: none;
        }
        .data-table td:first-child {
            width: 5%;
        }
        .data-table td:nth-child(2) {
            width: 35%;
        }
        .data-table td:nth-child(3) {
            width: 2%;
            text-align: center;
        }
        .data-table td:last-child {
            width: 58%;
        }

        .pernyataan-list {
            margin: 10px 0 15px 0;
        }
        .pernyataan-list ol {
            margin-left: 20px;
        }
        .pernyataan-list li {
            margin-bottom: 8px;
            text-align: justify;
        }

        .ttd-table {
            width: 100%;
            margin-top: 30px;
        }
        .ttd-table td {
            vertical-align: top;
            text-align: center;
            padding: 5px;
            font-size: 11pt;
        }
        .ttd-space {
            height: 70px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .catatan-section {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #999;
            font-size: 9pt;
            color: #666;
        }

        .kop-wrapper {
            margin-bottom: 5px;
        }

        .nomor-surat {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- Kop Surat --}}
    <div class="kop-wrapper">
        {!! $kopHtml !!}
    </div>

    {{-- Judul --}}
    <div class="title">SURAT PERNYATAAN ORANG TUA / WALI</div>
    <div class="subtitle">PESERTA DIDIK BARU TAHUN PELAJARAN {{ $calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y') }}/{{ ($calonSiswa->tahunPelajaran->tahun_mulai ?? date('Y')) + 1 }}</div>

    {{-- Pembuka --}}
    <div class="isi-surat">
        <p>Yang bertanda tangan di bawah ini:</p>
    </div>

    {{-- Data Orang Tua --}}
    <table class="data-table">
        <tr>
            <td>1.</td>
            <td>Nama Orang Tua/Wali</td>
            <td>:</td>
            <td><strong>{{ $namaOrtu }}</strong></td>
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
            <td>No. Telepon/HP</td>
            <td>:</td>
            <td>{{ $hpOrtu }}</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Hubungan dengan Peserta Didik</td>
            <td>:</td>
            <td>{{ $hubunganOrtu }}</td>
        </tr>
    </table>

    <div class="isi-surat" style="margin-top: 10px;">
        <p>Adalah orang tua/wali dari peserta didik:</p>
    </div>

    {{-- Data Peserta Didik --}}
    <table class="data-table">
        <tr>
            <td>1.</td>
            <td>Nama Peserta Didik</td>
            <td>:</td>
            <td><strong>{{ $calonSiswa->nama_lengkap }}</strong></td>
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
            <td>{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir ? $calonSiswa->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
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
            <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
        </tr>
        @if($calonSiswa->pilihan_program)
        <tr>
            <td>8.</td>
            <td>Jalur Minat / Program</td>
            <td>:</td>
            <td>{{ $calonSiswa->pilihan_program }}</td>
        </tr>
        @endif
    </table>

    {{-- MENYATAKAN --}}
    <div style="text-align: center; margin: 20px 0 10px 0;">
        <strong style="font-size: 13pt;">MENYATAKAN</strong>
    </div>

    <div class="pernyataan-list">
        <ol>
            <li>Bersedia membimbing dan mengawasi peserta didik tersebut di atas untuk mentaati tata tertib madrasah/sekolah selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</li>
            <li>Tidak keberatan apabila peserta didik di atas menerima sanksi sesuai dengan ketentuan dan peraturan yang berlaku di madrasah/sekolah.</li>
            <li>Bersedia memenuhi segala persyaratan administrasi yang ditetapkan oleh pihak madrasah/sekolah.</li>
            <li>Bersedia menghadiri setiap undangan rapat/pertemuan yang diselenggarakan oleh pihak madrasah/sekolah.</li>
            <li>Turut bertanggung jawab atas segala tindakan dan perbuatan peserta didik tersebut selama menjadi siswa di <strong>{{ $namaSekolah }}</strong>.</li>
        </ol>
    </div>

    <div class="isi-surat">
        <p>Demikian surat pernyataan ini saya buat dengan sebenarnya dan penuh rasa tanggung jawab, tanpa ada paksaan dari pihak manapun.</p>
    </div>

    {{-- Tanda Tangan --}}
    <table class="ttd-table">
        <tr>
            <td width="50%">Mengetahui,<br>Kepala Madrasah/Sekolah</td>
            <td width="50%">{{ $kota }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Yang membuat pernyataan,<br>Orang Tua/Wali</td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td>
                @if($kepalaSekolah)
                <span class="ttd-name">{{ $kepalaSekolah }}</span>
                    @if($nipKepalaSekolah)
                    <br>NIP. {{ $nipKepalaSekolah }}
                    @endif
                @else
                <span class="ttd-name">.................................</span>
                @endif
            </td>
            <td>
                <span class="ttd-name">{{ $namaOrtu }}</span>
            </td>
        </tr>
    </table>

    {{-- Catatan --}}
    <div class="catatan-section">
        <strong>Catatan:</strong><br>
        Surat pernyataan ini dibawa pada saat daftar ulang / rapat wali calon peserta didik baru.
        <br><br>
        <table style="width: 100%; font-size: 9pt; border: none;">
            <tr>
                <td style="width: 15%; border: none; padding: 2px;">No. Registrasi</td>
                <td style="width: 2%; border: none; padding: 2px;">:</td>
                <td style="width: 33%; border: none; padding: 2px;">{{ $calonSiswa->nomor_registrasi }}</td>
                <td style="width: 15%; border: none; padding: 2px;">No. HP Siswa</td>
                <td style="width: 2%; border: none; padding: 2px;">:</td>
                <td style="width: 33%; border: none; padding: 2px;">{{ $calonSiswa->nomor_hp ?? '-' }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px;">Nama</td>
                <td style="border: none; padding: 2px;">:</td>
                <td style="border: none; padding: 2px;">{{ $calonSiswa->nama_lengkap }}</td>
                <td style="border: none; padding: 2px;">No. HP Ortu</td>
                <td style="border: none; padding: 2px;">:</td>
                <td style="border: none; padding: 2px;">{{ $hpOrtu }}</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
