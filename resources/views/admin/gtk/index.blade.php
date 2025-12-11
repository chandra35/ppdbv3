@extends('adminlte::page')

@section('title', 'Data GTK - PPDB Admin')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users-cog mr-2"></i>Data GTK dari SIMANSA</h1>
    </div>
@stop

@section('content')
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
        <form action="{{ route('admin.gtk.index') }}" method="GET" class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="search">Cari</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Nama, NIP, NUPTK, Email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="kategori_ptk">Kategori PTK</label>
                    <select name="kategori_ptk" id="kategori_ptk" class="form-control select2">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriPtks as $kategori)
                            <option value="{{ $kategori }}" {{ request('kategori_ptk') == $kategori ? 'selected' : '' }}>
                                {{ $kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="jenis_ptk">Jenis PTK</label>
                    <select name="jenis_ptk" id="jenis_ptk" class="form-control select2">
                        <option value="">Semua Jenis</option>
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
                    <label>&nbsp;</label>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i>
                        </a>
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
            Daftar GTK 
            <span class="badge badge-info">{{ $gtks->total() }} data</span>
        </h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="selectAll" class="form-check-input">
                    </th>
                    <th>Nama Lengkap</th>
                    <th>NIP</th>
                    <th>NUPTK</th>
                    <th>Email</th>
                    <th>Jabatan</th>
                    <th>Status PPDB</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gtks as $gtk)
                    @php
                        $isRegistered = in_array($gtk->email, $existingEmails);
                        $ppdbUser = $isRegistered ? \App\Models\User::where('email', $gtk->email)->first() : null;
                    @endphp
                    <tr>
                        <td>
                            @if(!$isRegistered)
                                <input type="checkbox" class="form-check-input gtk-checkbox" 
                                       value="{{ $gtk->id }}" data-name="{{ $gtk->nama_lengkap }}">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $gtk->nama_lengkap }}</strong>
                            @if($gtk->jenis_kelamin)
                                <br><small class="text-muted">{{ $gtk->jenis_kelamin_label }}</small>
                            @endif
                        </td>
                        <td>{{ $gtk->nip ?? '-' }}</td>
                        <td>{{ $gtk->nuptk ?? '-' }}</td>
                        <td>{{ $gtk->email }}</td>
                        <td>
                            {{ $gtk->jabatan ?? '-' }}
                            @if($gtk->kategori_ptk)
                                <br><small class="badge badge-secondary">{{ $gtk->kategori_ptk }}</small>
                            @endif
                        </td>
                        <td>
                            @if($isRegistered)
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Terdaftar
                                </span>
                                @if($ppdbUser && $ppdbUser->roles->count() > 0)
                                    <br>
                                    @foreach($ppdbUser->roles as $role)
                                        <small class="badge badge-info">{{ $role->display_name }}</small>
                                    @endforeach
                                @endif
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fas fa-times"></i> Belum Terdaftar
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.gtk.show', $gtk->id) }}" 
                                   class="btn btn-action-view" data-toggle="tooltip" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($isRegistered)
                                    <button type="button" class="btn btn-action-edit" 
                                            onclick="editRoles('{{ $gtk->id }}')" data-toggle="tooltip" title="Edit Role">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-action-success" 
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
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data GTK ditemukan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($gtks->hasPages())
        <div class="card-footer">
            {{ $gtks->appends(request()->query())->links() }}
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

@section('css')
<style>
.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>
@stop

@section('js')
<script>
let currentGtkId = null;

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
