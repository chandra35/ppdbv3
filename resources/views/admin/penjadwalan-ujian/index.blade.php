@extends('adminlte::page')

@section('title', 'Penjadwalan Ujian CBT & Wawancara')

@section('css')
<style>
    .config-card { border-left: 4px solid #007bff; }
    .config-card.cbt { border-left-color: #28a745; }
    .config-card.wawancara { border-left-color: #ffc107; }
    .preview-card { border: 2px dashed #dee2e6; }
    .preview-card.has-data { border: 2px solid #28a745; }
    .sesi-row { transition: all 0.2s; }
    .sesi-row:hover { background-color: #f8f9fa; }
    .grup-badge { font-size: 0.7rem; padding: 2px 6px; }
    .capacity-warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
    .stats-mini { font-size: 0.85rem; }
    .stats-mini .value { font-size: 1.2rem; font-weight: bold; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-calendar-alt"></i> Penjadwalan Ujian</h1>
        <p class="text-muted mb-0">Atur jadwal CBT & Wawancara secara paralel</p>
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
            <a href="{{ route('admin.penjadwalan-ujian.list') }}" class="btn btn-outline-info btn-sm ml-2">
                <i class="fas fa-list"></i> Lihat Jadwal
            </a>
        </form>
    </div>
</div>
@stop

@section('content')
{{-- Alerts --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
</div>
@endif

{{-- Warnings --}}
@if(isset($warnings) && count($warnings) > 0)
<div class="alert alert-warning capacity-warning">
    <h6><i class="fas fa-exclamation-triangle mr-2"></i>Peringatan Kapasitas</h6>
    <ul class="mb-0">
        @foreach($warnings as $warning)
        <li>{{ $warning }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Existing Jadwal Info --}}
@if(isset($existingJadwal) && $existingJadwal && $existingJadwal->status === 'locked')
<div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>
    Sudah ada jadwal terkunci untuk tahun pelajaran ini: 
    <strong>{{ $existingJadwal->tanggal_ujian->format('d M Y') }}</strong>
    ({{ $existingJadwal->total_peserta }} peserta, {{ $existingJadwal->total_sesi }} sesi)
    <a href="{{ route('admin.penjadwalan-ujian.show', $existingJadwal) }}" class="btn btn-sm btn-info ml-2">
        <i class="fas fa-eye"></i> Lihat
    </a>
</div>
@endif

{{-- Statistics --}}
<div class="row mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-info mb-2">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Peserta</span>
                <span class="info-box-number">{{ number_format($totalPeserta) }}</span>
            </div>
        </div>
    </div>
    @if(isset($schedule))
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-success mb-2">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jumlah Sesi</span>
                <span class="info-box-number">{{ count($schedule['sesi']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-warning mb-2">
            <span class="info-box-icon"><i class="fas fa-door-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Ruang</span>
                <span class="info-box-number">{{ ($settings['jumlah_ruang_cbt'] ?? 0) + ($settings['jumlah_ruang_wawancara'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="info-box bg-olive mb-2">
            <span class="info-box-icon"><i class="fas fa-flag-checkered"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Estimasi Selesai</span>
                <span class="info-box-number">{{ $schedule['estimasi_selesai'] ?? '-' }}</span>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Configuration Form --}}
<form method="POST" action="{{ route('admin.penjadwalan-ujian.preview') }}" id="configForm">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif?->id }}">

    <div class="row">
        {{-- Left: CBT Config --}}
        <div class="col-lg-4">
            <div class="card config-card cbt">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title"><i class="fas fa-desktop mr-2"></i>Konfigurasi CBT</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Jumlah Ruang CBT <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_ruang_cbt" class="form-control" 
                               value="{{ $settings['jumlah_ruang_cbt'] ?? 3 }}" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas per Ruang <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas_cbt" class="form-control" 
                               value="{{ $settings['kapasitas_cbt'] ?? 30 }}" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi CBT (menit) <span class="text-danger">*</span></label>
                        <input type="number" name="durasi_cbt" class="form-control" 
                               value="{{ $settings['durasi_cbt'] ?? 90 }}" min="15" max="240" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Prefix Nama Ruang</label>
                        <input type="text" name="prefix_ruang_cbt" class="form-control" 
                               value="{{ $settings['prefix_ruang_cbt'] ?? 'Ruang CBT' }}" maxlength="50">
                    </div>
                    <hr>
                    <div class="stats-mini text-center">
                        <span class="text-muted">Total Kapasitas:</span>
                        <span class="value text-success" id="totalCbt">
                            {{ ($settings['jumlah_ruang_cbt'] ?? 3) * ($settings['kapasitas_cbt'] ?? 30) }}
                        </span>
                        <span class="text-muted">peserta/sesi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Center: Wawancara Config --}}
        <div class="col-lg-4">
            <div class="card config-card wawancara">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-microphone mr-2"></i>Konfigurasi Wawancara</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Jumlah Ruang Wawancara <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_ruang_wawancara" class="form-control" 
                               value="{{ $settings['jumlah_ruang_wawancara'] ?? 4 }}" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas per Ruang <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas_wawancara" class="form-control" 
                               value="{{ $settings['kapasitas_wawancara'] ?? 15 }}" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi Wawancara (menit) <span class="text-danger">*</span></label>
                        <input type="number" name="durasi_wawancara" class="form-control" 
                               value="{{ $settings['durasi_wawancara'] ?? 60 }}" min="15" max="240" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Prefix Nama Ruang</label>
                        <input type="text" name="prefix_ruang_wawancara" class="form-control" 
                               value="{{ $settings['prefix_ruang_wawancara'] ?? 'Ruang Wawancara' }}" maxlength="50">
                    </div>
                    <hr>
                    <div class="stats-mini text-center">
                        <span class="text-muted">Total Kapasitas:</span>
                        <span class="value text-warning" id="totalWawancara">
                            {{ ($settings['jumlah_ruang_wawancara'] ?? 4) * ($settings['kapasitas_wawancara'] ?? 15) }}
                        </span>
                        <span class="text-muted">peserta/sesi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Time & Filter Config --}}
        <div class="col-lg-4">
            <div class="card config-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Waktu & Filter</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tanggal Ujian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_ujian" class="form-control" 
                               value="{{ $settings['tanggal_ujian'] ?? now()->addDays(7)->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control" 
                               value="{{ $settings['jam_mulai'] ?? '08:00' }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jeda Antar Sesi (menit) <span class="text-danger">*</span></label>
                        <input type="number" name="jeda_sesi" class="form-control" 
                               value="{{ $settings['jeda_sesi'] ?? 30 }}" min="5" max="120" required>
                    </div>
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
                    <div class="form-group mb-0">
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
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-magic mr-2"></i>Generate Preview Jadwal
            </button>
        </div>
    </div>
</form>

{{-- Preview Section --}}
@if(isset($schedule))
<div class="card preview-card has-data">
    <div class="card-header bg-success text-white">
        <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview Hasil Generate</h3>
        <div class="card-tools">
            <form method="POST" action="{{ route('admin.penjadwalan-ujian.store') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Simpan dan kunci jadwal ini?')">
                    <i class="fas fa-lock mr-1"></i>Simpan & Kunci Jadwal
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        {{-- Summary --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-calculator mr-2"></i>Ringkasan Perhitungan</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Total Peserta</td>
                                <td class="text-right"><strong>{{ $totalPeserta }}</strong></td>
                            </tr>
                            <tr>
                                <td>Kapasitas CBT/Sesi</td>
                                <td class="text-right"><strong class="text-success">{{ $kapasitasCbt }}</strong> ({{ $settings['jumlah_ruang_cbt'] }} × {{ $settings['kapasitas_cbt'] }})</td>
                            </tr>
                            <tr>
                                <td>Kapasitas Wawancara/Sesi</td>
                                <td class="text-right"><strong class="text-warning">{{ $kapasitasWawancara }}</strong> ({{ $settings['jumlah_ruang_wawancara'] }} × {{ $settings['kapasitas_wawancara'] }})</td>
                            </tr>
                            <tr>
                                <td>Kapasitas Paralel</td>
                                <td class="text-right"><strong class="text-primary">{{ $kapasitasParalel }}</strong></td>
                            </tr>
                            <tr>
                                <td>Jumlah Sesi</td>
                                <td class="text-right"><strong>{{ count($schedule['sesi']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Estimasi Selesai</td>
                                <td class="text-right"><strong>{{ $schedule['estimasi_selesai'] }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle mr-2"></i>Cara Kerja</h6>
                        <ul class="mb-0 pl-3">
                            <li><strong>Grup A</strong>: CBT dulu → lalu Wawancara</li>
                            <li><strong>Grup B</strong>: Wawancara dulu → lalu CBT</li>
                            <li>Setiap peserta mengikuti <strong>2 sesi</strong> (CBT + Wawancara)</li>
                            <li>Distribusi berdasarkan <strong>Nomor Tes</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sesi Table --}}
        <h5><i class="fas fa-table mr-2"></i>Jadwal Sesi</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="80">Sesi</th>
                        <th width="150">Waktu</th>
                        <th class="bg-success text-white">CBT</th>
                        <th class="bg-warning">Wawancara</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule['sesi'] as $nomorSesi => $sesi)
                    <tr class="sesi-row">
                        <td class="text-center"><strong>Sesi {{ $nomorSesi }}</strong></td>
                        <td class="text-center">{{ $sesi['waktu_mulai'] }} - {{ $sesi['waktu_selesai'] }}</td>
                        <td>
                            <span class="badge badge-success">{{ $sesi['cbt']['jumlah'] }} peserta</span>
                            <br><small class="text-muted">No Tes: {{ $sesi['cbt']['range'] }}</small>
                        </td>
                        <td>
                            <span class="badge badge-warning text-dark">{{ $sesi['wawancara']['jumlah'] }} peserta</span>
                            <br><small class="text-muted">No Tes: {{ $sesi['wawancara']['range'] }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Gelombang Detail --}}
        <h5 class="mt-4"><i class="fas fa-layer-group mr-2"></i>Detail per Gelombang</h5>
        <div class="row">
            @foreach($schedule['gelombang'] as $nomorGelombang => $gelombang)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header py-2">
                        <strong>Gelombang {{ $nomorGelombang }}</strong>
                    </div>
                    <div class="card-body py-2">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td><span class="badge badge-primary grup-badge">Grup A</span></td>
                                <td>{{ $gelombang['grup_a'] }} peserta</td>
                                <td><small>CBT → Wawancara</small></td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-secondary grup-badge">Grup B</span></td>
                                <td>{{ $gelombang['grup_b'] }} peserta</td>
                                <td><small>Wawancara → CBT</small></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer">
        <form method="POST" action="{{ route('admin.penjadwalan-ujian.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.penjadwalan-ujian.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Batal
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('configForm').submit()">
                        <i class="fas fa-sync-alt mr-1"></i>Generate Ulang
                    </button>
                </div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Simpan dan kunci jadwal? Jadwal yang sudah dikunci tidak dapat diubah.')">
                        <i class="fas fa-lock mr-2"></i>Simpan & Kunci Jadwal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-calculate capacities
    function updateCapacities() {
        var cbtRuang = parseInt($('input[name="jumlah_ruang_cbt"]').val()) || 0;
        var cbtKapasitas = parseInt($('input[name="kapasitas_cbt"]').val()) || 0;
        var wawancaraRuang = parseInt($('input[name="jumlah_ruang_wawancara"]').val()) || 0;
        var wawancaraKapasitas = parseInt($('input[name="kapasitas_wawancara"]').val()) || 0;

        $('#totalCbt').text(cbtRuang * cbtKapasitas);
        $('#totalWawancara').text(wawancaraRuang * wawancaraKapasitas);
    }

    $('input[name="jumlah_ruang_cbt"], input[name="kapasitas_cbt"], input[name="jumlah_ruang_wawancara"], input[name="kapasitas_wawancara"]').on('input', updateCapacities);
});
</script>
@endsection
