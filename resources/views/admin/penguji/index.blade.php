@extends('adminlte::page')

@section('title', 'Manajemen Penguji')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .penguji-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .gtk-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .dual-list-container {
        display: flex;
        gap: 20px;
    }
    .dual-list-box {
        flex: 1;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }
    .dual-list-header {
        padding: 12px 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }
    .dual-list-header.gtk {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .dual-list-search {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    .dual-list-content {
        max-height: 400px;
        overflow-y: auto;
    }
    .dual-list-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dual-list-item:hover {
        background-color: #f8f9fa;
    }
    .dual-list-item.selected {
        background-color: #e3f2fd;
    }
    .dual-list-item .info {
        flex: 1;
    }
    .dual-list-item .name {
        font-weight: 600;
        color: #333;
    }
    .dual-list-item .meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .dual-list-actions {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
    }
    .dual-list-actions .btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #dc3545; }
    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    .checkbox-gtk {
        width: 20px;
        height: 20px;
    }
    .role-badge {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-user-tie mr-2"></i>Manajemen Penguji
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penguji</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info stats-card">
                <div class="inner">
                    <h3 id="statTotal">{{ $stats['total'] }}</h3>
                    <p>Total Penguji</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success stats-card">
                <div class="inner">
                    <h3 id="statActive">{{ $stats['active'] }}</h3>
                    <p>Penguji Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning stats-card">
                <div class="inner">
                    <h3>{{ $stats['assigned_today'] }}</h3>
                    <p>Ditugaskan Saat Ini</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-olive stats-card">
                <div class="inner">
                    <h3 id="statAvailable">{{ $availableGtk->count() }}</h3>
                    <p>GTK Tersedia</p>
                </div>
                <div class="icon"><i class="fas fa-user-plus"></i></div>
            </div>
        </div>
    </div>

    <!-- Dual Listbox Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt mr-2"></i>Assign GTK sebagai Penguji
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="password" class="form-control" id="defaultPassword" placeholder="Password default" value="ppdb123">
                    <div class="input-group-append">
                        <span class="input-group-text" title="Password untuk user baru">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column: Available GTK -->
                <div class="col-md-5">
                    <div class="dual-list-box">
                        <div class="dual-list-header gtk">
                            <i class="fas fa-users-cog mr-2"></i>Data GTK Tersedia
                            <span class="badge badge-light float-right" id="gtkCount">{{ $availableGtk->count() }}</span>
                        </div>
                        <div class="dual-list-search">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="searchGtk" placeholder="Cari GTK...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="selectAllGtk">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="dual-list-content" id="gtkList">
                            @forelse($availableGtk as $gtk)
                                <div class="dual-list-item" data-id="{{ $gtk->id }}">
                                    <input type="checkbox" class="checkbox-gtk gtk-checkbox" value="{{ $gtk->id }}">
                                    <div class="gtk-avatar">{{ strtoupper(substr($gtk->nama_lengkap, 0, 2)) }}</div>
                                    <div class="info">
                                        <div class="name">{{ $gtk->nama_lengkap }}</div>
                                        <div class="meta">
                                            {{ $gtk->nip ?? '-' }} • {{ $gtk->jabatan ?? 'GTK' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-user-check"></i>
                                    <p>Semua GTK sudah menjadi penguji</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Center: Action Buttons -->
                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="dual-list-actions">
                        <button type="button" class="btn btn-success" id="btnAssign" title="Jadikan Penguji" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="button" class="btn btn-danger" id="btnRemove" title="Hapus Role Penguji" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                </div>

                <!-- Right Column: Assigned Penguji -->
                <div class="col-md-5">
                    <div class="dual-list-box">
                        <div class="dual-list-header">
                            <i class="fas fa-user-tie mr-2"></i>Penguji Terdaftar
                            <span class="badge badge-light float-right" id="pengujiCount">{{ $pengujiList->total() }}</span>
                        </div>
                        <div class="dual-list-search">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="searchPenguji" placeholder="Cari penguji...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="selectAllPenguji">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="dual-list-content" id="pengujiList">
                            @forelse($pengujiList as $penguji)
                                <div class="dual-list-item" data-id="{{ $penguji->id }}">
                                    <input type="checkbox" class="checkbox-gtk penguji-checkbox" value="{{ $penguji->id }}">
                                    <div class="penguji-avatar">{{ strtoupper(substr($penguji->name, 0, 2)) }}</div>
                                    <div class="info">
                                        <div class="name">
                                            {{ $penguji->name }}
                                            @if(!$penguji->is_active)
                                                <span class="status-indicator status-inactive ml-1" title="Nonaktif"></span>
                                            @endif
                                        </div>
                                        <div class="meta">{{ $penguji->email }}</div>
                                    </div>
                                    <a href="{{ route('admin.penguji.show', $penguji) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-user-plus"></i>
                                    <p>Belum ada penguji terdaftar</p>
                                    <small>Pilih GTK dari kiri dan klik tombol <i class="fas fa-chevron-right"></i></small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <i class="fas fa-info-circle mr-1"></i>
            Pilih GTK di kiri, lalu klik <span class="badge badge-success"><i class="fas fa-chevron-right"></i></span> untuk menjadikan penguji.
            Password default: <code id="showPassword">ppdb123</code>
        </div>
    </div>

    <!-- Penguji Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Penguji</h3>
            <div class="card-tools">
                <form action="{{ route('admin.penguji.index') }}" method="GET" class="form-inline">
                    <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Terakhir Ditugaskan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengujiList as $index => $penguji)
                        <tr>
                            <td>{{ $pengujiList->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="penguji-avatar mr-2">{{ strtoupper(substr($penguji->name, 0, 2)) }}</div>
                                    <strong>{{ $penguji->name }}</strong>
                                </div>
                            </td>
                            <td><small>{{ $penguji->email }}</small></td>
                            <td>
                                @foreach($penguji->roles as $role)
                                    <span class="badge {{ $role->name == 'penguji' ? 'badge-primary' : 'badge-secondary' }} role-badge">
                                        {{ $role->display_name }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if($penguji->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Aktif</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $lastAssignment = \App\Models\PengujiRuang::with('sesiUjian')
                                        ->where('user_id', $penguji->id)->latest()->first();
                                @endphp
                                @if($lastAssignment && $lastAssignment->sesiUjian)
                                    <small>{{ $lastAssignment->sesiUjian->tanggal?->format('d M Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.penguji.show', $penguji) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-reset-password" 
                                            data-id="{{ $penguji->id }}" data-name="{{ $penguji->name }}" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <form action="{{ route('admin.penguji.toggle-status', $penguji) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn {{ $penguji->is_active ? 'btn-warning' : 'btn-success' }}"
                                                title="{{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                onclick="return confirm('{{ $penguji->is_active ? 'Nonaktifkan' : 'Aktifkan' }} penguji?')">
                                            <i class="fas {{ $penguji->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-user-tie fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada penguji terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengujiList->hasPages())
            <div class="card-footer">{{ $pengujiList->links() }}</div>
        @endif
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-key mr-2"></i>Reset Password Penguji</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Reset password untuk: <strong id="resetPengujiName"></strong></p>
                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="modalPassword" required minlength="8">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"><i class="fas fa-eye"></i></button>
                                <button type="button" class="btn btn-outline-info btn-generate" title="Generate"><i class="fas fa-random"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation" id="modalPasswordConfirm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Toggle button states based on checkbox selection
    function updateButtonStates() {
        var gtkSelected = $('.gtk-checkbox:checked').length > 0;
        var pengujiSelected = $('.penguji-checkbox:checked').length > 0;
        
        $('#btnAssign').prop('disabled', !gtkSelected);
        $('#btnRemove').prop('disabled', !pengujiSelected);
    }

    // GTK item click to toggle checkbox
    $(document).on('click', '#gtkList .dual-list-item', function(e) {
        if (!$(e.target).is('input[type="checkbox"]')) {
            var checkbox = $(this).find('.gtk-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
        $(this).toggleClass('selected', $(this).find('.gtk-checkbox').is(':checked'));
        updateButtonStates();
    });

    // Penguji item click to toggle checkbox
    $(document).on('click', '#pengujiList .dual-list-item', function(e) {
        if (!$(e.target).is('input[type="checkbox"]') && !$(e.target).closest('a').length) {
            var checkbox = $(this).find('.penguji-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
        $(this).toggleClass('selected', $(this).find('.penguji-checkbox').is(':checked'));
        updateButtonStates();
    });

    // Select All GTK
    $('#selectAllGtk').on('click', function() {
        var allChecked = $('.gtk-checkbox:visible').length === $('.gtk-checkbox:visible:checked').length;
        $('.gtk-checkbox:visible').prop('checked', !allChecked).each(function() {
            $(this).closest('.dual-list-item').toggleClass('selected', !allChecked);
        });
        updateButtonStates();
    });

    // Select All Penguji
    $('#selectAllPenguji').on('click', function() {
        var allChecked = $('.penguji-checkbox:visible').length === $('.penguji-checkbox:visible:checked').length;
        $('.penguji-checkbox:visible').prop('checked', !allChecked).each(function() {
            $(this).closest('.dual-list-item').toggleClass('selected', !allChecked);
        });
        updateButtonStates();
    });

    // Search GTK
    $('#searchGtk').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        $('#gtkList .dual-list-item').each(function() {
            var text = $(this).find('.name').text().toLowerCase() + ' ' + $(this).find('.meta').text().toLowerCase();
            $(this).toggle(text.indexOf(search) > -1);
        });
    });

    // Search Penguji
    $('#searchPenguji').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        $('#pengujiList .dual-list-item').each(function() {
            var text = $(this).find('.name').text().toLowerCase() + ' ' + $(this).find('.meta').text().toLowerCase();
            $(this).toggle(text.indexOf(search) > -1);
        });
    });

    // Assign GTK as Penguji
    $('#btnAssign').on('click', function() {
        var selectedIds = [];
        $('.gtk-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Pilih GTK terlebih dahulu.');
            return;
        }

        var password = $('#defaultPassword').val() || 'ppdb123';

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("admin.penguji.assign") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                gtk_ids: selectedIds,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.default_password) {
                        toastr.info('Password: ' + response.default_password, 'Info Login', {timeOut: 10000});
                    }
                    // Reload page to refresh lists
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            },
            complete: function() {
                $('#btnAssign').prop('disabled', false).html('<i class="fas fa-chevron-right"></i>');
            }
        });
    });

    // Remove Penguji Role
    $('#btnRemove').on('click', function() {
        var selectedIds = [];
        $('.penguji-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Pilih penguji terlebih dahulu.');
            return;
        }

        if (!confirm('Hapus role penguji dari ' + selectedIds.length + ' user terpilih?')) {
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        var removePromises = selectedIds.map(function(userId) {
            return $.ajax({
                url: '{{ route("admin.penguji.remove") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId
                }
            });
        });

        Promise.all(removePromises.map(p => p.catch(e => e)))
            .then(function(results) {
                var success = results.filter(r => r.success).length;
                var errors = results.filter(r => !r.success).length;

                if (success > 0) {
                    toastr.success(success + ' penguji berhasil dihapus role-nya.');
                }
                if (errors > 0) {
                    toastr.warning(errors + ' penguji gagal dihapus (mungkin masih punya tugas aktif).');
                }

                setTimeout(function() {
                    location.reload();
                }, 1500);
            })
            .finally(function() {
                $('#btnRemove').prop('disabled', false).html('<i class="fas fa-chevron-left"></i>');
            });
    });

    // Reset Password Modal
    $('.btn-reset-password').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#resetPengujiName').text(name);
        $('#resetPasswordForm').attr('action', '{{ url("admin/penguji") }}/' + id + '/reset-password');
        $('#modalPassword, #modalPasswordConfirm').val('');
        $('#resetPasswordModal').modal('show');
    });

    // Toggle Password Visibility
    $('.toggle-password').on('click', function() {
        var input = $(this).closest('.input-group').find('input');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate Password
    $('.btn-generate').on('click', function() {
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var password = '';
        for (var i = 0; i < 10; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#modalPassword').val(password).attr('type', 'text');
        $('#modalPasswordConfirm').val(password);
    });

    // Update password display
    $('#defaultPassword').on('input', function() {
        $('#showPassword').text($(this).val() || 'ppdb123');
    });
});
</script>
@stop
