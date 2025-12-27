@extends('adminlte::page')

@section('title', 'Daftar Pendaftar')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-users"></i> Daftar Pendaftar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pendaftar</li>
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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pendaftar.index') }}" method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Cari</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama, NISN, Email, No Registrasi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jalur</label>
                        <select name="jalur_id" class="form-control">
                            <option value="">Semua Jalur</option>
                            @foreach($jalurList as $jalur)
                            <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                {{ $jalur->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Gelombang</label>
                        <select name="gelombang_id" class="form-control">
                            <option value="">Semua Gelombang</option>
                            @foreach($gelombangList as $gelombang)
                            <option value="{{ $gelombang->id }}" {{ request('gelombang_id') == $gelombang->id ? 'selected' : '' }}>
                                {{ $gelombang->jalur->nama ?? '' }} - {{ $gelombang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pendaftar</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>No. Registrasi</th>
                        <th>Nama Lengkap</th>
                        <th>NISN</th>
                        <th>Jalur / Gelombang</th>
                        <th>Dokumen</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftars as $key => $pendaftar)
                    <tr>
                        <td>{{ $pendaftars->firstItem() + $key }}</td>
                        <td><code>{{ $pendaftar->nomor_registrasi ?? '-' }}</code></td>
                        <td>
                            <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="text-dark">
                                <strong>{{ $pendaftar->nama_lengkap }}</strong>
                            </a>
                            @if($pendaftar->jenis_kelamin)
                                <br><small class="text-muted">{{ $pendaftar->jenis_kelamin == 'laki-laki' ? 'Laki-laki' : 'Perempuan' }}</small>
                            @endif
                        </td>
                        <td>{{ $pendaftar->nisn ?? '-' }}</td>
                        <td>
                            @if($pendaftar->jalurPendaftaran)
                                <span class="badge" style="background: {{ $pendaftar->jalurPendaftaran->warna ?? '#007bff' }}; color: white;">
                                    {{ $pendaftar->jalurPendaftaran->nama }}
                                </span>
                                @if($pendaftar->gelombangPendaftaran)
                                    <br><small class="text-muted">{{ $pendaftar->gelombangPendaftaran->nama }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $dokumenCount = $pendaftar->dokumen->count();
                                $validCount = $pendaftar->dokumen->where('status_verifikasi', 'valid')->count();
                                $pendingCount = $pendaftar->dokumen->where('status_verifikasi', 'pending')->count();
                                $invalidCount = $pendaftar->dokumen->where('status_verifikasi', 'invalid')->count();
                                $revisionCount = $pendaftar->dokumen->where('status_verifikasi', 'revision')->count();
                            @endphp
                            @if($dokumenCount > 0)
                                <div class="btn-group btn-group-sm">
                                    @if($validCount > 0)
                                        <button class="btn btn-success" title="{{ $validCount }} dokumen valid">
                                            <i class="fas fa-check"></i> {{ $validCount }}
                                        </button>
                                    @endif
                                    @if($pendingCount > 0)
                                        <button class="btn btn-warning" title="{{ $pendingCount }} dokumen pending" 
                                                onclick="showDokumenModal('{{ $pendaftar->id }}', '{{ $pendaftar->nama_lengkap }}')">
                                            <i class="fas fa-clock"></i> {{ $pendingCount }}
                                        </button>
                                    @endif
                                    @if($invalidCount > 0)
                                        <button class="btn btn-danger" title="{{ $invalidCount }} dokumen invalid">
                                            <i class="fas fa-times"></i> {{ $invalidCount }}
                                        </button>
                                    @endif
                                    @if($revisionCount > 0)
                                        <button class="btn btn-info" title="{{ $revisionCount }} dokumen perlu revisi">
                                            <i class="fas fa-redo"></i> {{ $revisionCount }}
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted"><i class="fas fa-folder-open"></i> Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            @if($pendaftar->status_verifikasi == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($pendaftar->status_verifikasi == 'verified')
                                <span class="badge badge-info">Verified</span>
                            @elseif($pendaftar->status_verifikasi == 'approved')
                                <span class="badge badge-success">Diterima</span>
                            @elseif($pendaftar->status_verifikasi == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-secondary">{{ $pendaftar->status_verifikasi }}</span>
                            @endif
                        </td>
                        <td>{{ $pendaftar->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-action-view" data-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> <span class="btn-text">Detail</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Tidak ada pendaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $pendaftars->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal Dokumen Quick Action -->
    <div class="modal fade" id="dokumenModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt"></i> Dokumen - <span id="modalPendaftarNama"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="dokumenListContainer" class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30px;">#</th>
                                    <th>Jenis Dokumen</th>
                                    <th style="width: 120px;">Status</th>
                                    <th>Verifikasi Terakhir</th>
                                    <th style="width: 200px;">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody id="dokumenListBody">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quick Approve -->
    <div class="modal fade" id="quickApproveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle"></i> Setujui Dokumen
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Setujui Dokumen Ini?</h5>
                    <p class="text-muted" id="quickApproveText"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" id="confirmQuickApprove">
                        <i class="fas fa-check"></i> Ya, Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quick Reject -->
    <div class="modal fade" id="quickRejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-times-circle"></i> Tolak Dokumen
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="quickRejectForm">
                    <div class="modal-body">
                        <p><strong id="quickRejectText"></strong></p>
                        <div class="form-group">
                            <label>Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="quickRejectCatatan" rows="4" required 
                                      placeholder="Masukkan alasan penolakan dokumen..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tolak Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quick Revisi -->
    <div class="modal fade" id="quickRevisiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-redo"></i> Minta Revisi Dokumen
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="quickRevisiForm">
                    <div class="modal-body">
                        <p><strong id="quickRevisiText"></strong></p>
                        <div class="form-group">
                            <label>Catatan Revisi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="quickRevisiCatatan" rows="4" required 
                                      placeholder="Masukkan catatan untuk revisi dokumen..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Minta Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cancel Verifikasi -->
    <div class="modal fade" id="quickCancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-ban"></i> Batal Verifikasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="quickCancelForm">
                    <div class="modal-body">
                        <p><strong id="quickCancelText"></strong></p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Dokumen akan dikembalikan ke status <strong>Pending</strong>
                        </div>
                        <div class="form-group">
                            <label>Alasan Pembatalan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="quickCancelAlasan" rows="4" required 
                                      placeholder="Masukkan alasan pembatalan verifikasi..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-ban"></i> Batalkan Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    let currentDokumenId = null;
    let currentPendaftarId = null;

    // Show dokumen modal
    function showDokumenModal(pendaftarId, nama) {
        currentPendaftarId = pendaftarId;
        $('#modalPendaftarNama').text(nama);
        $('#dokumenModal').modal('show');
        loadDokumenList(pendaftarId);
    }

    // Load dokumen list via AJAX
    function loadDokumenList(pendaftarId) {
        $.ajax({
            url: `/admin/pendaftar/${pendaftarId}/dokumen-list`,
            method: 'GET',
            success: function(response) {
                let html = '';
                if (response.dokumen && response.dokumen.length > 0) {
                    response.dokumen.forEach((dok, index) => {
                        const statusBadge = getStatusBadge(dok.status_verifikasi);
                        const actionButtons = getActionButtons(dok);
                        const verifikasiInfo = getVerifikasiInfo(dok);
                        
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <strong>${dok.nama_dokumen_lengkap}</strong>
                                    ${dok.catatan_verifikasi ? `<br><small class="text-muted"><i class="fas fa-comment"></i> ${dok.catatan_verifikasi}</small>` : ''}
                                </td>
                                <td>${statusBadge}</td>
                                <td><small>${verifikasiInfo}</small></td>
                                <td>${actionButtons}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted">Tidak ada dokumen</td></tr>';
                }
                $('#dokumenListBody').html(html);
            },
            error: function() {
                $('#dokumenListBody').html('<tr><td colspan="5" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data</td></tr>');
            }
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
            'valid': '<span class="badge badge-success"><i class="fas fa-check"></i> Valid</span>',
            'invalid': '<span class="badge badge-danger"><i class="fas fa-times"></i> Invalid</span>',
            'revision': '<span class="badge badge-info"><i class="fas fa-redo"></i> Revisi</span>'
        };
        return badges[status] || `<span class="badge badge-secondary">${status}</span>`;
    }

    function getVerifikasiInfo(dok) {
        if (dok.verified_by_name) {
            const date = new Date(dok.verified_at).toLocaleString('id-ID');
            return `<i class="fas fa-user"></i> ${dok.verified_by_name}<br><i class="fas fa-clock"></i> ${date}`;
        }
        return '<span class="text-muted">Belum diverifikasi</span>';
    }

    function getActionButtons(dok) {
        let buttons = '';
        
        if (dok.status_verifikasi === 'pending') {
            buttons += `
                <button class="btn btn-success btn-sm" onclick="quickApprove('${dok.id}', '${dok.nama_dokumen_lengkap}')" title="Setujui">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="quickReject('${dok.id}', '${dok.nama_dokumen_lengkap}')" title="Tolak">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="quickRevisi('${dok.id}', '${dok.nama_dokumen_lengkap}')" title="Revisi">
                    <i class="fas fa-redo"></i>
                </button>
            `;
        } else if (dok.status_verifikasi === 'valid' || dok.status_verifikasi === 'invalid') {
            buttons += `
                <button class="btn btn-secondary btn-sm" onclick="quickCancel('${dok.id}', '${dok.nama_dokumen_lengkap}')" title="Batal Verifikasi">
                    <i class="fas fa-ban"></i> Batal
                </button>
            `;
        }
        
        return buttons;
    }

    // Quick Approve
    function quickApprove(dokumenId, namaDokumen) {
        currentDokumenId = dokumenId;
        $('#quickApproveText').text(`Dokumen: ${namaDokumen}`);
        $('#quickApproveModal').modal('show');
    }

    $('#confirmQuickApprove').click(function() {
        if (!currentDokumenId) return;
        
        $.ajax({
            url: `/admin/pendaftar/dokumen/${currentDokumenId}/approve`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#quickApproveModal').modal('hide');
                showToast('success', 'Dokumen berhasil disetujui!');
                if (currentPendaftarId) {
                    loadDokumenList(currentPendaftarId);
                }
                location.reload(); // Refresh untuk update badge count
            },
            error: function() {
                showToast('error', 'Gagal menyetujui dokumen');
            }
        });
    });

    // Quick Reject
    function quickReject(dokumenId, namaDokumen) {
        currentDokumenId = dokumenId;
        $('#quickRejectText').text(`Dokumen: ${namaDokumen}`);
        $('#quickRejectCatatan').val('');
        $('#quickRejectModal').modal('show');
    }

    $('#quickRejectForm').submit(function(e) {
        e.preventDefault();
        if (!currentDokumenId) return;
        
        $.ajax({
            url: `/admin/pendaftar/dokumen/${currentDokumenId}/reject`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                catatan: $('#quickRejectCatatan').val()
            },
            success: function(response) {
                $('#quickRejectModal').modal('hide');
                showToast('success', 'Dokumen berhasil ditolak');
                if (currentPendaftarId) {
                    loadDokumenList(currentPendaftarId);
                }
                location.reload();
            },
            error: function() {
                showToast('error', 'Gagal menolak dokumen');
            }
        });
    });

    // Quick Revisi
    function quickRevisi(dokumenId, namaDokumen) {
        currentDokumenId = dokumenId;
        $('#quickRevisiText').text(`Dokumen: ${namaDokumen}`);
        $('#quickRevisiCatatan').val('');
        $('#quickRevisiModal').modal('show');
    }

    $('#quickRevisiForm').submit(function(e) {
        e.preventDefault();
        if (!currentDokumenId) return;
        
        $.ajax({
            url: `/admin/pendaftar/dokumen/${currentDokumenId}/revisi`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                catatan: $('#quickRevisiCatatan').val()
            },
            success: function(response) {
                $('#quickRevisiModal').modal('hide');
                showToast('success', 'Permintaan revisi berhasil dikirim');
                if (currentPendaftarId) {
                    loadDokumenList(currentPendaftarId);
                }
                location.reload();
            },
            error: function() {
                showToast('error', 'Gagal mengirim permintaan revisi');
            }
        });
    });

    // Quick Cancel
    function quickCancel(dokumenId, namaDokumen) {
        currentDokumenId = dokumenId;
        $('#quickCancelText').text(`Dokumen: ${namaDokumen}`);
        $('#quickCancelAlasan').val('');
        $('#quickCancelModal').modal('show');
    }

    $('#quickCancelForm').submit(function(e) {
        e.preventDefault();
        if (!currentDokumenId) return;
        
        $.ajax({
            url: `/admin/pendaftar/dokumen/${currentDokumenId}/cancel`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                alasan: $('#quickCancelAlasan').val()
            },
            success: function(response) {
                $('#quickCancelModal').modal('hide');
                showToast('success', 'Verifikasi dokumen berhasil dibatalkan');
                if (currentPendaftarId) {
                    loadDokumenList(currentPendaftarId);
                }
                location.reload();
            },
            error: function() {
                showToast('error', 'Gagal membatalkan verifikasi');
            }
        });
    });

    // Toast notification
    function showToast(type, message) {
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        const toast = $(`
            <div class="toast ${bgColor} text-white" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:250px;">
                <div class="toast-body">
                    <i class="fas fa-${icon}"></i> ${message}
                </div>
            </div>
        `);
        
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(() => toast.remove()), 3000);
    }
</script>
@stop
