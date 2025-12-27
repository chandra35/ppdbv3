@extends('adminlte::page')

@section('title', 'Edit Data Pendaftar')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-control, .form-select {
        font-size: 13px;
        height: calc(1.8em + .75rem + 2px);
    }
    textarea.form-control {
        height: auto;
    }
    .select2-container .select2-selection--single {
        height: calc(1.8em + .75rem + 2px) !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.8em + .75rem) !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: calc(1.8em + .75rem + 2px) !important;
    }
    label.required::after {
        content: " *";
        color: red;
    }
    .subsection-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
</style>
@stop

@section('content_header')
    <h1>Edit Data Pendaftar</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.pendaftar.update', $pendaftar->id) }}" method="POST" id="formEditPendaftar">
        @csrf
        @method('PUT')
        
        <!-- Data Pribadi -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nisn">NISN</label>
                            <input type="text" id="nisn" class="form-control" 
                                value="{{ $pendaftar->nisn }}" disabled>
                            <small class="form-text text-muted">Tidak dapat diubah</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_lengkap" class="required">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" 
                                class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                value="{{ old('nama_lengkap', $pendaftar->nama_lengkap) }}" required>
                            @error('nama_lengkap')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nik" class="required">NIK</label>
                            <input type="text" name="nik" id="nik" 
                                class="form-control nik-input @error('nik') is-invalid @enderror" 
                                value="{{ old('nik', $pendaftar->nik) }}" 
                                maxlength="16" 
                                pattern="[0-9]{16}" 
                                inputmode="numeric"
                                placeholder="16 digit angka"
                                required>
                            @error('nik')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Hanya angka, 16 digit</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tempat_lahir" class="required">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" 
                                class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                value="{{ old('tempat_lahir', $pendaftar->tempat_lahir) }}" required>
                            @error('tempat_lahir')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_lahir" class="required">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                                class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                value="{{ old('tanggal_lahir', $pendaftar->tanggal_lahir ? \Carbon\Carbon::parse($pendaftar->tanggal_lahir)->format('Y-m-d') : '') }}" required>
                            @error('tanggal_lahir')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_kelamin" class="required">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" 
                                class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $pendaftar->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $pendaftar->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="agama" class="required">Agama</label>
                            <select name="agama" id="agama" 
                                class="form-control @error('agama') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="Islam" {{ old('agama', $pendaftar->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama', $pendaftar->agama) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('agama', $pendaftar->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('agama', $pendaftar->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama', $pendaftar->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('agama', $pendaftar->agama) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                            @error('agama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nomor_hp" class="required">No. HP/WhatsApp</label>
                            <input type="text" name="nomor_hp" id="nomor_hp" 
                                class="form-control phone-input @error('nomor_hp') is-invalid @enderror" 
                                value="{{ old('nomor_hp', $pendaftar->nomor_hp) }}" 
                                placeholder="08xxxxxxxxxx"
                                pattern="0[0-9]{9,12}"
                                inputmode="tel"
                                required>
                            @error('nomor_hp')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Hanya angka. Format: 08xx (tersimpan sebagai +628xx)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $pendaftar->user->email ?? '') }}">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alamat Lengkap -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Alamat Lengkap</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat_siswa">Alamat Lengkap (Nama Jalan/Dusun)</label>
                            <textarea name="alamat_siswa" id="alamat_siswa" rows="2" 
                                class="form-control @error('alamat_siswa') is-invalid @enderror">{{ old('alamat_siswa', $pendaftar->alamat_siswa) }}</textarea>
                            @error('alamat_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="rt_siswa">RT</label>
                            <input type="number" name="rt_siswa" id="rt_siswa" 
                                class="form-control @error('rt_siswa') is-invalid @enderror" 
                                value="{{ old('rt_siswa', $pendaftar->rt_siswa) }}" min="0" max="999">
                            @error('rt_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="rw_siswa">RW</label>
                            <input type="number" name="rw_siswa" id="rw_siswa" 
                                class="form-control @error('rw_siswa') is-invalid @enderror" 
                                value="{{ old('rw_siswa', $pendaftar->rw_siswa) }}" min="0" max="999">
                            @error('rw_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="provinsi_id_siswa" class="required">Provinsi</label>
                            <select name="provinsi_id_siswa" id="provinsi_id_siswa" 
                                class="form-control select2 @error('provinsi_id_siswa') is-invalid @enderror" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->code }}" 
                                        {{ old('provinsi_id_siswa', $pendaftar->provinsi_id_siswa) == $province->code ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provinsi_id_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kabupaten_id_siswa" class="required">Kabupaten/Kota</label>
                            <select name="kabupaten_id_siswa" id="kabupaten_id_siswa" 
                                class="form-control select2 @error('kabupaten_id_siswa') is-invalid @enderror" required>
                                <option value="">Pilih Kabupaten/Kota</option>
                            </select>
                            @error('kabupaten_id_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kecamatan_id_siswa" class="required">Kecamatan</label>
                            <select name="kecamatan_id_siswa" id="kecamatan_id_siswa" 
                                class="form-control select2 @error('kecamatan_id_siswa') is-invalid @enderror" required>
                                <option value="">Pilih Kecamatan</option>
                            </select>
                            @error('kecamatan_id_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kelurahan_id_siswa" class="required">Kelurahan/Desa</label>
                            <select name="kelurahan_id_siswa" id="kelurahan_id_siswa" 
                                class="form-control select2 @error('kelurahan_id_siswa') is-invalid @enderror" required>
                                <option value="">Pilih Kelurahan/Desa</option>
                            </select>
                            @error('kelurahan_id_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="kode_pos_siswa">Kode Pos</label>
                            <input type="number" name="kode_pos_siswa" id="kode_pos_siswa" 
                                class="form-control @error('kode_pos_siswa') is-invalid @enderror" 
                                value="{{ old('kode_pos_siswa', $pendaftar->kode_pos_siswa) }}" min="0" max="99999">
                            @error('kode_pos_siswa')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Orang Tua -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Data Orang Tua</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_kk">Nomor Kartu Keluarga (KK)</label>
                            <input type="text" name="no_kk" id="no_kk" 
                                class="form-control nik-input @error('no_kk') is-invalid @enderror" 
                                value="{{ old('no_kk', optional($pendaftar->ortu)->no_kk) }}"
                                maxlength="16" 
                                pattern="[0-9]{16}"
                                inputmode="numeric"
                                placeholder="16 digit angka">
                            @error('no_kk')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Hanya angka, 16 digit</small>
                        </div>
                    </div>
                </div>

                <div class="subsection-title">Data Ayah</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_ayah">Nama Ayah</label>
                            <input type="text" name="nama_ayah" id="nama_ayah" 
                                class="form-control @error('nama_ayah') is-invalid @enderror" 
                                value="{{ old('nama_ayah', optional($pendaftar->ortu)->nama_ayah) }}">
                            @error('nama_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nik_ayah">NIK Ayah</label>
                            <input type="text" name="nik_ayah" id="nik_ayah" 
                                class="form-control nik-input @error('nik_ayah') is-invalid @enderror" 
                                value="{{ old('nik_ayah', $pendaftar->ortu->nik_ayah ?? '') }}" 
                                maxlength="16"
                                pattern="[0-9]{16}"
                                inputmode="numeric"
                                placeholder="16 digit angka">
                            @error('nik_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tempat_lahir_ayah">Tempat Lahir Ayah</label>
                            <input type="text" name="tempat_lahir_ayah" id="tempat_lahir_ayah" 
                                class="form-control @error('tempat_lahir_ayah') is-invalid @enderror" 
                                value="{{ old('tempat_lahir_ayah', $pendaftar->ortu->tempat_lahir_ayah ?? '') }}">
                            @error('tempat_lahir_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_lahir_ayah">Tanggal Lahir Ayah</label>
                            <input type="date" name="tanggal_lahir_ayah" id="tanggal_lahir_ayah" 
                                class="form-control @error('tanggal_lahir_ayah') is-invalid @enderror" 
                                value="{{ old('tanggal_lahir_ayah', optional($pendaftar->ortu)->tanggal_lahir_ayah?->format('Y-m-d') ?? optional($pendaftar->ortu)->tanggal_lahir_ayah ?? '') }}">
                            @error('tanggal_lahir_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pendidikan_ayah">Pendidikan Ayah</label>
                            <select name="pendidikan_ayah" id="pendidikan_ayah" 
                                class="form-control @error('pendidikan_ayah') is-invalid @enderror">
                                <option value="">Pilih Pendidikan</option>
                                @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                    <option value="{{ $pend }}" {{ old('pendidikan_ayah', $pendaftar->ortu->pendidikan_ayah ?? '') == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                                @endforeach
                            </select>
                            @error('pendidikan_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pekerjaan_ayah">Pekerjaan Ayah</label>
                            <select name="pekerjaan_ayah" id="pekerjaan_ayah" 
                                class="form-control @error('pekerjaan_ayah') is-invalid @enderror">
                                <option value="">Pilih Pekerjaan</option>
                                <option value="tidak_bekerja" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'tidak_bekerja' ? 'selected' : '' }}>Tidak Bekerja</option>
                                <option value="pns" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'pns' ? 'selected' : '' }}>PNS</option>
                                <option value="tni_polri" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'tni_polri' ? 'selected' : '' }}>TNI/Polri</option>
                                <option value="swasta" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                                <option value="wiraswasta" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                <option value="petani" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'petani' ? 'selected' : '' }}>Petani</option>
                                <option value="nelayan" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'nelayan' ? 'selected' : '' }}>Nelayan</option>
                                <option value="buruh" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'buruh' ? 'selected' : '' }}>Buruh</option>
                                <option value="guru" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'guru' ? 'selected' : '' }}>Guru/Dosen</option>
                                <option value="dokter" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'dokter' ? 'selected' : '' }}>Dokter</option>
                                <option value="pedagang" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'pedagang' ? 'selected' : '' }}>Pedagang</option>
                                <option value="ibu_rumah_tangga" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'ibu_rumah_tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                                <option value="pensiunan" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'pensiunan' ? 'selected' : '' }}>Pensiunan</option>
                                <option value="lainnya" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('pekerjaan_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="penghasilan_ayah">Penghasilan Ayah</label>
                            <select name="penghasilan_ayah" id="penghasilan_ayah" 
                                class="form-control @error('penghasilan_ayah') is-invalid @enderror">
                                <option value="">Pilih Penghasilan</option>
                                <option value="tidak_ada" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == 'tidak_ada' ? 'selected' : '' }}>Tidak ada</option>
                                <option value="dibawah_1jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == 'dibawah_1jt' ? 'selected' : '' }}>< Rp 1.000.000</option>
                                <option value="1jt_2jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == '1jt_2jt' ? 'selected' : '' }}>Rp 1.000.000 - Rp 2.000.000</option>
                                <option value="2jt_3jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == '2jt_3jt' ? 'selected' : '' }}>Rp 2.000.000 - Rp 3.000.000</option>
                                <option value="3jt_5jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == '3jt_5jt' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                                <option value="5jt_10jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == '5jt_10jt' ? 'selected' : '' }}>Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="diatas_10jt" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == 'diatas_10jt' ? 'selected' : '' }}>> Rp 10.000.000</option>
                            </select>
                            @error('penghasilan_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hp_ayah">No. HP Ayah</label>
                            <input type="text" name="hp_ayah" id="hp_ayah" 
                                class="form-control phone-input @error('hp_ayah') is-invalid @enderror" 
                                value="{{ old('hp_ayah', $pendaftar->ortu->hp_ayah ?? '') }}"
                                placeholder="08xxxxxxxxxx"
                                pattern="0[0-9]{9,12}"
                                inputmode="tel">
                            @error('hp_ayah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="subsection-title mt-3">Data Ibu</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_ibu">Nama Ibu</label>
                            <input type="text" name="nama_ibu" id="nama_ibu" 
                                class="form-control @error('nama_ibu') is-invalid @enderror" 
                                value="{{ old('nama_ibu', optional($pendaftar->ortu)->nama_ibu) }}">
                            @error('nama_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nik_ibu">NIK Ibu</label>
                            <input type="text" name="nik_ibu" id="nik_ibu" 
                                class="form-control nik-input @error('nik_ibu') is-invalid @enderror" 
                                value="{{ old('nik_ibu', $pendaftar->ortu->nik_ibu ?? '') }}" 
                                maxlength="16"
                                pattern="[0-9]{16}"
                                inputmode="numeric"
                                placeholder="16 digit angka">
                            @error('nik_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tempat_lahir_ibu">Tempat Lahir Ibu</label>
                            <input type="text" name="tempat_lahir_ibu" id="tempat_lahir_ibu" 
                                class="form-control @error('tempat_lahir_ibu') is-invalid @enderror" 
                                value="{{ old('tempat_lahir_ibu', $pendaftar->ortu->tempat_lahir_ibu ?? '') }}">
                            @error('tempat_lahir_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_lahir_ibu">Tanggal Lahir Ibu</label>
                            <input type="date" name="tanggal_lahir_ibu" id="tanggal_lahir_ibu" 
                                class="form-control @error('tanggal_lahir_ibu') is-invalid @enderror" 
                                value="{{ old('tanggal_lahir_ibu', optional($pendaftar->ortu)->tanggal_lahir_ibu?->format('Y-m-d') ?? optional($pendaftar->ortu)->tanggal_lahir_ibu ?? '') }}">
                            @error('tanggal_lahir_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pendidikan_ibu">Pendidikan Ibu</label>
                            <select name="pendidikan_ibu" id="pendidikan_ibu" 
                                class="form-control @error('pendidikan_ibu') is-invalid @enderror">
                                <option value="">Pilih Pendidikan</option>
                                @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                    <option value="{{ $pend }}" {{ old('pendidikan_ibu', $pendaftar->ortu->pendidikan_ibu ?? '') == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                                @endforeach
                            </select>
                            @error('pendidikan_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pekerjaan_ibu">Pekerjaan Ibu</label>
                            <select name="pekerjaan_ibu" id="pekerjaan_ibu" 
                                class="form-control @error('pekerjaan_ibu') is-invalid @enderror">
                                <option value="">Pilih Pekerjaan</option>
                                <option value="tidak_bekerja" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'tidak_bekerja' ? 'selected' : '' }}>Tidak Bekerja</option>
                                <option value="pns" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'pns' ? 'selected' : '' }}>PNS</option>
                                <option value="tni_polri" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'tni_polri' ? 'selected' : '' }}>TNI/Polri</option>
                                <option value="swasta" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                                <option value="wiraswasta" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                <option value="petani" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'petani' ? 'selected' : '' }}>Petani</option>
                                <option value="nelayan" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'nelayan' ? 'selected' : '' }}>Nelayan</option>
                                <option value="buruh" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'buruh' ? 'selected' : '' }}>Buruh</option>
                                <option value="guru" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'guru' ? 'selected' : '' }}>Guru/Dosen</option>
                                <option value="dokter" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'dokter' ? 'selected' : '' }}>Dokter</option>
                                <option value="pedagang" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'pedagang' ? 'selected' : '' }}>Pedagang</option>
                                <option value="ibu_rumah_tangga" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'ibu_rumah_tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                                <option value="pensiunan" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'pensiunan' ? 'selected' : '' }}>Pensiunan</option>
                                <option value="lainnya" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('pekerjaan_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="penghasilan_ibu">Penghasilan Ibu</label>
                            <select name="penghasilan_ibu" id="penghasilan_ibu" 
                                class="form-control @error('penghasilan_ibu') is-invalid @enderror">
                                <option value="">Pilih Penghasilan</option>
                                <option value="tidak_ada" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == 'tidak_ada' ? 'selected' : '' }}>Tidak ada</option>
                                <option value="dibawah_1jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == 'dibawah_1jt' ? 'selected' : '' }}>< Rp 1.000.000</option>
                                <option value="1jt_2jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == '1jt_2jt' ? 'selected' : '' }}>Rp 1.000.000 - Rp 2.000.000</option>
                                <option value="2jt_3jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == '2jt_3jt' ? 'selected' : '' }}>Rp 2.000.000 - Rp 3.000.000</option>
                                <option value="3jt_5jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == '3jt_5jt' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                                <option value="5jt_10jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == '5jt_10jt' ? 'selected' : '' }}>Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="diatas_10jt" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == 'diatas_10jt' ? 'selected' : '' }}>> Rp 10.000.000</option>
                            </select>
                            @error('penghasilan_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hp_ibu">No. HP Ibu</label>
                            <input type="text" name="hp_ibu" id="hp_ibu" 
                                class="form-control phone-input @error('hp_ibu') is-invalid @enderror" 
                                value="{{ old('hp_ibu', $pendaftar->ortu->hp_ibu ?? '') }}"
                                placeholder="08xxxxxxxxxx"
                                pattern="0[0-9]{9,12}"
                                inputmode="tel">
                            @error('hp_ibu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Asal Sekolah -->
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Data Asal Sekolah</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="npsn_sekolah_asal">NPSN Sekolah</label>
                            <input type="text" name="npsn_sekolah_asal" id="npsn_sekolah_asal" 
                                class="form-control @error('npsn_sekolah_asal') is-invalid @enderror" 
                                value="{{ old('npsn_sekolah_asal', $pendaftar->npsn_sekolah_asal) }}"
                                maxlength="8"
                                placeholder="8 digit">
                            @error('npsn_sekolah_asal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Data dari API EMIS/Kemdikbud saat registrasi</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_sekolah_asal">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah_asal" id="nama_sekolah_asal" 
                                class="form-control @error('nama_sekolah_asal') is-invalid @enderror" 
                                value="{{ old('nama_sekolah_asal', $pendaftar->nama_sekolah_asal) }}">
                            @error('nama_sekolah_asal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat_sekolah_asal">Alamat Sekolah</label>
                            <textarea name="alamat_sekolah_asal" id="alamat_sekolah_asal" rows="3" 
                                class="form-control @error('alamat_sekolah_asal') is-invalid @enderror">{{ old('alamat_sekolah_asal', $pendaftar->alamat_sekolah_asal) }}</textarea>
                            @error('alamat_sekolah_asal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Show success/error message from session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonText: 'OK'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal!',
            html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            confirmButtonText: 'OK'
        });
    @endif

    // Initialize Select2
    $('.select2').select2({
        theme: 'classic',
        width: '100%'
    });

    // Store initial values for cascading
    const storedKabupaten = '{{ old("kabupaten_id_siswa", $pendaftar->kabupaten_id_siswa) }}';
    const storedKecamatan = '{{ old("kecamatan_id_siswa", $pendaftar->kecamatan_id_siswa) }}';
    const storedKelurahan = '{{ old("kelurahan_id_siswa", $pendaftar->kelurahan_id_siswa) }}';

    // Load initial cascading data
    const initialProvinsi = $('#provinsi_id_siswa').val();
    if (initialProvinsi) {
        loadKabupaten(initialProvinsi, storedKabupaten);
    }

    // Provinsi change event
    $('#provinsi_id_siswa').on('change', function() {
        const provinceCode = $(this).val();
        $('#kabupaten_id_siswa').html('<option value="">Pilih Kabupaten/Kota</option>').trigger('change');
        $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>').trigger('change');
        $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>').trigger('change');
        
        if (provinceCode) {
            loadKabupaten(provinceCode, null);
        }
    });

    // Kabupaten change event
    $('#kabupaten_id_siswa').on('change', function() {
        const cityCode = $(this).val();
        $('#kecamatan_id_siswa').html('<option value="">Pilih Kecamatan</option>').trigger('change');
        $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>').trigger('change');
        
        if (cityCode) {
            loadKecamatan(cityCode, null);
        }
    });

    // Kecamatan change event
    $('#kecamatan_id_siswa').on('change', function() {
        const districtCode = $(this).val();
        $('#kelurahan_id_siswa').html('<option value="">Pilih Kelurahan/Desa</option>').trigger('change');
        
        if (districtCode) {
            loadKelurahan(districtCode, null);
        }
    });

    // Load Kabupaten function
    function loadKabupaten(provinceCode, selectedValue) {
        $.ajax({
            url: '/laravolt/indonesia/cities',
            type: 'GET',
            data: { province_code: provinceCode },
            success: function(data) {
                let options = '<option value="">Pilih Kabupaten/Kota</option>';
                data.forEach(function(city) {
                    const selected = selectedValue && city.code == selectedValue ? 'selected' : '';
                    options += `<option value="${city.code}" ${selected}>${city.name}</option>`;
                });
                $('#kabupaten_id_siswa').html(options).trigger('change');
                
                // Load next level if there's a stored value
                if (selectedValue) {
                    loadKecamatan(selectedValue, storedKecamatan);
                }
            }
        });
    }

    // Load Kecamatan function
    function loadKecamatan(cityCode, selectedValue) {
        $.ajax({
            url: '/laravolt/indonesia/districts',
            type: 'GET',
            data: { city_code: cityCode },
            success: function(data) {
                let options = '<option value="">Pilih Kecamatan</option>';
                data.forEach(function(district) {
                    const selected = selectedValue && district.code == selectedValue ? 'selected' : '';
                    options += `<option value="${district.code}" ${selected}>${district.name}</option>`;
                });
                $('#kecamatan_id_siswa').html(options).trigger('change');
                
                // Load next level if there's a stored value
                if (selectedValue) {
                    loadKelurahan(selectedValue, storedKelurahan);
                }
            }
        });
    }

    // Load Kelurahan function
    function loadKelurahan(districtCode, selectedValue) {
        $.ajax({
            url: '/laravolt/indonesia/villages',
            type: 'GET',
            data: { district_code: districtCode },
            success: function(data) {
                let options = '<option value="">Pilih Kelurahan/Desa</option>';
                data.forEach(function(village) {
                    const selected = selectedValue && village.code == selectedValue ? 'selected' : '';
                    options += `<option value="${village.code}" ${selected}>${village.name}</option>`;
                });
                $('#kelurahan_id_siswa').html(options).trigger('change');
            }
        });
    }

    // Form submit confirmation
    $('#formEditPendaftar').on('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan perubahan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // ============================================
    // NIK VALIDATION - HANYA ANGKA
    // ============================================
    
    // Debug: Check berapa input yang ditemukan
    console.log('NIK inputs found:', $('.nik-input').length);
    console.log('Phone inputs found:', $('.phone-input').length);
    
    // Prevent keypress huruf pada NIK (realtime block)
    $(document).on('keypress', '.nik-input', function(e) {
        // Hanya izinkan angka 0-9
        const charCode = (e.which) ? e.which : e.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
            return false;
        }
        // Max 16 digit
        if (this.value.length >= 16) {
            e.preventDefault();
            return false;
        }
    });

    // Cleanup on input (backup jika ada cara lain input)
    $(document).on('input', '.nik-input', function() {
        // Remove semua karakter non-angka
        this.value = this.value.replace(/\D/g, '');
        // Max 16 digit
        if (this.value.length > 16) {
            this.value = this.value.slice(0, 16);
        }
    });

    // Prevent paste non-numeric di NIK
    $(document).on('paste', '.nik-input', function(e) {
        e.preventDefault();
        let pastedText = (e.originalEvent || e).clipboardData.getData('text/plain');
        // Hanya ambil angka dari paste
        let numericOnly = pastedText.replace(/\D/g, '').slice(0, 16);
        this.value = numericOnly;
    });

    // ============================================
    // PHONE NUMBER VALIDATION - HANYA ANGKA
    // ============================================
    
    // Prevent keypress huruf pada HP (realtime block)
    $(document).on('keypress', '.phone-input', function(e) {
        // Hanya izinkan angka 0-9
        const charCode = (e.which) ? e.which : e.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
            return false;
        }
        // Max 13 digit
        if (this.value.length >= 13) {
            e.preventDefault();
            return false;
        }
    });

    // Phone number formatting and validation
    $(document).on('input', '.phone-input', function() {
        // Remove semua karakter non-angka (huruf, spasi, simbol, +, -, dll)
        let value = this.value.replace(/\D/g, '');
        
        // Ensure starts with 0
        if (value.length > 0 && value[0] !== '0') {
            value = '0' + value;
        }
        
        // Max 13 digits (0812xxxxxxxxx)
        if (value.length > 13) {
            value = value.slice(0, 13);
        }
        
        this.value = value;
    });

    // Prevent paste non-numeric di HP
    $(document).on('paste', '.phone-input', function(e) {
        e.preventDefault();
        let pastedText = (e.originalEvent || e).clipboardData.getData('text/plain');
        // Hanya ambil angka dari paste
        let numericOnly = pastedText.replace(/\D/g, '');
        // Ensure starts with 0
        if (numericOnly.length > 0 && numericOnly[0] !== '0') {
            numericOnly = '0' + numericOnly;
        }
        // Max 13 digits
        this.value = numericOnly.slice(0, 13);
    });

    // Convert phone display: Show +628xx if stored, otherwise show 08xx
    $('.phone-input').each(function() {
        let value = $(this).val();
        if (value && value.startsWith('+62')) {
            // Convert +628xxx to 08xxx for display
            $(this).val('0' + value.substring(3));
        }
    });

    // Form submit confirmation
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan perubahan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@stop
