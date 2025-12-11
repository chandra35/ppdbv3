@extends('adminlte::page')

@section('title', 'Detail Activity Log')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-history"></i> Detail Activity Log</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">Activity Log</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Log</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $log->id }}</td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td>{{ $log->created_at->format('d F Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>
                                @if($log->user)
                                    <a href="{{ route('admin.users.show', $log->user->id) }}">
                                        {{ $log->user->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Action</th>
                            <td><span class="badge badge-{{ $log->action_badge }}">{{ ucfirst($log->action) }}</span></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $log->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Model Type</th>
                            <td><code>{{ $log->model_type ?? '-' }}</code></td>
                        </tr>
                        <tr>
                            <th>Model ID</th>
                            <td><code>{{ $log->model_id ?? '-' }}</code></td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>User Agent</th>
                            <td><small>{{ $log->user_agent ?? '-' }}</small></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if($log->old_values)
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Old Values</h3>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            @if($log->new_values)
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title text-white">New Values</h3>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop
