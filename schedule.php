<?php
require_once 'session.php';
require 'config.php';
$scheduleRows = $pdo->query(
    "SELECT r.id AS route_id, r.name AS route_name, r.barangay, s.day_name, s.collection_time
     FROM schedule s JOIN routes r ON r.id = s.route_id
     WHERE s.status = 'active' AND r.status = 'active'
     ORDER BY r.id, FIELD(s.day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.id"
)->fetchAll();
$schedulesByRoute = [];
foreach ($scheduleRows as $row) {
    $schedulesByRoute[$row['route_id']]['route'] = $row;
    $schedulesByRoute[$row['route_id']]['entries'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Schedule — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl schedule-page">
    <a href="index.php" class="back-link">← Back to Home</a>
    <header class="schedule-heading">
        <div><p class="eyebrow">MAUBAN WASTE COLLECTION</p><h1>Collection schedule</h1><p>Choose your service area to see the regular collection days and hours.</p></div>
        <a href="monthly-schedule.php" class="schedule-month-link btn btn-outline-success">View monthly schedule <span aria-hidden="true">→</span></a>
    </header>
    <section class="schedule-route-grid row g-3" aria-label="Weekly collection schedule by route">
        <?php foreach ($schedulesByRoute as $group): $route = $group['route']; ?>
        <article class="schedule-route-card col-12 col-md-6 col-lg-4">
            <div class="schedule-route-top"><span class="schedule-route-id"><?php echo htmlspecialchars(strtoupper($route['route_name']), ENT_QUOTES, 'UTF-8'); ?></span><span class="schedule-route-mark"><?php echo sprintf('%02d', (int) $route['route_id']); ?></span></div>
            <h2><?php echo htmlspecialchars($route['barangay'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="schedule-route-description">Regular household collection</p>
            <?php foreach ($group['entries'] as $entry): ?>
            <div class="schedule-time-row"><span class="schedule-day"><?php echo htmlspecialchars(strtoupper(substr($entry['day_name'], 0, 3)), ENT_QUOTES, 'UTF-8'); ?></span><span><?php echo htmlspecialchars($entry['collection_time'], ENT_QUOTES, 'UTF-8'); ?></span></div>
            <?php endforeach; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$schedulesByRoute): ?><div class="col-12"><p class="text-muted">No active collection schedules are available right now.</p></div><?php endif; ?>
    </section>
    <p class="schedule-footnote">Collection times are service windows and may vary with weather or operating conditions.</p>
</main>
</body>
</html>
