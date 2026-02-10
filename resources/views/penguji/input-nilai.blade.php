@extends('adminlte::page')

@section('title', 'Input Nilai - ' . ($calonSiswa->nama_lengkap ?? 'Peserta'))

@section('css')
<style>
    /* ===== Two-Column Layout ===== */
    .layout-row { display: flex; gap: 15px; align-items: flex-start; }
    .col-peserta { flex: 0 0 420px; position: sticky; top: 10px; }
    .col-nilai { flex: 1; min-width: 0; }

    @media (max-width: 1200px) {
        .layout-row { flex-direction: column; }
        .col-peserta { flex: none; width: 100%; position: static; }
    }

    /* ===== Peserta Card ===== */
    .peserta-detail-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .peserta-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 14px 16px;
    }
    .peserta-header .avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.5);
        object-fit: cover;
    }
    .peserta-body { padding: 0; }
    .peserta-body .info-section {
        padding: 10px 16px;
        border-bottom: 1px solid #f0f0f0;
    }
    .peserta-body .info-section:last-child { border-bottom: none; }
    .info-section h6 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2px 12px;
    }
    .info-grid.single-col { grid-template-columns: 1fr; }
    .info-item { font-size: 12px; line-height: 1.6; }
    .info-item .label { color: #888; }
    .info-item .value { font-weight: 600; color: #333; }

    /* ===== Dokumen Buttons ===== */
    .dok-btn-grid { display: flex; flex-wrap: wrap; gap: 5px; }
    .dok-btn {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 11px;
        cursor: pointer;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #495057;
        transition: all 0.2s;
        text-decoration: none !important;
    }
    .dok-btn:hover { background: #007bff; color: #fff; border-color: #007bff; }
    .dok-btn i { margin-right: 4px; font-size: 10px; }
    .dok-btn.dok-pdf i { color: #dc3545; }
    .dok-btn.dok-img i { color: #28a745; }
    .dok-btn:hover i { color: #fff; }

    /* ===== Group Labels ===== */
    .dok-group { margin-bottom: 8px; }
    .dok-group-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    /* ===== Document Preview Modal ===== */
    .doc-modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.92);
        z-index: 10000;
    }
    .doc-modal-backdrop.active { display: flex; flex-direction: column; }
    .doc-modal-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background: rgba(0,0,0,0.5);
        flex-shrink: 0;
    }
    .doc-modal-toolbar .doc-title {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }
    .doc-modal-toolbar .toolbar-btns { display: flex; gap: 6px; }
    .doc-modal-toolbar .toolbar-btns .btn {
        padding: 5px 12px;
        font-size: 13px;
        border-radius: 4px;
    }
    .doc-modal-content {
        flex: 1;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        position: relative;
    }
    .doc-modal-content img {
        max-width: 100%;
        max-height: 100%;
        transition: transform 0.15s ease;
        user-select: none;
    }
    .doc-modal-content iframe {
        width: 95%;
        height: 100%;
        border: none;
        border-radius: 6px;
        background: #fff;
    }
    .doc-modal-nav {
        display: flex;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px;
        background: rgba(0,0,0,0.5);
        flex-shrink: 0;
        flex-wrap: wrap;
        max-height: 80px;
        overflow-y: auto;
    }
    .doc-nav-btn {
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 11px;
        cursor: pointer;
        border: 1px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.8);
        transition: all 0.2s;
        white-space: nowrap;
    }
    .doc-nav-btn:hover, .doc-nav-btn.active {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }
    .doc-zoom-info {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
    }

    /* ===== Nilai Input ===== */
    .nilai-input {
        font-size: 1.2rem;
        font-weight: bold;
        text-align: center;
        height: 45px;
    }
    .nilai-input:focus {
        background-color: #fff3cd;
        border-color: #ffc107;
    }
    .komponen-card {
        border-left: 4px solid #007bff;
        margin-bottom: 12px;
    }
    .komponen-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 8px 15px;
    }
    .komponen-card .card-body {
        padding: 12px 15px;
    }
    .sub-komponen-row {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }
    .sub-komponen-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    .sub-komponen-row .rata-rata-box {
        flex: 0 0 auto;
        text-align: center;
        padding: 5px 15px;
        background: #e3f2fd;
        border-radius: 8px;
        min-width: 80px;
    }
    .sub-komponen-row .rata-rata-box .value {
        font-size: 1.4rem;
        font-weight: bold;
        color: #1565c0;
    }
    .sub-komponen-row .rata-rata-box .label-text {
        font-size: 10px;
        color: #666;
    }

    /* ===== Grade Badge ===== */
    .grade-badge {
        display: inline-block;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
        margin-left: 5px;
        vertical-align: middle;
    }
    .grade-sk { background: #ffcdd2; color: #c62828; }
    .grade-k { background: #ffe0b2; color: #e65100; }
    .grade-c { background: #fff9c4; color: #f57f17; }
    .grade-b { background: #c8e6c9; color: #2e7d32; }

    /* ===== Juz Selector ===== */
    .juz-selector { display: flex; flex-wrap: wrap; gap: 4px; }
    .juz-btn {
        width: 36px; height: 36px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px; font-weight: 600;
        background: #fff;
    }
    .juz-btn:hover { border-color: #007bff; color: #007bff; }
    .juz-btn.selected { background: #007bff; color: #fff; border-color: #007bff; }

    /* ===== Bottom Navigation ===== */
    .nav-peserta {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #fff;
        padding: 10px 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    .main-content { padding-bottom: 80px; }

    /* ===== Rubrik Legend ===== */
    .rubrik-table { font-size: 11px; }
    .rubrik-table td, .rubrik-table th { padding: 3px 8px; }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Input Nilai Seleksi</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('penguji.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penguji.ruangan', $ruangUjian->id) }}">{{ $ruangUjian->nama_ruang }}</a></li>
                <li class="breadcrumb-item active">Input Nilai</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
        </div>
    @endif

    <div class="layout-row">
        {{-- ===== LEFT: PESERTA INFO ===== --}}
        <div class="col-peserta">
            <div class="card peserta-detail-card mb-3">
                {{-- Header --}}
                <div class="peserta-header">
                    <div class="d-flex align-items-center">
                        @php
                            $pasFoto = $calonSiswa->dokumen->where('jenis_dokumen', 'foto')->first();
                            $avatarSrc = null;
                            if($pasFoto && $pasFoto->file_path && file_exists(public_path('storage/' . $pasFoto->file_path))) {
                                $avatarSrc = asset('storage/' . $pasFoto->file_path);
                            }
                            if(!$avatarSrc) {
                                $initials = collect(explode(' ', $calonSiswa->nama_lengkap))->take(2)->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
                                $bgColor = $calonSiswa->jenis_kelamin == 'L' ? '3498db' : 'e74c3c';
                                $avatarSrc = 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=80&background=' . $bgColor . '&color=ffffff&bold=true';
                            }
                        @endphp
                        <img src="{{ $avatarSrc }}" class="avatar mr-3" alt="Foto"
                             @if($pasFoto) style="cursor:pointer" onclick="openDocPreview(0)" @endif>
                        <div>
                            <h5 class="mb-0" style="font-weight:700; font-size:16px;">{{ $calonSiswa->nama_lengkap }}</h5>
                            <div class="mt-1">
                                <span class="badge badge-light text-dark" style="font-size:11px;">No. {{ $pesertaRuang->nomor_urut }}</span>
                                @if($calonSiswa->nomor_tes)
                                    <span class="badge badge-warning" style="font-size:11px;">Tes: {{ $calonSiswa->nomor_tes }}</span>
                                @endif
                                @if($calonSiswa->jalurPendaftaran)
                                    <span class="badge" style="background:{{ $calonSiswa->jalurPendaftaran->warna ?? '#17a2b8' }}; color:#fff; font-size:11px;">
                                        {{ $calonSiswa->jalurPendaftaran->nama }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="peserta-body">
                    {{-- Data Diri --}}
                    <div class="info-section">
                        <h6><i class="fas fa-user mr-1"></i>Data Diri</h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">No. Pendaftaran</span><br>
                                <span class="value">{{ $calonSiswa->no_pendaftaran ?? $calonSiswa->nomor_registrasi ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">NISN</span><br>
                                <span class="value">{{ $calonSiswa->nisn ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">L/P</span><br>
                                <span class="value">{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">TTL</span><br>
                                <span class="value">{{ $calonSiswa->tempat_lahir ?? '-' }}, {{ $calonSiswa->tanggal_lahir?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Asal Sekolah</span><br>
                                <span class="value">{{ $calonSiswa->nama_sekolah_asal ?? $calonSiswa->asal_sekolah ?? '-' }}</span>
                            </div>
                            @if($calonSiswa->gelombangPendaftaran)
                            <div class="info-item">
                                <span class="label">Gelombang</span><br>
                                <span class="value">{{ $calonSiswa->gelombangPendaftaran->nama }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="info-section">
                        <h6><i class="fas fa-map-marker-alt mr-1"></i>Alamat</h6>
                        <div class="info-grid single-col">
                            <div class="info-item">
                                <span class="value">{{ $calonSiswa->alamat_lengkap_siswa ?? ($calonSiswa->alamat_siswa ?? '-') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Data Orang Tua --}}
                    @php $ortu = $calonSiswa->ortu; @endphp
                    @if($ortu)
                    <div class="info-section">
                        <h6><i class="fas fa-users mr-1"></i>Orang Tua</h6>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Ayah</span><br>
                                <span class="value">{{ $ortu->nama_ayah ?? '-' }}</span>
                                @if($ortu->pekerjaan_ayah)
                                    <br><span class="label" style="font-size:11px;">{{ $ortu->pekerjaan_ayah_label }}</span>
                                @endif
                            </div>
                            <div class="info-item">
                                <span class="label">Ibu</span><br>
                                <span class="value">{{ $ortu->nama_ibu ?? '-' }}</span>
                                @if($ortu->pekerjaan_ibu)
                                    <br><span class="label" style="font-size:11px;">{{ $ortu->pekerjaan_ibu_label }}</span>
                                @endif
                            </div>
                            <div class="info-item">
                                <span class="label">Penghasilan Ayah</span><br>
                                <span class="value" style="font-size:11px;">{{ $ortu->penghasilan_ayah_label ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Penghasilan Ibu</span><br>
                                <span class="value" style="font-size:11px;">{{ $ortu->penghasilan_ibu_label ?? '-' }}</span>
                            </div>
                            @if($ortu->hp_ayah || $ortu->hp_ibu)
                            <div class="info-item">
                                <span class="label">No. HP</span><br>
                                <span class="value" style="font-size:11px;">
                                    {{ $ortu->hp_ayah ?? '-' }} / {{ $ortu->hp_ibu ?? '-' }}
                                </span>
                            </div>
                            @endif
                            @if($ortu->no_kk)
                            <div class="info-item">
                                <span class="label">No. KK</span><br>
                                <span class="value" style="font-size:11px;">{{ $ortu->no_kk }}</span>
                            </div>
                            @endif
                        </div>
                        @if($ortu->alamat_ortu)
                        <div class="info-grid single-col mt-1">
                            <div class="info-item">
                                <span class="label">Alamat Ortu</span><br>
                                <span class="value" style="font-size:11px;">{{ $ortu->alamat_lengkap_ortu }}</span>
                            </div>
                        </div>
                        @endif
                        @if($ortu->nama_wali)
                        <div class="info-grid mt-1">
                            <div class="info-item">
                                <span class="label">Wali</span><br>
                                <span class="value">{{ $ortu->nama_wali }} ({{ $ortu->hubungan_wali_label ?? '-' }})</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Penghasilan Wali</span><br>
                                <span class="value" style="font-size:11px;">{{ \App\Models\CalonOrtu::PENGHASILAN[$ortu->penghasilan_wali] ?? '-' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Dokumen Utama --}}
                    @if($dokumenList->count() > 0)
                    <div class="info-section">
                        <h6><i class="fas fa-folder-open mr-1"></i>Dokumen Persyaratan ({{ $dokumenList->count() }})</h6>
                        <div class="dok-btn-grid">
                            @foreach($dokumenList as $dok)
                                @php
                                    $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                    $label = $dokumenLabels[$dok->jenis_dokumen] ?? ucfirst(str_replace('_',' ',$dok->jenis_dokumen));
                                @endphp
                                <a href="javascript:void(0)" class="dok-btn {{ $isImg ? 'dok-img' : 'dok-pdf' }} doc-preview-trigger"
                                   data-url="{{ asset('storage/' . $dok->file_path) }}"
                                   data-title="{{ $label }}"
                                   data-type="{{ $isImg ? 'image' : 'pdf' }}">
                                    <i class="fas {{ $isImg ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                    {{ Str::limit($label, 18) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Dokumen Tambahan (Grouped) --}}
                    @if($dokumenTambahan->count() > 0)
                    <div class="info-section">
                        <h6><i class="fas fa-paperclip mr-1"></i>Dokumen Tambahan ({{ $dokumenTambahan->count() }})</h6>
                        @php
                            $grupPrestasi = $dokumenTambahan->filter(fn($d) => in_array($d->jenis_dokumen, ['sertifikat_prestasi', 'piagam']));
                            $grupBantuan = $dokumenTambahan->filter(fn($d) => in_array($d->jenis_dokumen, ['kip', 'pip', 'sktm']));
                            $grupSurat = $dokumenTambahan->filter(fn($d) => in_array($d->jenis_dokumen, ['surat_domisili', 'surat_rekomendasi']));
                            $grupLain = $dokumenTambahan->filter(fn($d) => in_array($d->jenis_dokumen, ['dokumen_lainnya']));
                        @endphp

                        @if($grupPrestasi->count() > 0)
                        <div class="dok-group">
                            <div class="dok-group-label"><i class="fas fa-trophy mr-1 text-warning"></i>Prestasi / Sertifikat</div>
                            <div class="dok-btn-grid">
                                @foreach($grupPrestasi as $dok)
                                    @php
                                        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                        $label = $dok->nama_dokumen ?? ($dokumenTambahanLabels[$dok->jenis_dokumen] ?? ucfirst(str_replace('_',' ',$dok->jenis_dokumen)));
                                    @endphp
                                    <a href="javascript:void(0)" class="dok-btn {{ $isImg ? 'dok-img' : 'dok-pdf' }} doc-preview-trigger"
                                       data-url="{{ asset('storage/' . $dok->file_path) }}"
                                       data-title="{{ $label }}"
                                       data-type="{{ $isImg ? 'image' : 'pdf' }}">
                                        <i class="fas {{ $isImg ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                        {{ Str::limit($label, 20) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($grupBantuan->count() > 0)
                        <div class="dok-group">
                            <div class="dok-group-label"><i class="fas fa-hand-holding-heart mr-1 text-info"></i>KIP / PIP / SKTM</div>
                            <div class="dok-btn-grid">
                                @foreach($grupBantuan as $dok)
                                    @php
                                        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                        $label = $dokumenTambahanLabels[$dok->jenis_dokumen] ?? ucfirst(str_replace('_',' ',$dok->jenis_dokumen));
                                    @endphp
                                    <a href="javascript:void(0)" class="dok-btn {{ $isImg ? 'dok-img' : 'dok-pdf' }} doc-preview-trigger"
                                       data-url="{{ asset('storage/' . $dok->file_path) }}"
                                       data-title="{{ $label }}"
                                       data-type="{{ $isImg ? 'image' : 'pdf' }}">
                                        <i class="fas {{ $isImg ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                        {{ Str::limit($label, 20) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($grupSurat->count() > 0)
                        <div class="dok-group">
                            <div class="dok-group-label"><i class="fas fa-envelope-open-text mr-1 text-secondary"></i>Surat Keterangan</div>
                            <div class="dok-btn-grid">
                                @foreach($grupSurat as $dok)
                                    @php
                                        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                        $label = $dokumenTambahanLabels[$dok->jenis_dokumen] ?? ucfirst(str_replace('_',' ',$dok->jenis_dokumen));
                                    @endphp
                                    <a href="javascript:void(0)" class="dok-btn {{ $isImg ? 'dok-img' : 'dok-pdf' }} doc-preview-trigger"
                                       data-url="{{ asset('storage/' . $dok->file_path) }}"
                                       data-title="{{ $label }}"
                                       data-type="{{ $isImg ? 'image' : 'pdf' }}">
                                        <i class="fas {{ $isImg ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                        {{ Str::limit($label, 20) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($grupLain->count() > 0)
                        <div class="dok-group">
                            <div class="dok-group-label"><i class="fas fa-file-alt mr-1 text-muted"></i>Lainnya</div>
                            <div class="dok-btn-grid">
                                @foreach($grupLain as $dok)
                                    @php
                                        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                        $label = $dok->nama_dokumen ?? ($dokumenTambahanLabels[$dok->jenis_dokumen] ?? 'Dokumen Lainnya');
                                    @endphp
                                    <a href="javascript:void(0)" class="dok-btn {{ $isImg ? 'dok-img' : 'dok-pdf' }} doc-preview-trigger"
                                       data-url="{{ asset('storage/' . $dok->file_path) }}"
                                       data-title="{{ $label }}"
                                       data-type="{{ $isImg ? 'image' : 'pdf' }}">
                                        <i class="fas {{ $isImg ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                        {{ Str::limit($label, 20) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: FORM INPUT NILAI ===== --}}
        <div class="col-nilai">
            <form action="{{ $saveRoute ?? route('penguji.save-nilai', [$ruangUjian->id, $pesertaRuang->id]) }}" method="POST" id="nilaiForm">
                @csrf

                @foreach($bobotList as $bobot)
                    <div class="card komponen-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0" style="font-size:14px;">
                                <i class="fas fa-star mr-1 text-warning"></i>
                                {{ $bobot->nama_komponen }}
                                <span class="badge badge-info ml-1">{{ $bobot->bobot }}%</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($bobot->komponen == 'wawancara')
                                <div class="form-group mb-0">
                                    <label class="mb-1">Nilai Wawancara <span class="text-danger">*</span></label>
                                    <input type="number" name="nilai_wawancara" 
                                           class="form-control nilai-input grade-input" 
                                           value="{{ old('nilai_wawancara', $nilai->nilai_wawancara ?? '') }}"
                                           min="0" max="100" step="0.01" placeholder="0 - 100"
                                           {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>

                            @elseif($bobot->komponen == 'baca_quran')
                                <div class="sub-komponen-row">
                                    <div class="form-group">
                                        <label class="mb-1" style="font-size:12px;">Tajwid</label>
                                        <input type="number" name="nilai_tajwid" 
                                               class="form-control nilai-input sub-baca grade-input" 
                                               value="{{ old('nilai_tajwid', $nilai->nilai_tajwid ?? '') }}"
                                               min="0" max="100" step="0.01" placeholder="0-100"
                                               {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1" style="font-size:12px;">Makhroj</label>
                                        <input type="number" name="nilai_makhroj" 
                                               class="form-control nilai-input sub-baca grade-input"
                                               value="{{ old('nilai_makhroj', $nilai->nilai_makhroj ?? '') }}"
                                               min="0" max="100" step="0.01" placeholder="0-100"
                                               {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1" style="font-size:12px;">Kelancaran</label>
                                        <input type="number" name="nilai_kelancaran" 
                                               class="form-control nilai-input sub-baca grade-input"
                                               value="{{ old('nilai_kelancaran', $nilai->nilai_kelancaran ?? '') }}"
                                               min="0" max="100" step="0.01" placeholder="0-100"
                                               {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                    </div>
                                    <div class="rata-rata-box">
                                        <div class="value" id="rataBaca">-</div>
                                        <div class="label-text">Rata-rata</div>
                                    </div>
                                </div>

                            @elseif($bobot->komponen == 'tulis_quran')
                                <div class="form-group mb-0">
                                    <label class="mb-1">Nilai Tulis Arab <span class="text-danger">*</span></label>
                                    <input type="number" name="nilai_tulis_quran" 
                                           class="form-control nilai-input grade-input"
                                           value="{{ old('nilai_tulis_quran', $nilai->nilai_tulis_quran ?? '') }}"
                                           min="0" max="100" step="0.01" placeholder="0 - 100"
                                           {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>

                            @elseif($bobot->komponen == 'hafalan')
                                <div class="form-group">
                                    <label class="mb-1">Nilai Hafalan <span class="text-danger">*</span></label>
                                    <input type="number" name="nilai_hafalan" 
                                           class="form-control nilai-input grade-input"
                                           value="{{ old('nilai_hafalan', $nilai->nilai_hafalan ?? '') }}"
                                           min="0" max="100" step="0.01" placeholder="0 - 100"
                                           {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="mb-1">Jumlah Juz Hafalan</label>
                                    <div class="juz-selector">
                                        @for($i = 0; $i <= 30; $i++)
                                            <div class="juz-btn {{ ($nilai->jumlah_juz_hafalan ?? 0) == $i ? 'selected' : '' }}" data-juz="{{ $i }}">
                                                {{ $i }}
                                            </div>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="jumlah_juz_hafalan" id="jumlahJuz" value="{{ old('jumlah_juz_hafalan', $nilai->jumlah_juz_hafalan ?? 0) }}">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Catatan --}}
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0" style="font-size:14px;">
                            <i class="fas fa-sticky-note mr-1"></i>Catatan Penguji
                        </h5>
                    </div>
                    <div class="card-body py-2">
                        <textarea name="catatan_penguji" class="form-control" rows="2" 
                                  placeholder="Catatan tambahan (opsional)"
                                  {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>{{ old('catatan_penguji', $nilai->catatan_penguji ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Rubrik Penilaian --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title" style="font-size:13px;">
                            <i class="fas fa-info-circle mr-1"></i>Rubrik Penilaian
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body py-2" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong style="font-size:12px;">Baca / Tulis Arab:</strong>
                                <table class="table table-bordered table-sm rubrik-table mt-1">
                                    <tr><td class="grade-sk" style="width:60px;">SK</td><td>0 - 45</td><td>Sangat Kurang</td></tr>
                                    <tr><td class="grade-k">K</td><td>46 - 55</td><td>Kurang</td></tr>
                                    <tr><td class="grade-c">C</td><td>56 - 74</td><td>Cukup</td></tr>
                                    <tr><td class="grade-b">B</td><td>75 - 100</td><td>Baik</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <strong style="font-size:12px;">Hafalan Al-Qur'an:</strong>
                                <table class="table table-bordered table-sm rubrik-table mt-1">
                                    <tr><td class="grade-sk" style="width:60px;">SK</td><td>0 - 45</td><td>Sangat Kurang</td></tr>
                                    <tr><td class="grade-k">K</td><td>46 - 55</td><td>Kurang</td></tr>
                                    <tr><td class="grade-c">C</td><td>56 - 74</td><td>Cukup</td></tr>
                                    <tr><td class="grade-b">B</td><td>75 - 100</td><td>Baik</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if(!$nilai || !$nilai->exists || $nilai->isEditable())
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" name="action" value="save" class="btn btn-warning btn-block">
                                        <i class="fas fa-save mr-1"></i>Simpan Draft
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="submit" name="action" value="submit" class="btn btn-success btn-block" 
                                            onclick="return confirm('Submit nilai? Nilai yang sudah disubmit tidak bisa diubah lagi.')">
                                        <i class="fas fa-paper-plane mr-1"></i>Submit Final
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-lock mr-1"></i>Nilai sudah disubmit dan tidak bisa diubah.
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

{{-- ===== Document Preview Modal ===== --}}
<div class="doc-modal-backdrop" id="docPreviewModal">
    <div class="doc-modal-toolbar">
        <div class="doc-title" id="docPreviewTitle">Dokumen</div>
        <div class="toolbar-btns">
            <button class="btn btn-sm btn-outline-light" onclick="docZoomIn()" title="Zoom In (+ key)">
                <i class="fas fa-search-plus"></i>
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="docZoomOut()" title="Zoom Out (- key)">
                <i class="fas fa-search-minus"></i>
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="docZoomReset()" title="Reset Zoom">
                <i class="fas fa-expand"></i>
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="docRotate()" title="Rotate (R key)">
                <i class="fas fa-redo"></i>
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="docDownload()" title="Download">
                <i class="fas fa-download"></i>
            </button>
            <span class="doc-zoom-info" id="zoomInfo" style="position:static; margin-left:8px;">100%</span>
            <button class="btn btn-sm btn-danger ml-2" onclick="closeDocPreview()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="doc-modal-content" id="docPreviewContent"></div>
    <div class="doc-modal-nav" id="docPreviewNav"></div>
</div>

{{-- Bottom Navigation --}}
<div class="nav-peserta">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-4 text-left">
                @if($prevPeserta)
                    <a href="{{ $prevRoute ?? route('penguji.input-nilai', [$ruangUjian->id, $prevPeserta]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left mr-1"></i>Sebelumnya
                    </a>
                @endif
            </div>
            <div class="col-4 text-center">
                <a href="{{ $backRoute ?? route('penguji.ruangan', $ruangUjian->id) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list mr-1"></i>Daftar
                </a>
            </div>
            <div class="col-4 text-right">
                @if($nextPeserta)
                    @if(!$nilai || !$nilai->exists || $nilai->isEditable())
                        <button type="button" class="btn btn-sm btn-primary" id="saveAndNext">
                            Simpan & Lanjut <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    @else
                        <a href="{{ $nextRoute ?? route('penguji.input-nilai', [$ruangUjian->id, $nextPeserta]) }}" class="btn btn-sm btn-primary">
                            Selanjutnya <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// ==========================================
// Document Preview System
// ==========================================
var docList = [];
var currentDocIndex = 0;
var currentZoom = 1;
var currentRotation = 0;

$(document).ready(function() {
    // Build document list from triggers
    $('.doc-preview-trigger').each(function(i) {
        docList.push({
            url: $(this).data('url'),
            title: $(this).data('title'),
            type: $(this).data('type')
        });
        $(this).attr('data-doc-index', i);
    });

    // Click to preview
    $(document).on('click', '.doc-preview-trigger', function(e) {
        e.preventDefault();
        openDocPreview(parseInt($(this).attr('data-doc-index')));
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (!$('#docPreviewModal').hasClass('active')) return;
        switch(e.key) {
            case 'Escape': closeDocPreview(); break;
            case 'ArrowLeft': navigateDoc(-1); break;
            case 'ArrowRight': navigateDoc(1); break;
            case '+': case '=': e.preventDefault(); docZoomIn(); break;
            case '-': e.preventDefault(); docZoomOut(); break;
            case 'r': case 'R': docRotate(); break;
            case '0': docZoomReset(); break;
        }
    });

    // Mouse wheel zoom
    $('#docPreviewContent').on('wheel', function(e) {
        if (!$('#docPreviewModal').hasClass('active')) return;
        e.preventDefault();
        if (e.originalEvent.deltaY < 0) docZoomIn(); else docZoomOut();
    });

    // === Juz Selector ===
    $('.juz-btn').on('click', function() {
        @if(!$nilai || !$nilai->exists || $nilai->isEditable())
            $('.juz-btn').removeClass('selected');
            $(this).addClass('selected');
            $('#jumlahJuz').val($(this).data('juz'));
        @endif
    });

    // === Calculate rata-rata Baca Quran ===
    function calcRataBaca() {
        var subs = [];
        $('.sub-baca').each(function() {
            var v = parseFloat($(this).val());
            if (!isNaN(v)) subs.push(v);
        });
        if (subs.length > 0) {
            var avg = subs.reduce((a,b) => a+b, 0) / subs.length;
            $('#rataBaca').text(avg.toFixed(1));
            $('#rataBaca').parent().find('.grade-badge').remove();
            var g = getGrade(avg);
            $('#rataBaca').after('<span class="grade-badge ' + g.cls + '">' + g.label + '</span>');
        } else {
            $('#rataBaca').text('-');
            $('#rataBaca').parent().find('.grade-badge').remove();
        }
    }
    $('.sub-baca').on('input', calcRataBaca);
    calcRataBaca();

    // === Grade Badge ===
    function getGrade(val) {
        if (val <= 45) return {label: 'SK', cls: 'grade-sk'};
        if (val <= 55) return {label: 'K', cls: 'grade-k'};
        if (val <= 74) return {label: 'C', cls: 'grade-c'};
        return {label: 'B', cls: 'grade-b'};
    }

    $('.grade-input:not(.sub-baca)').on('input', function() {
        var v = parseFloat($(this).val());
        $(this).next('.grade-badge').remove();
        if (!isNaN(v)) {
            var g = getGrade(v);
            $(this).after('<span class="grade-badge ' + g.cls + '">' + g.label + '</span>');
        }
    });
    $('.grade-input:not(.sub-baca)').each(function() {
        var v = parseFloat($(this).val());
        if (!isNaN(v)) {
            var g = getGrade(v);
            $(this).after('<span class="grade-badge ' + g.cls + '">' + g.label + '</span>');
        }
    });

    // === Save and Next ===
    $('#saveAndNext').on('click', function() {
        var form = $('#nilaiForm');
        $('<input>').attr({ type: 'hidden', name: 'action', value: 'save' }).appendTo(form);
        $('<input>').attr({ type: 'hidden', name: 'next', value: '{{ $nextPeserta }}' }).appendTo(form);
        form.submit();
    });

    // === Auto-focus first empty input ===
    $('.nilai-input').each(function() {
        if (!$(this).val()) {
            $(this).focus();
            return false;
        }
    });
});

// ==========================================
// Doc Preview Functions
// ==========================================
function openDocPreview(index) {
    if (index < 0 || index >= docList.length) return;
    currentDocIndex = index;
    currentZoom = 1;
    currentRotation = 0;
    updateZoomInfo();

    var doc = docList[index];
    $('#docPreviewTitle').text(doc.title);

    var content = $('#docPreviewContent');
    content.empty();

    if (doc.type === 'image') {
        content.html('<img src="' + doc.url + '" id="docPreviewImg" alt="' + doc.title + '" draggable="false">');
    } else {
        content.html('<iframe src="' + doc.url + '#toolbar=1&navpanes=0" id="docPreviewFrame"></iframe>');
    }

    // Build nav
    var nav = $('#docPreviewNav');
    nav.empty();
    docList.forEach(function(d, i) {
        var cls = i === index ? 'active' : '';
        var icon = d.type === 'image' ? 'fa-image' : 'fa-file-pdf';
        nav.append('<button class="doc-nav-btn ' + cls + '" onclick="openDocPreview(' + i + ')">' +
                   '<i class="fas ' + icon + ' mr-1"></i>' + d.title + '</button>');
    });

    $('#docPreviewModal').addClass('active');
    $('body').css('overflow', 'hidden');
}

function closeDocPreview() {
    $('#docPreviewModal').removeClass('active');
    $('#docPreviewContent').empty();
    $('body').css('overflow', '');
}

function navigateDoc(dir) {
    var i = currentDocIndex + dir;
    if (i >= 0 && i < docList.length) openDocPreview(i);
}

function docZoomIn() {
    currentZoom = Math.min(currentZoom + 0.25, 5);
    applyTransform();
}
function docZoomOut() {
    currentZoom = Math.max(currentZoom - 0.25, 0.25);
    applyTransform();
}
function docZoomReset() {
    currentZoom = 1;
    currentRotation = 0;
    applyTransform();
}
function docRotate() {
    currentRotation = (currentRotation + 90) % 360;
    applyTransform();
}
function applyTransform() {
    var el = document.getElementById('docPreviewImg') || document.getElementById('docPreviewFrame');
    if (el) {
        el.style.transform = 'scale(' + currentZoom + ') rotate(' + currentRotation + 'deg)';
    }
    updateZoomInfo();
}
function updateZoomInfo() {
    $('#zoomInfo').text(Math.round(currentZoom * 100) + '%');
}
function docDownload() {
    if (docList[currentDocIndex]) {
        var a = document.createElement('a');
        a.href = docList[currentDocIndex].url;
        a.download = docList[currentDocIndex].title;
        a.target = '_blank';
        a.click();
    }
}
</script>
@stop
