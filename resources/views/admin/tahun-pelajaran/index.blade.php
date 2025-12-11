@extends('adminlte::page')

@section('title', 'Tahun Pelajaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-calendar-alt mr-2"></i>Tahun Pelajaran</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus mr-1"></i> Tambah Tahun Pelajaran
        </button>
    </div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-exclamation-triangle"></i> {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-exclamation-triangle"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Info Tahun Aktif --}}
@if($tahunAktif)
<div class="callout callout-success">
    <h5><i class="fas fa-check-circle text-success"></i> Tahun Pelajaran Aktif</h5>
    <p class="mb-0">
        <strong class="h4">{{ $tahunAktif->nama }}</strong>
        @if($tahunAktif->keterangan)
        <span class="ml-3 text-muted">- {{ $tahunAktif->keterangan }}</span>
        @endif
    </p>
</div>
@else
<div class="callout callout-warning">
    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Belum Ada Tahun Pelajaran Aktif</h5>
    <p class="mb-0">Silahkan aktifkan salah satu tahun pelajaran atau buat baru.</p>
</div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Daftar Tahun Pelajaran</h3>
    </div>
    <div class="card-body">
        @if($tahunPelajaranList->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
            <p class="text-muted">Belum ada tahun pelajaran</p>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                <i class="fas fa-plus mr-1"></i> Buat Tahun Pelajaran Pertama
            </button>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tahun Pelajaran</th>
                        <th>Jalur Pendaftaran</th>
                        <th>Status</th>
                        <th style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tahunPelajaranList as $index => $tp)
                    <tr class="{{ $tp->is_active ? 'table-success' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $tp->nama }}</strong>
                            @if($tp->keterangan)
                            <br><small class="text-muted">{{ Str::limit($tp->keterangan, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $jalurCount = $tp->jalurPendaftaran()->count();
                            @endphp
                            @if($jalurCount > 0)
                            <a href="{{ route('admin.jalur.index', ['tahun_pelajaran_id' => $tp->id]) }}" class="badge badge-info">
                                {{ $jalurCount }} jalur
                            </a>
                            @else
                            <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($tp->is_active)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>
                            @else
                            <span class="badge badge-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(!$tp->is_active)
                                <form action="{{ route('admin.tahun-pelajaran.aktifkan', $tp) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Aktifkan" onclick="return confirm('Aktifkan tahun pelajaran {{ $tp->nama }}?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-warning" title="Edit" 
                                        data-toggle="modal" data-target="#modalEdit{{ $tp->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$tp->is_active && $jalurCount == 0)
                                <form action="{{ route('admin.tahun-pelajaran.destroy', $tp) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus" onclick="return confirm('Hapus tahun pelajaran {{ $tp->nama }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Info Card --}}
<div class="card card-outline card-info collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Tentang Tahun Pelajaran</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <p><strong>Tahun Pelajaran</strong> adalah periode akademik yang digunakan untuk mengelompokkan data pendaftaran:</p>
        <ul class="mb-0">
            <li>Hanya <strong>1 tahun pelajaran</strong> yang bisa aktif dalam satu waktu</li>
            <li>Semua jalur pendaftaran akan terhubung dengan tahun pelajaran</li>
            <li>Tahun pelajaran yang sudah memiliki jalur tidak bisa dihapus</li>
            <li>Format: <code>TAHUN_AWAL/TAHUN_AKHIR</code> (contoh: 2025/2026)</li>
        </ul>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.tahun-pelajaran.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalTambahLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Tahun Pelajaran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" name="nama" value="{{ old('nama') }}" 
                               placeholder="Contoh: 2025/2026" required>
                        @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: TAHUN/TAHUN (contoh: 2025/2026)</small>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                  id="keterangan" name="keterangan" rows="2" 
                                  placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="is_active">Langsung aktifkan tahun pelajaran ini</label>
                        </div>
                        <small class="text-muted">Jika dicentang, tahun pelajaran lain akan dinonaktifkan</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit untuk setiap tahun pelajaran --}}
@foreach($tahunPelajaranList as $tp)
<div class="modal fade" id="modalEdit{{ $tp->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel{{ $tp->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.tahun-pelajaran.update', $tp) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalEditLabel{{ $tp->id }}">
                        <i class="fas fa-edit mr-2"></i>Edit Tahun Pelajaran
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_{{ $tp->id }}">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                               id="nama_{{ $tp->id }}" name="nama" value="{{ $tp->nama }}" 
                               placeholder="Contoh: 2025/2026" required>
                        <small class="text-muted">Format: TAHUN/TAHUN (contoh: 2025/2026)</small>
                    </div>
                    <div class="form-group mb-0">
                        <label for="keterangan_{{ $tp->id }}">Keterangan</label>
                        <textarea class="form-control" 
                                  id="keterangan_{{ $tp->id }}" name="keterangan" rows="2" 
                                  placeholder="Keterangan tambahan (opsional)">{{ $tp->keterangan }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@stop

@section('js')
<script>
    // Auto open modal if there are validation errors
    @if($errors->any() && old('_method') != 'PUT')
    $(document).ready(function() {
        $('#modalTambah').modal('show');
    });
    @endif
</script>
@stop
