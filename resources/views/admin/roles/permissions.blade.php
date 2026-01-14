@extends('adminlte::page')

@section('title', 'Kelola Permissions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-key"></i> Kelola Permissions</h1>
        <ol class="breadcrumb m-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Permissions</li>
        </ol>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        {{-- Scan & Add Permissions Card --}}
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sync-alt"></i> Scan Permissions</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Scan kode untuk menemukan permission yang digunakan di controller dan view tapi belum terdaftar.
                    </p>
                    <button type="button" class="btn btn-primary btn-block" id="btnScan">
                        <i class="fas fa-search"></i> Scan Permissions
                    </button>
                    
                    <div id="scanResults" class="mt-3" style="display: none;">
                        <div class="alert alert-info mb-2">
                            <strong id="scanCount">0</strong> permission tidak terdaftar ditemukan
                        </div>
                        <div id="scanList" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
                        <button type="button" class="btn btn-success btn-block mt-2" id="btnAddAll" style="display: none;">
                            <i class="fas fa-plus"></i> Tambahkan Semua
                        </button>
                    </div>
                </div>
            </div>

            {{-- Add Manual Permission --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus"></i> Tambah Permission Manual</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="formAddPermission">
                        <div class="form-group">
                            <label>Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" 
                                   placeholder="contoh: finalisasi.view" required>
                            <small class="text-muted">Format: grup.aksi (contoh: finalisasi.view)</small>
                        </div>
                        <div class="form-group">
                            <label>Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control form-control-sm" 
                                   placeholder="contoh: Lihat Finalisasi" required>
                        </div>
                        <div class="form-group">
                            <label>Group <span class="text-danger">*</span></label>
                            <select name="group" class="form-control form-control-sm" required>
                                <option value="">Pilih Group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                @endforeach
                                <option value="__new__">+ Buat Group Baru</option>
                            </select>
                        </div>
                        <div class="form-group" id="newGroupWrapper" style="display: none;">
                            <label>Group Baru</label>
                            <input type="text" name="new_group" class="form-control form-control-sm" 
                                   placeholder="nama_group_baru">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-plus"></i> Tambah Permission
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Permissions List --}}
        <div class="col-lg-8">
            {{-- Hardcoded Permissions --}}
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock"></i> System Permissions (Hardcoded)</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary">Tidak dapat diedit</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="hardcodedAccordion">
                        @foreach($hardcodedPermissions as $group => $permissions)
                            <div class="card mb-0">
                                <div class="card-header p-2" id="heading-{{ $group }}">
                                    <button class="btn btn-link btn-sm text-left w-100 collapsed" type="button" 
                                            data-toggle="collapse" data-target="#collapse-{{ $group }}">
                                        <i class="fas fa-folder mr-2"></i>
                                        <strong>{{ ucfirst($group) }}</strong>
                                        <span class="badge badge-secondary float-right">{{ count($permissions) }}</span>
                                    </button>
                                </div>
                                <div id="collapse-{{ $group }}" class="collapse" data-parent="#hardcodedAccordion">
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tbody>
                                                @foreach($permissions as $name => $displayName)
                                                    <tr>
                                                        <td width="40%"><code>{{ $name }}</code></td>
                                                        <td>{{ $displayName }}</td>
                                                        <td width="10%" class="text-center">
                                                            <span class="badge badge-secondary">System</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Custom Permissions --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Custom Permissions</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $customPermissions->count() }} permission</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($customPermissions->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada custom permission.<br>Gunakan <strong>Scan</strong> atau <strong>Tambah Manual</strong> untuk menambahkan.</p>
                        </div>
                    @else
                        <table class="table table-sm table-hover mb-0" id="customPermissionsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Display Name</th>
                                    <th>Group</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customPermissions as $permission)
                                    <tr data-id="{{ $permission->id }}">
                                        <td><code>{{ $permission->name }}</code></td>
                                        <td>{{ $permission->display_name }}</td>
                                        <td><span class="badge badge-info">{{ $permission->group }}</span></td>
                                        <td>
                                            @if($permission->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-warning btn-edit" 
                                                    data-permission="{{ json_encode($permission) }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-danger btn-delete"
                                                    data-id="{{ $permission->id }}" data-name="{{ $permission->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Permission</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="formEditPermission">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <div class="form-group">
                            <label>Nama Permission</label>
                            <input type="text" id="editName" class="form-control" readonly disabled>
                            <small class="text-muted">Nama tidak dapat diubah</small>
                        </div>
                        <div class="form-group">
                            <label>Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="editDisplayName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Group <span class="text-danger">*</span></label>
                            <select name="group" id="editGroup" class="form-control" required>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="editIsActive" name="is_active" value="1">
                                <label class="custom-control-label" for="editIsActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
    .accordion .card-header {
        background-color: #f4f6f9;
    }
    .accordion .btn-link {
        color: #333;
        text-decoration: none;
    }
    .accordion .btn-link:hover {
        text-decoration: none;
    }
    code {
        color: #e83e8c;
        background-color: #f8f9fa;
        padding: 2px 5px;
        border-radius: 3px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Group select - new group input
    $('select[name="group"]').on('change', function() {
        if ($(this).val() === '__new__') {
            $('#newGroupWrapper').show();
            $('input[name="new_group"]').prop('required', true);
        } else {
            $('#newGroupWrapper').hide();
            $('input[name="new_group"]').prop('required', false);
        }
    });

    // Auto-generate display name from permission name
    $('input[name="name"]').on('blur', function() {
        const name = $(this).val();
        const displayNameInput = $('input[name="display_name"]');
        
        if (name && !displayNameInput.val()) {
            // Generate display name
            const parts = name.split('.');
            if (parts.length >= 2) {
                const actionMap = {
                    'view': 'Lihat', 'create': 'Tambah', 'edit': 'Edit', 'update': 'Update',
                    'delete': 'Hapus', 'export': 'Export', 'import': 'Import', 'print': 'Cetak',
                    'verify': 'Verifikasi', 'approve': 'Setujui', 'reject': 'Tolak',
                    'sync': 'Sinkronisasi', 'manage': 'Kelola', 'clear': 'Hapus Semua'
                };
                const action = parts[parts.length - 1];
                const resource = parts[0].charAt(0).toUpperCase() + parts[0].slice(1).replace(/-/g, ' ');
                const actionText = actionMap[action] || action.charAt(0).toUpperCase() + action.slice(1);
                displayNameInput.val(actionText + ' ' + resource);
            }
            
            // Also set group
            const groupSelect = $('select[name="group"]');
            if (parts.length > 0 && !groupSelect.val()) {
                const group = parts[0];
                if (groupSelect.find(`option[value="${group}"]`).length) {
                    groupSelect.val(group);
                }
            }
        }
    });

    // Scan Permissions
    $('#btnScan').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Scanning...');
        
        $.ajax({
            url: '{{ route("admin.roles.permissions.sync") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                $('#scanResults').show();
                $('#scanCount').text(response.count);
                
                const list = $('#scanList');
                list.empty();
                
                if (response.unregistered.length > 0) {
                    response.unregistered.forEach(function(perm) {
                        list.append(`
                            <label class="list-group-item list-group-item-action">
                                <input type="checkbox" class="mr-2 scan-checkbox" 
                                       data-name="${perm.name}" 
                                       data-display="${perm.display_name}"
                                       data-group="${perm.group}" checked>
                                <code>${perm.name}</code>
                                <small class="text-muted d-block ml-4">${perm.display_name}</small>
                            </label>
                        `);
                    });
                    $('#btnAddAll').show();
                } else {
                    list.append('<div class="text-center text-success p-3"><i class="fas fa-check-circle fa-2x mb-2"></i><br>Semua permission sudah terdaftar!</div>');
                    $('#btnAddAll').hide();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal scan permissions'));
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-search"></i> Scan Permissions');
            }
        });
    });

    // Add All scanned permissions
    $('#btnAddAll').on('click', function() {
        const btn = $(this);
        const permissions = [];
        
        $('.scan-checkbox:checked').each(function() {
            permissions.push({
                name: $(this).data('name'),
                display_name: $(this).data('display'),
                group: $(this).data('group')
            });
        });
        
        if (permissions.length === 0) {
            alert('Pilih minimal satu permission');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menambahkan...');
        
        $.ajax({
            url: '{{ route("admin.roles.permissions.bulk-add") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify({ permissions: permissions }),
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menambahkan permissions'));
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i> Tambahkan Semua');
            }
        });
    });

    // Add Permission Manual
    $('#formAddPermission').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        let data = {
            name: form.find('input[name="name"]').val(),
            display_name: form.find('input[name="display_name"]').val(),
            group: form.find('select[name="group"]').val(),
            description: form.find('textarea[name="description"]').val()
        };
        
        // Handle new group
        if (data.group === '__new__') {
            data.group = form.find('input[name="new_group"]').val();
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '{{ route("admin.roles.permissions.store") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menambahkan permission'));
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i> Tambah Permission');
            }
        });
    });

    // Edit Permission
    $('.btn-edit').on('click', function() {
        const perm = $(this).data('permission');
        $('#editId').val(perm.id);
        $('#editName').val(perm.name);
        $('#editDisplayName').val(perm.display_name);
        $('#editGroup').val(perm.group);
        $('#editDescription').val(perm.description || '');
        $('#editIsActive').prop('checked', perm.is_active);
        $('#editModal').modal('show');
    });

    // Submit Edit
    $('#formEditPermission').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const id = $('#editId').val();
        const btn = form.find('button[type="submit"]');
        
        const data = {
            display_name: $('#editDisplayName').val(),
            group: $('#editGroup').val(),
            description: $('#editDescription').val(),
            is_active: $('#editIsActive').is(':checked')
        };
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '{{ route("admin.roles.permissions.update", "__ID__") }}'.replace('__ID__', id),
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal mengupdate permission'));
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    // Delete Permission
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (!confirm(`Hapus permission "${name}"?`)) {
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.roles.permissions.destroy", "__ID__") }}'.replace('__ID__', id),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menghapus permission'));
            }
        });
    });
});
</script>
@stop
