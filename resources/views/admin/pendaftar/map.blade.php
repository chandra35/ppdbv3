@extends('adminlte::page')

@section('title', 'Peta Lokasi Pendaftaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-map-marked-alt mr-2"></i>Peta Lokasi Pendaftaran</h1>
            <small class="text-muted">Visualisasi lokasi saat calon siswa melakukan pendaftaran</small>
        </div>
        <div>
            <a href="{{ route('admin.pendaftar.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.pendaftar.map') }}" method="GET" class="row align-items-center">
                <div class="col-md-4">
                    <select name="jalur_id" class="form-control form-control-sm">
                        <option value="">Semua Jalur</option>
                        @foreach($jalurList as $jalur)
                            <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                {{ $jalur->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-info">
                        <i class="fas fa-map-marker-alt"></i> {{ $pendaftars->count() }} pendaftar dengan lokasi GPS
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- Map --}}
    <div class="card">
        <div class="card-body p-0">
            <div id="pendaftarMap" style="height: 600px; width: 100%;"></div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card mt-3">
        <div class="card-body">
            <h6><i class="fas fa-info-circle text-info mr-1"></i> Keterangan</h6>
            <div class="row">
                <div class="col-md-4">
                    <small>
                        <strong>Warna Marker:</strong><br>
                        Marker diwarnai berdasarkan jalur pendaftaran yang dipilih calon siswa.
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <strong>Ukuran Lingkaran:</strong><br>
                        Lingkaran transparan menunjukkan radius akurasi GPS saat pendaftaran.
                    </small>
                </div>
                <div class="col-md-4">
                    <small>
                        <strong>Klik Marker:</strong><br>
                        Klik marker untuk melihat detail pendaftar dan link ke data lengkap.
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    .leaflet-popup-content {
        min-width: 280px;
        max-width: 350px;
    }
    .pendaftar-popup {
        font-size: 13px;
    }
    .pendaftar-popup .popup-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px;
        margin: -10px -10px 10px -10px;
        border-radius: 4px 4px 0 0;
    }
    .pendaftar-popup .popup-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
    }
    .pendaftar-popup .popup-header small {
        opacity: 0.9;
    }
    .pendaftar-popup .info-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px dashed #eee;
    }
    .pendaftar-popup .info-row:last-child {
        border-bottom: none;
    }
    .pendaftar-popup .info-label {
        color: #666;
    }
    .pendaftar-popup .info-value {
        font-weight: 500;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Indonesia
    const map = L.map('pendaftarMap').setView([-2.5, 118], 5);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Pendaftar data
    const pendaftars = @json($pendaftars);

    // Create marker cluster group
    const markers = L.markerClusterGroup({
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50
    });
    
    // Layer for accuracy circles
    const accuracyCircles = L.layerGroup();

    // Status colors
    const statusColors = {
        'pending': '#ffc107',
        'verified': '#28a745',
        'revision': '#17a2b8',
        'rejected': '#dc3545'
    };
    
    const statusLabels = {
        'pending': 'Menunggu Verifikasi',
        'verified': 'Terverifikasi',
        'revision': 'Perlu Revisi',
        'rejected': 'Ditolak'
    };

    // Add markers
    pendaftars.forEach(function(p) {
        const color = p.warna_jalur || '#007bff';
        
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        const marker = L.marker([p.lat, p.lng], { icon: icon });
        
        // Build popup content
        const statusColor = statusColors[p.status] || '#6c757d';
        const statusLabel = statusLabels[p.status] || p.status;
        
        let locationInfo = '';
        if (p.address) {
            locationInfo = `<div class="info-row">
                <span class="info-label">Alamat:</span>
                <span class="info-value" style="text-align: right; max-width: 180px; font-size: 11px;">${p.address.substring(0, 80)}...</span>
            </div>`;
        }
        if (p.city) {
            locationInfo += `<div class="info-row">
                <span class="info-label">Kota:</span>
                <span class="info-value">${p.city}</span>
            </div>`;
        }
        
        const popupContent = `
            <div class="pendaftar-popup">
                <div class="popup-header">
                    <h5><i class="fas fa-user-graduate mr-1"></i> ${p.nama}</h5>
                    <small><i class="fas fa-id-card mr-1"></i> ${p.nomor_registrasi || p.nisn}</small>
                </div>
                <div class="info-row">
                    <span class="info-label">Jalur:</span>
                    <span class="info-value" style="color: ${color}"><i class="fas fa-road"></i> ${p.jalur}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: ${statusColor}">${statusLabel}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Waktu Daftar:</span>
                    <span class="info-value">${p.tanggal}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Perangkat:</span>
                    <span class="info-value"><i class="fas fa-${p.device === 'mobile' ? 'mobile-alt' : p.device === 'tablet' ? 'tablet-alt' : 'desktop'}"></i> ${p.device || '-'}</span>
                </div>
                ${p.accuracy ? `<div class="info-row">
                    <span class="info-label">Akurasi GPS:</span>
                    <span class="info-value">±${Math.round(p.accuracy)}m</span>
                </div>` : ''}
                ${p.altitude ? `<div class="info-row">
                    <span class="info-label">Altitude:</span>
                    <span class="info-value">${p.altitude.toFixed(1)}m</span>
                </div>` : ''}
                ${locationInfo}
                <div style="margin-top: 10px; text-align: center;">
                    <a href="{{ url('admin/pendaftar') }}/${p.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="https://www.google.com/maps?q=${p.lat},${p.lng}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-map"></i> Google Maps
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markers.addLayer(marker);
        
        // Add accuracy circle
        if (p.accuracy && p.accuracy < 500) {
            const circle = L.circle([p.lat, p.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.1,
                radius: p.accuracy,
                weight: 1
            });
            accuracyCircles.addLayer(circle);
        }
    });

    map.addLayer(markers);

    // Add layer control
    const overlayMaps = {
        "Lingkaran Akurasi GPS": accuracyCircles
    };
    L.control.layers(null, overlayMaps, { position: 'topright' }).addTo(map);

    // Fit bounds if there are markers
    if (pendaftars.length > 0) {
        const bounds = markers.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
});
</script>
@stop
