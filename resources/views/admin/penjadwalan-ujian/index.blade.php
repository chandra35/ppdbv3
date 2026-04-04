@extends('adminlte::page')

@section('title', 'Penjadwalan Ujian CBT & TBQ')

@section('css')
<style>
    .config-card { border-left: 4px solid #007bff; }
    .config-card.cbt { border-left-color: #28a745; }
    .config-card.wawancara { border-left-color: #ffc107; } /* TBQ */
    .preview-card { border: 2px dashed #dee2e6; }
    .preview-card.has-data { border: 2px solid #28a745; }
    .sesi-row { transition: all 0.2s; }
    .sesi-row:hover { background-color: #f8f9fa; }
    .grup-badge { font-size: 0.7rem; padding: 2px 6px; }
    .capacity-warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
    .stats-mini { font-size: 0.85rem; }
    .stats-mini .value { font-size: 1.2rem; font-weight: bold; }
    .calc-metric { padding: 8px 4px; }
    .calc-metric small { font-size: 0.75rem; }
    .calc-metric strong { font-size: 1.15rem; display: block; margin-top: 2px; }
    .calc-warning { color: #dc3545; }
    .calc-ok { color: #28a745; }
    #calcPanel .card-body { background: #f8f9fa; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-calendar-alt"></i> Penjadwalan Ujian</h1>
        <p class="text-muted mb-0">Atur jadwal CBT & TBQ secara paralel</p>
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
<div class="alert alert-info">
    Penjadwalan ujian sedang memakai konteks:
    Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
    Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
    Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
</div>
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

    {{-- Mode & Sesi Limit (above config cards so calculation uses these values) --}}
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="card card-outline card-primary mb-0">
                <div class="card-body py-2">
                    <div class="form-group mb-0">
                        <label><i class="fas fa-exchange-alt mr-1 text-primary"></i>Mode Penjadwalan <span class="text-danger">*</span></label>
                        <select name="mode" class="form-control" id="modeSelect" required>
                            <option value="swap" {{ ($settings['mode'] ?? 'swap') == 'swap' ? 'selected' : '' }}>
                                🔄 Swap (Grup A↔B bertukar)
                            </option>
                            <option value="queue" {{ ($settings['mode'] ?? 'swap') == 'queue' ? 'selected' : '' }}>
                                📋 Queue (CBT dulu, sisa langsung TBQ)
                            </option>
                        </select>
                        <small class="text-muted">
                            <strong>Swap:</strong> Grup A CBT, Grup B TBQ, lalu tukar.
                            <strong>Queue:</strong> CBT penuh dulu, sisanya langsung TBQ.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-outline card-primary mb-0">
                <div class="card-body py-2">
                    <div class="form-group mb-0">
                        <label><i class="fas fa-layer-group mr-1 text-primary"></i>Maksimum Sesi <i class="fas fa-info-circle text-muted" title="Batasi jumlah sesi agar ujian tidak terlalu sore. Kosongkan untuk otomatis."></i></label>
                        <input type="number" name="max_sesi" class="form-control" 
                               value="{{ $settings['max_sesi'] ?? '' }}" min="2" max="50" placeholder="Otomatis (tanpa batas)">
                        <small class="text-muted">Kosongkan untuk otomatis. Mode Swap: kelipatan 2 (tiap putaran = 2 sesi).</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <h3 class="card-title"><i class="fas fa-microphone mr-2"></i>Konfigurasi TBQ</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Jumlah Ruang TBQ <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_ruang_wawancara" class="form-control" 
                               value="{{ $settings['jumlah_ruang_wawancara'] ?? 4 }}" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas per Ruang <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas_wawancara" class="form-control" 
                               value="{{ $settings['kapasitas_wawancara'] ?? 15 }}" min="1" max="50" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi TBQ (menit) <span class="text-danger">*</span></label>
                        <input type="number" name="durasi_wawancara" class="form-control" 
                               value="{{ $settings['durasi_wawancara'] ?? 60 }}" min="15" max="240" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Prefix Nama Ruang</label>
                        <input type="text" name="prefix_ruang_wawancara" class="form-control" 
                               value="{{ $settings['prefix_ruang_wawancara'] ?? 'Ruang TBQ' }}" maxlength="50">
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

                    <hr>
                    <div class="form-group">
                        <label><i class="fas fa-user-shield mr-1"></i>Ketua Panitia</label>
                        <select name="ketua_panitia_id" class="form-control">
                            <option value="">-- Pilih Ketua Panitia --</option>
                            @foreach($pengujiList as $user)
                            <option value="{{ $user->id }}" {{ ($settings['ketua_panitia_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->roles->pluck('display_name')->join(', ') }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Dipilih sekarang, bisa diubah nanti di halaman detail.</small>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Filter Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="all" {{ ($selectedJalurIdInput ?? '') === 'all' ? 'selected' : '' }}>-- Semua Jalur --</option>
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
                            <option value="all" {{ ($selectedGelombangIdInput ?? '') === 'all' ? 'selected' : '' }}>-- Semua Gelombang --</option>
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

    {{-- Realtime Calculation Panel --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-info mb-0" id="calcPanel">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Estimasi Perhitungan Realtime</h3>
                </div>
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Kap. CBT/Sesi</small>
                            <strong class="text-success" id="calcKapCbt">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Kap. TBQ/Sesi</small>
                            <strong class="text-warning" id="calcKapWaw">-</strong>
                        </div>
                        <div class="col calc-metric" id="calcParalelCol">
                            <small class="text-muted d-block">Kap. Paralel</small>
                            <strong class="text-info" id="calcParalel">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Jml Sesi</small>
                            <strong class="text-primary" id="calcSesi">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Terjadwalkan</small>
                            <strong id="calcTerjadwal">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Ruang Akhir CBT</small>
                            <strong id="calcOverflowCbt">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Ruang Akhir Waw</small>
                            <strong id="calcOverflowWaw">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Total Durasi</small>
                            <strong id="calcDurasi">-</strong>
                        </div>
                        <div class="col calc-metric">
                            <small class="text-muted d-block">Est. Selesai</small>
                            <strong class="text-danger" id="calcSelesai">-</strong>
                        </div>
                    </div>
                    <div class="mt-2" id="calcRecommendation" style="display:none;">
                        <div class="alert alert-warning alert-sm mb-0 py-2 px-3">
                            <i class="fas fa-lightbulb mr-1"></i><span id="calcRecommendationText"></span>
                        </div>
                    </div>
                    <div class="text-center mt-2" id="calcNote" style="display:none;">
                        <small class="text-muted"><i class="fas fa-info-circle mr-1"></i><span id="calcNoteText"></span></small>
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
                                <td>Mode</td>
                                <td class="text-right">
                                    @if(($settings['mode'] ?? 'swap') == 'queue')
                                    <span class="badge badge-info">📋 Queue</span>
                                    @else
                                    <span class="badge badge-primary">🔄 Swap</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Total Peserta</td>
                                <td class="text-right">
                                    <strong>{{ count($schedule['peserta'] ?? []) }}</strong>
                                    @if(count($schedule['peserta'] ?? []) < $totalPeserta)
                                    <small class="text-danger">/ {{ $totalPeserta }} eligible</small>
                                    @else
                                    <small class="text-muted">/ {{ $totalPeserta }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Kapasitas CBT/Sesi</td>
                                <td class="text-right"><strong class="text-success">{{ $kapasitasCbt }}</strong> ({{ $settings['jumlah_ruang_cbt'] }} × {{ $settings['kapasitas_cbt'] }})</td>
                            </tr>
                            <tr>
                                <td>Kapasitas TBQ/Sesi</td>
                                <td class="text-right"><strong class="text-warning">{{ $kapasitasWawancara }}</strong> ({{ $settings['jumlah_ruang_wawancara'] }} × {{ $settings['kapasitas_wawancara'] }})</td>
                            </tr>
                            <tr>
                                <td>Jumlah Sesi</td>
                                <td class="text-right">
                                    <strong>{{ count($schedule['sesi']) }}</strong>
                                    @if($settings['max_sesi'] ?? null)
                                    <small class="text-muted">(maks: {{ $settings['max_sesi'] }})</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Estimasi Selesai</td>
                                <td class="text-right"><strong>{{ $schedule['estimasi_selesai'] }}</strong></td>
                            </tr>
                            @if($settings['ketua_panitia_id'] ?? null)
                            <tr>
                                <td>Ketua Panitia</td>
                                <td class="text-right">
                                    <strong><i class="fas fa-user-shield text-primary mr-1"></i>{{ $pengujiList->firstWhere('id', $settings['ketua_panitia_id'])?->name ?? '-' }}</strong>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle mr-2"></i>Cara Kerja - {{ ($settings['mode'] ?? 'swap') == 'queue' ? 'Mode Queue' : 'Mode Swap' }}</h6>
                        @if(($settings['mode'] ?? 'swap') == 'queue')
                        <ul class="mb-0 pl-3">
                            <li>CBT diisi penuh terlebih dahulu ({{ $kapasitasCbt }} orang)</li>
                            <li>Peserta yang belum CBT langsung <strong>TBQ</strong> sambil menunggu</li>
                            <li>Setelah CBT selesai, peserta langsung pindah ke TBQ</li>
                            <li>Efisien jika kapasitas TBQ > CBT</li>
                        </ul>
                        @else
                        <ul class="mb-0 pl-3">
                            <li><strong>Grup A</strong>: CBT dulu → lalu TBQ</li>
                            <li><strong>Grup B</strong>: TBQ dulu → lalu CBT</li>
                            <li>Setiap peserta mengikuti <strong>2 sesi</strong> (CBT + TBQ)</li>
                            <li>Distribusi berdasarkan <strong>Nomor Tes</strong></li>
                        </ul>
                        @endif
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
                        <th class="bg-warning">TBQ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule['sesi'] as $nomorSesi => $sesi)
                    @php
                        $normalCbt = ($settings['jumlah_ruang_cbt'] ?? 1) * ($settings['kapasitas_cbt'] ?? 30);
                        $normalWaw = ($settings['jumlah_ruang_wawancara'] ?? 1) * ($settings['kapasitas_wawancara'] ?? 15);
                        $cbtOverflow = $sesi['cbt']['jumlah'] > $normalCbt;
                        $wawOverflow = $sesi['wawancara']['jumlah'] > $normalWaw;
                    @endphp
                    <tr class="sesi-row">
                        <td class="text-center"><strong>Sesi {{ $nomorSesi }}</strong></td>
                        <td class="text-center">{{ $sesi['waktu_mulai'] }} - {{ $sesi['waktu_selesai'] }}</td>
                        <td>
                            <span class="badge badge-success">{{ $sesi['cbt']['jumlah'] }} peserta</span>
                            @if($cbtOverflow)
                            <span class="badge badge-danger" title="Melebihi kapasitas normal {{ $normalCbt }}">+{{ $sesi['cbt']['jumlah'] - $normalCbt }} overflow</span>
                            @endif
                            <br><small class="text-muted">No Tes: {{ $sesi['cbt']['range'] }}</small>
                        </td>
                        <td>
                            <span class="badge badge-warning text-dark">{{ $sesi['wawancara']['jumlah'] }} peserta</span>
                            @if($wawOverflow)
                            <span class="badge badge-danger" title="Melebihi kapasitas normal {{ $normalWaw }}">+{{ $sesi['wawancara']['jumlah'] - $normalWaw }} overflow</span>
                            @endif
                            <br><small class="text-muted">No Tes: {{ $sesi['wawancara']['range'] }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mode Info --}}
        <div class="alert alert-{{ ($settings['mode'] ?? 'swap') == 'swap' ? 'primary' : 'info' }} mt-3">
            <i class="fas fa-info-circle mr-2"></i>
            @if(($settings['mode'] ?? 'swap') == 'swap')
            <strong>Mode Swap:</strong> Setiap putaran terdiri dari 2 sesi. Sesi ganjil: Grup A → CBT, Grup B → TBQ. Sesi genap: bertukar.
            Sisa peserta yang tidak memenuhi kapasitas penuh ditempatkan di ruang terakhir.
            @else
            <strong>Mode Queue:</strong> Peserta mengikuti CBT dan TBQ secara mengalir.
            Yang belum dapat giliran CBT bisa langsung TBQ dulu, begitu pula sebaliknya.
            @endif
        </div>
    </div>
    <div class="card-footer">
        <form id="storeForm" method="POST" action="{{ route('admin.penjadwalan-ujian.store') }}">
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
                    <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#confirmSaveModal">
                        <i class="fas fa-lock mr-2"></i>Simpan & Kunci Jadwal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmSaveModal" tabindex="-1" role="dialog" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="confirmSaveModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Simpan & Kunci
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Perhatian!</strong> Jadwal yang sudah dikunci tidak dapat diubah.
                </div>
                <p class="mb-2">Apakah Anda yakin ingin menyimpan dan mengunci jadwal ini?</p>
                <ul class="mb-0">
                    <li><strong>Total Peserta:</strong> {{ $totalPeserta ?? 0 }} orang</li>
                    <li><strong>Total Sesi:</strong> {{ isset($schedule['sesi']) ? count($schedule['sesi']) : 0 }} sesi</li>
                    <li><strong>Mode:</strong> {{ ($settings['mode'] ?? 'swap') == 'queue' ? 'Queue (Antrian)' : 'Swap (Grup A↔B)' }}</li>
                    <li><strong>Estimasi Selesai:</strong> {{ $schedule['estimasi_selesai'] ?? '-' }}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" onclick="document.getElementById('storeForm').submit()">
                    <i class="fas fa-lock mr-1"></i>Ya, Simpan & Kunci
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('js')
<script>
$(document).ready(function() {
    var totalPeserta = {{ $totalPeserta ?? 0 }};

    function updateCalc() {
        // Read all inputs
        var cbtRuang = parseInt($('input[name="jumlah_ruang_cbt"]').val()) || 0;
        var cbtKap = parseInt($('input[name="kapasitas_cbt"]').val()) || 0;
        var wawRuang = parseInt($('input[name="jumlah_ruang_wawancara"]').val()) || 0;
        var wawKap = parseInt($('input[name="kapasitas_wawancara"]').val()) || 0;
        var durasiCbt = parseInt($('input[name="durasi_cbt"]').val()) || 0;
        var durasiWaw = parseInt($('input[name="durasi_wawancara"]').val()) || 0;
        var jedaSesi = parseInt($('input[name="jeda_sesi"]').val()) || 0;
        var maxSesi = parseInt($('input[name="max_sesi"]').val()) || 0;
        var mode = $('select[name="mode"]').val();
        var jamMulai = $('input[name="jam_mulai"]').val() || '08:00';

        var kapCbt = cbtRuang * cbtKap;
        var kapWaw = wawRuang * wawKap;

        // Update card inline stats
        $('#totalCbt').text(kapCbt);
        $('#totalWawancara').text(kapWaw);

        // Update calc panel: capacities
        $('#calcKapCbt').text(kapCbt);
        $('#calcKapWaw').text(kapWaw);

        var jumlahSesi = 0;
        var pesertaTerjadwal = totalPeserta;
        var kapParalel = 0;
        var noteText = '';
        var recommendations = [];

        if (mode === 'swap') {
            // --- SWAP MODE ---
            kapParalel = Math.min(kapCbt, kapWaw);
            var pesertaPerPutaran = kapParalel * 2; // Grup A + Grup B
            var jumlahPutaran = 0;
            if (pesertaPerPutaran > 0) {
                jumlahPutaran = Math.ceil(totalPeserta / pesertaPerPutaran);
            }
            jumlahSesi = jumlahPutaran * 2; // setiap putaran = 2 sesi

            // Apply max_sesi limit
            if (maxSesi > 0 && jumlahSesi > maxSesi) {
                var maxPut = Math.floor(maxSesi / 2);
                if (maxPut < 1) maxPut = 1;
                jumlahPutaran = maxPut;
                jumlahSesi = jumlahPutaran * 2;
                pesertaTerjadwal = Math.min(totalPeserta, jumlahPutaran * pesertaPerPutaran);
            }

            if (kapCbt !== kapWaw && kapCbt > 0 && kapWaw > 0) {
                var diff = Math.abs(kapCbt - kapWaw);
                var persen = Math.round((diff / Math.max(kapCbt, kapWaw)) * 100);
                noteText = 'Kapasitas CBT & TBQ tidak seimbang (beda ' + persen + '%). Pertimbangkan mode Queue.';
            }

            // Show swap-specific column (paralel)
            $('#calcParalelCol').show();
            $('#calcParalel').text(kapParalel);
            $('#calcSesi').text(jumlahSesi);

        } else {
            // --- QUEUE MODE ---
            $('#calcParalelCol').hide();

            if (kapCbt > 0 && kapWaw > 0) {
                var sesiCbtEst = Math.ceil(totalPeserta / kapCbt);
                var sesiWawEst = Math.ceil(totalPeserta / kapWaw) + 1;
                jumlahSesi = Math.max(sesiCbtEst, sesiWawEst);

                if (maxSesi > 0 && jumlahSesi > maxSesi) {
                    jumlahSesi = maxSesi;
                    var doneCbt = Math.min(totalPeserta, kapCbt * jumlahSesi);
                    var doneWaw = Math.min(doneCbt, kapWaw * Math.max(0, jumlahSesi - 1));
                    pesertaTerjadwal = Math.min(totalPeserta, doneWaw);
                }

                noteText = 'Mode Queue: perhitungan sesi adalah estimasi (~). Hasil aktual bisa sedikit berbeda.';
            }
            $('#calcSesi').text(jumlahSesi > 0 ? '~' + jumlahSesi : '-');
        }

        // Peserta terjadwalkan
        if (pesertaTerjadwal < totalPeserta && totalPeserta > 0) {
            $('#calcTerjadwal').html('<span class="calc-warning">' + pesertaTerjadwal + '/' + totalPeserta + '</span>');
        } else {
            $('#calcTerjadwal').html('<span class="calc-ok">' + pesertaTerjadwal + '/' + totalPeserta + '</span>');
        }

        // --- Overflow Ruang Akhir ---
        // Hitung peserta per sesi terakhir (sisa yang tidak bagi rata)
        if (jumlahSesi > 0 && totalPeserta > 0) {
            var pesertaSesiTerakhirCbt = 0;
            var pesertaSesiTerakhirWaw = 0;

            if (mode === 'swap' && kapParalel > 0) {
                var pesertaPerPut = kapParalel * 2;
                var jumlahPut = Math.ceil(pesertaTerjadwal / pesertaPerPut);
                var sisaPutTerakhir = pesertaTerjadwal - (jumlahPut - 1) * pesertaPerPut;
                // Putaran terakhir: setengah ke CBT, setengah ke TBQ
                pesertaSesiTerakhirCbt = Math.ceil(sisaPutTerakhir / 2);
                pesertaSesiTerakhirWaw = sisaPutTerakhir - pesertaSesiTerakhirCbt;
            } else if (mode === 'queue') {
                // Queue: sesi terakhir CBT
                var sisaCbt = pesertaTerjadwal % kapCbt;
                pesertaSesiTerakhirCbt = sisaCbt > 0 ? sisaCbt : kapCbt;
                var sisaWaw = pesertaTerjadwal % kapWaw;
                pesertaSesiTerakhirWaw = sisaWaw > 0 ? sisaWaw : kapWaw;
            }

            // CBT overflow: isi ruang dari pertama, sisa ke ruang terakhir
            if (pesertaSesiTerakhirCbt > 0 && cbtRuang > 0) {
                var fullCbtRooms = Math.min(Math.floor(pesertaSesiTerakhirCbt / cbtKap), cbtRuang - 1);
                var lastRoomCbt = pesertaSesiTerakhirCbt - (fullCbtRooms * cbtKap);
                var pctCbt = Math.round((lastRoomCbt / cbtKap) * 100);
                if (pctCbt > 120) {
                    $('#calcOverflowCbt').html('<span class="calc-warning">' + lastRoomCbt + '/' + cbtKap + ' (' + pctCbt + '%)</span>');
                } else {
                    $('#calcOverflowCbt').text(lastRoomCbt + '/' + cbtKap + ' (' + pctCbt + '%)');
                }
            } else {
                $('#calcOverflowCbt').text('-');
            }

            // TBQ overflow
            if (pesertaSesiTerakhirWaw > 0 && wawRuang > 0) {
                var perRoomWaw = Math.ceil(pesertaSesiTerakhirWaw / wawRuang);
                var pctWaw = Math.round((perRoomWaw / wawKap) * 100);
                if (pctWaw > 120) {
                    $('#calcOverflowWaw').html('<span class="calc-warning">' + perRoomWaw + '/' + wawKap + ' (' + pctWaw + '%)</span>');
                } else {
                    $('#calcOverflowWaw').text(perRoomWaw + '/' + wawKap + ' (' + pctWaw + '%)');
                }
            } else {
                $('#calcOverflowWaw').text('-');
            }
        } else {
            $('#calcOverflowCbt, #calcOverflowWaw').text('-');
        }

        // --- Rekomendasi Ruang Tambahan ---
        if (pesertaTerjadwal < totalPeserta && totalPeserta > 0) {
            var kekurangan = totalPeserta - pesertaTerjadwal;
            if (mode === 'swap') {
                // Saran 1: tambah sesi
                var neededPutExtra = Math.ceil(kekurangan / (kapParalel * 2 || 1));
                var sesiExtra = neededPutExtra * 2;
                recommendations.push('Tambah ' + sesiExtra + ' sesi (naikkan maks sesi menjadi ' + (jumlahSesi + sesiExtra) + ')');

                // Saran 2: tambah ruang bottleneck
                if (kapCbt <= kapWaw) {
                    var extraRoomCbt = Math.ceil(kekurangan / ((jumlahSesi / 2) * cbtKap || 1));
                    recommendations.push('Atau tambah ' + extraRoomCbt + ' ruang CBT (total: ' + (cbtRuang + extraRoomCbt) + ')');
                } else {
                    var extraRoomWaw = Math.ceil(kekurangan / ((jumlahSesi / 2) * wawKap || 1));
                    recommendations.push('Atau tambah ' + extraRoomWaw + ' ruang TBQ (total: ' + (wawRuang + extraRoomWaw) + ')');
                }
            } else {
                // Queue mode
                var bottleneck = kapCbt < kapWaw ? 'CBT' : 'TBQ';
                var bottleneckKap = kapCbt < kapWaw ? kapCbt : kapWaw;
                var extraSesi = Math.ceil(kekurangan / (bottleneckKap || 1));
                recommendations.push('Tambah ~' + extraSesi + ' sesi (naikkan maks sesi)');
                if (kapCbt < kapWaw) {
                    var extraR = Math.ceil(kekurangan / (jumlahSesi * cbtKap || 1));
                    recommendations.push('Atau tambah ' + extraR + ' ruang CBT (total: ' + (cbtRuang + extraR) + ')');
                } else {
                    var extraR = Math.ceil(kekurangan / (jumlahSesi * wawKap || 1));
                    recommendations.push('Atau tambah ' + extraR + ' ruang TBQ (total: ' + (wawRuang + extraR) + ')');
                }
            }
        }

        // Show recommendation
        if (recommendations.length > 0) {
            var recHtml = '<strong>' + (totalPeserta - pesertaTerjadwal) + ' peserta tidak terjadwalkan.</strong> Saran:<br>';
            recommendations.forEach(function(r) { recHtml += '• ' + r + '<br>'; });
            $('#calcRecommendation').show();
            $('#calcRecommendationText').html(recHtml);
        } else {
            $('#calcRecommendation').hide();
        }

        // Estimasi waktu
        var durasiMax = Math.max(durasiCbt, durasiWaw);
        var totalMenit = jumlahSesi > 0 ? (jumlahSesi * durasiMax + Math.max(0, jumlahSesi - 1) * jedaSesi) : 0;

        // Format durasi
        var totalJam = Math.floor(totalMenit / 60);
        var sisaMenit = totalMenit % 60;
        var durasiText = '';
        if (totalJam > 0) durasiText += totalJam + 'j ';
        durasiText += sisaMenit + 'm';
        $('#calcDurasi').text(jumlahSesi > 0 ? durasiText : '-');

        // Hitung jam selesai
        if (jumlahSesi > 0 && jamMulai) {
            var parts = jamMulai.split(':');
            var startMin = parseInt(parts[0]) * 60 + parseInt(parts[1] || 0);
            var endMin = startMin + totalMenit;
            var endH = Math.floor(endMin / 60);
            var endM = endMin % 60;
            var prefix = mode === 'queue' ? '~' : '';
            $('#calcSelesai').text(prefix + (endH < 10 ? '0' : '') + endH + ':' + (endM < 10 ? '0' : '') + endM);
        } else {
            $('#calcSelesai').text('-');
        }

        // Note
        if (noteText) {
            $('#calcNote').show();
            $('#calcNoteText').text(noteText);
        } else {
            $('#calcNote').hide();
        }
    }

    // Bind to all relevant inputs
    var calcInputs = 'input[name="jumlah_ruang_cbt"], input[name="kapasitas_cbt"], ' +
        'input[name="jumlah_ruang_wawancara"], input[name="kapasitas_wawancara"], ' +
        'input[name="durasi_cbt"], input[name="durasi_wawancara"], ' +
        'input[name="jeda_sesi"], input[name="max_sesi"], input[name="jam_mulai"]';

    $(calcInputs).on('input change', updateCalc);
    $('select[name="mode"]').on('change', updateCalc);

    // Initial calculation on page load
    updateCalc();
});
</script>
@endsection
