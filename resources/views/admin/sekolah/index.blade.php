@extends('adminlte::page')

@section('title', 'Pengaturan Sekolah')

@section('meta_tags')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-school"></i> Pengaturan Sekolah</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan Sekolah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sekolah.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            {{-- Identitas Sekolah --}}
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card"></i> Identitas Sekolah</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_sekolah">Nama Sekolah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                   id="nama_sekolah" name="nama_sekolah" 
                                   value="{{ old('nama_sekolah', $settings->nama_sekolah) }}" required>
                            @error('nama_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jenjang">Jenjang Pendidikan <span class="text-danger">*</span></label>
                            <select class="form-control @error('jenjang') is-invalid @enderror" 
                                    id="jenjang" name="jenjang" required>
                                @foreach(\App\Models\SekolahSettings::JENJANG_LIST as $kode => $nama)
                                    <option value="{{ $kode }}" {{ old('jenjang', $settings->jenjang) == $kode ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenjang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Jenjang ini menentukan syarat kelas minimum pendaftar PPDB
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="npsn">NPSN</label>
                                    <input type="text" class="form-control @error('npsn') is-invalid @enderror" 
                                           id="npsn" name="npsn" 
                                           value="{{ old('npsn', $settings->npsn) }}" maxlength="20">
                                    @error('npsn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nsm">NSM</label>
                                    <input type="text" class="form-control @error('nsm') is-invalid @enderror" 
                                           id="nsm" name="nsm" 
                                           value="{{ old('nsm', $settings->nsm) }}" maxlength="20">
                                    @error('nsm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Kepala Sekolah --}}
                        <hr>
                        <h6 class="text-muted"><i class="fas fa-user-tie"></i> Kepala Sekolah</h6>
                        
                        <div class="form-group">
                            <label for="nama_kepala_sekolah">Nama Kepala Sekolah</label>
                            <input type="text" class="form-control @error('nama_kepala_sekolah') is-invalid @enderror" 
                                   id="nama_kepala_sekolah" name="nama_kepala_sekolah" 
                                   value="{{ old('nama_kepala_sekolah', $settings->nama_kepala_sekolah) }}">
                            @error('nama_kepala_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nip_kepala_sekolah">NIP Kepala Sekolah</label>
                            <input type="text" class="form-control @error('nip_kepala_sekolah') is-invalid @enderror" 
                                   id="nip_kepala_sekolah" name="nip_kepala_sekolah" 
                                   value="{{ old('nip_kepala_sekolah', $settings->nip_kepala_sekolah) }}">
                            @error('nip_kepala_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Logo --}}
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image"></i> Logo Sekolah</h3>
                    </div>
                    <div class="card-body">
                        @if($settings->logo)
                            <div class="mb-3 text-center">
                                <img src="{{ Storage::url($settings->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label for="logo">Upload Logo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                <label class="custom-file-label" for="logo">Pilih file...</label>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kontak & Alamat --}}
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-phone"></i> Kontak</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $settings->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="telepon">Telepon</label>
                            <input type="text" class="form-control @error('telepon') is-invalid @enderror" 
                                   id="telepon" name="telepon" 
                                   value="{{ old('telepon', $settings->telepon) }}">
                            @error('telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                   id="website" name="website" 
                                   value="{{ old('website', $settings->website) }}" placeholder="https://">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="alamat_jalan">Alamat Jalan</label>
                            <textarea class="form-control @error('alamat_jalan') is-invalid @enderror" 
                                      id="alamat_jalan" name="alamat_jalan" rows="2">{{ old('alamat_jalan', $settings->alamat_jalan) }}</textarea>
                            @error('alamat_jalan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="province_code">Provinsi</label>
                                    <select class="form-control select2 @error('province_code') is-invalid @enderror" 
                                            id="province_code" name="province_code">
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->code }}" 
                                                {{ old('province_code', $settings->province_code) == $province->code ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city_code">Kabupaten/Kota</label>
                                    <select class="form-control select2 @error('city_code') is-invalid @enderror" 
                                            id="city_code" name="city_code">
                                        <option value="">-- Pilih Kab/Kota --</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->code }}" 
                                                {{ old('city_code', $settings->city_code) == $city->code ? 'selected' : '' }}>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district_code">Kecamatan</label>
                                    <select class="form-control select2 @error('district_code') is-invalid @enderror" 
                                            id="district_code" name="district_code">
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->code }}" 
                                                {{ old('district_code', $settings->district_code) == $district->code ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('district_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="village_code">Kelurahan/Desa</label>
                                    <select class="form-control select2 @error('village_code') is-invalid @enderror" 
                                            id="village_code" name="village_code">
                                        <option value="">-- Pilih Kel/Desa --</option>
                                        @foreach($villages as $village)
                                            <option value="{{ $village->code }}" 
                                                {{ old('village_code', $settings->village_code) == $village->code ? 'selected' : '' }}>
                                                {{ $village->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('village_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="kode_pos">Kode Pos</label>
                            <input type="text" class="form-control @error('kode_pos') is-invalid @enderror" 
                                   id="kode_pos" name="kode_pos" 
                                   value="{{ old('kode_pos', $settings->kode_pos) }}" maxlength="10">
                            @error('kode_pos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Lokasi Maps --}}
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map"></i> Lokasi di Peta (Leaflet)</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" name="latitude" 
                                           value="{{ old('latitude', $settings->latitude) }}" placeholder="-6.123456">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" name="longitude" 
                                           value="{{ old('longitude', $settings->longitude) }}" placeholder="106.123456">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="map" style="height: 250px; border-radius: 5px;"></div>
                        <small class="text-muted">Klik pada peta untuk menentukan lokasi sekolah</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Syarat PPDB --}}
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Syarat PPDB Berdasarkan Jenjang</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Jenjang Sekolah</th>
                                <th>Kelas Minimum Pendaftar</th>
                                <th>Jenjang Asal yang Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\SekolahSettings::KELAS_MINIMUM_PPDB as $jenjang => $syarat)
                                <tr class="{{ $settings->jenjang == $jenjang ? 'table-primary' : '' }}">
                                    <td>
                                        <strong>{{ $jenjang }}</strong>
                                        @if($settings->jenjang == $jenjang)
                                            <span class="badge badge-primary">Aktif</span>
                                        @endif
                                    </td>
                                    <td>Kelas {{ $syarat['kelas'] }}</td>
                                    <td>{{ implode(', ', $syarat['jenjang_asal']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Contoh:</strong> Jika sekolah adalah <strong>MA (Madrasah Aliyah)</strong>, 
                    maka saat pendaftar melakukan validasi NISN, sistem akan memeriksa apakah pendaftar 
                    sedang aktif di <strong>Kelas 9</strong> dan berasal dari <strong>SMP atau MTs</strong>. 
                    Jika tidak sesuai, pendaftaran akan ditolak.
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Simpan Pengaturan Sekolah
            </button>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
        }
    </style>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            // Setup AJAX dengan CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: function() {
                    return $(this).data('placeholder') || '-- Pilih --';
                }
            });

            // Custom file input label
            $('input[type="file"]').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Pilih file...');
            });

            // Cascade dropdown untuk wilayah
            $('#province_code').on('change', function() {
                var provinceCode = $(this).val();
                $('#city_code').html('<option value="">Loading...</option>');
                $('#district_code').html('<option value="">-- Pilih Kecamatan --</option>');
                $('#village_code').html('<option value="">-- Pilih Kel/Desa --</option>');

                if (provinceCode) {
                    $.ajax({
                        url: "{{ route('admin.sekolah.cities') }}",
                        type: 'GET',
                        data: { province_code: provinceCode },
                        success: function(data) {
                            var options = '<option value="">-- Pilih Kab/Kota --</option>';
                            data.forEach(function(city) {
                                options += '<option value="' + city.code + '">' + city.name + '</option>';
                            });
                            $('#city_code').html(options);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading cities:', error);
                            $('#city_code').html('<option value="">-- Error loading data --</option>');
                        }
                    });
                } else {
                    $('#city_code').html('<option value="">-- Pilih Kab/Kota --</option>');
                }
            });

            $('#city_code').on('change', function() {
                var cityCode = $(this).val();
                $('#district_code').html('<option value="">Loading...</option>');
                $('#village_code').html('<option value="">-- Pilih Kel/Desa --</option>');

                if (cityCode) {
                    $.ajax({
                        url: "{{ route('admin.sekolah.districts') }}",
                        type: 'GET',
                        data: { city_code: cityCode },
                        success: function(data) {
                            var options = '<option value="">-- Pilih Kecamatan --</option>';
                            data.forEach(function(district) {
                                options += '<option value="' + district.code + '">' + district.name + '</option>';
                            });
                            $('#district_code').html(options);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading districts:', error);
                            $('#district_code').html('<option value="">-- Error loading data --</option>');
                        }
                    });
                } else {
                    $('#district_code').html('<option value="">-- Pilih Kecamatan --</option>');
                }
            });

            $('#district_code').on('change', function() {
                var districtCode = $(this).val();
                $('#village_code').html('<option value="">Loading...</option>');

                if (districtCode) {
                    $.ajax({
                        url: "{{ route('admin.sekolah.villages') }}",
                        type: 'GET',
                        data: { district_code: districtCode },
                        success: function(data) {
                            var options = '<option value="">-- Pilih Kel/Desa --</option>';
                            data.forEach(function(village) {
                                options += '<option value="' + village.code + '">' + village.name + '</option>';
                            });
                            $('#village_code').html(options);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading villages:', error);
                            $('#village_code').html('<option value="">-- Error loading data --</option>');
                        }
                    });
                } else {
                    $('#village_code').html('<option value="">-- Pilih Kel/Desa --</option>');
                }
            });

            // Initialize Leaflet Map
            var lat = {{ $settings->latitude ?? -6.2088 }};
            var lng = {{ $settings->longitude ?? 106.8456 }};
            var zoom = {{ $settings->latitude ? 15 : 5 }};

            var map = L.map('map').setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker = null;
            
            @if($settings->latitude && $settings->longitude)
                marker = L.marker([lat, lng]).addTo(map);
            @endif

            map.on('click', function(e) {
                var lat = e.latlng.lat.toFixed(8);
                var lng = e.latlng.lng.toFixed(8);
                
                $('#latitude').val(lat);
                $('#longitude').val(lng);

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });

            // Update marker when coordinates are manually changed
            $('#latitude, #longitude').on('change', function() {
                var lat = parseFloat($('#latitude').val());
                var lng = parseFloat($('#longitude').val());
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    var latlng = L.latLng(lat, lng);
                    
                    if (marker) {
                        marker.setLatLng(latlng);
                    } else {
                        marker = L.marker(latlng).addTo(map);
                    }
                    
                    map.setView(latlng, 15);
                }
            });
        });
    </script>
@stop
