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
        color: #495057;
    }
    /* Widget header customization */
    .widget-user-2 .widget-user-header {
        padding: 15px 20px;
    }
    .widget-user-2 .widget-user-username {
        font-size: 1.3rem;
        margin-bottom: 5px;
    }
    .widget-user-2 .widget-user-image img,
    .widget-user-2 .widget-user-image div {
        float: none;
        margin: 0 auto;
    }
    /* Card header with tools */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-header .card-title {
        margin-bottom: 0;
    }
    /* Sticky action buttons on scroll */
    .action-sticky {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: #fff;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }
    /* Form group improvements */
    .form-group label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
    }
    /* Readonly field style */
    input[readonly], input[disabled] {
        background-color: #f4f6f9 !important;
        cursor: not-allowed;
    }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-md-6">
        <h1 class="m-0">
            <i class="fas fa-user-edit mr-2"></i>Edit Data Pendaftar
        </h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-info">
            <i class="fas fa-eye mr-1"></i>Lihat Detail
        </a>
        <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    
    {{-- Info Card Header --}}
    <div class="card card-widget widget-user-2 shadow-sm mb-3">
        <div class="widget-user-header bg-gradient-primary">
            <div class="row">
                <div class="col-md-1 text-center">
                    <div class="widget-user-image">
                        @if($pendaftar->foto)
                            <img class="img-circle elevation-2" src="{{ Storage::url($pendaftar->foto) }}" alt="Foto" style="width: 65px; height: 65px; object-fit: cover;">
                        @else
                            <div class="img-circle elevation-2 bg-white d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                                <i class="fas fa-user fa-2x text-gray"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-5">
                    <h3 class="widget-user-username mb-0">{{ $pendaftar->nama_lengkap }}</h3>
                    <h6 class="widget-user-desc">
                        <span class="badge badge-light mr-2">NISN: {{ $pendaftar->nisn }}</span>
                        @if($pendaftar->nomor_registrasi)
                            <span class="badge badge-warning">No. Reg: {{ $pendaftar->nomor_registrasi }}</span>
                        @endif
                    </h6>
                </div>
                <div class="col-md-6 text-right">
                    <div class="mb-2">
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'submitted' => 'info', 
                                'verified' => 'primary',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                            ];
                            $statusLabels = [
                                'draft' => 'Draft',
                                'submitted' => 'Menunggu Verifikasi',
                                'verified' => 'Terverifikasi',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
                            ];
                            $status = $pendaftar->status_verifikasi ?? 'draft';
                        @endphp
                        <span class="badge badge-lg badge-{{ $statusColors[$status] ?? 'secondary' }}" style="font-size: 0.9rem; padding: 8px 15px;">
                            <i class="fas fa-{{ $status == 'accepted' ? 'check-circle' : ($status == 'rejected' ? 'times-circle' : 'clock') }} mr-1"></i>
                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                        </span>
                    </div>
                    <small class="text-white-50">
                        <i class="fas fa-calendar-alt mr-1"></i>Terdaftar: {{ $pendaftar->created_at->format('d M Y H:i') }}
                        @if($pendaftar->updated_at != $pendaftar->created_at)
                            | <i class="fas fa-edit mr-1"></i>Diupdate: {{ $pendaftar->updated_at->format('d M Y H:i') }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="card-footer p-0">
            <ul class="nav nav-pills flex-column flex-md-row">
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-road mr-2"></i><strong>Jalur:</strong> {{ $pendaftar->jalurPendaftaran->nama ?? '-' }}
                    </span>
                </li>
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-layer-group mr-2"></i><strong>Gelombang:</strong> {{ $pendaftar->gelombangPendaftaran->nama ?? '-' }}
                    </span>
                </li>
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-graduation-cap mr-2"></i><strong>Program:</strong> 
                        @if($pendaftar->pilihan_program == 'Reguler')
                            <span class="badge badge-info">Reguler</span>
                        @elseif($pendaftar->pilihan_program == 'Asrama')
                            <span class="badge badge-success">Asrama</span>
                        @else
                            <span class="badge badge-secondary">Belum Memilih</span>
                        @endif
                    </span>
                </li>
                @if($pendaftar->nomor_tes)
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-id-badge mr-2"></i><strong>No. Tes:</strong> <span class="text-primary font-weight-bold">{{ $pendaftar->nomor_tes }}</span>
                    </span>
                </li>
                @endif
                @if($pendaftar->finalized_at)
                <li class="nav-item">
                    <span class="nav-link text-success">
                        <i class="fas fa-check-circle mr-2"></i><strong>Finalisasi:</strong> {{ \Carbon\Carbon::parse($pendaftar->finalized_at)->format('d M Y H:i') }}
                    </span>
                </li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.pendaftar.update', $pendaftar->id) }}" method="POST" id="formEditPendaftar">
        @csrf
        @method('PUT')
        
        <!-- Pilihan Program -->
        <div class="card card-info card-outline mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Pilihan Program</h3>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="pilihan_program">Pilihan Program <span class="text-danger">*</span></label>
                            <select name="pilihan_program" id="pilihan_program" 
                                class="form-control @error('pilihan_program') is-invalid @enderror" required>
                                <option value="">-- Pilih Program --</option>
                                <option value="Reguler" {{ old('pilihan_program', $pendaftar->pilihan_program) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="Asrama" {{ old('pilihan_program', $pendaftar->pilihan_program) == 'Asrama' ? 'selected' : '' }}>Asrama</option>
                            </select>
                            @error('pilihan_program')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Pilih program yang diinginkan (Reguler atau Asrama)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Pribadi -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Data Pribadi</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
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
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Alamat Lengkap</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
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
                            <label for="kodepos_siswa">Kode Pos</label>
                            <input type="number" name="kodepos_siswa" id="kodepos_siswa" 
                                class="form-control @error('kodepos_siswa') is-invalid @enderror" 
                                value="{{ old('kodepos_siswa', $pendaftar->kodepos_siswa) }}" min="0" max="99999">
                            @error('kodepos_siswa')
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
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Data Orang Tua</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
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
                                @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('pekerjaan_ayah', $pendaftar->ortu->pekerjaan_ayah ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
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
                                @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('penghasilan_ayah', $pendaftar->ortu->penghasilan_ayah ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
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
                                @foreach(\App\Models\CalonOrtu::PEKERJAAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('pekerjaan_ibu', $pendaftar->ortu->pekerjaan_ibu ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
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
                                @foreach(\App\Models\CalonOrtu::PENGHASILAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('penghasilan_ibu', $pendaftar->ortu->penghasilan_ibu ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
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
                <h3 class="card-title"><i class="fas fa-school mr-2"></i>Data Asal Sekolah</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @php
                    $npsnKosong = empty($pendaftar->npsn_asal_sekolah);
                @endphp
                
                @if($npsnKosong)
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Perhatian:</strong> NPSN asal sekolah belum diisi. Silakan masukkan NPSN dan klik "Sync NPSN" untuk mengambil data sekolah dari Kemdikdasmen.
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="npsn_asal_sekolah">NPSN Sekolah</label>
                            <div class="input-group">
                                <input type="text" name="npsn_asal_sekolah" id="npsn_asal_sekolah" 
                                    class="form-control @error('npsn_asal_sekolah') is-invalid @enderror" 
                                    value="{{ old('npsn_asal_sekolah', $pendaftar->npsn_asal_sekolah) }}"
                                    maxlength="8"
                                    placeholder="8 karakter NPSN"
                                    pattern="[A-Za-z0-9]{8}"
                                    {{ !$npsnKosong ? 'readonly' : '' }}>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="btnSyncNpsn" title="Sync data dari Kemdikdasmen">
                                        <i class="fas fa-sync-alt mr-1"></i>Sync NPSN
                                    </button>
                                </div>
                            </div>
                            @error('npsn_asal_sekolah')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                @if($npsnKosong)
                                    Masukkan 8 digit NPSN kemudian klik Sync untuk mengambil nama sekolah
                                @else
                                    <i class="fas fa-check-circle text-success"></i> Data dari registrasi. Klik Sync untuk update.
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nama_sekolah_asal">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah_asal" id="nama_sekolah_asal" 
                                class="form-control @error('nama_sekolah_asal') is-invalid @enderror" 
                                value="{{ old('nama_sekolah_asal', $pendaftar->nama_sekolah_asal) }}"
                                placeholder="Akan terisi otomatis setelah Sync NPSN"
                                readonly>
                            @error('nama_sekolah_asal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                {{-- Info tambahan sekolah (akan diisi oleh JS) --}}
                <div id="infoSekolahTambahan" class="row" style="{{ $pendaftar->alamat_sekolah_asal || $pendaftar->status_sekolah_asal ? '' : 'display: none;' }}">
                    <div class="col-12">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle mr-2"></i>Informasi Sekolah dari Kemdikdasmen</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Alamat:</strong> <span id="info_alamat">{{ $pendaftar->alamat_sekolah_asal ?? '-' }}</span></p>
                                    <p class="mb-1"><strong>Kelurahan:</strong> <span id="info_kelurahan">{{ $pendaftar->kelurahan_sekolah_asal ?? '-' }}</span></p>
                                    <p class="mb-1"><strong>Kecamatan:</strong> <span id="info_kecamatan">{{ $pendaftar->kecamatan_sekolah_asal ?? '-' }}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Kabupaten:</strong> <span id="info_kabupaten">{{ $pendaftar->kabupaten_sekolah_asal ?? '-' }}</span></p>
                                    <p class="mb-1"><strong>Provinsi:</strong> <span id="info_provinsi">{{ $pendaftar->provinsi_sekolah_asal ?? '-' }}</span></p>
                                    <p class="mb-1"><strong>Status:</strong> <span id="info_status">{{ $pendaftar->status_sekolah_asal ?? '-' }}</span> | <strong>Bentuk:</strong> <span id="info_bentuk">{{ $pendaftar->bentuk_sekolah_asal ?? '-' }}</span> | <strong>Akreditasi:</strong> <span id="info_akreditasi">{{ $pendaftar->akreditasi_sekolah_asal ?? '-' }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Hidden fields untuk menyimpan data sekolah --}}
                <input type="hidden" name="alamat_sekolah_asal" id="alamat_sekolah_asal" value="{{ old('alamat_sekolah_asal', $pendaftar->alamat_sekolah_asal) }}">
                <input type="hidden" name="kelurahan_sekolah_asal" id="kelurahan_sekolah_asal" value="{{ old('kelurahan_sekolah_asal', $pendaftar->kelurahan_sekolah_asal) }}">
                <input type="hidden" name="kecamatan_sekolah_asal" id="kecamatan_sekolah_asal" value="{{ old('kecamatan_sekolah_asal', $pendaftar->kecamatan_sekolah_asal) }}">
                <input type="hidden" name="kabupaten_sekolah_asal" id="kabupaten_sekolah_asal" value="{{ old('kabupaten_sekolah_asal', $pendaftar->kabupaten_sekolah_asal) }}">
                <input type="hidden" name="provinsi_sekolah_asal" id="provinsi_sekolah_asal" value="{{ old('provinsi_sekolah_asal', $pendaftar->provinsi_sekolah_asal) }}">
                <input type="hidden" name="status_sekolah_asal" id="status_sekolah_asal" value="{{ old('status_sekolah_asal', $pendaftar->status_sekolah_asal) }}">
                <input type="hidden" name="bentuk_sekolah_asal" id="bentuk_sekolah_asal" value="{{ old('bentuk_sekolah_asal', $pendaftar->bentuk_sekolah_asal) }}">
                <input type="hidden" name="akreditasi_sekolah_asal" id="akreditasi_sekolah_asal" value="{{ old('akreditasi_sekolah_asal', $pendaftar->akreditasi_sekolah_asal) }}">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card card-outline">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.pendaftar.show', $pendaftar->id) }}" class="btn btn-info btn-lg ml-2">
                            <i class="fas fa-eye mr-2"></i>Lihat Detail
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                        </a>
                    </div>
                </div>
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
        theme: 'default',
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

    // ============================================
    // SYNC NPSN dari Kemdikdasmen
    // ============================================
    $('#btnSyncNpsn').on('click', function() {
        const npsn = $('#npsn_asal_sekolah').val().trim();
        
        // Validate NPSN format
        if (!npsn) {
            Swal.fire({
                icon: 'warning',
                title: 'NPSN Kosong',
                text: 'Silakan masukkan NPSN terlebih dahulu.',
            });
            return;
        }
        
        if (!/^[A-Za-z0-9]{8}$/.test(npsn)) {
            Swal.fire({
                icon: 'warning',
                title: 'Format NPSN Salah',
                text: 'NPSN harus 8 karakter (huruf dan/atau angka).',
            });
            return;
        }
        
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
        
        $.ajax({
            url: '{{ route("admin.pendaftar.sync-npsn") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                npsn: npsn
            },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    
                    // Fill nama sekolah
                    $('#nama_sekolah_asal').val(data.nama_sekolah || '');
                    
                    // Fill hidden fields untuk disimpan ke database
                    $('#alamat_sekolah_asal').val(data.alamat || '');
                    $('#kelurahan_sekolah_asal').val(data.kelurahan || '');
                    $('#kecamatan_sekolah_asal').val(data.kecamatan || '');
                    $('#kabupaten_sekolah_asal').val(data.kabupaten || '');
                    $('#provinsi_sekolah_asal').val(data.provinsi || '');
                    $('#status_sekolah_asal').val(data.status || '');
                    $('#bentuk_sekolah_asal').val(data.bentuk_pendidikan || '');
                    $('#akreditasi_sekolah_asal').val(data.akreditasi || '');
                    
                    // Show info tambahan di UI
                    $('#info_alamat').text(data.alamat || '-');
                    $('#info_kelurahan').text(data.kelurahan || '-');
                    $('#info_kecamatan').text(data.kecamatan || '-');
                    $('#info_kabupaten').text(data.kabupaten || '-');
                    $('#info_provinsi').text(data.provinsi || '-');
                    $('#info_status').text(data.status || '-');
                    $('#info_bentuk').text(data.bentuk_pendidikan || '-');
                    $('#info_akreditasi').text(data.akreditasi || '-');
                    $('#infoSekolahTambahan').slideDown();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Sekolah Ditemukan',
                        html: `<strong>${data.nama_sekolah}</strong><br><small class="text-muted">${data.alamat || ''}, ${data.kabupaten || ''}</small><br><span class="badge badge-info">${data.status || ''}</span> <span class="badge badge-success">Akreditasi: ${data.akreditasi || '-'}</span>`,
                        timer: 4000,
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'NPSN Tidak Ditemukan',
                        text: response.message || 'Data sekolah tidak ditemukan di database Kemdikdasmen.',
                    });
                    $('#infoSekolahTambahan').slideUp();
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan saat mengambil data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Sync NPSN',
                    text: errorMsg,
                });
                $('#infoSekolahTambahan').slideUp();
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Allow NPSN input alphanumeric only
    $('#npsn_asal_sekolah').on('input', function() {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, 8);
    });
});
</script>
@stop
