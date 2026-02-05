@extends('adminlte::page')

@section('title', 'Daftar Jadwal Ujian')

@section('css')
<style>
    .jadwal-card { transition: all 0.2s; cursor: pointer; }
    .jadwal-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
    .status-locked { border-left: 4px solid #28a745; }
    .status-draft { border-left: 4px solid #6c757d; }
    .status-preview { border-left: 4px solid #ffc107; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-list"></i> Daftar Jadwal Ujian</h1>
        <p class="text-muted mb-0">Semua jadwal ujian yang sudah dibuat</p>
    </div>
    <div class="col-sm-6 text-right">
        <a href="{{ route('admin.penjadwalan-ujian.index') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Jadwal Baru
        </a>
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

{{-- Filter --}}
<div class="card collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Tahun Pelajaran:</label>
                <select name="tahun_pelajaran_id" class="form-control form-control-sm">
                    <option value="">-- Semua --</option>
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id') == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Status:</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Semua --</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Terkunci</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="preview" {{ request('status') == 'preview' ? 'selected' : '' }}>Preview</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.penjadwalan-ujian.list') }}" class="btn btn-sm btn-secondary ml-2">
                <i class="fas fa-undo"></i> Reset
            </a>
        </form>
    </div>
</div>

{{-- Jadwal List --}}
@if($jadwalList->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
        <h4>Belum ada jadwal ujian</h4>
        <p class="text-muted">Silakan buat jadwal baru untuk memulai</p>
        <a href="{{ route('admin.penjadwalan-ujian.index') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Jadwal Baru
        </a>
    </div>
</div>
@else
<div class="row">
    @foreach($jadwalList as $jadwal)
    <div class="col-lg-4 col-md-6">
        <div class="card jadwal-card status-{{ $jadwal->status }}" onclick="window.location='{{ route('admin.penjadwalan-ujian.show', $jadwal) }}'">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ $jadwal->tanggal_ujian->isoFormat('D MMM Y') }}
                </h3>
                @if($jadwal->status === 'locked')
                <span class="badge badge-success"><i class="fas fa-lock mr-1"></i>Terkunci</span>
                @elseif($jadwal->status === 'draft')
                <span class="badge badge-secondary"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                @else
                <span class="badge badge-warning"><i class="fas fa-eye mr-1"></i>Preview</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">
                    <i class="fas fa-graduation-cap mr-2"></i>{{ $jadwal->tahunPelajaran->nama ?? '-' }}
                </p>
                
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small">Peserta</div>
                        <div class="font-weight-bold text-lg">{{ number_format($jadwal->total_peserta) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Sesi</div>
                        <div class="font-weight-bold text-lg">{{ $jadwal->total_sesi }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Ruang</div>
                        <div class="font-weight-bold text-lg">{{ $jadwal->jumlah_ruang_cbt + $jadwal->jumlah_ruang_wawancara }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="info-box bg-success mb-0 py-1 px-2">
                            <div class="info-box-content">
                                <span class="info-box-text small">CBT</span>
                                <span class="info-box-number">{{ $jadwal->jumlah_ruang_cbt }} × {{ $jadwal->kapasitas_cbt }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-warning mb-0 py-1 px-2">
                            <div class="info-box-content">
                                <span class="info-box-text small">Wawancara</span>
                                <span class="info-box-number">{{ $jadwal->jumlah_ruang_wawancara }} × {{ $jadwal->kapasitas_wawancara }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $jadwal->jam_mulai }}
                    </small>
                    <div class="btn-group btn-group-sm" onclick="event.stopPropagation()">
                        <a href="{{ route('admin.penjadwalan-ujian.show', $jadwal) }}" class="btn btn-info" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.penjadwalan-ujian.export.excel', $jadwal) }}" class="btn btn-success" title="Export Excel">
                            <i class="fas fa-file-excel"></i>
                        </a>
                        @if($jadwal->status !== 'locked')
                        <form method="POST" action="{{ route('admin.penjadwalan-ujian.destroy', $jadwal) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" title="Hapus" 
                                    onclick="return confirm('Hapus jadwal ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $jadwalList->links() }}
</div>
@endif
@endsection
