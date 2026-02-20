@extends('layouts.pendaftar')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('css')
@if($kelulusanData)
<style>
/* ========== ENVELOPE ANIMATION ========== */
.kelulusan-envelope-wrapper {
    perspective: 1000px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem 0;
}

.envelope-container {
    position: relative;
    width: 280px;
    height: 200px;
    cursor: pointer;
    transform-style: preserve-3d;
}

.envelope {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 0 0 12px 12px;
    background: linear-gradient(145deg, #f0e6d3, #e8dcc8);
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    overflow: visible;
    transition: transform 0.3s ease;
}

.envelope:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
}

.envelope-body {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(145deg, #f0e6d3, #e8dcc8);
    border-radius: 0 0 12px 12px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
}

.envelope-body::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, transparent 49.5%, #d4c5a9 49.5%, #d4c5a9 50.5%, transparent 50.5%),
                linear-gradient(225deg, transparent 49.5%, #d4c5a9 49.5%, #d4c5a9 50.5%, transparent 50.5%);
    z-index: 1;
}

.envelope-seal {
    width: 50px;
    height: 50px;
    background: linear-gradient(145deg, #c0392b, #e74c3c);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 2px 8px rgba(192,57,43,0.4);
    transition: all 0.3s ease;
}

.envelope-seal i {
    color: #fff;
    font-size: 1.2rem;
}

.envelope-flap {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 4;
    transform-origin: top center;
    transition: transform 0.6s ease;
}

.envelope-flap-inner {
    width: 0;
    height: 0;
    border-left: 140px solid transparent;
    border-right: 140px solid transparent;
    border-top: 110px solid #ddd0b8;
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
}

/* Letter inside */
.letter {
    position: absolute;
    top: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(0);
    width: 240px;
    background: white;
    border-radius: 8px;
    padding: 1.5rem 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    z-index: 1;
    opacity: 0;
    transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-align: center;
}

.letter .letter-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.letter .letter-status {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
}

.letter .letter-msg {
    font-size: 0.78rem;
    color: #666;
    line-height: 1.4;
}

/* Opened state */
.envelope-container.opened .envelope-flap {
    transform: rotateX(180deg);
}

.envelope-container.opened .letter {
    opacity: 1;
    transform: translateX(-50%) translateY(-140px);
}

.envelope-container.opened .envelope-seal {
    opacity: 0;
    transform: scale(0);
}

/* Pulse on seal before open */
.envelope-seal {
    animation: sealPulse 2s infinite;
}

.envelope-container.opened .envelope-seal {
    animation: none;
}

@keyframes sealPulse {
    0%, 100% { box-shadow: 0 2px 8px rgba(192,57,43,0.4); }
    50% { box-shadow: 0 2px 20px rgba(192,57,43,0.8), 0 0 40px rgba(231,76,60,0.3); }
}

/* Tap hint */
.tap-hint {
    text-align: center;
    margin-top: 1rem;
    color: #999;
    font-size: 0.85rem;
    animation: tapBounce 1.5s infinite;
}

.envelope-container.opened ~ .tap-hint {
    display: none;
}

@keyframes tapBounce {
    0%, 100% { transform: translateY(0); opacity: 0.6; }
    50% { transform: translateY(-5px); opacity: 1; }
}

/* Result card after envelope */
.kelulusan-result-card {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    transition-delay: 0.8s;
}

.kelulusan-result-card.show {
    opacity: 1;
    transform: translateY(0);
}

/* Sparkle particles */
.sparkle-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 9999;
    overflow: hidden;
}

.sparkle {
    position: absolute;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: sparkleFloat 2s ease-out forwards;
    pointer-events: none;
}

@keyframes sparkleFloat {
    0% { 
        opacity: 1; 
        transform: translateY(0) scale(1) rotate(0deg); 
    }
    50% { 
        opacity: 0.8; 
    }
    100% { 
        opacity: 0; 
        transform: translateY(-200px) scale(0) rotate(360deg); 
    }
}

/* Confetti rain for lulus */
.confetti-piece {
    position: fixed;
    width: 10px;
    height: 20px;
    top: -20px;
    z-index: 9998;
    animation: confettiFall linear forwards;
    pointer-events: none;
}

@keyframes confettiFall {
    0% { 
        top: -20px; 
        opacity: 1;
        transform: rotateZ(0deg) rotateY(0deg); 
    }
    75% { opacity: 1; }
    100% { 
        top: 105vh; 
        opacity: 0;
        transform: rotateZ(720deg) rotateY(360deg); 
    }
}

/* Jadwal info banner */
.kelulusan-jadwal-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.2rem 1.5rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.kelulusan-jadwal-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.kelulusan-jadwal-banner::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
</style>
@endif
@endsection

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white mb-1">Selamat Datang, {{ $calonSiswa->nama_lengkap }}!</h4>
                        <p class="text-white-50 mb-0">
                            No. Registrasi: <strong class="text-white">{{ $calonSiswa->nomor_registrasi }}</strong>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="status-badge status-{{ $calonSiswa->status_verifikasi }}">
                            @if($calonSiswa->status_verifikasi === 'verified')
                                <i class="fas fa-check-circle"></i> Terverifikasi
                            @elseif($calonSiswa->status_verifikasi === 'pending')
                                <i class="fas fa-clock"></i> Menunggu Verifikasi
                            @elseif($calonSiswa->status_verifikasi === 'revision')
                                <i class="fas fa-exclamation-circle"></i> Perlu Revisi
                            @else
                                {{ ucfirst($calonSiswa->status_verifikasi) }}
                            @endif
                        </span>
                    </div>
                </div>
                
                {{-- Keterangan Status Verifikasi --}}
                @if($calonSiswa->is_finalisasi)
                    @if($calonSiswa->status_verifikasi === 'verified' && $calonSiswa->nomor_tes)
                        <div class="alert alert-success mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-check-circle text-white mr-2"></i>
                            <span class="text-white"><strong>Sudah Diverifikasi!</strong> Silahkan cetak Kartu Tes untuk mengikuti ujian. <a href="#" data-toggle="modal" data-target="#kartuUjianModal" class="text-white" style="text-decoration: underline; font-weight: bold;">Klik Disini</a></span>
                        </div>
                    @elseif($calonSiswa->status_verifikasi === 'pending')
                        <div class="alert alert-warning mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-hourglass-half text-white mr-2"></i>
                            <span class="text-white"><strong>Menunggu Verifikasi Berkas Oleh Panitia.</strong> Mohon tunggu 1-3 hari kerja.</span>
                        </div>
                    @elseif($calonSiswa->status_verifikasi === 'revision')
                        <div class="alert alert-danger mt-3 mb-0 py-2" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-exclamation-triangle text-white mr-2"></i>
                            <span class="text-white"><strong>Perlu Revisi!</strong> Silahkan periksa dan perbaiki dokumen yang diminta.</span>
                        </div>
                    @endif
                @endif

                {{-- Detail Dokumen Bermasalah --}}
                @if(isset($dokumenBermasalah) && $dokumenBermasalah->count() > 0)
                    <div class="mt-3 p-3" style="background: rgba(220, 53, 69, 0.3); border: 1px solid rgba(255,255,255,0.4); border-radius: 8px;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-file-exclamation text-white mr-2" style="font-size: 1.2rem;"></i>
                            <strong class="text-white">{{ $dokumenBermasalah->count() }} Dokumen Perlu Diperbaiki:</strong>
                        </div>
                        @foreach($dokumenBermasalah as $dok)
                            <div class="mb-2 ml-4 p-2" style="background: rgba(255,255,255,0.1); border-radius: 5px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-white">
                                        <i class="fas fa-file mr-1"></i>
                                        {{ ucwords(str_replace('_', ' ', $dok->jenis_dokumen)) }}
                                    </span>
                                    @if($dok->status_verifikasi === 'revision')
                                        <span class="badge" style="background: #ffc107; color: #000;">Perlu Revisi</span>
                                    @else
                                        <span class="badge" style="background: #dc3545; color: #fff;">Tidak Valid</span>
                                    @endif
                                </div>
                                @if($dok->catatan_verifikasi)
                                    <small class="text-white-50 d-block mt-1">
                                        <i class="fas fa-comment-alt mr-1"></i>{{ $dok->catatan_verifikasi }}
                                    </small>
                                @endif
                            </div>
                        @endforeach
                        <a href="{{ route('pendaftar.dokumen') }}" class="btn btn-light btn-sm mt-2">
                            <i class="fas fa-upload mr-1"></i> Upload Ulang Dokumen
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ========== KELULUSAN INFO ON DASHBOARD ========== --}}
@if($kelulusanData)
<div class="row" id="kelulusan-section">
    <div class="col-12">
        @if($kelulusanData['kelulusan'])
            {{-- Pendaftar sudah punya status kelulusan → tampilkan amplop --}}
            @php
                $kStatus = $kelulusanData['kelulusan']->status;
                $kSetting = $kelulusanData['setting'];
                $envelopeAlreadyOpened = $kelulusanData['envelope_opened'] ?? false;
                $isLulus = $kStatus === 'lulus';
                $isCadangan = $kStatus === 'cadangan';
                $statusLabel = $isLulus ? 'LULUS' : ($isCadangan ? 'CADANGAN' : 'TIDAK LULUS');
                $statusColor = $isLulus ? '#27ae60' : ($isCadangan ? '#f39c12' : '#e74c3c');
                $statusIcon = $isLulus ? '🎉' : ($isCadangan ? '⏳' : '😔');
                $statusMsg = $isLulus 
                    ? ($kSetting->pesan_lulus ?? 'Selamat! Anda dinyatakan LULUS seleksi PPDB.')
                    : ($isCadangan 
                        ? 'Anda masuk dalam daftar CADANGAN. Silakan pantau informasi lebih lanjut.'
                        : ($kSetting->pesan_tidak_lulus ?? 'Mohon maaf, Anda belum dinyatakan lulus pada seleksi PPDB tahun ini.'));
            @endphp
            <div class="card" style="border: 2px solid {{ $statusColor }}; border-radius: 16px; overflow: hidden;">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <h5 class="font-weight-bold" style="color: {{ $statusColor }};">
                            <i class="fas fa-graduation-cap mr-2"></i>{{ $kSetting->judul_pengumuman ?? 'Pengumuman Kelulusan' }}
                        </h5>
                    </div>

                    {{-- Envelope Animation --}}
                    @if(!$envelopeAlreadyOpened)
                    <div class="kelulusan-envelope-wrapper">
                        <div>
                            <div class="envelope-container" id="envelopeContainer" onclick="openEnvelope()">
                                {{-- Letter (behind envelope) --}}
                                <div class="letter" id="envelopeLetter">
                                    <div class="letter-icon">{{ $statusIcon }}</div>
                                    <div class="letter-status" style="color: {{ $statusColor }};">{{ $statusLabel }}</div>
                                    <div class="letter-msg">{{ Str::limit($statusMsg, 80) }}</div>
                                </div>
                                
                                {{-- Envelope --}}
                                <div class="envelope">
                                    <div class="envelope-body">
                                        <div class="envelope-seal">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Flap --}}
                                <div class="envelope-flap">
                                    <div class="envelope-flap-inner"></div>
                                </div>
                            </div>
                            <div class="tap-hint" id="tapHint">
                                <i class="fas fa-hand-pointer mr-1"></i> Ketuk amplop untuk membuka
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Result card (appears after envelope opens, or immediately if already opened) --}}
                    <div class="kelulusan-result-card {{ $envelopeAlreadyOpened ? 'show' : '' }}" id="kelulusanResultCard">
                        <div class="text-center p-3" style="background: {{ $statusColor }}10; border-radius: 12px; border: 1px solid {{ $statusColor }}30;">
                            <h4 class="font-weight-bold mb-2" style="color: {{ $statusColor }};">
                                {{ $statusIcon }} Anda Dinyatakan {{ $statusLabel }}
                            </h4>
                            <p class="mb-3 text-muted" style="max-width: 500px; margin: 0 auto;">{{ $statusMsg }}</p>
                            <a href="{{ route('pendaftar.kelulusan') }}" class="btn btn-sm text-white px-4 py-2" style="background: {{ $statusColor }}; border-radius: 25px;">
                                <i class="fas fa-arrow-right mr-1"></i> Lihat Detail Kelulusan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- Belum ada status kelulusan, tapi pengumuman aktif --}}
            @php $kSetting = $kelulusanData['setting']; @endphp
            <div class="card" style="border: 2px solid #6c757d; border-radius: 16px; overflow: hidden;">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="mb-3">
                            <span class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                  style="width: 80px; height: 80px;">
                                <i class="fas fa-user-slash fa-2x text-white"></i>
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-secondary">
                            <i class="fas fa-info-circle mr-1"></i> Tidak Termasuk Dalam Pengumuman
                        </h5>
                        <p class="text-muted mb-3">
                            Anda tidak terdaftar dalam pengumuman hasil seleksi PPDB.<br>
                            Hal ini bisa terjadi karena tidak mengikuti tes atau tidak melengkapi persyaratan.
                        </p>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-phone-alt mr-1"></i> Silakan hubungi panitia PPDB untuk informasi lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif

