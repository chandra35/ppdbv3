@extends('adminlte::page')

@section('title', 'Daftar Pendaftar')

@section('css')
@include('admin.partials.action-buttons-style')
<style>
    /* Mobile Optimization */
    @media (max-width: 767px) {
        body {
            font-size: 12px !important;
        }
        .content-wrapper {
            padding: 8px !important;
        }
        .content-header {
            padding: 8px 10px !important;
        }
        .content-header h1 {
            font-size: 16px !important;
            margin: 0 !important;
            font-weight: 600 !important;
        }
        .content-header .breadcrumb {
            display: none !important;
        }
        .card {
            margin-bottom: 8px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        .card-header {
            padding: 6px 10px !important;
            background: #f8f9fa !important;
        }
        .card-header h3 {
            font-size: 12px !important;
            margin: 0 !important;
            font-weight: 600 !important;
        }
        .card-body {
            padding: 8px !important;
        }
        .card-footer {
            padding: 6px 10px !important;
            background: #f8f9fa !important;
        }
        .alert {
            padding: 6px 8px !important;
            font-size: 11px !important;
            margin-bottom: 8px !important;
        }
        .form-control-sm {
            font-size: 11px !important;
            padding: 4px 8px !important;
            height: auto !important;
        }
        .btn-sm {
            font-size: 11px !important;
            padding: 4px 8px !important;
        }
        label {
            font-size: 10px !important;
            margin-bottom: 2px !important;
            font-weight: 600 !important;
        }
        .mobile-card-item {
            padding: 8px 10px !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        .badge {
            font-size: 9px !important;
            padding: 2px 4px !important;
        }
        .pagination {
            font-size: 11px !important;
        }
        .pagination .page-link {
            padding: 4px 8px !important;
        }
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2 mb-md-3 align-items-center">
        <div class="col-12 col-sm-6">
            <h1 style="font-size: 24px;"><i class="fas fa-users"></i> Daftar Pendaftar</h1>
        </div>
        <div class="col-12 col-sm-6">
            <div class="d-flex justify-content-sm-end align-items-center flex-wrap" style="gap: 8px;">
                {{-- Live DateTime --}}
                <div class="live-datetime text-right d-none d-md-block mr-2">
                    <div class="text-primary" id="live-date" style="font-size: 12px;"></div>
                    <div class="text-dark" id="live-time" style="font-size: 16px; font-weight: 600;"></div>
                </div>
                @if(auth()->user()->hasPermission('pendaftar.create'))
                <a href="{{ route('admin.pendaftar.tambah') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus mr-1"></i> Tambah Pendaftar
                </a>
                @endif
                {{-- Export Data Dropdown --}}
                <div class="btn-group">
                    <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('admin.pendaftar.export', ['type' => 'all']) }}">
                            <i class="fas fa-users mr-2"></i> Semua Pendaftar
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.pendaftar.export', ['type' => 'with_nomor_tes']) }}">
                            <i class="fas fa-id-card mr-2"></i> Peserta Ujian (Dengan Nomor Tes)
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.pendaftar.export-moodle') }}">
                            <i class="fas fa-graduation-cap mr-2"></i> Export Moodle (Punya Nomor Tes)
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-muted" href="#" data-toggle="modal" data-target="#exportFilterModal">
                            <i class="fas fa-filter mr-2"></i> Export dengan Filter...
                        </a>
                    </div>
                </div>
                <a href="{{ route('admin.pendaftar.map') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-map-marked-alt mr-1"></i> Peta Pendaftar
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    <div class="alert alert-info">
        Daftar pendaftar sedang memakai konteks:
        Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
        Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
        Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="padding: 8px;">
            <form id="filterForm" action="{{ route('admin.pendaftar.index') }}" method="GET" class="row" style="margin: 0 -4px;">
                {{-- Hidden inputs for sorting --}}
                <input type="hidden" name="sort" value="{{ $sortBy }}">
                <input type="hidden" name="dir" value="{{ $sortDir }}">
                <div class="col-12 col-md-6 col-lg-3 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">Cari</label>
                        <input type="text" name="search" id="search_filter" class="form-control form-control-sm" style="font-size: 11px; padding: 4px 8px;" placeholder="Nama, NISN..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">Jalur</label>
                        <select name="jalur_id" id="jalur_filter" class="form-control form-control-sm auto-submit" style="font-size: 11px; padding: 4px 8px;">
                            <option value="all" {{ $selectedJalurId === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            @foreach($jalurList as $jalur)
                            <option value="{{ $jalur->id }}" 
                                {{ $selectedJalurId == $jalur->id ? 'selected' : '' }}
                                data-tahun-aktif="{{ $jalur->tahunPelajaran->is_active ?? false }}">
                                {{ $jalur->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">Gelombang</label>
                        <select name="gelombang_id" id="gelombang_filter" class="form-control form-control-sm auto-submit" style="font-size: 11px; padding: 4px 8px;">
                            <option value="all" {{ $selectedGelombangId === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                            @foreach($gelombangList as $gelombang)
                            <option value="{{ $gelombang->id }}" 
                                data-jalur-id="{{ $gelombang->jalur_id }}"
                                {{ $selectedGelombangId == $gelombang->id ? 'selected' : '' }}
                                style="{{ ($selectedJalurId && $selectedJalurId !== 'all' && $gelombang->jalur_id != $selectedJalurId) ? 'display:none;' : '' }}">
                                {{ $gelombang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">Status</label>
                        <select name="status" class="form-control form-control-sm auto-submit" style="font-size: 11px; padding: 4px 8px;">
                            <option value="">Semua</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="has_nomor_tes" {{ request('status') == 'has_nomor_tes' ? 'selected' : '' }}>Dapat No.Tes</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2 col-lg-1 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">Per Hal</label>
                        <select name="per_page" class="form-control form-control-sm auto-submit" style="font-size: 11px; padding: 4px 8px;">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-7 col-lg-2 mb-1" style="padding: 0 4px;">
                    <div class="form-group mb-1">
                        <label class="d-none d-md-block" style="font-size: 10px; margin-bottom: 2px;">&nbsp;</label>
                        <div class="d-flex" style="gap: 4px;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-search"></i><span class="d-none d-sm-inline"> Filter</span>
                            </button>
                            <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pendaftar</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover d-none d-lg-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>
                            @php
                                $isNoTesSort = $sortBy == 'nomor_tes';
                                $noTesDir = $isNoTesSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nomor_tes', 'dir' => $noTesDir]) }}" class="text-dark">
                                No. Tes
                                @if($isNoTesSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $isNamaSort = $sortBy == 'nama_lengkap';
                                $namaDir = $isNamaSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_lengkap', 'dir' => $namaDir]) }}" class="text-dark">
                                Nama Lengkap
                                @if($isNamaSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $isNisnSort = $sortBy == 'nisn';
                                $nisnDir = $isNisnSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nisn', 'dir' => $nisnDir]) }}" class="text-dark">
                                NISN
                                @if($isNisnSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>Jalur / Gelombang</th>
                        <th>Pilihan Program</th>
                        <th>Lokasi</th>
                        <th>
                            @php
                                $isDokSort = $sortBy == 'dokumen_count';
                                $dokDir = $isDokSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'dokumen_count', 'dir' => $dokDir]) }}" class="text-dark">
                                Dokumen
                                @if($isDokSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $isStatusSort = $sortBy == 'status_verifikasi';
                                $statusDir = $isStatusSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status_verifikasi', 'dir' => $statusDir]) }}" class="text-dark">
                                Status
                                @if($isStatusSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php
                                $isDateSort = $sortBy == 'created_at';
                                $dateDir = $isDateSort && $sortDir == 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => $dateDir]) }}" class="text-dark">
                                Tgl Daftar
                                @if($isDateSort)
                                    <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>Last Login</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftars as $key => $pendaftar)
                    <tr>
                        <td>{{ $pendaftars->firstItem() + $key }}</td>
                        <td><code>{{ $pendaftar->nomor_tes ?? '-' }}</code></td>
                        <td>
                            <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="text-dark">
                                <strong>{{ $pendaftar->nama_lengkap }}</strong>
                            </a>
                        </td>
                        <td>{{ $pendaftar->nisn ?? '-' }}</td>
                        <td>
                            @if($pendaftar->jalurPendaftaran)
                                <span class="badge" style="background: {{ $pendaftar->jalurPendaftaran->warna ?? '#007bff' }}; color: white;">
                                    {{ $pendaftar->jalurPendaftaran->nama }}
                                </span>
                                @if($pendaftar->gelombangPendaftaran)
                                    <br><small class="text-muted">{{ $pendaftar->gelombangPendaftaran->nama }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pendaftar->pilihan_program)
                                <span class="badge badge-info">{{ $pendaftar->pilihan_program }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pendaftar->registration_address)
                                <small title="{{ $pendaftar->registration_address }}">
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    {{ Str::limit($pendaftar->registration_address, 35) }}
                                </small>
                            @elseif($pendaftar->hasRegistrationCoordinates())
                                <small class="text-muted" title="Koordinat: {{ $pendaftar->registration_coordinates }}">
                                    <i class="fas fa-crosshairs text-warning"></i> GPS
                                </small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $dokumenCount = $pendaftar->dokumen->count();
                                $validCount = $pendaftar->dokumen->where('status_verifikasi', 'valid')->count();
                                $pendingCount = $pendaftar->dokumen->where('status_verifikasi', 'pending')->count();
                                $invalidCount = $pendaftar->dokumen->where('status_verifikasi', 'invalid')->count();
                                $revisionCount = $pendaftar->dokumen->where('status_verifikasi', 'revision')->count();
                            @endphp
                            @if($dokumenCount > 0)
                                <div class="btn-group btn-group-sm" style="cursor: pointer;" onclick="showDokumenModal('{{ $pendaftar->id }}', '{{ addslashes($pendaftar->nama_lengkap) }}')">
                                    @if($validCount > 0)
                                        <button class="btn btn-success" title="{{ $validCount }} dokumen valid">
                                            <i class="fas fa-check"></i> {{ $validCount }}
                                        </button>
                                    @endif
                                    @if($pendingCount > 0)
                                        <button class="btn btn-warning" title="{{ $pendingCount }} dokumen pending">
                                            <i class="fas fa-clock"></i> {{ $pendingCount }}
                                        </button>
                                    @endif
                                    @if($invalidCount > 0)
                                        <button class="btn btn-danger" title="{{ $invalidCount }} dokumen invalid">
                                            <i class="fas fa-times"></i> {{ $invalidCount }}
                                        </button>
                                    @endif
                                    @if($revisionCount > 0)
                                        <button class="btn btn-info" title="{{ $revisionCount }} dokumen perlu revisi">
                                            <i class="fas fa-redo"></i> {{ $revisionCount }}
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted"><i class="fas fa-folder-open"></i> Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            @if($pendaftar->status_verifikasi == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($pendaftar->status_verifikasi == 'verified')
                                <span class="badge badge-info">Verified</span>
                            @elseif($pendaftar->status_verifikasi == 'approved')
                                <span class="badge badge-success">Diterima</span>
                            @elseif($pendaftar->status_verifikasi == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-secondary">{{ $pendaftar->status_verifikasi }}</span>
                            @endif
                        </td>
                        <td>{{ $pendaftar->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($pendaftar->user)
                                @if($pendaftar->user->isOnline())
                                    <span class="badge badge-success" style="font-size: 10px;"><i class="fas fa-circle"></i> Online</span>
                                @else
                                    <small class="text-muted">{{ $pendaftar->user->last_activity_human }}</small>
                                @endif
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-action-view" data-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> <span class="btn-text">Detail</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Tidak ada pendaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Mobile Card View -->
            <div class="d-lg-none">
                @forelse($pendaftars as $key => $pendaftar)
                <div class="mobile-card-item" style="background: {{ $loop->odd ? '#fff' : '#fafbfc' }}; padding: 8px;">
                    <div class="d-flex align-items-start" style="gap: 8px;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; margin-bottom: 3px; line-height: 1.2;">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="text-dark">
                                    {{ $pendaftar->nama_lengkap }}
                                </a>
                            </div>
                            <div style="font-size: 9px; margin-bottom: 3px;" class="text-muted">
                                <code style="font-size: 8px; padding: 1px 2px;">{{ $pendaftar->nomor_tes ?? '-' }}</code>
                                @if($pendaftar->nisn)
                                    <span style="margin-left: 2px;">{{ $pendaftar->nisn }}</span>
                                @endif
                            </div>
                            @if($pendaftar->jalurPendaftaran)
                            <div style="margin-bottom: 3px;">
                                <span class="badge" style="background: {{ $pendaftar->jalurPendaftaran->warna ?? '#007bff' }}; color: white; font-size: 8px; padding: 1px 3px;">
                                    {{ $pendaftar->jalurPendaftaran->nama }}
                                </span>
                                @if($pendaftar->gelombangPendaftaran)
                                    <span class="text-muted" style="font-size: 8px; margin-left: 2px;">{{ $pendaftar->gelombangPendaftaran->nama }}</span>
                                @endif
                                @if($pendaftar->pilihan_program)
                                    <span class="badge badge-info" style="font-size: 8px; padding: 1px 3px; margin-left: 2px;">{{ $pendaftar->pilihan_program }}</span>
                                @endif
                            </div>
                            @endif
                            <div style="margin-top: 3px;">
                                @php
                                    $dokumenCount = $pendaftar->dokumen->count();
                                    $validCount = $pendaftar->dokumen->where('status_verifikasi', 'valid')->count();
                                    $pendingCount = $pendaftar->dokumen->where('status_verifikasi', 'pending')->count();
                                    $invalidCount = $pendaftar->dokumen->where('status_verifikasi', 'invalid')->count();
                                    $revisionCount = $pendaftar->dokumen->where('status_verifikasi', 'revision')->count();
                                @endphp
                                @if($dokumenCount > 0)
                                    <span style="cursor: pointer;" onclick="showDokumenModal('{{ $pendaftar->id }}', '{{ addslashes($pendaftar->nama_lengkap) }}')">
                                    @if($validCount > 0)
                                        <span class="badge badge-success" style="font-size: 8px; padding: 1px 3px; margin-right: 2px;"><i class="fas fa-check"></i> {{ $validCount }}</span>
                                    @endif
                                    @if($pendingCount > 0)
                                        <span class="badge badge-warning" style="font-size: 8px; padding: 1px 3px; margin-right: 2px;"><i class="fas fa-clock"></i> {{ $pendingCount }}</span>
                                    @endif
                                    @if($invalidCount > 0)
                                        <span class="badge badge-danger" style="font-size: 8px; padding: 1px 3px; margin-right: 2px;"><i class="fas fa-times"></i> {{ $invalidCount }}</span>
                                    @endif
                                    @if($revisionCount > 0)
                                        <span class="badge badge-info" style="font-size: 8px; padding: 1px 3px;"><i class="fas fa-redo"></i> {{ $revisionCount }}</span>
                                    @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            @if($pendaftar->user && $pendaftar->user->isOnline())
                                <span class="badge badge-success" style="font-size: 7px; padding: 1px 3px;"><i class="fas fa-circle"></i></span>
                            @endif
                            @if($pendaftar->status_verifikasi == 'pending')
                                <span class="badge badge-warning" style="font-size: 8px; padding: 2px 4px; white-space: nowrap;">Pending</span>
                            @elseif($pendaftar->status_verifikasi == 'verified')
                                <span class="badge badge-info" style="font-size: 8px; padding: 2px 4px; white-space: nowrap;">Verified</span>
                            @elseif($pendaftar->status_verifikasi == 'approved')
                                <span class="badge badge-success" style="font-size: 8px; padding: 2px 4px; white-space: nowrap;">Diterima</span>
                            @elseif($pendaftar->status_verifikasi == 'rejected')
                                <span class="badge badge-danger" style="font-size: 8px; padding: 2px 4px; white-space: nowrap;">Ditolak</span>
                            @endif
                            <div style="margin-top: 4px;">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-primary btn-sm" style="font-size: 9px; padding: 3px 6px; white-space: nowrap;">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                            @if($pendaftar->registration_address)
                                <div class="text-muted" style="font-size: 7px; margin-top: 2px;" title="{{ $pendaftar->registration_address }}">
                                    <i class="fas fa-map-marker-alt text-success"></i> {{ Str::limit($pendaftar->registration_address, 20) }}
                                </div>
                            @endif
                            <div class="text-muted" style="font-size: 8px; margin-top: 1px;">
                                {{ $pendaftar->created_at->format('d/m') }}
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3" style="font-size: 11px;">Tidak ada pendaftar</div>
                @endforelse
            </div>
        </div>
        <div class="card-footer clearfix" style="padding: 6px 10px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted d-none d-md-block" style="font-size: 10px;">
                    {{ $pendaftars->firstItem() ?? 0 }}-{{ $pendaftars->lastItem() ?? 0 }} / {{ $pendaftars->total() }}
                </div>
                <div style="flex: 1; text-align: center;">
                    {{ $pendaftars->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Dokumen Quick Action -->
    <div class="modal fade" id="dokumenModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="max-height: 85vh;">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt"></i> Dokumen - <span id="modalPendaftarNama"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="dokumenListContainer" class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th style="width: 80px;">Preview</th>
                                    <th>Jenis Dokumen</th>
                                    <th style="width: 100px;">Ukuran</th>
                                    <th style="width: 120px;">Status</th>
                                    <th>Verifikasi Terakhir</th>
                                    <th style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="dokumenListBody">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview File -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title" id="filePreviewTitle">
                        <i class="fas fa-eye"></i> Preview Dokumen
                    </h6>
                    <div>
                        <a href="#" id="fileDownloadBtn" class="btn btn-sm btn-outline-light mr-2" target="_blank" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                            <span>&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-0 text-center" style="min-height: 400px; max-height: 80vh; overflow: auto; background: #f0f0f0;">
                    <div id="filePreviewLoading" class="py-5">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat preview...</p>
                    </div>
                    <div id="filePreviewContent" style="display: none;">
                        <img id="previewImage" src="" alt="Preview" class="img-fluid" style="max-height: 75vh; display: none;">
                        <iframe id="previewPdf" src="" style="width: 100%; height: 80vh; border: none; display: none;"></iframe>
                        <div id="previewUnsupported" style="display: none;" class="py-5">
                            <i class="fas fa-file fa-4x text-muted"></i>
                            <p class="mt-3 text-muted">Preview tidak tersedia untuk tipe file ini.</p>
                            <a href="#" id="previewDownloadLink" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    let currentPendaftarId = null;

    // Show dokumen modal
    function showDokumenModal(pendaftarId, nama) {
        currentPendaftarId = pendaftarId;
        $('#modalPendaftarNama').text(nama);
        $('#dokumenModal').modal('show');
        loadDokumenList(pendaftarId);
    }

    // Load dokumen list via AJAX
    function loadDokumenList(pendaftarId) {
        $.ajax({
            url: `/admin/pendaftar/${pendaftarId}/dokumen-list`,
            method: 'GET',
            success: function(response) {
                let html = '';
                if (response.dokumen && response.dokumen.length > 0) {
                    response.dokumen.forEach((dok, index) => {
                        const statusBadge = getStatusBadge(dok.status_verifikasi);
                        const actionButtons = getActionButtons(dok);
                        const verifikasiInfo = getVerifikasiInfo(dok);
                        const thumbnail = getThumbnail(dok);
                        
                        html += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td class="text-center">${thumbnail}</td>
                                <td>
                                    <strong>${dok.nama_dokumen_lengkap}</strong>
                                    <br><small class="text-muted">${dok.nama_file || ''}</small>
                                    ${dok.catatan_verifikasi ? `<br><small class="text-danger"><i class="fas fa-comment"></i> ${dok.catatan_verifikasi}</small>` : ''}
                                </td>
                                <td class="text-center"><small>${dok.file_size || '-'}</small></td>
                                <td class="text-center">${statusBadge}</td>
                                <td><small>${verifikasiInfo}</small></td>
                                <td class="text-center">${actionButtons}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center text-muted">Tidak ada dokumen</td></tr>';
                }
                $('#dokumenListBody').html(html);
            },
            error: function() {
                $('#dokumenListBody').html('<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data</td></tr>');
            }
        });
    }

    // Generate thumbnail for document
    function getThumbnail(dok) {
        if (!dok.file_path) {
            return '<span class="text-muted"><i class="fas fa-file fa-2x"></i></span>';
        }
        
        const fileUrl = dok.file_url || `/storage/${dok.file_path}`;
        const isImage = dok.mime_type && dok.mime_type.startsWith('image/');
        const isPdf = dok.mime_type === 'application/pdf';
        
        if (isImage) {
            return `<img src="${fileUrl}" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 2px solid #dee2e6;" 
                         onclick="previewFile('${fileUrl}', '${dok.mime_type}', '${dok.nama_dokumen_lengkap}')"
                         title="Klik untuk preview">`;
        } else if (isPdf) {
            return `<div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fef2f2; border-radius: 4px; cursor: pointer; border: 2px solid #dee2e6;" 
                         onclick="previewFile('${fileUrl}', '${dok.mime_type}', '${dok.nama_dokumen_lengkap}')"
                         title="Klik untuk preview PDF">
                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                    </div>`;
        } else {
            return `<div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 4px; cursor: pointer; border: 2px solid #dee2e6;"
                         onclick="previewFile('${fileUrl}', '${dok.mime_type}', '${dok.nama_dokumen_lengkap}')"
                         title="Klik untuk preview/download">
                        <i class="fas fa-file fa-2x text-secondary"></i>
                    </div>`;
        }
    }

    // Preview file in modal
    function previewFile(fileUrl, mimeType, title) {
        $('#filePreviewTitle').html('<i class="fas fa-eye"></i> ' + title);
        $('#fileDownloadBtn').attr('href', fileUrl);
        $('#filePreviewLoading').show();
        $('#filePreviewContent').hide();
        $('#previewImage').hide();
        $('#previewPdf').hide().attr('src', '');
        $('#previewUnsupported').hide();
        
        $('#filePreviewModal').modal('show');
        
        const isImage = mimeType && mimeType.startsWith('image/');
        const isPdf = mimeType === 'application/pdf';
        
        if (isImage) {
            const img = $('#previewImage');
            img.off('load error').on('load', function() {
                $('#filePreviewLoading').hide();
                $('#filePreviewContent').show();
                img.show();
            }).on('error', function() {
                $('#filePreviewLoading').hide();
                $('#filePreviewContent').show();
                $('#previewUnsupported').show();
                $('#previewDownloadLink').attr('href', fileUrl);
            }).attr('src', fileUrl);
        } else if (isPdf) {
            $('#previewPdf').attr('src', fileUrl);
            $('#filePreviewLoading').hide();
            $('#filePreviewContent').show();
            $('#previewPdf').show();
        } else {
            $('#filePreviewLoading').hide();
            $('#filePreviewContent').show();
            $('#previewUnsupported').show();
            $('#previewDownloadLink').attr('href', fileUrl);
        }
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            'valid': '<span class="badge badge-success"><i class="fas fa-check"></i> Valid</span>',
            'invalid': '<span class="badge badge-danger"><i class="fas fa-times"></i> Invalid</span>',
            'revision': '<span class="badge badge-info"><i class="fas fa-redo"></i> Revisi</span>'
        };
        return badges[status] || `<span class="badge badge-secondary">${status}</span>`;
    }

    function getVerifikasiInfo(dok) {
        if (dok.verified_by_name) {
            const date = new Date(dok.verified_at).toLocaleString('id-ID');
            return `<i class="fas fa-user"></i> ${dok.verified_by_name}<br><i class="fas fa-clock"></i> ${date}`;
        }
        return '<span class="text-muted">Belum diverifikasi</span>';
    }

    function getActionButtons(dok) {
        let buttons = '';
        
        // Preview button - always show if file exists
        if (dok.file_path) {
            const fileUrl = dok.file_url || `/storage/${dok.file_path}`;
            buttons += `
                <button class="btn btn-primary btn-sm" onclick="previewFile('${fileUrl}', '${dok.mime_type}', '${dok.nama_dokumen_lengkap}')" title="Preview">
                    <i class="fas fa-eye"></i>
                </button>
            `;
        }
        
        return buttons;
    }

    // Dynamic Gelombang filter based on Jalur selection
    $('#jalur_filter').on('change', function() {
        var selectedJalurId = $(this).val();
        var gelombangSelect = $('#gelombang_filter');
        
        // Reset gelombang selection
        gelombangSelect.val('all');
        
        // Show/hide gelombang options based on selected jalur
        gelombangSelect.find('option').each(function() {
            var jalurId = $(this).data('jalur-id');
            if (!jalurId) {
                // "Semua Gelombang" option - always show
                $(this).show();
            } else if (!selectedJalurId || selectedJalurId === 'all') {
                // No jalur selected - show all
                $(this).show();
            } else if (jalurId === selectedJalurId) {
                // Jalur matches - show
                $(this).show();
            } else {
                // Jalur doesn't match - hide
                $(this).hide();
            }
        });
        
        // Auto-submit form after filtering gelombang options
        $('#filterForm').submit();
    });
    
    // Auto-submit for other dropdowns (status, per_page, gelombang)
    $('.auto-submit').not('#jalur_filter').on('change', function() {
        $('#filterForm').submit();
    });
    
    // Auto-submit for search input with debounce (wait 500ms after typing stops)
    var searchTimeout;
    $('#search_filter').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Initialize gelombang filter on page load
    $(document).ready(function() {
        var selectedJalurId = $('#jalur_filter').val();
        if (selectedJalurId && selectedJalurId !== 'all') {
            $('#gelombang_filter').find('option').each(function() {
                var jalurId = $(this).data('jalur-id');
                if (jalurId && jalurId !== selectedJalurId) {
                    $(this).hide();
                }
            });
        }
        
        // Live DateTime
        function updateDateTime() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            document.getElementById('live-date').innerHTML = '<i class="far fa-calendar-alt mr-1"></i>' + dayName + ', ' + date + ' ' + month + ' ' + year;
            document.getElementById('live-time').innerHTML = '<i class="far fa-clock mr-1"></i>' + hours + ':' + minutes + ':' + seconds;
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Export Filter Modal - Gelombang filter based on Jalur
        $('#export_jalur_id').on('change', function() {
            var jalurId = $(this).val();
            var gelombangSelect = $('#export_gelombang_id');
            
            gelombangSelect.val('');
            gelombangSelect.find('option').each(function() {
                var optionJalurId = $(this).data('jalur-id');
                if (!optionJalurId || optionJalurId == jalurId || !jalurId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>

{{-- Export Filter Modal --}}
<div class="modal fade" id="exportFilterModal" tabindex="-1" role="dialog" aria-labelledby="exportFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="exportFilterModalLabel">
                    <i class="fas fa-file-excel mr-2"></i>Export Data dengan Filter
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.pendaftar.export') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="export_type"><i class="fas fa-filter mr-1"></i> Tipe Data</label>
                        <select name="type" id="export_type" class="form-control">
                            <option value="all">Semua Pendaftar</option>
                            <option value="with_nomor_tes">Peserta Ujian (Dengan Nomor Tes)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="export_jalur_id"><i class="fas fa-road mr-1"></i> Jalur Pendaftaran</label>
                        <select name="jalur_id" id="export_jalur_id" class="form-control">
                            <option value="">-- Semua Jalur --</option>
                            @foreach($jalurList as $jalur)
                                <option value="{{ $jalur->id }}">
                                    {{ $jalur->nama }} ({{ $jalur->tahunPelajaran?->nama ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="export_gelombang_id"><i class="fas fa-wave-square mr-1"></i> Gelombang</label>
                        <select name="gelombang_id" id="export_gelombang_id" class="form-control">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($gelombangList as $gelombang)
                                <option value="{{ $gelombang->id }}" data-jalur-id="{{ $gelombang->jalur_id }}">
                                    {{ $gelombang->nama }} - {{ $gelombang->jalur?->nama ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data yang diekspor mencakup: Data diri, alamat, orang tua, nilai rapor, dan status pendaftaran.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-download mr-1"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
