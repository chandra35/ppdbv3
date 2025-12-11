@extends('adminlte::page')

@section('title', 'Role Management')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag"></i> Role Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Roles</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Role</h3>
            <div class="card-tools">
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Role
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Display Name</th>
                        <th>Deskripsi</th>
                        <th>Jumlah User</th>
                        <th>Permissions</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <code>{{ $role->name }}</code>
                            @if($role->is_system)
                                <span class="badge badge-secondary">System</span>
                            @endif
                        </td>
                        <td>{{ $role->display_name }}</td>
                        <td>{{ Str::limit($role->description, 50) }}</td>
                        <td>
                            <span class="badge badge-info">{{ $role->users_count }} user</span>
                        </td>
                        <td>
                            @php $perms = $role->permissions ?? []; @endphp
                            @if(in_array('*', $perms))
                                <span class="badge badge-success">All Permissions</span>
                            @elseif(count($perms) > 0)
                                <span class="badge badge-primary">{{ count($perms) }} permissions</span>
                            @else
                                <span class="badge badge-secondary">No permissions</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-action-view" data-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$role->is_system)
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-action-edit" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline action-form" onsubmit="return confirm('Yakin hapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-action-delete" data-toggle="tooltip" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada role</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
