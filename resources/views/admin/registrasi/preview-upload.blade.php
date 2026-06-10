@extends('adminlte::page')

@section('title', 'Preview Import Registrasi')

@section('plugins.Select2', true)

@section('css')
<style>
    .preview-table { font-size: .82rem; }
    .preview-table td, .preview-table th { padding: .4rem .5rem; vertical-align: middle; }
    .row-exact { border-left: 4px solid #28a745; }
    .row-fuzzy { border-left: 4px solid #17a2b8; }
    .row-conflict { border-left: 4px solid #ffc107; }
    .row-unmatched { border-left: 4px solid #dc3545; }
    .filter-btn.active { font-weight: bold; box-shadow: 0 0 0 2px rgba(0,123,255,.5); }
    .progress-overlay {
        display: none; position: fixed; top:0; left:0; right:0; bottom:0;
        background: rgba(0,0,0,.6); z-index: 9999; justify-content: center; align-items: center;
    }
    .progress-overlay.show { display: flex; }
    .progress-box {
        background:#fff; border-radius:12px; padding:2rem 3rem; min-width:400px;
        text-align:center; box-shadow:0 8px 32px rgba(0,0,0,.3);
    }
    .progress-box .progress { height:24px; border-radius:12px; }
    .progress-box .progress-bar { transition: width .3s ease; font-size:.85rem; font-weight:bold; }
    .cand-select { min-width: 230px; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-search mr-2"></i>Preview Import <span class="text-primary">Registrasi</span></h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.registrasi.index') }}">Registrasi</a></li>
            <li class="breadcrumb-item active">Preview</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="callout callout-primary">
        <h5 class="mb-0">
            <i class="fas fa-file-excel mr-1"></i> File: <code>{{ $originalName }}</code>
            &nbsp;|&nbsp; Tahun: <strong>{{ $tahunAktif->nama }}</strong>
        </h5>
    </div>

    {{-- Summary --}}
    <div class="row">
        <div class="col-lg col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $preview['summary']['total'] }}</h3><p>Total Baris</p></div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $preview['summary']['exact'] }}</h3><p>Cocok Persis</p></div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $preview['summary']['fuzzy'] }}</h3><p>Mirip</p></div>
                <div class="icon"><i class="fas fa-equals"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $preview['summary']['conflict'] }}</h3><p>Konflik Jurusan</p></div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $preview['summary']['unmatched'] }}</h3><p>Tidak Cocok</p></div>
                <div class="icon"><i class="fas fa-times"></i></div>
            </div>
        </div>
    </div>

    @if($preview['summary']['duplicate'] > 0)
        <div class="alert alert-warning">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>{{ $preview['summary']['duplicate'] }}</strong> pendaftar sudah pernah diregistrasi pada tahun ini &mdash; jika tetap disimpan, datanya akan diperbarui.
        </div>
    @endif

    {{-- Legend --}}
    <div class="card card-outline card-info mb-3">
        <div class="card-body py-2">
            <small>
                <strong>Legenda baris:</strong>
                <span class="badge badge-success">Cocok Persis</span> nomor tes + nama + jurusan cocok &nbsp;
                <span class="badge badge-info">Mirip</span> nama mirip / dicocokkan sebagian &nbsp;
                <span class="badge badge-warning">Konflik Jurusan</span> kemungkinan pindah jurusan &nbsp;
                <span class="badge badge-danger">Tidak Cocok</span> pilih manual atau lewati
            </small>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-3">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">Semua ({{ $preview['summary']['total'] }})</button>
            <button type="button" class="btn btn-outline-success filter-btn" data-filter="matched_exact">Cocok ({{ $preview['summary']['exact'] }})</button>
            <button type="button" class="btn btn-outline-info filter-btn" data-filter="matched_fuzzy">Mirip ({{ $preview['summary']['fuzzy'] }})</button>
            <button type="button" class="btn btn-outline-warning filter-btn" data-filter="conflict_jurusan">Konflik ({{ $preview['summary']['conflict'] }})</button>
            <button type="button" class="btn btn-outline-danger filter-btn" data-filter="unmatched">Tidak Cocok ({{ $preview['summary']['unmatched'] }})</button>
        </div>
    </div>

    <form action="{{ route('admin.registrasi.upload.confirm') }}" method="POST" id="confirmForm">
        @csrf
        <input type="hidden" name="tahun_pelajaran_id" value="{{ $returnTahunId }}">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-table mr-2"></i>Hasil Pencocokan ({{ $preview['summary']['total'] }} baris)</h3>
                <div>
                    <button type="button" class="btn btn-xs btn-default" id="checkAll"><i class="fas fa-check-square"></i> Pilih Semua</button>
                    <button type="button" class="btn btn-xs btn-default" id="uncheckAll"><i class="far fa-square"></i> Hapus Pilihan</button>
                </div>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table table-bordered table-sm preview-table mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th width="40" class="text-center"><i class="fas fa-check"></i></th>
                            <th width="40" class="text-center">#</th>
                            <th>Data Excel</th>
                            <th>Pendaftar Tercocok</th>
                            <th width="250">Pilih Pendaftar</th>
                            <th width="130">Jurusan Final</th>
                            <th class="text-center" width="110">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preview['rows'] as $row)
                            @php
                                $rowClass = match($row['match_status']) {
                                    'matched_exact' => 'row-exact',
                                    'matched_fuzzy' => 'row-fuzzy',
                                    'conflict_jurusan' => 'row-conflict',
                                    default => 'row-unmatched',
                                };
                                $badge = match($row['match_status']) {
                                    'matched_exact' => ['success', 'Cocok Persis'],
                                    'matched_fuzzy' => ['info', 'Mirip'],
                                    'conflict_jurusan' => ['warning', 'Konflik Jurusan'],
                                    default => ['danger', 'Tidak Cocok'],
                                };
                                $defaultChecked = in_array($row['match_status'], ['matched_exact', 'matched_fuzzy', 'conflict_jurusan']);
                                $i = $row['index'];
                            @endphp
                            <tr class="preview-row {{ $rowClass }}" data-status="{{ $row['match_status'] }}">
                                <td class="text-center">
                                    <input type="checkbox" class="row-include" name="rows[{{ $i }}][include]" value="1" {{ $defaultChecked ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">{{ $row['no'] ?: ($i + 1) }}</td>
                                <td>
                                    <strong>{{ $row['nama_excel'] }}</strong><br>
                                    <small>Notes: <code>{{ $row['notes'] ?: '-' }}</code> &middot; Jurusan: <span class="badge badge-light">{{ $row['jurusan_excel'] ?: '-' }}</span></small>
                                    <input type="hidden" name="rows[{{ $i }}][notes]" value="{{ $row['notes'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][nama_excel]" value="{{ $row['nama_excel'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][jurusan_excel]" value="{{ $row['jurusan_excel'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][match_status]" value="{{ $row['match_status'] }}">
                                    <input type="hidden" name="rows[{{ $i }}][match_score]" value="{{ $row['match_score'] }}">
                                </td>
                                <td>
                                    @php $sel = collect($row['candidates'])->firstWhere('id', $row['selected_id']); @endphp
                                    @if($sel)
                                        <strong>{{ $sel['nama_lengkap'] }}</strong>
                                        <span class="badge badge-{{ $badge[0] }}">{{ $row['match_score'] }}%</span><br>
                                        <small>No. Tes: {{ $sel['nomor_tes'] ?: '-' }} &middot; {{ $sel['gelombang'] ?: '-' }} &middot; Jurusan DB: <strong>{{ $sel['pilihan_program'] ?: '-' }}</strong></small>
                                    @else
                                        <span class="text-muted"><i class="fas fa-question-circle"></i> Belum dicocokkan</span>
                                    @endif
                                </td>
                                <td>
                                    <select name="rows[{{ $i }}][calon_siswa_id]" class="form-control form-control-sm cand-select" style="width:100%;">
                                        <option value="">-- Lewati / Tidak dipilih --</option>
                                        @foreach($row['candidates'] as $c)
                                            <option value="{{ $c['id'] }}" {{ $row['selected_id'] === $c['id'] ? 'selected' : '' }}
                                                data-program="{{ $c['pilihan_program'] }}">
                                                {{ $c['nama_lengkap'] }} ({{ $c['nomor_tes'] ?: '-' }}) &middot; {{ $c['pilihan_program'] ?: '-' }} &middot; {{ $c['name_score'] }}%{{ $c['note_match'] ? ' ✓tes' : '' }}{{ ($c['lulus'] ?? false) ? ' [lulus]' : ' [belum lulus]' }}{{ $c['already_registered'] ? ' [terdaftar]' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1"><i class="fas fa-search"></i> Ketik untuk cari semua pendaftar</small>
                                </td>
                                <td>
                                    <input type="text" name="rows[{{ $i }}][jurusan_final]" class="form-control form-control-sm jurusan-final" value="{{ $row['jurusan_final'] }}">
                                    @if($row['jurusan_excel'] && $row['selected_program'] && strcasecmp($row['jurusan_excel'], $row['selected_program']) !== 0)
                                        <small class="d-block text-warning mt-1"><i class="fas fa-exchange-alt"></i> Awal: <strong>{{ $row['selected_program'] }}</strong> &rarr; <strong>{{ $row['jurusan_excel'] }}</strong></small>
                                    @endif
                                </td>
                                <td class="text-center"><span class="badge badge-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                                <td>
                                    @forelse($row['issues'] as $issue)
                                        <small class="d-block text-muted"><i class="fas fa-info-circle"></i> {{ $issue }}</small>
                                    @empty
                                        <small class="text-success"><i class="fas fa-check"></i> OK</small>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data terbaca dari file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <button type="submit" class="btn btn-success btn-lg" id="btnConfirm">
                    <i class="fas fa-save mr-1"></i> Simpan Registrasi (<span id="selectedCount">0</span> dipilih)
                </button>
                <a href="{{ route('admin.registrasi.upload', ['tahun_pelajaran_id' => $returnTahunId]) }}" class="btn btn-danger btn-lg">
                    <i class="fas fa-times-circle mr-1"></i> Batalkan
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Progress Overlay --}}
<div class="progress-overlay" id="progressOverlay">
    <div class="progress-box">
        <h4 class="mb-3"><i class="fas fa-cog fa-spin mr-2"></i>Menyimpan Registrasi...</h4>
        <p class="text-muted mb-3">Memproses data terpilih</p>
        <div class="progress mb-2">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressBar" role="progressbar" style="width:0%">0%</div>
        </div>
        <small class="text-muted" id="progressText">Mempersiapkan...</small>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    function updateCount() {
        $('#selectedCount').text($('.row-include:checked').length);
    }
    updateCount();

    $('.filter-btn').on('click', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        var f = $(this).data('filter');
        if (f === 'all') { $('.preview-row').show(); }
        else { $('.preview-row').hide(); $('.preview-row[data-status="' + f + '"]').show(); }
    });

    $('#checkAll').on('click', function () { $('.preview-row:visible .row-include').prop('checked', true); updateCount(); });
    $('#uncheckAll').on('click', function () { $('.preview-row:visible .row-include').prop('checked', false); updateCount(); });
    $(document).on('change', '.row-include', updateCount);

    // Select2 AJAX: cari di SELURUH pendaftar lulus tahun aktif
    $('.cand-select').select2({
        width: '100%',
        placeholder: '-- Lewati / Tidak dipilih --',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.registrasi.search-candidates') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, tahun_pelajaran_id: '{{ $returnTahunId }}' };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Saat memilih kandidat, centang baris & isi jurusan final bila kosong
    $(document).on('select2:select', '.cand-select', function (e) {
        var $row = $(this).closest('tr');
        $row.find('.row-include').prop('checked', true);
        var data = e.params.data || {};
        var $final = $row.find('.jurusan-final');
        if (!$final.val() && data.pilihan_program) {
            $final.val(data.pilihan_program);
        }
        updateCount();
    });
    $(document).on('change', '.cand-select', updateCount);

    $('#confirmForm').on('submit', function () {
        if ($('.row-include:checked').length === 0) {
            alert('Tidak ada baris yang dipilih untuk disimpan.');
            return false;
        }
        $('#btnConfirm').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        $('#progressOverlay').addClass('show');

        var steps = [
            { pct: 15, text: 'Memvalidasi data...' },
            { pct: 40, text: 'Mencocokkan pendaftar...' },
            { pct: 65, text: 'Menyimpan registrasi...' },
            { pct: 85, text: 'Memperbarui jurusan...' },
            { pct: 96, text: 'Finalisasi...' },
        ];
        var i = 0;
        var iv = setInterval(function () {
            if (i < steps.length) {
                $('#progressBar').css('width', steps[i].pct + '%').text(steps[i].pct + '%');
                $('#progressText').text(steps[i].text);
                i++;
            } else { clearInterval(iv); }
        }, 700);
        return true;
    });
});
</script>
@stop
