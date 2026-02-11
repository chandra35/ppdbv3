@extends('adminlte::page')

@section('title', 'Detail Jadwal Ujian')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .room-card { transition: all 0.2s; }
    .room-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .room-cbt { border-left: 4px solid #28a745; }
    .room-wawancara { border-left: 4px solid #ffc107; }
    .sesi-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    .peserta-row:hover { background-color: #f8f9fa; }
    .grup-a { background-color: rgba(0,123,255,0.1); }
    .grup-b { background-color: rgba(108,117,125,0.1); }
    .btn-print { min-width: 140px; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-calendar-check"></i> Detail Jadwal Ujian</h1>
        <p class="text-muted mb-0">
            {{ $jadwal->tahunPelajaran->nama }} | 
            {{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}
        </p>
    </div>
    <div class="col-sm-6 text-right">
        <a href="{{ route('admin.penjadwalan-ujian.list') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@stop

@section('content')
{{-- Status Badge --}}
<div class="row mb-3">
    <div class="col-12">
        @if($jadwal->status === 'locked')
        <div class="alert alert-success mb-0">
            <i class="fas fa-lock mr-2"></i>
            <strong>Jadwal Terkunci</strong> - Jadwal ini sudah final dan tidak dapat diubah.
            Dibuat pada {{ $jadwal->created_at->isoFormat('D MMMM Y, HH:mm') }}
        </div>
        @elseif($jadwal->status === 'preview')
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Preview</strong> - Jadwal ini belum disimpan.
        </div>
        @else
        <div class="alert alert-secondary mb-0">
            <i class="fas fa-pencil-alt mr-2"></i>
            <strong>Draft</strong> - Jadwal ini masih dalam tahap draft.
        </div>
        @endif
    </div>
</div>

{{-- Statistics --}}
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Peserta</span>
                <span class="info-box-number">{{ number_format($jadwal->total_peserta) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-desktop"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ruang CBT</span>
                <span class="info-box-number">{{ $jadwal->jumlah_ruang_cbt }} ruang × {{ $jadwal->kapasitas_cbt }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-microphone"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ruang Wawancara</span>
                <span class="info-box-number">{{ $jadwal->jumlah_ruang_wawancara }} ruang × {{ $jadwal->kapasitas_wawancara }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-gradient-olive">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sesi</span>
                <span class="info-box-number">{{ $jadwal->total_sesi }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Print & Export Buttons --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-print mr-2"></i>Cetak & Export</h3>
    </div>
    <div class="card-body">
        {{-- Quick Print (HTML) --}}
        <h6 class="mb-2"><i class="fas fa-bolt text-warning mr-2"></i>Quick Print (HTML)</h6>
        <p class="text-muted small mb-3">Print langsung tanpa download, cocok untuk kebutuhan cepat.</p>
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.print.kartu-peserta', $jadwal) }}" 
                   target="_blank" class="btn btn-outline-primary btn-block btn-print">
                    <i class="fas fa-id-card mr-2"></i>Kartu Peserta
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.print.daftar-hadir', $jadwal) }}" 
                   target="_blank" class="btn btn-outline-success btn-block btn-print">
                    <i class="fas fa-clipboard-list mr-2"></i>Daftar Hadir
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.print.nama-ruang', $jadwal) }}" 
                   target="_blank" class="btn btn-outline-warning btn-block btn-print">
                    <i class="fas fa-door-open mr-2"></i>Nama Ruang
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.print.jadwal-sesi', $jadwal) }}" 
                   target="_blank" class="btn btn-outline-info btn-block btn-print">
                    <i class="fas fa-calendar-alt mr-2"></i>Jadwal Sesi
                </a>
            </div>
        </div>

        <hr>

        {{-- PDF Print (with Kop Surat) --}}
        <h6 class="mb-2"><i class="fas fa-file-pdf text-danger mr-2"></i>Cetak PDF (dengan Kop Surat)</h6>
        <p class="text-muted small mb-3">Download PDF dengan kop surat resmi, cocok untuk dokumen formal.</p>
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.pdf.daftar-hadir', $jadwal) }}" 
                   target="_blank" class="btn btn-success btn-block btn-print">
                    <i class="fas fa-file-pdf mr-2"></i>Daftar Hadir
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.pdf.nama-ruang', $jadwal) }}" 
                   target="_blank" class="btn btn-warning btn-block btn-print">
                    <i class="fas fa-file-pdf mr-2"></i>Nama Ruang
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.pdf.daftar-peserta', $jadwal) }}" 
                   target="_blank" class="btn btn-info btn-block btn-print">
                    <i class="fas fa-file-pdf mr-2"></i>Daftar Peserta
                </a>
            </div>
        </div>

        <hr>

        {{-- Export --}}
        <h6 class="mb-2"><i class="fas fa-file-excel text-success mr-2"></i>Export Data</h6>
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="{{ route('admin.penjadwalan-ujian.export.excel', $jadwal) }}" 
                   target="_blank" class="btn btn-dark btn-block btn-print">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Ketua Panitia --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Ketua Panitia</h3>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-1"><strong>Ketua Panitia saat ini:</strong></p>
                <h5 id="ketuaPanitiaDisplay">
                    @if($jadwal->ketuaPanitia)
                        <i class="fas fa-user-check text-success mr-1"></i>{{ $jadwal->ketuaPanitia->name }}
                    @else
                        <span class="text-muted"><i class="fas fa-user-times mr-1"></i>Belum ditentukan</span>
                    @endif
                </h5>
                <small class="text-muted">Berlaku untuk semua sesi dalam jadwal ini.</small>
            </div>
            <div class="col-md-6">
                <label class="mb-1"><strong>Ubah Ketua Panitia:</strong></label>
                <div class="input-group">
                    <select id="selectKetuaPanitia" class="form-control">
                        <option value="">-- Pilih Ketua Panitia --</option>
                        @foreach($pengujiList as $user)
                            <option value="{{ $user->id }}" {{ $jadwal->ketua_panitia_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->roles->pluck('display_name')->join(', ') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btnSaveKetuaPanitia">
                            <i class="fas fa-save mr-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Schedule Overview --}}
<div class="card">
    <div class="card-header sesi-header">
        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Jadwal Sesi</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="80" class="text-center">Sesi</th>
                        <th width="150" class="text-center">Waktu</th>
                        <th class="text-center bg-success text-white">CBT ({{ $jadwal->durasi_cbt }} menit)</th>
                        <th class="text-center bg-warning">Wawancara ({{ $jadwal->durasi_wawancara }} menit)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sesiGrouped = $jadwal->sesiUjian->groupBy('nomor_sesi');
                    @endphp
                    @foreach($sesiGrouped as $nomorSesi => $sesiList)
                    @php
                        $sesiCbt = $sesiList->where('jenis_ujian', 'cbt')->first();
                        $sesiWawancara = $sesiList->where('jenis_ujian', 'wawancara')->first();
                        
                        // Get peserta for this session
                        $pesertaCbt = $jadwal->jadwalPeserta->where('sesi_cbt_id', $sesiCbt?->id ?? null);
                        $pesertaWawancara = $jadwal->jadwalPeserta->where('sesi_wawancara_id', $sesiWawancara?->id ?? null);
                    @endphp
                    <tr>
                        <td class="text-center align-middle"><strong>{{ $nomorSesi }}</strong></td>
                        <td class="text-center align-middle">
                            {{ optional($sesiCbt ?? $sesiWawancara)->waktu_mulai?->format('H:i') ?? '-' }} - 
                            {{ optional($sesiCbt ?? $sesiWawancara)->waktu_selesai?->format('H:i') ?? '-' }}
                        </td>
                        <td>
                            @if($sesiCbt)
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-success">{{ $pesertaCbt->count() }} peserta</span>
                                <small class="text-muted">{{ $jadwal->jumlah_ruang_cbt }} ruang</small>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($sesiWawancara)
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-warning text-dark">{{ $pesertaWawancara->count() }} peserta</span>
                                <small class="text-muted">{{ $jadwal->jumlah_ruang_wawancara }} ruang</small>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Room Distribution --}}
<div class="row">
    {{-- CBT Rooms --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-desktop mr-2"></i>Ruang CBT</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Ruang</th>
                                <th class="text-center">Kapasitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= $jadwal->jumlah_ruang_cbt; $i++)
                            <tr>
                                <td><i class="fas fa-door-open text-success mr-2"></i>{{ $jadwal->prefix_ruang_cbt }} {{ $i }}</td>
                                <td class="text-center">{{ $jadwal->kapasitas_cbt }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Wawancara Rooms --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-microphone mr-2"></i>Ruang Wawancara</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Ruang</th>
                                <th class="text-center">Kapasitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= $jadwal->jumlah_ruang_wawancara; $i++)
                            <tr>
                                <td><i class="fas fa-door-open text-warning mr-2"></i>{{ $jadwal->prefix_ruang_wawancara }} {{ $i }}</td>
                                <td class="text-center">{{ $jadwal->kapasitas_wawancara }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Peserta List --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Daftar Peserta ({{ $jadwal->jadwalPeserta->count() }})</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchPeserta" class="form-control" placeholder="Cari peserta...">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" id="pesertaTable">
                <thead class="thead-dark" style="position: sticky; top: 0;">
                    <tr>
                        <th width="50">#</th>
                        <th width="100">No Tes</th>
                        <th>Nama</th>
                        <th width="60" class="text-center">Grup</th>
                        <th class="text-center bg-success text-white">CBT</th>
                        <th class="text-center bg-warning">Wawancara</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwal->jadwalPeserta->sortBy('calonSiswa.nomor_tes') as $idx => $jp)
                    <tr class="peserta-row {{ $jp->grup === 'A' ? 'grup-a' : 'grup-b' }}">
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $jp->calonSiswa->nomor_tes ?? '-' }}</strong></td>
                        <td>{{ $jp->calonSiswa->nama_lengkap ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $jp->grup === 'A' ? 'badge-primary' : 'badge-secondary' }}">
                                {{ $jp->grup }}
                            </span>
                        </td>
                        <td class="text-center">
                            <small>
                                Sesi {{ $jp->sesiCbt?->nomor_sesi ?? '-' }}<br>
                                <span class="text-muted">{{ $jadwal->prefix_ruang_cbt }} {{ $jp->ruang_cbt_nomor ?? '-' }}</span>
                            </small>
                        </td>
                        <td class="text-center">
                            <small>
                                Sesi {{ $jp->sesiWawancara?->nomor_sesi ?? '-' }}<br>
                                <span class="text-muted">{{ $jadwal->prefix_ruang_wawancara }} {{ $jp->ruang_wawancara_nomor ?? '-' }}</span>
                            </small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Danger Zone --}}
<div class="card card-danger">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Zona Berbahaya</h3>
    </div>
    <div class="card-body">
        <div class="row">
            @if($jadwal->status === 'locked')
            {{-- Unlock Button --}}
            <div class="col-md-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body py-3">
                        <h6 class="mb-2"><i class="fas fa-unlock text-warning mr-2"></i>Buka Kunci Jadwal</h6>
                        <p class="text-muted small mb-2">Jadwal akan dibuka kuncinya agar bisa dihapus atau diubah.</p>
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#unlockModal">
                            <i class="fas fa-unlock mr-1"></i>Buka Kunci
                        </button>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Delete Button --}}
            <div class="col-md-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body py-3">
                        <h6 class="mb-2"><i class="fas fa-trash text-danger mr-2"></i>Hapus Jadwal</h6>
                        <p class="text-muted small mb-2">Hapus semua data jadwal termasuk sesi dan peserta.</p>
                        @if($jadwal->status === 'locked')
                        <button type="button" class="btn btn-secondary btn-sm" disabled title="Buka kunci terlebih dahulu">
                            <i class="fas fa-lock mr-1"></i>Terkunci
                        </button>
                        @else
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash mr-1"></i>Hapus Jadwal
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Unlock Modal --}}
<div class="modal fade" id="unlockModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-unlock mr-2"></i>Buka Kunci Jadwal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Perhatian!</strong> Setelah dibuka kuncinya, jadwal dapat dihapus.
                </div>
                <p>Apakah Anda yakin ingin membuka kunci jadwal ini?</p>
                <ul class="mb-0">
                    <li><strong>Tanggal:</strong> {{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</li>
                    <li><strong>Peserta:</strong> {{ number_format($jadwal->total_peserta) }} orang</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <form method="POST" action="{{ route('admin.penjadwalan-ujian.unlock', $jadwal) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-unlock mr-1"></i>Ya, Buka Kunci
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Jadwal</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan!
                </div>
                <p>Apakah Anda yakin ingin menghapus jadwal ini? Semua data berikut akan dihapus:</p>
                <ul class="text-danger">
                    <li>{{ number_format($jadwal->total_peserta) }} data jadwal peserta</li>
                    <li>{{ $jadwal->total_sesi }} sesi ujian</li>
                    <li>Semua data ruang dan penempatan</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <form method="POST" action="{{ route('admin.penjadwalan-ujian.destroy', $jadwal) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Ya, Hapus Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Search peserta
    $('#searchPeserta').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#pesertaTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Save Ketua Panitia
    $('#btnSaveKetuaPanitia').on('click', function() {
        var btn = $(this);
        var ketuaId = $('#selectKetuaPanitia').val();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Simpan');

        $.ajax({
            url: '{{ route("admin.penjadwalan-ujian.update-ketua-panitia", $jadwal->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ketua_panitia_id: ketuaId
            },
            success: function(response) {
                if (response.success) {
                    if (response.ketua_panitia_name) {
                        $('#ketuaPanitiaDisplay').html('<i class="fas fa-user-check text-success mr-1"></i>' + response.ketua_panitia_name);
                    } else {
                        $('#ketuaPanitiaDisplay').html('<span class="text-muted"><i class="fas fa-user-times mr-1"></i>Belum ditentukan</span>');
                    }
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Gagal menyimpan Ketua Panitia.');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan');
            }
        });
    });
});
</script>
@stop
