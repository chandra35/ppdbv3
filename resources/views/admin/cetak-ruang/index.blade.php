@extends('adminlte::page')

@section('title', 'Cetak Ruang Ujian')

@section('css')
<style>
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .room-card { border-left: 4px solid #007bff; }
    .room-card:hover { background-color: #f8f9fa; }
    .room-peserta { max-height: 300px; overflow-y: auto; }
    .preview-table th { font-size: 12px; }
    .preview-table td { font-size: 11px; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-door-open"></i> Cetak Ruang Ujian</h1>
        <p class="text-muted mb-0">Atur pembagian ruang dan cetak dokumen ruang ujian</p>
    </div>
    <div class="col-sm-6">
        <form class="form-inline justify-content-sm-end" method="GET">
            <label class="mr-2">Tahun Pelajaran:</label>
            <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($tahunPelajaranList as $tp)
                <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
@stop

@section('content')
{{-- Statistics --}}
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="info-box bg-info stat-card">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Peserta</span>
                <span class="info-box-number">{{ number_format($totalPeserta) }}</span>
                <small>Sudah Finalisasi + Nomor Tes</small>
            </div>
        </div>
    </div>
    @if(isset($rooms))
    <div class="col-lg-4 col-md-6">
        <div class="info-box bg-success stat-card">
            <span class="info-box-icon"><i class="fas fa-door-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jumlah Ruang</span>
                <span class="info-box-number">{{ count($rooms) }}</span>
                <small>Pembagian ruang ujian</small>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="info-box bg-warning stat-card">
            <span class="info-box-icon"><i class="fas fa-user-friends"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Peserta/Ruang</span>
                <span class="info-box-number">{{ $settings['peserta_per_ruang'] ?? 20 }}</span>
                <small>Kapasitas per ruang</small>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Settings Form --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan Pembagian Ruang</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cetak-ruang.preview') }}">
            @csrf
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Jumlah Peserta per Ruang <span class="text-danger">*</span></label>
                        <input type="number" name="peserta_per_ruang" class="form-control" 
                               value="{{ $settings['peserta_per_ruang'] ?? 20 }}" min="1" max="100" required>
                        <small class="text-muted">Maksimal peserta dalam 1 ruang</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Prefix Nama Ruang <span class="text-danger">*</span></label>
                        <input type="text" name="prefix_ruang" class="form-control" 
                               value="{{ $settings['prefix_ruang'] ?? 'Ruang' }}" maxlength="50" required>
                        <small class="text-muted">Contoh: "Ruang", "R.", "Kelas"</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Filter Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="">-- Semua Jalur --</option>
                            @foreach($jalurList as $jalur)
                            <option value="{{ $jalur->id }}" {{ ($settings['jalur_id'] ?? '') == $jalur->id ? 'selected' : '' }}>
                                {{ $jalur->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Filter Gelombang</label>
                        <select name="gelombang_id" class="form-control">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($gelombangList as $gel)
                            <option value="{{ $gel->id }}" {{ ($settings['gelombang_id'] ?? '') == $gel->id ? 'selected' : '' }}>
                                {{ $gel->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Urutan Peserta</label>
                        <select name="urutan" class="form-control">
                            <option value="nomor_tes" {{ ($settings['urutan'] ?? 'nomor_tes') == 'nomor_tes' ? 'selected' : '' }}>
                                Berdasarkan Nomor Tes
                            </option>
                            <option value="nama" {{ ($settings['urutan'] ?? '') == 'nama' ? 'selected' : '' }}>
                                Berdasarkan Nama (A-Z)
                            </option>
                            <option value="tanggal_finalisasi" {{ ($settings['urutan'] ?? '') == 'tanggal_finalisasi' ? 'selected' : '' }}>
                                Berdasarkan Tanggal Finalisasi
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Ujian</label>
                        <input type="date" name="tanggal_ujian" class="form-control" 
                               value="{{ $settings['tanggal_ujian'] ?? '' }}">
                        <small class="text-muted">Kosongkan jika tidak ingin dicetak</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Waktu Ujian</label>
                        <div class="input-group">
                            <input type="time" name="waktu_mulai" class="form-control" 
                                   value="{{ $settings['waktu_mulai'] ?? '' }}" placeholder="Mulai">
                            <div class="input-group-append input-group-prepend">
                                <span class="input-group-text">s/d</span>
                            </div>
                            <input type="time" name="waktu_selesai" class="form-control" 
                                   value="{{ $settings['waktu_selesai'] ?? '' }}" placeholder="Selesai">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Preview Pembagian Ruang
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Preview & Print Section --}}
@if(isset($rooms) && count($rooms) > 0)
<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-print"></i> Preview Pembagian Ruang & Cetak</h3>
        <div class="card-tools">
            <div class="btn-group">
                <a href="{{ route('admin.cetak-ruang.print.daftar-hadir') }}" target="_blank" class="btn btn-info btn-sm">
                    <i class="fas fa-clipboard-list"></i> Cetak Daftar Hadir (Semua)
                </a>
                <a href="{{ route('admin.cetak-ruang.print.daftar-peserta') }}" target="_blank" class="btn btn-warning btn-sm">
                    <i class="fas fa-list-alt"></i> Cetak Daftar Peserta (Semua)
                </a>
                <a href="{{ route('admin.cetak-ruang.print.nama-ruang') }}" target="_blank" class="btn btn-success btn-sm">
                    <i class="fas fa-door-open"></i> Cetak Nama Ruang (Semua)
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($rooms as $room)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card room-card">
                    <div class="card-header bg-light py-2">
                        <h5 class="mb-0">
                            <i class="fas fa-door-open text-primary"></i> 
                            {{ $room['nama'] }}
                            <span class="badge badge-info float-right">{{ $room['jumlah'] }} peserta</span>
                        </h5>
                    </div>
                    <div class="card-body p-2 room-peserta">
                        <table class="table table-sm table-hover preview-table mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th width="30">No</th>
                                    <th>Nomor Tes</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($room['peserta'] as $idx => $peserta)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td><code>{{ $peserta->nomor_tes }}</code></td>
                                    <td>{{ Str::limit($peserta->nama_lengkap, 20) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-1">
                        <div class="btn-group btn-group-sm w-100">
                            <a href="{{ route('admin.cetak-ruang.print.daftar-hadir', ['ruang' => $room['nama']]) }}" 
                               target="_blank" class="btn btn-outline-info" title="Daftar Hadir">
                                <i class="fas fa-clipboard-list"></i>
                            </a>
                            <a href="{{ route('admin.cetak-ruang.print.daftar-peserta', ['ruang' => $room['nama']]) }}" 
                               target="_blank" class="btn btn-outline-warning" title="Daftar Peserta">
                                <i class="fas fa-list-alt"></i>
                            </a>
                            <a href="{{ route('admin.cetak-ruang.print.nama-ruang', ['ruang' => $room['nama']]) }}" 
                               target="_blank" class="btn btn-outline-success" title="Nama Ruang">
                                <i class="fas fa-door-open"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@elseif(isset($rooms))
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> 
    Tidak ada peserta yang memenuhi kriteria (sudah finalisasi dan memiliki nomor tes).
</div>
@endif

{{-- Information Card --}}
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6><i class="fas fa-clipboard-list text-info"></i> Daftar Hadir</h6>
                <p class="text-muted small">
                    Dokumen daftar hadir untuk diisi oleh peserta ujian. 
                    Berisi kolom tanda tangan dan keterangan kehadiran.
                </p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-list-alt text-warning"></i> Daftar Peserta</h6>
                <p class="text-muted small">
                    Daftar peserta per ruang untuk ditempel di dalam ruang ujian. 
                    Membantu peserta menemukan tempat duduknya.
                </p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-door-open text-success"></i> Nama Ruang</h6>
                <p class="text-muted small">
                    Label nama ruang untuk ditempel di pintu ruangan ujian.
                    Menampilkan nama ruang dan rentang nomor tes.
                </p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-submit when filter changes
    $('select[name="jalur_id"], select[name="gelombang_id"]').on('change', function() {
        // Don't auto-submit filters, let user click preview
    });
});
</script>
@stop
