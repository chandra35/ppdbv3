@extends('adminlte::page')

@section('title', 'Input Nilai - ' . ($calonSiswa->nama_lengkap ?? 'Peserta'))

@section('css')
<style>
    /* ===== Compact Peserta Card ===== */
    .peserta-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .peserta-card .avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.5);
        object-fit: cover;
    }
    .peserta-card .info-table td {
        padding: 2px 8px 2px 0;
        font-size: 13px;
        color: rgba(255,255,255,0.9);
        border: none;
    }
    .peserta-card .info-table td:first-child {
        color: rgba(255,255,255,0.7);
        white-space: nowrap;
        width: 100px;
    }

    /* ===== Dokumen Thumbnails ===== */
    .dok-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .dok-thumb {
        width: 80px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s;
        text-decoration: none !important;
    }
    .dok-thumb:hover {
        transform: scale(1.08);
    }
    .dok-thumb .thumb-img {
        width: 75px;
        height: 55px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #dee2e6;
        display: block;
        margin: 0 auto;
    }
    .dok-thumb .thumb-placeholder {
        width: 75px;
        height: 55px;
        border-radius: 6px;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: #f8f9fa;
    }
    .dok-thumb .thumb-label {
        font-size: 9px;
        color: #666;
        margin-top: 3px;
        line-height: 1.2;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
    .juz-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .juz-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
        font-weight: 600;
        background: #fff;
    }
    .juz-btn:hover { border-color: #007bff; color: #007bff; }
    .juz-btn.selected { background: #007bff; color: #fff; border-color: #007bff; }

    /* ===== Bottom Navigation ===== */
    .nav-peserta {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 10px 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    .main-content { padding-bottom: 80px; }

    /* ===== Lightbox ===== */
    .lightbox-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .lightbox-overlay.active { display: flex; }
    .lightbox-overlay img {
        max-width: 90%;
        max-height: 90vh;
        border-radius: 8px;
    }
    .lightbox-overlay .lightbox-close {
        position: absolute;
        top: 20px; right: 30px;
        color: #fff;
        font-size: 30px;
        cursor: pointer;
        z-index: 10000;
    }
    .lightbox-overlay .lightbox-title {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: #fff;
        font-size: 14px;
        background: rgba(0,0,0,0.6);
        padding: 5px 15px;
        border-radius: 20px;
    }

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

    {{-- ===== DETAIL PENDAFTAR (Compact) ===== --}}
    <div class="peserta-card">
        <div class="d-flex align-items-start">
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
            <img src="{{ $avatarSrc }}" class="avatar mr-3" alt="Foto">
            <div class="flex-grow-1">
                <h5 class="mb-1" style="font-weight:700;">{{ $calonSiswa->nama_lengkap }}</h5>
                <table class="info-table">
                    <tr>
                        <td>No. Urut</td>
                        <td><strong>{{ $pesertaRuang->nomor_urut }}</strong> &middot; {{ $calonSiswa->no_pendaftaran ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td>{{ $calonSiswa->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>L/P</td>
                        <td>{{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah</td>
                        <td>{{ $calonSiswa->nama_sekolah_asal ?? $calonSiswa->asal_sekolah ?? '-' }}</td>
                    </tr>
                    @if($calonSiswa->jalurPendaftaran)
                    <tr>
                        <td>Jalur</td>
                        <td>
                            <span class="badge" style="background:{{ $calonSiswa->jalurPendaftaran->warna ?? '#007bff' }}; font-size:11px;">
                                {{ $calonSiswa->jalurPendaftaran->nama }}
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ===== DOKUMEN THUMBNAILS ===== --}}
    @if($dokumenList->count() > 0)
    <div class="card card-outline card-info mb-3">
        <div class="card-header py-2">
            <h3 class="card-title" style="font-size:14px;">
                <i class="fas fa-file-image mr-1"></i>Dokumen Pendaftar ({{ $dokumenList->count() }})
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="dok-grid">
                @foreach($dokumenList as $dok)
                    @php
                        $ext = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        $label = $dokumenLabels[$dok->jenis_dokumen] ?? ucfirst(str_replace('_',' ',$dok->jenis_dokumen));
                        $fileUrl = asset('storage/' . $dok->file_path);
                    @endphp
                    <a href="{{ $isImg ? 'javascript:void(0)' : $fileUrl }}" 
                       class="dok-thumb {{ $isImg ? 'lightbox-trigger' : '' }}"
                       data-url="{{ $fileUrl }}"
                       data-title="{{ $label }}"
                       data-type="{{ $isImg ? 'image' : 'pdf' }}"
                       {{ !$isImg ? 'target=_blank' : '' }}
                       title="{{ $label }}">
                        @if($isImg)
                            <img src="{{ $fileUrl }}" class="thumb-img" alt="{{ $label }}">
                        @else
                            <div class="thumb-placeholder">
                                <i class="fas fa-file-pdf fa-lg text-danger"></i>
                            </div>
                        @endif
                        <span class="thumb-label">{{ Str::limit($label, 12) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ===== FORM INPUT NILAI ===== --}}
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
                    @if($bobot->kode_komponen == 'wawancara')
                        <div class="form-group mb-0">
                            <label class="mb-1">Nilai Wawancara <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_wawancara" 
                                   class="form-control nilai-input grade-input" 
                                   data-type="baca"
                                   value="{{ old('nilai_wawancara', $nilai->nilai_wawancara ?? '') }}"
                                   min="0" max="100" step="0.01" placeholder="0 - 100"
                                   {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                        </div>

                    @elseif($bobot->kode_komponen == 'baca_quran')
                        {{-- Sub-komponen: Tajwid, Makhroj, Kelancaran → rata-rata --}}
                        <div class="sub-komponen-row">
                            <div class="form-group">
                                <label class="mb-1" style="font-size:12px;">Tajwid</label>
                                <input type="number" name="nilai_tajwid" 
                                       class="form-control nilai-input sub-baca grade-input" 
                                       data-type="baca"
                                       value="{{ old('nilai_tajwid', $nilai->nilai_tajwid ?? '') }}"
                                       min="0" max="100" step="0.01" placeholder="0-100"
                                       {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                            </div>
                            <div class="form-group">
                                <label class="mb-1" style="font-size:12px;">Makhroj</label>
                                <input type="number" name="nilai_makhroj" 
                                       class="form-control nilai-input sub-baca grade-input"
                                       data-type="baca" 
                                       value="{{ old('nilai_makhroj', $nilai->nilai_makhroj ?? '') }}"
                                       min="0" max="100" step="0.01" placeholder="0-100"
                                       {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                            </div>
                            <div class="form-group">
                                <label class="mb-1" style="font-size:12px;">Kelancaran</label>
                                <input type="number" name="nilai_kelancaran" 
                                       class="form-control nilai-input sub-baca grade-input"
                                       data-type="baca" 
                                       value="{{ old('nilai_kelancaran', $nilai->nilai_kelancaran ?? '') }}"
                                       min="0" max="100" step="0.01" placeholder="0-100"
                                       {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                            </div>
                            <div class="rata-rata-box">
                                <div class="value" id="rataBaca">-</div>
                                <div class="label-text">Rata-rata</div>
                            </div>
                        </div>

                    @elseif($bobot->kode_komponen == 'tulis_quran')
                        <div class="form-group mb-0">
                            <label class="mb-1">Nilai Tulis Arab <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_tulis_quran" 
                                   class="form-control nilai-input grade-input"
                                   data-type="baca" 
                                   value="{{ old('nilai_tulis_quran', $nilai->nilai_tulis_quran ?? '') }}"
                                   min="0" max="100" step="0.01" placeholder="0 - 100"
                                   {{ $nilai && $nilai->exists && !$nilai->isEditable() ? 'readonly' : '' }}>
                        </div>

                    @elseif($bobot->kode_komponen == 'hafalan')
                        <div class="form-group">
                            <label class="mb-1">Nilai Hafalan <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_hafalan" 
                                   class="form-control nilai-input grade-input"
                                   data-type="hafalan" 
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

{{-- Lightbox Overlay --}}
<div class="lightbox-overlay" id="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img src="" id="lightboxImg" alt="Preview">
    <div class="lightbox-title" id="lightboxTitle"></div>
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
$(document).ready(function() {
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
            // Remove old badge
            $('#rataBaca').parent().find('.grade-badge').remove();
            var g = getGrade(avg);
            $('#rataBaca').after('<span class="grade-badge ' + g.cls + '">' + g.label + '</span>');
        } else {
            $('#rataBaca').text('-');
            $('#rataBaca').parent().find('.grade-badge').remove();
        }
    }
    $('.sub-baca').on('input', calcRataBaca);
    calcRataBaca(); // Initial

    // === Grade Badge ===
    function getGrade(val) {
        if (val <= 45) return {label: 'SK', cls: 'grade-sk'};
        if (val <= 55) return {label: 'K', cls: 'grade-k'};
        if (val <= 74) return {label: 'C', cls: 'grade-c'};
        return {label: 'B', cls: 'grade-b'};
    }

    // Grade for individual inputs
    $('.grade-input:not(.sub-baca)').on('input', function() {
        var v = parseFloat($(this).val());
        $(this).next('.grade-badge').remove();
        if (!isNaN(v)) {
            var g = getGrade(v);
            $(this).after('<span class="grade-badge ' + g.cls + '">' + g.label + '</span>');
        }
    });
    // Init grade badges on page load
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

    // === Lightbox ===
    $('.lightbox-trigger').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title');
        $('#lightboxImg').attr('src', url);
        $('#lightboxTitle').text(title);
        $('#lightbox').addClass('active');
    });
    $('#lightbox').on('click', function(e) {
        if (e.target === this) closeLightbox();
    });
});

function closeLightbox() {
    $('#lightbox').removeClass('active');
}

// Escape key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
</script>
@stop
