@extends('adminlte::page')

@section('title', 'Peta Pengunjung')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-map-marker-alt mr-2"></i>Peta Pengunjung</h1>
            <small class="text-muted">Visualisasi lokasi pengunjung website PPDB</small>
        </div>
        <div>
            <a href="{{ route('admin.visitor-logs.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Statistik
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Date Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.visitor-logs.map') }}" method="GET" class="row align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-4 text-right">
                    <span class="badge badge-info">
                        <i class="fas fa-map-marker-alt"></i> {{ $visitors->count() }} titik lokasi
                    </span>
                    <span class="badge badge-success ml-1">
                        <i class="fas fa-satellite"></i> {{ $visitors->where('source', 'gps')->count() }} GPS
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- Map --}}
    <div class="card">
        <div class="card-body p-0">
            <div id="visitorMap" style="height: 600px; width: 100%;"></div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card mt-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <i class="fas fa-mobile-alt fa-2x text-success mb-2"></i>
                    <p class="mb-0"><strong>Mobile</strong></p>
                    <small class="text-muted">Pengunjung dari HP</small>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-tablet-alt fa-2x text-info mb-2"></i>
                    <p class="mb-0"><strong>Tablet</strong></p>
                    <small class="text-muted">Pengunjung dari Tablet</small>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-desktop fa-2x text-primary mb-2"></i>
                    <p class="mb-0"><strong>Desktop</strong></p>
                    <small class="text-muted">Pengunjung dari PC/Laptop</small>
                </div>
                <div class="col-md-3">
                    <i class="fas fa-satellite fa-2x text-success mb-2"></i>
                    <p class="mb-0"><strong>GPS Presisi</strong></p>
                    <small class="text-muted">Lingkaran = area akurasi</small>
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
        min-width: 250px;
        max-width: 350px;
    }
    .visitor-popup {
        font-size: 13px;
    }
    .visitor-popup .device-icon {
        font-size: 24px;
        margin-right: 10px;
    }
    .gps-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
    }
    .gps-badge.gps { background: #28a745; color: white; }
    .gps-badge.ip { background: #6c757d; color: white; }
    .coord-table {
        font-size: 11px;
        width: 100%;
    }
    .coord-table td {
        padding: 2px 4px;
    }
    .coord-label { color: #666; }
    .coord-value { font-family: monospace; }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Indonesia
    const map = L.map('visitorMap').setView([-2.5, 118], 5);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Visitor data
    const visitors = @json($visitors);

    // Create marker cluster group
    const markers = L.markerClusterGroup({
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50
    });
    
    // Layer group for accuracy circles
    const accuracyCircles = L.layerGroup();

    // Device colors
    const deviceColors = {
        'mobile': '#28a745',
        'tablet': '#17a2b8',
        'desktop': '#007bff'
    };

    // Helper functions
    function formatCoord(val, decimals = 8) {
        return val ? parseFloat(val).toFixed(decimals) : '-';
    }
    
    function getHeadingDirection(heading) {
        if (heading === null) return '';
        const directions = ['U', 'TL', 'T', 'TG', 'S', 'BD', 'B', 'BL'];
        const index = Math.round(heading / 45) % 8;
        return directions[index];
    }

    // Add markers
    visitors.forEach(function(visitor) {
        const color = deviceColors[visitor.device] || '#6c757d';
        const isGps = visitor.source === 'gps';
        
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${color}; width: ${isGps ? 14 : 12}px; height: ${isGps ? 14 : 12}px; border-radius: 50%; border: 2px solid ${isGps ? '#28a745' : 'white'}; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [isGps ? 14 : 12, isGps ? 14 : 12],
            iconAnchor: [isGps ? 7 : 6, isGps ? 7 : 6]
        });

        const marker = L.marker([visitor.lat, visitor.lng], { icon: icon });
        
        // Build detailed popup content
        let locationInfo = '';
        if (visitor.address) {
            locationInfo = `<strong>${visitor.address}</strong>`;
            if (visitor.subdistrict || visitor.district) {
                locationInfo += `<br><small>${visitor.subdistrict || ''}${visitor.subdistrict && visitor.district ? ', ' : ''}${visitor.district || ''}</small>`;
            }
            if (visitor.city || visitor.region) {
                locationInfo += `<br><small>${visitor.city || ''}${visitor.city && visitor.region ? ', ' : ''}${visitor.region || ''}</small>`;
            }
        } else {
            locationInfo = `<strong>${visitor.city || 'Unknown'}</strong>${visitor.region ? ', ' + visitor.region : ''}`;
        }
        
        // GPS data section
        let gpsInfo = '';
        if (isGps) {
            gpsInfo = `
                <table class="coord-table">
                    <tr>
                        <td class="coord-label">Latitude:</td>
                        <td class="coord-value">${formatCoord(visitor.lat)}</td>
                    </tr>
                    <tr>
                        <td class="coord-label">Longitude:</td>
                        <td class="coord-value">${formatCoord(visitor.lng)}</td>
                    </tr>
                    ${visitor.accuracy ? `<tr><td class="coord-label">Akurasi:</td><td class="coord-value">±${Math.round(visitor.accuracy)}m</td></tr>` : ''}
                    ${visitor.altitude !== null ? `<tr><td class="coord-label">Altitude:</td><td class="coord-value">${formatCoord(visitor.altitude, 1)}m</td></tr>` : ''}
                    ${visitor.heading !== null ? `<tr><td class="coord-label">Heading:</td><td class="coord-value">${Math.round(visitor.heading)}° ${getHeadingDirection(visitor.heading)}</td></tr>` : ''}
                    ${visitor.speed !== null ? `<tr><td class="coord-label">Speed:</td><td class="coord-value">${(visitor.speed * 3.6).toFixed(1)} km/h</td></tr>` : ''}
                </table>
            `;
        }
        
        const popupContent = `
            <div class="visitor-popup">
                <div class="d-flex align-items-center mb-2">
                    <span class="device-icon" style="color: ${color}">
                        ${visitor.device === 'mobile' ? '<i class="fas fa-mobile-alt"></i>' : 
                          visitor.device === 'tablet' ? '<i class="fas fa-tablet-alt"></i>' : 
                          '<i class="fas fa-desktop"></i>'}
                    </span>
                    <div>
                        ${locationInfo}
                    </div>
                </div>
                <div class="mb-2">
                    <span class="gps-badge ${isGps ? 'gps' : 'ip'}">
                        <i class="fas fa-${isGps ? 'satellite' : 'globe'}"></i> ${isGps ? 'GPS Presisi' : 'IP Address'}
                    </span>
                </div>
                ${gpsInfo}
                <hr class="my-2">
                <small>
                    <i class="fas fa-clock mr-1"></i> ${visitor.time}<br>
                    <i class="fas fa-${visitor.device} mr-1"></i> ${visitor.device.charAt(0).toUpperCase() + visitor.device.slice(1)}
                </small>
                <div class="mt-2">
                    <a href="https://www.google.com/maps?q=${visitor.lat},${visitor.lng}" target="_blank" class="btn btn-xs btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Google Maps
                    </a>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markers.addLayer(marker);
        
        // Add accuracy circle for GPS locations
        if (isGps && visitor.accuracy && visitor.accuracy < 500) {
            const circle = L.circle([visitor.lat, visitor.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.1,
                radius: visitor.accuracy,
                weight: 1
            });
            accuracyCircles.addLayer(circle);
        }
    });

    map.addLayer(markers);

    // Add toggle for accuracy circles
    const overlayMaps = {
        "Lingkaran Akurasi GPS": accuracyCircles
    };
    L.control.layers(null, overlayMaps, { position: 'topright' }).addTo(map);

    // Fit bounds if there are markers
    if (visitors.length > 0) {
        const bounds = markers.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
});
</script>
@stop
