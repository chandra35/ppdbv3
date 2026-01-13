@extends('layouts.pendaftar')

@section('title', 'Data Orang Tua')
@section('page-title', 'Data Orang Tua')

@section('breadcrumb')
<li class="breadcrumb-item active">Data Orang Tua</li>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if($calonSiswa->is_finalisasi)
        <div class="alert alert-warning">
            <h5><i class="fas fa-lock mr-2"></i>Data Sudah Difinalisasi</h5>
            <p class="mb-0">Data pendaftaran Anda sudah difinalisasi dan tidak dapat diubah. Jika terdapat kesalahan data, silakan hubungi panitia.</p>
        </div>
        @endif
        
        <form action="{{ route('pendaftar.data-ortu.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Data Kartu Keluarga -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-2"></i>
                        Data Kartu Keluarga
                        @if($calonSiswa->is_finalisasi)
                        <span class="badge badge-warning ml-2"><i class="fas fa-lock"></i> Terkunci</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nomor Kartu Keluarga (KK)</label>
                        <input type="text" name="no_kk" class="form-control @error('no_kk') is-invalid @enderror" 
                               value="{{ old('no_kk', $ortu->no_kk) }}" 
                               maxlength="16" 
                               pattern="[0-9]{16}"
                               inputmode="numeric"
                               placeholder="16 digit angka">
                        @error('no_kk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hanya angka, 16 digit</small>
                    </div>
                </div>
            </div>

            <!-- Data Ayah -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-male mr-2"></i>
                        Data Ayah
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Ayah <span class="text-danger">*</span></label>
                                <input type="text" name="nama_ayah" class="form-control @error('nama_ayah') is-invalid @enderror" 
                                       value="{{ old('nama_ayah', $ortu->nama_ayah) }}" required>
                                @error('nama_ayah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIK Ayah</label>
                                <input type="text" name="nik_ayah" class="form-control" 
                                       value="{{ old('nik_ayah', $ortu->nik_ayah) }}" maxlength="16">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir_ayah" class="form-control" 
                                       value="{{ old('tempat_lahir_ayah', $ortu->tempat_lahir_ayah) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="text" name="tanggal_lahir_ayah" id="tanggal_lahir_ayah" class="form-control datepicker" 
                                       value="{{ old('tanggal_lahir_ayah', $ortu->tanggal_lahir_ayah?->format('d/m/Y') ?? '') }}"
                                       placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pendidikan</label>
                                <select name="pendidikan_ayah" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                        <option value="{{ $pend }}" {{ old('pendidikan_ayah', $ortu->pendidikan_ayah) == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pekerjaan</label>
                                <select name="pekerjaan_ayah" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                        <option value="{{ $key }}" {{ old('pekerjaan_ayah', $ortu->pekerjaan_ayah) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Penghasilan</label>
                                <select name="penghasilan_ayah" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                        <option value="{{ $key }}" {{ old('penghasilan_ayah', $ortu->penghasilan_ayah) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>No. HP Ayah</label>
                        <input type="text" name="hp_ayah" class="form-control @error('hp_ayah') is-invalid @enderror" 
                               value="{{ old('hp_ayah', $ortu->hp_ayah) }}"
                               placeholder="08xxxxxxxxxx"
                               pattern="0[0-9]{9,12}"
                               inputmode="tel">
                        @error('hp_ayah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Ibu -->
            <div class="card">
                <div class="card-header bg-pink">
                    <h3 class="card-title text-white">
                        <i class="fas fa-female mr-2"></i>
                        Data Ibu
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Ibu <span class="text-danger">*</span></label>
                                <input type="text" name="nama_ibu" class="form-control @error('nama_ibu') is-invalid @enderror" 
                                       value="{{ old('nama_ibu', $ortu->nama_ibu) }}" required>
                                @error('nama_ibu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIK Ibu</label>
                                <input type="text" name="nik_ibu" class="form-control" 
                                       value="{{ old('nik_ibu', $ortu->nik_ibu) }}" maxlength="16">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir_ibu" class="form-control" 
                                       value="{{ old('tempat_lahir_ibu', $ortu->tempat_lahir_ibu) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="text" name="tanggal_lahir_ibu" id="tanggal_lahir_ibu" class="form-control datepicker" 
                                       value="{{ old('tanggal_lahir_ibu', $ortu->tanggal_lahir_ibu?->format('d/m/Y') ?? '') }}"
                                       placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pendidikan</label>
                                <select name="pendidikan_ibu" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                        <option value="{{ $pend }}" {{ old('pendidikan_ibu', $ortu->pendidikan_ibu) == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pekerjaan</label>
                                <select name="pekerjaan_ibu" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                        <option value="{{ $key }}" {{ old('pekerjaan_ibu', $ortu->pekerjaan_ibu) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Penghasilan</label>
                                <select name="penghasilan_ibu" class="form-control">
                                    <option value="">-- Pilih --</option>
                                    @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                        <option value="{{ $key }}" {{ old('penghasilan_ibu', $ortu->penghasilan_ibu) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>No. HP Ibu</label>
                        <input type="text" name="hp_ibu" class="form-control @error('hp_ibu') is-invalid @enderror" 
                               value="{{ old('hp_ibu', $ortu->hp_ibu) }}"
                               placeholder="08xxxxxxxxxx"
                               pattern="0[0-9]{9,12}"
                               inputmode="tel">
                        @error('hp_ibu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Alamat Orang Tua -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-home mr-2"></i>
                        Alamat Orang Tua
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea name="alamat_ortu" class="form-control @error('alamat_ortu') is-invalid @enderror" 
                                  rows="3" required>{{ old('alamat_ortu', $ortu->alamat_ortu) }}</textarea>
                        @error('alamat_ortu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provinsi <span class="text-danger">*</span></label>
                                <select name="provinsi_id" id="provinsi_ortu" class="form-control select2" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->code }}" {{ old('provinsi_id', $ortu->provinsi_id) == $province->code ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kabupaten/Kota <span class="text-danger">*</span></label>
                                <select name="kabupaten_id" id="kabupaten_ortu" class="form-control select2" required>
                                    <option value="">-- Pilih Kabupaten/Kota --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kecamatan <span class="text-danger">*</span></label>
                                <select name="kecamatan_id" id="kecamatan_ortu" class="form-control select2" required>
                                    <option value="">-- Pilih Kecamatan --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelurahan/Desa <span class="text-danger">*</span></label>
                                <select name="kelurahan_id" id="kelurahan_ortu" class="form-control select2" required>
                                    <option value="">-- Pilih Kelurahan/Desa --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Wali (Optional) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-friends mr-2"></i>
                        Data Wali <small class="text-muted">(Opsional)</small>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Wali</label>
                                <input type="text" name="nama_wali" class="form-control" 
                                       value="{{ old('nama_wali', $ortu->nama_wali) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hubungan dengan Siswa</label>
                                <input type="text" name="hubungan_wali" class="form-control" 
                                       value="{{ old('hubungan_wali', $ortu->hubungan_wali) }}" 
                                       placeholder="Contoh: Paman, Kakek, dll">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pekerjaan Wali</label>
                                <input type="text" name="pekerjaan_wali" class="form-control" 
                                       value="{{ old('pekerjaan_wali', $ortu->pekerjaan_wali) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. HP Wali</label>
                                <input type="text" name="nomor_hp_wali" class="form-control" 
                                       value="{{ old('nomor_hp_wali', $ortu->nomor_hp_wali) }}">
                            </div>
                        </div>
                    </div>
                </div>
                @if(!$calonSiswa->is_finalisasi)
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
                @endif
            </div>
        </form>
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
                    <li class="mb-2">Isi data sesuai dengan Kartu Keluarga</li>
                    <li class="mb-2">Data ayah dan ibu wajib diisi</li>
                    <li class="mb-2">Data wali diisi jika siswa tidak tinggal bersama orang tua</li>
                    <li>Pastikan nomor HP yang diisi aktif</li>
                </ul>
            </div>
        </div>

        @if($calonSiswa->data_ortu_completed)
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Data Orang Tua Lengkap!</strong><br>
            Silakan lanjutkan upload dokumen.
        </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Disable all form inputs if finalized
    @if($calonSiswa->is_finalisasi)
    $('input, select, textarea').prop('disabled', true);
    $('.select2').select2('destroy');
    @else
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
    @endif

    const storedKabupaten = '{{ old('kabupaten_id', $ortu->kabupaten_id) }}';
    const storedKecamatan = '{{ old('kecamatan_id', $ortu->kecamatan_id) }}';
    const storedKelurahan = '{{ old('kelurahan_id', $ortu->kelurahan_id) }}';

    const provinsiVal = $('#provinsi_ortu').val();
    if (provinsiVal) {
        loadKabupaten(provinsiVal, storedKabupaten);
    }

    $('#provinsi_ortu').on('change', function() {
        const provinceCode = $(this).val();
        $('#kabupaten_ortu').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        $('#kecamatan_ortu').html('<option value="">-- Pilih Kecamatan --</option>');
        $('#kelurahan_ortu').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
        if (provinceCode) {
            loadKabupaten(provinceCode);
        }
    });

    $('#kabupaten_ortu').on('change', function() {
        const cityCode = $(this).val();
        $('#kecamatan_ortu').html('<option value="">-- Pilih Kecamatan --</option>');
        $('#kelurahan_ortu').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
        if (cityCode) {
            loadKecamatan(cityCode);
        }
    });

    $('#kecamatan_ortu').on('change', function() {
        const districtCode = $(this).val();
        $('#kelurahan_ortu').html('<option value="">-- Pilih Kelurahan/Desa --</option>');
        
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
            $('#kabupaten_ortu').html(options);
            
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
            $('#kecamatan_ortu').html(options);
            
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
            $('#kelurahan_ortu').html(options);
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.datepicker', {
        dateFormat: 'd/m/Y',
        locale: 'id',
        allowInput: true,
        maxDate: 'today'
    });
});
</script>
@endsection
