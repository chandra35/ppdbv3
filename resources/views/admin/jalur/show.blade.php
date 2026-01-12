@extends('adminlte::page')

@section('title', 'Detail Jalur - ' . $jalur->nama)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="{{ $jalur->icon }} text-{{ $jalur->warna }} mr-2"></i>
            {{ $jalur->nama }}
            <small class="badge badge-{{ $jalur->warna }}">{{ $jalur->kode }}</small>
        </h1>
        <div>
            <a href="{{ route('admin.jalur.edit', $jalur) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('admin.jalur.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
{{-- Status Cards --}}
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-{{ $jalur->warna }}">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pendaftar</span>
                <span class="info-box-number">{{ $jalur->pendaftar_count }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $jalur->persentaseKuota() }}%"></div>
                </div>
                <span class="progress-description">{{ $jalur->persentaseKuota() }}% dari kuota</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-chair"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kuota</span>
                <span class="info-box-number">{{ $jalur->kuota }}</span>
                <span class="progress-description">Sisa: {{ $jalur->sisaKuota() }} kursi</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Gelombang</span>
                <span class="info-box-number">{{ $jalur->gelombang->count() }}</span>
                <span class="progress-description">
                    @php $aktif = $jalur->gelombang->where('is_active', true)->count(); @endphp
                    {{ $aktif }} aktif
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-{{ $jalur->is_active ? 'success' : 'secondary' }}">
            <span class="info-box-icon"><i class="fas fa-toggle-{{ $jalur->is_active ? 'on' : 'off' }}"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Status</span>
                <span class="info-box-number">{{ $jalur->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <span class="progress-description">
                    @if($jalur->tampil_di_publik)
                    <i class="fas fa-eye"></i> Tampil di publik
                    @else
                    <i class="fas fa-eye-slash"></i> Tidak tampil
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Detail & Gelombang --}}
    <div class="col-md-8">
        {{-- Detail Jalur --}}
        <div class="card card-outline card-{{ $jalur->warna }}">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Detail Jalur</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="150">Tahun Pelajaran</th>
                        <td>{{ $jalur->tahunPelajaran?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $jalur->deskripsi ?: '-' }}</td>
                    </tr>
                    @if($jalur->persyaratan)
                    <tr>
                        <th>Persyaratan</th>
                        <td>{!! nl2br(e($jalur->persyaratan)) !!}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Daftar Gelombang --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Gelombang Pendaftaran</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahGelombang">
                        <i class="fas fa-plus mr-1"></i> Tambah Gelombang
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($jalur->gelombang->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada gelombang pendaftaran</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahGelombang">
                        <i class="fas fa-plus mr-1"></i> Buat Gelombang Pertama
                    </button>
                </div>
                @else
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Gelombang</th>
                            <th>Periode</th>
                            <th class="text-center">Kuota</th>
                            <th class="text-center">Pendaftar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jalur->gelombang as $gelombang)
                        <tr class="{{ $gelombang->is_active ? 'table-success' : '' }}">
                            <td>{{ $gelombang->urutan }}</td>
                            <td>
                                <strong>{{ $gelombang->nama }}</strong>
                                @if(!$gelombang->tampil_nama_gelombang)
                                    <i class="fas fa-eye-slash text-muted ml-1" title="Tidak tampil di publik"></i>
                                @endif
                                @if($gelombang->is_active)
                                    <span class="badge badge-success ml-1">Aktif</span>
                                @endif
                            </td>
                            <td>
                                {{ $gelombang->tanggal_buka->format('d/m/Y') }} 
                                <small class="text-muted">{{ $gelombang->waktu_buka ? substr($gelombang->waktu_buka, 0, 5) : '00:00' }}</small>
                                - {{ $gelombang->tanggal_tutup->format('d/m/Y') }}
                                <small class="text-muted">{{ $gelombang->waktu_tutup ? substr($gelombang->waktu_tutup, 0, 5) : '23:59' }}</small>
                                @if($gelombang->status == 'open')
                                <br><small class="text-success"><i class="fas fa-clock"></i> {{ $gelombang->sisa_hari }} hari lagi</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $gelombang->kuota ?? 'Ikut Jalur' }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $gelombang->persentaseKuota() >= 90 ? 'danger' : ($gelombang->persentaseKuota() >= 70 ? 'warning' : 'info') }}">
                                    {{ $gelombang->pendaftar_count ?? $gelombang->kuota_terisi }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $gelombang->status_color }}">{{ $gelombang->status_label }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-warning btn-edit-gelombang" title="Edit"
                                        data-id="{{ $gelombang->id }}"
                                        data-nama="{{ $gelombang->nama }}"
                                        data-deskripsi="{{ $gelombang->deskripsi }}"
                                        data-tanggal_buka="{{ $gelombang->tanggal_buka->format('Y-m-d') }}"
                                        data-waktu_buka="{{ $gelombang->waktu_buka ? substr($gelombang->waktu_buka, 0, 5) : '00:00' }}"
                                        data-tanggal_tutup="{{ $gelombang->tanggal_tutup->format('Y-m-d') }}"
                                        data-waktu_tutup="{{ $gelombang->waktu_tutup ? substr($gelombang->waktu_tutup, 0, 5) : '23:59' }}"
                                        data-kuota="{{ $gelombang->kuota }}"
                                        data-biaya_pendaftaran="{{ $gelombang->biaya_pendaftaran }}"
                                        data-tampil_nama_gelombang="{{ $gelombang->tampil_nama_gelombang ? '1' : '0' }}"
                                        data-tampil_kuota="{{ $gelombang->tampil_kuota ? '1' : '0' }}"
                                        data-toggle="modal" data-target="#modalEditGelombang">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if($gelombang->status != 'open' && $gelombang->status != 'finished')
                                            <form action="{{ route('admin.jalur.gelombang.buka', [$jalur, $gelombang]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-door-open text-success mr-2"></i> Buka Pendaftaran
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($gelombang->status == 'open')
                                            <form action="{{ route('admin.jalur.gelombang.tutup', [$jalur, $gelombang]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-door-closed text-warning mr-2"></i> Tutup Pendaftaran
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($gelombang->status != 'finished' && $gelombang->status != 'draft')
                                            <form action="{{ route('admin.jalur.gelombang.selesaikan', [$jalur, $gelombang]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-flag-checkered text-dark mr-2"></i> Selesaikan
                                                </button>
                                            </form>
                                            @endif
                                            
                                            @if($gelombang->kuota_terisi == 0)
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.jalur.gelombang.destroy', [$jalur, $gelombang]) }}" method="POST" 
                                                  class="form-delete-gelombang">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash mr-2"></i> Hapus
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-md-4">
        {{-- Aksi Cepat --}}
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.jalur.toggle-status', $jalur) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-{{ $jalur->is_active ? 'warning' : 'success' }} btn-block">
                        <i class="fas fa-toggle-{{ $jalur->is_active ? 'off' : 'on' }} mr-1"></i>
                        {{ $jalur->is_active ? 'Nonaktifkan Jalur' : 'Aktifkan Jalur' }}
                    </button>
                </form>

                <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#modalTambahGelombang">
                    <i class="fas fa-plus mr-1"></i> Tambah Gelombang
                </button>

                <form action="{{ route('admin.jalur.duplicate', $jalur) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-copy mr-1"></i> Duplikasi untuk Tahun Baru
                    </button>
                </form>

                @if($jalur->kuota_terisi == 0)
                <hr>
                <form action="{{ route('admin.jalur.destroy', $jalur) }}" method="POST" 
                      id="form-delete-jalur">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-block">
                        <i class="fas fa-trash mr-1"></i> Hapus Jalur
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Dibuat:</td>
                        <td>{{ $jalur->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Diperbarui:</td>
                        <td>{{ $jalur->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>ID:</td>
                        <td><code>{{ Str::limit($jalur->id, 8) }}</code></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Panduan --}}
        <div class="card card-outline card-secondary collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Panduan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <h6>Alur Kerja:</h6>
                <ol class="pl-3">
                    <li>Pastikan Jalur <strong>Aktif</strong></li>
                    <li>Buat Gelombang Pendaftaran</li>
                    <li><strong>Buka Pendaftaran</strong> pada gelombang</li>
                    <li>Pendaftar dapat mendaftar</li>
                    <li><strong>Tutup/Selesaikan</strong> gelombang</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Gelombang --}}
