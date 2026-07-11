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
        Export mengambil data pendaftar lengkap, nilai rapor semester 1-5, nilai CBT, nilai TBQ, nilai akhir, status admisi, jalur, dan gelombang.
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
                    </div>
                    <div class="card-footer d-flex flex-wrap" style="gap: .5rem;">
                        <button type="button" id="btnPreview" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>Preview Match
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
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 620px;">
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
    };

    function syncContext() {
        $('#export_tahun_pelajaran_id').val($('#tahun_pelajaran_id').val());
        $('#export_jalur_id').val($('#jalur_id').val());
        $('#export_gelombang_id').val($('#gelombang_id').val());
        $('#export_tahun_label').val($('#tahun_pelajaran_id option:selected').data('label') || 'matrikulasi');
        $('#export_include_all_year').val($('#include_all_year').is(':checked') ? '1' : '0');
    }

    function badgeStatus(status) {
        if (status === 'found') return '<span class="badge badge-success">Ketemu</span>';
        if (status === 'duplicate') return '<span class="badge badge-warning">Duplikat</span>';
        return '<span class="badge badge-danger">Belum ketemu</span>';
    }

    function renderRows(matches) {
        if (!matches.length) {
            $('#previewBody').html('<tr><td colspan="5" class="text-center text-muted py-5">Belum ada baris untuk diproses.</td></tr>');
            return;
        }

        const rows = matches.map(item => {
            const candidate = item.candidate;
            const scoreClass = item.score >= 90 ? 'badge-success' : (item.score >= 78 ? 'badge-info' : 'badge-secondary');
            const nilai = candidate
                ? `<span class="badge badge-light">Rapor ${candidate.rapor_count}/5</span><br><span class="badge ${candidate.has_cbt ? 'badge-success' : 'badge-secondary'}">${candidate.has_cbt ? 'CBT ada' : 'CBT kosong'}</span>`
                : '-';
            const hasil = candidate
                ? `<strong>${candidate.nama_lengkap || '-'}</strong><br><small class="text-muted">${candidate.nisn || '-'} | ${candidate.nomor_tes || '-'} | ${candidate.nomor_registrasi || '-'}</small><br><small>${candidate.jalur || '-'} / ${candidate.gelombang || '-'}</small>`
                : '<span class="text-danger">Tidak ditemukan</span>';

            return `<tr>
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

        $.post(routes.preview, $('#exportForm').serialize())
            .done(response => {
                const data = response.data || {};
                $('#sumLines').text(data.total_lines || 0);
                $('#sumFound').text(data.found || 0);
                $('#sumDuplicate').text(data.duplicate || 0);
                $('#sumMissing').text(data.not_found || 0);
                renderRows(data.matches || []);
                $('#btnExport').prop('disabled', !(data.found > 0));
            })
            .fail(xhr => {
                alert(xhr.responseJSON?.message || 'Preview matching gagal.');
            })
            .always(() => setLoading(false));
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
