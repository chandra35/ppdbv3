@extends('adminlte::page')

@section('title', 'Manajemen Verifikator')

@section('content_header')
    <h1><i class="fas fa-user-check mr-2"></i>Manajemen Verifikator</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Verifikator PPDB</h3>
        <div class="card-tools">
            @if($availableUsers->count() > 0)
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahVerifikator">
                    <i class="fas fa-plus"></i> Tambah Verifikator
                </button>
            @else
                <button class="btn btn-secondary btn-sm" disabled title="Semua user sudah menjadi verifikator">
                    <i class="fas fa-info-circle"></i> Tidak ada user tersedia
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(!$activePpdbSettings)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Belum ada PPDB Settings yang aktif. Silakan aktifkan PPDB Settings terlebih dahulu.
            </div>
        @endif

        @if($verifikators->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="verifikatorTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama User</th>
                            <th>Email</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($verifikators as $index => $verifikator)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $verifikator->user->name ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    @if($verifikator->user->email)
                                        <a href="mailto:{{ $verifikator->user->email }}">
                                            {{ $verifikator->user->email }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($verifikator->is_active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-times"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('admin.verifikator.toggle-status', $verifikator->id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $verifikator->is_active ? 'btn-warning' : 'btn-success' }}"
                                                    title="{{ $verifikator->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas fa-{{ $verifikator->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-toggle="modal" 
                                                data-target="#modalHapus{{ $verifikator->id }}"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus -->
                                    <div class="modal fade" id="modalHapus{{ $verifikator->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h5 class="modal-title text-white">
                                                        <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus verifikator:</p>
                                                    <p class="font-weight-bold">{{ $verifikator->user->name ?? 'N/A' }}</p>
                                                    <p class="text-muted small">
                                                        <i class="fas fa-info-circle"></i> 
                                                        Verifikator yang sudah memverifikasi dokumen tidak dapat dihapus.
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <form action="{{ route('admin.verifikator.delete', $verifikator->id) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Belum ada verifikator yang ditambahkan. Klik tombol "Tambah Verifikator" untuk menambahkan User sebagai verifikator.
            </div>
        @endif
    </div>
    <div class="card-footer">
        <small class="text-muted">
            <i class="fas fa-info-circle"></i> 
            Verifikator adalah User (GTK/Admin/Operator) yang bertugas memverifikasi dokumen pendaftar PPDB.
        </small>
    </div>
</div>

<!-- Modal Tambah Verifikator -->
<div class="modal fade" id="modalTambahVerifikator" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-plus"></i> Tambah Verifikator
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.verifikator.assign') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="user_id">Pilih User <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Pilih User --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Hanya user yang belum menjadi verifikator yang ditampilkan.
                        </small>
                    </div>

                    @if($availableUsers->count() == 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Semua user sudah ditambahkan sebagai verifikator.
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tip:</strong> Gunakan menu <a href="{{ route('admin.gtk.index') }}" target="_blank">Data GTK</a> 
                        untuk mendaftarkan GTK dari SIMANSA sebagai user PPDB terlebih dahulu.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" @if($availableUsers->count() == 0) disabled @endif>
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection {
        height: calc(2.25rem + 2px) !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#verifikatorTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[0, 'asc']],
            pageLength: 25,
            responsive: true
        });
User
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: '-- Pilih User --',
            allowClear: true,
            dropdownParent: $('#modalTambahVerifikator')
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@stop