<div class="modal fade" id="modalTambahGelombang" tabindex="-1" role="dialog" aria-labelledby="modalTambahGelombangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.jalur.gelombang.store', $jalur) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalTambahGelombangLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Gelombang Pendaftaran
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Info Referensi Tanggal Jalur --}}
                    @if($jalur->tanggal_buka && $jalur->tanggal_tutup)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Referensi Tanggal Jalur:</strong>
                        {{ $jalur->tanggal_buka->format('d/m/Y') }} - {{ $jalur->tanggal_tutup->format('d/m/Y') }}
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama">Nama Gelombang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required
                                    placeholder="Contoh: Gelombang 1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kuota">Kuota <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="kuota" name="kuota" min="1" required
                                    placeholder="Jumlah kuota">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_buka">Tanggal Buka <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_buka" name="tanggal_buka" required
                                    min="{{ $jalur->tanggal_buka ? $jalur->tanggal_buka->format('Y-m-d') : '' }}"
                                    max="{{ $jalur->tanggal_tutup ? $jalur->tanggal_tutup->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="waktu_buka">Waktu Buka</label>
                                <input type="time" class="form-control" id="waktu_buka" name="waktu_buka" value="00:00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_tutup">Tanggal Tutup <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_tutup" name="tanggal_tutup" required
                                    min="{{ $jalur->tanggal_buka ? $jalur->tanggal_buka->format('Y-m-d') : '' }}"
                                    max="{{ $jalur->tanggal_tutup ? $jalur->tanggal_tutup->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="waktu_tutup">Waktu Tutup</label>
                                <input type="time" class="form-control" id="waktu_tutup" name="waktu_tutup" value="23:59">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"
                            placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="is_active">Langsung aktifkan gelombang</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Gelombang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Gelombang --}}
