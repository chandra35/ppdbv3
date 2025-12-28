@extends('adminlte::page')

@section('title', 'Hapus Data Pendaftar')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Hapus Data Pendaftar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Hapus Data Pendaftar</li>
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

    <!-- Warning Alert -->
    <div class="alert alert-warning">
        <h5><i class="icon fas fa-exclamation-triangle"></i> Peringatan!</h5>
        Halaman ini untuk menghapus data pendaftar secara aman (soft delete). Data yang dihapus akan dipindah ke <strong>Data Terhapus</strong> dan masih bisa di-restore.
    </div>

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
            <form method="GET" action="{{ route('admin.data.delete-list') }}">
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
                            <label>Status Verifikasi</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="diverifikasi" {{ request('status') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
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
                        <a href="{{ route('admin.data.delete-list') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card card-outline card-warning">
        <div class="card-header bg-warning">
            <h3 class="card-title">
                <i class="fas fa-user-minus"></i> Daftar Pendaftar Aktif
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">{{ $pendaftars->total() }} Data</span>
            </div>
        </div>
        <div class="card-body">
            @if($pendaftars->count() > 0)
                <!-- Bulk Actions -->
                <div class="mb-3">
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteBulk()">
                        <i class="fas fa-trash"></i> Hapus Terpilih
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
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendaftars as $index => $pendaftar)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="checkbox-item" value="{{ $pendaftar->id }}">
                                    </td>
                                    <td>{{ $pendaftars->firstItem() + $index }}</td>
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
                                        @if($pendaftar->status_verifikasi == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($pendaftar->status_verifikasi == 'diproses')
                                            <span class="badge badge-info">Diproses</span>
                                        @elseif($pendaftar->status_verifikasi == 'diverifikasi')
                                            <span class="badge badge-success">Diverifikasi</span>
                                        @elseif($pendaftar->status_verifikasi == 'ditolak')
                                            <span class="badge badge-danger">Ditolak</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $pendaftar->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-xs" onclick="deleteSingle('{{ $pendaftar->id }}', '{{ $pendaftar->nama_lengkap }}')" title="Hapus">
                                            <i class="fas fa-trash"></i> Hapus
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
                    {{ $pendaftars->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    Tidak ada data pendaftar.
                </div>
            @endif
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

    // Delete single record
    async function deleteSingle(id, name) {
        const { value: reason } = await Swal.fire({
            title: 'Hapus Pendaftar?',
            html: `
                <div class="text-left">
                    <p>Data <strong>${name}</strong> akan dipindah ke <strong>Data Terhapus</strong> dan masih bisa di-restore.</p>
                    <div class="form-group mt-3">
                        <label>Alasan (opsional):</label>
                        <textarea id="deleteReason" class="form-control" rows="3" placeholder="Alasan penghapusan..."></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            reverseButtons: true,
            preConfirm: () => {
                return document.getElementById('deleteReason').value;
            }
        });

        if (reason !== undefined) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/pendaftar/' + id;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            if (reason) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason;
                form.appendChild(reasonInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete bulk
    async function deleteBulk() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire('Error', 'Pilih minimal 1 data untuk dihapus', 'error');
            return;
        }

        const { value: reason } = await Swal.fire({
            title: `Hapus ${ids.length} Pendaftar?`,
            html: `
                <div class="text-left">
                    <p><strong>${ids.length} data</strong> akan dipindah ke <strong>Data Terhapus</strong> dan masih bisa di-restore.</p>
                    <div class="form-group mt-3">
                        <label>Alasan (opsional):</label>
                        <textarea id="bulkDeleteReason" class="form-control" rows="3" placeholder="Alasan penghapusan..."></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus Semua',
            preConfirm: () => {
                return document.getElementById('bulkDeleteReason').value;
            }
        });

        if (reason !== undefined) {
            // Submit each delete
            let deleteCount = 0;
            for (const id of ids) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/pendaftar/' + id;
                form.style.display = 'none';
                
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="reason" value="${reason || 'Hapus massal'}">
                `;
                
                document.body.appendChild(form);
                
                // Only submit the last form to trigger page reload
                if (deleteCount === ids.length - 1) {
                    form.submit();
                } else {
                    // Submit via AJAX for others
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form)
                    });
                }
                deleteCount++;
            }
        }
    }
</script>
@stop
