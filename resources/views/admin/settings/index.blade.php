@extends('adminlte::page')

@section('title', 'Pengaturan PPDB')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cog"></i> Pengaturan PPDB</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan PPDB</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Info Pengaturan Sekolah --}}
    @php
        $sekolah = \App\Models\SekolahSettings::first();
        $jalurAktif = \App\Models\JalurPendaftaran::getAktif();
    @endphp
    @if($sekolah)
        <div class="alert alert-info">
            <i class="fas fa-school"></i>
            <strong>Jenjang Sekolah:</strong> {{ $sekolah->jenjang }} ({{ \App\Models\SekolahSettings::JENJANG_LIST[$sekolah->jenjang] ?? $sekolah->jenjang }})
            <br>
            <small>Pendaftar harus aktif di <strong>Kelas {{ $sekolah->kelas_minimum_ppdb }}</strong> dari <strong>{{ implode('/', $sekolah->jenjang_asal_ppdb) }}</strong></small>
            <a href="{{ route('admin.sekolah.index') }}" class="btn btn-sm btn-outline-primary float-right">
                <i class="fas fa-edit"></i> Ubah Pengaturan Sekolah
            </a>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Perhatian:</strong> Pengaturan Sekolah belum dikonfigurasi.
            <a href="{{ route('admin.sekolah.index') }}" class="btn btn-sm btn-warning float-right">
                <i class="fas fa-cog"></i> Konfigurasi Sekolah
            </a>
        </div>
    @endif

    {{-- Info Jalur Aktif --}}
    @if($jalurAktif)
        <div class="callout callout-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-door-open text-success"></i>
                        Jalur Aktif: <strong>{{ $jalurAktif->nama }}</strong>
                    </h5>
                    <p class="mb-0">
                        Tahun Pelajaran {{ $jalurAktif->tahunPelajaran?->nama ?? '-' }} |
                        @if($jalurAktif->tanggal_buka && $jalurAktif->tanggal_tutup)
                        Periode: {{ $jalurAktif->tanggal_buka->format('d/m/Y') }} - {{ $jalurAktif->tanggal_tutup->format('d/m/Y') }} |
                        @endif
                        Kuota: {{ $jalurAktif->kuota_terisi }}/{{ $jalurAktif->kuota }}
                        @if($jalurAktif->tanggal_tutup)
                        | <span class="badge badge-{{ $jalurAktif->sisaHari() <= 7 ? 'danger' : 'info' }}">
                            Sisa {{ $jalurAktif->sisaHari() }} hari
                        </span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.jalur.show', $jalurAktif) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-eye"></i> Detail
                </a>
            </div>
        </div>
    @else
        <div class="callout callout-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Tidak Ada Jalur Pendaftaran Aktif
                    </h5>
                    <p class="mb-0">Pendaftaran saat ini ditutup. Silahkan aktifkan jalur pendaftaran untuk membuka pendaftaran.</p>
                </div>
                <a href="{{ route('admin.jalur.index') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus"></i> Kelola Jalur
                </a>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="row">
            {{-- Validasi --}}
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Validasi Pendaftaran</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="validasi_nisn_aktif" 
                                       name="validasi_nisn_aktif" value="1"
                                       {{ old('validasi_nisn_aktif', $settings->validasi_nisn_aktif) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="validasi_nisn_aktif">
                                    <strong>Validasi NISN Aktif</strong>
                                    <small class="text-muted d-block">Validasi NISN ke Kemendikbud saat pendaftaran dan cek kelas aktif sesuai jenjang sekolah</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="cegah_pendaftar_ganda" 
                                       name="cegah_pendaftar_ganda" value="1"
                                       {{ old('cegah_pendaftar_ganda', $settings->cegah_pendaftar_ganda) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="cegah_pendaftar_ganda">
                                    <strong>Cegah Pendaftar Ganda</strong>
                                    <small class="text-muted d-block">Cegah NISN yang sama mendaftar lebih dari sekali</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Format Nomor Registrasi Default --}}
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-hashtag"></i> Format Nomor Registrasi Default</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nomor_registrasi_prefix">Prefix Default <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nomor_registrasi_prefix') is-invalid @enderror" 
                                   id="nomor_registrasi_prefix" name="nomor_registrasi_prefix" 
                                   value="{{ old('nomor_registrasi_prefix', $settings->nomor_registrasi_prefix) }}" 
                                   maxlength="20" required>
                            @error('nomor_registrasi_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Digunakan jika jalur tidak memiliki prefix. Contoh: <code>{{ $settings->nomor_registrasi_prefix }}-{{ date('Y') }}-00001</code>
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Format Nomor Tes --}}
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card"></i> Format Nomor Tes</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nomor_tes_prefix">Prefix Nomor Tes <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nomor_tes_prefix') is-invalid @enderror" 
                                   id="nomor_tes_prefix" name="nomor_tes_prefix" 
                                   value="{{ old('nomor_tes_prefix', $settings->nomor_tes_prefix) }}" 
                                   maxlength="10" required>
                            @error('nomor_tes_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: NTS, TES, UJIAN</small>
                        </div>

                        <div class="form-group">
                            <label for="nomor_tes_format">Format Nomor Tes</label>
                            <input type="text" class="form-control @error('nomor_tes_format') is-invalid @enderror" 
                                   id="nomor_tes_format" name="nomor_tes_format" 
                                   value="{{ old('nomor_tes_format', $settings->nomor_tes_format) }}" 
                                   readonly>
                            @error('nomor_tes_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Format: <code>{PREFIX}-{TAHUN}-{JALUR}-{NOMOR}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="nomor_tes_digit">Jumlah Digit Nomor <span class="text-danger">*</span></label>
                            <select class="form-control @error('nomor_tes_digit') is-invalid @enderror" 
                                    id="nomor_tes_digit" name="nomor_tes_digit" required>
                                <option value="3" {{ old('nomor_tes_digit', $settings->nomor_tes_digit) == 3 ? 'selected' : '' }}>3 Digit (001-999)</option>
                                <option value="4" {{ old('nomor_tes_digit', $settings->nomor_tes_digit) == 4 ? 'selected' : '' }}>4 Digit (0001-9999)</option>
                                <option value="5" {{ old('nomor_tes_digit', $settings->nomor_tes_digit) == 5 ? 'selected' : '' }}>5 Digit (00001-99999)</option>
                            </select>
                            @error('nomor_tes_digit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Preview:</strong>
                            <code id="preview_nomor_tes">{{ $settings->nomor_tes_prefix }}-{{ date('Y') }}-PRE-{{ str_pad(1, $settings->nomor_tes_digit, '0', STR_PAD_LEFT) }}</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="col-md-6">
                {{-- Dokumen yang Diperlukan --}}
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt"></i> Dokumen yang Diperlukan</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $dokumenOptions = [
                                'kk' => 'Kartu Keluarga (KK)',
                                'akta_lahir' => 'Akta Kelahiran',
                                'ijazah' => 'Ijazah/SKL',
                                'foto' => 'Pas Foto',
                                'ktp_ortu' => 'KTP Orang Tua',
                                'skhun' => 'SKHUN',
                                'raport' => 'Raport',
                                'surat_sehat' => 'Surat Keterangan Sehat',
                                'surat_kelakuan_baik' => 'Surat Kelakuan Baik',
                            ];
                            $dokumenAktif = old('dokumen_aktif', $settings->dokumen_aktif ?? []);
                        @endphp

                        <div class="row">
                            @foreach($dokumenOptions as $key => $label)
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="dokumen_{{ $key }}" name="dokumen_aktif[]" value="{{ $key }}"
                                               {{ in_array($key, $dokumenAktif) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="dokumen_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-link"></i> Pengaturan Lainnya</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.jalur.index') }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-route"></i> Kelola Jalur Pendaftaran
                        </a>
                        <a href="{{ route('admin.sekolah.index') }}" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-school"></i> Pengaturan Sekolah
                        </a>
                        <a href="{{ route('admin.settings.halaman.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-file-alt"></i> Pengaturan Halaman
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Simpan Pengaturan PPDB
            </button>
        </div>
    </form>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Show success message
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    // Show error messages
    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif

    // Update preview nomor tes
    function updateNomorTesPreview() {
        const prefix = $('#nomor_tes_prefix').val() || 'NTS';
        const digit = parseInt($('#nomor_tes_digit').val()) || 4;
        const nomor = String(1).padStart(digit, '0');
        const tahun = '{{ date("Y") }}';
        const preview = `${prefix}-${tahun}-PRE-${nomor}`;
        $('#preview_nomor_tes').text(preview);
    }

    // Update preview on change
    $('#nomor_tes_prefix, #nomor_tes_digit').on('input change', updateNomorTesPreview);
});
</script>
@stop
