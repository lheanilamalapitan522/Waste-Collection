<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="home-page container-xl">
    <section class="landing-hero">
        <div class="hero">
            <p class="eyebrow">MAUBAN MUNICIPAL SERVICE</p>
            <h1>Clean Mauban<br><span>starts with all of us.</span></h1>
            <p class="hero-description">Find your collection schedule, explore service routes, and check the latest truck location for your community.</p>
            <div class="hero-buttons">
                <a href="schedule.php" class="btn-primary">View collection schedule</a>
                <a href="live-tracking.php" class="btn-secondary">Track a truck</a>
            </div>
            <p class="hero-note">Waste collection information for Mauban, Quezon.</p>
        </div>
        <aside class="hero-panel">
            <div class="hero-panel-icon" aria-hidden="true">MW</div>
            <p class="hero-panel-label">LOCAL SERVICE</p>
            <h2>Collection information, all in one place.</h2>
            <p>Schedules, routes, and driver-reported locations for residents and municipal teams.</p>
        </aside>
    </section>

    <section class="truck-overview" aria-labelledby="quick-links-title">
        <div class="truck-overview-heading">
            <p class="eyebrow">QUICK LINKS</p>
            <h2 id="quick-links-title">Plan your collection</h2>
            <p>Choose an area of the service to explore.</p>
        </div>
        <div class="truck-overview-grid row g-3">
            <a href="live-tracking.php" class="truck-overview-card col-12 col-md-6 col-xl-3">
                <span class="truck-card-icon" aria-hidden="true">01</span>
                <div><span class="truck-card-label">LIVE TRACKING</span><strong>Truck location</strong><small>View the latest shared position</small></div>
            </a>
            <a href="schedule.php" class="truck-overview-card col-12 col-md-6 col-xl-3">
                <span class="truck-card-icon" aria-hidden="true">02</span>
                <div><span class="truck-card-label">SCHEDULE</span><strong>Collection days</strong><small>See service times by route</small></div>
            </a>
            <a href="route.php" class="truck-overview-card col-12 col-md-6 col-xl-3">
                <span class="truck-card-icon" aria-hidden="true">03</span>
                <div><span class="truck-card-label">ROUTES</span><strong>Service areas</strong><small>Explore routes and stops</small></div>
            </a>
            <a href="about.php" class="truck-overview-card col-12 col-md-6 col-xl-3">
                <span class="truck-card-icon" aria-hidden="true">04</span>
                <div><span class="truck-card-label">ABOUT</span><strong>Our service</strong><small>Learn how the system works</small></div>
            </a>
        </div>
    </section>

    <section class="map-preview-section">
        <div class="section-heading">
            <p class="eyebrow">SERVICE AREA</p>
            <h2>Collection routes in Mauban</h2>
            <p>OpenStreetMap service area. The truck marker follows the latest shared location.</p>
        </div>
        <div class="map-preview">
            <div id="home-map" class="osm-map" aria-label="OpenStreetMap preview of Mauban and the latest truck location">
                <iframe title="OpenStreetMap service area and truck location" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" src="https://www.openstreetmap.org/export/embed?bbox=121.17233276367189%2C14.01335989114583%2C121.98944091796876%2C14.485876994143412&amp;layer=mapnik"></iframe>
                <span id="home-truck-marker" class="osm-truck-marker" hidden role="img" aria-label="Waste collector truck location"><svg viewBox="0 0 48 48" aria-hidden="true"><path fill="currentColor" d="M5 12h23a3 3 0 0 1 3 3v4h6.4a4 4 0 0 1 3.2 1.6l3.6 4.8A4 4 0 0 1 45 27v9h-3.2a6 6 0 0 1-11.6 0H18.8a6 6 0 0 1-11.6 0H4a2 2 0 0 1-2-2V15a3 3 0 0 1 3-3Zm26 10v7h10v-2l-3.7-5H31ZM13 33a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm23 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg></span>
            </div>
            <div class="map-preview-card">
                <span class="live-dot" aria-hidden="true"></span>
                <div><strong>Latest truck location</strong><p id="home-tracking-status" data-map-status role="status">Checking for a driver location…</p></div>
                <a href="live-tracking.php" class="map-link">Open live map</a>
            </div>
        </div>
    </section>

    <section class="community-strip">
        <div>
            <p class="eyebrow">SERVING OUR COMMUNITY</p>
            <h2>Clear information for a cleaner Mauban.</h2>
        </div>
        <a href="about.php" class="community-link">About the service</a>
    </section>
</main>

<script>
const homeMapFrame = document.querySelector('#home-map iframe');
const homeTruckMarker = document.getElementById('home-truck-marker');
const homeStatus = document.getElementById('home-tracking-status');
function setOsmCenter(frame, latitude, longitude) {
    const halfLng = 0.03;
    const halfLat = 0.0175;
    const bbox = [longitude - halfLng, latitude - halfLat, longitude + halfLng, latitude + halfLat].join(',');
    const url = 'https://www.openstreetmap.org/export/embed?bbox=' + encodeURIComponent(bbox) + '&layer=mapnik';
    if (frame && frame.getAttribute('src') !== url) frame.src = url;
}
async function refreshHomeTruck() {
    try {
        const response = await fetch('latest-location.php', { cache: 'no-store' });
        if (!response.ok) throw new Error('Location unavailable');
        const location = await response.json();
        if (location.latitude == null || location.longitude == null) {
            homeTruckMarker.hidden = true;
            setOsmCenter(homeMapFrame, 14.2497, 121.5809);
            homeStatus.textContent = location.message || 'No collector is currently on duty and sharing location.';
            return;
        }
        const latitude = Number(location.latitude);
        const longitude = Number(location.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
            homeTruckMarker.hidden = true;
            setOsmCenter(homeMapFrame, 14.2497, 121.5809);
            homeStatus.textContent = location.message || 'No collector is currently on duty and sharing location.';
            return;
        }
        setOsmCenter(homeMapFrame, latitude, longitude);
        homeTruckMarker.hidden = false;
        homeStatus.textContent = location.recorded_at
            ? (location.vehicle_number || 'Truck') + ' · Updated ' + new Date(location.recorded_at).toLocaleTimeString()
            : 'Location received';
    } catch (error) {
        homeTruckMarker.hidden = true;
        homeStatus.textContent = 'Could not verify the current collection schedule. Location is hidden.';
    }
}
refreshHomeTruck();
window.setInterval(refreshHomeTruck, 10000);
</script>
<script src="script.js"></script>
</body>
</html>
