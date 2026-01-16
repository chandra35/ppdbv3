@extends('adminlte::page')

@section('title', 'Detail Sesi Ujian')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-4-theme@1.0.0/dist/select2-bootstrap-4.min.css" rel="stylesheet" />
<style>
    .ruang-card {
        transition: all 0.3s ease;
    }
    .ruang-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .penguji-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.65rem;
        margin: 0.2rem;
        border-radius: 20px;
        font-size: 0.85rem;
        background: #e9ecef;
    }
    .penguji-tag.ketua {
        background: #ffc107;
        color: #000;
    }
    .penguji-tag .remove-penguji {
        margin-left: 0.5rem;
        cursor: pointer;
        color: #dc3545;
    }
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 38px;
    }
    .progress-sm {
        height: 8px;
    }
    .peserta-list {
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-clipboard-list mr-2"></i>{{ $sesiUjian->nama_sesi }}
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sesi-ujian.index') }}">Sesi Ujian</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
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

    <!-- Info Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Sesi</h3>
                    <div class="card-tools">
                        @if($sesiUjian->status == 'draft')
                            <span class="badge badge-secondary">Draft</span>
                        @elseif($sesiUjian->status == 'locked')
                            <span class="badge badge-warning">Terkunci</span>
                        @elseif($sesiUjian->status == 'in_progress')
                            <span class="badge badge-primary">Berlangsung</span>
                        @else
                            <span class="badge badge-success">Selesai</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tahun Pelajaran</strong></td>
                                    <td>: {{ $sesiUjian->tahunPelajaran->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jalur Pendaftaran</strong></td>
                                    <td>: {{ $sesiUjian->jalurPendaftaran->nama ?? '-' }}</td>
                                </tr>
                                @if($sesiUjian->gelombangPendaftaran)
                                <tr>
                                    <td><strong>Gelombang</strong></td>
                                    <td>: {{ $sesiUjian->gelombangPendaftaran->nama }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal Ujian</strong></td>
                                    <td>: {{ $sesiUjian->tanggal_ujian?->format('d F Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>: {{ $sesiUjian->waktu_mulai ?? '-' }} - {{ $sesiUjian->waktu_selesai ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kapasitas/Ruang</strong></td>
                                    <td>: {{ $sesiUjian->kapasitas_per_ruang }} peserta</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs mr-2"></i>Aksi</h3>
                </div>
                <div class="card-body">
                    @if($sesiUjian->status == 'locked')
                        <form action="{{ route('admin.sesi-ujian.update-status', $sesiUjian->id) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Mulai sesi ujian sekarang?')">
                                <i class="fas fa-play mr-2"></i>Mulai Sesi Ujian
                            </button>
                        </form>
                    @elseif($sesiUjian->status == 'in_progress')
                        <form action="{{ route('admin.sesi-ujian.update-status', $sesiUjian->id) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Selesaikan sesi ujian? Semua nilai akan dikunci.')">
                                <i class="fas fa-check mr-2"></i>Selesaikan Sesi
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.sesi-ujian.print-daftar-hadir', $sesiUjian->id) }}" class="btn btn-info btn-block" target="_blank">
                        <i class="fas fa-print mr-2"></i>Cetak Daftar Hadir
                    </a>

                    <a href="{{ route('admin.nilai-seleksi.show', $sesiUjian->id) }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-chart-bar mr-2"></i>Lihat Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ruangan Cards -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-door-open mr-2"></i>Daftar Ruangan ({{ $sesiUjian->ruangan->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($sesiUjian->ruangan as $ruang)
                    <div class="col-md-6 col-lg-4">
                        <div class="card ruang-card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-door-open mr-2"></i>{{ $ruang->nama_ruang }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Progress -->
                                @php
                                    $totalPeserta = $ruang->peserta->count();
                                    $progress = $ruang->getProgress();
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <small>Progress Penilaian</small>
                                        <small>{{ $progress['dinilai'] }}/{{ $totalPeserta }}</small>
                                    </div>
                                    <div class="progress progress-sm mt-1">
                                        <div class="progress-bar bg-success" style="width: {{ $progress['percentage'] }}%"></div>
                                    </div>
                                </div>

                                <!-- Penguji -->
                                <div class="mb-3">
                                    <label class="mb-1"><strong><i class="fas fa-user-tie mr-1"></i>Penguji:</strong></label>
                                    <div id="penguji-list-{{ $ruang->id }}">
                                        @forelse($ruang->penguji as $pr)
                                            <span class="penguji-tag {{ $pr->is_ketua ? 'ketua' : '' }}">
                                                {{ $pr->user->name ?? 'Unknown' }}
                                                @if($pr->is_ketua)
                                                    <i class="fas fa-star ml-1 text-warning"></i>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-muted">Belum ada penguji</span>
                                        @endforelse
                                    </div>
                                    
                                    @if(in_array($sesiUjian->status, ['locked', 'in_progress']))
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-assign-penguji"
                                                data-ruang-id="{{ $ruang->id }}"
                                                data-ruang-nama="{{ $ruang->nama_ruang }}">
                                            <i class="fas fa-user-plus mr-1"></i>Kelola Penguji
                                        </button>
                                    @endif
                                </div>

                                <!-- Peserta List -->
                                <div>
                                    <label class="mb-1">
                                        <strong><i class="fas fa-users mr-1"></i>Peserta ({{ $totalPeserta }}):</strong>
                                    </label>
                                    <div class="peserta-list border rounded p-2">
                                        <table class="table table-sm table-borderless mb-0">
                                            @forelse($ruang->peserta as $pr)
                                                <tr>
                                                    <td width="30">{{ $pr->nomor_urut }}.</td>
                                                    <td>{{ $pr->calonSiswa->nama_lengkap ?? '-' }}</td>
                                                    <td width="30">
                                                        @php
                                                            $nilai = \App\Models\NilaiSeleksi::where('sesi_ujian_id', $sesiUjian->id)
                                                                ->where('calon_siswa_id', $pr->calon_siswa_id)
                                                                ->first();
                                                        @endphp
                                                        @if($nilai && $nilai->status == 'verified')
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        @elseif($nilai && $nilai->status == 'submitted')
                                                            <i class="fas fa-clock text-warning"></i>
                                                        @else
                                                            <i class="fas fa-minus text-muted"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada peserta</td>
                                                </tr>
                                            @endforelse
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada ruangan</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign Penguji -->
<div class="modal fade" id="assignPengujiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus mr-2"></i>Kelola Penguji - <span id="modalRuangNama"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="assignPengujiForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ruang_ujian_id" id="modalRuangId">
                    
                    <div class="form-group">
                        <label><strong>Pilih Penguji:</strong></label>
                        <select name="penguji_ids[]" id="selectPenguji" class="form-control" multiple="multiple" style="width: 100%">
                            @foreach($pengujiList as $penguji)
                                <option value="{{ $penguji->id }}">
                                    {{ $penguji->name }} ({{ $penguji->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih satu atau lebih penguji untuk ruangan ini</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Ketua Penguji:</strong></label>
                        <select name="ketua_id" id="selectKetua" class="form-control">
                            <option value="">-- Pilih Ketua Penguji --</option>
                        </select>
                        <small class="text-muted">Ketua penguji bertanggung jawab atas jalannya ujian di ruangan</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for penguji
    $('#selectPenguji').select2({
        theme: 'bootstrap4',
        placeholder: 'Cari dan pilih penguji...',
        allowClear: true,
        dropdownParent: $('#assignPengujiModal')
    });

    $('#selectKetua').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih ketua penguji',
        allowClear: true,
        dropdownParent: $('#assignPengujiModal')
    });

    // Update ketua options when penguji selection changes
    $('#selectPenguji').on('change', function() {
        var selected = $(this).select2('data');
        var ketuaSelect = $('#selectKetua');
        var currentKetua = ketuaSelect.val();
        
        ketuaSelect.empty();
        ketuaSelect.append('<option value="">-- Pilih Ketua Penguji --</option>');
        
        selected.forEach(function(item) {
            ketuaSelect.append('<option value="' + item.id + '">' + item.text + '</option>');
        });

        // Restore selection if still valid
        if (currentKetua && selected.find(s => s.id == currentKetua)) {
            ketuaSelect.val(currentKetua).trigger('change.select2');
        }
    });

    // Open modal and load current penguji
    $('.btn-assign-penguji').on('click', function() {
        var ruangId = $(this).data('ruang-id');
        var ruangNama = $(this).data('ruang-nama');
        
        $('#modalRuangId').val(ruangId);
        $('#modalRuangNama').text(ruangNama);
        $('#assignPengujiForm').attr('action', '{{ route("admin.sesi-ujian.assign-penguji", $sesiUjian->id) }}');
        
        // Load current penguji for this ruangan
        $.get('{{ route("admin.sesi-ujian.index") }}/{{ $sesiUjian->id }}/ruangan/' + ruangId + '/penguji', function(data) {
            var pengujiIds = data.penguji.map(p => p.user_id);
            var ketuaId = data.penguji.find(p => p.is_ketua)?.user_id || '';
            
            $('#selectPenguji').val(pengujiIds).trigger('change');
            
            setTimeout(function() {
                $('#selectKetua').val(ketuaId).trigger('change.select2');
            }, 100);
        });
        
        $('#assignPengujiModal').modal('show');
    });
});
</script>
@stop
