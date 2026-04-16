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
    .cbt-badge {
        font-size: 0.78rem;
        padding: 0.35rem 0.55rem;
    }
    .row-cbt-missing {
        background: rgba(255, 193, 7, 0.09);
    }
    .row-cbt-missing:hover {
        background: rgba(255, 193, 7, 0.16) !important;
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
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai TBQ</a></li>
            <li class="breadcrumb-item active">Pengumuman</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="fas fa-layer-group mr-1"></i>
        <strong>Konteks aktif:</strong>
        Tahun {{ $contextInfo['tahun'] }},
        Jalur {{ $contextInfo['jalur'] }},
        Gelombang {{ $contextInfo['gelombang'] }}.
    </div>

    <div class="alert alert-primary">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-envelope-open-text mr-1"></i>
                <strong>One Day Service Kelulusan:</strong>
                Saat admin menetapkan status <code>Diterima</code> atau <code>Ditolak</code>, email notifikasi bisa langsung dikirim dari halaman ini.
                Isi email mengikuti template di Pengaturan Email, jadi setiap perubahan template admin akan ikut dipakai untuk pengiriman berikutnya.
            </div>
            <a href="{{ route('admin.email.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-cog mr-1"></i> Atur Template Email
            </a>
        </div>
    </div>

    <div class="alert alert-warning">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Pengaman CBT:</strong>
                Kandidat yang belum memiliki nilai CBT sekarang ditandai jelas, dan status <code>Diterima</code> akan ditolak otomatis sampai nilai CBT tersedia.
            </div>
            <a href="{{ route('admin.nilai-cbt.moodle-scan', request()->query()) }}" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-cloud-download-alt mr-1"></i> Ambil CBT dari Moodle
            </a>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-seleksi.pengumuman') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajarans as $tahun)
                                <option value="{{ $tahun->id }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ $selectedJalurIdInput == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label>Gelombang</label>
                        <select name="gelombang_id" class="form-control">
                            <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                            @foreach($gelombangs as $gelombang)
                                <option value="{{ $gelombang->id }}" {{ $selectedGelombangIdInput == $gelombang->id ? 'selected' : '' }}>
                                    {{ $gelombang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter mr-1"></i>Terapkan Filter
                    </button>
                    <a href="{{ route('admin.nilai-seleksi.pengumuman') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

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
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['belum_cbt'] }}</h3>
                    <p>Belum Ada Nilai CBT</p>
                </div>
                <div class="icon"><i class="fas fa-laptop-code"></i></div>
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
                            <small class="text-muted d-block mt-1">Template email mengikuti Pengaturan Email dan hanya dikirim untuk status diterima/ditolak.</small>
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
                        <th class="text-center">Nilai CBT</th>
                        <th class="text-center">Status CBT</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kandidat as $index => $cs)
                    <tr data-id="{{ $cs->id }}" data-status="{{ $cs->status_admisi }}" data-cbt-ready="{{ $cs->has_nilai_cbt ? '1' : '0' }}" class="{{ $cs->has_nilai_cbt ? '' : 'row-cbt-missing' }}">
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
                            @if($cs->has_nilai_cbt)
                                <span class="badge badge-info cbt-badge">
                                    {{ number_format((float) ($cs->nilai_cbt_rata ?? 0), 2) }}
                                </span>
                                @if($cs->nilai_cbt_total !== null)
                                    <small class="text-muted d-block">Total {{ number_format((float) $cs->nilai_cbt_total, 2) }}</small>
                                @endif
                            @else
                                <span class="badge badge-danger cbt-badge">Belum ikut CBT</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cs->has_nilai_cbt)
                                <span class="badge badge-success cbt-badge"><i class="fas fa-check-circle mr-1"></i>Sudah ada nilai</span>
                            @else
                                <span class="badge badge-warning cbt-badge"><i class="fas fa-exclamation-circle mr-1"></i>Belum ada nilai</span>
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
                                    onclick="showDetailModal('{{ $cs->id }}', @js($cs->nama_lengkap), '{{ $cs->status_admisi }}', @js($cs->catatan_admisi), '{{ $cs->has_nilai_cbt ? '1' : '0' }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
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
            { orderable: false, targets: [0, 10] }
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
        var sendEmail = $('#kirimEmail').is(':checked');
        var statusLabel = $('[name="status_admisi"]', this).val();

        if (statusLabel === 'diterima') {
            var missingCbt = $('.kandidat-check:checked').filter(function() {
                return $(this).closest('tr').data('cbt-ready') != 1;
            }).length;

            if (missingCbt > 0) {
                e.preventDefault();
                alert('Ada ' + missingCbt + ' kandidat terpilih yang belum memiliki nilai CBT. Status diterima tidak dapat diterapkan sebelum nilai CBT tersedia.');
                return false;
            }
        }

        var confirmMessage = 'Yakin ingin mengubah status ' + $('.kandidat-check:checked').length + ' kandidat menjadi ' + statusLabel + '?';

        if (sendEmail && (statusLabel === 'diterima' || statusLabel === 'ditolak')) {
            confirmMessage += '\n\nSistem juga akan mencoba mengirim email notifikasi hasil seleksi sesuai template yang aktif.';
        }

        return confirm(confirmMessage);
    });
});

// Show detail modal
function showDetailModal(id, nama, status, catatan, hasCbt) {
    $('#modalNama').val(nama);
    $('#modalStatus').val(status);
    $('#modalCatatan').val(catatan || '');
    $('#modalKirimEmail').prop('checked', true);
    $('#updateForm').data('cbt-ready', hasCbt === '1');
    $('#updateForm').attr('action', '{{ url("admin/nilai-seleksi/update-admisi") }}/' + id);
    $('#updateModal').modal('show');
}

$('#updateForm').on('submit', function(e) {
    var status = $('#modalStatus').val();
    var sendEmail = $('#modalKirimEmail').is(':checked');

    if (status === 'diterima' && !$(this).data('cbt-ready')) {
        e.preventDefault();
        alert('Pendaftar ini belum memiliki nilai CBT. Status diterima tidak dapat disimpan sebelum nilai CBT tersedia.');
        return false;
    }

    var message = 'Simpan perubahan status admisi untuk pendaftar ini?';

    if (sendEmail && (status === 'diterima' || status === 'ditolak')) {
        message += '\n\nEmail notifikasi juga akan dikirim sesuai template aktif.';
    }

    if (!confirm(message)) {
        e.preventDefault();
    }
});
</script>
@stop
