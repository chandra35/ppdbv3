@extends('adminlte::page')

@section('title', 'Tambah Pendaftar Baru')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
.wizard-step {
    display: none;
}
.wizard-step.active {
    display: block;
}
.wizard-nav {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}
.wizard-nav-item {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: 50px;
    background: #e9ecef;
    margin: 0 5px;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s;
}
.wizard-nav-item.active {
    background: #007bff;
    color: #fff;
}
.wizard-nav-item.completed {
    background: #28a745;
    color: #fff;
}
.wizard-nav-item .step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-size: 14px;
}
.wizard-nav-item.active .step-number,
.wizard-nav-item.completed .step-number {
    background: rgba(255,255,255,0.4);
}
.card-step {
    border: 2px solid #dee2e6;
    border-radius: 15px;
}
.card-step.active {
    border-color: #007bff;
}
.required-field::after {
    content: " *";
    color: red;
}
.info-box-manual {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    margin-bottom: 20px;
}
.credential-box {
    background: #f8f9fa;
    border: 2px dashed #28a745;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}
.credential-box h4 {
    color: #28a745;
    margin-bottom: 15px;
}
.credential-item {
    font-size: 18px;
    margin: 10px 0;
}
.credential-item strong {
    color: #333;
}
.credential-item .value {
    background: #e9ecef;
    padding: 5px 15px;
    border-radius: 5px;
    font-family: monospace;
    margin-left: 10px;
}
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-plus mr-2"></i>Tambah Pendaftar Baru</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.pendaftar.index') }}">Pendaftar</a></li>
                <li class="breadcrumb-item active">Tambah Baru</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Info Box --}}
        <div class="info-box-manual">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-info-circle fa-3x"></i>
                </div>
                <div class="col">
                    <h5 class="mb-1"><i class="fas fa-hand-paper mr-2"></i>Pendaftaran Manual oleh Admin/Verifikator</h5>
                    <p class="mb-0">
                        Fitur ini untuk membantu pendaftar yang tidak bisa mendaftar online (gaptek/kendala teknis). 
                        <strong>NISN diinput manual tanpa validasi API</strong> - pastikan data sudah benar sebelum disimpan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Wizard Navigation --}}
        <div class="wizard-nav">
            <div class="wizard-nav-item active" data-step="1">
                <span class="step-number">1</span>
                <span>Data PPDB</span>
            </div>
            <div class="wizard-nav-item" data-step="2">
                <span class="step-number">2</span>
                <span>Data Diri</span>
            </div>
            <div class="wizard-nav-item" data-step="3">
                <span class="step-number">3</span>
                <span>Data Orang Tua</span>
            </div>
            <div class="wizard-nav-item" data-step="4">
                <span class="step-number">4</span>
                <span>Konfirmasi</span>
            </div>
        </div>

        <form id="formTambahPendaftar" action="{{ route('admin.pendaftar.store') }}" method="POST">
            @csrf

            {{-- Step 1: Data PPDB --}}
            <div class="wizard-step active" id="step-1">
                <div class="card card-step active">
                    <div class="card-header bg-primary">
                        <h3 class="card-title text-white">
                            <i class="fas fa-graduation-cap mr-2"></i>Pilih Jalur & Gelombang Pendaftaran
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Tahun Pelajaran Aktif:</strong> {{ $tahunPelajaran->nama }}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Jalur Pendaftaran</label>
                                    <select name="jalur_pendaftaran_id" id="jalur_pendaftaran_id" class="form-control select2" required>
                                        <option value="">-- Pilih Jalur --</option>
                                        @foreach($jalurList as $jalur)
                                            <option value="{{ $jalur->id }}" 
                                                    data-gelombang="{{ $jalur->gelombang->toJson() }}"
                                                    data-kuota="{{ $jalur->kuota }}"
                                                    data-terisi="{{ $jalur->kuota_terisi }}">
                                                {{ $jalur->nama }} (Sisa: {{ $jalur->kuota - $jalur->kuota_terisi }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Gelombang Pendaftaran</label>
                                    <select name="gelombang_pendaftaran_id" id="gelombang_pendaftaran_id" class="form-control select2" required disabled>
                                        <option value="">-- Pilih Gelombang --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="jalurInfo" class="mt-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Kuota Jalur</span>
                                            <span class="info-box-number" id="kuotaJalur">0</span>
                                            <div class="progress">
                                                <div class="progress-bar" id="progressKuota" style="width: 0%"></div>
                                            </div>
                                            <span class="progress-description" id="kuotaText">Terisi: 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-primary btn-next" data-next="2">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 2: Data Diri Siswa --}}
            <div class="wizard-step" id="step-2">
                <div class="card card-step">
                    <div class="card-header bg-success">
                        <h3 class="card-title text-white">
                            <i class="fas fa-user mr-2"></i>Data Diri Siswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">NISN (Input Manual)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-warning text-dark">
                                                <i class="fas fa-keyboard"></i>
                                            </span>
                                        </div>
                                        <input type="text" name="nisn" id="nisn" class="form-control" 
                                               maxlength="10" pattern="[0-9]{10}" required
                                               placeholder="Masukkan 10 digit NISN">
                                    </div>
                                    <small class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        NISN diinput manual tanpa validasi API. Pastikan NISN benar!
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NIK (16 digit)</label>
                                    <input type="text" name="nik" class="form-control" 
                                           maxlength="16" pattern="[0-9]{16}"
                                           placeholder="Masukkan 16 digit NIK">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required-field">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required
                                   placeholder="Nama sesuai akta/ijazah">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Agama</label>
                                    <select name="agama" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                            <option value="{{ $agama }}">{{ $agama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">No. HP (WhatsApp)</label>
                                    <input type="text" name="nomor_hp" class="form-control" required
                                           placeholder="08xxxxxxxxxx" pattern="0[0-9]{9,12}">
                                    <small class="form-text text-muted">Format: 08xx (akan disimpan sebagai +628xx)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                           placeholder="email@contoh.com">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5><i class="fas fa-map-marker-alt mr-2"></i>Alamat Siswa</h5>

                        <div class="form-group">
                            <label class="required-field">Alamat Lengkap</label>
                            <textarea name="alamat_siswa" class="form-control" rows="2" required
                                      placeholder="Jalan/Dusun/Kampung..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>RT</label>
                                    <input type="number" name="rt_siswa" class="form-control" min="0" max="999">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>RW</label>
                                    <input type="number" name="rw_siswa" class="form-control" min="0" max="999">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Pos</label>
                                    <input type="number" name="kodepos_siswa" class="form-control" min="0" max="99999">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Provinsi</label>
                                    <select name="provinsi_id_siswa" id="provinsi_siswa" class="form-control select2" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->code }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Kabupaten/Kota</label>
                                    <select name="kabupaten_id_siswa" id="kabupaten_siswa" class="form-control select2" required>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Kecamatan</label>
                                    <select name="kecamatan_id_siswa" id="kecamatan_siswa" class="form-control select2" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Kelurahan/Desa</label>
                                    <select name="kelurahan_id_siswa" id="kelurahan_siswa" class="form-control select2" required>
                                        <option value="">-- Pilih Kelurahan/Desa --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5><i class="fas fa-school mr-2"></i>Asal Sekolah</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NPSN Sekolah Asal</label>
                                    <input type="text" name="npsn_asal_sekolah" class="form-control" maxlength="8">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Sekolah Asal</label>
                                    <input type="text" name="nama_sekolah_asal" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary btn-prev" data-prev="1">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary btn-next float-right" data-next="3">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3: Data Orang Tua --}}
            <div class="wizard-step" id="step-3">
                <div class="card card-step">
                    <div class="card-header bg-info">
                        <h3 class="card-title text-white">
                            <i class="fas fa-users mr-2"></i>Data Orang Tua
                        </h3>
                    </div>
                    <div class="card-body">
                        {{-- Nomor KK --}}
                        <div class="form-group">
                            <label>Nomor Kartu Keluarga (KK)</label>
                            <input type="text" name="no_kk" class="form-control" maxlength="16" 
                                   pattern="[0-9]{16}" placeholder="16 digit angka">
                        </div>

                        <div class="row">
                            {{-- Data Ayah --}}
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-primary">
                                        <h5 class="card-title text-white mb-0">
                                            <i class="fas fa-male mr-2"></i>Data Ayah
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="required-field">Nama Ayah</label>
                                            <input type="text" name="nama_ayah" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>NIK Ayah</label>
                                            <input type="text" name="nik_ayah" class="form-control" maxlength="16">
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tempat Lahir</label>
                                                    <input type="text" name="tempat_lahir_ayah" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tanggal Lahir</label>
                                                    <input type="date" name="tanggal_lahir_ayah" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Pendidikan</label>
                                            <select name="pendidikan_ayah" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                                    <option value="{{ $pend }}">{{ $pend }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Pekerjaan</label>
                                            <select name="pekerjaan_ayah" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Penghasilan</label>
                                            <select name="penghasilan_ayah" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>No. HP Ayah</label>
                                            <input type="text" name="hp_ayah" class="form-control" placeholder="08xxxxxxxxxx">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Data Ibu --}}
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-pink">
                                        <h5 class="card-title text-white mb-0">
                                            <i class="fas fa-female mr-2"></i>Data Ibu
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="required-field">Nama Ibu</label>
                                            <input type="text" name="nama_ibu" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>NIK Ibu</label>
                                            <input type="text" name="nik_ibu" class="form-control" maxlength="16">
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tempat Lahir</label>
                                                    <input type="text" name="tempat_lahir_ibu" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tanggal Lahir</label>
                                                    <input type="date" name="tanggal_lahir_ibu" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Pendidikan</label>
                                            <select name="pendidikan_ibu" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(['SD/Sederajat', 'SMP/Sederajat', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $pend)
                                                    <option value="{{ $pend }}">{{ $pend }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Pekerjaan</label>
                                            <select name="pekerjaan_ibu" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Penghasilan</label>
                                            <select name="penghasilan_ibu" class="form-control">
                                                <option value="">-- Pilih --</option>
                                                @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>No. HP Ibu</label>
                                            <input type="text" name="hp_ibu" class="form-control" placeholder="08xxxxxxxxxx">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Checkbox copy alamat --}}
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="copy_alamat_to_ortu" 
                                   name="copy_alamat_to_ortu" value="1" checked>
                            <label class="form-check-label" for="copy_alamat_to_ortu">
                                <i class="fas fa-copy mr-1"></i> Alamat orang tua sama dengan alamat siswa
                            </label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary btn-prev" data-prev="2">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary btn-next float-right" data-next="4">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 4: Konfirmasi --}}
            <div class="wizard-step" id="step-4">
                <div class="card card-step">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle mr-2"></i>Konfirmasi Data
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Perhatian!</strong> Periksa kembali semua data sebelum menyimpan. 
                            Sistem akan otomatis membuat akun login untuk pendaftar.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2"><i class="fas fa-graduation-cap mr-2"></i>Data PPDB</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%">Jalur</td>
                                        <td><strong id="confirm_jalur">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Gelombang</td>
                                        <td><strong id="confirm_gelombang">-</strong></td>
                                    </tr>
                                </table>

                                <h5 class="border-bottom pb-2 mt-4"><i class="fas fa-user mr-2"></i>Data Diri</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%">NISN</td>
                                        <td><strong id="confirm_nisn">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nama Lengkap</td>
                                        <td><strong id="confirm_nama">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>TTL</td>
                                        <td><strong id="confirm_ttl">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Kelamin</td>
                                        <td><strong id="confirm_jk">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Agama</td>
                                        <td><strong id="confirm_agama">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>No. HP</td>
                                        <td><strong id="confirm_hp">-</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2"><i class="fas fa-users mr-2"></i>Data Orang Tua</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="40%">Nama Ayah</td>
                                        <td><strong id="confirm_ayah">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nama Ibu</td>
                                        <td><strong id="confirm_ibu">-</strong></td>
                                    </tr>
                                </table>

                                <h5 class="border-bottom pb-2 mt-4"><i class="fas fa-map-marker-alt mr-2"></i>Alamat</h5>
                                <p id="confirm_alamat" class="text-muted">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary btn-prev" data-prev="3">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="submit" class="btn btn-success btn-lg float-right" id="btnSubmit">
                            <i class="fas fa-save mr-2"></i> Simpan Pendaftar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Success with Credentials --}}
