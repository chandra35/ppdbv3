@extends('adminlte::page')

@section('title', 'Pengunjung Online')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-users text-success mr-2"></i>Pengunjung Online</h1>
            <small class="text-muted">Daftar pengunjung yang sedang aktif di website</small>
        </div>
        <div>
            <a href="{{ route('admin.visitor-logs.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Statistics Cards --}}
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="stat-total">{{ $stats['total_online'] }}</h3>
                    <p>Total Online</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="stat-identified">{{ $stats['identified'] }}</h3>
                    <p>Teridentifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="stat-anonymous">{{ $stats['anonymous'] }}</h3>
                    <p>Anonim</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-secret"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="stat-mobile">{{ $stats['mobile'] }}</h3>
                    <p>Mobile</p>
                </div>
                <div class="icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="stat-desktop">{{ $stats['desktop'] }}</h3>
                    <p>Desktop</p>
                </div>
                <div class="icon">
                    <i class="fas fa-desktop"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3 id="stat-tablet">{{ $stats['tablet'] }}</h3>
                    <p>Tablet</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tablet-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings & Auto-refresh --}}
    <div class="card card-outline card-success mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <span class="mr-2">Definisi Online:</span>
                        <select id="minutes-filter" class="form-control form-control-sm" style="width: 120px;">
                            <option value="3" {{ $minutes == 3 ? 'selected' : '' }}>3 menit</option>
                            <option value="5" {{ $minutes == 5 ? 'selected' : '' }}>5 menit</option>
                            <option value="10" {{ $minutes == 10 ? 'selected' : '' }}>10 menit</option>
                            <option value="15" {{ $minutes == 15 ? 'selected' : '' }}>15 menit</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox" class="custom-control-input" id="auto-refresh" checked>
                        <label class="custom-control-label" for="auto-refresh">Auto-refresh (30 detik)</label>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <span class="text-muted">
                        <i class="fas fa-sync-alt"></i> Terakhir update: <span id="last-update">{{ now()->format('H:i:s') }}</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success ml-2" onclick="refreshData()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Online Visitors List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-circle text-success mr-1"></i>
                Pengunjung Online Saat Ini
            </h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped" id="online-table">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">Status</th>
                        <th>Nama / Identitas</th>
                        <th>Halaman Aktif</th>
                        <th>Perangkat</th>
                        <th>Lokasi</th>
                        <th>Durasi</th>
                        <th>Terakhir Aktif</th>
                    </tr>
                </thead>
                <tbody id="visitors-tbody">
                    @forelse($uniqueOnline as $visitor)
                        <tr>
                            <td class="text-center">
                                <i class="fas fa-circle text-success" title="Online"></i>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($visitor->user_id)
                                        <span class="badge badge-primary mr-2"><i class="fas fa-user"></i></span>
                                    @elseif($visitor->calon_siswa_id)
                                        <span class="badge badge-info mr-2"><i class="fas fa-user-graduate"></i></span>
                                    @else
                                        <span class="badge badge-secondary mr-2"><i class="fas fa-user-secret"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $visitor->visitor_name }}</strong>
                                        @if($visitor->calon_siswa_id && $visitor->calonSiswa)
                                            <br><small class="text-muted">{{ $visitor->calonSiswa->nomor_registrasi ?? $visitor->calonSiswa->nisn }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="text-primary">
                                        <i class="fas fa-file-alt mr-1"></i>
                                        {{ Str::limit($visitor->current_page_title ?? $visitor->page_title ?? '-', 40) }}
                                    </small>
                                    <br>
                                    <small class="text-muted" title="{{ $visitor->current_url ?? $visitor->page_url }}">
                                        {{ Str::limit($visitor->current_url ?? $visitor->page_url, 50) }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $visitor->device_color }}">
                                    <i class="{{ $visitor->device_icon }}"></i>
                                    {{ ucfirst($visitor->device_type ?? 'Unknown') }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    <i class="{{ $visitor->browser_icon }}"></i> {{ $visitor->browser ?? '-' }}
                                </small>
                            </td>
                            <td>
                                @if($visitor->city)
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    {{ $visitor->city }}
                                    @if($visitor->region)
                                        <br><small class="text-muted">{{ $visitor->region }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light">
                                    <i class="far fa-clock"></i> {{ $visitor->online_duration }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    {{ $visitor->last_activity_at?->diffForHumans() ?? '-' }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-visitors-row">
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 d-block"></i>
                                    <p>Tidak ada pengunjung yang sedang online</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card">
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-4">
                    <small>
                        <span class="badge badge-primary"><i class="fas fa-user"></i></span> Pengguna Terdaftar (Admin/Operator)
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <span class="badge badge-info"><i class="fas fa-user-graduate"></i></span> Calon Pendaftar / Peserta PPDB
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <span class="badge badge-secondary"><i class="fas fa-user-secret"></i></span> Pengunjung Anonim
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .small-box .inner h3 {
        font-size: 2rem;
    }
    .small-box .inner p {
        font-size: 0.9rem;
    }
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
    .bg-purple .icon i {
        color: rgba(255,255,255,0.2);
    }
    #online-table tbody tr {
        transition: background-color 0.3s ease;
    }
    #online-table tbody tr.highlight-new {
        background-color: #d4edda !important;
    }
    #online-table tbody tr.highlight-leaving {
        background-color: #f8d7da !important;
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>
@stop

@section('js')
<script>
let autoRefreshInterval = null;
const autoRefreshMs = 30000; // 30 seconds

document.addEventListener('DOMContentLoaded', function() {
    // Start auto-refresh if checked
    if (document.getElementById('auto-refresh').checked) {
        startAutoRefresh();
    }

    // Auto-refresh toggle
    document.getElementById('auto-refresh').addEventListener('change', function() {
        if (this.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });

    // Minutes filter change
    document.getElementById('minutes-filter').addEventListener('change', function() {
        refreshData();
    });
});

function startAutoRefresh() {
    stopAutoRefresh(); // Clear any existing interval
    autoRefreshInterval = setInterval(refreshData, autoRefreshMs);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function refreshData() {
    const minutes = document.getElementById('minutes-filter').value;
    
    fetch(`{{ route('admin.visitor-logs.online-data') }}?minutes=${minutes}`)
        .then(response => response.json())
        .then(data => {
            updateTable(data.visitors);
            updateStats(data.stats);
            document.getElementById('last-update').textContent = data.updated_at;
        })
        .catch(error => {
            console.error('Error fetching online data:', error);
        });
}

function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total_online;
    document.getElementById('stat-identified').textContent = stats.identified;
    document.getElementById('stat-anonymous').textContent = stats.anonymous;
}

function updateTable(visitors) {
    const tbody = document.getElementById('visitors-tbody');
    
    if (visitors.length === 0) {
        tbody.innerHTML = `
            <tr id="no-visitors-row">
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-user-slash fa-3x mb-3 d-block"></i>
                        <p>Tidak ada pengunjung yang sedang online</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    visitors.forEach(function(v) {
        let typeBadge = '';
        if (v.visitor_type === 'user') {
            typeBadge = '<span class="badge badge-primary mr-2"><i class="fas fa-user"></i></span>';
        } else if (v.visitor_type === 'pendaftar') {
            typeBadge = '<span class="badge badge-info mr-2"><i class="fas fa-user-graduate"></i></span>';
        } else {
            typeBadge = '<span class="badge badge-secondary mr-2"><i class="fas fa-user-secret"></i></span>';
        }

        let deviceBadge = 'secondary';
        let deviceIcon = 'fas fa-question-circle';
        if (v.device === 'mobile') {
            deviceBadge = 'success';
            deviceIcon = 'fas fa-mobile-alt';
        } else if (v.device === 'desktop') {
            deviceBadge = 'primary';
            deviceIcon = 'fas fa-desktop';
        } else if (v.device === 'tablet') {
            deviceBadge = 'info';
            deviceIcon = 'fas fa-tablet-alt';
        }

        html += `
            <tr>
                <td class="text-center">
                    <i class="fas fa-circle text-success pulse-animation" title="Online"></i>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        ${typeBadge}
                        <strong>${escapeHtml(v.nama)}</strong>
                    </div>
                </td>
                <td>
                    <small class="text-primary">
                        <i class="fas fa-file-alt mr-1"></i>
                        ${escapeHtml(v.current_page ? v.current_page.substring(0, 40) : '-')}
                    </small>
                    <br>
                    <small class="text-muted" title="${escapeHtml(v.current_url || '')}">
                        ${escapeHtml((v.current_url || '').substring(0, 50))}
                    </small>
                </td>
                <td>
                    <span class="badge badge-${deviceBadge}">
                        <i class="${deviceIcon}"></i>
                        ${v.device ? v.device.charAt(0).toUpperCase() + v.device.slice(1) : 'Unknown'}
                    </span>
                    <br>
                    <small class="text-muted">${escapeHtml(v.browser || '-')}</small>
                </td>
                <td>
                    ${v.city ? `<i class="fas fa-map-marker-alt text-danger"></i> ${escapeHtml(v.city)}` : '<span class="text-muted">-</span>'}
                    ${v.region ? `<br><small class="text-muted">${escapeHtml(v.region)}</small>` : ''}
                </td>
                <td>
                    <span class="badge badge-light">
                        <i class="far fa-clock"></i> ${escapeHtml(v.online_duration)}
                    </span>
                </td>
                <td>
                    <small>${escapeHtml(v.last_activity)}</small>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@stop
