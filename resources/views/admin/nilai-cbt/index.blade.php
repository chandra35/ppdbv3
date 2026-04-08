@extends('adminlte::page')

@section('title', 'Nilai CBT')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
    .cbt-scan-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(5px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 1rem;
    }
    .cbt-scan-overlay.show {
        display: flex;
    }
    .cbt-scan-card {
        width: min(680px, 100%);
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 28px 90px rgba(15, 23, 42, .32);
    }
    .cbt-scan-head {
        padding: 1.1rem 1.35rem;
        background: linear-gradient(135deg, #0f766e 0%, #2563eb 55%, #1d4ed8 100%);
        color: #fff;
    }
    .cbt-scan-body {
        padding: 1.35rem;
    }
    .cbt-scan-steps {
        display: grid;
        gap: .9rem;
    }
    .cbt-scan-step {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: .8rem .95rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }
    .cbt-scan-dot {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
        font-weight: 700;
    }
    .cbt-scan-progress {
        height: 12px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .cbt-scan-progress > span {
        display: block;
        width: 45%;
        height: 100%;
        background: linear-gradient(90deg, #0f766e, #2563eb, #0f766e);
        background-size: 220% 100%;
        animation: cbtScanBar 1.5s linear infinite, cbtScanWidth 1.8s ease-in-out infinite;
    }
    .cbt-scan-pulse {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 rgba(34, 197, 94, 0.5);
        animation: cbtScanPulse 1.6s infinite;
    }
    @keyframes cbtScanBar {
        0% { background-position: 0% 0; }
        100% { background-position: 220% 0; }
    }
    @keyframes cbtScanWidth {
        0% { width: 28%; }
        50% { width: 86%; }
        100% { width: 38%; }
    }
    @keyframes cbtScanPulse {
        0% { transform: scale(.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, .45); }
        70% { transform: scale(1); box-shadow: 0 0 0 12px rgba(34, 197, 94, 0); }
        100% { transform: scale(.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .cbt-scan-statusline {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        margin-top:1rem;
        font-size:.88rem;
        color:#475569;
    }
    .cbt-scan-badge {
        display:inline-flex;
        align-items:center;
        gap:.4rem;
        padding:.35rem .7rem;
        border-radius:999px;
        background:#ecfeff;
        color:#0f766e;
        font-weight:700;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-laptop mr-2"></i>Nilai CBT
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Nilai CBT</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Nilai CBT sedang memakai konteks:
        Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
        Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
        Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
    </div>
    <!-- Filter -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-cbt.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control" onchange="this.form.submit()">
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ $selectedTahunId == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jalur</label>
                            <select name="jalur_id" class="form-control" onchange="this.form.submit()">
                                <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>-- Semua Jalur --</option>
                                @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ (string) $selectedJalurIdInput === (string) $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Gelombang</label>
                            <select name="gelombang_id" class="form-control" onchange="this.form.submit()">
                                <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>-- Semua Gelombang --</option>
                                @foreach($gelombangs as $gelombang)
                                <option value="{{ $gelombang->id }}" {{ (string) $selectedGelombangIdInput === (string) $gelombang->id ? 'selected' : '' }}>
                                    {{ $gelombang->nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $data->count() }}</h3>
                    <p>Total Peserta CBT</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->avg('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Keseluruhan</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->max('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Tertinggi</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $data->count() > 0 ? number_format($data->min('rata_rata') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Terendah</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    <!-- Progress Import per Mapel -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Progress Import per Mapel</h3>
            <div class="card-tools">
                <a href="{{ route('admin.nilai-cbt.moodle-scan', request()->query()) }}" class="btn btn-outline-info btn-sm mr-2" id="btnScanMoodle" data-scan-url="{{ route('admin.nilai-cbt.moodle-scan', request()->query()) }}">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Scan dari Moodle
                </a>
                <a href="{{ route('admin.nilai-cbt.upload') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-upload mr-1"></i> Upload Nilai CBT
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($data->isEmpty())
                <p class="text-muted text-center mb-0">Belum ada data. Upload nilai per mapel untuk memulai.</p>
            @else
                <div class="row">
                    @php
                        $progressColors = [
                            'nilai_mtk' => 'primary',
                            'nilai_ipa' => 'success',
                            'nilai_ips' => 'warning',
                            'nilai_bahasa_inggris' => 'info',
                        ];
                        $progressIcons = [
                            'nilai_mtk' => 'fa-calculator',
                            'nilai_ipa' => 'fa-flask',
                            'nilai_ips' => 'fa-globe-asia',
                            'nilai_bahasa_inggris' => 'fa-language',
                        ];
                    @endphp
                    @foreach($mapelProgress as $field => $mp)
                        <div class="col-md-3 col-6 mb-3">
                            <div class="info-box mb-0">
                                <span class="info-box-icon bg-{{ $progressColors[$field] ?? 'secondary' }}">
                                    <i class="fas {{ $progressIcons[$field] ?? 'fa-book' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $mp['label'] }}</span>
                                    <span class="info-box-number">{{ $mp['filled'] }} / {{ $mp['total'] }}</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-{{ $progressColors[$field] ?? 'secondary' }}"
                                             style="width: {{ $mp['percent'] }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        @if($mp['percent'] == 100)
                                            <i class="fas fa-check-circle text-success"></i> Lengkap
                                        @elseif($mp['percent'] > 0)
                                            {{ $mp['percent'] }}% terisi
                                        @else
                                            <a href="{{ route('admin.nilai-cbt.upload', ['mapel' => $field]) }}" class="text-primary">
                                                <i class="fas fa-upload"></i> Upload
                                            </a>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data Nilai CBT</h3>
        </div>
        <div class="card-body">
            @if($data->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada data nilai CBT. <a href="{{ route('admin.nilai-cbt.moodle-scan', request()->query()) }}">Scan dari Moodle</a> atau <a href="{{ route('admin.nilai-cbt.upload') }}">upload sekarang</a>.</p>
                </div>
            @else
                <table id="cbtTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">No</th>
                            <th>NISN</th>
                            <th>No. Tes</th>
                            <th>Nama Peserta</th>
                            <th class="text-center">MTK</th>
                            <th class="text-center">IPA</th>
                            <th class="text-center">IPS</th>
                            <th class="text-center">B. Inggris</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Rata-rata</th>
                            <th class="text-center" width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $cbt)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><code>{{ $cbt->calonSiswa->nisn ?? '-' }}</code></td>
                                <td>{{ $cbt->calonSiswa->nomor_tes ?? '-' }}</td>
                                <td>
                                    <strong>{{ $cbt->calonSiswa->nama_lengkap ?? '-' }}</strong>
                                    @if($cbt->calonSiswa->jenis_kelamin == 'L')
                                        <i class="fas fa-mars text-primary"></i>
                                    @else
                                        <i class="fas fa-venus text-danger"></i>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_mtk ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_ipa ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_ips ?? '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $cbt->nilai_bahasa_inggris ?? '-' }}</td>
                                <td class="text-center">{{ number_format($cbt->total_nilai ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary" style="font-size: 1rem;">
                                        {{ number_format($cbt->rata_rata ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.nilai-cbt.destroy', $cbt) }}" method="POST"
                                          onsubmit="return confirm('Hapus data CBT ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<div class="cbt-scan-overlay" id="cbtScanOverlay" aria-hidden="true">
    <div class="cbt-scan-card">
        <div class="cbt-scan-head">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1"><i class="fas fa-cloud-download-alt mr-2"></i>Memindai Nilai CBT dari Moodle</h4>
                    <div class="small text-white-50">Mohon tunggu, sistem sedang menyiapkan preview hasil scan sesuai filter aktif.</div>
                </div>
                <div class="cbt-scan-pulse"></div>
            </div>
        </div>
        <div class="cbt-scan-body">
            <div class="cbt-scan-progress mb-4">
                <span></span>
            </div>
            <div class="cbt-scan-statusline">
                <span id="cbtScanLiveText">Menyiapkan koneksi ke Moodle dan membaca mapping aktif...</span>
                <span class="cbt-scan-badge"><i class="fas fa-bolt"></i><span id="cbtScanPercentText">0%</span></span>
            </div>
            <div class="cbt-scan-steps">
                <div class="cbt-scan-step">
                    <span class="cbt-scan-dot">1</span>
                    <div>
                        <div class="font-weight-bold">Membaca filter PPDB</div>
                        <small class="text-muted">Tahun, jalur, dan gelombang aktif dipakai sebagai konteks scan.</small>
                    </div>
                </div>
                <div class="cbt-scan-step">
                    <span class="cbt-scan-dot">2</span>
                    <div>
                        <div class="font-weight-bold">Mencocokkan user Moodle</div>
                        <small class="text-muted">Sistem membandingkan pendaftar dengan akun Moodle berdasarkan username NISN.</small>
                    </div>
                </div>
                <div class="cbt-scan-step">
                    <span class="cbt-scan-dot">3</span>
                    <div>
                        <div class="font-weight-bold">Mengambil quiz dan nilai</div>
                        <small class="text-muted">Semua quiz dari course yang dipetakan dibaca untuk membentuk preview nilai CBT.</small>
                    </div>
                </div>
                <div class="cbt-scan-step">
                    <span class="cbt-scan-dot">4</span>
                    <div>
                        <div class="font-weight-bold">Menyiapkan preview sebelum simpan</div>
                        <small class="text-muted">Hasil scan tidak langsung disimpan. Anda tetap meninjau preview terlebih dahulu.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    let cbtScanTicker = null;
    const cbtScanMessages = [
        'Menyiapkan koneksi ke Moodle dan membaca mapping aktif...',
        'Mencocokkan pendaftar dengan username Moodle berbasis NISN...',
        'Mengambil daftar quiz dan attempt yang relevan...',
        'Menyusun preview nilai CBT sebelum proses simpan...'
    ];

    function startCbtScanFeedback() {
        $('#cbtScanOverlay').addClass('show');
        let progress = 7;
        let messageIndex = 0;
        $('#cbtScanPercentText').text(progress + '%');
        $('#cbtScanLiveText').text(cbtScanMessages[0]);
        if (cbtScanTicker) {
            clearInterval(cbtScanTicker);
        }
        cbtScanTicker = setInterval(function () {
            progress = Math.min(progress + Math.floor(Math.random() * 11) + 5, 92);
            messageIndex = (messageIndex + 1) % cbtScanMessages.length;
            $('#cbtScanPercentText').text(progress + '%');
            $('#cbtScanLiveText').text(cbtScanMessages[messageIndex]);
        }, 700);
    }

    $('#btnScanMoodle').on('click', function (event) {
        event.preventDefault();
        const targetUrl = $(this).data('scan-url') || this.href;
        startCbtScanFeedback();
        setTimeout(function () {
            window.location.href = targetUrl;
        }, 550);
    });

    @if($data->isNotEmpty())
    $('#cbtTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                title: 'Nilai CBT PPDB',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                className: 'btn btn-info btn-sm',
                title: 'Nilai CBT PPDB',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] }
            }
        ],
        order: [[9, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        }
    });
    @endif
});
</script>
@stop