<div class="modal fade" id="successModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <i class="fas fa-check-circle mr-2"></i>Pendaftar Berhasil Ditambahkan!
                </h5>
            </div>
            <div class="modal-body">
                <div class="credential-box">
                    <h4><i class="fas fa-key mr-2"></i>Kredensial Login</h4>
                    <p class="text-muted">Berikan informasi ini kepada pendaftar untuk login</p>
                    
                    <div class="credential-item">
                        <span>Nomor Registrasi:</span>
                        <span class="value" id="cred_noreg">-</span>
                    </div>
                    <div class="credential-item">
                        <span>Username:</span>
                        <span class="value" id="cred_username">-</span>
                    </div>
                    <div class="credential-item">
                        <span>Password:</span>
                        <span class="value" id="cred_password">-</span>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyCredentials()">
                        <i class="fas fa-copy mr-1"></i> Salin Kredensial
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="printCredentials()">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list mr-1"></i> Kembali ke Daftar
                </a>
                <a href="#" id="btnViewDetail" class="btn btn-primary">
                    <i class="fas fa-eye mr-1"></i> Lihat Detail
                </a>
                <button type="button" class="btn btn-success" onclick="location.reload()">
                    <i class="fas fa-plus mr-1"></i> Tambah Lagi
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Wizard Navigation
    $('.btn-next').on('click', function() {
        const nextStep = $(this).data('next');
        
        // Validate current step before proceeding
        if (!validateCurrentStep(nextStep - 1)) {
            return;
        }
        
        goToStep(nextStep);
    });

    $('.btn-prev').on('click', function() {
        const prevStep = $(this).data('prev');
        goToStep(prevStep);
    });

    function goToStep(step) {
        // Hide all steps
        $('.wizard-step').removeClass('active');
        $('.wizard-nav-item').removeClass('active');
        
        // Show target step
        $('#step-' + step).addClass('active');
        $('.wizard-nav-item[data-step="' + step + '"]').addClass('active');
        
        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            $('.wizard-nav-item[data-step="' + i + '"]').addClass('completed');
        }
        
        // If going to confirmation step, update summary
        if (step === 4) {
            updateConfirmation();
        }
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    function validateCurrentStep(step) {
        let isValid = true;
        const stepEl = $('#step-' + step);
        
        // Check required fields in current step
        stepEl.find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Silakan lengkapi semua field yang wajib diisi (bertanda *)',
                confirmButtonColor: '#3085d6'
            });
        }

        return isValid;
    }

    function updateConfirmation() {
        // PPDB
        $('#confirm_jalur').text($('#jalur_pendaftaran_id option:selected').text() || '-');
        $('#confirm_gelombang').text($('#gelombang_pendaftaran_id option:selected').text() || '-');
        
        // Data Diri
        $('#confirm_nisn').text($('input[name="nisn"]').val() || '-');
        $('#confirm_nama').text($('input[name="nama_lengkap"]').val() || '-');
        $('#confirm_ttl').text(
            ($('input[name="tempat_lahir"]').val() || '-') + ', ' + 
            ($('input[name="tanggal_lahir"]').val() || '-')
        );
        $('#confirm_jk').text($('select[name="jenis_kelamin"] option:selected').text() || '-');
        $('#confirm_agama').text($('select[name="agama"] option:selected').text() || '-');
        $('#confirm_hp').text($('input[name="nomor_hp"]').val() || '-');
        
        // Orang Tua
        $('#confirm_ayah').text($('input[name="nama_ayah"]').val() || '-');
        $('#confirm_ibu').text($('input[name="nama_ibu"]').val() || '-');
        
        // Alamat
        const alamat = [
            $('textarea[name="alamat_siswa"]').val(),
            'RT ' + ($('input[name="rt_siswa"]').val() || '-'),
            'RW ' + ($('input[name="rw_siswa"]').val() || '-'),
            $('#kelurahan_siswa option:selected').text(),
            $('#kecamatan_siswa option:selected').text(),
            $('#kabupaten_siswa option:selected').text(),
            $('#provinsi_siswa option:selected').text()
        ].filter(Boolean).join(', ');
        $('#confirm_alamat').text(alamat || '-');
    }

    // Jalur change - load gelombang
    $('#jalur_pendaftaran_id').on('change', function() {
        const selected = $(this).find(':selected');
        const gelombangList = selected.data('gelombang') || [];
        const kuota = selected.data('kuota') || 0;
        const terisi = selected.data('terisi') || 0;
        
        // Update gelombang dropdown
        let options = '<option value="">-- Pilih Gelombang --</option>';
        gelombangList.forEach(function(g) {
            const sisa = g.kuota - g.kuota_terisi;
            // For manual input, show all gelombang including inactive ones with remaining quota
            if (sisa > 0) {
                const status = g.is_active ? '' : ' (Tidak Aktif)';
                options += `<option value="${g.id}">${g.nama}${status} (Sisa: ${sisa})</option>`;
            }
        });
        
        $('#gelombang_pendaftaran_id').html(options).prop('disabled', false);
        
        // Update kuota info
        if (kuota > 0) {
            const persen = Math.round((terisi / kuota) * 100);
            $('#kuotaJalur').text(kuota);
            $('#kuotaText').text('Terisi: ' + terisi + ' (' + persen + '%)');
            $('#progressKuota').css('width', persen + '%');
            $('#jalurInfo').show();
        } else {
            $('#jalurInfo').hide();
        }
    });

    // Address cascading - Provinsi
    $('#provinsi_siswa').on('change', function() {
        const code = $(this).val();
        $('#kabupaten_siswa, #kecamatan_siswa, #kelurahan_siswa').html('<option value="">-- Pilih --</option>');
        
        if (code) {
            $.get('/api/indonesia/cities/' + code, function(data) {
                let options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.code}">${item.name}</option>`;
                });
                $('#kabupaten_siswa').html(options);
            });
        }
    });

    $('#kabupaten_siswa').on('change', function() {
        const code = $(this).val();
        $('#kecamatan_siswa, #kelurahan_siswa').html('<option value="">-- Pilih --</option>');
        
        if (code) {
            $.get('/api/indonesia/districts/' + code, function(data) {
                let options = '<option value="">-- Pilih Kecamatan --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.code}">${item.name}</option>`;
                });
                $('#kecamatan_siswa').html(options);
            });
        }
    });

    $('#kecamatan_siswa').on('change', function() {
        const code = $(this).val();
        $('#kelurahan_siswa').html('<option value="">-- Pilih --</option>');
        
        if (code) {
            $.get('/api/indonesia/villages/' + code, function(data) {
                let options = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                data.forEach(function(item) {
                    options += `<option value="${item.code}">${item.name}</option>`;
                });
                $('#kelurahan_siswa').html(options);
            });
        }
    });

    // Form submission
    $('#formTambahPendaftar').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = $('#btnSubmit');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success modal with credentials
                    $('#cred_noreg').text(response.data.nomor_registrasi);
                    $('#cred_username').text(response.data.username);
                    $('#cred_password').text(response.data.password);
                    $('#btnViewDetail').attr('href', '{{ route("admin.pendaftar.index") }}/' + response.data.id);
                    
                    $('#successModal').modal('show');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        msg = errors.join('<br>');
                    }
                }
                Swal.fire('Error!', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Pendaftar');
            }
        });
    });
});

