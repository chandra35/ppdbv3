@extends('adminlte::page')

@section('title', 'Backup & Restore')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Backup & Restore</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Backup & Restore</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
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

    <!-- Create Backup Card -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-database"></i> Buat Backup Baru
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Info:</strong> Anda bisa membuat backup database saja atau backup lengkap (database + file). Backup database cocok untuk uji coba admin karena lebih cepat dan ringan.
            </div>
            <form action="{{ route('admin.backup.create') }}" method="POST" id="backupForm">
                @csrf
                <input type="hidden" name="backup_type" id="backup_type" value="database">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-backup" data-backup-type="database">
                        <i class="fas fa-database"></i> Backup Database
                    </button>
                    <button type="submit" class="btn btn-outline-primary btn-backup" data-backup-type="full">
                        <i class="fas fa-box-open"></i> Backup Lengkap
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Backup List Card -->
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Riwayat Backup
            </h3>
            <div class="card-tools">
                <span class="badge badge-success">{{ count($backups) }} Backup</span>
            </div>
        </div>
        <div class="card-body">
            @if(count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama File</th>
                                <th width="100">Tipe</th>
                                <th width="120">Ukuran</th>
                                <th width="150">Tanggal</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $index => $backup)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-file-archive text-warning"></i>
                                        {{ $backup['name'] }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $backup['type'] === 'Database' ? 'info' : 'success' }}">
                                            {{ $backup['type'] }}
                                        </span>
                                    </td>
                                    <td>{{ $backup['size'] }}</td>
                                    <td>
                                        <small>{{ $backup['date'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.backup.download', $backup['name']) }}" class="btn btn-info btn-sm" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteBackup('{{ $backup['name'] }}')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    Belum ada backup. Buat backup pertama Anda sekarang!
                </div>
            @endif
        </div>
    </div>

    <!-- Restore Info Card (Collapsed) -->
    <div class="card card-outline card-warning collapsed-card">
        <div class="card-header bg-warning">
            <h3 class="card-title">
                <i class="fas fa-upload"></i> Restore dari Backup
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Peringatan!</strong> Fitur restore otomatis belum tersedia. Untuk restore backup:
            </div>
            <ol>
                <li>Download file backup yang diinginkan</li>
                <li>Extract file ZIP</li>
                <li>Import database.sql ke MySQL menggunakan phpMyAdmin atau mysql command</li>
                <li>Copy folder dokumen_pendaftar dan public ke storage/app/</li>
            </ol>
            <div class="alert alert-info mt-3">
                <strong>Command untuk import database:</strong>
                <pre class="mb-0">mysql -u username -p database_name &lt; database.sql</pre>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle backup form submission
    $('#backupForm').submit(function(e) {
        e.preventDefault();
        const backupType = $('#backup_type').val();
        const isDatabaseOnly = backupType === 'database';
        
        Swal.fire({
            title: 'Buat Backup?',
            html: isDatabaseOnly
                ? 'Backup database akan dibuat. Proses ini biasanya lebih cepat.<br><br><strong>Jangan tutup halaman ini!</strong>'
                : 'Backup lengkap (database + file) akan dibuat. Ini mungkin membutuhkan waktu beberapa menit.<br><br><strong>Jangan tutup halaman ini!</strong>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Buat Backup',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const formData = new FormData(document.getElementById('backupForm'));
                return fetch('{{ route("admin.backup.create") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: isDatabaseOnly ? 'Backup database selesai dibuat. Halaman akan dimuat ulang.' : 'Backup lengkap selesai dibuat. Halaman akan dimuat ulang.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });

    $('.btn-backup').on('click', function() {
        $('#backup_type').val($(this).data('backup-type'));
    });

    // Delete backup
    function deleteBackup(filename) {
        Swal.fire({
            title: 'Hapus Backup?',
            html: `File backup <strong>${filename}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/backup/${filename}`;
                
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
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@stop
