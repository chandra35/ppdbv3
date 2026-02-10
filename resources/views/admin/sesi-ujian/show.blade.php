@extends('adminlte::page')

@section('title', 'Detail Sesi Ujian')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-4-theme@1.0.0/dist/select2-bootstrap-4.min.css" rel="stylesheet" />
<style>
    .ruang-card {
        transition: all 0.3s ease;
    }
    .ruang-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .penguji-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.65rem;
        margin: 0.2rem;
        border-radius: 20px;
        font-size: 0.85rem;
        background: #e9ecef;
    }
    .penguji-tag.ketua {
        background: #ffc107;
        color: #000;
    }
    .penguji-tag .remove-penguji {
        margin-left: 0.5rem;
        cursor: pointer;
        color: #dc3545;
    }
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 38px;
    }
    .progress-sm {
        height: 8px;
    }
    .peserta-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .bg-info-light {
        background-color: #d1ecf1 !important;
        animation: pulse-bg 2s ease-in-out infinite;
    }
    .bg-success-light {
        background-color: #d4edda !important;
    }
    @keyframes pulse-bg {
        0%, 100% { background-color: #d1ecf1; }
        50% { background-color: #bee5eb; }
    }
    .badge-sm {
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-clipboard-list mr-2"></i>{{ $sesiUjian->nama }}
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sesi-ujian.index') }}">Sesi Ujian</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </div>
</div>
@stop

@section('content')
@php
    $isCbt = $sesiUjian->jenis_ujian === 'cbt';
    $labelPetugas = $isCbt ? 'Pengawas & Proktor' : 'Penguji';
    $labelKetuaPetugas = $isCbt ? 'Koordinator' : 'Ketua Penguji';
@endphp
<div class="container-fluid">
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

    <!-- Info Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Sesi</h3>
                    <div class="card-tools">
                        @if($sesiUjian->status == 'draft')
                            <span class="badge badge-secondary">Draft</span>
                        @elseif($sesiUjian->status == 'locked')
                            <span class="badge badge-warning">Terkunci</span>
                        @elseif($sesiUjian->status == 'in_progress')
                            <span class="badge badge-primary">Berlangsung</span>
                        @else
                            <span class="badge badge-success">Selesai</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tahun Pelajaran</strong></td>
                                    <td>: {{ $sesiUjian->tahunPelajaran->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jalur Pendaftaran</strong></td>
                                    <td>: {{ $sesiUjian->jalur->nama ?? '-' }}</td>
                                </tr>
                                @if($sesiUjian->gelombang)
                                <tr>
                                    <td><strong>Gelombang</strong></td>
                                    <td>: {{ $sesiUjian->gelombang->nama }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal Ujian</strong></td>
                                    <td>: {{ $sesiUjian->tanggal?->format('d F Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>: {{ $sesiUjian->waktu_mulai ?? '-' }} - {{ $sesiUjian->waktu_selesai ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kapasitas/Ruang</strong></td>
                                    <td>: {{ $sesiUjian->peserta_per_ruang }} peserta</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Aksi</h3>
                </div>
                <div class="card-body">
                    @if($sesiUjian->status == 'locked')
                        <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#mulaiSesiModal">
                            <i class="fas fa-play mr-2"></i>Mulai Sesi Ujian
                        </button>
                    @elseif($sesiUjian->status == 'in_progress')
                        <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#selesaikanSesiModal">
                            <i class="fas fa-check mr-2"></i>Selesaikan Sesi
                        </button>
                    @endif

                    <a href="{{ route('admin.sesi-ujian.print-daftar-hadir', $sesiUjian->id) }}" class="btn btn-info btn-block" target="_blank">
                        <i class="fas fa-print mr-2"></i>Cetak Daftar Hadir
                    </a>

                    <a href="{{ route('admin.nilai-seleksi.show', $sesiUjian->id) }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-chart-bar mr-2"></i>Lihat Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ruangan Cards -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-door-open mr-2"></i>Daftar Ruangan ({{ $sesiUjian->ruangan->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($sesiUjian->ruangan as $ruang)
                    <div class="col-md-6 col-lg-4">
                        <div class="card ruang-card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-door-open mr-2"></i>{{ $ruang->nama_ruang }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Progress -->
                                @php
                                    $totalPeserta = $ruang->peserta->count();
                                    $progress = $ruang->getProgress();
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <small>Progress Penilaian</small>
                                        <small>{{ $progress['dinilai'] }}/{{ $totalPeserta }}</small>
                                    </div>
                                    <div class="progress progress-sm mt-1">
                                        <div class="progress-bar bg-success" style="width: {{ $progress['percentage'] }}%"></div>
                                    </div>
                                </div>

                                <!-- Petugas (Penguji / Pengawas & Proktor) -->
                                <div class="mb-3">
                                    <label class="mb-1"><strong><i class="fas fa-user-tie mr-1"></i>{{ $labelPetugas }}:</strong></label>
                                    <div id="penguji-list-{{ $ruang->id }}">
                                        @forelse($ruang->penguji as $pr)
                                            <span class="penguji-tag {{ $pr->is_ketua ? 'ketua' : '' }}">
                                                {{ $pr->user->name ?? 'Unknown' }}
                                                @if($pr->is_ketua)
                                                    <i class="fas fa-star ml-1 text-warning"></i>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-muted">Belum ada {{ strtolower($labelPetugas) }}</span>
                                        @endforelse
                                    </div>
                                    
                                    @if(in_array($sesiUjian->status, ['locked', 'in_progress']))
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-assign-penguji"
                                                data-ruang-id="{{ $ruang->id }}"
                                                data-ruang-nama="{{ $ruang->nama_ruang }}"
                                                data-jumlah-peserta="{{ $totalPeserta }}">
                                            <i class="fas fa-user-plus mr-1"></i>Kelola {{ $labelPetugas }}
                                        </button>
                                    @endif
                                </div>

                                <!-- Peserta List -->
                                <div>
                                    <label class="mb-1">
                                        <strong><i class="fas fa-users mr-1"></i>Peserta ({{ $totalPeserta }}):</strong>
                                        <a href="{{ route('admin.sesi-ujian.peserta-ruang', [$sesiUjian->id, $ruang->id]) }}" 
                                           class="btn btn-xs btn-outline-info float-right" title="Kelola Peserta">
                                            <i class="fas fa-users-cog"></i> Kelola
                                        </a>
                                    </label>
                                    <div class="peserta-list border rounded p-2" id="peserta-list-{{ $ruang->id }}">
                                        <table class="table table-sm table-borderless mb-0">
                                            @forelse($ruang->peserta as $pr)
                                                <tr id="peserta-row-{{ $pr->id }}" class="peserta-row 
                                                    @if($pr->status == 'in_progress') bg-info-light @elseif($pr->status == 'completed') bg-success-light @endif">
                                                    <td width="30">{{ $pr->nomor_urut }}.</td>
                                                    <td width="90"><small class="text-muted">{{ $pr->calonSiswa->nomor_tes ?? '-' }}</small></td>
                                                    <td>
                                                        {{ $pr->calonSiswa->nama_lengkap ?? '-' }}
                                                        @if($pr->status == 'in_progress')
                                                            <span class="badge badge-primary badge-sm ml-1 peserta-status-badge"><i class="fas fa-volume-up mr-1"></i>Sedang Diuji</span>
                                                        @elseif($pr->status == 'completed')
                                                            <span class="badge badge-success badge-sm ml-1 peserta-status-badge"><i class="fas fa-check mr-1"></i>Selesai</span>
                                                        @endif
                                                    </td>
                                                    <td width="30">
                                                        @php
                                                            $nilai = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $sesiUjian->id)
                                                                ->where('calon_siswa_id', $pr->calon_siswa_id)
                                                                ->first();
                                                        @endphp
                                                        @if($nilai && in_array($nilai->status, ['submitted', 'verified']))
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        @elseif($nilai && $nilai->status == 'draft')
                                                            <i class="fas fa-clock text-warning"></i>
                                                        @else
                                                            <i class="fas fa-minus text-muted"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Tidak ada peserta</td>
                                                </tr>
                                            @endforelse
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada ruangan</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Info Penguji Card -->
    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-2"></i>Panduan Penugasan {{ $labelPetugas }}
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-user-tie text-primary mr-2"></i>Cara Menugaskan {{ $labelPetugas }}</h6>
                    <ol class="pl-3">
                        <li>Klik tombol <span class="badge badge-outline-primary">Kelola {{ $labelPetugas }}</span> pada setiap ruangan</li>
                        <li>Pilih satu atau lebih {{ strtolower($labelPetugas) }} dari daftar</li>
                        <li>Tentukan {{ strtolower($labelKetuaPetugas) }} (opsional)</li>
                        <li>Klik Simpan</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-clipboard-check text-success mr-2"></i>Catatan Penting</h6>
                    <ul class="pl-3">
                        <li>{{ $labelPetugas }} harus sudah terdaftar di <a href="{{ route('admin.penguji.index') }}">Manajemen {{ $labelPetugas }}</a></li>
                        <li>{{ $labelKetuaPetugas }} bertanggung jawab atas jalannya ujian di ruangan</li>
                        <li>Setelah penugasan, {{ strtolower($labelPetugas) }} dapat login ke portal <code>/penguji</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign Penguji (Improved) -->
<div class="modal fade" id="assignPengujiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus mr-2"></i>Kelola {{ $labelPetugas }} - <span id="modalRuangNama"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="assignPengujiForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ruang_ujian_id" id="modalRuangId">
                    
                    <!-- Info Ruangan -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-door-open mr-1"></i>Ruangan:</strong>
                                <span id="infoRuangNama">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-users mr-1"></i>Jumlah Peserta:</strong>
                                <span id="infoJumlahPeserta">0</span>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-user-tie mr-1"></i>{{ $labelPetugas }} Saat Ini:</strong>
                                <span id="infoJumlahPenguji">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Penguji -->
                    <div class="form-group">
                        <label class="d-flex justify-content-between align-items-center">
                            <span><strong><i class="fas fa-users mr-1"></i>Pilih {{ $labelPetugas }}:</strong></span>
                            <a href="{{ route('admin.penguji.index') }}" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-plus mr-1"></i>Kelola {{ $labelPetugas }}
                            </a>
                        </label>
                        <select name="penguji_ids[]" id="selectPenguji" class="form-control" multiple="multiple" style="width: 100%">
                            @foreach($pengujiList as $penguji)
                                @php
                                    $roleNames = $penguji->roles->pluck('display_name')->join(', ');
                                    $isDedicatedPenguji = $penguji->roles->contains('name', 'penguji');
                                @endphp
                                <option value="{{ $penguji->id }}" 
                                        data-email="{{ $penguji->email }}"
                                        data-phone="{{ $penguji->phone ?? '-' }}"
                                        data-roles="{{ $roleNames }}"
                                        data-dedicated="{{ $isDedicatedPenguji ? '1' : '0' }}">
                                    {{ $penguji->name }} ({{ $isDedicatedPenguji ? $labelPetugas : $roleNames }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>Pilih satu atau lebih {{ strtolower($labelPetugas) }}. 
                            {{ $labelPetugas }} dengan label <span class="badge badge-primary badge-sm">{{ $labelPetugas }}</span> adalah {{ strtolower($labelPetugas) }} khusus.
                        </small>
                    </div>

                    <!-- Daftar Penguji Terpilih -->
                    <div id="selectedPengujiList" class="mb-3" style="display: none;">
                        <label><strong><i class="fas fa-check-circle mr-1"></i>{{ $labelPetugas }} Terpilih:</strong></label>
                        <div id="selectedPengujiCards" class="row"></div>
                    </div>

                    <!-- Pilih Ketua -->
                    <div class="form-group">
                        <label>
                            <strong><i class="fas fa-star text-warning mr-1"></i>{{ $labelKetuaPetugas }}:</strong>
                        </label>
                        <select name="ketua_id" id="selectKetua" class="form-control">
                            <option value="">-- Pilih {{ $labelKetuaPetugas }} (Opsional) --</option>
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>{{ $labelKetuaPetugas }} bertanggung jawab koordinasi dan memastikan kelancaran ujian di ruangan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.penguji.index') }}" target="_blank" class="btn btn-outline-info mr-auto">
                        <i class="fas fa-external-link-alt mr-1"></i>Kelola Semua {{ $labelPetugas }}
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Mulai Sesi Ujian --}}
@if($sesiUjian->status == 'locked')
<div class="modal fade" id="mulaiSesiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-play mr-2"></i>Mulai Sesi Ujian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Sesi ujian akan dimulai dan penguji dapat mulai menginput nilai.
                </div>
                <p>Apakah Anda yakin ingin memulai sesi ujian ini?</p>
                <ul class="mb-0">
                    <li><strong>Sesi:</strong> {{ $sesiUjian->nama }}</li>
                    <li><strong>Tanggal:</strong> {{ $sesiUjian->tanggal?->isoFormat('dddd, D MMMM Y') ?? '-' }}</li>
                    <li><strong>Waktu:</strong> {{ $sesiUjian->waktu_mulai?->format('H:i') ?? '-' }} - {{ $sesiUjian->waktu_selesai?->format('H:i') ?? '-' }}</li>
                    <li><strong>Ruangan:</strong> {{ $sesiUjian->ruangan->count() }} ruang</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <form action="{{ route('admin.sesi-ujian.update-status', $sesiUjian->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play mr-1"></i>Ya, Mulai Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Selesaikan Sesi --}}
@if($sesiUjian->status == 'in_progress')
<div class="modal fade" id="selesaikanSesiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check mr-2"></i>Selesaikan Sesi Ujian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Perhatian!</strong> Setelah sesi diselesaikan, semua nilai akan dikunci dan tidak dapat diubah lagi.
                </div>
                <p>Apakah Anda yakin ingin menyelesaikan sesi ujian ini?</p>
                <ul class="mb-0">
                    <li><strong>Sesi:</strong> {{ $sesiUjian->nama }}</li>
                    <li><strong>Tanggal:</strong> {{ $sesiUjian->tanggal?->isoFormat('dddd, D MMMM Y') ?? '-' }}</li>
                    <li><strong>Ruangan:</strong> {{ $sesiUjian->ruangan->count() }} ruang</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <form action="{{ route('admin.sesi-ujian.update-status', $sesiUjian->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Custom template for Select2 options
    function formatPenguji(penguji) {
        if (!penguji.id) return penguji.text;
        
        var $option = $(penguji.element);
        var isDedicated = $option.data('dedicated') == '1';
        var roles = $option.data('roles') || '';
        var email = $option.data('email') || '';
        
        var badge = isDedicated 
            ? '<span class="badge badge-primary ml-2">Penguji</span>'
            : '<span class="badge badge-secondary ml-2">' + roles + '</span>';
        
        return $('<span>' + penguji.text.split('(')[0].trim() + badge + '<br><small class="text-muted">' + email + '</small></span>');
    }

    // Initialize Select2 for penguji with custom template
    $('#selectPenguji').select2({
        theme: 'bootstrap4',
        placeholder: 'Ketik untuk mencari penguji...',
        allowClear: true,
        dropdownParent: $('#assignPengujiModal'),
        templateResult: formatPenguji,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            
            var searchTerm = params.term.toLowerCase();
            var text = (data.text || '').toLowerCase();
            var email = ($(data.element).data('email') || '').toLowerCase();
            
            if (text.indexOf(searchTerm) > -1 || email.indexOf(searchTerm) > -1) {
                return data;
            }
            
            return null;
        }
    });

    $('#selectKetua').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih ketua penguji...',
        allowClear: true,
        dropdownParent: $('#assignPengujiModal')
    });

    // Update selected penguji cards and ketua options
    $('#selectPenguji').on('change', function() {
        var selected = $(this).select2('data');
        var ketuaSelect = $('#selectKetua');
        var currentKetua = ketuaSelect.val();
        var cardsContainer = $('#selectedPengujiCards');
        
        // Update ketua dropdown
        ketuaSelect.empty();
        ketuaSelect.append('<option value="">-- Pilih Ketua Penguji (Opsional) --</option>');
        
        // Update cards
        cardsContainer.empty();
        
        if (selected.length > 0) {
            $('#selectedPengujiList').show();
            
            selected.forEach(function(item) {
                var $option = $(item.element);
                var isDedicated = $option.data('dedicated') == '1';
                var email = $option.data('email') || '';
                var phone = $option.data('phone') || '-';
                
                ketuaSelect.append('<option value="' + item.id + '">' + item.text.split('(')[0].trim() + '</option>');
                
                var card = `
                    <div class="col-md-6 mb-2">
                        <div class="card card-outline ${isDedicated ? 'card-primary' : 'card-secondary'} mb-0">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-${isDedicated ? 'primary' : 'secondary'} text-white d-flex align-items-center justify-content-center mr-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                        ${item.text.split('(')[0].trim().substring(0, 2).toUpperCase()}
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>${item.text.split('(')[0].trim()}</strong>
                                        ${isDedicated ? '<i class="fas fa-check-circle text-primary ml-1" title="Penguji Terdaftar"></i>' : ''}
                                        <br>
                                        <small class="text-muted">${email}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                cardsContainer.append(card);
            });
            
            $('#infoJumlahPenguji').text(selected.length);
        } else {
            $('#selectedPengujiList').hide();
            $('#infoJumlahPenguji').text('0');
        }

        // Restore ketua selection if still valid
        if (currentKetua && selected.find(s => s.id == currentKetua)) {
            ketuaSelect.val(currentKetua).trigger('change.select2');
        }
    });

    // Open modal and load current penguji
    $('.btn-assign-penguji').on('click', function() {
        var ruangId = $(this).data('ruang-id');
        var ruangNama = $(this).data('ruang-nama');
        var jumlahPeserta = $(this).data('jumlah-peserta') || 0;
        
        $('#modalRuangId').val(ruangId);
        $('#modalRuangNama').text(ruangNama);
        $('#infoRuangNama').text(ruangNama);
        $('#infoJumlahPeserta').text(jumlahPeserta);
        $('#assignPengujiForm').attr('action', '{{ route("admin.sesi-ujian.assign-penguji", $sesiUjian->id) }}');
        
        // Reset form
        $('#selectPenguji').val([]).trigger('change');
        $('#selectKetua').val('').trigger('change.select2');
        $('#selectedPengujiList').hide();
        
        // Load current penguji for this ruangan
        $.get('{{ route("admin.sesi-ujian.index") }}/{{ $sesiUjian->id }}/ruangan/' + ruangId + '/penguji', function(data) {
            if (data.penguji && data.penguji.length > 0) {
                var pengujiIds = data.penguji.map(function(p) { return p.user_id; });
                var ketuaItem = data.penguji.find(function(p) { return p.is_ketua; });
                var ketuaId = ketuaItem ? ketuaItem.user_id : '';
                
                $('#selectPenguji').val(pengujiIds).trigger('change');
                
                setTimeout(function() {
                    $('#selectKetua').val(ketuaId).trigger('change.select2');
                }, 150);
            }
        }).fail(function() {
            console.log('Failed to load penguji data');
        });
        
        $('#assignPengujiModal').modal('show');
    });

    // Form submit with AJAX
    $('#assignPengujiForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Update penguji display on the card
                    var ruangId = $('#modalRuangId').val();
                    var pengujiNames = response.penguji_names || '-';
                    
                    $('#penguji-list-' + ruangId).html(
                        pengujiNames === '-' 
                            ? '<span class="text-muted">Belum ada penguji</span>'
                            : pengujiNames.split(', ').map(function(name) {
                                return '<span class="penguji-tag">' + name + '</span>';
                            }).join('')
                    );
                    
                    $('#assignPengujiModal').modal('hide');
                    
                    // Show success toast
                    toastr.success(response.message || 'Penguji berhasil ditugaskan');
                } else {
                    toastr.error(response.message || 'Gagal menyimpan penugasan');
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';
                toastr.error(message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ====================================
    // AJAX Polling: Real-time peserta status
    // ====================================
    @if(in_array($sesiUjian->status, ['in_progress']))
    function updatePesertaStatus(data) {
        $.each(data, function(ruangId, ruangData) {
            $.each(ruangData.peserta, function(i, peserta) {
                var row = $('#peserta-row-' + peserta.id);
                if (row.length === 0) return;

                row.removeClass('bg-info-light bg-success-light');
                row.find('.peserta-status-badge').remove();

                var nameTd = row.find('td').eq(2);
                if (peserta.status === 'in_progress') {
                    row.addClass('bg-info-light');
                    nameTd.append(' <span class="badge badge-primary badge-sm ml-1 peserta-status-badge"><i class="fas fa-volume-up mr-1"></i>Sedang Diuji</span>');
                } else if (peserta.status === 'completed') {
                    row.addClass('bg-success-light');
                    nameTd.append(' <span class="badge badge-success badge-sm ml-1 peserta-status-badge"><i class="fas fa-check mr-1"></i>Selesai</span>');
                }
            });
        });
    }

    var statusPollInterval = setInterval(function() {
        $.get('{{ route("admin.sesi-ujian.status-peserta", $sesiUjian->id) }}', function(data) {
            updatePesertaStatus(data);
        });
    }, 10000); // Poll every 10 seconds

    // Stop polling when page is hidden, restart when visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(statusPollInterval);
            statusPollInterval = null;
        } else if (!statusPollInterval) {
            statusPollInterval = setInterval(function() {
                $.get('{{ route("admin.sesi-ujian.status-peserta", $sesiUjian->id) }}', function(data) {
                    updatePesertaStatus(data);
                });
            }, 10000);
            // Immediately fetch on tab focus
            $.get('{{ route("admin.sesi-ujian.status-peserta", $sesiUjian->id) }}', function(data) {
                updatePesertaStatus(data);
            });
        }
    });

    @endif
});
</script>
@stop
