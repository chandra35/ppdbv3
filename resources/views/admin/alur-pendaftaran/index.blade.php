@extends('adminlte::page')

@section('title', 'Alur Pendaftaran')

@section('css')
@include('admin.partials.action-buttons-style')
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-list-ol mr-2"></i> Alur Pendaftaran</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus mr-1"></i> Tambah Alur
        </button>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Alur Pendaftaran</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $alurs->where('is_active', true)->count() }} aktif</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($alurs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40" class="text-center"></th>
                            <th width="50" class="text-center">No</th>
                            <th width="60" class="text-center">Icon</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-alur">
                        @foreach($alurs as $alur)
                        <tr data-id="{{ $alur->id }}">
                            <td class="text-center drag-handle" style="cursor: grab;">
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary urutan-badge">{{ $alur->urutan }}</span>
                            </td>
                            <td class="text-center">
                                <i class="{{ $alur->icon }} fa-lg text-primary"></i>
                            </td>
                            <td>
                                <strong>{{ $alur->judul }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $alur->deskripsi ?: '-' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="btn btn-status-toggle {{ $alur->is_active ? 'active' : 'inactive' }}" style="cursor: default;">
                                    <i class="fas fa-{{ $alur->is_active ? 'check' : 'times' }} mr-1"></i>
                                    {{ $alur->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-btns">
                                    <button type="button" class="btn btn-action-edit" 
                                            data-toggle="modal" 
                                            data-target="#editModal{{ $alur->id }}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-action-delete" 
                                            onclick="confirmDelete('{{ $alur->id }}', '{{ $alur->judul }}')"
                                            data-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <p class="text-muted">Belum ada data alur pendaftaran</p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Alur Pertama
                </button>
            </div>
            @endif
        </div>
        @if($alurs->count() > 1)
        <div class="card-footer">
            <small class="text-muted">
                <i class="fas fa-grip-vertical mr-1"></i> 
                Drag & drop baris menggunakan handle <i class="fas fa-grip-vertical"></i> untuk mengubah urutan
            </small>
        </div>
        @endif
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview Tampilan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    @foreach($alurs->where('is_active', true) as $alur)
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" 
                             style="width: 40px; height: 40px; min-width: 40px;">
                            {{ $alur->urutan }}
                        </div>
                        <div class="flex-grow-1 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <h6 class="mb-1 font-weight-bold">
                                <i class="{{ $alur->icon }} text-primary mr-2"></i>{{ $alur->judul }}
                            </h6>
                            <p class="mb-0 text-muted small">{{ $alur->deskripsi }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.settings.alur-pendaftaran.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Tambah Alur Pendaftaran</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required 
                                   placeholder="Contoh: Registrasi Online">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2" 
                                      placeholder="Deskripsi singkat langkah ini"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <select name="icon" class="form-control">
                                @foreach($iconList as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Preview: <i id="iconPreviewCreate" class="fas fa-user-plus ml-2"></i></small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active_create" name="is_active" checked>
                                <label class="custom-control-label" for="is_active_create">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach($alurs as $alur)
    <div class="modal fade" id="editModal{{ $alur->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.settings.alur-pendaftaran.update', $alur) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Alur Pendaftaran</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required 
                                   value="{{ $alur->judul }}">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2">{{ $alur->deskripsi }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <select name="icon" class="form-control icon-select" data-preview="iconPreviewEdit{{ $alur->id }}">
                                @foreach($iconList as $value => $label)
                                <option value="{{ $value }}" {{ $alur->icon == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Preview: <i id="iconPreviewEdit{{ $alur->id }}" class="{{ $alur->icon }} ml-2"></i></small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" 
                                       id="is_active_{{ $alur->id }}" name="is_active" 
                                       {{ $alur->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active_{{ $alur->id }}">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Delete Form --}}
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize SortableJS
        var sortableEl = document.getElementById('sortable-alur');
        if (sortableEl) {
            var sortable = Sortable.create(sortableEl, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'bg-light',
                chosenClass: 'bg-info',
                dragClass: 'shadow',
                onEnd: function(evt) {
                    updateOrder();
                }
            });
        }
    });
    
    function updateOrder() {
        var order = [];
        document.querySelectorAll('#sortable-alur tr').forEach(function(row, index) {
            order.push(row.dataset.id);
            // Update nomor urutan di badge
            row.querySelector('.urutan-badge').textContent = index + 1;
        });
        
        // Kirim ke server
        fetch('{{ route("admin.settings.alur-pendaftaran.update-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toast notification
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Urutan berhasil diperbarui',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                Swal.fire('Error', data.message || 'Gagal menyimpan urutan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Terjadi kesalahan saat menyimpan urutan', 'error');
        });
    }

    function confirmDelete(id, judul) {
        Swal.fire({
            title: 'Hapus Alur Pendaftaran?',
            html: 'Anda yakin ingin menghapus alur <strong>"' + judul + '"</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = '{{ route("admin.settings.alur-pendaftaran.index") }}/' + id;
                form.submit();
            }
        });
    }

    // Icon preview on select change
    document.querySelectorAll('select[name="icon"]').forEach(function(select) {
        select.addEventListener('change', function() {
            const previewId = this.dataset.preview || 'iconPreviewCreate';
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.className = this.value + ' ml-2';
            }
        });
    });

    // Create modal icon preview
    document.querySelector('#createModal select[name="icon"]').addEventListener('change', function() {
        document.getElementById('iconPreviewCreate').className = this.value + ' ml-2';
    });
</script>
@stop
