@extends('adminlte::page')

@section('title', 'Kelola Slider')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-images mr-2"></i>Kelola Slider</h1>
        <a href="{{ route('admin.settings.slider.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Slider
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="card card-outline card-primary">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Slider</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $sliders->count() }} Slider</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($sliders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="60" class="text-center">Urutan</th>
                            <th width="150">Gambar</th>
                            <th>Judul / Deskripsi</th>
                            <th width="150">Link</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-sliders">
                        @foreach($sliders as $slider)
                        <tr data-id="{{ $slider->id }}">
                            <td class="text-center">
                                <span class="badge badge-secondary handle" style="cursor: move;" title="Drag untuk mengurutkan">
                                    <i class="fas fa-grip-vertical mr-1"></i>{{ $slider->urutan }}
                                </span>
                            </td>
                            <td>
                                @if($slider->gambar)
                                    <img src="{{ asset('storage/' . $slider->gambar) }}" 
                                         alt="{{ $slider->judul }}" 
                                         class="img-thumbnail" 
                                         style="width: 120px; height: 70px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 120px; height: 70px;">
                                        <i class="fas fa-image fa-2x"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $slider->judul ?: '(Tanpa Judul)' }}</strong>
                                @if($slider->deskripsi)
                                    <br><small class="text-muted">{{ Str::limit($slider->deskripsi, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($slider->link)
                                    <a href="{{ $slider->link }}" target="_blank" class="text-primary small">
                                        <i class="fas fa-external-link-alt mr-1"></i>{{ Str::limit($slider->link, 25) }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.settings.slider.toggle-status', $slider) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($slider->status === 'active')
                                        <button type="submit" class="btn btn-sm btn-success" title="Klik untuk nonaktifkan">
                                            <i class="fas fa-check-circle mr-1"></i>Aktif
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Klik untuk aktifkan">
                                            <i class="fas fa-times-circle mr-1"></i>Nonaktif
                                        </button>
                                    @endif
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.settings.slider.edit', $slider) }}" class="btn btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary" title="Preview" data-toggle="modal" data-target="#previewModal{{ $slider->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('admin.settings.slider.destroy', $slider) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus slider ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Preview Modal --}}
                        <div class="modal fade" id="previewModal{{ $slider->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header py-2">
                                        <h5 class="modal-title">Preview: {{ $slider->judul ?: 'Slider' }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-0">
                                        @if($slider->gambar)
                                            <img src="{{ asset('storage/' . $slider->gambar) }}" class="img-fluid w-100" alt="{{ $slider->judul }}">
                                        @endif
                                        @if($slider->judul || $slider->deskripsi)
                                        <div class="p-3">
                                            @if($slider->judul)
                                                <h5>{{ $slider->judul }}</h5>
                                            @endif
                                            @if($slider->deskripsi)
                                                <p class="mb-0">{{ $slider->deskripsi }}</p>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <p class="text-muted">Belum ada slider</p>
                <a href="{{ route('admin.settings.slider.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Tambah Slider Pertama
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Tips Card --}}
    <div class="card card-outline card-secondary collapsed-card">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> Tips Slider</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Ukuran gambar:</strong> Direkomendasikan 1920x600 pixel untuk hasil terbaik</li>
                <li><strong>Format file:</strong> JPG, PNG, GIF, atau WEBP (maks 5MB)</li>
                <li><strong>Urutan:</strong> Slider akan ditampilkan sesuai urutan (terkecil tampil duluan)</li>
                <li><strong>Status:</strong> Hanya slider dengan status Aktif yang akan ditampilkan di halaman depan</li>
            </ul>
        </div>
    </div>
@stop

@section('css')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
    .handle:hover {
        background-color: #6c757d !important;
    }
</style>
@stop
