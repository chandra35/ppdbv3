@extends('layouts.app')

@section('title', 'Step 4: Konfirmasi')

@section('content')
<div style="max-width: 700px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 2rem;">Langkah 4: Konfirmasi Pendaftaran</h2>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Periksa kembali data Anda sebelum mengkonfirmasi. Data yang sudah dikonfirmasi tidak dapat diubah.</p>
        </div>

        <h4 style="color: #667eea; margin: 1.5rem 0 1rem 0;">Data Pribadi</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600; width: 30%;">NISN</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->nisn ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Nama Lengkap</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->nama_lengkap ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Tempat/Tanggal Lahir</td>
                <td style="padding: 0.75rem;">{{ ($caalonSiswa->tempat_lahir ?? 'N/A') . ', ' . ($caalonSiswa->tanggal_lahir ? \Carbon\Carbon::parse($caalonSiswa->tanggal_lahir)->format('d-m-Y') : 'N/A') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Jenis Kelamin</td>
                <td style="padding: 0.75rem;">{{ ucfirst($caalonSiswa->jenis_kelamin ?? 'N/A') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Agama</td>
                <td style="padding: 0.75rem;">{{ ucfirst($caalonSiswa->agama ?? 'N/A') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Email</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->user->email ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">No. HP Pribadi</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->no_hp_pribadi ?? 'N/A' }}</td>
            </tr>
        </table>

        <h4 style="color: #667eea; margin: 2rem 0 1rem 0;">Alamat Rumah</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600; width: 30%;">Alamat</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->alamat_rumah ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Kelurahan</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->kelurahan ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Kecamatan</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->kecamatan ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Kabupaten/Kota</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->kabupaten_kota ?? 'N/A' }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 0.75rem; font-weight: 600;">Provinsi</td>
                <td style="padding: 0.75rem;">{{ $caalonSiswa->provinsi ?? 'N/A' }}</td>
            </tr>
        </table>

        <h4 style="color: #667eea; margin: 2rem 0 1rem 0;">Dokumen Terupload</h4>
        <ul style="color: #666;">
            @if($caalonSiswa->dokumen()->exists())
                @foreach($caalonSiswa->dokumen as $doc)
                    <li>{{ ucfirst(str_replace('_', ' ', $doc->jenis_dokumen)) }} - {{ $doc->file_size }} bytes</li>
                @endforeach
            @else
                <li>Belum ada dokumen terupload</li>
            @endif
        </ul>

        <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 2rem 0;">
            <p style="margin: 0; color: #856404;">
                <strong>Perhatian:</strong> Dengan mengklik tombol "Konfirmasi Pendaftaran", Anda menyetujui bahwa semua data yang telah diisi adalah akurat dan benar. Data tidak dapat diubah setelah konfirmasi.
            </p>
        </div>

        <form method="POST" action="{{ route('ppdb.register.step4.confirm') }}">
            @csrf
            
            <div class="form-group">
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="agree" required style="width: auto; margin-right: 0.5rem;">
                    <span>Saya setuju bahwa semua data di atas adalah akurat dan benar</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step3') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">Kembali</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Konfirmasi Pendaftaran</button>
            </div>
        </form>
    </div>
</div>
@endsection
