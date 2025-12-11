@extends('adminlte::page')

@section('title', 'Kelola Berita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-newspaper mr-2"></i>Kelola Berita</h1>
        <a href="{{ route('admin.settings.berita.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Berita
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    {{-- Filter Section --}}
    <div class="card card-outline card-primary">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.settings.berita.index') }}" class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="mb-1 small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Judul atau deskripsi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 small">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="mb-1 small">Kategori</label>
                    <select name="kategori" class="form-control form-control-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $key => $label)
                            <option value="{{ $key }}" {{ request('kategori') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.settings.berita.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Facebook Status --}}
    @if(!$facebookConfigured)
    <div class="alert alert-warning alert-dismissible fade show py-2">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong>Facebook belum dikonfigurasi.</strong> 
        <a href="{{ route('admin.settings.halaman.index') }}#social" class="alert-link">Klik di sini</a> untuk mengatur integrasi Facebook.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- Data Table --}}
    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="80">Gambar</th>
                            <th>Judul</th>
                            <th width="100">Kategori</th>
                            <th width="100">Status</th>
                            <th width="100">Featured</th>
                            <th width="80">Views</th>
                            <th width="100">Facebook</th>
                            <th width="120">Tanggal</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beritas as $index => $berita)
                        <tr>
                            <td>{{ $beritas->firstItem() + $index }}</td>
                            <td>
                                @if($berita->gambar)
                                    <img src="{{ asset('storage/' . $berita->gambar) }}" 
                                         alt="{{ $berita->judul }}" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 40px; object-fit: cover;">
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-image"></i></span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ Str::limit($berita->judul, 40) }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($berita->deskripsi, 50) }}</small>
                            </td>
                            <td>
                                @if($berita->kategori)
                                    <span class="badge badge-info">{{ $kategoris[$berita->kategori] ?? $berita->kategori }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($berita->status) {
                                        'published' => 'success',
                                        'draft' => 'warning',
                                        'archived' => 'secondary',
                                        default => 'light'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">{{ ucfirst($berita->status) }}</span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.settings.berita.toggle-featured', $berita) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $berita->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ $berita->is_featured ? 'Hapus dari Featured' : 'Jadikan Featured' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light"><i class="fas fa-eye mr-1"></i>{{ number_format($berita->views ?? 0) }}</span>
                            </td>
                            <td class="text-center">
                                @if($berita->shared_to_facebook)
                                    <span class="badge badge-primary" title="Sudah dishare ke Facebook">
                                        <i class="fab fa-facebook-f"></i> Shared
                                    </span>
                                @elseif($berita->status === 'published' && $facebookConfigured)
                                    <form action="{{ route('admin.settings.berita.share-facebook', $berita) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Share ke Facebook" onclick="return confirm('Share berita ini ke Facebook?')">
                                            <i class="fab fa-facebook-f"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($berita->tanggal_publikasi)
                                    <small>{{ $berita->tanggal_publikasi->format('d M Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $berita->tanggal_publikasi->format('H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.settings.berita.edit', $berita) }}" class="btn btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.settings.berita.destroy', $berita) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus berita ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada berita</p>
                                    <a href="{{ route('admin.settings.berita.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Tambah Berita Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($beritas->hasPages())
        <div class="card-footer py-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $beritas->firstItem() }}-{{ $beritas->lastItem() }} dari {{ $beritas->total() }} berita
                </small>
                {{ $beritas->links() }}
            </div>
        </div>
        @endif
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
</style>
@stop
