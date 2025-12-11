@extends('adminlte::page')

@section('title', 'Tambah Jadwal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-plus-circle mr-2"></i>Tambah Jadwal</h1>
        <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.settings.jadwal.store') }}" method="POST">
                @csrf
                
                <div class="card card-outline card-primary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Detail Jadwal</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" id="nama_kegiatan" 
                                   class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   value="{{ old('nama_kegiatan') }}" 
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
                                           value="{{ old('tanggal_mulai') }}" required>
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
                                           value="{{ old('tanggal_selesai') }}">
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
                                      placeholder="Keterangan tambahan...">{{ old('keterangan') }}</textarea>
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
                                               value="{{ old('warna', '#007bff') }}" 
                                               style="height: 38px; width: 60px; padding: 2px;">
                                        <input type="text" name="warna" id="warna" 
                                               class="form-control @error('warna') is-invalid @enderror" 
                                               value="{{ old('warna', '#007bff') }}">
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
                                           value="{{ old('urutan', $maxUrutan + 1) }}" min="0" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Jadwal
                        </button>
                        <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            {{-- Color Presets --}}
            <div class="card card-outline card-secondary">
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
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #e83e8c; color: white;" data-color="#e83e8c">Pink</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #fd7e14; color: white;" data-color="#fd7e14">Orange</button>
                        <button type="button" class="btn btn-sm m-1 color-preset" style="background-color: #20c997; color: white;" data-color="#20c997">Teal</button>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="card card-outline card-info">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> Tips</h3>
                </div>
                <div class="card-body small">
                    <ul class="mb-0 pl-3">
                        <li><strong>Urutan</strong> menentukan posisi jadwal di timeline (urutan 1 tampil paling atas)</li>
                        <li><strong>Warna</strong> untuk membedakan tiap tahapan PPDB</li>
                        <li>Status <strong>Aktif</strong> berarti jadwal akan ditampilkan di halaman depan</li>
                        <li>Jika kegiatan berlangsung 1 hari, kosongkan tanggal selesai</li>
                    </ul>
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
