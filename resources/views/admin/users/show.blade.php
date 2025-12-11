@extends('adminlte::page')

@section('title', 'Detail User')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user"></i> Detail User</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                             alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{ $user->name }}</h3>
                    <p class="text-muted text-center">{{ $user->email }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Roles</b>
                            <span class="float-right">
                                @forelse($user->roles as $role)
                                    <span class="badge badge-primary">{{ $role->display_name }}</span>
                                @empty
                                    <span class="badge badge-secondary">No Role</span>
                                @endforelse
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Terdaftar</b>
                            <span class="float-right">{{ $user->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Update Terakhir</b>
                            <span class="float-right">{{ $user->updated_at->diffForHumans() }}</span>
                        </li>
                    </ul>

                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Terbaru</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Action</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->activityLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge badge-{{ $log->action_badge }}">{{ ucfirst($log->action) }}</span></td>
                                <td>{{ Str::limit($log->description, 50) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Belum ada aktivitas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($user->activityLogs->count() > 0)
                <div class="card-footer">
                    <a href="{{ route('admin.logs.index', ['user_id' => $user->id]) }}">Lihat semua aktivitas</a>
                </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permissions</h3>
                </div>
                <div class="card-body">
                    @php
                        $allPerms = [];
                        foreach($user->roles as $role) {
                            $allPerms = array_merge($allPerms, $role->permissions ?? []);
                        }
                        $allPerms = array_unique($allPerms);
                    @endphp

                    @if($user->isAdmin())
                        <span class="badge badge-success badge-lg"><i class="fas fa-star"></i> All Permissions (Admin)</span>
                    @elseif(count($allPerms) > 0)
                        @foreach($allPerms as $perm)
                            <span class="badge badge-primary m-1">{{ $perm }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">Tidak ada permission</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop
