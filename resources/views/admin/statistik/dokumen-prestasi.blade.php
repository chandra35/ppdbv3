@extends('adminlte::page')

@section('title', 'Statistik Dokumen & Prestasi')

@section('css')
<style>
    .chart-container { position: relative; height: 300px; }
    .stat-card { border-radius: 10px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 15px; top: 15px; }
    .stat-number { font-size: 2rem; font-weight: bold; }
    .stat-label { font-size: 0.9rem; color: #6c757d; }
    .prestasi-badge { font-size: 12px; padding: 5px 10px; margin: 2px; display: inline-block; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-certificate"></i> Statistik Dokumen & Prestasi</h1>
    </div>
    <div class="col-sm-6">
        <div class="d-flex justify-content-sm-end align-items-center" style="gap: 10px;">
            <a href="{{ route('admin.statistik.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <form class="form-inline">
                <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}
                    </option>
                    @endforeach
                </select>
                <select name="jalur_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                    <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                    @foreach($jalurList as $jalur)
                    <option value="{{ $jalur->id }}" {{ (string) $selectedJalurIdInput === (string) $jalur->id ? 'selected' : '' }}>
                        {{ $jalur->nama }}
                    </option>
                    @endforeach
                </select>
                <select name="gelombang_id" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                    <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                    @foreach($gelombangList as $gelombang)
                    <option value="{{ $gelombang->id }}" {{ (string) $selectedGelombangIdInput === (string) $gelombang->id ? 'selected' : '' }}>
                        {{ $gelombang->nama }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="alert alert-info">
    Statistik dokumen dan prestasi sedang menggunakan konteks:
    Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
    Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
    Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
</div>

@php
    // Prepare stats from byJenisDokumen
    $dokumenStats = [];
    foreach($byJenisDokumen as $dok) {
        $dokumenStats[$dok->jenis_dokumen] = $dok->total;
    }
    $totalDokumen = $byJenisDokumen->sum('total');
    $prestasiCards = [];
    $cardThemes = [
        'bg-gradient-warning',
        'bg-gradient-info',
        'bg-gradient-success',
        'bg-gradient-danger',
        'bg-gradient-primary',
        'bg-gradient-secondary',
        'bg-gradient-dark',
    ];
    $cardIcons = [
        'sertifikat_prestasi' => 'fas fa-trophy',
        'piagam' => 'fas fa-award',
        'sertifikat_ksm' => 'fas fa-medal',
        'piagam_ksm' => 'fas fa-scroll',
        'sertifikat_osn' => 'fas fa-atom',
        'piagam_osn' => 'fas fa-star',
        'sertifikat_olimpiade' => 'fas fa-medal',
        'piagam_olimpiade' => 'fas fa-certificate',
        'sertifikat_tahfidz' => 'fas fa-quran',
        'piagam_tahfidz' => 'fas fa-book-open',
    ];

    foreach ($dokumenTambahanTypes as $jenis => $label) {
        $prestasiCards[] = [
            'jenis' => $jenis,
            'label' => $label,
            'total' => $dokumenStats[$jenis] ?? 0,
            'theme' => $cardThemes[count($prestasiCards) % count($cardThemes)],
            'icon' => $cardIcons[$jenis] ?? 'fas fa-file-alt',
        ];
    }
@endphp

{{-- Statistik Dokumen Prestasi --}}
<div class="row">
    @foreach($prestasiCards as $card)
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card {{ $card['theme'] }} text-white">
            <div class="card-body">
                <i class="{{ $card['icon'] }} stat-icon"></i>
                <div class="stat-number">{{ $card['total'] }}</div>
                <div class="stat-label text-white">{{ $card['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card bg-gradient-secondary text-white">
            <div class="card-body">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-number">{{ $pendaftarDenganPrestasi }}</div>
                <div class="stat-label text-white">Pendaftar dgn Prestasi</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card stat-card bg-gradient-dark text-white">
            <div class="card-body">
                <i class="fas fa-folder-open stat-icon"></i>
                <div class="stat-number">{{ $totalDokumen }}</div>
                <div class="stat-label text-white">Total Dokumen</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Perbandingan Jenis Dokumen --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Perbandingan Jenis Dokumen</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dokumenPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Dokumen per Jenis Bar --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Jumlah per Jenis Dokumen</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dokumenBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pendaftar dengan Prestasi --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-star"></i> Pendaftar dengan Dokumen Prestasi</h3>
        <div class="card-tools">
            <span class="badge badge-primary">{{ $pendaftarDenganPrestasi }} dari {{ $totalPendaftar }} pendaftar</span>
            <span class="badge badge-success">{{ $totalPendaftar > 0 ? round(($pendaftarDenganPrestasi / $totalPendaftar) * 100, 1) : 0 }}%</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="progress-group">
                    <span class="progress-text">Pendaftar dengan Prestasi</span>
                    <span class="float-right"><b>{{ $pendaftarDenganPrestasi }}</b>/{{ $totalPendaftar }}</span>
                    <div class="progress">
                        @php $persen = $totalPendaftar > 0 ? ($pendaftarDenganPrestasi / $totalPendaftar) * 100 : 0; @endphp
                        <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="progress-group">
                    <span class="progress-text">Pendaftar Tanpa Prestasi</span>
                    <span class="float-right"><b>{{ $totalPendaftar - $pendaftarDenganPrestasi }}</b>/{{ $totalPendaftar }}</span>
                    <div class="progress">
                        @php $persen = $totalPendaftar > 0 ? (($totalPendaftar - $pendaftarDenganPrestasi) / $totalPendaftar) * 100 : 0; @endphp
                        <div class="progress-bar bg-secondary" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Pendaftar dengan Prestasi --}}
@if($detailPrestasi && $detailPrestasi->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Pendaftar dengan Dokumen Prestasi</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Nama Pendaftar</th>
                    <th>Asal Sekolah</th>
                    <th>NPSN/NSM</th>
                    <th class="text-center">Jumlah Dokumen</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detailPrestasi as $i => $item)
                <tr>
                    <td>{{ ($detailPrestasi->currentPage() - 1) * $detailPrestasi->perPage() + $i + 1 }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->nama_sekolah_asal ?? '-' }}</td>
                    <td>
                        @if($item->npsn_asal_sekolah)
                            <code class="text-primary">{{ $item->npsn_asal_sekolah }}</code>
                        @endif
                        @if($item->nsm_asal_sekolah)
                            @if($item->npsn_asal_sekolah) / @endif
                            <code class="text-info">{{ $item->nsm_asal_sekolah }}</code>
                        @endif
                        @if(!$item->npsn_asal_sekolah && !$item->nsm_asal_sekolah)
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $dokumenData = $item->dokumen->map(function($d) {
                                return [
                                    'jenis' => $d->jenis_dokumen,
                                    'keterangan' => $d->keterangan,
                                    'file' => $d->file_url,
                                    'status' => $d->status
                                ];
                            });
                        @endphp
                        <a href="javascript:void(0)" class="badge badge-info btn-show-dokumen" 
                           data-id="{{ $item->id }}"
                           data-nama="{{ $item->nama_lengkap }}"
                           data-dokumen="{{ $dokumenData->toJson() }}">
                            <i class="fas fa-folder-open mr-1"></i>{{ $item->dokumen->count() }} dokumen
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.pendaftar.show', $item->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($detailPrestasi->hasPages())
    <div class="card-footer clearfix">
        {{ $detailPrestasi->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@endif

{{-- Modal Detail Dokumen --}}
<div class="modal fade" id="modalDokumen" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-folder-open mr-2"></i>Dokumen Prestasi: <span id="modalNamaPendaftar"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Document Viewer --}}
                <div id="dokumenViewer">
                    <div class="text-center mb-3">
                        <span class="badge badge-primary" id="dokumenCounter">1 / 1</span>
                    </div>
                    
                    {{-- Preview Area --}}
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span id="dokumenJenis" class="font-weight-bold"></span>
                            <span id="dokumenStatus"></span>
                        </div>
                        <div class="card-body text-center" id="dokumenPreview" style="min-height: 300px; background: #f8f9fa;">
                            {{-- Preview image/pdf here --}}
                        </div>
                        <div class="card-footer">
                            <strong>Keterangan:</strong>
                            <p id="dokumenKeterangan" class="mb-0 text-muted">-</p>
                        </div>
                    </div>
                </div>
                
                {{-- Empty State --}}
                <div id="dokumenEmpty" class="text-center text-muted py-5" style="display: none;">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>Tidak ada dokumen prestasi</p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-primary" id="btnPrevDok" onclick="navigateDokumen(-1)">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnNextDok" onclick="navigateDokumen(1)">
                        Selanjutnya <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div>
                    <a href="#" id="btnOpenFile" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Buka File
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    var byJenisDokumen = @json($byJenisDokumen);
    var labels = byJenisDokumen.map(d => d.label);
    var data = byJenisDokumen.map(d => d.total);
    var colors = ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#007bff', '#6c757d', '#343a40', '#20c997', '#6610f2', '#fd7e14'];
    
    // Pie Chart
    new Chart(document.getElementById('dokumenPieChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
    
    // Bar Chart
    new Chart(document.getElementById('dokumenBarChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah',
                data: data,
                backgroundColor: colors,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    
    // Dokumen viewer dalam modal
    var currentDokumen = [];
    var currentDokIndex = 0;
    
    var jenisLabels = @json($dokumenTambahanTypes);
    
    var statusBadge = {
        'pending': '<span class="badge badge-warning">Pending</span>',
        'verified': '<span class="badge badge-success">Verified</span>',
        'rejected': '<span class="badge badge-danger">Ditolak</span>',
        'revision': '<span class="badge badge-info">Revisi</span>'
    };
    
    function showDokumen(index) {
        if (!currentDokumen || currentDokumen.length === 0) {
            $('#dokumenViewer').hide();
            $('#dokumenEmpty').show();
            $('#btnPrevDok, #btnNextDok, #btnOpenFile').hide();
            return;
        }
        
        $('#dokumenViewer').show();
        $('#dokumenEmpty').hide();
        
        if (index < 0) index = 0;
        if (index >= currentDokumen.length) index = currentDokumen.length - 1;
        currentDokIndex = index;
        
        var dok = currentDokumen[index];
        var jenisLabel = jenisLabels[dok.jenis] || dok.jenis;
        var kategoriLabel = '-';
        if (['sertifikat_prestasi', 'piagam', 'sertifikat_ksm', 'piagam_ksm', 'sertifikat_osn', 'piagam_osn', 'sertifikat_olimpiade', 'piagam_olimpiade'].includes(dok.jenis)) {
            kategoriLabel = 'Prestasi & Akademik';
        } else if (['sertifikat_tahfidz', 'piagam_tahfidz'].includes(dok.jenis)) {
            kategoriLabel = 'Keagamaan';
        }
        var status = statusBadge[dok.status] || '<span class="badge badge-secondary">' + (dok.status || '-') + '</span>';
        
        // Update counter
        $('#dokumenCounter').text((index + 1) + ' / ' + currentDokumen.length);
        
        // Update info
        $('#dokumenJenis').html('<i class="fas fa-file-alt mr-2"></i>' + jenisLabel + ' <span class="badge badge-light border ml-2" style="font-size:11px;">' + kategoriLabel + '</span>');
        $('#dokumenStatus').html(status);
        $('#dokumenKeterangan').text(dok.keterangan || '-');
        
        // Preview area
        var preview = $('#dokumenPreview');
        preview.empty();
        
        if (dok.file) {
            var ext = dok.file.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                preview.html('<img src="' + dok.file + '" class="img-fluid" style="max-height: 400px; cursor: pointer;" onclick="window.open(\'' + dok.file + '\', \'_blank\')">');
            } else if (ext === 'pdf') {
                preview.html('<embed src="' + dok.file + '" type="application/pdf" width="100%" height="400px">');
            } else {
                preview.html('<div class="py-5"><i class="fas fa-file fa-3x text-muted mb-3"></i><p>File: ' + dok.file.split('/').pop() + '</p></div>');
            }
            $('#btnOpenFile').attr('href', dok.file).show();
        } else {
            preview.html('<div class="py-5 text-muted"><i class="fas fa-image fa-3x mb-3"></i><p>Tidak ada file</p></div>');
            $('#btnOpenFile').hide();
        }
        
        // Update navigation buttons
        $('#btnPrevDok').prop('disabled', index <= 0).toggle(currentDokumen.length > 1);
        $('#btnNextDok').prop('disabled', index >= currentDokumen.length - 1).toggle(currentDokumen.length > 1);
    }
    
    function navigateDokumen(direction) {
        showDokumen(currentDokIndex + direction);
    }
    
    // Modal Show Dokumen - klik dari tabel
    $(document).on('click', '.btn-show-dokumen', function() {
        var nama = $(this).data('nama');
        currentDokumen = $(this).data('dokumen') || [];
        currentDokIndex = 0;
        
        $('#modalNamaPendaftar').text(nama);
        showDokumen(0);
        $('#modalDokumen').modal('show');
    });
    
    // Keyboard navigation
    $(document).on('keydown', function(e) {
        if ($('#modalDokumen').hasClass('show')) {
            if (e.key === 'ArrowLeft') {
                navigateDokumen(-1);
            } else if (e.key === 'ArrowRight') {
                navigateDokumen(1);
            }
        }
    });
</script>
@stop