{{-- Notifikasi Pindah Gelombang --}}
@if(isset($gelombangBerikutnya) && $gelombangBerikutnya)
<div class="row" id="pindah-gelombang-section">
    <div class="col-12">
        <div class="card" style="border: 2px solid #17a2b8; border-radius: 16px; overflow: hidden; background: linear-gradient(135deg, #f0f9ff 0%, #e8f4fd 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="mr-3">
                        <span class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-exchange-alt fa-lg text-white"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="font-weight-bold text-info mb-1">
                            <i class="fas fa-bullhorn mr-1"></i> {{ $gelombangBerikutnya->nama }} {{ $calonSiswa->jalurPendaftaran->nama ?? '' }} Sudah Dibuka!
                        </h5>
                        <p class="text-muted mb-2">
                            Anda dapat mendaftar ulang di <strong>{{ $gelombangBerikutnya->nama }}</strong> tanpa perlu registrasi dari awal.
                            Data pribadi & dokumen Anda akan dipertahankan.
                        </p>
                        <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 15px;">
                            <span class="text-dark">
                                <i class="fas fa-users text-info mr-1"></i>
                                Sisa kuota: <strong>{{ $gelombangBerikutnya->sisaKuota() }}</strong> dari {{ $gelombangBerikutnya->kuotaEfektif() }}
                            </span>
                            <span class="text-dark">
                                <i class="fas fa-calendar-times text-danger mr-1"></i>
                                Batas: <strong>{{ \Carbon\Carbon::parse($gelombangBerikutnya->tanggal_tutup)->locale('id')->translatedFormat('d F Y') }}</strong>
                            </span>
                        </div>
                        <button type="button" class="btn btn-info btn-lg px-4" onclick="konfirmasiPindahGelombang()" id="btnPindahGelombang">
                            <i class="fas fa-arrow-right mr-2"></i> Daftar {{ $gelombangBerikutnya->nama }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Compact Checklist Status --}}
