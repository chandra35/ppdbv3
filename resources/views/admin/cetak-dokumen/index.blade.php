@extends('adminlte::page')

@section('title', 'Cetak Dokumen')

@section('css')
<style>
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .btn-group-print .btn { margin: 0 2px; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-print"></i> Cetak Dokumen</h1>
        <p class="text-muted mb-0">Cetak bukti registrasi dan kartu tes pendaftar yang sudah difinalisasi</p>
    </div>
    <div class="col-sm-6">
        <form class="form-inline justify-content-sm-end">
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
    <div class="col-lg-6 col-md-6">
        <div class="info-box bg-success stat-card">
            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sudah Final</span>
                <span class="info-box-number">{{ number_format($stats['total_final']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="info-box bg-info stat-card">
            <span class="info-box-icon"><i class="fas fa-id-card"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Memiliki Nomor Tes</span>
                <span class="info-box-number">{{ number_format($stats['dengan_nomor_tes']) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Data Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar Pendaftar (Sudah Finalisasi)</h3>
        <div class="card-tools">
            <div class="btn-group btn-group-print">
                <button type="button" class="btn btn-primary btn-sm" id="btnBatchRegistrasi" disabled>
                    <i class="fas fa-file-alt"></i> Cetak Bukti Registrasi (<span id="selectedCountReg">0</span>)
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="btnBatchKartuTes" disabled>
                    <i class="fas fa-id-card"></i> Cetak Kartu Tes (<span id="selectedCountTes">0</span>)
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form method="GET" class="mb-3">
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">
            <div class="row">
                <div class="col-md-3">
                    <select name="jalur_id" class="form-control form-control-sm">
                        <option value="">-- Semua Jalur --</option>
                        @foreach($jalurList as $jalur)
                        <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                            {{ $jalur->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="gelombang_id" class="form-control form-control-sm">
                        <option value="">-- Semua Gelombang --</option>
                        @foreach($gelombangList as $gel)
                        <option value="{{ $gel->id }}" {{ request('gelombang_id') == $gel->id ? 'selected' : '' }}>
                            {{ $gel->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Cari nama/NISN/No.Reg/No.Tes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.cetak-dokumen.index', ['tahun_pelajaran_id' => $tahunAktif?->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Data Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" id="checkAll" title="Pilih Semua">
                        </th>
                        <th width="40">#</th>
                        <th>Nama Lengkap</th>
                        <th>No. Registrasi</th>
                        <th>No. Tes</th>
                        <th>Jalur</th>
                        <th>Gelombang</th>
                        <th>Tgl Finalisasi</th>
                        <th class="text-center" width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftarList as $i => $p)
                    <tr data-id="{{ $p->id }}" data-has-tes="{{ $p->nomor_tes ? '1' : '0' }}">
                        <td class="text-center">
                            <input type="checkbox" class="check-item" value="{{ $p->id }}" data-has-tes="{{ $p->nomor_tes ? '1' : '0' }}">
                        </td>
                        <td>{{ ($pendaftarList->currentPage() - 1) * $pendaftarList->perPage() + $i + 1 }}</td>
                        <td>
                            <strong>{{ $p->nama_lengkap }}</strong>
                            <br><small class="text-muted">NISN: {{ $p->nisn ?? '-' }}</small>
                        </td>
                        <td>
                            @if($p->nomor_registrasi)
                            <code>{{ $p->nomor_registrasi }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($p->nomor_tes)
                            <code class="text-success">{{ $p->nomor_tes }}</code>
                            @else
                            <span class="text-muted"><i class="fas fa-clock"></i> Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($p->jalurPendaftaran)
                            <span class="badge" style="background: {{ $p->jalurPendaftaran->warna ?? '#6c757d' }}; color: white;">
                                {{ $p->jalurPendaftaran->nama }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $p->gelombangPendaftaran?->nama ?? '-' }}</td>
                        <td>
                            <small>{{ $p->tanggal_finalisasi ? $p->tanggal_finalisasi->format('d/m/Y H:i') : '-' }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.pendaftar.cetak-registrasi', $p->id) }}" 
                                   class="btn btn-primary" title="Cetak Bukti Registrasi" target="_blank">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                                @if($p->nomor_tes)
                                <a href="{{ route('admin.pendaftar.cetak-ujian', $p->id) }}" 
                                   class="btn btn-warning" title="Cetak Kartu Tes" target="_blank">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                @else
                                <button type="button" class="btn btn-secondary" disabled title="Belum ada nomor tes">
                                    <i class="fas fa-id-card"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            Tidak ada pendaftar yang sudah difinalisasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pendaftarList->hasPages())
    <div class="card-footer clearfix">
        {{ $pendaftarList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

{{-- Hidden Forms for Batch Print --}}
<form id="formBatchRegistrasi" action="{{ route('admin.cetak-dokumen.batch-registrasi') }}" method="POST" target="_blank">
    @csrf
    <div id="inputsRegistrasi"></div>
</form>

<form id="formBatchKartuTes" action="{{ route('admin.cetak-dokumen.batch-kartu-tes') }}" method="POST" target="_blank">
    @csrf
    <div id="inputsKartuTes"></div>
</form>
@stop

@section('js')
<script>
$(function() {
    // Update selected count
    function updateSelectedCount() {
        const checkedItems = $('.check-item:checked');
        const totalChecked = checkedItems.length;
        let tesCount = 0;
        
        checkedItems.each(function() {
            if ($(this).data('has-tes') == '1') {
                tesCount++;
            }
        });
        
        $('#selectedCountReg').text(totalChecked);
        $('#selectedCountTes').text(tesCount);
        
        $('#btnBatchRegistrasi').prop('disabled', totalChecked === 0);
        $('#btnBatchKartuTes').prop('disabled', tesCount === 0);
    }
    
    // Check all
    $('#checkAll').change(function() {
        $('.check-item').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });
    
    // Individual checkbox
    $(document).on('change', '.check-item', function() {
        updateSelectedCount();
    });
    
    // Batch print registrasi
    $('#btnBatchRegistrasi').click(function() {
        const container = $('#inputsRegistrasi');
        container.empty();
        
        $('.check-item:checked').each(function() {
            container.append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
        });
        
        $('#formBatchRegistrasi').submit();
    });
    
    // Batch print kartu tes
    $('#btnBatchKartuTes').click(function() {
        const container = $('#inputsKartuTes');
        container.empty();
        
        $('.check-item:checked').each(function() {
            if ($(this).data('has-tes') == '1') {
                container.append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
            }
        });
        
        if ($('#inputsKartuTes input').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Data',
                text: 'Tidak ada pendaftar terpilih yang memiliki nomor tes'
            });
            return;
        }
        
        $('#formBatchKartuTes').submit();
    });
});
</script>
@stop
