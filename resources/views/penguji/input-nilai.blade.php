@extends('layouts.admin')

@section('title', 'Input Nilai - ' . ($calonSiswa->nama_lengkap ?? 'Peserta'))

@push('css')
<style>
    .nilai-input {
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
        height: 50px;
    }
    .nilai-input:focus {
        background-color: #fff3cd;
    }
    .komponen-card {
        border-left: 4px solid #007bff;
    }
    .komponen-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .nav-peserta {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    .peserta-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .juz-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .juz-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s;
    }
    .juz-btn:hover {
        border-color: #007bff;
    }
    .juz-btn.selected {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .main-content {
        padding-bottom: 100px;
    }
</style>
@endpush

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-edit mr-2"></i>Input Nilai Seleksi
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('penguji.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('penguji.ruangan', $ruangUjian->id) }}">{{ $ruangUjian->nama_ruang }}</a></li>
            <li class="breadcrumb-item active">Input Nilai</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Peserta Info -->
        <div class="col-md-4">
            <div class="card peserta-info-card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="text-center mb-1">{{ $calonSiswa->nama_lengkap ?? '-' }}</h4>
                    <p class="text-center mb-3 opacity-75">
                        <small>No. {{ $pesertaRuang->nomor_urut }} | {{ $calonSiswa->no_pendaftaran ?? '-' }}</small>
                    </p>
                    <hr class="bg-white opacity-25">
                    <table class="table table-sm table-borderless text-white mb-0">
                        <tr>
                            <td>NISN</td>
                            <td>: {{ $calonSiswa->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>: {{ $calonSiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td>Asal Sekolah</td>
                            <td>: {{ $calonSiswa->asal_sekolah ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($nilai && $nilai->exists && !$nilai->isEditable())
                <div class="alert alert-warning">
                    <i class="fas fa-lock mr-2"></i>
                    Nilai sudah disubmit dan tidak bisa diubah.
                </div>
            @endif
        </div>

        <!-- Input Nilai Form -->
        <div class="col-md-8">
            <form action="{{ route('penguji.save-nilai', [$ruangUjian->id, $pesertaRuang->id]) }}" method="POST" id="nilaiForm">
                @csrf
                
                <!-- Komponen Penilaian -->
                @foreach($bobotList as $bobot)
                    <div class="card komponen-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-star mr-2 text-warning"></i>
                                {{ $bobot->nama_komponen }}
                                <span class="badge badge-info ml-2">Bobot: {{ $bobot->bobot }}%</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($bobot->kode_komponen == 'wawancara')
                                <div class="form-group">
                                    <label>Nilai Wawancara <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="nilai_wawancara" 
                                           class="form-control nilai-input" 
                                           value="{{ old('nilai_wawancara', $nilai->nilai_wawancara ?? '') }}"
                                           min="0" max="100" step="0.01"
                                           placeholder="0 - 100"
                                           {{ $nilai && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>
                            @elseif($bobot->kode_komponen == 'baca_quran')
                                <div class="form-group">
                                    <label>Nilai Baca Al-Qur'an <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="nilai_baca_quran" 
                                           class="form-control nilai-input" 
                                           value="{{ old('nilai_baca_quran', $nilai->nilai_baca_quran ?? '') }}"
                                           min="0" max="100" step="0.01"
                                           placeholder="0 - 100"
                                           {{ $nilai && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>
                            @elseif($bobot->kode_komponen == 'tulis_quran')
                                <div class="form-group">
                                    <label>Nilai Tulis Al-Qur'an <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="nilai_tulis_quran" 
                                           class="form-control nilai-input" 
                                           value="{{ old('nilai_tulis_quran', $nilai->nilai_tulis_quran ?? '') }}"
                                           min="0" max="100" step="0.01"
                                           placeholder="0 - 100"
                                           {{ $nilai && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>
                            @elseif($bobot->kode_komponen == 'hafalan')
                                <div class="form-group">
                                    <label>Nilai Hafalan <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="nilai_hafalan" 
                                           class="form-control nilai-input" 
                                           value="{{ old('nilai_hafalan', $nilai->nilai_hafalan ?? '') }}"
                                           min="0" max="100" step="0.01"
                                           placeholder="0 - 100"
                                           {{ $nilai && !$nilai->isEditable() ? 'readonly' : '' }}>
                                </div>
                                <div class="form-group">
                                    <label>Jumlah Juz Hafalan</label>
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

                <!-- Catatan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sticky-note mr-2"></i>Catatan Penguji
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="catatan_penguji" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Catatan tambahan tentang peserta (opsional)"
                                  {{ $nilai && !$nilai->isEditable() ? 'readonly' : '' }}>{{ old('catatan_penguji', $nilai->catatan_penguji ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(!$nilai || $nilai->isEditable())
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" name="action" value="save" class="btn btn-warning btn-lg btn-block">
                                        <i class="fas fa-save mr-2"></i>Simpan sebagai Draft
                                    </button>
                                    <small class="text-muted">Nilai disimpan sementara, bisa diubah nanti</small>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="action" value="submit" class="btn btn-success btn-lg btn-block" onclick="return confirm('Submit nilai? Nilai yang sudah disubmit tidak bisa diubah lagi.')">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit Nilai
                                    </button>
                                    <small class="text-muted">Nilai final, tidak bisa diubah</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Navigation Bar -->
<div class="nav-peserta">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-4 text-left">
                @if($prevPeserta)
                    <a href="{{ route('penguji.input-nilai', [$ruangUjian->id, $prevPeserta]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left mr-1"></i>Sebelumnya
                    </a>
                @endif
            </div>
            <div class="col-4 text-center">
                <a href="{{ route('penguji.ruangan', $ruangUjian->id) }}" class="btn btn-secondary">
                    <i class="fas fa-list mr-1"></i>Daftar Peserta
                </a>
            </div>
            <div class="col-4 text-right">
                @if($nextPeserta)
                    @if(!$nilai || $nilai->isEditable())
                        <button type="button" class="btn btn-primary" id="saveAndNext">
                            Simpan & Lanjut<i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    @else
                        <a href="{{ route('penguji.input-nilai', [$ruangUjian->id, $nextPeserta]) }}" class="btn btn-primary">
                            Selanjutnya<i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Juz selector
    $('.juz-btn').on('click', function() {
        @if(!$nilai || $nilai->isEditable())
            $('.juz-btn').removeClass('selected');
            $(this).addClass('selected');
            $('#jumlahJuz').val($(this).data('juz'));
        @endif
    });

    // Save and Next button
    $('#saveAndNext').on('click', function() {
        var form = $('#nilaiForm');
        $('<input>').attr({
            type: 'hidden',
            name: 'action',
            value: 'save'
        }).appendTo(form);
        
        $('<input>').attr({
            type: 'hidden',
            name: 'next',
            value: '{{ $nextPeserta }}'
        }).appendTo(form);
        
        form.submit();
    });

    // Auto-focus on first empty input
    $('.nilai-input').each(function() {
        if (!$(this).val()) {
            $(this).focus();
            return false;
        }
    });
});
</script>
@endpush