<div class="modal fade" id="modalEditGelombang" tabindex="-1" role="dialog" aria-labelledby="modalEditGelombangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEditGelombang" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalEditGelombangLabel">
                        <i class="fas fa-edit mr-2"></i>Edit Gelombang Pendaftaran
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Info Referensi Tanggal Jalur --}}
                    @if($jalur->tanggal_buka && $jalur->tanggal_tutup)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Referensi Tanggal Jalur:</strong>
                        {{ $jalur->tanggal_buka->format('d/m/Y') }} - {{ $jalur->tanggal_tutup->format('d/m/Y') }}
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nama">Nama Gelombang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama" name="nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_kuota">Kuota</label>
                                <input type="number" class="form-control" id="edit_kuota" name="kuota" min="1"
                                    placeholder="Kosongkan untuk ikut kuota jalur">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal_buka">Tanggal Buka <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_buka" name="tanggal_buka" required
                                    min="{{ $jalur->tanggal_buka ? $jalur->tanggal_buka->format('Y-m-d') : '' }}"
                                    max="{{ $jalur->tanggal_tutup ? $jalur->tanggal_tutup->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_waktu_buka">Waktu Buka</label>
                                <input type="time" class="form-control" id="edit_waktu_buka" name="waktu_buka">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal_tutup">Tanggal Tutup <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_tutup" name="tanggal_tutup" required
                                    min="{{ $jalur->tanggal_buka ? $jalur->tanggal_buka->format('Y-m-d') : '' }}"
                                    max="{{ $jalur->tanggal_tutup ? $jalur->tanggal_tutup->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_waktu_tutup">Waktu Tutup</label>
                                <input type="time" class="form-control" id="edit_waktu_tutup" name="waktu_tutup">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_biaya_pendaftaran">Biaya Pendaftaran</label>
                                <input type="number" class="form-control" id="edit_biaya_pendaftaran" name="biaya_pendaftaran" min="0"
                                    placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Visibilitas Publik</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_tampil_nama_gelombang" name="tampil_nama_gelombang" value="1">
                                    <label class="custom-control-label" for="edit_tampil_nama_gelombang">Tampilkan nama gelombang</label>
                                </div>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="edit_tampil_kuota" name="tampil_kuota" value="1">
                                    <label class="custom-control-label" for="edit_tampil_kuota">Tampilkan kuota gelombang</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi">Keterangan</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="2"
                            placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Update Gelombang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Handle edit gelombang button click
    $('.btn-edit-gelombang').on('click', function() {
        var id = $(this).data('id');
        var baseUrl = '{{ route("admin.jalur.gelombang.update", [$jalur->id, ":gelombang"]) }}';
        var actionUrl = baseUrl.replace(':gelombang', id);
        
        $('#formEditGelombang').attr('action', actionUrl);
        $('#edit_nama').val($(this).data('nama'));
        $('#edit_deskripsi').val($(this).data('deskripsi'));
        $('#edit_tanggal_buka').val($(this).data('tanggal_buka'));
        $('#edit_waktu_buka').val($(this).data('waktu_buka') || '00:00');
        $('#edit_tanggal_tutup').val($(this).data('tanggal_tutup'));
        $('#edit_waktu_tutup').val($(this).data('waktu_tutup') || '23:59');
        $('#edit_kuota').val($(this).data('kuota') || '');
        $('#edit_biaya_pendaftaran').val($(this).data('biaya_pendaftaran') || 0);
        $('#edit_tampil_nama_gelombang').prop('checked', $(this).data('tampil_nama_gelombang') == '1');
        $('#edit_tampil_kuota').prop('checked', $(this).data('tampil_kuota') == '1');
    });
    
    // Validasi tanggal_tutup >= tanggal_buka
    $('#tanggal_buka, #edit_tanggal_buka').on('change', function() {
        var prefix = $(this).attr('id').includes('edit_') ? 'edit_' : '';
        var tanggalBuka = $('#' + prefix + 'tanggal_buka').val();
        $('#' + prefix + 'tanggal_tutup').attr('min', tanggalBuka);
    });
    
    // SweetAlert2 for delete gelombang
    $('.form-delete-gelombang').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: 'Hapus Gelombang?',
            text: 'Gelombang yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    
    // SweetAlert2 for delete jalur
    $('#form-delete-jalur').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: 'Hapus Jalur "{{ $jalur->nama }}"?',
            html: '<p class="text-danger"><strong>Perhatian!</strong></p>' +
                  '<p>Semua gelombang dalam jalur ini juga akan dihapus.</p>' +
                  '<p>Data yang dihapus tidak dapat dikembalikan!</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Jalur!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop
