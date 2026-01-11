@extends('adminlte::page')

@section('title', 'Detail Log Pengunjung')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-list mr-2"></i>Detail Log Pengunjung</h1>
            <small class="text-muted">Daftar lengkap kunjungan ke website PPDB</small>
        </div>
        <div>
            <a href="{{ route('admin.visitor-logs.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Statistik
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.visitor-logs.list') }}" method="GET" class="row align-items-center">
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm" 
                           value="{{ request('date') }}" placeholder="Tanggal">
                </div>
                <div class="col-md-2">
                    <select name="device" class="form-control form-control-sm">
                        <option value="">Semua Device</option>
                        <option value="desktop" {{ request('device') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="mobile" {{ request('device') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="tablet" {{ request('device') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="converted" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="yes" {{ request('converted') == 'yes' ? 'selected' : '' }}>Sudah Mendaftar</option>
                        <option value="no" {{ request('converted') == 'no' ? 'selected' : '' }}>Belum Mendaftar</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Cari IP, kota, alamat...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.visitor-logs.list') }}" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 140px;">Waktu</th>
                            <th style="width: 110px;">IP Address</th>
                            <th style="width: 70px;">Device</th>
                            <th>Browser & Platform</th>
                            <th>Lokasi</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visitors as $visitor)
                            <tr class="{{ $visitor->converted_to_registration ? 'table-success' : '' }}">
                                <td>
                                    <small>
                                        <strong>{{ $visitor->visited_at->format('d M Y') }}</strong><br>
                                        <span class="text-muted">{{ $visitor->visited_at->format('H:i:s') }}</span>
                                    </small>
                                </td>
                                <td>
                                    <code class="small">{{ $visitor->ip_address }}</code>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $visitor->device_color }}" title="{{ $visitor->device_type }}">
                                        <i class="{{ $visitor->device_icon }}"></i>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <i class="{{ $visitor->browser_icon }} mr-1"></i>
                                        {{ $visitor->browser }} {{ $visitor->browser_version }}<br>
                                        <span class="text-muted">
                                            <i class="{{ $visitor->platform_icon }} mr-1"></i>
                                            {{ $visitor->platform }} {{ $visitor->platform_version }}
                                        </span>
                                    </small>
                                </td>
                                <td>
                                    @if($visitor->address)
                                        <small>
                                            <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                            {{ Str::limit($visitor->address, 35) }}
                                            @if($visitor->city)
                                                <br><span class="text-muted">{{ $visitor->city }}</span>
                                            @endif
                                        </small>
                                    @elseif($visitor->city || $visitor->country)
                                        <small>
                                            <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
                                            {{ $visitor->location_string }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {!! $visitor->conversion_badge !!}
                                    @if($visitor->converted_to_registration && $visitor->calonSiswa)
                                        <br>
                                        <a href="{{ route('admin.pendaftar.show', $visitor->calon_siswa_id) }}" 
                                           class="btn btn-xs btn-outline-primary mt-1" title="Lihat Data Pendaftar">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($visitor->hasCoordinates())
                                        <a href="https://www.google.com/maps?q={{ $visitor->latitude }},{{ $visitor->longitude }}" 
                                           target="_blank" class="btn btn-xs btn-outline-info" title="Lihat di Maps">
                                            <i class="fas fa-map"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Tidak ada data pengunjung
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($visitors->hasPages())
            <div class="card-footer">
                {{ $visitors->links() }}
            </div>
        @endif
    </div>
    
    {{-- Legend --}}
    <div class="card card-outline card-secondary">
        <div class="card-header py-2">
            <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-1"></i> Keterangan</h5>
        </div>
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-4">
                    <small>
                        <strong>Status Pendaftaran:</strong><br>
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Mendaftar</span> Sudah melakukan pendaftaran<br>
                        <span class="badge badge-secondary"><i class="fas fa-clock"></i> Belum</span> Belum mendaftar
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <strong>Perangkat:</strong><br>
                        <span class="badge badge-success"><i class="fas fa-mobile-alt"></i></span> Mobile<br>
                        <span class="badge badge-info"><i class="fas fa-tablet-alt"></i></span> Tablet<br>
                        <span class="badge badge-primary"><i class="fas fa-desktop"></i></span> Desktop
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <strong>Warna Baris:</strong><br>
                        <span style="background: #d4edda; padding: 2px 8px; border-radius: 3px;">Hijau</span> = Sudah Mendaftar
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
    }
    .table-success {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
</style>
@stop
