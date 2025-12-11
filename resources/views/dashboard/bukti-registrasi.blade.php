@extends('layouts.app')

@section('title', 'Bukti Registrasi PPDB')

@section('content')
<div style="max-width: 900px; margin: 2rem auto;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="color: #667eea; margin: 0;">Bukti Registrasi PPDB</h2>
            <form method="POST" action="{{ route('ppdb.bukti-registrasi.print') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">Cetak/PDF</button>
            </form>
        </div>

        <!-- Print area -->
        <div id="bukti-registrasi" style="background: white; padding: 3rem; border: 1px solid #ddd; page-break-after: avoid;">
            
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 2rem; border-bottom: 3px solid #667eea; padding-bottom: 1rem;">
                <h1 style="margin: 0; color: #667eea; font-size: 1.8rem;">BUKTI REGISTRASI PPDB</h1>
                <p style="margin: 0.25rem 0; color: #666; font-size: 0.95rem;">Penerimaan Peserta Didik Baru Tahun Pelajaran {{ now()->year }}/{{ now()->year + 1 }}</p>
            </div>

            <!-- Registration Number -->
            <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; text-align: center;">
                <p style="margin: 0; color: #999; font-size: 0.9rem;">NOMOR REGISTRASI</p>
                <p style="margin: 0.5rem 0 0 0; color: #667eea; font-size: 1.5rem; font-weight: bold;">{{ $caalonSiswa->nomor_registrasi }}</p>
            </div>

            <!-- Personal Data Section -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; margin-bottom: 1rem;">DATA PRIBADI PENDAFTAR</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem; width: 25%; color: #666; font-weight: 600;">Nama Lengkap</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->nama_lengkap }}</td>
                        <td style="padding: 0.5rem; width: 25%; color: #666; font-weight: 600;">NISN</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->nisn }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Tempat Lahir</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->tempat_lahir }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Tanggal Lahir</td>
                        <td style="padding: 0.5rem; color: #333;">{{ \Carbon\Carbon::parse($caalonSiswa->tanggal_lahir)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Jenis Kelamin</td>
                        <td style="padding: 0.5rem; color: #333;">{{ ucfirst($caalonSiswa->jenis_kelamin) }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Agama</td>
                        <td style="padding: 0.5rem; color: #333;">{{ ucfirst($caalonSiswa->agama) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Email</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->user->email }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">No. HP</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->no_hp_pribadi }}</td>
                    </tr>
                </table>
            </div>

            <!-- Address Section -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; margin-bottom: 1rem;">ALAMAT RUMAH</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem; width: 25%; color: #666; font-weight: 600;">Alamat Lengkap</td>
                        <td colspan="3" style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->alamat_rumah }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Kelurahan/Desa</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->kelurahan }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Kecamatan</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->kecamatan }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Kabupaten/Kota</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->kabupaten_kota }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Provinsi</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->provinsi }}</td>
                    </tr>
                </table>
            </div>

            <!-- Parent/Guardian Data -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; margin-bottom: 1rem;">DATA ORANG TUA/WALI</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem; width: 25%; color: #666; font-weight: 600;">No. HP Orang Tua/Wali</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->no_hp_ortu ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Registration Info -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; margin-bottom: 1rem;">INFORMASI PENDAFTARAN</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem; width: 25%; color: #666; font-weight: 600;">Tanggal Registrasi</td>
                        <td style="padding: 0.5rem; color: #333;">{{ \Carbon\Carbon::parse($caalonSiswa->tanggal_registrasi)->format('d-m-Y H:i') }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Status Verifikasi</td>
                        <td style="padding: 0.5rem; color: #333;">{{ ucfirst($caalonSiswa->status_verifikasi) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Status Admisi</td>
                        <td style="padding: 0.5rem; color: #333;">{{ ucfirst($caalonSiswa->status_admisi) }}</td>
                        <td style="padding: 0.5rem; color: #666; font-weight: 600;">Asal Sekolah</td>
                        <td style="padding: 0.5rem; color: #333;">{{ $caalonSiswa->asal_sekolah ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid #ddd; text-align: center; color: #999; font-size: 0.85rem;">
                <p style="margin: 0;">Bukti registrasi ini adalah sah dan dapat digunakan sebagai bukti pendaftaran PPDB.</p>
                <p style="margin: 0.5rem 0 0 0;">Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</p>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <a href="{{ route('ppdb.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
            <button onclick="window.print()" class="btn btn-primary">Cetak Halaman</button>
        </div>
    </div>
</div>

<style media="print">
    body {
        background: white;
    }
    .navbar, .footer, .btn, form { display: none !important; }
    #bukti-registrasi { border: none !important; page-break-after: always; }
</style>
@endsection
