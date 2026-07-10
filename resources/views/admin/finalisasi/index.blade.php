@extends('adminlte::page')

@section('title', 'Finalisasi Pendaftar')

@section('css')
<style>
    .completion-badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 20px;
    }
    .completion-100 { background: #28a745; color: white; }
    .completion-partial { background: #ffc107; color: #212529; }
    .completion-0 { background: #dc3545; color: white; }
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .check-icon { color: #28a745; }
    .cross-icon { color: #dc3545; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-clipboard-check"></i> Finalisasi Pendaftar</h1>
        <p class="text-muted mb-0">Finalisasi pendaftar yang datang langsung ke sekolah</p>
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
<div class="alert alert-info">
    Finalisasi sedang memakai konteks:
    Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
    Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
    Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
</div>
{{-- Statistics --}}
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-warning stat-card">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Belum Difinalisasi</span>
                <span class="info-box-number">{{ number_format($stats['total_belum_final']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-info stat-card">
            <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Siap Finalisasi</span>
                <span class="info-box-number">{{ number_format($stats['siap_finalisasi']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-danger stat-card">
            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Belum Lengkap</span>
                <span class="info-box-number">{{ number_format($stats['belum_lengkap']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-success stat-card">
            <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sudah Finalisasi</span>
                <span class="info-box-number">{{ number_format($stats['sudah_finalisasi']) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Data Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Daftar Pendaftar</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" id="btnBatchFinalisasi" disabled>
                <i class="fas fa-check-double"></i> Finalisasi Terpilih (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form method="GET" class="mb-3">
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">
            <div class="row">
                <div class="col-md-2">
                    <select name="jalur_id" class="form-control form-control-sm">
                        <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>-- Semua Jalur --</option>
                        @foreach($jalurList as $jalur)
                        <option value="{{ $jalur->id }}" {{ (string) $selectedJalurIdInput === (string) $jalur->id ? 'selected' : '' }}>
                            {{ $jalur->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="gelombang_id" class="form-control form-control-sm">
                        <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>-- Semua Gelombang --</option>
                        @foreach($gelombangList as $gel)
                        <option value="{{ $gel->id }}" {{ (string) $selectedGelombangIdInput === (string) $gel->id ? 'selected' : '' }}>
                            {{ $gel->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="kelengkapan" class="form-control form-control-sm">
                        <option value="">-- Kelengkapan --</option>
                        <option value="lengkap" {{ request('kelengkapan') == 'lengkap' ? 'selected' : '' }}>Lengkap (100%)</option>
                        <option value="tidak_lengkap" {{ request('kelengkapan') == 'tidak_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status_finalisasi" class="form-control form-control-sm">
                        <option value="belum" {{ request('status_finalisasi', 'belum') == 'belum' ? 'selected' : '' }}>Belum Final</option>
                        <option value="sudah" {{ request('status_finalisasi') == 'sudah' ? 'selected' : '' }}>Sudah Final</option>
                        <option value="semua" {{ request('status_finalisasi') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Cari nama/NISN..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.finalisasi.index', ['tahun_pelajaran_id' => $tahunAktif?->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sync"></i>
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
                            <input type="checkbox" id="checkAll" title="Pilih Semua (Lengkap)">
                        </th>
                        <th width="40">#</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>Jalur</th>
                        <th>Gelombang</th>
                        <th class="text-center">Data Diri</th>
                        <th class="text-center">Data Ortu</th>
                        <th class="text-center">Dokumen</th>
                        <th class="text-center">Kelengkapan</th>
                        <th class="text-center" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftarList as $i => $p)
                    @php
                        $kelengkapan = 0;
                        if($p->data_diri_completed) $kelengkapan += 33;
                        if($p->data_ortu_completed) $kelengkapan += 33;
                        if($p->data_dokumen_completed) $kelengkapan += 34;
                        $isComplete = $kelengkapan >= 100;
                    @endphp
                    <tr data-id="{{ $p->id }}" data-complete="{{ $isComplete && !$p->is_finalisasi ? '1' : '0' }}">
                        <td class="text-center">
                            @if($p->is_finalisasi)
                            <i class="fas fa-check-double text-success" title="Sudah difinalisasi"></i>
                            @elseif($isComplete)
                            <input type="checkbox" class="check-item" value="{{ $p->id }}">
                            @else
                            <i class="fas fa-ban text-muted" title="Data belum lengkap"></i>
                            @endif
                        </td>
                        <td>{{ ($pendaftarList->currentPage() - 1) * $pendaftarList->perPage() + $i + 1 }}</td>
                        <td>
                            <strong>{{ $p->nama_lengkap }}</strong>
                            @if($p->nomor_registrasi)
                            <br><small class="text-muted">{{ $p->nomor_registrasi }}</small>
                            @endif
                        </td>
                        <td><code>{{ $p->nisn ?? '-' }}</code></td>
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
                        <td class="text-center">
                            @if($p->data_diri_completed)
                            <i class="fas fa-check-circle check-icon"></i>
                            @else
                            <i class="fas fa-times-circle cross-icon"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->data_ortu_completed)
                            <i class="fas fa-check-circle check-icon"></i>
                            @else
                            <i class="fas fa-times-circle cross-icon"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->data_dokumen_completed)
                            <i class="fas fa-check-circle check-icon"></i>
                            @else
                            <i class="fas fa-times-circle cross-icon"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($kelengkapan >= 100)
                            <span class="completion-badge completion-100">100%</span>
                            @elseif($kelengkapan > 0)
                            <span class="completion-badge completion-partial">{{ $kelengkapan }}%</span>
                            @else
                            <span class="completion-badge completion-0">0%</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.pendaftar.show', $p->id) }}" class="btn btn-xs btn-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($p->is_finalisasi)
                            <span class="badge badge-success" title="Sudah difinalisasi">
                                <i class="fas fa-check-double"></i> Final
                            </span>
                            @elseif($isComplete)
                            <button type="button" class="btn btn-xs btn-success btn-finalisasi" 
                                    data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}" data-noreg="{{ $p->nomor_registrasi }}" title="Finalisasi">
                                <i class="fas fa-check"></i> Finalisasi
                            </button>
                            @else
                            <button type="button" class="btn btn-xs btn-secondary" disabled title="Data belum lengkap">
                                <i class="fas fa-ban"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            Tidak ada pendaftar yang belum difinalisasi
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

{{-- Modal Konfirmasi Finalisasi --}}
<div class="modal fade" id="modalFinalisasi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-clipboard-check"></i> Konfirmasi Finalisasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin memfinalisasi pendaftar:</p>
                <h5 class="text-center text-primary" id="modalNamaPendaftar"></h5>
                <hr>
                <div id="singlePreviewLoading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Memuat preview nomor...
                </div>
                <div id="singlePreviewContent" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Jalur</small>
                            <strong id="singlePreviewJalur">-</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Gelombang</small>
                            <strong id="singlePreviewGelombang">-</strong>
                        </div>
                    </div>
                    <div class="alert alert-success mt-3 mb-3">
                        <small class="text-muted d-block">Nomor tes yang akan dipakai</small>
                        <code id="singlePreviewNomorTes" style="font-size:18px;font-weight:700;">-</code>
                        <small class="d-block mt-1">Rule/format: <span id="singlePreviewRule">-</span></small>
                        <small class="d-block mt-1 text-muted" id="singlePreviewMessage"></small>
                    </div>
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block">Nomor registrasi</small>
                        <strong id="singlePreviewNomorRegistrasi">-</strong>
                        <small class="d-block text-muted" id="singlePreviewNomorRegistrasiStatus"></small>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Setelah difinalisasi:
                    <ul class="mb-0 mt-2">
                        <li>Status akan berubah menjadi <strong>Final</strong></li>
                        <li>Nomor registrasi akan digenerate (jika belum ada)</li>
                        <li>Nomor tes akan digenerate</li>
                        <li>Pendaftar dapat mencetak bukti registrasi dan kartu tes</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmFinalisasi" disabled>
                    <i class="fas fa-check"></i> Ya, Finalisasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Batch Finalisasi --}}
<div class="modal fade" id="modalBatchFinalisasi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-double"></i> Finalisasi Batch</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin memfinalisasi <strong id="batchCount">0</strong> pendaftar terpilih?</p>
                <div id="batchPreviewLoading" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Memuat preview batch...
                </div>
                <div id="batchPreviewContent" style="display:none;">
                    <div class="row text-center mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Valid</small>
                            <strong class="text-success" id="batchPreviewValid">0</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tidak valid</small>
                            <strong class="text-danger" id="batchPreviewInvalid">0</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Nomor tes baru</small>
                            <strong class="text-primary" id="batchPreviewGenerateCount">0</strong>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height:260px;overflow:auto;">
                        <table class="table table-sm table-bordered mb-2">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>No. Tes Preview</th>
                                    <th>Rule</th>
                                </tr>
                            </thead>
                            <tbody id="batchPreviewRows"></tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block">Ditampilkan maksimal 10 pendaftar pertama. Nomor final dapat bergeser jika ada admin lain generate nomor bersamaan.</small>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Pastikan semua data pendaftar sudah benar sebelum difinalisasi.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnConfirmBatch" disabled>
                    <i class="fas fa-check-double"></i> Ya, Finalisasi Semua
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    let selectedId = null;
    const escapeHtml = (value) => $('<div>').text(value || '').html();
    
    // Update selected count
    function updateSelectedCount() {
        const count = $('.check-item:checked').length;
        $('#selectedCount').text(count);
        $('#btnBatchFinalisasi').prop('disabled', count === 0);
    }
    
    // Check all (only complete items)
    $('#checkAll').change(function() {
        $('.check-item').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });
    
    // Individual checkbox
    $(document).on('change', '.check-item', function() {
        updateSelectedCount();
    });
    
    // Single finalisasi
    $('.btn-finalisasi').click(function() {
        selectedId = $(this).data('id');
        $('#modalNamaPendaftar').text($(this).data('nama'));
        $('#btnConfirmFinalisasi').prop('disabled', true);
        $('#singlePreviewContent').hide();
        $('#singlePreviewLoading').html('<i class="fas fa-spinner fa-spin"></i> Memuat preview nomor...').show();
        $('#modalFinalisasi').modal('show');

        $.ajax({
            url: '{{ url("admin/finalisasi") }}/' + selectedId + '/preview',
            method: 'GET',
            success: function(response) {
                const data = response.data || {};
                $('#singlePreviewJalur').text(data.jalur || '-');
                $('#singlePreviewGelombang').text(data.gelombang_nomor_tes || data.gelombang || '-');
                $('#singlePreviewNomorTes').text(data.nomor_tes || 'Belum bisa dipreview');
                $('#singlePreviewRule').text(data.nomor_tes_rule || '-');
                $('#singlePreviewMessage').text(data.nomor_tes_message || 'Nomor final dapat berubah jika ada admin lain generate nomor bersamaan.');
                $('#singlePreviewNomorRegistrasi').text(data.nomor_registrasi || 'Akan digenerate');
                $('#singlePreviewNomorRegistrasiStatus').text(data.nomor_registrasi_status || '');

                $('#singlePreviewLoading').hide();
                $('#singlePreviewContent').show();
                $('#btnConfirmFinalisasi').prop('disabled', false);
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                let errorHtml = '<div class="alert alert-danger mb-0"><strong>' + escapeHtml(response.message || 'Preview gagal dimuat.') + '</strong>';
                if (response.errors) {
                    errorHtml += '<ul class="text-left mt-2 mb-0">';
                    response.errors.forEach(function(err) {
                        errorHtml += '<li>' + escapeHtml(err) + '</li>';
                    });
                    errorHtml += '</ul>';
                }
                errorHtml += '</div>';
                $('#singlePreviewLoading').html(errorHtml).show();
                $('#singlePreviewContent').hide();
                $('#btnConfirmFinalisasi').prop('disabled', true);
            }
        });
    });
    
    // Confirm single finalisasi
    $('#btnConfirmFinalisasi').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: '{{ url("admin/finalisasi") }}/' + selectedId + '/finalisasi',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#modalFinalisasi').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: response.message + '<br><br>' +
                          '<strong>No. Registrasi:</strong> ' + response.data.nomor_registrasi + '<br>' +
                          '<strong>No. Tes:</strong> ' + response.data.nomor_tes,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorHtml = '<ul class="text-left">';
                if (response.errors) {
                    response.errors.forEach(function(err) {
                        errorHtml += '<li>' + err + '</li>';
                    });
                }
                errorHtml += '</ul>';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: response.message + errorHtml
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i> Ya, Finalisasi');
            }
        });
    });
    
    // Batch finalisasi button
    $('#btnBatchFinalisasi').click(function() {
        const count = $('.check-item:checked').length;
        const ids = [];
        $('.check-item:checked').each(function() {
            ids.push($(this).val());
        });

        $('#batchCount').text(count);
        $('#btnConfirmBatch').prop('disabled', true);
        $('#batchPreviewContent').hide();
        $('#batchPreviewRows').empty();
        $('#batchPreviewLoading').html('<i class="fas fa-spinner fa-spin"></i> Memuat preview batch...').show();
        $('#modalBatchFinalisasi').modal('show');

        $.ajax({
            url: '{{ route("admin.finalisasi.batch-preview") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: ids
            },
            success: function(response) {
                const data = response.data || {};
                $('#batchPreviewValid').text(data.valid || 0);
                $('#batchPreviewInvalid').text(data.invalid || 0);
                $('#batchPreviewGenerateCount').text(data.will_generate_nomor_tes || 0);

                const rows = data.previews || [];
                if (rows.length === 0) {
                    $('#batchPreviewRows').html('<tr><td colspan="3" class="text-center text-muted">Tidak ada data preview.</td></tr>');
                } else {
                    const html = rows.map(function(item) {
                        const errors = item.errors && item.errors.length
                            ? '<small class="text-danger d-block">' + item.errors.map(escapeHtml).join(', ') + '</small>'
                            : '';
                        return '<tr>' +
                            '<td><strong>' + escapeHtml(item.nama) + '</strong>' + errors + '</td>' +
                            '<td><code>' + escapeHtml(item.nomor_tes || '-') + '</code></td>' +
                            '<td><small>' + escapeHtml(item.nomor_tes_rule || '-') + '</small></td>' +
                            '</tr>';
                    }).join('');
                    $('#batchPreviewRows').html(html);
                }

                $('#batchPreviewLoading').hide();
                $('#batchPreviewContent').show();
                $('#btnConfirmBatch').prop('disabled', (data.valid || 0) === 0);
            },
            error: function(xhr) {
                $('#batchPreviewLoading').html(
                    '<div class="alert alert-danger mb-0">' + escapeHtml(xhr.responseJSON?.message || 'Preview batch gagal dimuat.') + '</div>'
                ).show();
                $('#batchPreviewContent').hide();
                $('#btnConfirmBatch').prop('disabled', true);
            }
        });
    });
    
    // Confirm batch finalisasi
    $('#btnConfirmBatch').click(function() {
        const btn = $(this);
        const ids = [];
        $('.check-item:checked').each(function() {
            ids.push($(this).val());
        });
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: '{{ route("admin.finalisasi.batch") }}',
            method: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                ids: ids
            },
            success: function(response) {
                $('#modalBatchFinalisasi').modal('hide');
                Swal.fire({
                    icon: response.data.failed > 0 ? 'warning' : 'success',
                    title: response.data.failed > 0 ? 'Sebagian Berhasil' : 'Berhasil!',
                    html: 'Berhasil: <strong class="text-success">' + response.data.success + '</strong><br>' +
                          'Gagal: <strong class="text-danger">' + response.data.failed + '</strong>',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Ya, Finalisasi Semua');
            }
        });
    });
});
</script>
@stop
