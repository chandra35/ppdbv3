@extends('adminlte::page')

@section('title', 'Matrikulasi PPDB')

@section('css')
<style>
    .matrikulasi-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: end;
    }
    .match-textarea {
        min-height: 320px;
        font-family: Consolas, Monaco, monospace;
        font-size: 13px;
        line-height: 1.45;
    }
    .summary-tile {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 14px 16px;
        background: #fff;
        height: 100%;
    }
    .summary-tile .label {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .summary-tile .value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 800;
    }
    .table-preview td {
        vertical-align: middle;
    }
    .badge-score {
        min-width: 46px;
        display: inline-block;
    }
    .missing-panel {
        display: none;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fef2f2;
        color: #7f1d1d;
        padding: 12px 14px;
        margin-bottom: 14px;
    }
    .missing-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }
    .missing-chip {
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 12px;
        font-weight: 700;
    }
    .row-missing {
        background: #fff1f2;
    }
    .preview-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-user-graduate mr-2"></i>Matrikulasi PPDB</h1>
        <p class="text-muted mb-0">Smart match daftar nama peserta matrikulasi untuk export data lengkap dan nilai.</p>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Matrikulasi PPDB</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="alert alert-info">
        Simpan peserta matrikulasi dari daftar nama, assign Reguler/Asrama, tandai Smart-Q, lalu export data lengkap beserta nilai.
    </div>

    <div class="row mb-3">
        <div class="col-md-3 col-6 mb-2">
            <div class="summary-tile">
                <div class="label">Tersimpan</div>
                <div class="value" id="storedTotal">{{ $storedStats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="summary-tile">
                <div class="label">Reguler</div>
                <div class="value text-primary" id="storedReguler">{{ $storedStats['reguler'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="summary-tile">
                <div class="label">Asrama</div>
                <div class="value text-info" id="storedAsrama">{{ $storedStats['asrama'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="summary-tile">
                <div class="label">Smart-Q</div>
                <div class="value text-success" id="storedSmartQ">{{ $storedStats['smart_q'] }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i>Konteks Data</h3>
        </div>
        <div class="card-body">
            <div class="matrikulasi-toolbar">
                <div style="min-width: 220px;">
                    <label>Tahun Pelajaran</label>
                    <select id="tahun_pelajaran_id" class="form-control">
                        @foreach($tahunPelajarans as $tahun)
                            <option value="{{ $tahun->id }}" data-label="{{ $tahun->nama }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 220px;">
                    <label>Jalur</label>
                    <select id="jalur_id" class="form-control">
                        <option value="" {{ in_array($selectedJalurIdInput, [null, 'all'], true) ? 'selected' : '' }}>Semua Jalur</option>
                        @foreach($jalurs as $jalur)
                            <option value="{{ $jalur->id }}" {{ $selectedJalurIdInput == $jalur->id ? 'selected' : '' }}>{{ $jalur->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 220px;">
                    <label>Gelombang</label>
                    <select id="gelombang_id" class="form-control">
                        <option value="" {{ in_array($selectedGelombangIdInput, [null, 'all'], true) ? 'selected' : '' }}>Semua Gelombang</option>
                        @foreach($gelombangs as $gelombang)
                            <option value="{{ $gelombang->id }}" {{ $selectedGelombangIdInput == $gelombang->id ? 'selected' : '' }}>{{ $gelombang->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="include_all_year" value="1">
                    <label class="form-check-label" for="include_all_year">Cari lintas tahun</label>
                </div>
                <button type="button" id="btnApplyContext" class="btn btn-outline-primary mb-1">
                    <i class="fas fa-sync-alt mr-1"></i>Terapkan
                </button>
                <form id="exportStoredForm" method="POST" action="{{ route('admin.matrikulasi.export-stored') }}" class="mb-1">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" class="stored_tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">
                    <input type="hidden" name="jalur_id" class="stored_jalur_id" value="{{ $selectedJalurIdInput === 'all' ? '' : $selectedJalurIdInput }}">
                    <input type="hidden" name="gelombang_id" class="stored_gelombang_id" value="{{ $selectedGelombangIdInput === 'all' ? '' : $selectedGelombangIdInput }}">
                    <input type="hidden" name="tahun_label" class="stored_tahun_label" value="{{ $selectedTahun?->nama ?? 'matrikulasi' }}">
                    <input type="hidden" name="kategori" id="stored_export_kategori" value="">
                    <input type="hidden" name="smart_q" id="stored_export_smart_q" value="all">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i>Export Data Tersimpan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <form id="exportForm" method="POST" action="{{ route('admin.matrikulasi.export') }}">
        @csrf
        <input type="hidden" name="tahun_pelajaran_id" id="export_tahun_pelajaran_id" value="{{ $selectedTahunIdInput }}">
        <input type="hidden" name="jalur_id" id="export_jalur_id" value="{{ $selectedJalurIdInput === 'all' ? '' : $selectedJalurIdInput }}">
        <input type="hidden" name="gelombang_id" id="export_gelombang_id" value="{{ $selectedGelombangIdInput === 'all' ? '' : $selectedGelombangIdInput }}">
        <input type="hidden" name="tahun_label" id="export_tahun_label" value="{{ $selectedTahun?->nama ?? 'matrikulasi' }}">
        <input type="hidden" name="include_all_year" id="export_include_all_year" value="0">

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list mr-1"></i>Daftar Nama</h3>
                    </div>
                    <div class="card-body">
                        <label>Paste nama/NISN/nomor tes, satu peserta per baris</label>
                        <textarea name="names" id="names" class="form-control match-textarea" placeholder="ABD KATON RHAMADAN&#10;ABEL AULIA HASNA&#10;011267781">{{ old('names') }}</textarea>
                        <small class="text-muted d-block mt-2">Bisa menampung ratusan baris. Matching memakai nomor dulu, lalu nama exact, lalu fuzzy nama.</small>
                        <div class="border rounded p-3 mt-3 bg-light">
                            <label>Assign Matrikulasi</label>
                            <select name="kategori" id="kategori" class="form-control">
                                <option value="">Simpan tanpa kategori</option>
                                <option value="reguler">Reguler</option>
                                <option value="asrama">Asrama</option>
                            </select>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="is_smart_q" id="is_smart_q" value="1">
                                <label class="form-check-label" for="is_smart_q">Tandai ikut Smart-Q</label>
                            </div>
                            <small class="text-muted d-block mt-2">Untuk Smart-Q yang juga asrama/reguler, pilih kategorinya lalu centang Smart-Q.</small>
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-wrap" style="gap: .5rem;">
                        <button type="button" id="btnPreview" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>Preview Match
                        </button>
                        <button type="button" id="btnSave" class="btn btn-info" disabled>
                            <i class="fas fa-save mr-1"></i>Simpan/Assign
                        </button>
                        <button type="submit" id="btnExport" class="btn btn-success" disabled>
                            <i class="fas fa-file-excel mr-1"></i>Export XLS
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row mb-3">
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-tile">
                            <div class="label">Baris</div>
                            <div class="value" id="sumLines">0</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-tile">
                            <div class="label">Ketemu</div>
                            <div class="value text-success" id="sumFound">0</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-tile">
                            <div class="label">Duplikat</div>
                            <div class="value text-warning" id="sumDuplicate">0</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-tile">
                            <div class="label">Belum Ketemu</div>
                            <div class="value text-danger" id="sumMissing">0</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-table mr-1"></i>Preview Hasil Matching</h3>
                        <div class="card-tools preview-actions">
                            <button type="button" class="btn btn-xs btn-outline-secondary btnPreviewFilter active" data-filter="all">Semua</button>
                            <button type="button" class="btn btn-xs btn-outline-success btnPreviewFilter" data-filter="found">Ketemu</button>
                            <button type="button" class="btn btn-xs btn-outline-danger btnPreviewFilter" data-filter="not_found">Belum ketemu</button>
                            <button type="button" class="btn btn-xs btn-outline-warning btnPreviewFilter" data-filter="duplicate">Duplikat</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="missingPanel" class="missing-panel">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><i class="fas fa-exclamation-triangle mr-1"></i>Baris belum ketemu</strong>
                                    <div class="small">Periksa ejaan, NISN, atau coba aktifkan Cari lintas tahun.</div>
                                </div>
                                <button type="button" id="btnCopyMissing" class="btn btn-xs btn-outline-danger">
                                    <i class="fas fa-copy mr-1"></i>Copy
                                </button>
                            </div>
                            <div id="missingList" class="missing-list"></div>
                        </div>
                        <div class="d-flex flex-wrap mb-2" style="gap: .5rem;">
                            <button type="button" class="btn btn-sm btn-outline-primary btnSetStoredFilter" data-kategori="" data-smart="all">Export Semua Tersimpan</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btnSetStoredFilter" data-kategori="reguler" data-smart="all">Reguler</button>
                            <button type="button" class="btn btn-sm btn-outline-info btnSetStoredFilter" data-kategori="asrama" data-smart="all">Asrama</button>
                            <button type="button" class="btn btn-sm btn-outline-success btnSetStoredFilter" data-kategori="" data-smart="yes">Smart-Q</button>
                        </div>
                    </div>
                    <div class="table-responsive p-0" style="max-height: 620px;">
                        <table class="table table-hover table-sm table-preview mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Input</th>
                                    <th>Hasil PPDB</th>
                                    <th style="width: 110px;">Nilai</th>
                                    <th style="width: 110px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">Paste daftar nama lalu klik Preview Match.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(function () {
    const routes = {
        preview: @json(route('admin.matrikulasi.preview')),
        store: @json(route('admin.matrikulasi.store')),
    };
    let lastMatches = [];
    let activeFilter = 'all';

    function syncContext() {
        $('#export_tahun_pelajaran_id').val($('#tahun_pelajaran_id').val());
        $('#export_jalur_id').val($('#jalur_id').val());
        $('#export_gelombang_id').val($('#gelombang_id').val());
        $('#export_tahun_label').val($('#tahun_pelajaran_id option:selected').data('label') || 'matrikulasi');
        $('#export_include_all_year').val($('#include_all_year').is(':checked') ? '1' : '0');
        $('.stored_tahun_pelajaran_id').val($('#tahun_pelajaran_id').val());
        $('.stored_jalur_id').val($('#jalur_id').val());
        $('.stored_gelombang_id').val($('#gelombang_id').val());
        $('.stored_tahun_label').val($('#tahun_pelajaran_id option:selected').data('label') || 'matrikulasi');
    }

    function badgeStatus(status) {
        if (status === 'found') return '<span class="badge badge-success">Ketemu</span>';
        if (status === 'duplicate') return '<span class="badge badge-warning">Duplikat</span>';
        return '<span class="badge badge-danger">Belum ketemu</span>';
    }

    function renderMissingPanel(matches) {
        const missing = matches.filter(item => item.status === 'not_found');
        if (!missing.length) {
            $('#missingPanel').hide();
            $('#missingList').empty();
            return;
        }

        const chips = missing.map(item => {
            const input = $('<div>').text(item.input).html();
            return `<span class="missing-chip">#${item.row} ${input}</span>`;
        });

        $('#missingList').html(chips.join(''));
        $('#missingPanel').show();
    }

    function filteredMatches() {
        if (activeFilter === 'all') return lastMatches;
        return lastMatches.filter(item => item.status === activeFilter);
    }

    function renderRows() {
        const matches = filteredMatches();
        renderMissingPanel(lastMatches);

        if (!matches.length) {
            $('#previewBody').html('<tr><td colspan="5" class="text-center text-muted py-5">Belum ada baris untuk diproses.</td></tr>');
            return;
        }

        const rows = matches.map(item => {
            const candidate = item.candidate;
            const scoreClass = item.score >= 90 ? 'badge-success' : (item.score >= 78 ? 'badge-info' : 'badge-secondary');
            const rowClass = item.status === 'not_found' ? ' class="row-missing"' : '';
            const nilai = candidate
                ? `<span class="badge badge-light">Rapor ${candidate.rapor_count}/5</span><br><span class="badge ${candidate.has_cbt ? 'badge-success' : 'badge-secondary'}">${candidate.has_cbt ? 'CBT ada' : 'CBT kosong'}</span>`
                : '-';
            const hasil = candidate
                ? `<strong>${candidate.nama_lengkap || '-'}</strong><br><small class="text-muted">${candidate.nisn || '-'} | ${candidate.nomor_tes || '-'} | ${candidate.nomor_registrasi || '-'}</small><br><small>${candidate.jalur || '-'} / ${candidate.gelombang || '-'}</small>${storedBadges(candidate)}`
                : '<span class="text-danger">Tidak ditemukan</span>';

            return `<tr${rowClass}>
                <td>${item.row}</td>
                <td>${$('<div>').text(item.input).html()}<br><span class="badge ${scoreClass} badge-score">${item.score}</span></td>
                <td>${hasil}</td>
                <td>${nilai}</td>
                <td>${badgeStatus(item.status)}</td>
            </tr>`;
        });

        $('#previewBody').html(rows.join(''));
    }

    function setLoading(isLoading) {
        $('#btnPreview').prop('disabled', isLoading).html(isLoading
            ? '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...'
            : '<i class="fas fa-search mr-1"></i>Preview Match');
    }

    function storedBadges(candidate) {
        if (!candidate.matrikulasi) return '';
        const badges = [];
        if (candidate.matrikulasi.kategori) {
            badges.push(`<span class="badge badge-primary">${candidate.matrikulasi.kategori === 'asrama' ? 'Asrama' : 'Reguler'}</span>`);
        }
        if (candidate.matrikulasi.is_smart_q) {
            badges.push('<span class="badge badge-success">Smart-Q</span>');
        }
        return badges.length ? `<br>${badges.join(' ')}` : '';
    }

    function setSaveLoading(isLoading) {
        $('#btnSave').prop('disabled', isLoading || !(lastMatches.filter(item => item.status === 'found').length > 0)).html(isLoading
            ? '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'
            : '<i class="fas fa-save mr-1"></i>Simpan/Assign');
    }

    function updateStoredStats(stats) {
        if (!stats) return;
        $('#storedTotal').text(stats.total || 0);
        $('#storedReguler').text(stats.reguler || 0);
        $('#storedAsrama').text(stats.asrama || 0);
        $('#storedSmartQ').text(stats.smart_q || 0);
    }

    $('#btnApplyContext').on('click', function () {
        const params = new URLSearchParams();
        if ($('#tahun_pelajaran_id').val()) params.set('tahun_pelajaran_id', $('#tahun_pelajaran_id').val());
        if ($('#jalur_id').val()) params.set('jalur_id', $('#jalur_id').val());
        if ($('#gelombang_id').val()) params.set('gelombang_id', $('#gelombang_id').val());
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    });

    $('#tahun_pelajaran_id, #jalur_id, #gelombang_id, #include_all_year').on('change', syncContext);

    $('#btnPreview').on('click', function () {
        syncContext();
        setLoading(true);
        $('#btnExport').prop('disabled', true);
        $('#btnSave').prop('disabled', true);

        $.post(routes.preview, $('#exportForm').serialize())
            .done(response => {
                const data = response.data || {};
                $('#sumLines').text(data.total_lines || 0);
                $('#sumFound').text(data.found || 0);
                $('#sumDuplicate').text(data.duplicate || 0);
                $('#sumMissing').text(data.not_found || 0);
                lastMatches = data.matches || [];
                activeFilter = (data.not_found || 0) > 0 ? 'not_found' : 'all';
                $('.btnPreviewFilter').removeClass('active');
                $(`.btnPreviewFilter[data-filter="${activeFilter}"]`).addClass('active');
                renderRows();
                $('#btnExport').prop('disabled', !(data.found > 0));
                $('#btnSave').prop('disabled', !(data.found > 0));
            })
            .fail(xhr => {
                alert(xhr.responseJSON?.message || 'Preview matching gagal.');
            })
            .always(() => setLoading(false));
    });

    $('#btnSave').on('click', function () {
        syncContext();
        if (!$('#names').val().trim()) {
            alert('Isi daftar nama terlebih dahulu.');
            return;
        }

        setSaveLoading(true);
        $.post(routes.store, $('#exportForm').serialize())
            .done(response => {
                updateStoredStats(response.data?.stats);
                alert(response.message || 'Data matrikulasi disimpan.');
                $('#btnPreview').trigger('click');
            })
            .fail(xhr => {
                alert(xhr.responseJSON?.message || 'Gagal menyimpan data matrikulasi.');
            })
            .always(() => setSaveLoading(false));
    });

    $('.btnSetStoredFilter').on('click', function () {
        $('#stored_export_kategori').val($(this).data('kategori') || '');
        $('#stored_export_smart_q').val($(this).data('smart') || 'all');
        $('.btnSetStoredFilter').removeClass('active');
        $(this).addClass('active');
    });

    $('.btnPreviewFilter').on('click', function () {
        activeFilter = $(this).data('filter');
        $('.btnPreviewFilter').removeClass('active');
        $(this).addClass('active');
        renderRows();
    });

    $('#btnCopyMissing').on('click', function () {
        const missing = lastMatches
            .filter(item => item.status === 'not_found')
            .map(item => item.input)
            .join('\n');

        if (!missing) return;
        navigator.clipboard?.writeText(missing);
        $(this).html('<i class="fas fa-check mr-1"></i>Copied');
        setTimeout(() => $(this).html('<i class="fas fa-copy mr-1"></i>Copy'), 1200);
    });

    $('#exportForm').on('submit', function (event) {
        syncContext();
        if (!$('#names').val().trim()) {
            event.preventDefault();
            alert('Isi daftar nama terlebih dahulu.');
        }
    });

    syncContext();
});
</script>
@endsection
