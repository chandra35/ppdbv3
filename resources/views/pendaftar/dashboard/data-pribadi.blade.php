@extends('layouts.pendaftar')

@section('title', 'Data Pribadi')
@section('page-title', 'Data Pribadi')

@section('breadcrumb')
<li class="breadcrumb-item active">Data Pribadi</li>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-2"></i>
                    Formulir Data Pribadi
                </h3>
            </div>
            <form action="{{ route('pendaftar.data-pribadi.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NISN</label>
                                <input type="text" class="form-control" value="{{ $calonSiswa->nisn }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIK <span class="text-muted">(16 digit)</span></label>
                                <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" 
                                       value="{{ old('nik', $calonSiswa->nik) }}" maxlength="16">
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                               value="{{ old('nama_lengkap', $calonSiswa->nama_lengkap) }}" required>
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                       value="{{ old('tempat_lahir', $calonSiswa->tempat_lahir) }}" required>
                                @error('tempat_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                       value="{{ old('tanggal_lahir', $calonSiswa->tanggal_lahir?->format('Y-m-d')) }}" required>
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $calonSiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $calonSiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Agama <span class="text-danger">*</span></label>
                                <select name="agama" class="form-control @error('agama') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                        <option value="{{ $agama }}" {{ old('agama', $calonSiswa->agama) == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                    @endforeach
                                </select>
                                @error('agama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. HP (WhatsApp) <span class="text-danger">*</span></label>
                                @php
                                    $displayPhone = old('nomor_hp', $calonSiswa->nomor_hp);
                                    // Convert +628xxx to 08xxx for display
                                    if (!empty($displayPhone) && str_starts_with($displayPhone, '+62')) {
                                        $displayPhone = '0' . substr($displayPhone, 3);
                                    }
                                @endphp
                                <input type="text" name="nomor_hp" class="form-control @error('nomor_hp') is-invalid @enderror" 
                                       value="{{ $displayPhone }}" 
                                       placeholder="08xxxxxxxxxx"
                                       pattern="0[0-9]{9,12}"
                                       inputmode="tel"
                                       required>
                                @error('nomor_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Hanya angka. Format: 08xx (tersimpan sebagai +628xx)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $calonSiswa->user->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fas fa-map-marker-alt mr-2"></i>Alamat Siswa</h5>

                    <div class="form-group">
                        <label>Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat_siswa" class="form-control @error('alamat_siswa') is-invalid @enderror" 
                                  rows="3" required>{{ old('alamat_siswa', $calonSiswa->alamat_siswa) }}</textarea>
                        @error('alamat_siswa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="copyToOrtu" name="copy_alamat_to_ortu" value="1" 
                               {{ old('copy_alamat_to_ortu', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="copyToOrtu">
                            <i class="fas fa-copy mr-1"></i> Salin alamat siswa ke alamat orang tua
                        </label>
                        <small class="form-text text-muted">Jika dicentang, alamat ini akan otomatis disalin ke data orang tua</small>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>RT</label>
                                <input type="number" name="rt_siswa" class="form-control @error('rt_siswa') is-invalid @enderror" 
                                       value="{{ old('rt_siswa', $calonSiswa->rt_siswa) }}" min="0" max="999">
                                @error('rt_siswa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>RW</label>
                                <input type="number" name="rw_siswa" class="form-control @error('rw_siswa') is-invalid @enderror" 
                                       value="{{ old('rw_siswa', $calonSiswa->rw_siswa) }}" min="0" max="999">
                                @error('rw_siswa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Provinsi <span class="text-danger">*</span></label>
                        <select name="provinsi_id_siswa" id="provinsi" class="form-control select2" required>
                            <option value="">-- Pilih Provinsi --</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->code }}" {{ old('provinsi_id_siswa', $calonSiswa->provinsi_id_siswa) == $province->code ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kabupaten/Kota <span class="text-danger">*</span></label>
                        <select name="kabupaten_id_siswa" id="kabupaten" class="form-control select2" required>
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kecamatan <span class="text-danger">*</span></label>
                        <select name="kecamatan_id_siswa" id="kecamatan" class="form-control select2" required>
                            <option value="">-- Pilih Kecamatan --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kelurahan/Desa <span class="text-danger">*</span></label>
                        <select name="kelurahan_id_siswa" id="kelurahan" class="form-control select2" required>
                            <option value="">-- Pilih Kelurahan/Desa --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kode Pos</label>
                        <input type="number" name="kodepos_siswa" class="form-control @error('kodepos_siswa') is-invalid @enderror" 
                               value="{{ old('kodepos_siswa', $calonSiswa->kodepos_siswa) }}" min="0" max="99999">
                        @error('kodepos_siswa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h5><i class="fas fa-school mr-2"></i>Asal Sekolah</h5>

                    <div class="form-group">
                        <label>NPSN</label>
                        <input type="text" name="npsn_asal_sekolah" class="form-control" 
                               value="{{ old('npsn_asal_sekolah', $calonSiswa->npsn_asal_sekolah) }}" readonly>
                        <small class="form-text text-muted">NPSN diisi otomatis dari data EMIS</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Asal Sekolah</label>
                        <input type="text" name="nama_sekolah_asal" class="form-control @error('nama_sekolah_asal') is-invalid @enderror" 
                               value="{{ old('nama_sekolah_asal', $calonSiswa->nama_sekolah_asal) }}" readonly>
                        @error('nama_sekolah_asal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nama sekolah diisi otomatis dari data EMIS</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">
                    <i class="fas fa-info-circle mr-2"></i>
                    Petunjuk Pengisian
                </h3>
            </div>
            <div class="card-body">
                <ul class="pl-3 mb-0">
                    <li class="mb-2">Isi semua data dengan benar dan sesuai dokumen resmi</li>
                    <li class="mb-2">Field dengan tanda <span class="text-danger">*</span> wajib diisi</li>
                    <li class="mb-2">NIK harus sesuai dengan Kartu Keluarga</li>
                    <li class="mb-2">Nomor HP harus aktif WhatsApp</li>
                    <li>Data yang sudah disimpan masih dapat diubah sebelum proses verifikasi</li>
                </ul>
            </div>
        </div>

        @if($calonSiswa->data_diri_completed)
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Data Pribadi Lengkap!</strong><br>
            Silakan lanjutkan mengisi data orang tua.
        </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Stored values
    const storedKabupaten = '{{ old('kabupaten_id_siswa', $calonSiswa->kabupaten_id_siswa) }}';
    const storedKecamatan = '{{ old('kecamatan_id_siswa', $calonSiswa->kecamatan_id_siswa) }}';
    const storedKelurahan = '{{ old('kelurahan_id_siswa', $calonSiswa->kelurahan_id_siswa) }}';

    // Load kabupaten on page load if provinsi selected
    const provinsiVal = $('#provinsi').val();
    if (provinsiVal) {
        loadKabupaten(provinsiVal, storedKabupaten);
    }

    // Province change
    $('#provinsi').on('change', function() {
        const provinceCode = $(this).val();
        $('#kabupaten').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        $('#kecamatan').html('<option value="">-- Pilih Kecamatan --</option>');
        $('#kelurahan').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
        if (provinceCode) {
            loadKabupaten(provinceCode);
        }
    });

    // Kabupaten change
    $('#kabupaten').on('change', function() {
        const cityCode = $(this).val();
        $('#kecamatan').html('<option value="">-- Pilih Kecamatan --</option>');
        $('#kelurahan').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
        if (cityCode) {
            loadKecamatan(cityCode);
        }
    });

    // Kecamatan change
    $('#kecamatan').on('change', function() {
        const districtCode = $(this).val();
        $('#kelurahan').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
        if (districtCode) {
            loadKelurahan(districtCode);
        }
    });

    function loadKabupaten(provinceCode, selected = null) {
        $.get('/api/indonesia/cities/' + provinceCode, function(data) {
            let options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
            data.forEach(function(item) {
                const isSelected = (item.code === selected) ? 'selected' : '';
                options += `<option value="${item.code}" ${isSelected}>${item.name}</option>`;
            });
            $('#kabupaten').html(options);
            
            if (selected) {
                loadKecamatan(selected, storedKecamatan);
            }
        });
    }

    function loadKecamatan(cityCode, selected = null) {
        $.get('/api/indonesia/districts/' + cityCode, function(data) {
            let options = '<option value="">-- Pilih Kecamatan --</option>';
            data.forEach(function(item) {
                const isSelected = (item.code === selected) ? 'selected' : '';
                options += `<option value="${item.code}" ${isSelected}>${item.name}</option>`;
            });
            $('#kecamatan').html(options);
            
            if (selected) {
                loadKelurahan(selected, storedKelurahan);
            }
        });
    }

    function loadKelurahan(districtCode, selected = null) {
        $.get('/api/indonesia/villages/' + districtCode, function(data) {
            let options = '<option value="">-- Pilih Kelurahan/Desa --</option>';
            data.forEach(function(item) {
                const isSelected = (item.code === selected) ? 'selected' : '';
                options += `<option value="${item.code}" ${isSelected}>${item.name}</option>`;
            });
            $('#kelurahan').html(options);
        });
    }
});
</script>
@endsection