@php
    $gelombangStatus = $calonSiswa->gelombangPendaftaran ? $calonSiswa->gelombangPendaftaran->status : null;
    $pendaftaranDitutup = $gelombangStatus && $gelombangStatus !== 'open';
@endphp
@if(!$kelengkapan['finalisasi'] && !$pendaftaranDitutup)
<div class="row">
    <div class="col-12">
        <div class="card card-outline {{ $kelengkapan['semua_lengkap'] ? 'card-success' : 'card-warning' }}" style="border-top-width: 3px;">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        @if($kelengkapan['semua_lengkap'])
                            <i class="fas fa-check-circle text-success mr-2" style="font-size: 1.3rem;"></i>
                            <span class="font-weight-bold text-success">Semua Data Lengkap!</span>
                            <span class="text-muted ml-2">Silahkan lakukan finalisasi</span>
                        @else
                            <i class="fas fa-clipboard-list text-warning mr-2" style="font-size: 1.3rem;"></i>
                            <span class="font-weight-bold">Lengkapi Data:</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-1" style="gap: 0.4rem;">
                        {{-- Data Pribadi --}}
                        <a href="{{ route('pendaftar.data-pribadi') }}" class="badge badge-{{ $kelengkapan['data_diri'] ? 'success' : 'secondary' }} px-2 py-1" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-{{ $kelengkapan['data_diri'] ? 'check' : 'times' }} mr-1"></i>Data Pribadi
                        </a>
                        
                        {{-- Data Orang Tua --}}
                        <a href="{{ route('pendaftar.data-ortu') }}" class="badge badge-{{ $kelengkapan['data_ortu'] ? 'success' : 'secondary' }} px-2 py-1" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-{{ $kelengkapan['data_ortu'] ? 'check' : 'times' }} mr-1"></i>Data Ortu
                        </a>
                        
                        {{-- Dokumen --}}
                        <a href="{{ route('pendaftar.dokumen') }}" class="badge badge-{{ $kelengkapan['dokumen'] ? 'success' : 'secondary' }} px-2 py-1" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-{{ $kelengkapan['dokumen'] ? 'check' : 'times' }} mr-1"></i>Dokumen ({{ $kelengkapan['dokumen_count'] }}/{{ $kelengkapan['dokumen_total'] }})
                        </a>
                        
                        {{-- Nilai Rapor + File Upload - hijau hanya jika nilai DAN file lengkap --}}
                        <a href="{{ route('pendaftar.nilai-rapor') }}" class="badge badge-{{ $kelengkapan['rapor_lengkap'] ? 'success' : 'secondary' }} px-2 py-1" style="font-size: 0.8rem; text-decoration: none;" 
                           title="Nilai: {{ $kelengkapan['nilai_rapor_terisi'] }}/5 semester, File: {{ $kelengkapan['file_rapor_uploaded'] }}/5 uploaded">
                            <i class="fas fa-{{ $kelengkapan['rapor_lengkap'] ? 'check' : 'times' }} mr-1"></i>Rapor 
                            <small>({{ $kelengkapan['nilai_rapor_terisi'] }}/5 <i class="fas fa-file-alt"></i> {{ $kelengkapan['file_rapor_uploaded'] }}/5 <i class="fas fa-upload"></i>)</small>
                        </a>
                        
                        {{-- Pilihan Program (jika aktif) --}}
                        @if($kelengkapan['pilihan_program_aktif'])
                        <a href="{{ route('pendaftar.pilihan-program') }}" class="badge badge-{{ $kelengkapan['pilihan_program_lengkap'] ? 'success' : 'secondary' }} px-2 py-1" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-{{ $kelengkapan['pilihan_program_lengkap'] ? 'check' : 'times' }} mr-1"></i>Pilihan Program
                        </a>
                        @endif
                        
                        {{-- Finalisasi --}}
                        @php
                            $gelombangMasihBuka = $calonSiswa->gelombangPendaftaran && $calonSiswa->gelombangPendaftaran->status === 'open';
                        @endphp
                        @if(!$gelombangMasihBuka)
                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.8rem;" title="Pendaftaran gelombang {{ $calonSiswa->gelombangPendaftaran->nama ?? '' }} sudah ditutup">
                            <i class="fas fa-ban mr-1"></i>Pendaftaran Ditutup
                        </span>
                        @elseif($kelengkapan['semua_lengkap'])
                        <a href="{{ route('pendaftar.finalisasi') }}" class="badge badge-primary px-2 py-1" style="font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-arrow-right mr-1"></i>Finalisasi
                        </a>
                        @else
                        <span class="badge badge-light px-2 py-1" style="font-size: 0.8rem;">
                            <i class="fas fa-lock mr-1"></i>Finalisasi
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Info Cards -->
    <div class="col-12">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $progress['overall'] }}%</h3>
                <p>Progress Pendaftaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Progress Card -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Progress Pendaftaran
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Data Pribadi</span>
                        <span class="font-weight-bold">{{ $progress['data_diri'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['data_diri'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Data Orang Tua</span>
                        <span class="font-weight-bold">{{ $progress['data_ortu'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['data_ortu'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Upload Dokumen</span>
                        <span class="font-weight-bold">{{ $progress['dokumen'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['dokumen'] }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Nilai Rapor</span>
                        <span class="font-weight-bold">{{ $progress['nilai_rapor'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['nilai_rapor'] }}%"></div>
                    </div>
                </div>
                
                @if(isset($progress['pilihan_program']))
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Pilihan Program</span>
                        <span class="font-weight-bold">{{ $progress['pilihan_program'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['pilihan_program'] }}%"></div>
                    </div>
                </div>
                @endif
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Verifikasi</span>
                        <span class="font-weight-bold">{{ $progress['verifikasi'] }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress['verifikasi'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.data-pribadi') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Data Pribadi</h6>
                                @if($calonSiswa->data_diri_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.data-ortu') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Data Orang Tua</h6>
                                @if($calonSiswa->data_ortu_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.nilai-rapor') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-warning">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Nilai Rapor</h6>
                                @if($calonSiswa->nilai_rapor_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.dokumen') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-info">
                                    <i class="fas fa-file-upload"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Upload Dokumen</h6>
                                @if($calonSiswa->data_dokumen_completed)
                                    <small class="text-success"><i class="fas fa-check"></i> Lengkap</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Belum Lengkap</small>
                                @endif
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.status') }}" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-warning">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Status</h6>
                                <small class="text-muted">Lihat Status</small>
                            </div>
                        </a>
                    </div>
                    
                    {{-- Lokasi Card --}}
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card quick-action-card h-100 text-center p-3" id="locationCard" style="cursor: pointer;" onclick="requestLocation()">
                            @if($calonSiswa->registration_location_source)
                                <div class="icon text-success">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Lokasi</h6>
                                <small class="text-success"><i class="fas fa-check"></i> Terdeteksi</small>
                            @else
                                <div class="icon text-danger" id="locationIcon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h6 class="mt-2 mb-0" id="locationTitle">Lokasi</h6>
                                <small class="text-danger" id="locationStatus">
                                    <i class="fas fa-times"></i> Belum Aktif
                                    @if($wajibLokasi)<span class="badge badge-danger ml-1" style="font-size: 0.6rem;">WAJIB</span>@endif
                                </small>
                            @endif
                        </div>
                    </div>
                    
                    @if($calonSiswa->is_finalisasi)
                    <div class="col-md-4 col-6 mb-3">
                        <a href="{{ route('pendaftar.cetak-bukti-registrasi.preview') }}" target="_blank" class="text-decoration-none">
                            <div class="card quick-action-card h-100 text-center p-3">
                                <div class="icon text-primary">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Bukti Registrasi</h6>
                                <small class="text-muted">Preview & Download</small>
                            </div>
                        </a>
                    </div>
                    
                    @if($calonSiswa->nomor_tes)
                    <div class="col-md-4 col-6 mb-3">
                        <a href="#" class="text-decoration-none" data-toggle="modal" data-target="#kartuUjianModal">
                            <div class="card quick-action-card h-100 text-center p-3 border-success">
                                <div class="icon text-success">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <h6 class="mt-2 mb-0">Kartu Ujian</h6>
                                <small class="text-success"><i class="fas fa-print"></i> Siap Cetak</small>
                            </div>
                        </a>
                    </div>
                    @else
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card quick-action-card h-100 text-center p-3" style="opacity: 0.6;">
                            <div class="icon text-secondary">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h6 class="mt-2 mb-0">Kartu Ujian</h6>
                            <small class="text-warning"><i class="fas fa-clock"></i> Menunggu Verifikasi</small>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Profile Card -->
        @php
            $fotoProfileDashboard = $calonSiswa->dokumen()->where('jenis_dokumen', 'foto')->first();
            $fotoProfileDashboardUrl = $fotoProfileDashboard ? asset('storage/' . $fotoProfileDashboard->file_path) : null;
        @endphp
        <div class="card">
            <div class="card-body text-center">
                @if($fotoProfileDashboardUrl)
                    <img src="{{ $fotoProfileDashboardUrl }}" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #667eea;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($calonSiswa->nama_lengkap) }}&size=150&background=667eea&color=fff'">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($calonSiswa->nama_lengkap) }}&size=150&background=667eea&color=fff" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px;">
                @endif
                <h5 class="mb-1">{{ $calonSiswa->nama_lengkap }}</h5>
                <p class="text-muted mb-2">NISN: {{ $calonSiswa->nisn }}</p>
                @if($calonSiswa->nisn_valid)
                    <span class="badge badge-success"><i class="fas fa-check"></i> NISN Terverifikasi</span>
                @endif
            </div>
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-6 border-right">
                        <small class="text-muted d-block">Terdaftar</small>
                        <strong>{{ $calonSiswa->created_at->format('d M Y') }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tahun Ajaran</small>
                        <strong>{{ $calonSiswa->tahunPelajaran->nama ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Status Timeline
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="timeline timeline-inverse p-3">
                    <div class="time-label">
                        <span class="bg-success">Pendaftaran</span>
                    </div>
                    <div>
                        <i class="fas fa-user-plus bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->created_at->format('d M Y H:i') }}</span>
                            <h3 class="timeline-header">Akun Dibuat</h3>
                            <div class="timeline-body">
                                Pendaftaran berhasil dilakukan
                            </div>
                        </div>
                    </div>

                    @if($calonSiswa->status_verifikasi === 'verified')
                    <div class="time-label">
                        <span class="bg-info">Verifikasi</span>
                    </div>
                    <div>
                        <i class="fas fa-check bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->verified_at?->format('d M Y H:i') ?? '-' }}</span>
                            <h3 class="timeline-header">Data Terverifikasi</h3>
                            <div class="timeline-body">
                                {{ $calonSiswa->catatan_verifikasi ?? 'Data pendaftaran telah diverifikasi' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($calonSiswa->status_admisi !== 'pending')
                    <div class="time-label">
                        <span class="bg-{{ $calonSiswa->status_admisi === 'diterima' ? 'success' : ($calonSiswa->status_admisi === 'ditolak' ? 'danger' : 'warning') }}">
                            Hasil Seleksi
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-{{ $calonSiswa->status_admisi === 'diterima' ? 'check-circle' : 'times-circle' }} bg-{{ $calonSiswa->status_admisi === 'diterima' ? 'success' : 'danger' }}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $calonSiswa->approved_at?->format('d M Y H:i') ?? '-' }}</span>
                            <h3 class="timeline-header">{{ ucfirst($calonSiswa->status_admisi) }}</h3>
                            <div class="timeline-body">
                                {{ $calonSiswa->catatan_admisi ?? '-' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$calonSiswa->registration_location_source)
@push('scripts')
<script>
function requestLocation() {
    const card = document.getElementById('locationCard');
    const icon = document.getElementById('locationIcon');
    const title = document.getElementById('locationTitle');
    const status = document.getElementById('locationStatus');
    
    // Show loading state
    icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    icon.className = 'icon text-primary';
    status.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Mendeteksi...';
    status.className = 'text-primary';
    
    if (!navigator.geolocation) {
        handleFallbackIP('Browser tidak mendukung GPS');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            // GPS success
            saveLocation({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy,
                altitude: position.coords.altitude,
                location_source: 'gps'
            });
        },
        function(error) {
            let errorMsg = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin ditolak';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Tidak tersedia';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Waktu habis';
                    break;
            }
            handleFallbackIP(errorMsg);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

function handleFallbackIP(errorMsg) {
    const status = document.getElementById('locationStatus');
    status.innerHTML = '<i class="fas fa-globe"></i> Via IP...';
    status.className = 'text-info';
    
    // Use IP fallback
    saveLocation({
        location_source: 'ip'
    });
}

function saveLocation(data) {
    fetch('{{ route("pendaftar.update-location") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        const icon = document.getElementById('locationIcon');
        const status = document.getElementById('locationStatus');
        const card = document.getElementById('locationCard');
        
        if (result.success) {
            // Success state
            icon.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
            icon.className = 'icon text-success';
            
            if (data.location_source === 'gps') {
                status.innerHTML = '<i class="fas fa-check"></i> GPS';
            } else {
                status.innerHTML = '<i class="fas fa-check"></i> IP';
            }
            status.className = 'text-success';
            
            // Add location info as tooltip
            const locationParts = [result.data.city, result.data.region].filter(Boolean);
            if (locationParts.length) {
                card.title = locationParts.join(', ');
            }
            
            // Remove click handler
            card.onclick = null;
            card.style.cursor = 'default';
        } else {
            resetLocationCard(result.message || 'Gagal menyimpan');
        }
    })
    .catch(error => {
        resetLocationCard('Error');
    });
}

function resetLocationCard(message) {
    const icon = document.getElementById('locationIcon');
    const status = document.getElementById('locationStatus');
    
    icon.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
    icon.className = 'icon text-danger';
    status.innerHTML = '<i class="fas fa-times"></i> ' + message;
    status.className = 'text-danger';
}
</script>
@endpush
@endif

{{-- Modal Informasi Pendaftar (muncul setelah login) --}}
@if($showInfoModal && count($infoList) > 0)
<div class="modal fade" id="modalInfoPendaftar" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-bullhorn mr-2"></i> Informasi Penting</h5>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                @foreach($infoList as $index => $info)
                    <div class="info-item {{ $index > 0 ? 'mt-4 pt-4 border-top' : '' }}">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle mr-2"></i>{{ $info->judul }}
                        </h5>
                        <div class="info-content pl-4">
                            {!! nl2br(e($info->isi)) !!}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block" data-dismiss="modal">
                    <i class="fas fa-check mr-2"></i> Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('js')
@if($showInfoModal && count($infoList) > 0)
<script>
    $(function() {
        $('#modalInfoPendaftar').modal('show');
    });
</script>
@endif

@if($kelulusanData && $kelulusanData['kelulusan'] && !($kelulusanData['envelope_opened'] ?? false))
<script>
let envelopeOpened = false;

function openEnvelope() {
    if (envelopeOpened) return;
    envelopeOpened = true;

    const container = document.getElementById('envelopeContainer');
    const hint = document.getElementById('tapHint');
    const resultCard = document.getElementById('kelulusanResultCard');

    // Open envelope
    container.classList.add('opened');
    hint.style.display = 'none';

    // Collect location data then send AJAX
    function sendEnvelopeLog(locationData) {
        fetch('{{ route("pendaftar.kelulusan.envelope-opened") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(locationData || {})
        });
    }

    // Try to get geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                sendEnvelopeLog({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            function() {
                // Permission denied or error, send without location
                sendEnvelopeLog({});
            },
            { timeout: 5000, maximumAge: 300000 }
        );
    } else {
        sendEnvelopeLog({});
    }

    // Show sparkles from envelope
    createSparkles(container);

    // Show result card after delay
    setTimeout(() => {
        resultCard.classList.add('show');
    }, 400);

    @if($kelulusanData['kelulusan']->status === 'lulus')
    // Confetti rain for lulus!
    setTimeout(() => {
        createConfettiRain();
    }, 600);
    @endif
}

function createSparkles(origin) {
    const rect = origin.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;

    const sparkleContainer = document.createElement('div');
    sparkleContainer.className = 'sparkle-container';
    document.body.appendChild(sparkleContainer);

    const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];

    for (let i = 0; i < 40; i++) {
        setTimeout(() => {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            const color = colors[Math.floor(Math.random() * colors.length)];
            sparkle.style.background = color;
            sparkle.style.boxShadow = `0 0 6px ${color}`;
            sparkle.style.left = (cx + (Math.random() - 0.5) * 200) + 'px';
            sparkle.style.top = (cy + (Math.random() - 0.5) * 100) + 'px';
            sparkle.style.width = (4 + Math.random() * 8) + 'px';
            sparkle.style.height = sparkle.style.width;
            sparkle.style.animationDuration = (1 + Math.random() * 1.5) + 's';
            sparkleContainer.appendChild(sparkle);
        }, i * 30);
    }

    setTimeout(() => sparkleContainer.remove(), 3500);
}

function createConfettiRain() {
    const colors = ['#e74c3c', '#3498db', '#2ecc71', '#f1c40f', '#9b59b6', '#e67e22', '#1abc9c', '#ff6b81'];

    for (let i = 0; i < 80; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti-piece';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.width = (6 + Math.random() * 8) + 'px';
            confetti.style.height = (12 + Math.random() * 12) + 'px';
            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            confetti.style.animationDuration = (2 + Math.random() * 3) + 's';
            confetti.style.animationDelay = '0s';
            confetti.style.opacity = 0.9;
            document.body.appendChild(confetti);

            setTimeout(() => confetti.remove(), 5500);
        }, i * 40);
    }
}

// Auto-scroll to kelulusan section on load
$(function() {
    const section = document.getElementById('kelulusan-section');
    if (section) {
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
});
</script>
@endif

{{-- JS Pindah Gelombang --}}
@if(isset($gelombangBerikutnya) && $gelombangBerikutnya)
<script>
function konfirmasiPindahGelombang() {
    Swal.fire({
        title: 'Pindah ke {{ $gelombangBerikutnya->nama }}?',
        html: `
            <div class="text-left" style="font-size: 14px;">
                <p class="mb-2">Anda akan dipindahkan dari <strong>{{ $calonSiswa->gelombangPendaftaran->nama ?? 'Gelombang sebelumnya' }}</strong> ke <strong>{{ $gelombangBerikutnya->nama }}</strong>.</p>
                <div class="alert alert-info py-2 px-3 mb-2">
                    <small>
                        <i class="fas fa-check mr-1"></i> Data pribadi & dokumen <strong>tetap tersimpan</strong><br>
                        <i class="fas fa-check mr-1"></i> Nomor registrasi akan <strong>diperbarui</strong><br>
                        <i class="fas fa-check mr-1"></i> Riwayat gelombang sebelumnya <strong>tetap tercatat</strong>
                    </small>
                </div>
                <div class="alert alert-warning py-2 px-3 mb-0">
                    <small>
                        <i class="fas fa-exclamation-triangle mr-1"></i> Status kelulusan sebelumnya akan <strong>direset</strong><br>
                        <i class="fas fa-exclamation-triangle mr-1"></i> Tindakan ini <strong>tidak dapat dibatalkan</strong>
                    </small>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-exchange-alt mr-1"></i> Ya, Pindah Gelombang',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("pendaftar.pindah-gelombang") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Gagal pindah gelombang');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Pindah Gelombang!',
                html: `
                    <p>${result.value.message}</p>
                    <p class="mb-0"><strong>Nomor Registrasi Baru:</strong><br>
                    <code style="font-size: 18px;">${result.value.nomor_registrasi_baru}</code></p>
                `,
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(() => {
                window.location.reload();
            });
        }
    });
}
</script>
@endif
@endsection