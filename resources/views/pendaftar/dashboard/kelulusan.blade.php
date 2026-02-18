@extends('layouts.pendaftar')

@section('title', 'Info Kelulusan')
@section('page-title', 'Info Kelulusan')

@section('breadcrumb')
<li class="breadcrumb-item active">Kelulusan</li>
@endsection

@section('css')
<style>
    .kelulusan-hero {
        padding: 40px 20px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .kelulusan-hero.lulus {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: #fff;
    }
    .kelulusan-hero.tidak_lulus {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        color: #fff;
    }
    .kelulusan-hero.cadangan {
        background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        color: #333;
    }
    .kelulusan-hero.belum {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }
    .kelulusan-icon {
        width: 120px; height: 120px; border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex; align-items: center; justify-content: center;
        margin-bottom: 20px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .kelulusan-status-text {
        font-size: 2.5rem; font-weight: 800; letter-spacing: 3px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    .confetti { position: absolute; width: 10px; height: 10px; border-radius: 2px; animation: fall linear forwards; }
    @keyframes fall {
        to { transform: translateY(500px) rotate(720deg); opacity: 0; }
    }
    .wa-group-card {
        background: linear-gradient(135deg, #25D366, #128C7E);
        border-radius: 15px; padding: 24px; color: #fff;
        transition: transform 0.3s;
    }
    .wa-group-card:hover { transform: translateY(-3px); }
    .wa-group-card a { color: #fff; text-decoration: none; }
    .dokumen-item-card {
        border-left: 4px solid #007bff; background: #f8f9fa;
        padding: 12px 16px; border-radius: 0 8px 8px 0; margin-bottom: 8px;
    }
    .jadwal-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 15px; padding: 24px; color: #fff;
    }
    .info-penting-content {
        line-height: 1.8;
        font-size: 0.95rem;
    }
    .info-penting-content table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    .info-penting-content table td,
    .info-penting-content table th {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
    }
    .info-penting-content ul, .info-penting-content ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .info-penting-content a {
        color: #007bff;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        @if($kelulusan)
            {{-- Status Kelulusan --}}
            <div class="kelulusan-hero {{ $kelulusan->status }}" id="kelulusanHero">
                <div class="kelulusan-icon">
                    @if($kelulusan->status === 'lulus')
                        <i class="fas fa-graduation-cap fa-4x"></i>
                    @elseif($kelulusan->status === 'tidak_lulus')
                        <i class="fas fa-heart-broken fa-4x"></i>
                    @else
                        <i class="fas fa-hourglass-half fa-4x"></i>
                    @endif
                </div>
                <div class="kelulusan-status-text mb-3">
                    @if($kelulusan->status === 'lulus')
                        SELAMAT! ANDA LULUS 🎓
                    @elseif($kelulusan->status === 'tidak_lulus')
                        MOHON MAAF
                    @else
                        CADANGAN
                    @endif
                </div>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    @if($kelulusan->status === 'lulus')
                        Selamat! Anda dinyatakan LULUS seleksi PPDB {{ $namaSekolah }}
                    @elseif($kelulusan->status === 'tidak_lulus')
                        {{ $setting->pesan_tidak_lulus }}
                    @else
                        Anda masuk dalam daftar cadangan. Mohon menunggu informasi selanjutnya.
                    @endif
                </p>
                <div class="mt-3" style="opacity: 0.8;">
                    <small>
                        <i class="fas fa-user mr-1"></i>{{ $calonSiswa->nama_lengkap }} |
                        <i class="fas fa-id-card mr-1"></i>NISN: {{ $calonSiswa->nisn }} |
                        <i class="fas fa-road mr-1"></i>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}
                    </small>
                </div>
            </div>

            {{-- Detail Info --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Detail Kelulusan</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <tr>
                            <td width="35%"><strong>Nama Lengkap</strong></td>
                            <td>{{ $calonSiswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td><strong>NISN</strong></td>
                            <td>{{ $calonSiswa->nisn }}</td>
                        </tr>
                        <tr>
                            <td><strong>No. Registrasi</strong></td>
                            <td>{{ $calonSiswa->nomor_registrasi }}</td>
                        </tr>
                        @if($calonSiswa->nomor_tes)
                        <tr>
                            <td><strong>No. Peserta Tes</strong></td>
                            <td>{{ $calonSiswa->nomor_tes }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Jalur Pendaftaran</strong></td>
                            <td>{{ $calonSiswa->jalurPendaftaran->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Gelombang</strong></td>
                            <td>{{ $calonSiswa->gelombangPendaftaran->nama ?? '-' }}</td>
                        </tr>
                        @if($calonSiswa->pilihan_program)
                        <tr>
                            <td><strong>Jalur Minat</strong></td>
                            <td>{{ $calonSiswa->pilihan_program }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Status Kelulusan</strong></td>
                            <td>
                                @if($kelulusan->status === 'lulus')
                                    <span class="badge badge-success px-3 py-2" style="font-size: 1rem;"><i class="fas fa-check-circle mr-1"></i>LULUS</span>
                                @elseif($kelulusan->status === 'tidak_lulus')
                                    <span class="badge badge-danger px-3 py-2" style="font-size: 1rem;"><i class="fas fa-times-circle mr-1"></i>TIDAK LULUS</span>
                                @else
                                    <span class="badge badge-warning px-3 py-2" style="font-size: 1rem;"><i class="fas fa-clock mr-1"></i>CADANGAN</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Penetapan</strong></td>
                            <td>{{ $kelulusan->tanggal_kelulusan ? $kelulusan->tanggal_kelulusan->format('d F Y, H:i') . ' WIB' : '-' }}</td>
                        </tr>
                        @if($kelulusan->catatan)
                        <tr>
                            <td><strong>Catatan</strong></td>
                            <td>{{ $kelulusan->catatan }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- WhatsApp Group (hanya untuk yang lulus) --}}
            @if($kelulusan->status === 'lulus' && $setting->tampilkan_link_wa && $setting->link_grup_wa)
            <div class="wa-group-card mb-4">
                <div class="d-flex align-items-center">
                    <div class="mr-4">
                        <i class="fab fa-whatsapp fa-4x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1">Bergabung ke Grup WhatsApp</h4>
                        <p class="mb-2" style="opacity: 0.9;">{{ $setting->nama_grup_wa ?? 'Grup PPDB Siswa Baru' }}</p>
                        <a href="{{ $setting->link_grup_wa }}" target="_blank" class="btn btn-lg" style="background: #fff; color: #128C7E; font-weight: 600;">
                            <i class="fab fa-whatsapp mr-2" style="color: #25D366;"></i>Gabung Sekarang
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Info Penting (hanya untuk yang lulus) --}}
            @if($kelulusan->status === 'lulus' && $setting->catatan_daftar_ulang)
            <div class="card">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title text-white"><i class="fas fa-exclamation-circle mr-2"></i>INFO PENTING</h3>
                </div>
                <div class="card-body">
                    <div class="info-penting-content">
                        {!! $setting->catatan_daftar_ulang !!}
                    </div>
                </div>
            </div>
            @endif

            {{-- Dokumen Persyaratan (hanya untuk yang lulus) --}}
            @if($kelulusan->status === 'lulus' && $setting->tampilkan_dokumen && $setting->dokumen_persyaratan && count($setting->dokumen_persyaratan) > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Dokumen Persyaratan Daftar Ulang</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Siapkan dokumen berikut untuk daftar ulang:</p>
                    @foreach($setting->dokumen_persyaratan as $i => $dok)
                    <div class="dokumen-item-card">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-primary rounded-circle mr-3" style="width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;">{{ $i + 1 }}</span>
                            <span>{{ $dok }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Surat Pernyataan Orang Tua (hanya untuk yang lulus) --}}
            @if($kelulusan->status === 'lulus')
            <div class="card">
                <div class="card-header bg-gradient-warning">
                    <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i>Surat Pernyataan Orang Tua/Wali</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                    </div>
                    <p class="text-muted mb-3">
                        Download dan cetak surat pernyataan orang tua/wali.<br>
                        <strong>Wajib dibawa saat rapat wali dan daftar ulang.</strong>
                    </p>
                    <a href="{{ route('pendaftar.kelulusan.surat-pernyataan') }}" target="_blank" class="btn btn-danger btn-lg">
                        <i class="fas fa-download mr-2"></i>Download Surat Pernyataan
                    </a>
                </div>
            </div>
            @endif

        @else
            {{-- Belum ada data kelulusan --}}
            <div class="kelulusan-hero belum">
                <div class="kelulusan-icon">
                    <i class="fas fa-hourglass-half fa-4x"></i>
                </div>
                <div class="kelulusan-status-text mb-3" style="font-size: 1.8rem;">
                    MENUNGGU PENGUMUMAN
                </div>
                <p style="font-size: 1.1rem; opacity: 0.9;">
                    Hasil kelulusan belum tersedia. Mohon periksa secara berkala.
                </p>
            </div>
        @endif

        <div class="text-center mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

@section('js')
@if($kelulusan && $kelulusan->status === 'lulus')
<script>
// Confetti effect for lulus students
$(function() {
    var hero = document.getElementById('kelulusanHero');
    var colors = ['#ff0', '#f0f', '#0ff', '#ff6b6b', '#51cf66', '#ffd43b', '#4dabf7'];
    for (var i = 0; i < 50; i++) {
        setTimeout(function() {
            var confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-10px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            confetti.style.animationDelay = Math.random() * 2 + 's';
            hero.appendChild(confetti);
            setTimeout(function() { confetti.remove(); }, 5000);
        }, i * 100);
    }
});
</script>
@endif
@endsection
