{{-- GPS Location Permission Component --}}
{{-- Include this in layouts that need GPS tracking --}}

<style>
    .gps-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .gps-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    .gps-modal {
        background: white;
        border-radius: 24px;
        padding: 40px;
        max-width: 450px;
        width: 90%;
        text-align: center;
        transform: scale(0.8) translateY(20px);
        transition: all 0.3s ease;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    }
    
    .gps-overlay.show .gps-modal {
        transform: scale(1) translateY(0);
    }
    
    .gps-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 45px;
        color: white;
        animation: pulse-location 2s infinite;
    }
    
    @keyframes pulse-location {
        0%, 100% { 
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
        }
        50% { 
            box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
        }
    }
    
    .gps-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
    
    .gps-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 30px;
    }
    
    .gps-benefits {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        text-align: left;
    }
    
    .gps-benefits h6 {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 12px;
    }
    
    .gps-benefits ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .gps-benefits li {
        font-size: 0.85rem;
        color: #64748b;
        padding: 6px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .gps-benefits li i {
        color: #10b981;
        font-size: 0.9rem;
    }
    
    .gps-buttons {
        display: flex;
        gap: 12px;
    }
    
    .btn-gps {
        flex: 1;
        padding: 14px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-gps-allow {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-gps-allow:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-gps-skip {
        background: #f1f5f9;
        color: #64748b;
    }
    
    .btn-gps-skip:hover {
        background: #e2e8f0;
    }
    
    .gps-status {
        display: none;
        margin-top: 20px;
        padding: 15px;
        border-radius: 12px;
        font-size: 0.9rem;
    }
    
    .gps-status.loading {
        display: block;
        background: #eff6ff;
        color: #3b82f6;
    }
    
    .gps-status.success {
        display: block;
        background: #ecfdf5;
        color: #10b981;
    }
    
    .gps-status.error {
        display: block;
        background: #fef2f2;
        color: #ef4444;
    }
    
    .gps-location-info {
        display: none;
        margin-top: 15px;
        padding: 15px;
        background: #f0f9ff;
        border-radius: 12px;
        text-align: left;
        font-size: 0.8rem;
    }
    
    .gps-location-info.show {
        display: block;
    }
    
    .gps-location-info .coord-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .gps-location-info .coord-row:last-child {
        border-bottom: none;
    }
    
    .gps-location-info .coord-label {
        color: #64748b;
        font-weight: 500;
    }
    
    .gps-location-info .coord-value {
        color: #1e293b;
        font-family: monospace;
    }
    
    .gps-privacy {
        margin-top: 20px;
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .gps-privacy i {
        margin-right: 5px;
    }
    
    /* Mobile responsive */
    @media (max-width: 576px) {
        .gps-modal {
            padding: 30px 20px;
        }
        
        .gps-icon {
            width: 80px;
            height: 80px;
            font-size: 35px;
        }
        
        .gps-title {
            font-size: 1.3rem;
        }
        
        .gps-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="gps-overlay" id="gpsOverlay">
    <div class="gps-modal">
        <div class="gps-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <h3 class="gps-title">Aktifkan Lokasi</h3>
        <p class="gps-description">
            Untuk pengalaman PPDB yang lebih baik, izinkan kami mengakses lokasi Anda.
        </p>
        
        <div class="gps-benefits">
            <h6><i class="fas fa-shield-alt"></i> Mengapa kami memerlukan lokasi?</h6>
            <ul>
                <li><i class="fas fa-check-circle"></i> Verifikasi zona pendaftaran</li>
                <li><i class="fas fa-check-circle"></i> Analisis jangkauan informasi PPDB</li>
                <li><i class="fas fa-check-circle"></i> Meningkatkan layanan berdasarkan lokasi</li>
            </ul>
        </div>
        
        <div class="gps-buttons" id="gpsButtons">
            <button type="button" class="btn-gps btn-gps-skip" onclick="skipGpsPermission()">
                <i class="fas fa-times"></i> Nanti
            </button>
            <button type="button" class="btn-gps btn-gps-allow" onclick="requestGpsPermission()">
                <i class="fas fa-location-arrow"></i> Izinkan
            </button>
        </div>
        
        <div class="gps-status" id="gpsStatus">
            <i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...
        </div>
        
        <div class="gps-location-info" id="gpsLocationInfo">
            <div class="coord-row">
                <span class="coord-label">Latitude:</span>
                <span class="coord-value" id="coordLat">-</span>
            </div>
            <div class="coord-row">
                <span class="coord-label">Longitude:</span>
                <span class="coord-value" id="coordLng">-</span>
            </div>
            <div class="coord-row">
                <span class="coord-label">Akurasi:</span>
                <span class="coord-value" id="coordAccuracy">-</span>
            </div>
            <div class="coord-row" id="coordAltRow" style="display:none;">
                <span class="coord-label">Altitude:</span>
                <span class="coord-value" id="coordAlt">-</span>
            </div>
            <div class="coord-row" id="coordAddressRow" style="display:none;">
                <span class="coord-label">Alamat:</span>
                <span class="coord-value" id="coordAddress" style="font-family: inherit; font-size: 0.75rem;">-</span>
            </div>
        </div>
        
        <p class="gps-privacy">
            <i class="fas fa-lock"></i> Lokasi Anda aman dan hanya digunakan untuk keperluan PPDB
        </p>
    </div>
</div>

<script>
(function() {
    const SESSION_ID = '{{ session()->getId() }}';
    const API_URL = '{{ route("api.visitor-location") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const GPS_ASKED_KEY = 'ppdb_gps_asked';
    const GPS_GRANTED_KEY = 'ppdb_gps_granted';
    
    // Check if GPS permission was already asked in this session
    function hasAskedGps() {
        return sessionStorage.getItem(GPS_ASKED_KEY) === 'true';
    }
    
    function setAskedGps() {
        sessionStorage.setItem(GPS_ASKED_KEY, 'true');
    }
    
    function setGpsGranted() {
        sessionStorage.setItem(GPS_GRANTED_KEY, 'true');
    }
    
    function hasGpsGranted() {
        return sessionStorage.getItem(GPS_GRANTED_KEY) === 'true';
    }
    
    // Show GPS modal
    function showGpsModal() {
        const overlay = document.getElementById('gpsOverlay');
        if (overlay) {
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Hide GPS modal
    function hideGpsModal() {
        const overlay = document.getElementById('gpsOverlay');
        if (overlay) {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
    
    // Update status display
    function showStatus(type, message) {
        const status = document.getElementById('gpsStatus');
        const buttons = document.getElementById('gpsButtons');
        
        if (status && buttons) {
            buttons.style.display = 'none';
            status.className = 'gps-status ' + type;
            status.innerHTML = message;
        }
    }
    
    // Show location info
    function showLocationInfo(position, address) {
        const info = document.getElementById('gpsLocationInfo');
        
        document.getElementById('coordLat').textContent = position.coords.latitude.toFixed(8);
        document.getElementById('coordLng').textContent = position.coords.longitude.toFixed(8);
        document.getElementById('coordAccuracy').textContent = '±' + Math.round(position.coords.accuracy) + ' meter';
        
        if (position.coords.altitude !== null) {
            document.getElementById('coordAlt').textContent = position.coords.altitude.toFixed(1) + ' m';
            document.getElementById('coordAltRow').style.display = 'flex';
        }
        
        if (address) {
            document.getElementById('coordAddress').textContent = address;
            document.getElementById('coordAddressRow').style.display = 'flex';
        }
        
        info.classList.add('show');
    }
    
    // Send location to server
    function sendLocationToServer(position) {
        const data = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitude_accuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            session_id: SESSION_ID
        };
        
        return fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            console.log('Location sent successfully:', result);
            return result;
        })
        .catch(error => {
            console.error('Failed to send location:', error);
            return null;
        });
    }
    
    // Request GPS permission
    window.requestGpsPermission = function() {
        setAskedGps();
        
        if (!navigator.geolocation) {
            showStatus('error', '<i class="fas fa-exclamation-triangle"></i> Browser Anda tidak mendukung GPS');
            setTimeout(hideGpsModal, 2000);
            return;
        }
        
        showStatus('loading', '<i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi presisi tinggi...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                setGpsGranted();
                
                showStatus('loading', '<i class="fas fa-spinner fa-spin"></i> Mengirim data lokasi...');
                
                // Send to server and get address
                sendLocationToServer(position).then(function(result) {
                    showStatus('success', '<i class="fas fa-check-circle"></i> Lokasi berhasil didapatkan!');
                    
                    // Show location details
                    const address = result && result.data ? result.data.address : null;
                    showLocationInfo(position, address);
                    
                    // Close modal after showing info
                    setTimeout(hideGpsModal, 3000);
                });
            },
            function(error) {
                let errorMessage = 'Gagal mendapatkan lokasi';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin lokasi ditolak. Silakan aktifkan GPS di pengaturan browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Waktu mendapatkan lokasi habis. Coba lagi di tempat terbuka.';
                        break;
                }
                
                showStatus('error', '<i class="fas fa-exclamation-triangle"></i> ' + errorMessage);
                setTimeout(hideGpsModal, 3000);
            },
            {
                enableHighAccuracy: true, // Use GPS for high accuracy
                timeout: 15000, // Wait up to 15 seconds
                maximumAge: 0 // Don't use cached position
            }
        );
    };
    
    // Skip GPS permission
    window.skipGpsPermission = function() {
        setAskedGps();
        hideGpsModal();
    };
    
    // Auto-send location if previously granted
    function autoSendLocation() {
        if (hasGpsGranted() && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    sendLocationToServer(position);
                },
                function(error) {
                    console.log('Auto location failed:', error.message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000 // Cache for 30 seconds
                }
            );
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we should show the GPS modal
        if (!hasAskedGps()) {
            // Show modal after a short delay
            setTimeout(showGpsModal, 1500);
        } else if (hasGpsGranted()) {
            // Auto-send location silently
            autoSendLocation();
        }
    });
})();
</script>
