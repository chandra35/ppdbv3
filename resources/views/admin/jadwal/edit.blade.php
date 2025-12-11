@extends('adminlte::page')

@section('title', 'Edit Jadwal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Edit Jadwal</h1>
        <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.settings.jadwal.update', $jadwal) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Detail Jadwal</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" id="nama_kegiatan" 
                                   class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   value="{{ old('nama_kegiatan', $jadwal->nama_kegiatan) }}" 
                                   placeholder="Contoh: Pendaftaran Online" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                                           class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           value="{{ old('tanggal_mulai', $jadwal->tanggal_mulai->format('Y-m-d')) }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                                           class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           value="{{ old('tanggal_selesai', $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('Y-m-d') : '') }}">
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika hanya 1 hari</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3" 
                                      class="form-control @error('keterangan') is-invalid @enderror" 
                                      placeholder="Keterangan tambahan...">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="warna">Warna Label</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control" id="warna_picker" 
                                               value="{{ old('warna', $jadwal->warna ?: '#007bff') }}" 
                                               style="height: 38px; width: 60px; padding: 2px;">
                                        <input type="text" name="warna" id="warna" 
                                               class="form-control @error('warna') is-invalid @enderror" 
                                               value="{{ old('warna', $jadwal->warna ?: '#007bff') }}">
                                    </div>
                                    @error('warna')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" name="urutan" id="urutan" 
                                           class="form-control @error('urutan') is-invalid @enderror" 
                                           value="{{ old('urutan', $jadwal->urutan) }}" min="0" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $jadwal->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update Jadwal
                        </button>
                        <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            {{-- Info --}}
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Info Jadwal</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status Kegiatan:</span>
                        @if($jadwal->is_ongoing)
                            <span class="badge badge-success">Sedang Berlangsung</span>
                        @elseif($jadwal->is_upcoming)
                            <span class="badge badge-info">Akan Datang</span>
                        @else
                            <span class="badge badge-secondary">Selesai</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dibuat:</span>
                        <small>{{ $jadwal->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Diupdate:</span>
                        <small>{{ $jadwal->updated_at->format('d M Y H:i') }}</small>
                    </div>
                </div>
            </div>

            {{-- Color Presets --}}
            <div class="card card-outline card-info">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Preset Warna</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #007bff; color: white;" data-color="#007bff">Primary</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #28a745; color: white;" data-color="#28a745">Success</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #17a2b8; color: white;" data-color="#17a2b8">Info</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #ffc107; color: black;" data-color="#ffc107">Warning</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #dc3545; color: white;" data-color="#dc3545">Danger</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #6f42c1; color: white;" data-color="#6f42c1">Purple</button>
                    </div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="card card-outline card-danger">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Zona Bahaya</h3>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Hapus jadwal ini secara permanen</p>
                    <form action="{{ route('admin.settings.jadwal.destroy', $jadwal) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-block">
                            <i class="fas fa-trash mr-1"></i> Hapus Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Color picker sync
    document.getElementById('warna_picker').addEventListener('input', function(e) {
        document.getElementById('warna').value = e.target.value;
    });

    // Color presets
    document.querySelectorAll('.color-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var color = this.dataset.color;
            document.getElementById('warna').value = color;
            document.getElementById('warna_picker').value = color;
        });
    });
</script>
@stop
