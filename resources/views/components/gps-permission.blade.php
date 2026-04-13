{{-- GPS Location Background Detection Component --}}
{{-- Silent background GPS detection - no popup/overlay --}}

<script>
(function() {
    const SESSION_ID = '{{ session()->getId() }}';
    const API_URL = '{{ route("api.visitor-location") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const GPS_SENT_KEY = 'ppdb_gps_sent';

    function hasAlreadySent() {
        return sessionStorage.getItem(GPS_SENT_KEY) === 'true';
    }

    function sendLocationToServer(position) {
        var data = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitude_accuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            session_id: SESSION_ID
        };

        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            sessionStorage.setItem(GPS_SENT_KEY, 'true');
            console.log('[GPS] Location sent:', result);
        })
        .catch(function(e) {
            console.log('[GPS] Send failed:', e.message);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (hasAlreadySent()) return;

        if (!navigator.geolocation) {
            console.log('[GPS] Browser tidak support geolocation');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                sendLocationToServer(position);
            },
            function(error) {
                console.log('[GPS] Gagal:', error.message);
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    });
})();
</script>
