@extends('adminlte::page')

@section('title', 'Pengumuman Hasil Seleksi')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.4.0/css/select.bootstrap4.min.css">
<style>
    .status-badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }
    .status-diterima { background-color: #28a745; color: #fff; }
    .status-ditolak { background-color: #dc3545; color: #fff; }
    .status-cadangan { background-color: #ffc107; color: #000; }
    .status-pending { background-color: #6c757d; color: #fff; }
    .rank-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .rank-1 { background: gold; color: #000; }
    .rank-2 { background: silver; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }
    .selection-count {
        font-size: 1.1rem;
        font-weight: bold;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-bullhorn mr-2"></i>Pengumuman Hasil Seleksi
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai Seleksi</a></li>
            <li class="breadcrumb-item active">Pengumuman</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Kandidat</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['diterima'] }}</h3>
                    <p>Diterima</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['ditolak'] }}</h3>
                    <p>Ditolak</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Belum Diumumkan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Card -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Aksi Massal</h3>
        </div>
        <div class="card-body">
            <form id="bulkForm" method="POST" action="{{ route('admin.nilai-seleksi.bulk-update-admisi') }}">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label>Status Admisi</label>
                            <select name="status_admisi" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="diterima">✅ Diterima</option>
                                <option value="ditolak">❌ Ditolak</option>
                                <option value="cadangan">⏳ Cadangan</option>
                                <option value="pending">🔄 Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label>Catatan (opsional)</label>
                            <input type="text" name="catatan_admisi" class="form-control" placeholder="Catatan untuk pendaftar...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="kirim_email" value="1" class="custom-control-input" id="kirimEmail" checked>
                                <label class="custom-control-label" for="kirimEmail">
                                    <i class="fas fa-envelope mr-1"></i> Kirim Email
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block" disabled id="bulkSubmit">
                            <i class="fas fa-paper-plane mr-1"></i> Terapkan ke <span class="selection-count">0</span> Terpilih
                        </button>
                    </div>
                </div>
                <div id="selectedIds"></div>
            </form>
        </div>
    </div>

    <!-- Kandidat Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Kandidat</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-info" id="selectAllBtn">
                    <i class="fas fa-check-double mr-1"></i> Pilih Semua Pending
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="clearAllBtn">
                    <i class="fas fa-times mr-1"></i> Bersihkan Pilihan
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="kandidatTable" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="width: 50px;">#</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>Jalur</th>
                        <th>No. Tes</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kandidat as $index => $cs)
                    <tr data-id="{{ $cs->id }}" data-status="{{ $cs->status_admisi }}">
                        <td class="text-center">
                            <input type="checkbox" class="kandidat-check" value="{{ $cs->id }}">
                        </td>
                        <td class="text-center">
                            @if($index < 3 && $cs->nilai_akhir)
                                <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td>
                            <strong>{{ $cs->nama_lengkap }}</strong>
                            @if($cs->jenis_kelamin)
                                <span class="badge badge-{{ $cs->jenis_kelamin == 'L' ? 'primary' : 'pink' }} ml-1">
                                    {{ $cs->jenis_kelamin == 'L' ? 'L' : 'P' }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $cs->nisn }}</td>
                        <td>
                            <span class="badge badge-info">{{ $cs->jalurPendaftaran?->nama ?? '-' }}</span>
                        </td>
                        <td>{{ $cs->nomor_tes ?? '-' }}</td>
                        <td class="text-center">
                            @if($cs->nilai_akhir)
                                <span class="badge badge-{{ $cs->nilai_akhir >= 75 ? 'success' : ($cs->nilai_akhir >= 50 ? 'warning' : 'danger') }}">
                                    {{ number_format($cs->nilai_akhir, 2) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge status-badge status-{{ $cs->status_admisi }}">
                                @switch($cs->status_admisi)
                                    @case('diterima')
                                        <i class="fas fa-check-circle mr-1"></i> Diterima
                                        @break
                                    @case('ditolak')
                                        <i class="fas fa-times-circle mr-1"></i> Ditolak
                                        @break
                                    @case('cadangan')
                                        <i class="fas fa-clock mr-1"></i> Cadangan
                                        @break
                                    @default
                                        <i class="fas fa-hourglass-half mr-1"></i> Pending
                                @endswitch
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="showDetailModal('{{ $cs->id }}', '{{ $cs->nama_lengkap }}', '{{ $cs->status_admisi }}', '{{ $cs->catatan_admisi }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            Tidak ada kandidat yang memenuhi syarat untuk pengumuman.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Update Individual -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateForm" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Update Status Admisi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Pendaftar</label>
                        <input type="text" id="modalNama" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Status Admisi <span class="text-danger">*</span></label>
                        <select name="status_admisi" id="modalStatus" class="form-control" required>
                            <option value="pending">🔄 Pending</option>
                            <option value="diterima">✅ Diterima</option>
                            <option value="ditolak">❌ Ditolak</option>
                            <option value="cadangan">⏳ Cadangan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan_admisi" id="modalCatatan" class="form-control" rows="3" 
                                  placeholder="Catatan untuk pendaftar..."></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="kirim_email" value="1" class="custom-control-input" id="modalKirimEmail" checked>
                            <label class="custom-control-label" for="modalKirimEmail">
                                <i class="fas fa-envelope mr-1"></i> Kirim Email Notifikasi
                            </label>
                        </div>
                        <small class="text-muted">Email hanya dikirim untuk status Diterima atau Ditolak</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#kandidatTable').DataTable({
        responsive: true,
        order: [[6, 'desc']], // Sort by nilai_akhir desc
        columnDefs: [
            { orderable: false, targets: [0, 8] }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        }
    });

    // Update selection count
    function updateSelectionCount() {
        var count = $('.kandidat-check:checked').length;
        $('.selection-count').text(count);
        $('#bulkSubmit').prop('disabled', count === 0);
        
        // Update hidden inputs
        $('#selectedIds').empty();
        $('.kandidat-check:checked').each(function() {
            $('#selectedIds').append('<input type="hidden" name="calon_siswa_ids[]" value="' + $(this).val() + '">');
        });
    }

    // Check all
    $('#checkAll').on('change', function() {
        $('.kandidat-check').prop('checked', $(this).is(':checked'));
        updateSelectionCount();
    });

    // Individual check
    $(document).on('change', '.kandidat-check', function() {
        updateSelectionCount();
        if (!$(this).is(':checked')) {
            $('#checkAll').prop('checked', false);
        }
    });

    // Select all pending
    $('#selectAllBtn').on('click', function() {
        $('tr[data-status="pending"]').find('.kandidat-check').prop('checked', true);
        updateSelectionCount();
    });

    // Clear all
    $('#clearAllBtn').on('click', function() {
        $('.kandidat-check').prop('checked', false);
        $('#checkAll').prop('checked', false);
        updateSelectionCount();
    });

    // Bulk form validation
    $('#bulkForm').on('submit', function(e) {
        if ($('.kandidat-check:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu kandidat!');
            return false;
        }
        if (!$('[name="status_admisi"]', this).val()) {
            e.preventDefault();
            alert('Pilih status admisi!');
            return false;
        }
        return confirm('Yakin ingin mengubah status ' + $('.kandidat-check:checked').length + ' kandidat?');
    });
});

// Show detail modal
function showDetailModal(id, nama, status, catatan) {
    $('#modalNama').val(nama);
    $('#modalStatus').val(status);
    $('#modalCatatan').val(catatan || '');
    $('#updateForm').attr('action', '{{ url("admin/nilai-seleksi/update-admisi") }}/' + id);
    $('#updateModal').modal('show');
}
</script>
@stop
