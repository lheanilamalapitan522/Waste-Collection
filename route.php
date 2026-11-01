<?php
require_once 'auth.php';
require 'config.php';

$route = $pdo->query("SELECT id, name, barangay, status FROM routes WHERE status = 'active' ORDER BY id LIMIT 1")->fetch();
$routePoints = [];
$routeSchedules = [];
if ($route) {
    $pointsQuery = $pdo->prepare('SELECT point_name, latitude, longitude FROM route_points WHERE route_id = :route_id ORDER BY point_order');
    $pointsQuery->execute([':route_id' => $route['id']]);
    $routePoints = $pointsQuery->fetchAll();
    $scheduleQuery = $pdo->prepare("SELECT day_name, collection_time FROM schedule WHERE route_id = :route_id AND status = 'active' ORDER BY FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $scheduleQuery->execute([':route_id' => $route['id']]);
    $routeSchedules = $scheduleQuery->fetchAll();
}
$pointData = array_map(static function (array $point): array {
    return [
        'name' => $point['point_name'],
        'lat' => (float) $point['latitude'],
        'lng' => (float) $point['longitude']
    ];
}, $routePoints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Routes — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
    <style>
        #routes-map { width: 100%; height: 540px; border-radius: 14px; background: #e7ece6; }
        .route-map-card { padding: 14px; }
        .route-stop-list { list-style: none; margin: 18px 0 0; padding: 0; }
        .route-stop { position: relative; display: flex; gap: 13px; padding: 0 0 18px; }
        .route-stop:not(:last-child)::before { position: absolute; top: 17px; bottom: -2px; left: 9px; width: 2px; background: #dce9db; content: ""; }
        .route-stop-dot { z-index: 1; width: 20px; height: 20px; flex: 0 0 20px; border: 5px solid #e4f2e2; border-radius: 50%; background: #21813f; }
        .route-stop:last-child .route-stop-dot { background: #d97706; }
        .route-stop strong { display: block; color: #23442b; font-size: .92rem; }
        .route-stop small { color: #718074; }
        @media(max-width: 768px) { #routes-map { height: 380px; } }
    </style>
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl route-page">
    <a href="index.php" class="back-link">← Back to Home</a>
    <header class="route-heading">
        <div>
            <p class="eyebrow">SERVICE AREA</p>
            <h1>Collection route</h1>
            <p>Follow the service path and see each collection point.</p>
        </div>
        <?php if (is_admin()): ?><a href="admin.php" class="btn btn-outline-success">Open administration</a><?php endif; ?>
    </header>

    <section class="row g-4">
        <div class="col-12 col-lg-8">
            <article class="section-card route-map-card">
                <div class="route-map-heading">
                    <div><span class="eyebrow">MAP VIEW</span><h2><?php echo $route ? htmlspecialchars($route['name'], ENT_QUOTES, 'UTF-8') : 'No active route'; ?></h2></div>
                    <span class="route-live-badge"><span class="live-dot"></span> GPS STATUS</span>
                </div>
                <p class="route-location-status" id="route-location-status" role="status">Checking for a driver location…</p>
                <div id="routes-map" class="osm-map" aria-label="OpenStreetMap centered on this route and the active collector">
                    <iframe id="route-map-frame" title="OpenStreetMap collection route area" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" src="https://www.openstreetmap.org/export/embed?bbox=121.17233276367189%2C14.01335989114583%2C121.98944091796876%2C14.485876994143412&amp;layer=mapnik"></iframe>
                    <span id="route-truck-marker" class="osm-truck-marker" hidden role="img" aria-label="Waste collector truck location"><svg viewBox="0 0 48 48" aria-hidden="true"><path fill="currentColor" d="M5 12h23a3 3 0 0 1 3 3v4h6.4a4 4 0 0 1 3.2 1.6l3.6 4.8A4 4 0 0 1 45 27v9h-3.2a6 6 0 0 1-11.6 0H18.8a6 6 0 0 1-11.6 0H4a2 2 0 0 1-2-2V15a3 3 0 0 1 3-3Zm26 10v7h10v-2l-3.7-5H31ZM13 33a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm23 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg></span>
                </div>
                <p class="small mt-2 mb-0"><a id="route-map-link" href="https://www.openstreetmap.org/#map=14/14.1815/121.6875" target="_blank" rel="noopener noreferrer">View larger map</a></p>
            </article>
        </div>
        <aside class="col-12 col-lg-4">
            <article class="route-info-card">
                <p class="eyebrow">ROUTE DETAILS</p>
                <?php if ($route): ?>
                    <span class="route-code">ROUTE <?php echo str_pad((string) $route['id'], 2, '0', STR_PAD_LEFT); ?></span>
                    <h2><?php echo htmlspecialchars($route['barangay'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="route-info-copy">Stops along this collection route</p>
                    <?php if ($routePoints): ?>
                        <ol class="route-stop-list">
                        <?php foreach ($routePoints as $index => $point): ?>
                            <li class="route-stop"><span class="route-stop-dot" aria-hidden="true"></span><div><strong><?php echo htmlspecialchars($point['point_name'], ENT_QUOTES, 'UTF-8'); ?></strong><small>Stop <?php echo $index + 1; ?></small></div></li>
                        <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="route-empty">No stops have been added to this route yet.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <h2>No active route</h2>
                    <p class="route-info-copy">There are no active collection routes to display.</p>
                <?php endif; ?>
            </article>
            <article class="route-info-card route-schedule-card">
                <p class="eyebrow">COLLECTION WINDOWS</p>
                <h2>Schedule</h2>
                <?php if ($routeSchedules): ?>
                    <?php foreach ($routeSchedules as $entry): ?>
                        <div class="route-schedule-row"><strong><?php echo htmlspecialchars($entry['day_name'], ENT_QUOTES, 'UTF-8'); ?></strong><span><?php echo htmlspecialchars($entry['collection_time'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="route-info-copy">No active collection times are listed.</p>
                <?php endif; ?>
                <a href="schedule.php" class="route-text-link">Full schedule <span aria-hidden="true">→</span></a>
            </article>
        </aside>
    </section>
</main>
<script>
const points = <?php echo json_encode($pointData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const selectedRouteName = <?php echo json_encode($route['name'] ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
const routeMapFrame = document.getElementById('route-map-frame');
const routeTruckMarker = document.getElementById('route-truck-marker');
const routeMapLink = document.getElementById('route-map-link');
const routeStatus = document.getElementById('route-location-status');
const defaultCenter = points.length ? points[0] : { lat: 14.1815, lng: 121.6875 };
function setOsmRouteCenter(latitude, longitude) {
    const halfLng = 0.03;
    const halfLat = 0.0175;
    const bbox = [longitude - halfLng, latitude - halfLat, longitude + halfLng, latitude + halfLat].join(',');
    const url = 'https://www.openstreetmap.org/export/embed?bbox=' + encodeURIComponent(bbox) + '&layer=mapnik';
    if (routeMapFrame && routeMapFrame.getAttribute('src') !== url) routeMapFrame.src = url;
    if (routeMapLink) routeMapLink.href = 'https://www.openstreetmap.org/#map=14/' + latitude + '/' + longitude;
}
setOsmRouteCenter(defaultCenter.lat, defaultCenter.lng);
async function refreshRouteTruck() {
    try {
        const response = await fetch('latest-location.php', { cache: 'no-store' });
        if (!response.ok) throw new Error('Location unavailable');
        const location = await response.json();
        if (location.latitude == null || location.longitude == null) {
            routeTruckMarker.hidden = true;
            setOsmRouteCenter(defaultCenter.lat, defaultCenter.lng);
            routeStatus.textContent = location.message || 'No collector is currently sharing location during an assigned schedule.';
            return;
        }
        if (location.route_name !== selectedRouteName) {
            routeTruckMarker.hidden = true;
            setOsmRouteCenter(defaultCenter.lat, defaultCenter.lng);
            routeStatus.textContent = 'The active collector is assigned to ' + (location.route_name || 'a different route') + '.';
            return;
        }
        const latitude = Number(location.latitude);
        const longitude = Number(location.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) throw new Error('Invalid location');
        setOsmRouteCenter(latitude, longitude);
        routeTruckMarker.hidden = false;
        routeStatus.textContent = location.recorded_at
            ? (location.vehicle_number || 'Truck') + ' · Updated ' + new Date(location.recorded_at).toLocaleString()
            : 'Collector location received.';
    } catch (error) {
        routeTruckMarker.hidden = true;
        routeStatus.textContent = 'Could not verify the active schedule. Location is hidden.';
    }
}
refreshRouteTruck();
window.setInterval(refreshRouteTruck, 10000);
</script>
</body>
</html>
