@extends('adminlte::page')

@section('title', 'Sesi Ujian Seleksi')

@section('css')
<style>
    .status-badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
    }
    .session-card {
        transition: all 0.3s ease;
    }
    .session-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .progress-mini {
        height: 6px;
        border-radius: 3px;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-clipboard-list mr-2"></i>Sesi Ujian Seleksi
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Sesi Ujian</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="fas fa-layer-group mr-1"></i>
        <strong>Konteks aktif:</strong>
        Tahun {{ $contextInfo['tahun'] }},
        Jalur {{ $contextInfo['jalur'] }},
        Gelombang {{ $contextInfo['gelombang'] }}.
    </div>

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

    <!-- Filter -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sesi-ujian.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control select2">
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ $selectedTahunIdInput == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Jalur</label>
                            <select name="jalur_id" class="form-control">
                                <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>-- Semua Jalur --</option>
                                @foreach($jalurs as $jalur)
                                    <option value="{{ $jalur->id }}" {{ $selectedJalurIdInput == $jalur->id ? 'selected' : '' }}>
                                        {{ $jalur->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Gelombang</label>
                            <select name="gelombang_id" class="form-control">
                                <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>-- Semua --</option>
                                @foreach($gelombangs as $gelombang)
                                    <option value="{{ $gelombang->id }}" {{ $selectedGelombangIdInput == $gelombang->id ? 'selected' : '' }}>
                                        {{ $gelombang->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Terkunci</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Berlangsung</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('admin.sesi-ujian.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-sync mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info -->
    <div class="callout callout-info">
        <h5><i class="fas fa-info-circle mr-2"></i>Informasi</h5>
        <p class="mb-0">
            Sesi ujian dibuat otomatis dari menu <a href="{{ route('admin.penjadwalan-ujian.index') }}"><strong>Penjadwalan Ujian</strong></a>.
            Setelah jadwal disimpan & dikunci, sesi ujian akan muncul di sini. Anda dapat menugaskan penguji ke setiap ruangan.
        </p>
    </div>

    <!-- Sesi List -->
    @if($sesiUjians->count() > 0)
        <div class="row">
            @foreach($sesiUjians as $sesi)
                <div class="col-md-6 col-lg-4">
                    <div class="card session-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-2"></i>{{ $sesi->nama }}
                            </h3>
                            <div class="card-tools">
                                @if($sesi->status == 'draft')
                                    <span class="badge badge-secondary status-badge">Draft</span>
                                @elseif($sesi->status == 'locked')
                                    <span class="badge badge-warning status-badge">Terkunci</span>
                                @elseif($sesi->status == 'in_progress')
                                    <span class="badge badge-primary status-badge">Berlangsung</span>
                                @else
                                    <span class="badge badge-success status-badge">Selesai</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-3">
                                <tr>
                                    <td width="40%"><i class="fas fa-graduation-cap mr-2 text-muted"></i>Tahun</td>
                                    <td>: {{ $sesi->tahunPelajaran->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-road mr-2 text-muted"></i>Jalur</td>
                                    <td>: {{ $sesi->jalur->nama ?? '-' }}</td>
                                </tr>
                                @if($sesi->gelombang)
                                <tr>
                                    <td><i class="fas fa-wave-square mr-2 text-muted"></i>Gelombang</td>
                                    <td>: {{ $sesi->gelombang->nama }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><i class="fas fa-clock mr-2 text-muted"></i>Waktu</td>
                                    <td>: {{ $sesi->tanggal?->format('d/m/Y') }} {{ $sesi->waktu_mulai }}</td>
                                </tr>
                            </table>

                            <!-- Progress -->
                            <div class="mb-3">
                                <small class="text-muted">Progress Penilaian</small>
                                @php
                                    $totalPeserta = $sesi->ruangan ? $sesi->ruangan->sum(fn($r) => $r->peserta->count()) : 0;
                                    $sudahDinilai = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $sesi->id)
                                        ->whereIn('status', ['submitted', 'verified'])
                                        ->count();
                                    $percentage = $totalPeserta > 0 ? round(($sudahDinilai / $totalPeserta) * 100) : 0;
                                @endphp
                                <div class="progress progress-mini mt-1">
                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                </div>
                                <small>{{ $sudahDinilai }} / {{ $totalPeserta }} peserta ({{ $percentage }}%)</small>
                            </div>

                            <!-- Stats -->
                            <div class="row text-center">
                                <div class="col-4 border-right">
                                    <h5 class="mb-0">{{ $sesi->ruangan ? $sesi->ruangan->count() : 0 }}</h5>
                                    <small class="text-muted">Ruangan</small>
                                </div>
                                <div class="col-4 border-right">
                                    <h5 class="mb-0">{{ $totalPeserta }}</h5>
                                    <small class="text-muted">Peserta</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0">{{ $sesi->pengujiRuang ? $sesi->pengujiRuang->unique('user_id')->count() : 0 }}</h5>
                                    <small class="text-muted">Penguji</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.sesi-ujian.show', $sesi->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                            @if(in_array($sesi->status, ['locked', 'in_progress']))
                                <a href="{{ route('admin.sesi-ujian.print-daftar-hadir', $sesi->id) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </a>
                            @endif
                            @if(in_array($sesi->status, ['draft', 'locked']))
                                <button type="button" class="btn btn-sm btn-danger float-right btn-delete" 
                                        data-id="{{ $sesi->id }}" data-nama="{{ $sesi->nama }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $sesiUjians->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5>Belum Ada Sesi Ujian</h5>
                <p class="text-muted">
                    Buat sesi ujian melalui menu <a href="{{ route('admin.penjadwalan-ujian.index') }}">Penjadwalan Ujian</a>
                </p>
            </div>
        </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i class="fas fa-trash mr-2"></i>Hapus Sesi Ujian</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus sesi ujian <strong id="deleteName"></strong>?</p>
                    <p class="text-danger mb-0"><small>Semua data ruangan dan peserta di sesi ini akan ikut terhapus.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Delete button
    $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        
        $('#deleteName').text(nama);
        $('#deleteForm').attr('action', '{{ route("admin.sesi-ujian.index") }}/' + id);
        $('#deleteModal').modal('show');
    });
});
</script>
@stop
