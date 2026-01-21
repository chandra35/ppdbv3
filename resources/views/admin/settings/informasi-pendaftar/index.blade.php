@extends('adminlte::page')

@section('title', 'Informasi Pendaftar')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .info-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .info-card.inactive {
        border-left-color: #6c757d;
        opacity: 0.7;
    }
    .sortable-ghost {
        opacity: 0.5;
        background: #f0f0f0;
    }
    .drag-handle {
        cursor: move;
        color: #aaa;
    }
    .drag-handle:hover {
        color: #333;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #28a745;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-info-circle"></i> Informasi Pendaftar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Pengaturan</a></li>
                <li class="breadcrumb-item active">Informasi Pendaftar</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Alert Info --}}
            <div class="alert alert-info">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Tips:</strong> Informasi yang diaktifkan dengan opsi "Tampilkan di Modal" akan muncul sebagai popup ketika pendaftar pertama kali login.
                Anda dapat menambahkan beberapa informasi dan mengatur urutannya dengan drag & drop.
            </div>
            
            {{-- Tombol Tambah --}}
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Informasi
                </button>
            </div>

            {{-- Daftar Informasi --}}
            <div id="informasiList">
                @forelse($informasiList as $info)
                    <div class="card info-card {{ !$info->is_active ? 'inactive' : '' }}" data-id="{{ $info->id }}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="drag-handle"><i class="fas fa-grip-vertical fa-lg"></i></span>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1">
                                        {{ $info->judul }}
                                        @if(!$info->is_active)
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                        @if($info->tampilkan_modal)
                                            <span class="badge badge-info"><i class="fas fa-window-restore"></i> Modal</span>
                                        @endif
                                    </h5>
                                    <div class="text-muted small">
                                        {!! Str::limit(strip_tags($info->isi), 150) !!}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex align-items-center">
                                        {{-- Toggle Active --}}
                                        <div class="mr-3 text-center">
                                            <label class="toggle-switch mb-0">
                                                <input type="checkbox" class="toggle-active" data-id="{{ $info->id }}" {{ $info->is_active ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="small text-muted">Aktif</div>
                                        </div>
                                        {{-- Toggle Modal --}}
                                        <div class="mr-3 text-center">
                                            <label class="toggle-switch mb-0">
                                                <input type="checkbox" class="toggle-modal" data-id="{{ $info->id }}" {{ $info->tampilkan_modal ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <div class="small text-muted">Modal</div>
                                        </div>
                                        {{-- Actions --}}
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info btn-edit" 
                                                data-id="{{ $info->id }}"
                                                data-judul="{{ $info->judul }}"
                                                data-isi="{{ $info->isi }}"
                                                data-toggle="modal" data-target="#modalEdit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $info->id }}" data-judul="{{ $info->judul }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada informasi</h5>
                            <p class="text-muted">Klik tombol "Tambah Informasi" untuk menambahkan informasi baru</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.settings.informasi-pendaftar.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Informasi</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Jadwal Tes Masuk">
                        </div>
                        <div class="form-group">
                            <label>Isi Informasi <span class="text-danger">*</span></label>
                            <textarea name="isi" class="form-control" rows="6" required placeholder="Tulis informasi yang ingin disampaikan kepada pendaftar..."></textarea>
                            <small class="text-muted">Anda bisa menggunakan format HTML sederhana seperti &lt;b&gt;, &lt;i&gt;, &lt;ul&gt;, &lt;li&gt;</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="isActiveAdd" name="is_active" value="1" checked>
                                        <label class="custom-control-label" for="isActiveAdd">Aktifkan informasi ini</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="tampilkanModalAdd" name="tampilkan_modal" value="1" checked>
                                        <label class="custom-control-label" for="tampilkanModalAdd">Tampilkan di modal setelah login</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Informasi</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="editJudul" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Isi Informasi <span class="text-danger">*</span></label>
                            <textarea name="isi" id="editIsi" class="form-control" rows="6" required></textarea>
                            <small class="text-muted">Anda bisa menggunakan format HTML sederhana seperti &lt;b&gt;, &lt;i&gt;, &lt;ul&gt;, &lt;li&gt;</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Form Delete Hidden --}}
    <form id="formDelete" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // Sortable untuk reorder
    var el = document.getElementById('informasiList');
    if (el) {
        new Sortable(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                var order = [];
                document.querySelectorAll('#informasiList .info-card').forEach(function(card) {
                    order.push(card.dataset.id);
                });
                
                fetch('{{ route('admin.settings.informasi-pendaftar.update-order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                    }
                });
            }
        });
    }

    // Toggle Active
    $(document).on('change', '.toggle-active', function() {
        var id = $(this).data('id');
        var card = $(this).closest('.info-card');
        
        $.post('{{ url('admin/settings/informasi-pendaftar') }}/' + id + '/toggle-active', {
            _token: '{{ csrf_token() }}'
        }, function(res) {
            if (res.success) {
                toastr.success(res.message);
                card.toggleClass('inactive', !res.is_active);
            }
        });
    });

    // Toggle Modal
    $(document).on('change', '.toggle-modal', function() {
        var id = $(this).data('id');
        
        $.post('{{ url('admin/settings/informasi-pendaftar') }}/' + id + '/toggle-modal', {
            _token: '{{ csrf_token() }}'
        }, function(res) {
            if (res.success) {
                toastr.success(res.message);
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var judul = $(this).data('judul');
        var isi = $(this).data('isi');
        
        $('#editJudul').val(judul);
        $('#editIsi').val(isi);
        $('#formEdit').attr('action', '{{ url('admin/settings/informasi-pendaftar') }}/' + id);
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var judul = $(this).data('judul');
        
        if (confirm('Hapus informasi "' + judul + '"?')) {
            $('#formDelete').attr('action', '{{ url('admin/settings/informasi-pendaftar') }}/' + id);
            $('#formDelete').submit();
        }
    });
</script>
@stop