// Copy credentials to clipboard
function copyCredentials() {
    const text = `Nomor Registrasi: ${$('#cred_noreg').text()}\nUsername: ${$('#cred_username').text()}\nPassword: ${$('#cred_password').text()}`;
    
    navigator.clipboard.writeText(text).then(function() {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Kredensial disalin!',
            showConfirmButton: false,
            timer: 2000
        });
    });
}

// Print credentials
function printCredentials() {
    const printContent = `
        <html>
        <head>
            <title>Kredensial Login PPDB</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .credential-box { border: 2px dashed #28a745; padding: 20px; border-radius: 10px; }
                .item { margin: 15px 0; font-size: 16px; }
                .item strong { display: inline-block; width: 150px; }
                .value { background: #f0f0f0; padding: 5px 15px; border-radius: 5px; font-family: monospace; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>KREDENSIAL LOGIN PPDB</h2>
                <p>Simpan informasi ini dengan baik</p>
            </div>
            <div class="credential-box">
                <div class="item">
                    <strong>Nomor Registrasi:</strong>
                    <span class="value">${$('#cred_noreg').text()}</span>
                </div>
                <div class="item">
                    <strong>Username:</strong>
                    <span class="value">${$('#cred_username').text()}</span>
                </div>
                <div class="item">
                    <strong>Password:</strong>
                    <span class="value">${$('#cred_password').text()}</span>
                </div>
            </div>
            <div class="footer">
                <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                <p>Gunakan kredensial ini untuk login di halaman pendaftar</p>
            </div>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}
</script>
@stop
