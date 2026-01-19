@extends('adminlte::page')

@section('title', 'Log Email')

@section('content_header')
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h1><i class="fas fa-envelope-open-text text-primary"></i> Log Email</h1>
        </div>
        <div class="col-sm-6 text-right">
            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cleanupModal">
                <i class="fas fa-trash"></i> Hapus Log Lama
            </button>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Email</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['sent']) }}</h3>
                    <p>Terkirim</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['failed']) }}</h3>
                    <p>Gagal</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['today']) }}</h3>
                    <p>Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.email-logs.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipe</label>
                            <select name="type" class="form-control form-control-sm">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Email/Nama..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Email Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Riwayat Email</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Waktu</th>
                        <th>Penerima</th>
                        <th>Subject</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $key => $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $key }}</td>
                        <td>
                            <small>{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            <br>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <strong>{{ $log->to_name ?? '-' }}</strong>
                            <br>
                            <small class="text-muted">{{ $log->to_email }}</small>
                        </td>
                        <td>
                            {{ Str::limit($log->subject, 40) }}
                            @if($log->message_preview)
                                <br><small class="text-muted">{{ Str::limit($log->message_preview, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $log->type_label }}</span>
                        </td>
                        <td>
                            {!! $log->status_badge !!}
                            @if($log->status === 'failed' && $log->error_message)
                                <br><small class="text-danger" title="{{ $log->error_message }}">
                                    {{ Str::limit($log->error_message, 30) }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-xs btn-secondary" 
                                    onclick="showDetail('{{ $log->id }}')" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($log->calonSiswa)
                                <a href="{{ route('admin.pendaftar.show', $log->calon_siswa_id) }}" 
                                   class="btn btn-xs btn-info" title="Lihat Pendaftar">
                                    <i class="fas fa-user"></i>
                                </a>
                            @endif
                            @if($log->status === 'failed')
                                <form action="{{ route('admin.email-logs.retry', $log->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-warning" title="Kirim Ulang"
                                            onclick="return confirm('Kirim ulang email ini?')">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    {{-- Hidden data for modal --}}
                    <script type="application/json" id="log-data-{{ $log->id }}">
                        {!! json_encode([
                            'id' => $log->id,
                            'to_email' => $log->to_email,
                            'to_name' => $log->to_name,
                            'subject' => $log->subject,
                            'type' => $log->type,
                            'type_label' => $log->type_label,
                            'status' => $log->status,
                            'status_badge' => $log->status_badge,
                            'error_message' => $log->error_message,
                            'message_preview' => $log->message_preview,
                            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                            'sent_at' => $log->sent_at?->format('d/m/Y H:i:s'),
                            'calon_siswa_nama' => $log->calonSiswa?->nama_lengkap,
                            'calon_siswa_nisn' => $log->calonSiswa?->nisn,
                        ]) !!}
                    </script>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada log email</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    {{-- Cleanup Modal --}}
    <div class="modal fade" id="cleanupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.email-logs.cleanup') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-trash"></i> Hapus Log Lama
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Hapus log yang lebih dari:</label>
                            <select name="days" class="form-control">
                                <option value="7">7 hari</option>
                                <option value="14">14 hari</option>
                                <option value="30" selected>30 hari</option>
                                <option value="60">60 hari</option>
                                <option value="90">90 hari</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Log yang dihapus tidak dapat dikembalikan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Detail Email Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-envelope-open-text"></i> Detail Email
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Left Column - Email Info --}}
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="120"><strong>Status</strong></td>
                                    <td id="detail-status"></td>
                                </tr>
                                <tr>
                                    <td><strong>Tipe</strong></td>
                                    <td><span class="badge badge-info" id="detail-type"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu Dibuat</strong></td>
                                    <td id="detail-created"></td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu Terkirim</strong></td>
                                    <td id="detail-sent"></td>
                                </tr>
                            </table>
                        </div>
                        {{-- Right Column - Recipient Info --}}
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="120"><strong>Penerima</strong></td>
                                    <td id="detail-to-name"></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td id="detail-to-email"></td>
                                </tr>
                                <tr id="detail-calon-row" style="display: none;">
                                    <td><strong>Calon Siswa</strong></td>
                                    <td id="detail-calon"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- Subject --}}
                    <div class="form-group">
                        <label><strong><i class="fas fa-heading mr-1"></i> Subject</strong></label>
                        <div class="bg-light p-2 rounded" id="detail-subject"></div>
                    </div>
                    
                    {{-- Message Preview --}}
                    <div class="form-group" id="detail-preview-group">
                        <label><strong><i class="fas fa-file-alt mr-1"></i> Preview Pesan (sama dengan yang dikirim)</strong></label>
                        <div class="border rounded p-3 bg-white" id="detail-preview" style="max-height: 300px; overflow-y: auto;"></div>
                    </div>
                    
                    {{-- Error Message (if failed) --}}
                    <div class="form-group" id="detail-error-group" style="display: none;">
                        <label><strong><i class="fas fa-exclamation-triangle mr-1 text-danger"></i> Error Message</strong></label>
                        <div class="alert alert-danger mb-0" id="detail-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .small-box .icon {
        font-size: 50px;
    }
    #detail-preview {
        max-height: 300px;
        overflow-y: auto;
    }
    #detail-preview p {
        margin-bottom: 10px;
    }
    #detail-preview ul, #detail-preview ol {
        padding-left: 20px;
    }
</style>
@stop

@section('js')
<script>
function showDetail(logId) {
    // Get log data from hidden JSON
    const dataElement = document.getElementById('log-data-' + logId);
    if (!dataElement) {
        alert('Data tidak ditemukan');
        return;
    }
    
    const log = JSON.parse(dataElement.textContent);
    
    // Fill modal with data
    $('#detail-status').html(log.status_badge);
    $('#detail-type').text(log.type_label);
    $('#detail-created').text(log.created_at);
    $('#detail-sent').text(log.sent_at || '-');
    $('#detail-to-name').text(log.to_name || '-');
    $('#detail-to-email').text(log.to_email);
    $('#detail-subject').text(log.subject);
    
    // Message preview - render as HTML
    if (log.message_preview) {
        $('#detail-preview').html(log.message_preview);
        $('#detail-preview-group').show();
    } else {
        $('#detail-preview-group').hide();
    }
    
    // Error message (for failed emails)
    if (log.status === 'failed' && log.error_message) {
        $('#detail-error').text(log.error_message);
        $('#detail-error-group').show();
    } else {
        $('#detail-error-group').hide();
    }
    
    // Calon siswa info
    if (log.calon_siswa_nama) {
        $('#detail-calon').text(log.calon_siswa_nama + (log.calon_siswa_nisn ? ' (' + log.calon_siswa_nisn + ')' : ''));
        $('#detail-calon-row').show();
    } else {
        $('#detail-calon-row').hide();
    }
    
    // Show modal
    $('#detailModal').modal('show');
}
</script>
@stop
