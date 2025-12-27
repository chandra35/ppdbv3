@extends('adminlte::page')

@section('title', 'Data GTK - PPDB Admin')

@section('css')
@include('admin.partials.action-buttons-style')
<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.pagination {
    margin-bottom: 0;
}
.page-link {
    padding: 0.375rem 0.75rem;
}
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-users-cog"></i> Data GTK
            </h1>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                @if($source === 'simansa')
                    <span class="badge badge-primary badge-lg mr-2">
                        <i class="fas fa-database"></i> Mode: SIMANSA
                    </span>
                @else
                    <span class="badge badge-secondary badge-lg mr-2">
                        <i class="fas fa-server"></i> Mode: Local
                    </span>
                @endif
                @if($simansaAvailable)
                    <form action="{{ route('admin.gtk.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary" id="syncBtn">
                            <i class="fas fa-sync-alt"></i> Sync
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.gtk.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Manual
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')

@if($simansaAvailable && isset($syncStats))
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-sync-alt"></i> Informasi Sinkronisasi Data GTK
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-primary">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total GTK</span>
                        <span class="info-box-number">{{ $syncStats['total'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dari SIMANSA</span>
                        <span class="info-box-number">{{ $syncStats['synced'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-pen"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Input Manual</span>
                        <span class="info-box-number">{{ $syncStats['manual'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Terakhir Sync</span>
                        <span class="info-box-number" style="font-size: 0.9rem;">{{ $syncStats['last_sync'] ?? 'Belum Sync' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter & Cari GTK</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.gtk.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Cari</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Nama, NIP, Email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                <div class="form-group">
                    <label for="kategori_ptk">Kategori PTK</label>
                    <select name="kategori_ptk" id="kategori_ptk" class="form-control">
                        <option value="">Semua</option>
                        @foreach($kategoriPtks as $kategori)
                            <option value="{{ $kategori }}" {{ request('kategori_ptk') == $kategori ? 'selected' : '' }}>
                                {{ $kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="jenis_ptk">Jenis PTK</label>
                        <select name="jenis_ptk" id="jenis_ptk" class="form-control">
                            <option value="">Semua</option>
                            @foreach($jenisPtks as $jenis)
                                <option value="{{ $jenis }}" {{ request('jenis_ptk') == $jenis ? 'selected' : '' }}>
                                    {{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="source">Source</label>
                        <select name="source" id="source" class="form-control">
                            <option value="">Semua</option>
                            <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="simansa" {{ request('source') == 'simansa' ? 'selected' : '' }}>SIMANSA</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Register Card -->
<div class="card card-outline card-info collapsed-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users-cog mr-2"></i>Registrasi Massal GTK
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.gtk.bulk-register') }}" method="POST" id="bulkRegisterForm">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="role_id">Role yang Diberikan <span class="text-danger">*</span></label>
                        <select name="role_id" id="role_id" class="form-control select2" required>
                            <option value="">Pilih Role</option>
                            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="default_password">Password Default</label>
                        <input type="text" name="default_password" id="default_password" 
                               class="form-control" placeholder="Default: ppdb123">
                        <small class="text-muted">Kosongkan untuk menggunakan password default: ppdb123</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-info btn-block" id="bulkRegisterBtn" disabled>
                            <i class="fas fa-user-plus"></i> Registrasi GTK Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>
            <div id="selectedGtkIds"></div>
        </form>
    </div>
</div>

<!-- GTK List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Daftar GTK 
            <span class="badge badge-info ml-2">{{ $gtks->total() }}</span>
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="bg-gradient-navy">
                    <tr>
                        <th width="50" class="text-center" style="color: white;">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" id="selectAll">
                                <label for="selectAll"></label>
                            </div>
                        </th>
                        <th style="color: white;">Nama Lengkap</th>
                        <th width="150" style="color: white;">NIP</th>
                        <th style="color: white;">Email</th>
                        <th style="color: white;">Jabatan</th>
                        <th width="100" class="text-center" style="color: white;">Source</th>
                        <th width="120" class="text-center" style="color: white;">Status</th>
                        <th width="160" class="text-center" style="color: white;">Aksi</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($gtks as $gtk)
                    @php
                        $isRegistered = in_array($gtk->email, $existingEmails);
                        $ppdbUser = $isRegistered ? \App\Models\User::where('email', $gtk->email)->first() : null;
                    @endphp
                    <tr>
                        <td class="text-center">
                            @if(!$isRegistered)
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox" class="gtk-checkbox" id="gtk_{{ $gtk->id }}"
                                           value="{{ $gtk->id }}" data-name="{{ $gtk->nama_lengkap }}">
                                    <label for="gtk_{{ $gtk->id }}"></label>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $gtk->nama_lengkap }}</strong>
                            @if($gtk->jenis_kelamin)
                                <br><small class="text-muted">
                                    <i class="fas fa-{{ $gtk->jenis_kelamin == 'L' ? 'mars text-primary' : 'venus text-danger' }}"></i>
                                    {{ $gtk->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <code class="text-dark">{{ $gtk->nip ?? '-' }}</code>
                        </td>
                        <td>
                            <small>{{ $gtk->email }}</small>
                        </td>
                        <td>
                            {{ $gtk->jabatan ?? '-' }}
                            @if($gtk->kategori_ptk)
                                <br><span class="badge badge-secondary badge-sm">{{ $gtk->kategori_ptk }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($gtk->source === 'manual')
                                <span class="badge badge-warning">
                                    <i class="fas fa-pencil-alt"></i> Manual
                                </span>
                            @else
                                <span class="badge badge-info">
                                    <i class="fas fa-database"></i> SIMANSA
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isRegistered)
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Terdaftar
                                </span>
                                @if($ppdbUser && $ppdbUser->roles->count() > 0)
                                    <br>
                                    @foreach($ppdbUser->roles as $role)
                                        <small class="badge badge-secondary mt-1">{{ $role->display_name }}</small>
                                    @endforeach
                                @endif
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fas fa-times"></i> Belum
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.gtk.show', $gtk->id) }}" 
                                   class="btn btn-info" data-toggle="tooltip" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($gtk->source === 'manual')
                                    <a href="{{ route('admin.gtk.edit', $gtk->id) }}" 
                                       class="btn btn-warning" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deleteGtk('{{ $gtk->id }}', '{{ $gtk->nama_lengkap }}')"
                                            data-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                
                                @if($isRegistered)
                                    <button type="button" class="btn btn-primary" 
                                            onclick="editRoles('{{ $gtk->id }}')" data-toggle="tooltip" title="Edit Role">
                                        <i class="fas fa-user-cog"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success" 
                                            onclick="registerGtk('{{ $gtk->id }}')" data-toggle="tooltip" title="Daftarkan">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Tidak ada data GTK</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($gtks->hasPages())
        <div class="card-footer clearfix">
            <div class="float-left">
                <p class="text-sm text-muted mb-0">
                    Menampilkan {{ $gtks->firstItem() ?? 0 }} sampai {{ $gtks->lastItem() ?? 0 }} dari {{ $gtks->total() }} data
                </p>
            </div>
            <div class="float-right">
                {{ $gtks->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="registerForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Daftarkan GTK sebagai User PPDB</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="gtkInfo" class="alert alert-info mb-3"></div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="roles[]" class="form-control select2" multiple required>
                            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih satu atau lebih role untuk user ini</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" placeholder="Default: ppdb123">
                        <small class="text-muted">Kosongkan untuk menggunakan password default</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Daftarkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Roles Modal -->
<div class="modal fade" id="editRolesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRolesForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role User PPDB</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="editGtkInfo" class="alert alert-info mb-3"></div>
                    
                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select name="roles[]" id="editRolesSelect" class="form-control select2" multiple required>
                            @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger float-left" onclick="removeUser()">
                        <i class="fas fa-user-times"></i> Hapus User
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove User Form (Hidden) -->
<form id="removeUserForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@stop

@section('css')\n@include('admin.partials.action-buttons-style')
<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* Checkbox Styling */
.icheck-primary {
    display: inline-block;
    position: relative;
}
.icheck-primary input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}
.icheck-primary label {
    position: relative;
    padding-left: 25px;
    margin-bottom: 0;
    cursor: pointer;
}
.icheck-primary label:before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    border: 2px solid #adb5bd;
    border-radius: 3px;
    background-color: #fff;
    transition: all 0.2s;
}
.icheck-primary input[type="checkbox"]:checked + label:before {
    background-color: #007bff;
    border-color: #007bff;
}
.icheck-primary input[type="checkbox"]:checked + label:after {
    content: '';
    position: absolute;
    left: 6px;
    top: 50%;
    transform: translateY(-50%) rotate(45deg);
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
}
.icheck-primary input[type="checkbox"]:hover + label:before {
    border-color: #007bff;
}

.pagination {
    margin-bottom: 0;
}
.page-link {
    padding: 0.375rem 0.75rem;
}
.card-footer.clearfix {
    background-color: #f8f9fa;
}

/* Table Styling - Navy Header */
.bg-gradient-navy {
    background: linear-gradient(to bottom, #001f3f 0%, #003366 100%) !important;
}
.bg-gradient-navy th {
    font-weight: 700 !important;
    text-transform: uppercase !important;
    font-size: 0.8rem !important;
    letter-spacing: 0.5px !important;
    padding: 0.75rem 0.5rem !important;
    border-color: #003366 !important;
}
.table-bordered thead th {
    border-bottom: 2px solid #003366 !important;
}
.table-bordered td {
    vertical-align: middle;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
.badge-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
.btn-group-sm > .btn {
    padding: 0.35rem 0.5rem;
    font-size: 0.875rem;
}
.table-sm td, .table-sm th {
    padding: 0.5rem;
}
</style>
@stop

@section('js')
<script>
let currentGtkId = null;

// Sync button loading state
$('#syncBtn').closest('form').on('submit', function(e) {
    const btn = $('#syncBtn');
    btn.prop('disabled', true);
    btn.html('<i class="fas fa-spinner fa-spin"></i> Syncing...');
    
    // Show loading overlay
    Swal.fire({
        title: 'Syncing dari SIMANSA...',
        html: 'Mohon tunggu, sedang menyinkronkan data GTK',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

// Delete GTK function
function deleteGtk(id, name) {
    Swal.fire({
        title: 'Hapus GTK?',
        html: `Yakin ingin menghapus <strong>${name}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = $('<form>', {
                'method': 'POST',
                'action': `{{ url('admin/gtk') }}/${id}`
            });
            form.append('{{ csrf_field() }}');
            form.append('<input type="hidden" name="_method" value="DELETE">');
            $('body').append(form);
            form.submit();
        }
    });
}

// Select All Checkbox
$('#selectAll').on('change', function() {
    $('.gtk-checkbox').prop('checked', $(this).prop('checked'));
    updateBulkRegister();
});

// Individual Checkbox
$(document).on('change', '.gtk-checkbox', function() {
    updateBulkRegister();
});

function updateBulkRegister() {
    const checked = $('.gtk-checkbox:checked');
    const count = checked.length;
    
    $('#selectedCount').text(count);
    $('#bulkRegisterBtn').prop('disabled', count === 0);
    
    // Update hidden inputs
    $('#selectedGtkIds').empty();
    checked.each(function() {
        $('#selectedGtkIds').append(
            `<input type="hidden" name="gtk_ids[]" value="${$(this).val()}">`
        );
    });
}

function registerGtk(id) {
    currentGtkId = id;
    $('#registerForm').attr('action', `{{ url('admin/gtk') }}/${id}/register`);
    
    // Get GTK info via AJAX or from table
    const row = $(`input[value="${id}"]`).closest('tr');
    const name = row.find('td:eq(1) strong').text();
    const email = row.find('td:eq(4)').text();
    
    $('#gtkInfo').html(`<strong>${name}</strong><br>Email: ${email}`);
    $('#registerModal select[name="roles[]"]').val(null).trigger('change');
    $('#registerModal input[name="password"]').val('');
    $('#registerModal').modal('show');
}

function editRoles(id) {
    currentGtkId = id;
    $('#editRolesForm').attr('action', `{{ url('admin/gtk') }}/${id}/update-roles`);
    
    // Get GTK info from table
    const row = $(`button[onclick="editRoles('${id}')"]`).closest('tr');
    const name = row.find('td:eq(1) strong').text();
    const email = row.find('td:eq(4)').text();
    const rolesBadges = row.find('td:eq(6) .badge-info');
    
    $('#editGtkInfo').html(`<strong>${name}</strong><br>Email: ${email}`);
    
    // Get current roles from badges (need to fetch from server ideally)
    // For now, clear selection
    $('#editRolesSelect').val(null).trigger('change');
    
    $('#editRolesModal').modal('show');
}

function removeUser() {
    if (!currentGtkId) return;
    
    Swal.fire({
        title: 'Hapus User PPDB?',
        text: 'User akan dihapus dari sistem PPDB. Data GTK di SIMANSA tidak akan terpengaruh.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#removeUserForm').attr('action', `{{ url('admin/gtk') }}/${currentGtkId}/remove`);
            $('#removeUserForm').submit();
        }
    });
}

// Bulk register confirmation
$('#bulkRegisterForm').on('submit', function(e) {
    const count = $('.gtk-checkbox:checked').length;
    
    if (!$('#role_id').val()) {
        e.preventDefault();
        Swal.fire('Error', 'Pilih role terlebih dahulu!', 'error');
        return false;
    }
    
    e.preventDefault();
    
    Swal.fire({
        title: `Registrasi ${count} GTK?`,
        text: 'GTK yang dipilih akan didaftarkan sebagai user PPDB dengan role yang ditentukan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Daftarkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
