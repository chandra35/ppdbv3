@extends('adminlte::page')

@section('title', 'Kelola Jadwal PPDB')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-calendar-alt mr-2"></i>Kelola Jadwal PPDB</h1>
        <a href="{{ route('admin.settings.jadwal.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Jadwal
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Jadwal</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $jadwals->count() }} Jadwal</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($jadwals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Kegiatan</th>
                                    <th width="180">Tanggal</th>
                                    <th width="100" class="text-center">Status</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwals as $index => $jadwal)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: {{ $jadwal->warna ?: '#6c757d' }}; color: white;">
                                            {{ $jadwal->urutan }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $jadwal->nama_kegiatan }}</strong>
                                        @if($jadwal->keterangan)
                                            <br><small class="text-muted">{{ Str::limit($jadwal->keterangan, 50) }}</small>
                                        @endif
                                        @if($jadwal->is_ongoing)
                                            <br><span class="badge badge-success"><i class="fas fa-play-circle mr-1"></i>Sedang Berlangsung</span>
                                        @elseif($jadwal->is_upcoming)
                                            <br><span class="badge badge-info"><i class="fas fa-clock mr-1"></i>Akan Datang</span>
                                        @elseif($jadwal->is_past)
                                            <br><span class="badge badge-secondary"><i class="fas fa-check-circle mr-1"></i>Selesai</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar mr-1 text-muted"></i>
                                        {{ $jadwal->tanggal_range }}
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.settings.jadwal.toggle-status', $jadwal) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-status-toggle {{ $jadwal->is_active ? 'active' : 'inactive' }}" data-toggle="tooltip" title="{{ $jadwal->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                                <i class="fas fa-{{ $jadwal->is_active ? 'check' : 'times' }} mr-1"></i>
                                                {{ $jadwal->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.settings.jadwal.edit', $jadwal) }}" class="btn btn-action-edit" data-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.settings.jadwal.destroy', $jadwal) }}" method="POST" class="d-inline action-form" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action-delete" data-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada jadwal</p>
                        <a href="{{ route('admin.settings.jadwal.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Tambah Jadwal Pertama
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Timeline Preview --}}
            <div class="card card-outline card-success">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-stream mr-1"></i> Preview Timeline</h3>
                </div>
                <div class="card-body">
                    @if($jadwals->where('is_active', true)->count() > 0)
                    <div class="timeline">
                        @foreach($jadwals->where('is_active', true) as $jadwal)
                        <div class="time-label">
                            <span class="bg-{{ $jadwal->is_ongoing ? 'success' : ($jadwal->is_upcoming ? 'info' : 'secondary') }}">
                                {{ $jadwal->tanggal_mulai->format('d M') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-circle" style="color: {{ $jadwal->warna ?: '#007bff' }};"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">{{ $jadwal->nama_kegiatan }}</h3>
                                @if($jadwal->keterangan)
                                <div class="timeline-body small">
                                    {{ Str::limit($jadwal->keterangan, 80) }}
                                </div>
                                @endif
                                <div class="timeline-footer small text-muted">
                                    {{ $jadwal->tanggal_range }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted">
                        <i class="fas fa-eye-slash fa-2x mb-2"></i>
                        <p class="small">Tidak ada jadwal aktif</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Info --}}
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Info</h3>
                </div>
                <div class="card-body small">
                    <ul class="mb-0 pl-3">
                        <li><strong>Urutan:</strong> Menentukan posisi di timeline</li>
                        <li><strong>Warna:</strong> Untuk membedakan kategori jadwal</li>
                        <li><strong>Status Aktif:</strong> Hanya jadwal aktif yang tampil di halaman depan</li>
                        <li>Status "Sedang Berlangsung" otomatis berdasarkan tanggal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .timeline {
        margin: 0;
        padding: 0;
        position: relative;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #dee2e6;
        left: 18px;
    }
    .timeline > div {
        position: relative;
        margin-bottom: 15px;
    }
    .timeline > div > .timeline-item {
        margin-left: 45px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .timeline > div > i {
        position: absolute;
        left: 7px;
        top: 0;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        font-size: 10px;
        text-align: center;
        line-height: 26px;
        background: white;
        border: 2px solid #dee2e6;
    }
    .time-label > span {
        padding: 3px 8px;
        border-radius: 4px;
        color: white;
        font-size: 11px;
        font-weight: bold;
    }
    .timeline-header {
        font-size: 13px;
        font-weight: 600;
        margin: 0 0 5px 0;
    }
    .timeline-body {
        padding: 5px 0;
    }
    .timeline-footer {
        font-size: 11px;
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
