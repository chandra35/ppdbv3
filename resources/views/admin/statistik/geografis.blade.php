@extends('adminlte::page')

@section('title', 'Statistik Sebaran Geografis')

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .chart-container { position: relative; height: 300px; }
    #map { height: 400px; border-radius: 5px; }
    .table-stat th, .table-stat td { padding: 8px 12px; font-size: 13px; }
    .progress-bar-custom { height: 20px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
    .progress-bar-custom .bar { height: 100%; background: linear-gradient(90deg, #007bff, #00d4ff); transition: width 0.3s; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-map-marked-alt"></i> Sebaran Geografis Pendaftar</h1>
    </div>
    <div class="col-sm-6">
        <div class="d-flex justify-content-sm-end align-items-center" style="gap: 10px;">
            <a href="{{ route('admin.statistik.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <form class="form-inline">
                <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@stop

@section('content')
{{-- Peta Sebaran --}}
@if($mapData->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map"></i> Peta Lokasi Pendaftaran</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $mapData->count() }} lokasi terdeteksi</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- By Provinsi --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-globe-asia"></i> Pendaftar per Provinsi</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Provinsi</th>
                            <th width="80" class="text-center">Jumlah</th>
                            <th width="150">Grafik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maxProv = $byProvinsi->max('total') ?: 1; @endphp
                        @forelse($byProvinsi as $i => $prov)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="?provinsi={{ urlencode($prov->provinsi_code) }}&tahun_pelajaran_id={{ $tahunAktif?->id }}">
                                    {{ $prov->provinsi }}
                                </a>
                            </td>
                            <td class="text-center"><strong>{{ $prov->total }}</strong></td>
                            <td>
                                <div class="progress-bar-custom">
                                    <div class="bar" style="width: {{ ($prov->total / $maxProv) * 100 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- By Kabupaten --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-city"></i> Pendaftar per Kabupaten/Kota</h3>
                @if($filterProvinsi)
                <div class="card-tools">
                    <span class="badge badge-primary">{{ $filterProvinsi }}</span>
                    <a href="{{ route('admin.statistik.geografis', ['tahun_pelajaran_id' => $tahunAktif?->id]) }}" class="badge badge-secondary">Reset</a>
                </div>
                @endif
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Kabupaten/Kota</th>
                            <th width="80" class="text-center">Jumlah</th>
                            <th width="150">Grafik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maxKab = $byKabupaten->max('total') ?: 1; @endphp
                        @forelse($byKabupaten as $i => $kab)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="?kabupaten={{ urlencode($kab->kabupaten_code) }}&tahun_pelajaran_id={{ $tahunAktif?->id }}">
                                    {{ $kab->kabupaten }}
                                </a>
                                @if(!$filterProvinsi)
                                <br><small class="text-muted">{{ $kab->provinsi }}</small>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $kab->total }}</strong></td>
                            <td>
                                <div class="progress-bar-custom">
                                    <div class="bar" style="width: {{ ($kab->total / $maxKab) * 100 }}%; background: linear-gradient(90deg, #28a745, #20c997);"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- By Kecamatan --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker"></i> Pendaftar per Kecamatan</h3>
                @if($filterKabupaten)
                <div class="card-tools">
                    <span class="badge badge-success">{{ $filterKabupaten }}</span>
                </div>
                @endif
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Kecamatan</th>
                            <th width="80" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byKecamatan as $i => $kec)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="?kecamatan={{ urlencode($kec->kecamatan_code) }}&tahun_pelajaran_id={{ $tahunAktif?->id }}">
                                    {{ $kec->kecamatan }}
                                </a>
                                @if(!$filterKabupaten)
                                <br><small class="text-muted">{{ $kec->kabupaten }}</small>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $kec->total }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Pilih kabupaten untuk melihat data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- By Kelurahan --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-home"></i> Pendaftar per Kelurahan/Desa</h3>
                @if($filterKecamatan)
                <div class="card-tools">
                    <span class="badge badge-info">{{ $filterKecamatan }}</span>
                </div>
                @endif
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Kelurahan/Desa</th>
                            <th width="80" class="text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byKelurahan as $i => $kel)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $kel->kelurahan }}
                                @if(!$filterKecamatan)
                                <br><small class="text-muted">{{ $kel->kecamatan }}</small>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $kel->total }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Pilih kecamatan untuk melihat data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@if($mapData->count() > 0)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-6.9, 107.6], 8);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    var markers = [];
    @foreach($mapData as $pendaftar)
    markers.push(L.marker([{{ $pendaftar->registration_latitude }}, {{ $pendaftar->registration_longitude }}])
        .bindPopup('<strong>{{ addslashes($pendaftar->nama_lengkap) }}</strong><br><small>{{ addslashes($pendaftar->registration_address ?? "Alamat tidak tersedia") }}</small>'));
    @endforeach
    
    var group = L.featureGroup(markers).addTo(map);
    if (markers.length > 0) {
        map.fitBounds(group.getBounds().pad(0.1));
    }
</script>
@endif
@stop
