<?php
require_once 'auth.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
    <style>
        .tracking-status { margin: 12px 0; }
        .tracking-embed-map { position: relative; width: 100%; height: min(65vh, 620px); min-height: 360px; overflow: hidden; border-radius: 12px; background: #e9efe8; }
        .tracking-embed-map iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; pointer-events: none; }
        .tracking-location-dot { position: absolute; z-index: 2; top: 50%; left: 50%; display: grid; width: 48px; height: 48px; place-items: center; transform: translate(-50%, -50%); border: 3px solid #fff; border-radius: 50%; background: #087a3e; color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.45); }
        .tracking-location-dot svg { width: 29px; height: 29px; }
        .tracking-location-dot::after { position: absolute; inset: -9px; border: 2px solid rgba(8,122,62,.45); border-radius: 50%; content: ""; animation: tracking-pulse 1.8s ease-out infinite; }
        @keyframes tracking-pulse { 0% { transform: scale(.55); opacity: .9; } 100% { transform: scale(1.45); opacity: 0; } }
    </style>
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>
    <div class="page-header"><div class="page-title-section"><h1>LIVE TRUCK TRACKING</h1><p>Collector locations appear during their assigned active collection schedules.</p></div></div>
    <section class="section-card" style="padding: 16px;">
        <p id="tracking-status" class="tracking-status" data-map-status role="status" aria-live="polite">Waiting for a truck location…</p>
        <div class="tracking-embed-map" id="tracking-map" aria-label="OpenStreetMap centered on the live collector location">
            <iframe id="tracking-map-frame" title="OpenStreetMap centered on the live collector location" loading="eager" referrerpolicy="strict-origin-when-cross-origin" src="https://www.openstreetmap.org/export/embed?bbox=121.17233276367189%2C14.01335989114583%2C121.98944091796876%2C14.485876994143412&amp;layer=mapnik"></iframe>
            <span id="tracking-location-dot" class="tracking-location-dot" hidden role="img" aria-label="Waste collection truck current location">
                <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="currentColor" d="M5 12h23a3 3 0 0 1 3 3v4h6.4a4 4 0 0 1 3.2 1.6l3.6 4.8A4 4 0 0 1 45 27v9h-3.2a6 6 0 0 1-11.6 0H18.8a6 6 0 0 1-11.6 0H4a2 2 0 0 1-2-2V15a3 3 0 0 1 3-3Zm26 10v7h10v-2l-3.7-5H31ZM13 33a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm23 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>
            </span>
        </div>
        <p class="small mt-2 mb-0"><a id="tracking-map-link" href="https://www.openstreetmap.org/#map=11/14.2497/121.5809" target="_blank" rel="noopener noreferrer">View larger map</a></p>
    </section>
</main>
<script>
const mapFrame = document.getElementById('tracking-map-frame');
const mapLink = document.getElementById('tracking-map-link');
const locationDot = document.getElementById('tracking-location-dot');
const status = document.getElementById('tracking-status');

function centerEmbedMap(latitude, longitude) {
    const halfLongitudeSpan = 0.408554077148435;
    const halfLatitudeSpan = 0.236258551498791;
    const bounds = [
        longitude - halfLongitudeSpan,
        latitude - halfLatitudeSpan,
        longitude + halfLongitudeSpan,
        latitude + halfLatitudeSpan
    ].join(',');
    const nextUrl = 'https://www.openstreetmap.org/export/embed?bbox=' + encodeURIComponent(bounds) + '&layer=mapnik';
    if (mapFrame && mapFrame.getAttribute('src') !== nextUrl) mapFrame.src = nextUrl;
    if (mapLink) mapLink.href = 'https://www.openstreetmap.org/#map=11/' + latitude + '/' + longitude;
}

centerEmbedMap(14.24568806399191, 121.55897618339998);

async function refreshLocation() {
    try {
        const response = await fetch('latest-location.php', { cache: 'no-store' });
        if (!response.ok) throw new Error('Location request failed');
        const location = await response.json();
        if (location.latitude == null || location.longitude == null) {
            locationDot.hidden = true;
            centerEmbedMap(14.1815, 121.6875);
            status.textContent = location.message || 'No collector is currently sharing location during an assigned collection schedule.';
            return;
        }

        const latitude = Number(location.latitude);
        const longitude = Number(location.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) throw new Error('Invalid coordinates');
        centerEmbedMap(latitude, longitude);
        locationDot.hidden = false;
        const updatedAt = location.recorded_at ? new Date(location.recorded_at) : null;
        status.textContent = (location.vehicle_number || 'Waste collection truck') + ' on ' + (location.route_name || 'assigned route') +
            (updatedAt && !Number.isNaN(updatedAt.getTime()) ? ' · Updated ' + updatedAt.toLocaleString() : ' · Location received');
    } catch (error) {
        locationDot.hidden = true;
        centerEmbedMap(14.1815, 121.6875);
        status.textContent = 'Could not verify the active collection schedule. Location is hidden until it can be checked.';
    }
}

refreshLocation();
window.setInterval(refreshLocation, 10000);
</script>
</body>
</html>
