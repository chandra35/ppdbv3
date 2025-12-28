@extends('adminlte::page')

@section('title', 'Edit Jalur - ' . $jalur->nama)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Edit Jalur Pendaftaran</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.jalur.update', $jalur) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informasi Jalur</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Jalur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                       id="nama" name="nama" value="{{ old('nama', $jalur->nama) }}" required>
                                @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode">Kode Jalur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode') is-invalid @enderror" 
                                       id="kode" name="kode" value="{{ old('kode', $jalur->kode) }}" 
                                       style="text-transform: uppercase;" required>
                                @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                                <select class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" 
                                        id="tahun_pelajaran_id" name="tahun_pelajaran_id" required>
                                    <option value="">-- Pilih Tahun Pelajaran --</option>
                                    @foreach($tahunPelajaranList as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $jalur->tahun_pelajaran_id) == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} @if($tp->is_active) (Aktif) @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('tahun_pelajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kuota">Kuota Pendaftar <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('kuota') is-invalid @enderror" 
                                       id="kuota" name="kuota" value="{{ old('kuota', $jalur->kuota) }}" min="1" required>
                                @error('kuota')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Terisi: {{ $jalur->kuota_terisi }} pendaftar</small>
                            </div>
                        </div>
                    </div>

                    {{-- Periode Pendaftaran --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_buka">Tanggal Dibuka</label>
                                <input type="date" class="form-control @error('tanggal_buka') is-invalid @enderror" 
                                       id="tanggal_buka" name="tanggal_buka" value="{{ old('tanggal_buka', $jalur->tanggal_buka?->format('Y-m-d')) }}">
                                @error('tanggal_buka')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tanggal mulai menerima pendaftar</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_tutup">Tanggal Ditutup</label>
                                <input type="date" class="form-control @error('tanggal_tutup') is-invalid @enderror" 
                                       id="tanggal_tutup" name="tanggal_tutup" value="{{ old('tanggal_tutup', $jalur->tanggal_tutup?->format('Y-m-d')) }}">
                                @error('tanggal_tutup')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Tanggal terakhir menerima pendaftar</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" name="deskripsi" rows="2">{{ old('deskripsi', $jalur->deskripsi) }}</textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="persyaratan">Persyaratan Khusus</label>
                        <textarea class="form-control @error('persyaratan') is-invalid @enderror" 
                                  id="persyaratan" name="persyaratan" rows="4">{{ old('persyaratan', $jalur->persyaratan) }}</textarea>
                        @error('persyaratan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Tampilan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="warna">Warna <span class="text-danger">*</span></label>
                                <select class="form-control @error('warna') is-invalid @enderror" id="warna" name="warna" required>
                                    @foreach($warnaOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('warna', $jalur->warna) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('warna')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon <span class="text-danger">*</span></label>
                                <select class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" required>
                                    @foreach($iconOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('icon', $jalur->icon) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tampil_di_publik" name="tampil_di_publik" value="1" {{ old('tampil_di_publik', $jalur->tampil_di_publik) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="tampil_di_publik">Tampilkan di Halaman Publik</label>
                                </div>
                                <small class="text-muted">Info jalur muncul di landing page</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tampil_kuota" name="tampil_kuota" value="1" {{ old('tampil_kuota', $jalur->tampil_kuota ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="tampil_kuota">Tampilkan Kuota ke Publik</label>
                                </div>
                                <small class="text-muted">Jika tidak dicentang, kuota disembunyikan</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            {{-- Status Info --}}
                            <div class="callout callout-{{ $jalur->status == 'open' ? 'success' : ($jalur->status == 'closed' ? 'warning' : ($jalur->status == 'finished' ? 'secondary' : 'info')) }} py-2 px-3 mb-0">
                                <small>
                                    <strong>Status:</strong> 
                                    @switch($jalur->status)
                                        @case('open')
                                            <span class="text-success"><i class="fas fa-door-open mr-1"></i>Dibuka</span>
                                            @break
                                        @case('closed')
                                            <span class="text-warning"><i class="fas fa-pause mr-1"></i>Ditutup Sementara</span>
                                            @break
                                        @case('finished')
                                            <span class="text-secondary"><i class="fas fa-check mr-1"></i>Selesai</span>
                                            @break
                                        @default
                                            <span class="text-muted"><i class="fas fa-file mr-1"></i>Draft</span>
                                    @endswitch
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pilihan Program/Jurusan Section --}}
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-ul mr-2"></i>Pilihan Program/Jurusan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="pilihan_program_aktif" 
                                   name="pilihan_program_aktif" value="1" 
                                   {{ old('pilihan_program_aktif', $jalur->pilihan_program_aktif) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="pilihan_program_aktif">
                                <strong>Aktifkan Pilihan Program/Jurusan</strong>
                            </label>
                        </div>
                        <small class="text-muted">
                            Jika diaktifkan, pendaftar harus memilih program sebelum finalisasi (contoh: Reguler/Asrama, IPA/IPS, dll)
                        </small>
                    </div>

                    <div id="pilihan_program_settings" style="display: none;">
                        <hr>
                        
                        <div class="form-group">
                            <label>Tipe Pilihan <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="tipe_reguler_asrama" 
                                               name="pilihan_program_tipe" value="reguler_asrama"
                                               {{ old('pilihan_program_tipe', $jalur->pilihan_program_tipe) == 'reguler_asrama' ? 'checked' : '' }}>
                                        <label for="tipe_reguler_asrama" class="custom-control-label">
                                            <strong>Reguler/Asrama</strong>
                                            <br><small class="text-muted">Untuk pemilihan tipe pendidikan</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="tipe_jurusan" 
                                               name="pilihan_program_tipe" value="jurusan"
                                               {{ old('pilihan_program_tipe', $jalur->pilihan_program_tipe) == 'jurusan' ? 'checked' : '' }}>
                                        <label for="tipe_jurusan" class="custom-control-label">
                                            <strong>Jurusan</strong>
                                            <br><small class="text-muted">IPA, IPS, Bahasa, dll</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="tipe_custom" 
                                               name="pilihan_program_tipe" value="custom"
                                               {{ old('pilihan_program_tipe', $jalur->pilihan_program_tipe) == 'custom' ? 'checked' : '' }}>
                                        <label for="tipe_custom" class="custom-control-label">
                                            <strong>Custom</strong>
                                            <br><small class="text-muted">Isi pilihan sendiri</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pilihan yang Tersedia <span class="text-danger">*</span></label>
                            <div id="options_container">
                                @php
                                    $existingOptions = old('pilihan_program_options', $jalur->pilihan_program_options ?? []);
                                    if (empty($existingOptions)) {
                                        $existingOptions = ['', '']; // Default 2 empty options
                                    }
                                @endphp
                                @foreach($existingOptions as $index => $option)
                                <div class="input-group mb-2 option-row">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-grip-vertical"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="pilihan_program_options[]" 
                                           value="{{ $option }}" placeholder="Contoh: Reguler, Asrama, IPA, IPS, dll">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger btn-remove-option">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="btn_add_option">
                                <i class="fas fa-plus mr-1"></i> Tambah Pilihan
                            </button>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Minimal 2 pilihan. Pendaftar akan memilih salah satu.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="pilihan_program_catatan">Catatan/Instruksi untuk Pendaftar</label>
                            <textarea class="form-control" id="pilihan_program_catatan" 
                                      name="pilihan_program_catatan" rows="3" 
                                      placeholder="Contoh: Pilih program sesuai minat dan kemampuan Anda. Pilihan tidak dapat diubah setelah finalisasi.">{{ old('pilihan_program_catatan', $jalur->pilihan_program_catatan) }}</textarea>
                            <small class="text-muted">Akan ditampilkan di halaman pilihan program pendaftar</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.jalur.show', $jalur) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        {{-- Preview --}}
        <div class="card card-outline" id="preview-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview</h3>
            </div>
            <div class="card-body text-center">
                <i id="preview-icon" class="{{ $jalur->icon }} fa-3x text-{{ $jalur->warna }} mb-3"></i>
                <h5 id="preview-nama">{{ $jalur->nama }}</h5>
                <span id="preview-badge" class="badge badge-{{ $jalur->warna }}">{{ $jalur->kode }}</span>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Statistik</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="mb-0">{{ $jalur->kuota_terisi }}/{{ $jalur->kuota }}</h2>
                    <small class="text-muted">Pendaftar</small>
                </div>
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-{{ $jalur->persentaseKuota() >= 90 ? 'danger' : ($jalur->persentaseKuota() >= 70 ? 'warning' : 'success') }}" 
                         style="width: {{ $jalur->persentaseKuota() }}%">
                        {{ $jalur->persentaseKuota() }}%
                    </div>
                </div>
                <p class="text-center text-muted">Sisa Kuota: {{ $jalur->sisaKuota() }}</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function() {
    $('#kode').on('input', function() {
        $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''));
    });
    
    function updatePreview() {
        var nama = $('#nama').val() || 'Nama Jalur';
        var kode = $('#kode').val() || 'KODE';
        var warna = $('#warna').val();
        var icon = $('#icon').val();
        
        $('#preview-nama').text(nama);
        $('#preview-badge').text(kode).attr('class', 'badge badge-' + warna);
        $('#preview-icon').attr('class', icon + ' fa-3x text-' + warna + ' mb-3');
        $('#preview-card').attr('class', 'card card-outline card-' + warna);
    }
    
    $('#nama, #kode, #warna, #icon').on('input change', updatePreview);
    
    // ========== Pilihan Program Logic ==========
    
    // Toggle pilihan program settings
    function togglePilihanProgramSettings() {
        if ($('#pilihan_program_aktif').is(':checked')) {
            $('#pilihan_program_settings').slideDown();
            // Centang radio pertama jika belum ada yang dicentang
            if (!$('input[name="pilihan_program_tipe"]:checked').length) {
                $('#tipe_reguler_asrama').prop('checked', true);
            }
        } else {
            $('#pilihan_program_settings').slideUp();
        }
    }
    
    $('#pilihan_program_aktif').on('change', togglePilihanProgramSettings);
    togglePilihanProgramSettings(); // Init on page load
    
    // Preset options based on tipe
    const presets = {
        'reguler_asrama': ['Reguler', 'Asrama'],
        'jurusan': ['IPA', 'IPS', 'Bahasa'],
        'custom': ['', '']
    };
    
    $('input[name="pilihan_program_tipe"]').on('change', function() {
        const tipe = $(this).val();
        const preset = presets[tipe] || ['', ''];
        
        // Clear existing options
        $('#options_container').empty();
        
        // Add preset options
        preset.forEach(function(value) {
            addOptionRow(value);
        });
    });
    
    // Add option row
    function addOptionRow(value = '') {
        const row = $(`
            <div class="input-group mb-2 option-row">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-grip-vertical"></i></span>
                </div>
                <input type="text" class="form-control" name="pilihan_program_options[]" 
                       value="${value}" placeholder="Contoh: Reguler, Asrama, IPA, IPS, dll">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger btn-remove-option">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        $('#options_container').append(row);
    }
    
    // Add option button
    $('#btn_add_option').on('click', function() {
        addOptionRow();
    });
    
    // Remove option button
    $(document).on('click', '.btn-remove-option', function() {
        const totalRows = $('.option-row').length;
        if (totalRows > 2) {
            $(this).closest('.option-row').remove();
        } else {
            toastr.warning('Minimal harus ada 2 pilihan!');
        }
    });
    
    // Form validation before submit
    $('form').on('submit', function(e) {
        if ($('#pilihan_program_aktif').is(':checked')) {
            // Check if tipe is selected
            if (!$('input[name="pilihan_program_tipe"]:checked').length) {
                e.preventDefault();
                toastr.error('Pilih tipe pilihan program terlebih dahulu!');
                return false;
            }
            
            // Check if at least 2 non-empty options
            const options = $('input[name="pilihan_program_options[]"]').map(function() {
                return $(this).val().trim();
            }).get().filter(v => v !== '');
            
            if (options.length < 2) {
                e.preventDefault();
                toastr.error('Minimal harus ada 2 pilihan program yang diisi!');
                return false;
            }
        }
    });
});
</script>
@stop
