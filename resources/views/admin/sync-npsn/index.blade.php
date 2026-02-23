@extends('adminlte::page')

@section('title', 'Sync NPSN Asal Sekolah')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-sync-alt text-info mr-2"></i>Sync NPSN Asal Sekolah</h1>
            <small class="text-muted">Sinkronisasi data sekolah asal (status, bentuk pendidikan, akreditasi) dari Kemendikdasmen</small>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    {{-- Statistik Cards --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($totalDenganNpsn) }}</h3>
                <p>Punya NPSN</p>
            </div>
            <div class="icon"><i class="fas fa-school"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($totalBelumSync) }}</h3>
                <p>Belum Sync</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($totalSudahSync) }}</h3>
                <p>Sudah Sync</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($totalTanpaNpsn) }}</h3>
                <p>Tanpa NPSN</p>
            </div>
            <div class="icon"><i class="fas fa-question-circle"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter & Aksi</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sync-npsn.index') }}" class="row align-items-end">
            <div class="col-md-3">
                <label>Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-control form-control-sm">
                    @foreach($tahunList as $tp)
                        <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                            {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Status Sync</label>
                <select name="filter_status" class="form-control form-control-sm">
                    <option value="belum" {{ $filterStatus == 'belum' ? 'selected' : '' }}>Belum Sync</option>
                    <option value="sudah" {{ $filterStatus == 'sudah' ? 'selected' : '' }}>Sudah Sync</option>
                    <option value="semua" {{ $filterStatus == 'semua' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary btn-block">
                    <i class="fas fa-search mr-1"></i>Filter
                </button>
            </div>
            <div class="col-md-4 text-right">
                @if($totalBelumSync > 0)
                <button type="button" id="btnSyncAll" class="btn btn-sm btn-info">
                    <i class="fas fa-sync-alt mr-1"></i>Sync Semua Belum Sync
                    <span class="badge badge-light ml-1">{{ $npsnUnikBelumSync }} NPSN</span>
                </button>
                @else
                <button type="button" class="btn btn-sm btn-success" disabled>
                    <i class="fas fa-check mr-1"></i>Semua Sudah Sync
                </button>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Progress Bar (hidden by default) --}}
<div id="syncProgress" class="card card-outline card-primary" style="display:none;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tasks mr-1"></i>Progress Sync</h3>
        <div class="card-tools">
            <button type="button" id="btnCancelSync" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-stop mr-1"></i>Stop
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="progress mb-2" style="height: 25px;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                 role="progressbar" style="width: 0%;">0%</div>
        </div>
        <div class="d-flex justify-content-between text-sm">
            <span id="progressText">Mempersiapkan...</span>
            <span>
                <span class="text-success" id="progressSuccess">0</span> berhasil |
                <span class="text-danger" id="progressFailed">0</span> gagal |
                <span class="text-muted" id="progressRemaining">0</span> tersisa
            </span>
        </div>
        <div id="syncLog" class="mt-3" style="max-height: 200px; overflow-y: auto; font-size: 0.8em; display:none;">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr><th width="10%">NPSN</th><th>Nama Sekolah</th><th width="12%">Status</th><th width="12%">Bentuk</th><th width="8%">Akr.</th><th width="8%">Siswa</th><th width="12%">Hasil</th></tr>
                </thead>
                <tbody id="syncLogBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Tabel Data --}}
<div class="card card-outline card-default">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>Data Pendaftar ({{ $pendaftarList->count() }} data)
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" id="tblPendaftar">
                <thead class="thead-light">
                    <tr>
                        <th width="3%">No</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>NPSN</th>
                        <th>Nama Sekolah</th>
                        <th>Status</th>
                        <th>Bentuk</th>
                        <th>Akreditasi</th>
                        <th>Kabupaten</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftarList as $i => $p)
                    <tr id="row-{{ $p->id }}">
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $p->nama_lengkap }}</strong></td>
                        <td><code>{{ $p->nisn }}</code></td>
                        <td><code class="npsn-cell">{{ $p->npsn_asal_sekolah }}</code></td>
                        <td class="nama-sekolah-cell">{{ $p->nama_sekolah_asal ?? '-' }}</td>
                        <td class="status-cell">
                            @if($p->status_sekolah_asal)
                                <span class="badge badge-{{ $p->status_sekolah_asal == 'NEGERI' ? 'primary' : 'warning' }}">
                                    {{ $p->status_sekolah_asal }}
                                </span>
                            @else
                                <span class="badge badge-light text-danger">Kosong</span>
                            @endif
                        </td>
                        <td class="bentuk-cell">
                            @if($p->bentuk_sekolah_asal)
                                <span class="badge badge-info">{{ $p->bentuk_sekolah_asal }}</span>
                            @else
                                <span class="badge badge-light text-danger">Kosong</span>
                            @endif
                        </td>
                        <td class="akreditasi-cell">
                            {{ $p->akreditasi_sekolah_asal ?? '-' }}
                        </td>
                        <td>{{ $p->kabupaten_sekolah_asal ?? '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-xs btn-outline-info btn-sync-one"
                                    data-npsn="{{ $p->npsn_asal_sekolah }}"
                                    data-id="{{ $p->id }}"
                                    title="Sync NPSN {{ $p->npsn_asal_sekolah }}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                            @if($filterStatus == 'belum')
                                Semua data NPSN sudah tersinkronisasi!
                            @else
                                Tidak ada data yang sesuai filter.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .inner h3 { font-size: 2rem; }
    .small-box .inner p { font-size: 0.85rem; }
    .table td, .table th { vertical-align: middle; }
    #syncLog table { font-size: 0.85em; }
    .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
</style>
@stop

@section('js')
<script>
$(function() {
    const csrfToken = '{{ csrf_token() }}';
    const syncOneUrl = '{{ route("admin.sync-npsn.sync-one") }}';
    const npsnListUrl = '{{ route("admin.sync-npsn.npsn-list") }}';
    const tahunId = '{{ $selectedTahunId }}';
    let cancelSync = false;

    // ============================================
    // SYNC INDIVIDUAL NPSN
    // ============================================
    $(document).on('click', '.btn-sync-one', function() {
        const $btn = $(this);
        const npsn = $btn.data('npsn');
        const originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: syncOneUrl,
            type: 'POST',
            data: { _token: csrfToken, npsn: npsn, tahun_pelajaran_id: tahunId },
            success: function(res) {
                if (res.success) {
                    // Update ALL rows with same NPSN
                    $('tr').each(function() {
                        const $row = $(this);
                        const rowNpsn = $row.find('.npsn-cell').text().trim();
                        if (rowNpsn === npsn && res.data) {
                            updateRowData($row, res.data);
                        }
                    });
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal sync NPSN';
                toastr.error(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function updateRowData($row, data) {
        if (data.nama_sekolah_asal) {
            $row.find('.nama-sekolah-cell').text(data.nama_sekolah_asal);
        }
        if (data.status_sekolah_asal) {
            const cls = data.status_sekolah_asal === 'NEGERI' ? 'primary' : 'warning';
            $row.find('.status-cell').html('<span class="badge badge-' + cls + '">' + data.status_sekolah_asal + '</span>');
        }
        if (data.bentuk_sekolah_asal) {
            $row.find('.bentuk-cell').html('<span class="badge badge-info">' + data.bentuk_sekolah_asal + '</span>');
        }
        if (data.akreditasi_sekolah_asal) {
            $row.find('.akreditasi-cell').text(data.akreditasi_sekolah_asal);
        }
    }

    // ============================================
    // BATCH SYNC ALL
    // ============================================
    $('#btnSyncAll').on('click', function() {
        Swal.fire({
            title: 'Sync Semua NPSN?',
            html: 'Akan mengambil data dari <strong>referensi.data.kemendikdasmen.go.id</strong> untuk <strong>{{ $npsnUnikBelumSync }} NPSN unik</strong> yang belum tersinkronisasi.<br><br><small class="text-muted">Proses ini mungkin membutuhkan waktu beberapa menit tergantung jumlah data dan kecepatan server Kemendikdasmen.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i>Mulai Sync',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#17a2b8',
        }).then((result) => {
            if (result.isConfirmed) {
                startBatchSync();
            }
        });
    });

    function startBatchSync() {
        cancelSync = false;
        const $progress = $('#syncProgress');
        const $progressBar = $('#progressBar');
        const $progressText = $('#progressText');
        const $logBody = $('#syncLogBody');

        // Show progress, hide sync button
        $progress.slideDown();
        $('#btnSyncAll').prop('disabled', true);
        $logBody.empty();
        $('#syncLog').show();

        // Fetch NPSN list
        $.get(npsnListUrl, { tahun_pelajaran_id: tahunId }, function(res) {
            if (!res.success || res.data.length === 0) {
                $progressText.text('Tidak ada NPSN yang perlu di-sync.');
                return;
            }

            const npsnList = res.data;
            const total = npsnList.length;
            let current = 0;
            let success = 0;
            let failed = 0;

            $('#progressRemaining').text(total);
            $progressText.text('Memulai sync ' + total + ' NPSN unik...');

            function syncNext() {
                if (cancelSync || current >= total) {
                    // Done
                    const statusMsg = cancelSync ? 'Sync dihentikan.' : 'Sync selesai!';
                    $progressText.html('<strong>' + statusMsg + '</strong> ' + success + ' berhasil, ' + failed + ' gagal.');
                    $progressBar.removeClass('progress-bar-animated');
                    if (!cancelSync) $progressBar.removeClass('bg-info').addClass('bg-success');
                    else $progressBar.removeClass('bg-info').addClass('bg-warning');
                    $('#btnSyncAll').prop('disabled', false);
                    
                    if (success > 0) {
                        setTimeout(function() {
                            Swal.fire({
                                icon: cancelSync ? 'warning' : 'success',
                                title: statusMsg,
                                html: success + ' NPSN berhasil di-sync, ' + failed + ' gagal.<br><small>Halaman akan di-refresh.</small>',
                                timer: 3000,
                                showConfirmButton: true,
                            }).then(() => location.reload());
                        }, 500);
                    }
                    return;
                }

                const item = npsnList[current];
                const pct = Math.round(((current + 1) / total) * 100);
                $progressBar.css('width', pct + '%').text(pct + '%');
                $progressText.text('Sync ' + (current + 1) + '/' + total + ': ' + item.npsn + ' (' + (item.nama_sekolah || '...') + ')');

                $.ajax({
                    url: syncOneUrl,
                    type: 'POST',
                    data: { _token: csrfToken, npsn: item.npsn, tahun_pelajaran_id: tahunId },
                    timeout: 30000,
                    success: function(res) {
                        if (res.success) {
                            success++;
                            const d = res.data || {};
                            addLogRow(item.npsn, d.nama_sekolah_asal || item.nama_sekolah, d.status_sekolah_asal, d.bentuk_sekolah_asal, d.akreditasi_sekolah_asal, res.affected, true);
                        } else {
                            failed++;
                            addLogRow(item.npsn, item.nama_sekolah, '-', '-', '-', item.jumlah, false, res.message);
                        }
                    },
                    error: function(xhr) {
                        failed++;
                        addLogRow(item.npsn, item.nama_sekolah, '-', '-', '-', item.jumlah, false, xhr.responseJSON?.message || 'Error');
                    },
                    complete: function() {
                        current++;
                        $('#progressSuccess').text(success);
                        $('#progressFailed').text(failed);
                        $('#progressRemaining').text(total - current);

                        // Delay 1 detik antar request agar tidak overload server Kemendikdasmen
                        setTimeout(syncNext, 1000);
                    }
                });
            }

            syncNext();
        });
    }

    function addLogRow(npsn, namaSekolah, status, bentuk, akreditasi, affected, isSuccess, errorMsg) {
        const cls = isSuccess ? 'table-success' : 'table-danger';
        const hasil = isSuccess 
            ? '<span class="badge badge-success">OK (' + affected + ')</span>'
            : '<span class="badge badge-danger" title="' + (errorMsg || '') + '">Gagal</span>';
        
        $('#syncLogBody').prepend(
            '<tr class="' + cls + '">' +
            '<td><code>' + npsn + '</code></td>' +
            '<td>' + (namaSekolah || '-') + '</td>' +
            '<td>' + (status || '-') + '</td>' +
            '<td>' + (bentuk || '-') + '</td>' +
            '<td>' + (akreditasi || '-') + '</td>' +
            '<td>' + (affected || '-') + '</td>' +
            '<td>' + hasil + '</td>' +
            '</tr>'
        );
    }

    // Cancel button
    $('#btnCancelSync').on('click', function() {
        cancelSync = true;
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Stopping...');
    });

    // DataTable (simple, no server-side)
    if ($.fn.DataTable && $('#tblPendaftar tbody tr').length > 1) {
        $('#tblPendaftar').DataTable({
            paging: true,
            pageLength: 25,
            ordering: true,
            info: true,
            searching: true,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                paginate: { previous: '&laquo;', next: '&raquo;' },
                zeroRecords: 'Data tidak ditemukan',
            }
        });
    }
});
</script>
@stop
