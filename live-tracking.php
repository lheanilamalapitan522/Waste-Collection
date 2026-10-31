<?php
require_once 'auth.php';
require 'config.php';
$isCollector = is_collector();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking — <?php echo SITE_NAME; ?></title>
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        html, body { margin: 0; padding: 0; }
        
        #tracking-map {
            width: 100%;
            height: 550px;
            background: #e5e3df;
            border-radius: 8px;
            position: relative;
            z-index: 1;
            border: 0;
        }
        
        .tracking-container {
            position: relative;
            margin: 20px 0;
        }
        
        .truck-info-panel {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #ffffff;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 260px;
        }
        
        .truck-info-panel h3 {
            font-size: 1rem;
            margin: 0 0 12px 0;
            color: #222222;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .live-pulse {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
            70% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 0.9rem;
        }
        
        .info-row span:first-child { color: #666666; }
        .info-row span:last-child { font-weight: 600; color: #222222; }
        
        .controls-panel {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .controls-panel button {
            background: #009933;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            margin: 2px;
        }
        
        .controls-panel button.paused { background: #f59e0b; }
        
        .route-legend {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #ffffff;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            font-size: 0.85rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .dot-start { background: #22c55e; }
        .dot-route { background: #3b82f6; }
        .dot-end { background: #ef4444; }
        
        .leaflet-container { z-index: 10 !important; }
    </style>
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="page-header">
        <div class="page-title-section">
            <h1>🚛 LIVE TRUCK TRACKING</h1>
            <p>Real-time location — Truck 01 • Route 1 — Brgy. San Isidro → Poblacion</p>
        </div>
    </div>

    <div class="tracking-container">
        <iframe
            id="tracking-map"
            title="Live truck tracking map in Mauban, Quezon"
            src="https://www.google.com/maps?q=Mauban%2C+Quezon%2C+Philippines&output=embed"
            loading="lazy"
            allowfullscreen></iframe>
        
        <div class="truck-info-panel">
            <h3><span class="live-pulse"></span> Truck 01 — On Route</h3>
            <div class="info-row"><span>📍 Current:</span><span id="current-location">Brgy. San Isidro</span></div>
            <div class="info-row"><span>➡ Next Stop:</span><span id="next-location">Brgy. Pag-asa</span></div>
            <div class="info-row"><span>⏱ ETA:</span><span id="eta">~12 min</span></div>
            <div class="info-row"><span>🚀 Speed:</span><span id="speed">28 km/h</span></div>
            <div class="info-row"><span>📅 Last Update:</span><span id="last-update">Just now</span></div>
        </div>

    </div>
</div>

<script>
const routePoints = [
    { lat: 14.1852, lng: 121.6835, name: 'Brgy. San Isidro' },
    { lat: 14.1828, lng: 121.6860, name: 'Brgy. Pag-asa' },
    { lat: 14.1805, lng: 121.6885, name: 'Brgy. Bagong Bayan' },
    { lat: 14.1780, lng: 121.6910, name: 'Brgy. Poblacion' }
];

let map, truckMarker;
let currentPointIndex = 0;
let segmentProgress = 0;
let isPaused = false;
let hasLiveLocation = false;
const isCollector = <?php echo $isCollector ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('tracking-map');
    if (window.L && mapContainer && mapContainer.tagName !== 'IFRAME') {
        initMap();
    }
});

function initMap() {
    const mapContainer = document.getElementById('tracking-map');
    if (!mapContainer) {
        console.error('Map container not found!');
        return;
    }
    
    const startPos = [routePoints[0].lat, routePoints[0].lng];
    map = L.map('tracking-map').setView(startPos, 15);
    
    // ✅ Fixed: Using OpenStreetMap — works everywhere!
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const truckIcon = L.divIcon({
        html: '<div style="font-size: 28px; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));">🚛</div>',
        className: '',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    truckMarker = L.marker(startPos, { icon: truckIcon })
        .addTo(map)
        .bindPopup('<strong>🚛 Truck 01</strong><br>Route: San Isidro → Poblacion<br>Status: ● Moving');

    const latlngs = routePoints.map(p => [p.lat, p.lng]);
    L.polyline(latlngs, { color: '#3b82f6', weight: 4, opacity: 0.8 }).addTo(map);

    L.circleMarker(latlngs[0], {
        radius: 8, fillColor: '#22c55e', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2
    }).addTo(map);

    L.circleMarker(latlngs[latlngs.length - 1], {
        radius: 8, fillColor: '#ef4444', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2
    }).addTo(map);

    if (isCollector) {
        startLocationSharing();
    } else {
        startAnimation();
        setInterval(fetchLatestLocation, 10000);
        fetchLatestLocation();
    }

    document.getElementById('toggleBtn').addEventListener('click', isCollector ? toggleLocationSharing : toggleAnimation);
    document.getElementById('centerBtn').addEventListener('click', () => {
        map.panTo(truckMarker.getLatLng());
    });
}

let watchId = null;
function startLocationSharing() {
    if (!navigator.geolocation) {
        document.getElementById('last-update').textContent = 'GPS unavailable';
        return;
    }
    watchId = navigator.geolocation.watchPosition(function(position) {
        truckMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
        document.getElementById('current-location').textContent = 'Current device location';
        document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
        fetch('location-update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            })
        }).catch(function() {
            document.getElementById('last-update').textContent = 'Update failed';
        });
    }, function() {
        document.getElementById('last-update').textContent = 'GPS permission denied';
    }, { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 });
    document.getElementById('toggleBtn').textContent = '⏹ Stop sharing';
}

function toggleLocationSharing() {
    if (watchId === null) {
        startLocationSharing();
        return;
    }
    navigator.geolocation.clearWatch(watchId);
    watchId = null;
    document.getElementById('toggleBtn').textContent = '▶ Start sharing';
    document.getElementById('last-update').textContent = 'Sharing stopped';
}

function fetchLatestLocation() {
    fetch('latest-location.php')
        .then(function(response) { return response.json(); })
        .then(function(location) {
            if (!location.latitude || !location.longitude) return;
            hasLiveLocation = true;
            if (truckMarker) {
                truckMarker.setLatLng([location.latitude, location.longitude]);
            }
            document.getElementById('current-location').textContent =
                Number(location.latitude).toFixed(5) + ', ' +
                Number(location.longitude).toFixed(5);
            document.getElementById('last-update').textContent = location.recorded_at;
        })
        .catch(function() {
            document.getElementById('last-update').textContent = 'Location unavailable';
        });
}

function startAnimation() {
    setInterval(() => {
        if (isPaused || hasLiveLocation) return;
        segmentProgress += 0.008;
        if (segmentProgress >= 1) {
            segmentProgress = 0;
            currentPointIndex++;
            if (currentPointIndex >= routePoints.length - 1) currentPointIndex = 0;
        }
        const from = routePoints[currentPointIndex];
        const to = routePoints[currentPointIndex + 1];
        const t = segmentProgress;
        const easeT = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
        const newLat = from.lat + (to.lat - from.lat) * easeT;
        const newLng = from.lng + (to.lng - from.lng) * easeT;
        truckMarker.setLatLng([newLat, newLng]);
        
        document.getElementById('current-location').textContent = from.name;
        document.getElementById('next-location').textContent = to.name;
        document.getElementById('eta').textContent = '~' + Math.round((1 - segmentProgress) * 15) + ' min';
        document.getElementById('speed').textContent = (Math.floor(Math.random() * 10) + 22) + ' km/h';
        document.getElementById('last-update').textContent = 'Just now';
    }, 80);
}

function toggleAnimation() {
    isPaused = !isPaused;
    const btn = document.getElementById('toggleBtn');
    btn.textContent = isPaused ? '▶ Resume' : '⏸ Pause';
    btn.classList.toggle('paused', isPaused);
}

if (!isCollector) {
    document.addEventListener('DOMContentLoaded', function() {
        fetchLatestLocation();
        window.setInterval(fetchLatestLocation, 10000);
    });
}
</script>

</body>
</html>