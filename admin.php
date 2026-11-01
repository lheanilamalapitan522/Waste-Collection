<?php
require_once 'auth.php';
require 'config.php';
require_role('admin');

$totalRoutes = (int) $pdo->query('SELECT COUNT(*) FROM routes')->fetchColumn();
$activeRoutes = (int) $pdo->query("SELECT COUNT(*) FROM routes WHERE status = 'active'")->fetchColumn();
$totalDrivers = (int) $pdo->query('SELECT COUNT(*) FROM drivers')->fetchColumn();
$activeDrivers = (int) $pdo->query("SELECT COUNT(*) FROM drivers WHERE status = 'active'")->fetchColumn();
$totalSchedules = (int) $pdo->query('SELECT COUNT(*) FROM schedule')->fetchColumn();

$routes = $pdo->query('SELECT id, name, barangay, status FROM routes ORDER BY id')->fetchAll();
$schedules = $pdo->query(
    "SELECT s.id, r.name AS route_name, r.barangay, s.day_name, s.collection_time, s.status
     FROM schedule s LEFT JOIN routes r ON r.id = s.route_id
     ORDER BY FIELD(s.day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.id"
)->fetchAll();
$drivers = $pdo->query(
    'SELECT d.name, d.status, v.vehicle_number FROM drivers d
     LEFT JOIN vehicles v ON v.id = d.vehicle_id ORDER BY d.id'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl admin-page">
    <header class="admin-heading" id="overview">
        <div>
            <p class="eyebrow">SYSTEM OVERVIEW</p>
            <p>Review current routes, schedules, and collection vehicles.</p>
        </div>
    </header>

    <section class="admin-stat-grid row g-3" aria-label="System totals">
        <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat-card"><span>ACTIVE ROUTES</span><strong><?php echo $activeRoutes; ?><small> / <?php echo $totalRoutes; ?></small></strong></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat-card"><span>ACTIVE DRIVERS</span><strong><?php echo $activeDrivers; ?><small> / <?php echo $totalDrivers; ?></small></strong></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat-card"><span>SCHEDULE ENTRIES</span><strong><?php echo $totalSchedules; ?></strong></article></div>
        <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat-card admin-stat-action"><span>LIVE SERVICE</span><a href="live-tracking.php">Open truck map <span aria-hidden="true">→</span></a></article></div>
    </section>

    <section class="admin-table-grid">
        <article class="section-card admin-table-card" id="routes">
            <div class="section-top"><div><p class="eyebrow">SERVICE AREAS</p><h2 class="section-title">Routes</h2></div><a href="route.php" class="btn btn-outline-success btn-sm">View route map</a></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>ROUTE</th><th>BARANGAY</th><th>STATUS</th></tr></thead>
                <tbody>
                <?php foreach ($routes as $route): ?>
                    <tr><td><?php echo htmlspecialchars($route['name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($route['barangay'], ENT_QUOTES, 'UTF-8'); ?></td><td><span class="status-badge status-<?php echo htmlspecialchars($route['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst($route['status']), ENT_QUOTES, 'UTF-8'); ?></span></td></tr>
                <?php endforeach; ?>
                <?php if (!$routes): ?><tr><td colspan="3" class="text-muted">No routes have been added.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </article>

        <article class="section-card admin-table-card" id="schedules">
            <div class="section-top"><div><p class="eyebrow">COLLECTION WINDOWS</p><h2 class="section-title">Schedules</h2></div><a href="schedule.php" class="btn btn-outline-success btn-sm">View schedule</a></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>ROUTE / BARANGAY</th><th>DAY</th><th>TIME</th></tr></thead>
                <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr><td><?php echo htmlspecialchars(($schedule['route_name'] ?? 'Unassigned') . ' · ' . ($schedule['barangay'] ?? 'No barangay'), ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($schedule['day_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($schedule['collection_time'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$schedules): ?><tr><td colspan="3" class="text-muted">No schedules have been added.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </article>

        <article class="section-card admin-table-card" id="team">
            <div class="section-top"><div><p class="eyebrow">COLLECTION TEAM</p><h2 class="section-title">Drivers and vehicles</h2></div><a href="users.php" class="btn btn-outline-success btn-sm">Manage accounts</a></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>DRIVER</th><th>VEHICLE</th><th>STATUS</th></tr></thead>
                <tbody>
                <?php foreach ($drivers as $driver): ?>
                    <tr><td><?php echo htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($driver['vehicle_number'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8'); ?></td><td><span class="status-badge status-<?php echo htmlspecialchars($driver['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst($driver['status']), ENT_QUOTES, 'UTF-8'); ?></span></td></tr>
                <?php endforeach; ?>
                <?php if (!$drivers): ?><tr><td colspan="3" class="text-muted">No drivers have been added.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </article>
    </section>
</main>
</body>
</html>
