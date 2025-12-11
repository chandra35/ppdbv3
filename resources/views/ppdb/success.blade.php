@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div style="max-width: 600px; margin: 3rem auto;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">✓</div>
        
        <h2 style="color: #28a745; margin-bottom: 1rem;">Pendaftaran Berhasil!</h2>
        
        <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">
            Terima kasih telah melakukan pendaftaran PPDB. Data Anda telah disimpan dengan aman.
        </p>

        <div class="card" style="background: #f0f4ff; margin-bottom: 2rem;">
            <h4 style="color: #667eea; margin-top: 0;">Nomor Registrasi Anda</h4>
            <div style="font-size: 1.5rem; font-weight: bold; color: #667eea; margin: 1rem 0;">
                {{ $nomor_registrasi }}
            </div>
            <p style="color: #666; margin: 0;">Simpan nomor ini untuk referensi Anda</p>
        </div>

        <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;">
                <strong>Langkah Selanjutnya:</strong> 
                Anda dapat mencetak bukti registrasi melalui dashboard. Dokumen Anda akan diverifikasi oleh admin dalam waktu 2-3 hari kerja.
            </p>
        </div>

        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('ppdb.dashboard') }}" class="btn btn-primary" style="flex: 1; text-align: center;">
                Buka Dashboard
            </a>
            <a href="{{ route('ppdb.bukti-registrasi') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">
                Cetak Bukti Registrasi
            </a>
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h4 style="color: #667eea; margin-top: 0;">Info Penting</h4>
        <ul style="color: #666; line-height: 1.8;">
            <li><strong>Email Verifikasi:</strong> Periksa email Anda untuk mendapatkan konfirmasi</li>
            <li><strong>Status Verifikasi:</strong> Pantau status dokumen Anda melalui dashboard</li>
            <li><strong>Nomor Pendaftaran Final:</strong> Akan diberikan setelah verifikasi dokumen selesai</li>
            <li><strong>Nomor Tes:</strong> Akan diumumkan melalui sistem setelah periode pendaftaran ditutup</li>
        </ul>
    </div>
</div>
@endsection
