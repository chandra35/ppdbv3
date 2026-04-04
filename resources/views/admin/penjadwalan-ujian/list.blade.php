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
<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
    <div>
        Daftar jadwal ujian sedang memakai konteks:
        Tahun <strong>{{ $contextInfo['tahun'] ?? ($tahunAktif?->nama ?? 'Semua Tahun') }}</strong>,
        Jalur <strong>{{ $contextInfo['jalur'] ?? 'Semua Jalur' }}</strong>,
        Gelombang <strong>{{ $contextInfo['gelombang'] ?? 'Semua Gelombang' }}</strong>.
    </div>
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
                <label class="mr-2">Jalur:</label>
                <select name="jalur_id" class="form-control form-control-sm">
                    <option value="all" {{ ($selectedJalurIdInput ?? '') === 'all' ? 'selected' : '' }}>-- Semua --</option>
                    @foreach($jalurList ?? [] as $jalur)
                    <option value="{{ $jalur->id }}" {{ (string) ($selectedJalurIdInput ?? '') === (string) $jalur->id ? 'selected' : '' }}>
                        {{ $jalur->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Gelombang:</label>
                <select name="gelombang_id" class="form-control form-control-sm">
                    <option value="all" {{ ($selectedGelombangIdInput ?? '') === 'all' ? 'selected' : '' }}>-- Semua --</option>
                    @foreach($gelombangList ?? [] as $gelombang)
                    <option value="{{ $gelombang->id }}" {{ (string) ($selectedGelombangIdInput ?? '') === (string) $gelombang->id ? 'selected' : '' }}>
                        {{ $gelombang->nama }}
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
            <a href="{{ route('admin.penjadwalan-ujian.list', ['tahun_pelajaran_id' => $tahunAktif?->id]) }}" class="btn btn-sm btn-secondary ml-2">
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
                                <span class="info-box-text small">TBQ</span>
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
                        <button type="button" class="btn btn-danger" title="Hapus" 
                                data-toggle="modal" data-target="#deleteModal{{ $jadwal->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                        @else
                        <button type="button" class="btn btn-secondary" title="Terkunci" disabled>
                            <i class="fas fa-lock"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Delete Modal for each jadwal --}}
    @if($jadwal->status !== 'locked')
    <div class="modal fade" id="deleteModal{{ $jadwal->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash mr-2"></i>Hapus Jadwal</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan!
                    </div>
                    <p>Apakah Anda yakin ingin menghapus jadwal berikut?</p>
                    <ul class="mb-0">
                        <li><strong>Tanggal:</strong> {{ $jadwal->tanggal_ujian->isoFormat('dddd, D MMMM Y') }}</li>
                        <li><strong>Peserta:</strong> {{ number_format($jadwal->total_peserta) }} orang</li>
                        <li><strong>Sesi:</strong> {{ $jadwal->total_sesi }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <form method="POST" action="{{ route('admin.penjadwalan-ujian.destroy', $jadwal) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $jadwalList->links() }}
</div>
@endif
@endsection
