@extends('adminlte::page')

@section('title', 'Data Terhapus - Kelola Data')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Data Terhapus</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Terhapus</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-times"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter Data</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form method="GET" action="{{ route('admin.data.deleted') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Gelombang</label>
                            <select name="gelombang_id" class="form-control form-control-sm">
                                <option value="">Semua Gelombang</option>
                                @foreach($gelombangs as $gelombang)
                                    <option value="{{ $gelombang->id }}" {{ request('gelombang_id') == $gelombang->id ? 'selected' : '' }}>
                                        {{ $gelombang->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cari (Nama/NISN)</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama atau NISN..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.data.deleted') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trash-restore"></i> Daftar Data Terhapus
            </h3>
            <div class="card-tools">
                <span class="badge badge-danger">{{ $deletedData->total() }} Data</span>
            </div>
        </div>
        <div class="card-body">
            @if($deletedData->count() > 0)
                <!-- Bulk Actions -->
                <div class="mb-3">
                    <button type="button" class="btn btn-success btn-sm" onclick="restoreBulk()">
                        <i class="fas fa-undo"></i> Restore Terpilih
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="forceDeleteBulk()">
                        <i class="fas fa-trash-alt"></i> Hapus Permanen Terpilih
                    </button>
                    <div class="float-right">
                        <label class="mb-0">
                            <input type="checkbox" id="checkAll"> Pilih Semua
                        </label>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="checkAllHeader"></th>
                                <th width="50">No</th>
                                <th>Nama Lengkap</th>
                                <th>NISN</th>
                                <th>Gelombang</th>
                                <th>Dihapus Oleh</th>
                                <th>Tanggal Hapus</th>
                                <th>Alasan</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedData as $index => $pendaftar)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="checkbox-item" value="{{ $pendaftar->id }}">
                                    </td>
                                    <td>{{ $deletedData->firstItem() + $index }}</td>
                                    <td>{{ $pendaftar->nama_lengkap }}</td>
                                    <td>{{ $pendaftar->nisn }}</td>
                                    <td>
                                        @if($pendaftar->gelombangPendaftaran)
                                            <span class="badge badge-info">
                                                {{ $pendaftar->gelombangPendaftaran->nama }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pendaftar->deletedBy)
                                            {{ $pendaftar->deletedBy->name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $pendaftar->deleted_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $pendaftar->deleted_reason ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-xs" onclick="restore('{{ $pendaftar->id }}')" title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="forceDelete('{{ $pendaftar->id }}', '{{ $pendaftar->nama_lengkap }}')" title="Hapus Permanen">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-info btn-xs" title="Lihat Detail" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $deletedData->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    Tidak ada data terhapus.
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Delete by Gelombang Card (Danger Zone) -->
    <div class="card card-outline card-danger collapsed-card">
        <div class="card-header bg-danger">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i> DANGER ZONE - Hapus Massal Berdasarkan Gelombang
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Peringatan!</strong> Tindakan ini akan melakukan soft delete pada semua data pendaftar di gelombang yang dipilih. Data masih bisa di-restore dari menu ini.
            </div>
            <form id="bulkDeleteGelombangForm" onsubmit="return bulkDeleteGelombang(event)">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pilih Gelombang <span class="text-danger">*</span></label>
                            <select name="gelombang_id" id="gelombang_bulk_delete" class="form-control" required>
                                <option value="">-- Pilih Gelombang --</option>
                                @foreach($gelombangs as $gelombang)
                                    <option value="{{ $gelombang->id }}">{{ $gelombang->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Alasan</label>
                            <input type="text" name="reason" class="form-control" placeholder="Alasan penghapusan...">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus Semua Data Gelombang
                </button>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    .table-sm td, .table-sm th {
        padding: 0.25rem;
        font-size: 0.875rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.75rem;
        }
        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.7rem;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Check All functionality
    $('#checkAll, #checkAllHeader').change(function() {
        $('.checkbox-item').prop('checked', $(this).prop('checked'));
    });

    $('.checkbox-item').change(function() {
        if ($('.checkbox-item:checked').length == $('.checkbox-item').length) {
            $('#checkAll, #checkAllHeader').prop('checked', true);
        } else {
            $('#checkAll, #checkAllHeader').prop('checked', false);
        }
    });

    // Get selected IDs
    function getSelectedIds() {
        return $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();
    }

    // Restore single record
    function restore(id) {
        Swal.fire({
            title: 'Restore Data?',
            text: 'Data akan dikembalikan ke daftar pendaftar aktif',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Restore',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.data.restore", "") }}/' + id;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Restore bulk
    function restoreBulk() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire('Error', 'Pilih minimal 1 data untuk restore', 'error');
            return;
        }

        Swal.fire({
            title: `Restore ${ids.length} data?`,
            text: 'Data akan dikembalikan ke daftar pendaftar aktif',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Restore'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.data.restore.bulk") }}';
                form.innerHTML = '@csrf';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Force delete single (Permanent delete with double confirmation)
    async function forceDelete(id, name) {
        const { value: confirm1 } = await Swal.fire({
            title: 'HAPUS PERMANEN?',
            html: `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Data <strong>${name}</strong> akan <strong>DIHAPUS PERMANEN</strong> dan <strong>TIDAK BISA DIKEMBALIKAN!</strong>
                </div>
                <p class="mb-0">Ketik <code class="bg-dark text-white px-2 py-1">HAPUS</code> untuk konfirmasi</p>
            `,
            input: 'text',
            inputPlaceholder: 'Ketik: HAPUS',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'HAPUS PERMANEN',
            cancelButtonText: 'Batal',
            preConfirm: (value) => {
                if (value !== 'HAPUS') {
                    Swal.showValidationMessage('Ketik "HAPUS" untuk konfirmasi');
                    return false;
                }
                return true;
            }
        });

        if (confirm1) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.data.force.delete", "") }}/' + id;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Force delete bulk
    async function forceDeleteBulk() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire('Error', 'Pilih minimal 1 data untuk hapus permanen', 'error');
            return;
        }

        const { value: confirm1 } = await Swal.fire({
            title: 'HAPUS PERMANEN?',
            html: `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>${ids.length} data</strong> akan <strong>DIHAPUS PERMANEN</strong> dan <strong>TIDAK BISA DIKEMBALIKAN!</strong>
                </div>
                <p class="mb-0">Ketik <code class="bg-dark text-white px-2 py-1">HAPUS</code> untuk konfirmasi</p>
            `,
            input: 'text',
            inputPlaceholder: 'Ketik: HAPUS',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'HAPUS PERMANEN',
            preConfirm: (value) => {
                if (value !== 'HAPUS') {
                    Swal.showValidationMessage('Ketik "HAPUS" untuk konfirmasi');
                    return false;
                }
                return true;
            }
        });

        if (confirm1) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.data.force.delete.bulk") }}';
            form.innerHTML = '@csrf @method("DELETE")';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Bulk delete by gelombang
    async function bulkDeleteGelombang(e) {
        e.preventDefault();
        
        const gelombangSelect = document.getElementById('gelombang_bulk_delete');
        const gelombangName = gelombangSelect.options[gelombangSelect.selectedIndex].text;
        
        if (!gelombangSelect.value) {
            Swal.fire('Error', 'Pilih gelombang terlebih dahulu', 'error');
            return false;
        }

        const { value: confirm } = await Swal.fire({
            title: 'Hapus Semua Data?',
            html: `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Semua data pendaftar di gelombang <strong>${gelombangName}</strong> akan dihapus (soft delete).
                </div>
                <p>Data masih bisa di-restore dari menu Data Terhapus.</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal'
        });

        if (confirm) {
            document.getElementById('bulkDeleteGelombangForm').submit();
        }

        return false;
    }
</script>
@stop
